<?php class ws_libro_pub_cycle extends mr_abstract_change {
	
	const sqlTable = "mr_pub_cycles";
	
	static private $objs=array(), $byTitle=array(), $byOwner=array();
	
	private $pubs;

/**
 * Factory project
 *
 * @param int $id
 * @param array $arr=false
 * @return ws_libro_pub_cycle
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
	if(!(@self::$objs[$id] instanceof self))
	{
		self::$objs[$id] = new self($id, $arr);
		self::$byTitle[self::$objs[$id]->arr["owner_id"]][self::$objs[$id]->arr["title"]] =& self::$objs[$id];
	}
	return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
 	$this->id = $id;
 	$this->arr = is_array($arr) || !$id ? $arr : mr_sql::fetch("SELECT * FROM mr_pub_cycles WHERE id=".$this->id, mr_sql::assoc);
 }
 
/**
 * Цикл по владельцу и названию
 *
 * @param int $owner
 * @param string $title
 * @return ws_libro_pub_cycle
 */
 static public function byTitle($owner, $title)
 {
 	if(!(self::$byTitle[$owner][$title] instanceof self))
 	{
 		$arr = mr_sql::fetch(array("SELECT * FROM mr_pub_cycles WHERE owner_id=? AND title=?", $owner, $title), mr_sql::assoc);
 		if(!is_array($arr))
 		{
 			$arr["id"] = -mt_rand(1, 100);
 			$arr["owner_id"] = $owner;
 			$arr["title"] = $title;
 			$arr["description"] = "";
 			$arr["position"] = null;
 		}
 		self::factory($arr["id"], $arr);
 	}
 	return self::$byTitle[$owner][$title];
 }
  
/**
 * Загружает все циклы по их владельцу
 *
 * @param int $owner_id
 * @return mr_list
 */
 static public function byOwner($owner_id, $loadPubs=false, $loadExternal=false)
 {
 	if(!(@self::$byOwner[$owner_id] instanceof mr_list))
 	{
 		$r = mr_sql::qw("SELECT * FROM mr_pub_cycles WHERE owner_id=? ORDER BY position", $owner_id);
 		$ids = array();
 		while($f = mr_sql::fetch($r, mr_sql::assoc))
 		{
 			$ids[] = $f["id"];
 			self::factory($f["id"], $f);
 		}
 		self::$byOwner[$owner_id] = new mr_list(__CLASS__, $ids);
 		if($loadPubs)
 		{
 			$calc = false;
 			$ps = ws_libro_pub::several("author=".$owner_id, 0, 0, "cycle, position", $calc, $loadExternal);
 			$ids_by_cyc = array();
 			foreach($ps as $p) $ids_by_cyc[$p->cycle][] = $p->id();
 			foreach(self::$byOwner[$owner_id] as $c) $c->pubs = new mr_list("ws_libro_pub", count($ids_by_cyc[$c->id()])?$ids_by_cyc[$c->id()]:array());
 		}
 	}
 	return self::$byOwner[$owner_id];
 }

/**
 * Владелец цикла
 *
 * @return ws_user
 */
 public function user()
 {
 	return ws_user::factory($this->arr["owner_id"]);
 }

/**
 * Ссылка на страничку цикла
 *
 * @param string[opt] $class
 * @return string
 */
 public function link($class=null)
 {
 	return "<a href=\"".$this->href()."\"".($class?" class=\"$class\"":"").($this->arr["description"]?" title=\"".$this->arr["description"]."\"":"").">".$this->arr['title']."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Адрес цикла от корня
 *
 * @return string
 */
 public function href()
 {
 	return $this->user()->href("pubs-".$this->id());
 }
 
/**
 * Все произведения цикла
 *
 * @param bool $loadExternal=false Подгружать статистику произведений
 * @return mr_list
 */
 public function publist($loadExternal = false, $loadAnonymous = false)
 {
 	if(!($this->pubs instanceof mr_list))
 	{
 		$calc = false;
 		$this->pubs = ws_libro_pub::several("cycle=".$this->id.($loadAnonymous?"":" AND anonymous='no'"), 0, 0, "position", $calc, $loadExternal);
 	}
 	return $this->pubs;
 }
 
/**
 * Может ли цикл быть показан, или все произведения в нём скрыты?
 *
 * @return bool
 */
 public function is_showable()
 {
 	$ps = $this->publist();
 	foreach($ps as $p) if($p->is_showable()) return true;
 	return false;
 }
 
/**
 * Добавить произведение
 *
 * @param ws_libro_pub_item $pub
 * @return unknown
 */
 public function addPub(ws_libro_pub_item $pub)
 {
 	if($this->id < 0)
 	{
 		mr_sql::qw("INSERT INTO mr_pub_cycles(owner_id, title, position, description) VALUES(?, ?, ?, ?)",
 			$this->arr["owner_id"], $this->arr["title"], mr_sql::fetch("SELECT COUNT(owner_id)+1 FROM mr_pub_cycles WHERE owner_id=".$this->arr["owner_id"], mr_sql::get), $this->arr["description"]);
 		$this->id = mr_sql::insert_id();
 		
 		$pub->position = 1;
 	} else {
 		$pub->position = count($this->publist(false, true))+1;
 		$this->pubs = null;
 	}
 	
 	return ($pub->cycle = $this->id);
 }
 
/**
 * Перемещает произведение в себе на новое положение
 *
 * @param ws_libro_pub_item $pub
 * @param int $newIndex
 */
 public function movePub(ws_libro_pub_item $pub, $newIndex)
 {
 	if($newIndex > $pub->position)
 		mr_sql::qw("UPDATE ".ws_libro_pub_item::sqlTable." SET position=position-1 WHERE cycle=".$this->id." AND position<=".$newIndex." AND position>".$pub->position);
 	else
	 	mr_sql::qw("UPDATE ".ws_libro_pub_item::sqlTable." SET position=position+1 WHERE cycle=".$this->id." AND position>=".$newIndex." AND position<".$pub->position);
 	$pub->position = $newIndex;
 	$pub->save();
 	$this->pubs = null;
 }
 
/**
 * Убрать произведение
 *
 * @param ws_libro_pub_item $pub
 */
 public function removePub(ws_libro_pub_item $pub)
 {
 	$list = $this->publist();
 	$p_ok = false;
 	$ids = array();
 	foreach($list as $p)
 	{
 		if($p->id() == $pub->id())
 		{
 			$p_ok = 1;
 			continue;
 		}
 		if($p_ok) $ids[] = $p->id();
 	}
 	if(count($ids)) mr_sql::query("UPDATE mr_publications SET position=position-1 WHERE id IN (".join(",", $ids).")");
 	elseif(count($list) <= 1)
 	{
 		mr_sql::qw("DELETE FROM mr_pub_cycles WHERE id=".$this->id." LIMIT 1");
 		$this->id = 0;
 	}
 	$this->pubs = null;
 }
 
/**
 * Удаляет цикл, если он пустой или есть цикл для переноса
 *
 * @param int $target_cycle=null
 * @return bool
 */
 public function remove($target_cycle=null)
 {
 	if(count($this->publist()))
 	{
 		if(!$target_cycle) return false;
 		$target = self::factory($target_cycle);
 		foreach($this->pubs as $p) 
 		{
 			$target->addPub($p->item());
 			$p->item()->save();
 		}
 	}
 	mr_sql::qw("DELETE FROM mr_pub_cycles WHERE id=".$this->id." LIMIT 1");
 	return true;
 }
	
}?>