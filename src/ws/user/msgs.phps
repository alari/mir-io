<?php class ws_user_msgs {

	private $userid, $counter;
	
	static private $objs=array();
	
 static public function factory($id)
 {
  $id = (int)$id;
    if(!$id) return null;
    
  if(!(@self::$objs[$id] instanceof self)) self::$objs[$id] = new self($id);
  
  return self::$objs[$id];
 }
	
 private function __construct($userid)
 {
 	$this->userid = $userid;
 }
 
/**
 * Количество сообщений в папке выбранного типа
 * 
 * @param string $box="inbox" Рассматриваемая Папка
 * @param string $type="new" Или "total" -- кол-во сообщений
 * @return int
 */
 public function count($box="inbox", $type="new")
 {
  if(!isset($this->counter[$box]))
  {
   $this->counter[$box] = mr_sql::fetch("SELECT COUNT(box) AS total, COUNT(IF(readen='no',1,NULL)) AS new FROM mr_user_msgs WHERE owner=".$this->userid." AND box='$box'", mr_sql::obj);
  }
  return (int)@$this->counter[$box]->$type;
 }

/**
 * Пометить прочитанным или непрочитанным
 * 
 * @param int $msgid Айди сообщения
 * @param bool $readen=true
 * 
 */
 public function mark($msgid, $readen=true)
 {
  mr_sql::query("UPDATE mr_user_msgs SET readen='".($readen?"yes":"no")."' WHERE owner=".$this->userid." AND id=$msgid LIMIT 1");
 }
 
/**
 * Отмечает письмо флажком
 *
 * @param int $msgid
 */
 public function flag($msgid)
 {
 	mr_sql::query("UPDATE mr_user_msgs SET flagged=IF(flagged='yes','no','yes') WHERE owner={$this->userid} AND id=$msgid LIMIT 1");
 }

/**
 * Добавление нового сообщения
 *
 * @param string $box
 * @param string $title
 * @param string $plainmessage
 * @param int $target
 * @param bool $readen=true
 * @return int
 */
 public function add($box, $title, $plainmessage, $target, $readen=true)
 {
  $message = new mr_text_trans($plainmessage);
  $message->t2x(mr_text_trans::plain);
 	$size = $message->getAuthorSize();
 	$title = mr_text_string::remove_excess($title);

 	ws_attach::checkXML($t = $message->finite(true), ws_attach::increment);
 	
  mr_sql::qw("INSERT INTO mr_user_msgs(owner, box, target, readen, time, title, content, size)
  				VALUES(".$this->userid.", '$box', ?, '".($readen?"yes":"no")."', UNIX_TIMESTAMP(), ?, ?, ?)",
  				$target, $title, $t, $size);
  return mr_sql::insert_id();
 }

/**
 * Замена одного узла сообщений другим (update)
 *
 * @param int $msgid
 * @param string $title
 * @param string $plainmessage
 * @param int $target
 * @param bool $readen=true
 */
 public function replace($msgid, $title, $plainmessage, $target, $readen=true)
 {
 	$message = new mr_text_trans($plainmessage);
 	$message->t2x(mr_text_trans::plain);
 	$size = $message->getAuthorSize();
 	$title = mr_text_string::remove_excess($title);
  
 	ws_attach::checkXML(mr_sql::fetch("SELECT content FROM mr_user_msgs WHERE owner=".$this->userid." AND id=$msgid LIMIT 1", mr_sql::get), ws_attach::decrement);
 	
 	$t = $message->finite(true);
 	ws_attach::checkXML($t, ws_attach::increment);
 	
  mr_sql::qw("UPDATE mr_user_msgs SET target=?, readen=?, time=UNIX_TIMESTAMP(), title=?, content=?, size=? WHERE owner=".$this->userid." AND id=$msgid LIMIT 1",
  	(int)$target, $readen?"yes":"no", $title, $t, $size);
 }

/**
 * Перемещение сообщения из одной папки в другую
 *
 * @param int $msgid
 * @param string $box_to="recycled"
 * @param bool $readen=true
 */
 public function move($msgid, $box_to="recycled", $readen=true)
 {
  mr_sql::query("UPDATE mr_user_msgs SET box='$box_to', readen='".($readen?"yes":"no")."' WHERE owner=".$this->userid." AND id=$msgid LIMIT 1");
 }

/**
 * Удаление ненужного сообщения
 * 
 * @param string $box
 * @param int $msgid
 */
 public function remove($msgid)
 {
  ws_attach::checkXML( mr_sql::fetch("SELECT content FROM mr_user_msgs WHERE owner=".$this->userid." AND id=$msgid LIMIT 1", mr_sql::get), ws_attach::decrement);
  mr_sql::query("DELETE FROM mr_user_msgs WHERE owner=".$this->userid." AND id=$msgid LIMIT 1");
  return mr_sql::affected_rows();
 }

/**
 * Очистка корзины
 */
 public function recycle()
 {
  $r = mr_sql::query("SELECT content FROM mr_user_msgs WHERE owner=".$this->userid." AND box='recycled'");
  while($t = mr_sql::fetch($r, mr_sql::get)) ws_attach::checkXML($t, ws_attach::decrement );
  mr_sql::query("DELETE FROM mr_user_msgs WHERE owner=".$this->userid." AND box='recycled'");
 }
	
	}
?>