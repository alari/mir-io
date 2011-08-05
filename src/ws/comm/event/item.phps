<?php
/**
 * Класс элементов-событий с основной контрольной функциональностью
 *
 */
	class ws_comm_event_item extends ws_comm_event_anonce {
		const fields="*";

/**
 * Проект factory
 *
 * @param int $id Идентификатор элемента
 * @param array[opt] $arr Ассоциативный массив инфы о элементе
 * @param int[opt] $resps Количество отзывов на элемент
 * @return ws_comm_event_item
 */
 static public function factory($id, $arr=false, $resps=false)
 {
 	return self::sub_factory(__CLASS__, $id, $arr, $resps);
 }
 
/**
 * Позволяет загружать много анонсов одним запросом
 *
 * @param string|array $cond="1=1" Строка -- where, массив -- список id.
 * @param int[opt] $limit Сколько штук
 * @param int[opt] $offset Смещение в результатах
 * @param string[opt] $order="time DESC" Ключ для сортировки
 * @return mr_list
 */
 static public function several($where="1=1", $limit=0, $offset=0, $order="time DESC", &$calcResult=false)
 {
 	return self::sub_several(__CLASS__, $where, $limit, $offset, $order, $calcResult);
 }
 
 protected function __construct($id, $arr=false, $resps=false)
 {
	return self::sub_construct(__CLASS__, $id, $arr, $resps);
 }
		
/**
 * Объект события по имени и сообществу
 *
 * @param string $name
 * @param int $comm
 * @return ws_comm_event_item
 */
 static public function byName($name, $comm)
 {
 	$a = ws_comm_event_item::several("name='$name' AND comm_id=".$comm, 1);
 	return $a[0];
 }

/**
 * Производит изменения, сделанные через __set()
 *
 * @return int
 */
 public function save()
 {
 	if($this->changed["name"] && $this->changed["name"] != $this->arr["name"])
 	{
 		if(mr_sql::fetch(array("SELECT COUNT(*) FROM mr_comm_events WHERE comm_id=? AND name=?", $this->__get("comm_id"), $this->changed["name"]), mr_sql::get))
 			unset($this->changed["name"]);
 	}
 	return parent::save();
 }
 
 public function increment_view()
 {
 	mr_sql::qw("UPDATE mr_comm_events SET views=views+1 WHERE id=? LIMIT 1", $this->id);
 	if(ws_self::ok()) ws_user_remind::zeroize( ws_user_remind::type_comm_event_resp, $this->id );
 }
 
/**
 * Может ли текущий юзер редактировать событие?
 *
 * @return bool
 */
 public function is_editable()
 {
 	if(ws_self::id() == $this->arr["user_id"]) return true;
 	if(ws_self::is_member($this->arr["comm_id"]) >= ws_comm::st_member && $this->arr["owner"] == "public") return true;
 	if(ws_self::is_member($this->arr["comm_id"]) >= ws_comm::st_curator && $this->arr["owner"] == "protected") return true;
 	return false;
 }
  
/**
 * Создать объект события (нулёвый)
 *
 * @param int $comm_id
 * @param int $section
 * @param int $user_id
 * @param xmltext $content
 * @return ws_comm_event_item
 */
 static public function create($comm_id, $section, $user_id, $content, $title)
 {
 	$x = new mr_text_trans($content);
 	$x->t2x( mr_text_trans::prose );
 	$cont = $x->finite();
 	mr_sql::qw("INSERT INTO mr_comm_events(name, comm_id, section, user_id, content, size, title, time) VALUES('', ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())",
 		 $comm_id, $section, $user_id, $cont, $x->getAuthorSize(), $title);
 		ws_attach::checkXML($cont, ws_attach::increment);
 	return self::factory(mr_sql::insert_id());
 }
 
/**
 * Удалить текущее событие со всеми детьми
 *
 */
 public function delete()
 {
 	ws_user_remind::delete( ws_user_remind::type_comm_event_resp, $this->id );
 	$this->getNotes()->delete();
 	
 	ws_attach::checkXML($this->arr["content"], ws_attach::decrement);
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=?", $this->id);
 	
 	unset($this);
 }
 
/**
 * Добавляет отзыв на событие. Возвращает объект отзыва, чтобы, например, дёрнуть оповещения
 *
 * @param int $user_id
 * @param string $message
 * @return ws_comm_event_note
 */
 public function addNote($user_id, $message)
 {
 	return ws_comm_event_note::create($this->id, $user_id, $message);
 }
 
/**
 * Возвращает все отзывы на событие как объекты
 *
 * @return mr_list
 */
 public function getNotes()
 {
 	return ws_comm_event_note::several("event_id=".$this->id, 0, 0, "time");
 }
 
 public function xml()
 {
 	return substr(parent::xml(), 0, -2).">".$this->__get("content")."</event>";
 }
 
/**
 * Можно ли добавить отзыв
 *
 * @return bool
 */
 public function can_add_note()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	return $this->is_showable() && !$this->auth()->ignores( ws_self::id() );
 }
 
/**
 * Можно ли править текст
 *
 * @return bool
 */
 public function can_edit()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	if( ws_self::id() == $this->auth()->id() ) return true;
 	if( !ws_self::is_member( $this->comm()->id() ) ) return false;
 	if( $this->arr["owner"]=="public" && ws_self::is_member( $this->comm()->id() ) ) return true;
 	if( $this->arr["owner"]=="protected" && ws_self::is_member( $this->comm()->id(), ws_comm::st_curator ) ) return true;
 	return false;
 }
 
/**
 * Можно ли удалить запись
 *
 * @return bool
 */
 public function can_delete()
 {
 	if( ws_self::id() == $this->auth()->id() ) return true;
 	if( ws_self::is_allowed("to_delete_pubs", $this->comm()->id()) ) return true;
 	return false;
 }
 
/**
 * Можно ли прятать запись
 *
 * @return bool
 */
 public function can_ch_vis()
 {
 	if( ws_self::id() == $this->auth()->id() ) return true;
 	if( ws_self::is_allowed("to_hide", $this->comm()->id()) ) return true;
 	return false;
 }
 
/**
 * Можно ли закрывать-открывать обсуждение
 *
 * @return bool
 */
 public function can_close()
 {
 	return $this->can_ch_vis();
 }
	}
?>