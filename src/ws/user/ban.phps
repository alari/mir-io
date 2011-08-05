<?php class ws_user_ban extends mr_abstract_change {

	const sqlTable = "mr_user_ban";

	static protected $objs = array(), $byUser = array(), $byAdmin = array();

/**
 * Проект фактори
 *
 * @param int $id
 * @param array $arr
 * @return ws_user_circle
 */
 static public function factory($arr=false)
 {
 	$id = is_array($arr) ? $arr["id"] : (int)$arr;
 	if( !isset(self::$objs[$id]) || !self::$objs[$id] instanceof self )
 	{
 		self::$objs[$id] = new self($id, $arr);
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
 		self::factory($f);
 		$ids[] = $f["id"];
 	}

 	return new mr_list(__CLASS__, $ids);
 }

/**
 * @param int $user_id
 * @return mr_list
 */
 static public function byUser($user_id)
 {
 	$user_id = (int)$user_id;
 	if(self::$byUser[$user_id] instanceof mr_list)
 		return self::$byUser[$user_id];

 	else return self::$byUser[$user_id] = self::several("user_id = $user_id");
 }

/**
 * @param int $user_id
 * @return mr_list
 */
 static public function byAdmin($admin_id)
 {
 	$user_id = (int)$user_id;
 	if(isset(self::$byAdmin[$admin_id]) && self::$byAdmin[$admin_id] instanceof mr_list)
 		return self::$byAdmin[$admin_id];

	else return self::$byAdmin[$admin_id] = self::several("admin_id = $admin_id");
 }

/**
 * @return ws_user
 */
 public function user()
 {
 	return ws_user::factory( $this->arr["user_id"] );
 }

/**
 * @return ws_user
 */
 public function admin()
 {
 	return ws_user::factory( $this->arr["admin_id"] );
 }

/**
 * @param int $user_id
 * @param int $admin_id
 * @param string $reason
 * @return ws_user_ban
 */
 static public function create($user_id, $admin_id, $reason)
 {
 	$for_days = self::factorial( count(self::byUser($user_id))+1 );

 	$since = time();
 	$till = $since + $for_days*86400;

 	mr_sql::qw("INSERT INTO ".self::sqlTable."(user_id,admin_id,reason,till,since) VALUES(?, ?, ?, ?, ?)",
 		$user_id, $admin_id, $reason, $till, $since);
 	return self::factory( mr_sql::insert_id() );
 }

 /**
  * Factorial function
  *
  * @param int $N
  * @return int
  */
 static public function factorial($N) {
 	$N = (int)$N;
 	if($N <= 2) return $N;
 	return $N*self::factorial($N-1);
 }


	}