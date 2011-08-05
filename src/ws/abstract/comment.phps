<?php
	abstract class ws_abstract_comment extends mr_abstract_change {
 static protected $objs;
 
/**
 * Отправляет оповещения об этом сообщении всем подписчикам
 *
 */
 abstract public function notify_subscribers();
 
/**
 * Проверяет права пользователя и выводит, может ли сообщение быть показано
 *
 */
 abstract public function is_showable();
 
/**
 * Права администратора
 *
 */
 abstract public function can_edit();
 abstract public function can_hide();
 abstract public function can_delete();
 
/**
 * Ссылка на родителя комментария
 */
 abstract public function parent_link();
 
/**
 * Вывод перед текстом
 * 
 * @return string
 */
public function out_pre()
{
	return "";
}
/**
 * Вывод в адм. консоли
 * 
 * @return string
 */
public function out_adm()
{
	return "";
}
 
/**
 * Осуществляет выборку нескольких записей одним запросом
 *
 * @param string|array $where="1=1" where-запрос или массив полей id
 * @param int $limit Количество выбранных записей
 * @param int $offset Смещение в результате
 * @param string $order="time DESC"
 * @param int &$calcResult=false Если переменная передана, то в неё запишется полное количество строчек в запросе
 * @return mr_list массив объектов сообщений
 */
 abstract static public function several($where="1=1", $limit=0, $offset=0, $order="time DESC", &$calcResult=false);
 
/**
 * Осуществляет выборку нескольких записей одним запросом
 *
 * @param string $query
 * @param int &$calcResult=false Если переменная передана, то в неё запишется полное количество строчек в запросе
 * @return mr_list массив объектов сообщений
 */
 abstract static public function several_query($query, &$calcResult=false);
 
/**
 * Возвращает объект сообщения
 *
 * @param int $id
 * @param array[opt] $arr Ассоциативный массив данных записи
 * @return self
 */
 abstract static public function factory($id, $arr=false);
 
 static protected function sub_several($class, $where, $limit, $offset, $order, &$calcResult)
 {
 	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
  $q = "SELECT ";
  if($calcResult !== false) $q.="SQL_CALC_FOUND_ROWS ";
  $q.="* FROM ".constant("$class::sqlTable")." WHERE ".$where;
  if($order) $q.=" ORDER BY $order";
  if($limit) $q.=" LIMIT ".($offset?$offset.", ":"").$limit;

  $return = self::sub_several_query($class, $q, $calcResult);
  
  return $return;
 }
 
 static protected function sub_factory($class, $id, $arr)
 {
  if(!isset(self::$objs[$class][$id]))
  	self::$objs[$class][$id] = new $class($id, $arr);
  return self::$objs[$class][$id];
 }
 
 static protected function sub_several_query($class, $query, &$calcResult)
 {
  $r = mr_sql::query($query);
  
  if($calcResult !== false) $calcResult = mr_sql::found_rows();
  
  $usrs = array();
  $return = array();
  
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$return[] = $f["id"];
  	self::sub_factory($class, $f["id"], $f);
  	if($f["user_id"] && !in_array($f["user_id"], $usrs)) $usrs[] = $f["user_id"];
  }
  
  ws_user::several($usrs);
  
  return new mr_list($class, $return);
 }
 
 
/**
 * Конструктор.
 *
 * @param int $id
 * @param array[opt] $arr=false Ассоциативный массив данных записи
 */
 protected function __construct($id, $arr=false)
 { 	
  $this->id = (int)$id;
  $this->arr = is_array($arr)?$arr:mr_sql::fetch("SELECT * FROM ".constant(get_class($this)."::sqlTable")." WHERE id=".$this->id, mr_sql::assoc);
 }
 
/**
 * Абстрактно удаляет сообщение
 *
 * @return int
 */
 public function delete()
 {
 	ws_attach::checkXML($this->arr["content"], ws_attach::decrement);
 	mr_sql::qw("DELETE FROM ".constant(get_class($this)."::sqlTable")." WHERE id=".$this->id);
 	return mr_sql::affected_rows();
 }
 
/**
 * Возвращает xml-текст сообщения
 * 
 * @param string $nodeName="note" Имя узла, в котором контент
 *
 * @return xmlstring
 */
 public function xml($nodeName="note")
 {
  $r = "<".$nodeName;
  foreach($this->arr as $k=>$v) if($k!="content")
  	$r .= ' '.str_replace("_", "-", $k).'="'.htmlspecialchars($v).'"';
  $r.=">".$this->arr["content"]."</".$nodeName.">";
  return $r;
 }
 
/**
 * Возвращает объект автора записи
 *
 * @return ws_user
 */
 public function user()
 {
  return ws_user::factory($this->arr["user_id"]);
 }
	}
	
	/**
	 * Добавление параметров после поля ввода
	 *
	 */
		interface i_comment_afterform {
			static public function afterform($parent);
		}
		
	/**
	 * Подписки
	 *
	 */
		interface i_comment_reminder {
			static public function reminder($parent);
		}
?>