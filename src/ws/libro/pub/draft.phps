<?php class ws_libro_pub_draft extends mr_abstract_change {
	
	const sqlTable = "mr_drafts";
	const fields = "id, user_id, title, type, size, time";
	
	static protected $objs = array();
	
/**
 * Загружает черновик целиком, тогда как several загружает анонсы
 *
 * @param int $id
 * @param array $arr
 * @return ws_libro_pub_draft
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
 }
 
/**
 * Делает выборку черновиков для вывода их на страничку
 *
 * @param int $user_id
 * @param string[opt] $type
 * @param string[opt] $order
 * @param int[opt] $offset
 * @param int[opt] $limit
 * @param int[opt] $count
 * @return mr_list
 */
 static public function several($user_id, $type=false, $order="time DESC", $offset=0, $limit=0, &$count=false)
 { 	
 	$where = "user_id=?";
 	if($type) $where .= " AND type=?";
 	$query = "SELECT ".($count!==false?"SQL_CALC_FOUND_ROWS ":"").self::fields." FROM ".self::sqlTable." WHERE $where".($order?" ORDER BY $order":"").($limit?" LIMIT ".($offset?"$offset, ":"").$limit:"");
 	
 	$r = mr_sql::qw($query, $user_id, $type);
 	
 	if($count!==false) $count = mr_sql::found_rows();
 	
 	$ids = array();
 	
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		$ids[] = $f["id"];
 		self::factory($f["id"], $f);
 	}
 	
 	return new mr_list(__CLASS__, $ids);
 }
	
/**
 * Создаёт новый черновик
 *
 * @param int $user_id
 * @param string $title
 * @param string $type
 * @return ws_libro_pub_draft
 */
 static public function create($user_id, $title, $type)
 {
 
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(user_id, title, type, time) VALUES(?, ?, ?, UNIX_TIMESTAMP())",
 		$user_id, $title, $type);
 	return self::factory( mr_sql::insert_id() );
 	
 }
 
/**
 * Ссылка на прочтение черновика
 *
 * @return string
 */
 public function link()
 {
  return "&laquo;<a href=\"".$this->href()."\">".$this->arr["title"]."</a>&raquo;";
 }
 public function __toString()
 {
 	return $this->link();
 }
 public function href()
 {
 	return mr::host("own")."/draft/".$this->id().".xml";
 }
 
/**
 * Владелец черновика
 * 
 * @return ws_user
 */
 public function owner()
 {
 	return ws_user::factory($this->arr["user_id"]);
 }
 
/**
 * Удаление черновика
 *
 */
 public function delete()
 {
 	// Вложения
 	ws_attach::checkXML($this->__get("content").$this->__get("epygraph").$this->__get("postscriptum"), ws_attach::decrement);
 	
 	// Строчка
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $this->id());
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
	
	}?>