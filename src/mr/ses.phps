<?php
	class mr_ses implements i_initiate {
		static private
			$table = "mr_sessions",
			$sidname = "mrsid",
			$life = 2400,
			$autorun = true,
			$gabbage_clean = 3,
			$logout = "logout",
			$robots = "",
			$domain = ".mir.io";

		static private $sid = false, $userid=0, $ok = false, $data=array(), $data_changed=false, $position=false,
			$justStarted = false;

		/**
		 * Функция инициализации через окружение mr.
		 *
		 * Параметры конфигурации:
		 * table = mr_sessions Табличка или другое хранилище сессий
		 * sidname = mrsid Имя для сессионного параметра
		 * life = 2400 Время, после которого удаляется сессия
		 * autorun = yes Начинать ли автоматически сессию без user_id
		 * gabbage_clean = 3 Процент вероятности удаления старых сессий
		 * logout = logout Переменная $_GET, при установлении которой -- выходит
		 * robots Список через запятую подстрок, по которым определяются роботы
		 *
		 * @param array $mr_config_params Массив конфигурации сессий
		 */
		static public function init($mr_config_params)
		{
			// Устанавливаем параметры конфигурации
			foreach($mr_config_params as $name => $value)
			{
				if(isset(self::$$name) && $value!=self::$$name) self::$$name = $value;
			}

			// Ищем идентификатор сессии, если есть
			self::$sid = @$_GET[self::$sidname];
			if(strlen(self::$sid) != 32)
				self::$sid = @$_POST[self::$sidname];
			if(strlen(self::$sid) != 32)
				self::$sid = @$_COOKIE[self::$sidname];

			// Определение пользователя в один запрос, по сиду -- если авторизован,
			// по айпи -- иначе
			if(strlen(self::$sid) == 32)
			{
				$r = mr_sql::qw(
					"SELECT * FROM ".self::$table." WHERE id=?",
					self::$sid
					);
			} else {
				$r = mr_sql::qw(
					"SELECT * FROM ".self::$table." WHERE user_id IS NULL AND remote_addr=?",
					$_SERVER['REMOTE_ADDR']
					);
			}

			// Если для пользователя уже есть строчка в базе данных
			if(mr_sql::num_rows($r) == 1)
			{
				$f = mr_sql::fetch($r, mr_sql::obj);
				self::$sid = $f->id;
				// Проверка по айпи
				if($f->remote_addr == $_SERVER['REMOTE_ADDR'] || $f->check_ip == "no" || !$f->remote_addr)
				{
					self::$userid = $f->user_id;
					self::$ok = true;

					if(!isset($_GET[self::$logout])){
						self::$data = unserialize( $f->data );
						if(!self::$data) self::$data = array();

						self::sharesid();
					} else self::end();
				}
			}

			// Если сессия не инициализирована
			if(!self::$ok)
			{
				self::$sid = false;
				if(self::$autorun) self::start();
			}

			// Очистка старых записей с долей вероятности
			if(mt_rand(0, 100) <= self::$gabbage_clean) {
				self::gabbage_clean();
			}
		}

		static public function justStarted()
		{
			return self::$justStarted;
		}

		/**
		 * Начало новой сессии, если сессия уже длится -- обновление её параметров
		 *
		 * @param int $userID Идентификатор пользователя
		 * @param bool $checkIP Проверять ли REMOTE_ADDR
		 * @param int $status Статус пользователя, если нужен
		 */
		static public function start($userID=NULL, $checkIP=true, $status=0)
		{
			self::$userid = $userID;
			if(!self::$sid)
			{
				mt_srand(($userID+1)*microtime(true)*1000000);
				self::$sid=md5(base_convert(($userID+1).mt_rand(10000000, 100000000).($userID*time()+1), 10, 36));
			}
			if(!self::$userid) self::$justStarted = true;
			if($userID)
			{
				mr_sql::qw("DELETE FROM ".self::$table." WHERE user_id=?", $userID);
			} else {
				mr_sql::qw("DELETE FROM ".self::$table." WHERE user_id IS NULL AND remote_addr=?", $_SERVER['REMOTE_ADDR']);
			}
			mr_sql::qw(
				self::$ok ? "UPDATE ".self::$table." SET user_id=?, check_ip=?, status=?, time=UNIX_TIMESTAMP(), remote_addr=? WHERE id=?" : "INSERT INTO ".self::$table."(user_id, check_ip, status, time, remote_addr, id) VALUES(?, ?, ?, UNIX_TIMESTAMP(), ?, ?)",
				$userID, $checkIP ? "yes" : "no", $status, $_SERVER['REMOTE_ADDR'], self::$sid
				);

			if(count(self::$data))
			{
				self::$data = array();
				self::$data_changed = true;
			}

			self::$ok = true;

			self::sharesid();
		}

		/**
		 * Возвращает состояние текущей сессии
		 *
		 * @return bool
		 */
		static public function ok()
		{
			return self::$ok;
		}

		/**
		 * Завершает текущую сессию
		 *
		 */
		static public function end()
		{
			if(self::$ok)
			{
				self::$data = array();
				mr_sql::qw("DELETE FROM ".self::$table." WHERE id=? LIMIT 1", self::$sid);

				self::$sid = self::$ok = false;
				self::$userid = false;
			}
		}

		/**
		 * Идентификатор сессии в виде имя=значение (для установки в http).
		 *
		 * @param string $prefix="" Префикс вроде ? или & для установки в URL
		 * @param bool $force=false Не проверять наличие Cookie для аттрибута
		 *
		 * @return string
		 */
		static public function sessid($prefix="", $force=false)
		{
			return self::$sid&&self::$userid&&(self::$sid!=@$_COOKIE[self::$sidname]||$force)?($prefix.self::$sidname."=".self::$sid):"";
		}

		/**
		 * Возвращает айди пользователя
		 *
		 */
		static public function id()
		{
			return self::$userid;
		}

		/**
		 * Установка переменной сеанса, доступной только этому пользователю.
		 * В функции производится сериализация объекта $value
		 *
		 * @param string $name
		 * @param mixed $value
		 */
		static public function set($name, $value)
		{
			if(self::$ok)
			{
				self::$data[$name]=$value;
				self::$data_changed = true;
			}
		}

		/**
		 * Возврат переменной сеанса, доступной только этому пользователю.
		 * Возвращается десериализованный объект
		 *
		 * @param string $name
		 *
		 * @return mixed
		 */
		static public function get($name)
		{
			if(self::$ok)
			{
				return @self::$data[$name];
			} else return false;
		}

		/**
		 * Удаляет переменную сеанса
		 *
		 * @param string $name
		 */
		static public function clear($name)
		{
			unset(self::$data[$name]);
			self::$data_changed = true;
		}

		static public function position($pos = true)
		{
			self::$position = $pos;
		}

		static public function update()
		{
			if(!self::$ok) return;

			if(self::$data_changed)
				 mr_sql::qw(
						"UPDATE ".self::$table." SET data=? WHERE id=? LIMIT 1",
						serialize(self::$data), self::$sid);

			if(self::$position)
			{
				$pos = str_replace( self::sessid("", true), "", $_SERVER["REQUEST_URI"] );
			}

			if(self::$userid)
			{
				if(@$pos)
				{
					mr_sql::qw(
						"UPDATE ".self::$table." SET time=UNIX_TIMESTAMP(), position=? WHERE id=? LIMIT 1",
						$pos, self::$sid
					);
				} else {
					mr_sql::qw(
						"UPDATE ".self::$table." SET time=UNIX_TIMESTAMP() WHERE id=? LIMIT 1",
						self::$sid
					);
				}
			} else {
				if(@$pos)
				{
					mr_sql::qw(
						"UPDATE ".self::$table." SET time=UNIX_TIMESTAMP(), position=? WHERE user_id=0 AND remote_addr=? LIMIT 1",
						$pos, $_SERVER['REMOTE_ADDR']
					);
				} else {
					mr_sql::qw(
						"UPDATE ".self::$table." SET time=UNIX_TIMESTAMP() WHERE user_id IS NULL AND remote_addr=? LIMIT 1",
						$_SERVER['REMOTE_ADDR']
					);
				}
			}

		}

		static private function gabbage_clean()
		{
			mr_sql::query(
				"DELETE FROM ".self::$table." WHERE time<UNIX_TIMESTAMP()-".self::$life." LIMIT 1");
			mr_sql::query(
				"DELETE FROM ".self::$table." WHERE time<UNIX_TIMESTAMP()-".floor(self::$life/2)." AND user_id=0 LIMIT 5");

		}

		static private function sharesid()
		{
			register_shutdown_function(array(__CLASS__, "update"));
			if(!self::$userid) return;

			if(@$_COOKIE[self::$sidname] != self::$sid)
			{
				output_add_rewrite_var(self::$sidname, self::$sid);
			}
			setcookie(self::$sidname, self::$sid, 0, "/", self::$domain);
		}
	}