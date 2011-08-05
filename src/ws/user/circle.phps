<?php class ws_user_circle extends mr_abstract_change {
	
	const sqlTable = "mr_user_circle";
	
	static protected $objs = array(), $byUser = array(), $byTarget = array(), $respects = array();
	
/**
 * Проект фактори
 *
 * @param int $id
 * @param array $arr
 * @return ws_user_circle
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
 	if( !(@self::$objs[$id] instanceof self) )
 	{
 		self::$objs[$id] = new self($id, $arr);
 		self::$respects[ self::$objs[$id]->arr["owner"] ][ self::$objs[$id]->arr["target"] ] = true;
 	}
 	return self::$objs[$id];
 }
 protected function __construct($id, $arr)
 {
 	$this->id = $id;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id." LIMIT 1", mr_sql::assoc);
 	if(!is_array($this->arr))
 		$this->id = 0;
 }
 
/**
 * Много одним запросом
 *
 * @param string|ids $where
 * @return mr_list
 */
 static public function several($where)
 {
 	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	
 	$r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE $where");
 	$ids = array();
 	
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		self::factory($f["id"], $f);
 		$ids[] = $f["id"];
 	}
 	
 	return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Кто есть в круге чтения
 *
 * @param int $user_id
 * @return mr_list
 */
 static public function byUser($user_id)
 {
 	$user_id = (int)$user_id;
 	if(self::$byUser[$user_id] instanceof mr_list)
 		return self::$byUser[$user_id];
 		
 	else return self::$byUser[$user_id] = self::several("owner = $user_id");
 }
 
/**
 * У кого в круге чтения
 *
 * @param int $user_id
 * @return mr_list
 */
 static public function byTarget($user_id)
 {
 	$user_id = (int)$user_id;
 	if(self::$byTarget[$user_id] instanceof mr_list)
 		return self::$byUser[$user_id];
 		
	else return self::$byTarget[$user_id] = self::several("target = $user_id");
 }
 
/**
 * Владелец связи
 *
 * @return ws_user
 */
 public function user()
 {
 	return ws_user::factory( $this->arr["owner"] );
 }
 
/**
 * Цель связи
 *
 * @return ws_user
 */
 public function target()
 {
 	return ws_user::factory( $this->arr["target"] );
 }
 
/**
 * Есть ли у юзера кто-то в круге чтения
 *
 * @param int $user_id
 * @param int $target
 * @return bool
 */
 static public function respects($user_id, $target, $trusts=null)
 {
 	if(!isset(self::$respects[$user_id][$target]))
 	{
 		$r = self::several("owner=$user_id AND target=$target");
 		if(count($r))
 			self::$respects[$user_id][$target] = $r[0];
 		else self::$respects[$user_id][$target] = false;
 	}
 	if(!(self::$respects[$user_id][$target] instanceof self))
 		return false;
 	if(!$trusts)
 		return true;
 		
 	$t = "trust_".$trusts;
 	return (self::$respects[$user_id][$target]->$t == "yes");
 }
 
/**
 * Создаёт новую связь, если её нет ещё
 *
 * @param int $user_id
 * @param int $target
 * @return ws_user_circle
 */
 static public function create($user_id, $target)
 {
 	$r = self::several( "owner=$user_id AND target=$target" );
 	if(count($r)) return $r[0];
 	
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(owner, target) VALUES(?, ?)",
 		$user_id, $target);
 	return self::factory( mr_sql::insert_id() );
 }
	
	}
?>