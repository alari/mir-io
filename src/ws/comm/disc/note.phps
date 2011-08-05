<?php
	class ws_comm_disc_note extends ws_abstract_comment implements i_comment_reminder {
  
		const sqlTable = "mr_disc_notes";

 static public function several($where="1=1", $limit=0, $offset=0, $order="time DESC", &$calcResult=false)
 {
 	return parent::sub_several(__CLASS__, $where, $limit, $offset, $order, $calcResult);
 }
 
 static public function several_query($query, &$calcResult=false)
 {
 	return self::sub_several_query(__CLASS__, $query, $calcResult);
 }
 
/**
 * Factory project
 *
 * @param int $id
 * @param array $arr
 * @return ws_comm_disc_note
 */
 static public function factory($id, $arr=false)
 {
 	return parent::sub_factory(__CLASS__, $id, $arr);
 }
		
/**
 * Создаёт новое сообщение, которое потом нужно обработать!
 *
 * @param int $thread_id Ветка
 * @param int $user_id Автор комментария
 * @param xmltext $content текст контента
 * @return ws_comm_disc_note
 */
 static public function create($thread_id, $user_id, $content)
 {
 	$t = new mr_text_trans($content);
 	$t->t2x(mr_text_trans::plain);
 	$cont = $t->finite();
  mr_sql::qw("INSERT INTO ".self::sqlTable."(thread_id, user_id, content, size, time) VALUES(?, ?, ?, ?, UNIX_TIMESTAMP())",
  	$thread_id, $user_id?$user_id:ws_user::anonime, $cont, $t->getAuthorSize());
  	
  	$id = mr_sql::insert_id();
  	
  	if($id) mr_sql::qw("UPDATE mr_disc_threads SET last_time=UNIX_TIMESTAMP(), last_id=? WHERE id=? LIMIT 1", $id, $thread_id);
  	ws_attach::checkXML($cont, ws_attach::increment);
  	return self::factory($id);
 }
     
/**
 * Дискуссионная ветка
 *
 * @return ws_comm_disc_thread
 */
 public function thread()
 {
  return ws_comm_disc_thread::factory($this->arr["thread_id"]);
 }
 
 public function notify_subscribers()
 {
 	$eml_title = "Новое сообщение в дискуссии: ".$this->thread()->title;
 	
 	$u = ws_user::factory($this->arr["user_id"]);
 	
 	$eml_body = mr_text_trans::node2text($this->arr["content"])."
 	
 	Ветка: ".$this->thread()->href()."
 	Сообщение оставил: ".$u->name()." (".$u->href().")
 	
 Вы получили это сообщение потому, что оформили подписку на новые сообщения в этой ветке дискуссий. Чтобы отписаться, снимите галочку внизу странички дискуссии.";
 	
 	ws_user_remind::event(ws_user_remind::type_society_thread_notes, $this->arr["thread_id"], $eml_title, $eml_body, $u->id(), $this->arr["time"]);
 }
 
 /**
 * Проверяет права пользователя и выводит, может ли сообщение быть показано
 *
 */
 public function is_showable()
 {
 	if(!$this->thread()->is_showable()) return false;
 	if($this->arr["hidden"] == "no" || $this->arr["user_id"]==ws_self::id()) return true;
 	if(ws_self::is_allowed("see_hidden", $this->thread()->comm()->id())) return true;
 	return false;
 }
 
/**
 * Права администратора
 *
 */
 public function can_edit()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	return false;
 }
 
 public function can_hide()
 {
 	if(ws_self::is_allowed("to_delete_notes", $this->thread()->comm()->id())) return true;
 	if(ws_self::is_allowed("to_hide", $this->thread()->comm()->id())) return true;
 	return false;
 }
 
 public function can_delete()
 {
 	if(ws_self::is_allowed("to_delete_notes", $this->thread()->comm()->id())) return true;
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	return false;
 }
 
 public function parent_link()
 {
 	return "Дискуссия: ".$this->thread()->link().", ".$this->thread()->comm()->link();
 }
 
/**
 * Для ремайндера
 *
 * @param int $parent
 * @return int
 */
 static public function reminder( $parent )
 {
 	return ws_user_remind::type_society_thread_notes;
 }
	}
?>