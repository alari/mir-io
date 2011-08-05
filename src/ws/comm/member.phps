<?php
/**
 * Класс участников сообщества
 *
 */
	class ws_comm_member extends mr_abstract_change  {
		
		const sqlTable = "mr_comm_members";
		
		static protected $objs = array();

/**
 * Проект factory
 *
 * @param int $id Идентификатор элемента
 * @param array[opt] $arr Ассоциативный массив инфы о элементе
 * @return ws_comm_member
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
 * Участники по какому-то правилу
 *
 * @param int $comm_id 
 * @param int|null $status
 * @param yes|no|auth|null $confirmed
 * @return mr_list
 */
 static public function several($comm_id, $status=1, $confirmed="yes")
 {
	$r = mr_sql::qw("SELECT * FROM ".self::sqlTable." WHERE comm_id=".$comm_id.($status==null?"":" AND status=".$status).($confirmed!=null?" AND confirmed='$confirmed'":""));

 	$ids = array();
 	$usrs = array();

 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		self::factory($f["id"], $f);
 		$ids[] = $f["id"];
 		if(!in_array($f["user_id"], $usrs)) $usrs[] = $f["user_id"];
 	}
 	
 	ws_user::several($usrs);
 	
 	return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Все связи с сообществом
 *
 * @param int $user_id
 * @param bool $load_comms
 * @return mr_list
 */
 static public function byUser($user_id, $load_comms=false)
 {
 	$user_id = (int)$user_id;
 	$r = mr_sql::qw("SELECT * FROM ".self::sqlTable." WHERE user_id=? ORDER BY status DESC, confirmed", $user_id);
 	
 	$ids = array();
 	$comms = array();

 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		self::factory($f["id"], $f);
 		$ids[] = $f["id"];
 		if($load_comms && !in_array($f["comm_id"], $comms)) $comms[] = $f["comm_id"];
 	}
 	
 	if(count($comms)>1) ws_comm::several($comms);
 	
 	return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Загрузка одного элемента по пользователю и сообществу.
 * Пока без кэша!
 *
 * @param int $user_id
 * @param int $comm_id
 * @return ws_comm_member
 */
 static public function item($user_id, $comm_id)
 {
 	$r = mr_sql::qw("SELECT * FROM ".self::sqlTable." WHERE user_id=? AND comm_id=?", $user_id, $comm_id);
 	$f = mr_sql::fetch($r, mr_sql::assoc);
 	if(!$f) return null;
 	return self::factory($f["id"], $f);
 }
   
/**
 * Удалить текущую связь юзер-сообщество
 *
 */
 public function delete()
 {
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=?", $this->id);
 	unset($this);
 }
 
/**
 * Родительское сообщество
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
 	return ws_user::factory( $this->arr["user_id"] )->link("profile", $class);
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Объект пользователя
 *
 * @return ws_user
 */
 public function user()
 {
 	return ws_user::factory( $this->arr["user_id"] );
 }
 
/**
 * Строковое значение статуса
 *
 * @return string
 */
 public function status()
 {
 	return ws_comm::mem_status( $this->arr["status"] );
 }
 
/**
 * Создание новой связки. Не производит проверок!
 *
 * @param int $comm_id
 * @param int $user_id
 * @param yes|no|auth $confirmed
 * @param int $status
 * @return ws_comm_member
 */
 static public function create($comm_id, $user_id, $confirmed="no", $status=1)
 {
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(comm_id, user_id, confirmed, status) VALUES(?, ?, ?, ?)",
 		$comm_id, $user_id, $confirmed, $status);
 	return self::factory( mr_sql::insert_id() );
 }
	}
?>