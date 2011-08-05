<?php
/**
 * Класс для детального рассмотрения произведения, для манипуляции над ним.
 *
 */
	class ws_libro_pub_item extends mr_abstract_change2 {
 
 const sqlTable = "mr_publications";
 const sqlTableMerge = "mr_pub_contents";
		
 static protected $objs = array();
 
 private $responses;

/**
 * Factory project
 *
 * @param int $id
 * @param array[opt] $arr
 * @return ws_libro_pub_item
 */
 static public function factory($id, $arr=false)
 {
 	if(!(@self::$objs[$id] instanceof self))
 		self::$objs[$id] = new self($id, $arr);
 		
 	return self::$objs[$id];
 }
 private function __construct($id, $arr=false)
 {
 	$this->id = (int)$id;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);
 	if($this->arr["anonymous"]=="yes" && $this->arr["time"] < time() - 8640000)
 	{
 		$this->__set("anonymous", "no");
 		$this->save();
 	}
 }
 
/**
 * Создаёт новое произведение, с которым ещё нужно манипулировать
 *
 * @param ws_libro_pub_draft $draft
 * @return ws_libro_pub_item
 */
 static public function create(ws_libro_pub_draft $draft, $anonymous, $meta, $authmark, $auto_contest, $section)
 {
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(author, size, type, title, time, anonymous, meta, authmark, auto_contest, section)
 		VALUES(?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?, ?, ?, ?)",
 		$draft->owner()->id(), $draft->size, $draft->type, $draft->title, $anonymous, $meta, $authmark, $auto_contest, $section);
 		
 	$id = mr_sql::insert_id();
 	if(!$id) return false;
 	
 	mr_sql::qw("INSERT INTO ".self::sqlTableMerge."(id, epygraph, content, postscriptum, first_pub, write_time, write_place)
 		VALUES(?, ?, ?, ?, ?, ?, ?)",
 		$id, $draft->epygraph, $draft->content, $draft->postscriptum, $draft->first_pub, $draft->write_time, $draft->write_place);
 		
 	if(!mr_sql::affected_rows())
 	{
 		mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $id);
 		return false;
 	}
 	
 	$draft->delete();
 	
 	return self::factory($id);
 }
 
/**
 * Возвращает анонс на произведение
 *
 * @return ws_libro_pub
 */
 public function pub()
 {
 	return ws_libro_pub::factory($this->id, $this->arr);
 }
 
/**
 * Может ли пользователь голосовать за это произведение
 *
 * @return bool
 */
 public function can_vote()
 {
 	if( !ws_self::ok() || ws_self::id()==$this->arr["author"] )
 		return false;
 	if( !ws_self::is_allowed("clubvote") && $this->currentStat()->vote != 0 )
 		return false;
 	return true;
 }
 
/**
 * Может ли пользователь оставить отзыв
 *
 * @return bool
 */
 public function can_resp()
 {
 	static $can;
 	
 	if(isset($can)) return $can;
 	
 	if(!ws_self::ok()) return false;
 	
 	if($this->__get("author")==ws_self::id()) return $can=true;
 	
 	if($this->__get("discuss") == "disable"
  	 ||($this->__get("discuss") == "protected" && !ws_self::is_member($this->__get("meta")))
 	 ||($this->__get("discuss") == "private" && !mr_sql::fetch(array("SELECT COUNT(owner) FROM mr_user_circle WHERE owner=? AND target=?", $this->__get("author"), ws_self::id()), mr_sql::get))
 	 ) $can = false;
 	elseif(ws_self::ok() && !ws_self::is_allowed("responses", $this->__get("meta"))) $can = false;
 	elseif($this->pub()->author(true)->ignores( ws_self::id() )) $can = false;
 	elseif(!$this->pub()->is_showable()) $can = false;
 	else $can = true;
 	
 	return $can;
 }
 
/**
 * Все отзывы на произведение
 *
 * @return mr_list
 */
 public function getNotes()
 {
 	if(!($this->responses instanceof mr_list))
 	{
 		$this->responses = ws_libro_pub_resp::several("pub_id=".$this->id, 0, 0, "time");
 		ws_comm_pub_anchor::byPub($this->id);
 	}
 	return $this->responses;
 }
 
/**
 * Возвращает объект цикла произведения
 *
 * @return ws_libro_pub_cycle
 */
 public function cycle()
 {
 	return ws_libro_pub_cycle::factory($this->arr["cycle"]);
 }
 
/**
 * Устанавливает новый цикл, делает всё, что нужно в этой связи
 * Необходимо после сделать save!
 *
 * @param ws_libro_pub_cycle $new
 */
 public function setCycle(ws_libro_pub_cycle $new)
 {
 	if($new->id() != $this->arr["cycle"] && $this->arr["author"] == $new->user()->id())
 	{
 	 $this->cycle()->removePub($this);
 	 $new->addPub($this);
 	}
 }
 
/**
 * Удаляет произведение
 *
 */
 public function delete()
 {
 	// Отзывы
 	$this->getNotes()->delete();
 	
 	// Статистика
 	ws_libro_pub_stat::byPub($this->id())->delete();
 	
 	// Рекомендации
 	$this->pub()->advices()->delete();
 	
 	// Из сообществ
 	$this->pub()->comm_anchors()->delete();
 	
 	// Из цикла
 	$this->cycle()->removePub($this);
 	
 	// Из ЁКЛМН
 	
 	// Вложения
 	ws_attach::checkXML($this->__get("content").$this->__get("epygraph").$this->__get("postscriptum"), ws_attach::decrement);
 	
 	// Строчка
 	mr_sql::query("DELETE FROM mr_publications WHERE id=".$this->id);
 	mr_sql::query("DELETE FROM mr_pub_contents WHERE id=".$this->id);
 }
 
/**
 * Обновляет весь xml-текст произведения
 *
 * @param string $content
 * @param string $epygraph
 * @param string $postscriptum
 */
 public function setContent($content, $epygraph, $postscriptum)
 {
 	$c = new mr_text_trans($content);
 	$e = new mr_text_trans($epygraph);
 	$p = new mr_text_trans($postscriptum);
 	
 	$c->t2x($this->__get("type")=="stihi"?mr_text_trans::stihi:mr_text_trans::prose);
 	$e->t2x(mr_text_trans::plain);
 	$p->t2x(mr_text_trans::plain);
 	
 	ws_attach::checkXML($this->__get("content").$this->__get("epygraph").$this->__get("postscriptum"), ws_attach::decrement);
 	
 	$this->__set("content", $c->finite());
 	$this->__set("epygraph", $e->finite());
 	$this->__set("postscriptum", $p->finite());
 	$this->__set("size", str_replace(",", ".", $p->getAuthorSize()+$e->getAuthorSize()+$c->getAuthorSize()));
 	
 	ws_attach::checkXML($this->__get("content").$this->__get("epygraph").$this->__get("postscriptum"), ws_attach::increment);
 }
 
/**
 * Возвращает объект текущего просмотра. Если нужно, засчитывает.
 *
 * @param bool $encount=false
 * @return ws_libro_pub_stat
 */
 public function currentStat($encount=false)
 {
 	return ws_libro_pub_stat::current($this->id, $encount);
 }
	}
?>