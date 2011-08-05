<?php
/**
 * Класс элементов-событий с основной контрольной функциональностью
 *
 */
	class ws_blog_item extends ws_blog_anonce {
		const fields="*";

/**
 * Проект factory
 *
 * @param int $id Идентификатор элемента
 * @param array[opt] $arr Ассоциативный массив инфы о элементе
 * @param int[opt] $resps Количество отзывов на элемент
 * @return ws_blog_item
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
 * Видимые записи в блоге
 *
 * @param int $author
 * @param int $limit
 * @param int $offset
 * @param int $calcResult
 * @return mr_list
 */
 static public function several_visible($author=0, $limit=20, $offset=0, &$calcResult=false)
 {
 	$query = "SELECT ";
 	if($calcResult!==false) $query.="SQL_CALC_FOUND_ROWS ";
 	
 	if(!ws_self::ok())
 		$query .= "* FROM ".self::sqlTable." WHERE visibility='public'".($author?" AND user_id=".$author:"")." ORDER BY time DESC";
 	else
 		$query .= "t.*
		FROM mr_user_blog_threads t
		WHERE
			(t.visibility='public'
				OR
			(t.visibility!='private' AND EXISTS (SELECT bc.id FROM mr_user_circle bc WHERE bc.owner=t.user_id AND bc.target=".ws_self::id()." AND bc.trust_blogs='yes'))
				OR
			t.user_id=".ws_self::id().")
			".($author?" AND t.user_id=".$author:"")."
		ORDER BY t.time DESC";
 		
 	if($limit>0) $query .= " LIMIT ".$offset.", ".$limit;
 	
 	return self::sub_query($query, $calcResult, __CLASS__, true);
 }
 
/**
 * Видимые записи в блоге -- по закладке
 *
 * @param int $bm_id
 * @param int $limit
 * @param int $offset
 * @param int $calcResult
 * @return mr_list
 */
 static public function several_bm($bm_id, $limit=20, $offset=0, &$calcResult=false)
 {
 	if(!$bm_id) return false;
	$query = "SELECT ";
 	if($calcResult!==false) $query.="SQL_CALC_FOUND_ROWS ";
 	
 	$query .= "t.* FROM mr_user_blog_threads t RIGHT JOIN mr_user_blog_bm_anchors a ON a.thread_id=t.id AND a.bm_id=".$bm_id." WHERE ";
 	
 	if(!ws_self::ok())
 		$query .= "t.visibility='public'";
 	else
 		$query .= "
			(t.visibility='public'
				OR
			(t.visibility!='private' AND EXISTS (SELECT bc.id FROM mr_user_circle bc WHERE bc.owner=t.user_id AND bc.target=".ws_self::id()." AND bc.trust_blogs='yes'))
				OR
			t.user_id=".ws_self::id().")";
 		
 	$query .= " ORDER BY t.time DESC";
 		
 	if($limit>0) $query .= " LIMIT ".$offset.", ".$limit;
 	
 	return self::sub_query($query, $calcResult, __CLASS__, true);
 }
 
/**
 * Видимые записи в блоге -- Круг Чтения
 *
 * @param int $owner
 * @param int $limit
 * @param int $offset
 * @param int $calcResult
 * @return mr_list
 */
 static public function several_circle($owner, $limit=20, $offset=0, &$calcResult=false, $followed=true)
 {
 	$owner = (int)$owner;
 	if(!$owner) return null;
 	$query = "SELECT ";
 	if($calcResult!==false) $query.="SQL_CALC_FOUND_ROWS ";
 	
 	if(!ws_self::ok())
 		return null;
 	else
 		$query .= "t.*
		FROM mr_user_blog_threads t
		WHERE
			 EXISTS (SELECT bc.id FROM mr_user_circle bc WHERE bc.owner=$owner".($followed?" AND bc.follow_blogs='yes'":"")." AND bc.target=t.user_id)
			 	AND
			(t.visibility='public'
				OR
			(t.visibility!='private' AND EXISTS (SELECT bc.id FROM mr_user_circle bc WHERE bc.owner=t.user_id AND bc.target=".ws_self::id()." AND bc.trust_blogs='yes'))
				OR
			t.user_id=".ws_self::id().")			
		ORDER BY t.time DESC";
 		
 	if($limit>0) $query .= " LIMIT ".$offset.", ".$limit;
 	
 	return self::sub_query($query, $calcResult, __CLASS__, true);
 }
 
/**
 * Может ли текущий юзер редактировать запись?
 *
 * @return bool
 */
 public function is_editable()
 {
 	if(ws_self::id() == $this->arr["user_id"]) return true;
 	if(ws_self::is_allowed("edit_blog")) return true;
 	return false;
 }
  
/**
 * Создать объект записи блога
 *
 * @param int $user_id
 * @param string $title
 * @param ppp $visibility
 * @param yes|hidden|no $responses
 * @param plaintext $content
 * @return ws_blog_item
 */
 static public function create($user_id, $title, $visibility, $responses, $content)
 {
 	$t = new mr_text_trans($content);
 	$t->t2x(mr_text_trans::plain);
 	$cont = $t->finite();
 	mr_sql::qw("INSERT INTO ".parent::sqlTable."(user_id, title, visibility, responses, content, size, time)
 		VALUES(?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())", $user_id, $title, $visibility, $responses, $cont, $t->getAuthorSize());
 	$newid = mr_sql::insert_id();
 	if($newid) ws_attach::checkXML($cont, ws_attach::increment);
 	return self::factory($newid);
 }
 
/**
 * Удалить текущее событие со всеми детьми
 *
 */
 public function delete()
 {
 	$this->getNotes()->delete();
 	ws_attach::checkXML($this->arr["content"], ws_attach::decrement);
 	mr_sql::qw("DELETE FROM ".parent::sqlTable." WHERE id=?", $this->id);
 	$this->bms()->delete();
 	unset($this);
 }
 
/**
 * Добавляет отзыв на запись блога. Возвращает объект отзыва, чтобы, например, дёрнуть оповещения
 *
 * @param int $user_id
 * @param string $message
 * @return ws_blog_note
 */
 public function addNote($user_id, $message)
 {
 	return ws_blog_note::create($this->id, $user_id, $message, $this->arr["responses"]=="hidden");
 }
 
/**
 * Возвращает все отзывы на событие как объекты
 *
 * @return mr_list
 */
 public function getNotes()
 {
 	return ws_blog_note::several("thread_id=".$this->id, 0, 0, "time");
 }
 
/**
 * Якоря к закладкам
 *
 * @return mr_list
 */
 public function bms()
 {
 	return ws_blog_anchor::byItem($this->id);
 }
 
/**
 * Создаёт якорь для закладки, если его не существует
 *
 * @param ws_blog_bm $bm
 */
 public function addBM(ws_blog_bm $bm)
 {
 	foreach($this->bms() as $a) if($a->bm()->id() == $bm->id())
 		return;
 	$a = ws_blog_anchor::create( $this->id, $bm->id(), $this->auth()->id() );
 }
 
/**
 * Удаляет существующую закладку
 *
 * @param ws_blog_bm $bm
 * @return unknown
 */
 public function removeBM(ws_blog_bm $bm)
 {
 	foreach($this->bms() as $a) if($a->bm()->id() == $bm->id())
 		return $a->delete();
 }
 
/**
 * Можно ли удалять
 *
 * @return bool
 */
 public function can_delete()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	return ws_self::id()==$this->auth()->id();
 }
 
/**
 * Можно ли добавить коммент
 *
 * @return bool
 */
 public function can_add_note()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	// HARDCODE
 	if(!ws_self::ok() && $this->auth()->set_blog_resps == "public") return true;
 	if($this->arr["responses"] != "no" && !$this->auth()->ignores(ws_self::id())) return true;
 	return false;
 }
 
/**
 * Можно ли править текст
 *
 * @return bool
 */
 public function can_edit()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	return ws_self::id()==$this->auth()->id();
 }
	}
?>