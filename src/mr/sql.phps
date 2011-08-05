<?php
/**
 * Абстракция для соединения и работы с СУБД mySQL
 *
 * @package mr
 * @author Dmitry Kourinski (cogito@mirari.ru)
 * @copyright (c) 2007
 */

	class mr_sql implements i_initiate{
		const assoc=1;
		const obj=2;
		const num=3;
		const get=4;
		
		static private $queries = 0, $connected = false;
		
		/**
		 * Функция запускается при инициации класса через mr::uses
		 * и соединяется с СУБД по переданным параметрам конфигурации
		 * 
		 * Параметры конфигурации:
		 * host =
		 * user =
		 * password =
		 * database =
		 *
		 * @param array $ini Массив конфигурации
		 */
		static public function init($ini)
		{
			$c = &$ini;
			
			if(!@mysql_connect($c["host"], $c["user"], $c["password"]))
			{
				throw new Exception("Невозможно соединиться с СУБД", 500);
   			return false;
			}
			
			if(!@mysql_select_db($c["database"]))
   		{
				throw new Exception("Соединение с СУБД установлено, но невозможно выбрать базу данных", 500);
   			return false;
			} 
			
			if(mysql_errno())
   		{
				throw new Exception("Ошибка ".mysql_errno()." при соединении с СУБД", 500);
   			return false;
			}
			
 			mysql_query("set character_set_client='utf8'");
 			mysql_query("set character_set_results='utf8'");
 			mysql_query("set collation_connection='utf8_unicode_ci'");
 			mr::log("Database connected");
 			self::$connected = true;
 			return true;
		}
		
		/**
		 * Возвращает состояние соединения с СУБД
		 * 
		 * @return bool
		 */
		static public function connected()
		{
			return self::$connected;
		}
		
		/**
		 * Производит обычное сообщение с СУБД
		 *
		 * @param string $query
		 * @return resourse
		 */
		static public function query($query)
		{
			++self::$queries;
			mr::log($query);
			return mysql_query($query);
		}
		
		/**
		 * Обрабатывает и выполняет произвольное sql-выражение с подстановками.
		 * 
		 * Например
		 * <code>
		 * mr_sql::qw("SELECT * FROM table WHERE login=? OR id=?", "login", 7);
		 * </code>
		 * эквивалентно
		 * <code>
		 * mr_sql::query("SELECT * FROM table WHERE login='login' OR id=7")
		 * </code>
		 *
		 * @param string $query
		 * @param mixed[opt] $param
		 * @return resourse
		 */
		static public function qw()
		{
			$args = func_get_args();
			
			$q = array_shift($args);
			return self::query( self::queryWrapper($q, $args) );
		}
		
		/**
		 * Обработчик запросов mysql. Заменяет ? на параметры.
		 * Публичный для устроения тестов
		 *
		 * @param string $query
		 * @param array $params
		 * @return string
		 * @access private
		 */
		static public function queryWrapper($query, $params)
		{
			$query = str_replace("%", "%%", $query);
			$query = str_replace("?", "%s", $query);
			foreach ($params as $k=>$v) {
				if(is_null($v))
				{
					$params[$k]="NULL";
					continue;
				}
				if($v === '')
				{
					$params[$k]="''";
					continue;
				}
				if(is_array($v)) $v = "(".join(",", $v).")";
				if(is_double($v)) $params[$k] = str_replace(",", ".", (string)$v);
				if(is_numeric($v)) continue;
				$params[$k] = "'".mysql_escape_string($v)."'";
			}
			
			array_unshift($params, $query);
			return call_user_func_array("sprintf", $params);
		}
		
		/**
		 * Возвращает массив или ячейку из результата запроса. Запрос может быть
		 * как вида string для запуска mr_sql::query(), так и вида array() для mr_sql::qw()
		 * или resourse для прямого использования
		 *
		 * @param array|resourse|string $request
		 * @param int $mode=self::assoc
		 * @return mixed
		 */
		static public function fetch($request, $mode=self::assoc)
		{
			if(is_array($request))
			{
				$r = call_user_func_array(array(__CLASS__, "qw"), $request);
			} elseif(is_string($request)) {
				$r = self::query($request);
			} elseif(is_resource($request)) {
				$r = &$request;
			}
			
			if(@$r) {
				switch($mode)
				{
					case self::assoc:
						return mysql_fetch_assoc($r);
					break;
					case self::num:
						return mysql_fetch_array($r);
					break;
					case self::obj:
						return mysql_fetch_object($r);
					break;
					case self::get:
						$f = mysql_fetch_array($r);
						return $f[0];
					break;
					default:
						return false;
				}
			} else return false;
		}
		
		/**
		 * Возвращает строку последней ошибки в работе СУБД
		 *
		 * @return string
		 */
		static public function error()
		{
			return @mysql_error();
		}
		
		/**
		 * Возвращает количество строчек, содержащихся в ресурсе запроса
		 *
		 * @param resourse $result
		 * @return int
		 */
		static public function num_rows($result)
		{
			return (int)mysql_num_rows($result);
		}
		
		/**
		 * Последний вставленный auto_increment primary key
		 *
		 * @return int
		 */
		static public function insert_id()
		{
			return (int)@mysql_insert_id();
		}
		
		/**
		 * Количество строк, затронутых последним преобразованием
		 *
		 * @return int
		 */
		static public function affected_rows()
		{
			return (int)@mysql_affected_rows();
		}
		
		/**
		 * Количество query за эту сессию
		 *
		 * @return int
		 */
		static public function queries()
		{
			return self::$queries;
		}
		
		static public function found_rows()
		{
			return self::fetch("SELECT FOUND_ROWS()", self::get);
		}
	}
?>
