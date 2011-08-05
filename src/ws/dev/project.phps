<?php class ws_dev_project extends mr_abstract_change {
	
	const sqlTable = "mr_dev_projects";
	
	static private $objs = array();
		
/**
 * Загрузка проекта
 *
 * @param int|array $id
 * @return ws_dev_project
 */
 static public function factory($id)
 {
  if(is_array($id))
  {
  	$arr = $id;
  	$id = $arr["id"];
  }
 	
  if(!(@self::$objs[$id] instanceof self))
  	self::$objs[$id] = new self($id, isset($arr)?$arr:null);
  return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);	
 }
 
 public function is_showable()
 {
 	return true;
 }
 
/**
 * Создаёт новый проект
 *
 * @param string $title
 * @return ws_dev_project
 */
 static public function create($title)
 { 
  mr_sql::qw("INSERT INTO ".self::sqlTable."(title, user_id, time)
  VALUES(?, ?, UNIX_TIMESTAMP())",
  	$title, ws_self::id());
  	
  return self::factory(mr_sql::insert_id());
 }
	
/**
 * Ссылка на рекомендацию
 *
 * @param string $class=null
 * @return string
 */
 public function link($class=null)
 {
  return "<a href=\"".$this->href()."\">$this->title</a>";
 }
 public function __toString()
 {
  return $this->link();
 }
 
 /**
  * Ссылка на страничку тикета
  *
  * @return string
  */
 public function href($closed=false)
 {
 	return mr::host("dev")."/project-".$this->id.($closed?".closed-show":"").".xml";
 }
 
 /**
  * List of all open projects
  *
  * @return mr_list
  */
 static public function getAll()
 {
 	$q = new mr_sql_query(self::sqlTable);
 	return $q->fetch(__CLASS__);
 }
 
 /**
  * List of all modules inside project
  *
  * @param bool $opened
  * @return mr_list
  */
 public function getModules($opened=true)
 {
 	return ws_dev_ticket::getModules($this->id, $opened);
 }
 
 public function getTickets($opened=true)
 {
 	return ws_dev_ticket::getAll($this->id, $opened);
 }
	
	}?>