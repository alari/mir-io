<?php class ws_blog_anchor extends mr_abstract_change {

	const sqlTable = "mr_user_blog_bm_anchors";
	
	static protected $objs=array(), $byItem=array();
	
/**
 * Factory project
 *
 * @param int $id
 * @param array $arr
 * @return ws_blog_anchor
 */
 static public function factory($id, $arr=false)
 {
  if(!(@self::$objs[$id] instanceof self))
  {
  	self::$objs[$id] = new self($id, $arr);
  	if(self::$objs[$id]->resp_id)
  		self::$byResp[self::$objs[$id]->resp_id][] = &self::$objs[$id];
  }
  return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);
 }
 
/**
 * Создаёт новую связь закладки и записи
 *
 * @param int $thread_id
 * @param int $bm_id
 * @param int $user_id
 * @return ws_blog_anchor
 */
 static public function create($thread_id, $bm_id, $user_id)
 {
 	foreach (self::byItem($thread_id) as $bma) if($bma->bm()->id()==$bm_id) 
 		return $bma;
 		
  mr_sql::qw("INSERT INTO ".self::sqlTable."(thread_id, bm_id, user_id) VALUES(?, ?, ?)", $thread_id, $bm_id, $user_id);
  return self::factory(mr_sql::insert_id());
 }
 
/**
 * Все связи по элементу
 *
 * @param int $id
 * @return mr_list
 */
 static public function byItem($id)
 {
  if(!(@self::$byItem[$id] instanceof mr_list))
  {
  	$r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE thread_id=".$id);
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	self::$byItem[$id] = new mr_list(__CLASS__, $ids);
  }
  return self::$byItem[$id];
 }
 
/**
 * Загружает связи для набора записей
 *
 * @param array $thids
 */
 static public function loadByItems(array $thids)
 {
  if(!count($thids)) return;
  $r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE thread_id IN (".join(",", $thids).")");
  	$ids = array();
  	$bms = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[$f["thread_id"]][] = $f["id"];
  		self::factory($f["id"], $f);
  		if(!in_array($f["bm_id"], $bms)) $bms[] = $f["bm_id"];
  	}
  	foreach($thids as $id)
  		self::$byItem[$id] = new mr_list(__CLASS__, is_array(@$ids[$id])?$ids[$id]:array());
  		
  	if(count($bms)) ws_blog_bm::several($bms);
 }
	
/**
 * Удаляет якорь произведения
 *
 */
 public function delete()
 {
  mr_sql::query("DELETE FROM ".self::sqlTable." WHERE id=".$this->id);
  $this->bm()->check();
  unset($this);
 }
	
/**
 * Анонс записи
 *
 * @return ws_blog_anonce
 */
 public function anonce()
 {
  return ws_blog_anonce::factory($this->arr["thread_id"]);
 }
 
/**
 * Родительский элемент записи
 *
 * @return ws_blog_item
 */
 public function item()
 {
 	return ws_blog_item::factory($this->arr["thread_id"]);
 }
	
/**
 * Закладка связи
 *
 * @return ws_blog_bm
 */
 public function bm()
 {
  return ws_blog_bm::factory($this->arr["bm_id"]);
 }

/**
 * Предложивший пользователь или null
 *
 * @return ws_user
 */
 public function user()
 {
  return $this->arr["user_id"] ? ws_user::factory($this->arr["user_id"]) : null;		
 }
	 
/**
 * Возвращает ссылку для вывода связи в анонсе произведения
 *
 * @param string $class=null
 * @return string
 */
 public function link($class=null)
 {
  return $this->bm()->link($class);
 }
 public function __toString()
 {
 	return $this->link();
 }
	}?>