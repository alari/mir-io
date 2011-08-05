<?php
/**
 * Класс дискуссионных разделов
 *
 */
	class ws_comm_disc extends mr_abstract_change  {
		
		const sqlTable = "mr_discussions";
		
		static protected $objs = array(), $byComm = array();

/**
 * Проект factory
 *
 * @param int $id Идентификатор элемента
 * @param array[opt] $arr Ассоциативный массив инфы о элементе
 * @return ws_comm_disc
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
 	if(!isset(self::$objs[$id]) || !self::$objs[$id] instanceof self)
 		self::$objs[$id] = new self($id, $arr);
 	return self::$objs[$id];
 }
 protected function __construct($id, $arr=false)
 {
	$this->id = $id;
 	if(is_array($arr))
 		$this->arr = $arr;
 	else
 		$this->arr = mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);
 
 	if(!is_array($this->arr)) return false;
 }
 
/**
 * Возвращает дискуссионные разделы по комьюнити
 *
 * @param int $comm_id
 * @return mr_list
 */
 static public function byComm($comm_id=0, $strong=true, $display=false)
 {
 	static $whole_loaded = false;
 	if(!$comm_id)
 	{
 		if($whole_loaded) return self::$byComm;
 		$discs = self::several("1=1".($display?" AND display='yes'":"").($strong?" AND strong='yes'":""));
 		foreach( $discs as $d )
 		{
 			if( !isset(self::$byComm[$d->comm()->id()]) || !self::$byComm[$d->comm()->id()] instanceof mr_list )
 				self::$byComm[$d->comm()->id()] = new mr_list(__CLASS__, array());
 			self::$byComm[$d->comm()->id()] [] = $d;
 		}
 		$whole_loaded = true;
 		return self::$byComm;
 	} else {
 		 if( !(@self::$byComm[$comm_id] instanceof mr_list) )
 			self::$byComm[$comm_id] = self::several("comm_id=".$comm_id.($display?" AND display='yes'":"").($strong?" AND strong='yes'":""));
 		return self::$byComm[$comm_id];
 	}
 }
 
/**
 * Позволяет загружать много разделов
 *
 * @param string|array $cond="1=1" Строка -- where, массив -- список id.
 * @param int[opt] $limit Сколько штук
 * @param int[opt] $offset Смещение в результатах
 * @param string[opt] $order="time DESC" Ключ для сортировки
 * @return mr_list
 */
 static public function several($where="1=1", $limit=0, $offset=0, $order="id DESC", &$calcResult=false)
 {
	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	$r = mr_sql::qw("SELECT ".($calcResult!==false?"SQL_CALC_FOUND_ROWS ":"")."* FROM ".self::sqlTable." WHERE $where".($order?" ORDER BY $order":"").($limit?" LIMIT ".($offset?"$offset, ":"").$limit:""));

 	if($calcResult !== false) $calcResult = mr_sql::found_rows();

 	$ids = array();
 	$comms = array();
 	
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		if(!self::factory($f["id"], $f)->is_showable()) continue;
 		$ids[] = $f["id"];
 		if(!in_array($f["comm_id"], $comms)) $comms[] = $f["comm_id"];
 	}
 	
 	ws_comm::several($comms);
 	
 	return new mr_list(__CLASS__, $ids);
 }
   
/**
 * Создать объект дискуссии (нулёвый)
 *
 * @param int $comm_id
 * @param ppp $visibility
 * @return ws_comm_disc
 */
 static public function create($comm_id, $visibility, $title)
 {
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(comm_id, visibility, title, description) VALUES(?, ?, ?, '')", $comm_id, $visibility, $title);
 	return self::factory(mr_sql::insert_id());
 }
 
/**
 * Удалить текущее событие со всеми детьми
 *
 */
 public function delete()
 {
 	$this->getThreads()->delete();
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=?", $this->id);
 	unset($this);
 }
 
/**
 * Возвращает ветки как объекты
 *
 * @param $page=0 Страница
 * @param $perpage=20 На страницу
 * @return mr_list
 */
 public function getThreads($page=0, $perpage=20, &$calcResult=false)
 {
 	return ws_comm_disc_thread::several("disc_id=".$this->id, $perpage, $perpage*$page, "last_time DESC", $calcResult);
 }
 
/**
 * Сообщество, владеющее дискуссионным разделом
 *
 * @return ws_comm
 */
 public function comm()
 {
 	return ws_comm::factory($this->arr["comm_id"]);
 }
 
/**
 * Ссылка на дискуссионный раздел
 *
 * @param int[opt] $page
 * @param string[opt] $class
 * @return string
 */
 public function link($page=0, $class="")
 {
 	return "<a href=\"".$this->href($page)."\"".($class?' class="'.$class.'"':"").($this->arr["description"]?' title="'.htmlspecialchars($this->arr["description"]).'"':"").">".$this->arr["title"]."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Адрес от корня, с номером страницы, если надо
 *
 * @param int[opt] $page
 * @return string
 */
 public function href($page=0)
 {
 	return $this->comm()->href("disc-{$this->id}".($page?".page-".$page:"").".xml");
 }
  
/**
 * Видим ли раздел дискуссий
 *
 * @return bool
 */
 public function is_showable()
 {
 	switch($this->arr["visibility"])
 	{
 		case "public": return true;
 		case "protected": return ws_self::is_member($this->comm()->id());
 		case "private": return ws_self::is_member($this->comm()->id(), ws_comm::st_curator);
 		case "disable": return false;
 		default: return true;
 	}
 }
 
 public function can_add_thread()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	switch($this->arr["apply"])
 	{
 		case "public": return ws_self::ok();
 		case "protected": return ws_self::is_member($this->comm()->id());
 		case "private": return ws_self::is_member($this->comm()->id(), ws_comm::st_curator);
 		case "disable": return false;
 		default: return true;
 	}
 }
	}
?>