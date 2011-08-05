<?php class ws_user extends mr_abstract_change2 {

	protected $name, $allows, $comm_member, $msgs, $meta=0, $params, $circle;
	static protected $objs=array(), $logins=array();

	const sqlTable = "mr_users";
	const sqlTableMerge = "mr_user_config";
	const anonime = 562;

/**
 * Возвращает объект класса
 *
 * @param int $id
 * @param array[opt] $arr
 * @return ws_user
 */
 static public function factory($id, $arr=false)
 {
 	if(!$id) $id = self::anonime;
 	if(!@self::$objs[$id] instanceof self)
 	{
 		self::$objs[$id] = $id ? new self($id, $arr) : null;
 		self::$logins[ strtolower(self::$objs[$id]->login) ] =& self::$objs[$id];
 	}
 	return self::$objs[$id];
 }

 /**
  * Creates new user
  *
  * @param string $login
  * @param string $pwd
  * @param string $email
  * @return ws_user
  */
 static public function create($login, $pwd, $email){
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(login, md5, email, registration_time, remote_addr) VALUES(?, ?, ?, UNIX_TIMESTAMP(), ?)",
 		$login, md5($pwd), $email, $_SERVER["REMOTE_ADDR"]);
 	$id = mr_sql::insert_id();
 	if(!$id) {
 		return false;
 	}
 	mr_sql::qw("INSERT INTO ".self::sqlTableMerge."(id) VALUES(?)", $id);
 	if(!mr_sql::affected_rows()) {
 		mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $id);
 		return false;
 	}
 	return self::factory($id);
 }

/**
 * Возвращает объект по логину
 *
 * @param string $login
 * @return ws_user
 */
 static public function getByLogin($login)
 {
 	$login = strtolower($login);
 	if(!isset(self::$logins[$login]))
 	{
 		$arr = mr_sql::fetch(array("SELECT * FROM mr_users WHERE login=? LIMIT 1", $login), mr_sql::assoc);
 		return self::factory($arr["id"], $arr);
 	} else return self::$logins[$login];
 }


/**
 * Возвращает объект по электропочте
 *
 * @param string $email
 * @return ws_user
 */
 static public function getByEmail($email)
 {
 	$arr = mr_sql::fetch(array("SELECT * FROM mr_users WHERE email=? LIMIT 1", $email), mr_sql::assoc);
 	return self::factory($arr["id"], $arr);
 }

 static public function getIdByLogin($login)
 {
  $usr = self::getByLogin($login);
  if($usr instanceof self) return $usr->id();
  return self::anonime;
 }

 /**
  * Конструктор приватен
  *
  * @param int $id
  * @param array[opt] $arr
  */
 private function __construct($id, $arr=false)
 {
 	$this->id = (int)$id;
 	if(!is_array($arr))
 		$this->arr = mr_sql::fetch("SELECT * FROM mr_users WHERE id=$this->id LIMIT 1", mr_sql::assoc);
 	else $this->arr = $arr;
 }

/**
 * Загружает много юзеров одним запросом
 *
 * @param string $where SQL-выражение в WHERE
 * @return mr_list
 */
 static public function several($where)
 {
 	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	$r = mr_sql::query("SELECT * FROM mr_users WHERE ".$where);
 	$ids = array();
 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		self::factory($f["id"], $f);
 		$ids[] = $f["id"];
 	}
 	return new mr_list(__CLASS__, $ids);
 }

/**
 * Имя пользователя -- ник, ФИО или логин
 *
 * @return string
 */
 public function name()
 {
  if(!$this->name)
  {
   switch( $this->arr["set_name_by"] )
   {
    case "fio": if(!$this->name && $this->arr["fio"]) $this->name = $this->arr["fio"];
    case "nick": if(!$this->name && $this->arr["nick"]) $this->name = $this->arr["nick"];
    default: if(!$this->name) $this->name = $this->arr["login"];
   }
   if(!$this->name)
   {
    $this->name = "Undefined #".$this->id;
   }
  }
  return $this->name;
 }

/**
 * Возвращает ссылку на профиль или внутреннюю страничку юзера
 *
 * @param string $where="profile" Страничка юзера
 * @param string $class Класс ссылки
 * @return string
 */
 public function link($where="profile", $class=null)
 {
 	if($this->id == self::anonime) return $this->name();
 	return "<a href=\"".$this->href($where)."\"".($class?" class=\"$class\"":"")." title=\"".htmlspecialchars($this->arr["login"])."\">".($this->is_banned()?"<s>":"").$this->name().($this->is_banned()?"</s>":"")."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }

/**
 * Возвращает адрес странички юзера
 *
 * @param string $where="profile"
 * @return string
 */
 public function href($where="profile")
 {
 	return mr::host("users")."/~".strtolower($this->arr["login"])."/".$where;
 }

 /**
 * Забанен ли пользователь
 *
 * @return bool
 */
 public function is_banned()
 {
  return ($this->arr["banned_till"] && $this->arr["banned_till"] > time());
 }

/**
 * Является ли пользователь членом сообщества. Проверка статуса
 *
 * @param int $comm
 * @param int[opt] $status
 * @return int|bool
 */
 public function is_member($comm, $status=null)
 {
 	$comm = (int)$comm;
  if(!is_array($this->comm_member))
  	$this->comm_member = ws_comm::memberof($this->id);

  if(!isset($this->comm_member[$comm])) return false;
  return $status!==null ? $this->comm_member[$comm] >= $status : $this->comm_member[$comm];
 }

/**
 * Возвращает сообщества, где есть пользователь
 * $comm => $status
 *
 * @return array
 */
 public function memberof($load_comms=0)
 {
 	return is_array($this->comm_member) ? $this->comm_member : $this->comm_member = ws_comm::memberof($this->id, $load_comms);
 }

/**
 * Проверяет разрешения пользователей
 *
 * @param string $what
 * @param int $comm=0
 * @return bool
 */
 public function is_allowed($what, $comm=0)
 {
 	if($this->is_banned()) return false;
 	if($this->is_meta(ws_comm::st_coord)) return true;

 	$coord_only = array(
 		"comm_control",
 		"comm_control_advanced",
 		"metaadm"
 	);

  static $paid = array("circle", "images");

  if($what == "attach"){
  	if($this->is_full()) return true;

  	if(mr_sql::fetch("SELECT SUM(size) FROM mr_attach_stat WHERE user_id=$this->id", mr_sql::get) > 200 * 1024 * 1024) return false;
  	return true;
  }

  // paid
  if(!$comm && in_array($what, $paid)) return $this->is_full();

  // private
  if(!is_array($this->allows))
  {
  	// HARDCODE
  	$this->allows = array();
  	$r = mr_sql::qw("SELECT * FROM mr_comm_allows WHERE user_id=?", $this->id);
  	while($f = mr_sql::fetch($r, mr_sql::obj))
  		$this->allows[$f->comm_id][$f->name] = $f->value=="yes"?1:0;
  }
  if(isset($this->allows[0][$what])) return $this->allows[0][$what];
  if($comm && isset($this->allows[$comm][$what])) return $this->allows[$comm][$what];

  // adm //to_delete_notes //to_delete_pubs //to_hide //pub_stat //comm_control_advanced //comm_control //edit_blog //responses

  // default
  switch($what)
  {
  	case "auth": return $this->email_confirmed=="yes";
  	case "chat": return true;
  	case "responses": return true;
  	case "attach": return true;
  	case "circle": case "pub_stat": return true;
  	case "adm_chat": return $this->is_member(1);
  	case "meta_adm_panel": return $this->is_member(1, ws_comm::st_coord);
  	case "comm_control": case "to_delete_notes": case "to_delete_pubs": return $this->is_member($comm, ws_comm::st_leader);
  	case "to_hide": case "see_hidden": return $this->is_member($comm, ws_comm::st_curator) || $this->is_member(1, ws_comm::st_curator);
  	case "clubvote": return $this->is_full();

  }

  if($this->is_member(1, ws_comm::st_curator) && !in_array($what, $coord_only)) return true;

  return false;
 }

/**
 * Является ли юзер владельцем полного аккаунта
 *
 * @return unknown
 */
 public function is_full()
 {
  if($this->arr["user_group"]==1 || $this->arr["user_group"]==7) return true;
  return false;
 }

/**
 * Является ли человек администратором + контроль статуса
 *
 * @param int[opt] $stat
 * @return string|bool
 */
 public function is_meta($stat=null)
 {
  return $this->is_member(1, $stat);
 }

 // template
 public function param($name)
 {
 	if(!is_array($this->params))
 	{
 	 $this->params = array();
 	 $r = mr_sql::query("SELECT * FROM mr_user_params WHERE user_id=".$this->id);
 	 while($f = mr_sql::fetch($r, mr_sql::obj)) $this->params[$f->name] = $f->value;
 	}
 }

/**
 * Объект работы с сообщениями пользователя
 *
 * @return ws_user_msgs
 */
 public function msgs()
 {
 	return ws_user_msgs::factory($this->id);
 }

/**
 * Аватарка пользователя для html
 *
 * @param string[opt] $href Адрес ссылки, если аватарка является ссылкой
 * @return string
 */
 public function avatar($href=null)
 {
 	return ($href?"<a href=\"$href\">":"").'<img src="http://sep.litclub.net/avatar/'.$this->arr["login"].'" alt="'.$this->arr["login"].'" border="0" />'.($href?"</a>":"");
 }

 public function status()
 {
 	return $this->arr["status"];
 }

/**
 * Игнорирует ли данный юзер юзера-аргумент
 *
 * @param int $user_id
 * @return bool
 */
 public function ignores($user_id)
 {
 	return false;
 }

/**
 * Уважает (занёс в круг чтения) ли
 *
 * @param int $user_id
 * @return bool
 */
 public function respects($user_id, $trust=null)
 {
 	return $user_id ? ws_user_circle::respects($this->id, $user_id, $trust) : false;
 }

/**
 * Оболочка для статического вызова пользовательских функций
 *
 * @return string
 */
 static public function call()
 {
  $args = func_get_args();
   $userid = array_shift($args);
   $method = array_shift($args);
  $user = self::factory($userid);
  if($user instanceof self && $method) return call_user_func_array(array($user, $method), $args);
 }

/**
 * Оболочка для статического получения данных о юзере
 *
 * @param int $userid
 * @param string $what
 * @return string
 */
 static public function get($userid, $what)
 {
  $user = self::factory($userid);
  return $user->__get($what);
 }
	} ?>
