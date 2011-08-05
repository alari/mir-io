<?php class ws_libro_pub_advice extends mr_abstract_change {

	const sqlTable = "mr_pub_advices";

	static private $objs = array(), $byPub=array();

/**
 * Загрузка рекомендации
 *
 * @param int $id
 * @param array $arr
 * @return ws_libro_pub_advice
 */
 static public function factory($id, $arr=false)
 {
  if(!(@self::$objs[$id] instanceof self))
  	self::$objs[$id] = new self($id, $arr);
  return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM mr_pub_advices WHERE id=".$this->id, mr_sql::assoc);
 }

/**
 * Все рекомендации по произведению
 *
 * @param int $id
 * @return mr_list
 */
 static public function byPub($id)
 {
  if(!isset(self::$byPub[$id]) || !self::$byPub[$id] instanceof mr_list)
  {
  	$r = mr_sql::query("SELECT * FROM mr_pub_advices WHERE pub_id=".$id);
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	self::$byPub[$id] = new mr_list(__CLASS__, $ids);
  }
  return self::$byPub[$id];
 }

/**
 * Загружает рекомендации для набора произведений
 *
 * @param array $pids
 */
 static public function loadByPubs(array $pids)
 {
  if(!count($pids)) return;
  $r = mr_sql::query("SELECT * FROM mr_pub_advices WHERE pub_id IN (".join(",", $pids).")");
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[ $f["pub_id"] ][] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	foreach($pids as $id)
  		self::$byPub[$id] = new mr_list(__CLASS__, isset($ids[$id])&&is_array($ids[$id])?$ids[$id]:array());
 }

/**
 * Список рекомендаций пользователя
 *
 * @param int $id
 * @return mr_list
 */
 static public function byUser($id)
 {
    $r = mr_sql::query("SELECT * FROM mr_pub_advices WHERE user_id=".$id." ORDER BY position");
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	return new mr_list(__CLASS__, $ids);
 }

/**
 * Создаёт новую рекомендацию
 *
 * @param int $pub_id
 * @param int $user_id
 * @param string $reason
 * @return ws_libro_pub_advice
 */
 static public function create($pub_id, $user_id, $reason)
 {
  $reason = mr_text_trans::text2xml($reason, mr_text_trans::plain);

  $pos = mr_sql::fetch("SELECT COUNT(user_id) FROM mr_pub_advices WHERE user_id=$user_id", mr_sql::get)+1;

  mr_sql::qw("INSERT INTO mr_pub_advices(pub_id, user_id, reason, time, position) VALUES(?, ?, ?, UNIX_TIMESTAMP(), ?)",
  	$pub_id, $user_id, $reason, $pos);

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
  return ws_user::factory($this->arr["user_id"])->link("advices#adv".$this->id, $class);
 }
 public function __toString()
 {
  return $this->link();
 }

/**
 * Удаление рекомендации
 *
 */
 public function delete()
 {
  mr_sql::query("DELETE FROM mr_pub_advices WHERE id=".$this->id);
  mr_sql::qw("UPDATE mr_pub_advices SET position=position-1 WHERE user_id=? AND position>?",
  	$this->arr["user_id"], $this->arr["position"]);
  unset($this);
 }

/**
 * Произведение, на которое рекомендация
 *
 * @return ws_libro_pub
 */
 public function pub()
 {
 	return ws_libro_pub::factory( $this->arr["pub_id"] );
 }

/**
 * Дана ли уже рекомендация на это произведение
 *
 * @param int $pub_id
 * @param int $user_id
 * @return int
 */
 static public function exists($pub_id, $user_id)
 {
 	return mr_sql::fetch(array("SELECT COUNT(pub_id) FROM ".self::sqlTable." WHERE pub_id=? AND owner=?", $pub_id, $user_id), mr_sql::get);
 }

 static public function can_advice(ws_libro_pub_item $pub)
 {
	if($pub->pub()->author(true)->id() == ws_self::id())
 		return false;
 	if(!ws_self::ok())
 		return false;
 	if( mr_sql::fetch(array("SELECT COUNT(pub_id) FROM ".self::sqlTable." WHERE pub_id=? AND user_id=?", $pub->id(), ws_self::id()), mr_sql::get) )
 		return false;
 	return true;
 }

	}?>