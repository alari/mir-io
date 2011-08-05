<?php class ws_comm_pub_anchor extends mr_abstract_change {

	const sqlTable = "mr_comm_pubs";

	static protected $objs=array(), $byPub=array(), $byResp=array();

/**
 * Factory project
 *
 * @param int $id
 * @param array $arr
 * @return ws_comm_pub_anchor
 */
 static public function factory($id, $arr=false)
 {
  if(!isset(self::$objs[$id]) || !self::$objs[$id] instanceof self)
  {
  	self::$objs[$id] = new self($id, $arr);
  	if(self::$objs[$id]->resp_id)
  		self::$byResp[self::$objs[$id]->resp_id][] = &self::$objs[$id];
  }
  return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM mr_comm_pubs WHERE id=".$this->id, mr_sql::assoc);
 }

/**
 * Создаёт новую связь произведения с сообществом
 *
 * @param int $pid
 * @param int $category
 * @param int $comm_id
 * @param int $editor
 * @return ws_comm_pub_anchor
 */
 static public function create($pid, $category, $comm_id, $editor)
 {
  mr_sql::qw("INSERT INTO mr_comm_pubs(pub_id, comm_id, category, editor) VALUES(?, ?, ?, ?)", $pid, $category, $comm_id, $editor);
  return self::factory(mr_sql::insert_id());
 }

/**
 * Все связи по произведению
 *
 * @param int $id
 * @return mr_list
 */
 static public function byPub($id)
 {
  if(!isset(self::$byPub[$id]) || !self::$byPub[$id] instanceof mr_list)
  {
  	$r = mr_sql::query("SELECT * FROM mr_comm_pubs WHERE pub_id=".$id);
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[$f["comm_id"]] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	self::$byPub[$id] = new mr_list(__CLASS__, $ids);
  }
  return self::$byPub[$id];
 }

/**
 * Загруженные связи с рецензией
 *
 * @param int $id
 * @return array
 */
 static public function byResp($id)
 {
  return @self::$byResp[$id];
 }

/**
 * Загружает произведения по категории или сообществу
 *
 * @param int $category_id
 * @param int $comm_id
 * @param string $type
 * @param string $order
 * @param int $offset
 * @param int $limit
 * @param int &$calcResult
 * @param bool $loadExternal
 * @return mr_list
 */
 static public function pubs($category_id, $comm_id, $type=null, $order="p.time DESC", $offset=0, $limit=100, &$calcResult=false, $loadExternal=false)
 {
 	$where = ($comm_id==null?"":"c.comm_id=".$comm_id).($category_id!==null?" AND c.category=".$category_id:"").($type?" AND p.type='$type'":"");
	$query = "SELECT".($calcResult===false?"":" SQL_CALC_FOUND_ROWS")." p.*, c.editor, c.category, c.user_id, c.comm_id, c.id AS c_id, c.resp_id FROM mr_publications p LEFT JOIN mr_comm_pubs c ON c.pub_id=p.id WHERE $where ORDER BY ".$order." LIMIT $offset, $limit";

 	return ws_libro_pub::several_query($query, $calcResult, $loadExternal);
 }

/**
 * Возвращает произведения-рецензии
 *
 * @param int|array $comm_id
 * @param int $offset
 * @param int $limit
 * @param bool &$calcResult
 * @param bool $loadExternal
 * @return mr_list
 */
 static public function rec($comm_id=null, $offset=0, $limit=0, &$calcResult=false, $loadExternal=false)
 {
	$where = "c.resp_id!=0".($comm_id==null?"":(is_array($comm_id)?" AND c.comm_id IN (".join(",", $comm_id).")":"c.comm_id=".$comm_id));
	$query = "SELECT".($calcResult===false?"":" SQL_CALC_FOUND_ROWS")." p.*, c.editor, c.category, c.user_id, c.comm_id, c.id AS c_id, c.resp_id FROM mr_publications p LEFT JOIN mr_comm_pubs c ON c.pub_id=p.id WHERE $where ORDER BY c.resp_id DESC LIMIT $offset, $limit";

 	return ws_libro_pub::several_query($query, $calcResult, $loadExternal);
 }

/**
 * Возвращает произведения-рецензии
 *
 * @param int $user_id
 * @param int|array $comm_id
 * @param bool $required требует рецензии или уже отрецензировано
 * @param int $offset
 * @param int $limit
 * @param bool &$calcResult
 * @param bool $loadExternal
 * @return mr_list
 */
 static public function recByUser($user_id, $comm_id=null, $required=true, $offset=0, $limit=0, &$calcResult=false, $loadExternal=false)
 {
	$where = "c.editor=$user_id".($required?" AND c.resp_id=0":" AND c.resp_id!=0").($comm_id==null?"":(is_array($comm_id)?" AND c.comm_id IN (".join(",", $comm_id).")":("c.comm_id=".$comm_id)));
	$query = "SELECT".($calcResult===false?"":" SQL_CALC_FOUND_ROWS")." p.*, c.editor, c.category, c.user_id, c.comm_id, c.id AS c_id, c.resp_id FROM mr_publications p LEFT JOIN mr_comm_pubs c ON c.pub_id=p.id WHERE $where ORDER BY c.time".($limit?" LIMIT $offset, $limit":"");

 	return ws_libro_pub::several_query($query, $calcResult, $loadExternal);
 }

/**
 * Загружает связи для набора произведений
 *
 * @param array $pids
 */
 static public function loadByPubs(array $pids)
 {
  if(!count($pids)) return;
  $r = mr_sql::query("SELECT * FROM mr_comm_pubs WHERE pub_id IN (".join(",", $pids).")");
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[ $f["pub_id"] ][] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	foreach($pids as $id)
  		self::$byPub[$id] = new mr_list(__CLASS__, is_array($ids[$id])?$ids[$id]:array());
 }

 static public function loadByResps(array $rids)
 {
  if(!count($rids)) return;
  $r = mr_sql::query("SELECT * FROM mr_comm_pubs WHERE resp_id IN (".join(",", $rids).")");
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[ $f["resp_id"] ][] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	foreach($rids as $id)
  		self::$byResp[$id] = new mr_list(__CLASS__, isset($ids[$id])&&is_array($ids[$id])?$ids[$id]:array());
 }

/**
 * Удаляет якорь произведения
 *
 */
 public function delete()
 {
  mr_sql::query("DELETE FROM mr_comm_pubs WHERE id=".$this->id);
  unset($this);
 }

/**
 * Анонс произведения
 *
 * @return ws_libro_pub
 */
 public function pub()
 {
  return ws_libro_pub::factory($this->arr["pub_id"]);
 }

/**
 * Категория связи
 *
 * @return ws_comm_pub_categ
 */
 public function category()
 {
  return $this->arr["category"] ? ws_comm_pub_categ::factory($this->arr["category"]) : null;
 }

/**
 * Редактор, если есть, или null
 *
 * @return ws_user
 */
 public function editor()
 {
  return $this->arr["editor"] ? ws_user::factory($this->arr["editor"]) : null;
 }

/**
 * Предложивший пользователь или null
 *
 * @return ws_user
 */
 public function user()
 {
  return $this->arr["user_id"] ? ws_user::factory($this->arr["user_id"]) : null;
 }

/**
 * Сообщество связи
 *
 * @return ws_comm
 */
 public function comm()
 {
  return ws_comm::factory($this->arr["comm_id"]);
 }

/**
 * Возвращает ссылку для вывода связи в анонсе произведения
 *
 * @param string $class=null
 * @return string
 */
 public function link($class=null)
 {
  return $this->comm()->link($class);
 }
 public function __toString()
 {
 	return $this->link();
 }
	}?>