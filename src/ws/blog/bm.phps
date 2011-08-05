<?php
	class ws_blog_bm extends mr_abstract_change {
		static private $objs = array();
		
		const sqlTable = "mr_user_blog_bookmarks";
			
/**
 * Factory project
 *
 * @param int $id
 * @param array $arr
 * @return ws_blog_bm
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
 	if(!isset(self::$objs[$id]))
 		self::$objs[$id] = new self($id, $arr);
 	return self::$objs[$id];
 }
 
/**
 * Несколько закладок
 *
 * @param string $cond="1=1"
 * @return mr_list
 */
 static public function several($cond="1=1")
 {
 	if(is_array($cond) && !count($cond)) return;
 	if(is_array($cond)) $cond = "id IN (".join(",", $cond).")";
 	$r = mr_sql::qw("SELECT * FROM ".self::sqlTable." WHERE ".$cond);
 	$arr = array();
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		self::factory($f["id"], $f);
 		$arr[] = $f["id"];
 	}
 		
 	return new mr_list(__CLASS__, $arr);
 }
 
/**
 * Закладки по юзеру
 *
 * @param unknown_type $id
 * @return unknown
 */
 static public function byUser($id)
 {
 	return self::several("user_id=".$id);
 }
		
 protected function __construct($id, $arr=false)
 {
 	$this->id = (int)$id;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=$this->id", mr_sql::assoc);

 	if(!is_array($this->arr)) return false;
 }
 
/**
 * Ссылка на закладку
 *
 * @param string $class=null
 * @return string
 */
 public function link($class=null)
 {
 	return "<a href=\"".$this->href()."\" ".($class?"class=\"$class\" ":"")."title=\"".htmlspecialchars(@$this->arr["description"])."\">".$this->arr["title"]."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Возвращает адрес закладки
 *
 * @param int $page
 * @return string
 */
 public function href($page=0)
 {
 	if($page<0) $page = 0;
 	return $this->user()->href($this->id.($page?"-".$page:""));
 }
 
/**
 * Владелец закладки
 *
 * @return ws_user
 */
 public function user()
 {
 	return ws_user::factory( $this->arr["user_id"] );
 }
 
/**
 * Записи по закладке полностью
 *
 * @param int $limit
 * @param int $offset
 * @param string $order
 * @param int &$calcResult
 * @return mr_list
 */
 public function items($limit=20, $offset=0, &$calcResult=false)
 {
 	return ws_blog_item::several_bm($this->id, $limit, $offset, $calcResult);
 }
  
/**
 * Удаление закладки со всеми частностями
 *
 */
 public function delete()
 {
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $this->id);
 	mr_sql::qw("DELETE FROM ".ws_blog_anchor::sqlTable." WHERE bm_id=?", $this->id);
 	unset($this);
 }
 
/**
 * Проверяет, есть ли закладки. Если нет, удаляет
 *
 */
 public function check()
 {
 	if(mr_sql::fetch("SELECT COUNT(bm_id) FROM ".ws_blog_anchor::sqlTable." WHERE bm_id=".$this->id, mr_sql::get)==0)
 		$this->delete();
 }
 
/**
 * Создаёт новый элемент закладки
 *
 * @param int $user_id
 * @param string $title
 * @return ws_blog_bm
 */
 static public function create($user_id, $title)
 {
 	$f = mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE title=? AND user_id=?", $title, $user_id), mr_sql::assoc);
 	if(is_array($f))
 		return self::factory($f["id"], $f);
 	else {
 		mr_sql::qw("INSERT INTO ".self::sqlTable."(title, user_id) VALUES(?, ?)", $title, $user_id);
 		$id = mr_sql::insert_id();
 		if(!$id) return null;
 		return self::factory( $id, array("id"=>$id, "title"=>$title, "user_id"=>$user_id) );
 	}
 }
	}
?>