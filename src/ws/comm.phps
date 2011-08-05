<?php class ws_comm extends mr_abstract_change2 {

	const st_member = 1;
	const st_curator = 2;
	const st_leader = 3;
	const st_coord = 4;

	const sqlTable = "mr_communities";
	const sqlTableMerge = "mr_comm_info";

	static public $metas, $objs=array(), $byname=array();

	static protected $images = array(
			"meta"=>"Метасообщество",
			"closed"=>"Закрытое Сообщество",
			"members"=>"Полузакрытое Сообщество",
			"open"=>"Открытое Сообщество"
		);

		static public $org_spheres = array(
			'libro' => "Литература",
			'im' => "Арт",
			'photo' => "Фото",
			'music' => "Музыка",
			'disc' => "Дискуссии",
			'events' => "События",
			'cols' => "Колонки",
			'real' => "Реал",
			'site' => "Сайт"
		);

		static public $org_directs = array(
			'contest' => "Конкурсы",
			'recense' => "Рецензии",
			'publish' => "Публикация",
			'review' => "Обзоры",
			'theme' => "Тематика",
			'adm' => "Администрация",
			'soc' => "Общение"
		);

	protected $can_apply_pubs=array();


/**
 * Возвращает array comm_id=>status сообществ, где участвует пользователь
 *
 * @param int $userid
 * @return Array
 */
 static public function memberof($userid, $load_comms=0)
 {
  $r = mr_sql::qw("SELECT * FROM mr_comm_members WHERE user_id=?", $userid);
  $ret = array();
  $comms = array();

  while($f = mr_sql::fetch($r, mr_sql::obj))
  {
  	$ret[ $f->comm_id ] = $f->confirmed=="yes"?$f->status:0;
  	if($load_comms) $comms[] = $f->comm_id;
  }

  if($load_comms) self::several($comms);

  return $ret;
 }

/**
 * Массив номеров всех метасообществ
 *
 * @return array
 */
 static public function metas()
 {
  if(!is_array(self::$metas))
  {
  	self::$metas = self::several("type='meta'");
  	foreach(self::$metas as $k=>$v) self::$metas[$k] = $v->id();
  }

  return self::$metas;
 }

/**
 * Создаёт группу объектов комьюнити
 *
 * @param string $where="1=1"
 * @return mr_list
 */
 static public function several($where="1=1")
 {
 	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
  $r = mr_sql::query("SELECT * FROM mr_communities WHERE ".$where);
  $ids = array();
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["id"];
  	self::factory($f["id"], $f);
  }

  return new mr_list(__CLASS__, $ids);
 }

/**
 * Factory project
 *
 * @param int $id
 * @param array[opt] $arr
 * @return ws_comm
 */
 static public function factory($id, $arr=null)
 {
  if(!(@self::$objs[$id] instanceof self))
  {
   self::$objs[$id] = new self($id, $arr);
   self::$byname[strtolower($arr["name"])] = &self::$objs[$id];
  }

  return self::$objs[$id];
 }

/**
 * Возвращает объект сообщества по его имени
 *
 * @param string $name
 * @return ws_comm
 */
 static public function byName($name)
 {
  $name = strtolower($name);
  if(!(@self::$byname[$name] instanceof self))
  {
  	$arr = mr_sql::fetch(array("SELECT * FROM mr_communities WHERE name=?", $name), mr_sql::assoc);
  	self::$objs[$arr["id"]] = new self($arr["id"], $arr);
    self::$byname[strtolower($arr["name"])] = &self::$objs[$arr["id"]];
  }

  return self::$byname[$name];
 }

 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM mr_communities WHERE id=$this->id", mr_sql::assoc);
 }

/**
 * Оформленная ссылка на страничку сообщества
 *
 * @param string[opt] $class
 * @param string[opt] $href Ссылка внутри сообщества, если нужно
 * @param string[opt] $onclick Параметры онклик, если установлены, href=javascript:void(0)
 * @return string
 */
 public function link($class=null, $href=null, $onclick=null)
 {
 	if(!$href && !$onclick) $href = $this->href();
 	elseif($href) $href = $this->href($href);
 	elseif($onclick) {
 		$onclick = " onclick=\"".$onclick."\"";
 		$href = "javascript:void(0)";
 	}
 	return "<img src=\"".mr::host("iface")."/img/comm/".$this->arr["type"].".gif\" alt=\"\" title=\"".self::$images[$this->arr["type"]]."\" width=\"7\" height=\"7\" />&nbsp;<a href=\"".$href."\"".($class?" class=\"$class\"":"").$onclick." title=\"".htmlspecialchars($this->arr["description"])."\">".$this->arr["title"]."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }

/**
 * Адрес главной странички сообщества или раздела в ней
 *
 * @param string[opt] $where
 * @return string
 */
 public function href($where="")
 {
 	return mr::host("comm")."/-".strtolower($this->arr["name"])."/".$where;
 }

/**
 * Статус сообщества строчкой -- открытое и тп
 *
 * @return string
 */
 public function status()
 {
 	return self::$images[$this->arr["type"]];
 }

 public function mem_status($st)
 {
 	switch($st){
 		case self::st_coord: return "Координатор";
 		case self::st_leader: return "Лидер";
 		case self::st_curator: return "Куратор";
 		case self::st_member: return "Участник";
 		default: return "Претендент";
 	}
 }

/**
 * Все участники этого сообщества array(userid=>status)
 *
 * @return array
 */
 public function members()
 {
 	static $members;
 	if(!is_array($members))
 	{
 		$r = mr_sql::query("SELECT * FROM mr_comm_members WHERE comm_id=".$this->id);
 		$uids = array();
 		while($f = mr_sql::fetch($r, mr_sql::obj))
 		{
 			$members[ $f->user_id ] = $f->confirmed=="yes"?$f->status:0;
 			$uids[] = $f->user_id;
 		}
 	}
 	ws_user::several($uids);
 	return $members;
 }

/**
 * Работает ли сообщество в каком-то направлении
 *
 * @param string $direct
 * @return bool
 */
 public function is_direct($direct)
 {
 	return strpos($this->arr["org_direct"], $direct)!==false;
 }

 /**
 * Занимается ли сообщество в какую-то сторону
 *
 * @param string $sphere
 * @return bool
 */
 public function is_sphere($sphere)
 {
 	return strpos($this->arr["org_sphere"], $sphere)!==false;
 }

/**
 * Может ли пользователь подать (переместить) произведение
 *
 * @param string $type
 * @param int $time
 * @return bool
 */
 public function can_add_pub($type, $time=null)
 {
 	if(isset($this->can_apply_pubs[$type])) return $this->can_apply_pubs[$type];

 	if(!ws_self::ok()) return false;

 	$apply_pubs = $this->__get("apply_pubs");
 	if($apply_pubs == "disable") return false;
 	if($apply_pubs == "private" && !ws_self::is_member($this->id, self::st_curator)) return false;
 	if($apply_pubs == "protected" && !ws_self::is_member($this->id,1)) return false;

 	$quote = $this->__get("apply_".$type);
 	if(!$quote) return false;
 	if($quote<0) return true;

 	$period = $quote>1 ? 86400 : (1/$quote)*86400;
 	$num = $quote>1 ? $quote : 1;
 	if(!$time) $time=time();

 		return
 	$this->can_apply_pubs[$type] =
 			(
 		($this->__get("type")=="meta"||$this->__get("type")=="closed")
 	 ? mr_sql::fetch(
 	 	array("SELECT COUNT(meta) FROM mr_publications WHERE meta=? AND author=? AND time<? AND time>?", $this->id, ws_self::id(), $time-$period, $time), mr_sql::get)
 	 : mr_sql::fetch(
 	 	array("SELECT COUNT(c.comm_id) FROM mr_comm_pubs c LEFT JOIN mr_publications p ON p.id=c.pub_id WHERE c.comm_id=? AND p.author=? AND p.time<? AND p.time>?", $this->id, ws_self::id(), $time-$period, $time), mr_sql::get)
 	 		) < $num;
 }

/**
 * Можно ли управлять ходом дискуссии
 *
 * @param ws_user $user
 * @return bool
 */
 public function can_ch_discuss($user=null)
 {
 	if( !($user instanceof ws_user) )
 	{
 		if(!ws_self::ok()) return false;
 		$user = ws_self::self();
 	}
  	if($this->__get("apply_pubs_disc") == "public") return true;
	elseif($this->__get("apply_pubs_disc") == "protected" && $user->is_member($this->id)) return true;
  	elseif($this->__get("apply_pubs_disc") == "private" && $user->is_member($meta->id, ws_comm::st_curator)) return true;
 }

/**
 * Может ли юзер оставлять рецензии в сообществе -- вообще
 *
 * @return bool
 */
 public function can_make_recense()
 {
 	if(!ws_self::ok()) return false;
 	if(!ws_self::is_member($this->id)) return false;
 	switch($this->__get("recense_apply"))
 	{
 		case "disable": return false;
 		case "private": return ws_self::is_member($this->id, self::st_curator);
 		default: return true;
 	}
 }
	}
?>