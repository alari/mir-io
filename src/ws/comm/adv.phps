<?php
/**
 * Класс текстовых баннеров внутренней рекламной ротации
 *
 */
	class ws_comm_adv extends mr_abstract_change  {
		
		const sqlTable = "mr_adv";
		
		static protected $objs = array();

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
 	if(!(@self::$objs[$id] instanceof self))
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
 * Позволяет загружать много объявлений
 *
 * @param string|array $cond="1=1" Строка -- where, массив -- список id.
 * @param int[opt] $limit Сколько штук
 * @param string[opt] $order="time DESC" Ключ для сортировки
 * @return mr_list
 */
 static public function several($limit=0, $comm_id=0, $order="RAND()")
 {
	$r = mr_sql::qw("SELECT * FROM ".self::sqlTable.($comm_id?" WHERE comm_id=".$comm_id:"").($order?" ORDER BY $order":"").($limit?" LIMIT ".$limit:""));

 	$ids = array();

 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		self::factory($f["id"], $f);
 		$ids[] = $f["id"];
 	}
 	
 	return new mr_list(__CLASS__, $ids);
 }
 
 /**
  * Creates new advertisement
  * 
  * @param ws_comm $comm
  * @param string $url
  * @param string $link
  * @param string $comment
  * @return ws_comm_adv 
  */
 static public function create(ws_comm $comm, $url, $link, $comment="")
 {
 	if(count(self::several(0, $comm->id)) >= $comm->adv_limit) return false;
 	if(!$url) return false;
 	if(!$link) return false;
 	
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(comm_id, url, link, comment) VALUES(?,?,?,?)",
 		$comm->id, $url, $link, $comment);
 		
 	return self::factory(mr_sql::insert_id());
 }
 
   
/**
 * Удалить текущее событие со всеми детьми
 *
 */
 public function delete()
 {
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=?", $this->id);
 	unset($this);
 }
 
/**
 * Сообщество, владеющее объявлением
 *
 * @return ws_comm
 */
 public function comm()
 {
 	return ws_comm::factory($this->arr["comm_id"]);
 }
 
/**
 * Ссылка 
 *
 * @param string[opt] $class
 * @return string
 */
 public function link($class="")
 {
 	return "<a href=\"".$this->arr["url"]."\"".($class?' class="'.$class.'"':"").">".$this->arr["link"]."</a>".($this->arr["comment"]?" &ndash; ".$this->arr["comment"]:"");
 }
 public function __toString()
 {
 	return $this->link();
 }
	}
?>