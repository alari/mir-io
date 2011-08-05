<?php class ws_comm_pub_categ extends mr_abstract_change {

	const sqlTable = "mr_comm_pubs_categories";
	
	static private $objs = array();
	
/**
 * Factory project
 *
 * @param int $id
 * @param array $arr=false
 * @return ws_comm_pub_categ
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
	if(!(@self::$objs[$id] instanceof self))
		self::$objs[$id] = new self($id, $arr);
		
	return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
 	$this->id = $id;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM mr_comm_pubs_categories WHERE id=".$this->id, mr_sql::assoc);
 }
 
/**
 * Категории по сообществу и дополнительным свойствам
 *
 * @param int $comm_id
 * @param bool $display=false
 * @param bool $apply=false
 * @param bool $auth_apply=false
 * @return mr_list
 */
 static public function several($comm_id, $display=false, $apply=false, $auth_apply=false)
 {
	$r = mr_sql::qw("SELECT * FROM ".self::sqlTable." WHERE comm_id=?".($display?" AND display='yes'":"").($apply?" AND apply='yes'":"").($auth_apply?" AND auth_apply='yes'":""), $comm_id);
	$ids = array();
	while($f = mr_sql::fetch($r, mr_sql::assoc))
	{
		$ids[] = $f["id"];
		self::factory($f["id"], $f);
	}
	return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Произведения по категории
 *
 * @param string $type
 * @param string $order
 * @param int $offset
 * @param int $limit
 * @param int &$calcResult
 * @param bool $loadExternal
 * @return mr_list
 */
 public function pubs($type=null, $order="p.time DESC", $offset=0, $limit=100, &$calcResult=false, $loadExternal=true)
 {
 	return ws_comm_pub_anchor::pubs($this->id, $this->comm()->id(), $type, $order, $offset, $limit, $calcResult, $loadExternal);
 }
 
 
/**
 * Сообщество, владеющее категорией
 *
 * @return ws_comm
 */
 public function comm()
 {
 	return ws_comm::factory( $this->arr["comm_id"] );
 }
 
/**
 * Ссылка на категорию
 *
 * @param string[opt] $class
 * @return string
 */
 public function link($class=null)
 {
 	return "<a href=\"".$this->href()."\"".($class?" class=\"$class\"":"")." title=\"".htmlspecialchars($this->arr["description"])."\">".$this->arr["title"]."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Адрес категории, при необходимости -- со вставками
 *
 * @param string[opt] $add
 * @return string
 */
 public function href($add="")
 {
 	return $this->comm()->href("pubs-".$this->id.$add.".xml");
 }
	
}?>