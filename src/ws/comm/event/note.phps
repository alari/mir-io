<?php
	class ws_comm_event_note extends ws_abstract_comment implements i_comment_reminder {
  
		const sqlTable = "mr_comm_events_notes";

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
 * @return ws_comm_event_note
 */
 static public function factory($id, $arr=false)
 {
 	return parent::sub_factory(__CLASS__, $id, $arr);
 }
		
/**
 * Создаёт новое сообщение, которое потом нужно обработать!
 *
 * @param int $event_id Родительское событие
 * @param int $user_id Автор комментария
 * @param xmltext $content xml-текст контента
 * @return ws_comm_event_note
 */
 static public function create($event_id, $user_id, $content)
 {
 	$t = new mr_text_trans($content);
 	$t->t2x(mr_text_trans::plain);
 	$cont = $t->finite();
  mr_sql::qw("INSERT INTO ".self::sqlTable."(event_id, user_id, content, size, time) VALUES(?, ?, ?, ?, UNIX_TIMESTAMP())",
  	$event_id, $user_id?$user_id:ws_user::anonime, $cont, $t->getAuthorSize());
  	ws_attach::checkXML($cont, ws_attach::increment);
  	return self::factory(mr_sql::insert_id());
 }
     
/**
 * Родительское событие
 *
 * @return ws_comm_event_item
 */
 public function event_item()
 {
  return ws_comm_event_item::factory($this->arr["event_id"]);
 }
 
 /**
  * Анонс родительского события
  *
  * @return ws_comm_event_anonce
  */
 public function event_anonce()
 {
  return ws_comm_event_anonce::factory($this->arr["event_id"]);
 }
 
 public function notify_subscribers()
 {
 	$eml_title = "Новый отзыв на событие: ".$this->event_anonce()->title.", Лента: ".$this->event_anonce()->title;
 	
 	$u = ws_user::factory($this->arr["user_id"]);
 	
 	$eml_body = mr_text_trans::node2text($this->arr["content"])."
 	
 	Событие: ".$this->event_anonce()->href()."
 	Сообщение оставил: ".$u->name()." (".$u->href().")
 	
 Вы получили это сообщение потому, что оформили подписку на новые отзывы на данное событие. Чтобы отписаться, снимите галочку внизу странички события.";
 	
 	ws_user_remind::event(ws_user_remind::type_comm_event_resp, $this->arr["event_id"], $eml_title, $eml_body, $u->id(), $this->arr["time"]);
 }
 
 /**
 * Проверяет права пользователя и выводит, может ли сообщение быть показано
 *
 */
 public function is_showable()
 {
 	if($this->arr["hidden"] == "no" || $this->arr["user_id"]==ws_self::id()) return true;
 	if(ws_self::is_allowed("see_hidden", $this->event_anonce()->comm_id)) return true;
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
 	if(ws_self::is_allowed("to_delete_notes", $this->event_anonce()->comm_id)) return true;
 	if(ws_self::is_allowed("to_hide", $this->event_anonce()->comm_id)) return true;
 	return false;
 }
 
 public function can_delete()
 {
 	if(ws_self::is_allowed("to_delete_notes", $this->event_anonce()->comm_id)) return true;
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	return false;
 }
 
 public function parent_link()
 {
 	return "Отзыв на событие: ".$this->event_anonce()->link().", ".$this->event_anonce()->comm()->link();
 }
 
/**
 * Для ремайндера
 *
 * @param int $parent
 * @return int
 */
 static public function reminder( $parent )
 {
 	return ws_user_remind::type_comm_event_resp;
 }
	}
?>