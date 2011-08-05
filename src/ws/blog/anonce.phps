<?php
/**
 * Класс анонсов на дневниковые записи
 *
 */
	class ws_blog_anonce extends mr_abstract_change {
		protected $resps;
		static private $objs = array();
		const fields = "id, user_id, time, title";
		const sqlTable = "mr_user_blog_threads";

/**
 * Проект factory
 *
 * @param int $id Идентификатор элемента
 * @param array[opt] $arr Ассоциативный массив инфы о элементе
 * @param int[opt] $resps Количество отзывов на элемент
 * @return ws_blog_anonce
 */
 static public function factory($id, $arr=false, $resps=false)
 {
 	return self::sub_factory(__CLASS__, $id, $arr, $resps);
 }
 
/**
 * Позволяет загружать много анонсов одним запросом
 *
 * @param string|array $cond="1=1" Строка -- where, массив -- список id.
 * @param int[opt] $limit Сколько штук
 * @param int[opt] $offset Смещение в результатах
 * @param string[opt] $order="time DESC" Ключ для сортировки
 * @return mr_list
 */
 static public function several($where="1=1", $limit=0, $offset=0, $order="time DESC", &$calcResult=false)
 {
 	return self::sub_several(__CLASS__, $where, $limit, $offset, $order, $calcResult);
 }
 
 protected function __construct($id, $arr=false, $resps=false)
 {
	return self::sub_construct(__CLASS__, $id, $arr, $resps);
 }
 
 static protected function sub_factory($class, $id, $arr, $resps)
 {
 	$id = (int)$id;
 	if(!isset(self::$objs[$class][$id]))
 		self::$objs[$class][$id] = new $class($id, $arr, $resps);
 	return self::$objs[$class][$id];
 }
 
 static protected function sub_several($class, $where, $limit, $offset, $order, &$calcResult)
 {
	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	return self::sub_query("SELECT ".($calcResult!==false?"SQL_CALC_FOUND_ROWS ":"").constant("$class::fields")." FROM ".self::sqlTable." WHERE $where".($order?" ORDER BY $order":"").($limit?" LIMIT ".($offset?"$offset, ":"").$limit:""), $calcResult, $class);
 }
 
/**
 * Запрос по строке запроса
 *
 * @param string $query
 * @param int $calcResult
 * @return mr_list
 */
 static public function sub_query($query, &$calcResult=false, $class=__CLASS__, $loadExternal=false)
 {
	$r = mr_sql::query($query);
 	if($calcResult !== false) $calcResult = mr_sql::found_rows();
 	
 	$array = array();
 	$ids = array();
 	
 	$usrs = array();
 	
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		$array[] = $f;
 		$ids[] = $f["id"];
 		if(!in_array($f["user_id"], $usrs)) $usrs[] = $f["user_id"];
 	}
 	
 	$ra = array();
 	
 	if($loadExternal&&count($ids))
 	{
	 	$resps = mr_sql::qw("SELECT COUNT(id) AS c, thread_id FROM mr_user_blog_msgs WHERE thread_id IN (".join(", ", $ids).") AND (hidden='no' OR user_id=".ws_self::id().") GROUP BY thread_id");
	 	while($r = mr_sql::fetch($resps, mr_sql::obj)) $ra[$r->thread_id]=$r->c;
	 	
	 	if(count($usrs)>1) 	ws_user::several($usrs);
	 	ws_blog_anchor::loadByItems($ids);
 	}
 	
	$ret = array();
 	
 	foreach($array as $a)
 	{
 		call_user_func_array(array($class, "factory"), array($a["id"], $a, (int)@$ra[ $a["id"] ]));
 		$ret[] = $a["id"];
 	}
 	
 	return new mr_list($class, $ret);
 }

 protected function sub_construct($class, $id, $arr, $resps)
 {
 	$this->id = $id;
 	if(is_array($arr))
 		$this->arr = $arr;
 	else
 		$this->arr = mr_sql::fetch("SELECT ".constant("$class::fields")." FROM mr_user_blog_threads WHERE id=".$this->id, mr_sql::assoc);
 
 	if(!is_array($this->arr)) return false;
 	if($resps>0 || $resps===0) $this->resps = $resps;
 }

/**
 * Возвращает количество отзывов на событие
 *
 * @return int
 */
 public function notes()
 {
 	if($this->resps === false)
 		$this->resps = mr_sql::fetch("SELECT COUNT(id) FROM mr_user_blog_msgs WHERE thread_id=".$this->id, mr_sql::get);
 	return $this->resps;
 }
 
/**
 * Возвращает объект автора элемента
 *
 * @return ws_user
 */
 public function auth()
 {
 	return ws_user::factory($this->arr["user_id"]);
 }
 
/**
 * Возвращает гиперссылку на элемент.
 *
 * @param string[opt] $class Класс ссылки
 * @return string
 */
 public function link($class="")
 {
 	return "<a href=\"".$this->href()."\"".($class?" class=\"$class\"":"").">".($this->arr["title"]?$this->arr["title"]:"<i>(Запись от ".date("d.m.Y H:i", $this->arr["time"]).")</i>")."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Возвращает адрес от корня до элемента
 *
 * @return string
 */
 public function href()
 {
 	return $this->auth()->href( $this->id.".ml" );
 }
 
/**
 * Можно ли показывать элемент?
 *
 * @return bool
 */
 public function is_showable()
 {
 	if($this->arr["visibility"] == "public") return true;
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	if($this->arr["visibility"] == "protected" && $this->auth()->respects( ws_self::id(), "blogs" )) return true;
 	return false;
 }
	}
?>