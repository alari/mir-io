<?php
	class ws_comm_event_sec extends mr_abstract_change {
		static private $objs = array();
		
		const sqlTable = "mr_comm_events_sections";
	
/**
 * Секция по имени и комьюнити
 *
 * @param string $name
 * @param int $comm
 * @return ws_comm_event_sec
 */
 static public function loadByName($name, $comm)
 {
 	$f = mr_sql::fetch(array("SELECT * FROM mr_comm_events_sections WHERE name=? AND comm_id=?", $name, $comm), mr_sql::assoc);
 	return self::factory($f["id"], $f);
 }
		
/**
 * Factory project
 *
 * @param int $id
 * @param array $stat
 * @return ws_comm_event_sec
 */
 static public function factory($id, $stat=false)
 {
 	$id = (int)$id;
 	if(!isset(self::$objs[$id]))
 		self::$objs[$id] = new self($id, $stat);
 	return self::$objs[$id];
 }
 
/**
 * Несколько колонок разом
 *
 * @param string $cond="1=1"
 * @return mr_list
 */
 static public function several($cond="1=1")
 {
 	if(is_array($cond)) $cond = "id IN (".join(",", $cond).")";
 	$r = mr_sql::qw("SELECT * FROM mr_comm_events_sections WHERE ".$cond);
 	$arr = array();
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		self::factory($f["id"], $f);
 		$arr[] = $f["id"];
 	}
 		
 	return new mr_list(__CLASS__, $arr);
 }
		
 protected function __construct($id, $stat=false)
 {
 	$this->id = (int)$id;
 	if(is_array($stat))
 	{
 		$this->arr = $stat;
 		return true;
 	}
 	$this->arr = mr_sql::fetch("SELECT * FROM mr_comm_events_sections WHERE id=$this->id", mr_sql::assoc);
 	if(!is_array($this->arr)) return false;
 }
 
/**
 * Ссылка на колонку
 *
 * @param string $class=null
 * @return string
 */
 public function link($class=null)
 {
 	return "<a href=\"".$this->href()."\" ".($class?"class=\"$class\" ":"")."title=\"".htmlspecialchars($this->arr["description"])."\">".$this->arr["title"]."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Возвращает адрес колонки
 *
 * @param int $page
 * @return string
 */
 public function href($page=0)
 {
 	if($page<0) $page = 0;
 	return $this->comm()->href("-".$this->arr["name"].($page?".page-".$page:"").".ml");
 }
 
/**
 * Сообщество-владелец колонки
 *
 * @return ws_comm
 */
 public function comm()
 {
 	return ws_comm::factory($this->arr["comm_id"]);
 }
 
/**
 * События в колонке полностью
 *
 * @param int $limit
 * @param int $offset
 * @param string $order
 * @param int &$calcResult
 * @return mr_list
 */
 public function items($limit=0, $offset=0, $order="time DESC", &$calcResult=false)
 {
 	return ws_comm_event_item::several("section=".$this->id.(ws_self::is_allowed("see_hidden", $this->arr["comm_id"])?"":" AND hidden='no'"), $limit, $offset, $order, $calcResult);
 }
 
/**
 * Анонсы событий в колонке
 *
 * @param int $limit
 * @param int $offset
 * @param string $order
 * @param int &$calcResult
 * @return mr_list
 */
 public function anonces($limit=0, $offset=0, $order="time DESC", &$calcResult=false)
 {
 	return ws_comm_event_anonce::several("section=".$this->id.(ws_self::is_allowed("see_hidden", $this->arr["comm_id"])?"":" AND hidden='no'"), $limit, $offset, $order, $calcResult);
 }
 
/**
 * Удаление колонки со всем содержимым, либо перевязкой содержимого к другой колонке
 *
 * @param ws_comm_event_sec|ws_comm $target
 */
 public function delete($target=null)
 {
 	if($target instanceof ws_comm_event_sec)
 	{
 		mr_sql::qw("UPDATE ".ws_comm_event_item::sqlTable." SET section=?, comm_id=? WHERE section=?",
 			$target->id(), $target->comm()->id(), $this->id());
 		
 		mr_sql::qw("DELETE FROM mr_comm_events_sections WHERE id=? LIMIT 1", $this->id);
 		unset($this);
 	} elseif($target instanceof ws_comm) {
 		mr_sql::qw("UPDATE ".ws_comm_event_item::sqlTable." SET comm_id=? WHERE section=?",
 			$target->id(), $this->id());
 		$this->__set("comm_id", $target->id());
 		$this->save();
 	} else {
 		
	 	$this->items()->delete();
 		mr_sql::qw("DELETE FROM mr_comm_events_sections WHERE id=? LIMIT 1", $this->id);
 		unset($this);
 	}
 }
 
/**
 * Создаёт новую колонку. Не проводит проверок параметров
 *
 * @param int $comm_id
 * @param string $name
 * @param string $title
 * @return ws_comm_event_sec
 */
 static public function create($comm_id, $name, $title)
 {
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(comm_id, name, title, apply) VALUES(?, ?, ?, 'disable')",
 		$comm_id, $name, $title);
 	return self::factory( mr_sql::insert_id() );
 }
 
/**
 * Можно ли добавить сообщение текущему пользователю
 *
 * @return bool
 */
 public function can_add_item()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	if($this->arr["apply"] == "protected" && !ws_self::is_member($this->arr["comm_id"]))
 		return false;
 	if($this->arr["apply"] == "private" && ws_self::is_member($this->arr["comm_id"])<ws_comm::st_curator)
 		return false;
 	if($this->arr["apply"] == "column" && (!ws_self::is_member($this->arr["comm_id"]) || ws_self::id()!=$this->arr["owner"]))
 		return false;
 	if($this->arr["apply"] == "disable")
 		return false;
 		
 		return true;
 }
	}
?>