<?php class ws_chat_msg extends mr_abstract_change {

	const sqlTable = "mr_chat";
	static protected $objs = array();
	
/**
 * Страница сообщений
 *
 * @param int $page
 * @param int $perpage
 * @return mr_list
 */
 static public function several($page, $perpage)
 {
 	$r = mr_sql::qw("SELECT * FROM ".self::sqlTable." ORDER BY id DESC LIMIT ?, ?", $page*$perpage, $perpage);
 	$ids = array();
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		$ids[] = $f["id"];
 		self::factory($f["id"], $f);
 	}
 	return new mr_list(__CLASS__, $ids);
 }
	
/**
 * Factory project
 *
 * @param int $id
 * @param array[opt] $arr
 * @return ws_chat_msg
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
 	if(!(@self::$objs[$id] instanceof self))
 		self::$objs[$id] = new self($id, $arr);
 	return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
 	$this->id = $id;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE id=? LIMIT 1", $id), mr_sql::assoc);
 }
 
/**
 * Автор записи
 *
 * @return ws_user
 */
 public function user()
 {
 	return ws_user::factory( $this->arr["user_id"] );
 }
 
/**
 * Удаление записи
 *
 */
 public function delete()
 {
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $this->id);
 	$this->id = null;
 	$this->arr = null;
 	unset($this);
 }
 
/**
 * Можно ли текущему пользователю удалять
 * 
 * @return bool
 */
 public function can_delete()
 {
 	return ws_self::is_meta(1);
 }
 
/**
 * Можно ли текущему пользователю добавлять
 *
 * @return bool
 */
 static public function can_add()
 {
 	return ws_self::is_allowed("chat");
 }
 
/**
 * Добавление нового сообщения чата. Возвращает айди нового
 *
 * @param string $msg
 * @param int $user_id
 * @return int
 */
 static public function add($msg, $user_id=0)
 {
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(user_id, msg, time) VALUES(?, ?, UNIX_TIMESTAMP())",
 		$user_id?$user_id:ws_self::id(), $msg);
 	return mr_sql::insert_id();
 }
 
/**
 * Обезопашенная строка сообщения
 *
 * @return string
 */
 public function __toString()
 {
 	return nl2br(htmlspecialchars(mr_text_string::word_wrap( trim($this->arr["msg"]), 50, "\n", true )));
 }
	}
?>