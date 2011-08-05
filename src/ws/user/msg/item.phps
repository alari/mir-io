<?php class ws_user_msg_item extends mr_abstract_change {
	
	static protected $objs = array();
	const sqlTable = "mr_user_msgs";
	const anonce_fields = "id, box, owner, title, time, target, readen, flagged";
	
/**
 * Factory project
 *
 * @param int $id
 * @param array[opt] $arr
 * @return ws_user_msg_item
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
  if( !(@self::$objs[$id] instanceof self) )
  	self::$objs[$id] = new self($id, $arr);
  return self::$objs[$id];
 }
 
 /**
  * Construct. Если без айди, создаёт сообщение, которому потом быть сохранённым
  *
  * @param int $id
  * @param array $arr=array("owner"=>"", "target"=>"", "box"=>"", "title"=>"", "flagged"=>"no", "readen"=>"no", "content"=>"", "size"=>"")
  */
 public function __construct($id=false, $arr=array("owner"=>"", "target"=>"", "box"=>"", "title"=>"", "flagged"=>"no", "readen"=>"no", "content"=>"", "size"=>""))
 {
  $this->id = $id;
  $this->arr = is_array($arr)?$arr:mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$id, mr_sql::assoc);
 }
 
/**
 * Выборка набора сообщений
 *
 * @param int $owner
 * @param string $box=box|flagged
 * @param int $limit=-1
 * @param int $offset=0
 * @param bool $anonces=false
 * @param int $count=false
 * @return mr_list
 */
 static public function several($owner, $box, $limit=-1, $offset=0, $anonces=false, &$count=false)
 {
  $r = mr_sql::qw("SELECT ".($count===false?"":"SQL_CALC_FOUND_ROWS ").($anonces?self::anonce_fields:"*")." FROM ".self::sqlTable." WHERE owner=? AND ".($box=="flagged"?"flagged=?":"box=?")." ORDER BY time DESC LIMIT $offset, $limit", $owner, $box=="flagged"?"yes":$box);
  if($count!==false) $count = mr_sql::found_rows();
  $usrs = array();
  $ids = array();
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["id"];
  	self::factory($f["id"], $f);
  	if(!in_array($f["target"], $usrs)) $usrs[] = $f["target"];
  }
  ws_user::several( $usrs );
  return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Загружает сообщения по массиву идентификаторов
 *
 * @param int $owner
 * @param array $ids
 * @return mr_list
 */
 static public function several_ids($owner, array $ids)
 {
 	if(!count($ids)) return;
  $r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE owner=".$owner." AND id IN (".join(",", $ids).")");
  while($f = mr_sql::fetch($r, mr_sql::assoc)) self::factory($f["id"], $f);
  
  return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Загружает сообщения по строке запроса. Сделано для поиска сообщений
 *
 * @param string $query
 * @param bool $loadExternal Загружать пользователей target
 * @param int $count
 * @return mr_list
 */
 static public function several_query($query, $loadExternal=false, &$count=false)
 {
 	$r = mr_sql::query($query);
 	if($count !== false) $count = mr_sql::found_rows();
 	
  if($loadExternal) $usrs = array();
  $ids = array();
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["id"];
  	self::factory($f["id"], $f);
  	if($loadExternal && !in_array($f["target"], $usrs)) $usrs[] = $f["target"];
  }
  if($loadExternal) ws_user::several( $usrs );
  return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Ссылка для прочтения сообщения
 *
 * @param string $class
 * @return string
 */
 public function link($class=null)
 {
 	return $this->id ? '<a href="'.$this->href().'"'.($class?" class=\"$class\"":"").'>'.$this->arr["title"].'</a>' : $this->arr["title"];
 }
 public function __toString()
 {
 	return $this->link();
 }
 
 public function href()
 {
 	return mr::host("own")."/msg/".$this->id.".xml";
 }
 
/**
 * Владелец сообщения
 *
 * @return ws_user
 */
 public function owner()
 {
 	return ws_user::factory( $this->arr["owner"] );
 }
 
/**
 * Адресат или отправитель
 *
 * @return ws_user
 */
 public function target()
 {
 	return ws_user::factory( $this->arr["target"] );
 }
 
/**
 * Более всего заточено для добавления письма и в отправленные, и во входящие
 *
 */
 public function __clone()
 {
 	$this->id = false;
 	list($this->arr["owner"], $this->arr["target"]) = array($this->arr["target"], $this->arr["owner"]);
 	$this->arr["box"] = $this->arr["box"]=="sent" ? "inbox" : "sent";
 	$this->arr["readen"] = $this->arr["readen"]=="yes" ? "no" : "yes";
 }
 
/**
 * Сохраняет изменённое или созданное сообщение
 *
 * @return int
 */
 public function save()
 {
  if($this->id)
  	return parent::save();
  else {
  	if(isset($this->arr["id"]))
  		unset($this->arr["id"]);
  	
  	foreach($$this->changed as $k => $v)
  		$this->arr[$k] = $v;
  		
  	if($this->arr["target"] == 0)
  		return false;
  		
  	$keys = array_keys($this->arr);
  	$values = array_values($this->arr);
  	
  	$params = array_merge(
  		array("INSERT INTO ".self::sqlTable."(".join(",", $keys).", time) VALUES(".str_repeat("?,", count($keys))." UNIX_TIMESTAMP())"),
  		$values
  		);
  		
  	call_user_func_array(array("mr_sql", "qw"), $params);
  	
  	$this->id = mr_sql::insert_id();
  	if($this->id)
  	{
  		self::$objs[ $this->id ] = $this;
  		return true;
  	}
  	return false;
  }
 }
 
/**
 * Удаляет сообщение из папки
 *
 * @return bool
 */
 public function delete()
 {
 	if($this->id)
 	{
 		if($this->arr["box"]=="recycled")
 		{
	 		ws_attach::checkXML( $this->arr["content"], ws_attach::decrement );
	 		mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $this->id);
	 		return mr_sql::affected_rows();
 		} else {
 			$this->__set("box", "recycled");
 			return $this->save();
 		}
 	}
 	return false;
 }
	
	}?>