<?php
/**
 * Класс анонсов на события
 *
 */
	class ws_comm_event_anonce extends mr_abstract_change {
		protected $resps;
		static protected $objs = array();
		const fields = "id, name, comm_id, user_id, time, title, description, hidden, section, city";
		const sqlTable = "mr_comm_events";

/**
 * Проект factory
 *
 * @param int $id Идентификатор элемента
 * @param array[opt] $arr Ассоциативный массив инфы о элементе
 * @param int[opt] $resps Количество отзывов на элемент
 * @return ws_comm_event_anonce
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
 
/**
 * Загружает по запросу
 *
 * @param string $query
 * @param int $calcResult
 * @param ws_comm_event_anonce|ws_comm_event_item $class
 * @param bool $loadExternal
 * @return mr_list
 */
 static public function several_query($query, &$calcResult=false, $class="ws_comm_event_anonce", $loadExternal=true)
 {
	$ev = mr_sql::query($query);
 	
 	if($calcResult !== false) $calcResult = mr_sql::found_rows();
 	
 	$array = array();
 	$ids = array();
 	
 	$usrs = array();
 	$comms = array();
 	
 	$sections = array();
 	$cities = array();
 	
 	$resp_arr = array();
 	
 	while($f = mr_sql::fetch($ev, mr_sql::assoc))
 	{
 		$ids[] = $f["id"];
 		if(@self::$objs[$f["id"]]) continue;
 	
 		$array[] = $f;	
 		$resp_arr[] = $f["id"];
 		if(!in_array($f["user_id"], $usrs)) $usrs[] = $f["user_id"];
 		if(!in_array($f["comm_id"], $comms)) $comms[] = $f["comm_id"];
 		if($loadExternal)
 		{
	 		if(!in_array($f["section"], $sections)) $sections[] = $f["section"];
	 		if($f["city"] && !in_array($f["city"], $cities)) $cities[] = $f["city"];
 		}
 	}
 	
 	if(count($comms)) ws_comm::several($comms);
 	if(count($usrs)) ws_user::several($usrs);
 	if($loadExternal)
 	{
	 	if(count($sections)) ws_comm_event_sec::several($sections);
	 	if(count($cities)) ws_geo_city::several($cities);
	 	if(count($resp_arr))
	 	{
		 	$resps = mr_sql::qw("SELECT COUNT(id) AS c, event_id FROM mr_comm_events_notes WHERE event_id IN (".join(", ", $resp_arr).") GROUP BY event_id");
		 	$ra = array();
		 	while($r = mr_sql::fetch($resps, mr_sql::obj)) $ra[$r->event_id]=$r->c;
	 	}
 	}
 	
 	$ret = array();
 	
 	foreach($array as $a)
 	{
 		if(
 			!call_user_func_array(array($class, "factory"), array($a["id"], $a, (int)@$ra[ $a["id"] ]))
 			->is_showable()
 			) continue;
 		$ret[] = $a["id"];
 	}
 	
 	return new mr_list($class, $ret);
 }
 
 static protected function sub_several($class, $where, $limit, $offset, $order, &$calcResult)
 {

 	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	$q = "SELECT ".($calcResult!==false?"SQL_CALC_FOUND_ROWS ":"").constant("$class::fields")." FROM mr_comm_events WHERE $where".($order?" ORDER BY $order":"").($limit?" LIMIT ".($offset?"$offset, ":"").$limit:"");
 	return self::several_query($q, $calcResult, $class);
 }

 protected function sub_construct($class, $id, $arr, $resps)
 {
 	$this->id = $id;
 	if(is_array($arr))
 		$this->arr = $arr;
 	else
 		$this->arr = mr_sql::fetch("SELECT ".constant("$class::fields")." FROM mr_comm_events WHERE id=".$this->id, mr_sql::assoc);
 
 	if(!is_array($this->arr)) return false;
 		
 	if($resps === false)
 		$this->resps = mr_sql::fetch("SELECT COUNT(id) FROM mr_comm_events_notes WHERE event_id=".$this->id, mr_sql::get);
 	else $this->resps = $resps; 	
 }

/**
 * Возвращает количество отзывов на событие
 *
 * @return int
 */
 public function notes()
 {
 	return $this->resps;
 }
 
/**
 * Возвращает объект ленты элемента
 *
 * @return ws_comm_event_sec
 */
 public function section()
 {
 	return ws_comm_event_sec::factory($this->arr["section"]);
 }

/**
 * Возвращает объект метасообщества элемента
 *
 * @return ws_comm
 */
 public function comm()
 {
 	return ws_comm::factory($this->arr["comm_id"]);
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
 	return "<a href=\"".$this->href()."\"".($class?" class=\"$class\"":"").($this->arr["description"]?" title=\"".htmlspecialchars($this->arr["description"])."\"":"").">".($this->arr["title"]?$this->arr["title"]:"<s>без названия</s>")."</a>";
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
 	return ws_comm::factory($this->arr["comm_id"])->href(($this->arr["name"]?$this->arr["name"]:$this->id).".ml");
 }
 
/**
 * Можно ли показывать элемент?
 *
 * @return bool
 */
 public function is_showable()
 {
 	if($this->arr["hidden"] == "no") return true;
 	return ws_self::is_allowed("see_hidden", $this->arr["comm_id"]);
 }
 
/**
 * Возвращает в строчном виде xml-элемент для отображения анонса
 *
 * @return xmlstring
 */
 public function xml()
 {
 	return "<event notes=\"".(int)$this->resps."\" id=\"$this->id\" title=\"".htmlspecialchars($this->arr["title"])."\" description=\"".htmlspecialchars($this->arr["description"])."\" size=\"{$this->arr['size']}\" time=\"{$this->arr['time']}\" user-id=\"{$this->arr['user_id']}\" hidden=\"{$this->arr['hidden']}\"
 		 link=\"".$this->href()."\" comm-id=\"{$this->arr['comm_id']}\"
 		 sec-link=\"{$this->section()->href()}\" sec-title=\"".htmlspecialchars($this->section()->title)."\" sec-description=\"".htmlspecialchars($this->section()->description)."\"
 		/>";
 }
	}
?>