<?php
	class ws_blog_note extends ws_abstract_comment implements i_comment_reminder {
  
		const sqlTable = "mr_user_blog_msgs";

 static public function several($where="1=1", $limit=0, $offset=0, $order="time", &$calcResult=false)
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
 * @param int $item_id Запись блога
 * @param int $user_id Автор комментария
 * @param xmltext $content xml-текст контента
 * @param bool $hidden Спрятан ли
 * @return ws_blog_note
 */
 static public function create($item_id, $user_id, $content, $hidden)
 {
 	$t = new mr_text_trans($content);
 	$t->t2x(mr_text_trans::plain);
 	if(!$user_id) $user_id = ws_user::anonime;
  mr_sql::qw("INSERT INTO ".self::sqlTable."(thread_id, user_id, content, size, time, hidden) VALUES(?, ?, ?, ?, UNIX_TIMESTAMP(), ?)",
  	$item_id, $user_id, $t->finite(), $t->getAuthorSize(), $hidden?"yes":"no");
  	
  	return self::factory(mr_sql::insert_id());
 }
     
/**
 * Родительское событие
 *
 * @return ws_blog_item
 */
 public function item()
 {
  return ws_blog_item::factory($this->arr["thread_id"]);
 }
 
 /**
  * Анонс родительского события
  *
  * @return ws_blog_anonce
  */
 public function anonce()
 {
  return ws_blog_anonce::factory($this->arr["thread_id"]);
 }
 
 public function notify_subscribers()
 {
 	$eml_title = "Новый отзыв на запись в дневнике: ".$this->anonce()->title.", Дневник ведёт: ".$this->anonce()->auth()->name();
 	
 	$u = ws_user::factory($this->arr["user_id"]);
 	
 	$eml_body = mr_text_trans::node2text($this->arr["content"])."
 	
 	Событие: ".$this->anonce()->href()."
 	Сообщение оставил: ".$u->name()." (".$u->href().")
 	
 Вы получили это сообщение потому, что оформили подписку на новые отзывы на данное событие. Чтобы отписаться, снимите галочку внизу странички события.";
 	
 	ws_user_remind::event(ws_user_remind::type_blog_resp, $this->arr["thread_id"], $eml_title, $eml_body, $u->id(), $this->arr["time"]);
 	
 	$auth = $this->item()->auth();
 	if($auth->id() != $this->arr["user_id"])
 	{
 		if($auth->set_responses_notify == "email")
 		{
 			@mail($auth->email, "Новый отзыв на вашу запись в дневнике \"".$this->item()->title."\" - Мир Ио", $eml_body, "From: noreply@mirari.ru
To: ".$auth->email."
Content-type: text/plain; charset=utf-8");
 		} elseif($auth->set_responses_notify == "reminder") {
 			ws_user_remind::create(ws_user_remind::type_blog_resp, $this->arr["thread_id"], $auth->id(), ws_user_remind::method_reminder, true);
 		}
 	}
 }
 
 /**
 * Проверяет права пользователя и выводит, может ли сообщение быть показано
 *
 */
 public function is_showable()
 {
 	if($this->arr["hidden"] == "no" || $this->arr["user_id"]==ws_self::id() || $this->anonce()->auth()->id()==ws_self::id()) return true;
 	return false;
 }
 
/**
 * Права администратора
 *
 */
 public function can_edit()
 {
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	return false;
 }
 
 public function can_hide()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	if(ws_self::is_allowed("to_delete_notes")) return true;
 	if(ws_self::is_allowed("to_hide")) return true;
 	if($this->anonce()->auth()->id() == ws_self::id()) return true;
 	return false;
 }
 
 public function can_delete()
 {
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	if($this->anonce()->auth()->id() == ws_self::id()) return true;
 	return false;
 }
 
/**
 * Ссылка на родителя
 *
 * @return string
 */
 public function parent_link()
 {
 	return "Комментарий на запись в блоге: ".$this->anonce()->link().", ведёт ".$this->anonce()->auth()->link();
 }
 
/**
 * Для ремайндера
 *
 * @param int $parent
 * @return int
 */
 static public function reminder( $parent )
 {
 	if(!$parent) return false;
 	$parent = ws_blog_anonce::factory($parent);
 	if(!$parent) return false;
 	if( $parent->auth()->id() != ws_self::id() )
 		return ws_user_remind::type_blog_resp;
 }
	}
?>