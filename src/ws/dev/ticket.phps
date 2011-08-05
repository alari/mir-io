<?php class ws_dev_ticket extends mr_abstract_change {

	const sqlTable = "mr_dev_tickets";

	static private $objs = array();
	static private $statuses = array(
		1 => "fixed",
		2 => "wontfix",
		3 => "new",
		4 => "reopened",
		5 => "invalid",
		6 => "duplicate",
		7 => "closed"
	);
	const status = 3;
	const stat_reopen = 4;
	static private $stat_opened = Array(
		3, 4
	);
	static private $stat_closed = Array(
		1, 2, 5, 6, 7
	);
	static private $priorities = array(
		1 => "ASAP (FIX RIGHT NOW!!!)",
		2 => "Critical (Fix today please!)",
		3 => "High priority (Focus on that)",
		4 => "Important (Required for the next release)",
		5 => "Normal priority",
		6 => "Low priority (On your leisure)",
		7 => "Postponded (Think on it)"
	);
	const priority = 5;
	static private $types = array(
		1 => "Bug",
		2 => "Improve",
		3 => "Feature",
		4 => "Module",
		5 => "Task"
	);
	const type = 1;
	static private $types_module = array(4,5);

/**
 * Загрузка тикета
 *
 * @param int|array $id
 * @return ws_dev_ticket
 */
 static public function factory($id)
 {
  if(is_array($id))
  {
  	$arr = $id;
  	$id = $arr["id"];
  }

  if(!(@self::$objs[$id] instanceof self))
  	self::$objs[$id] = new self($id, isset($arr)?$arr:null);
  return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);
 }

 public function is_showable()
 {
 	return ws_self::is_member(27);
 }

/**
 * Создаёт новый тикет
 *
 * @param int $project
 * @param string $title
 * @param string $content (plain)
 * @param int $type
 * @param int $priority
 * @return ws_dev_ticket
 */
 static public function create($project, $title, $content, $type=self::type, $priority=self::priority, $module=0)
 {
  $content = mr_text_trans::text2xml($content, mr_text_trans::prose);

  mr_sql::qw("INSERT INTO ".self::sqlTable."(project, user_id, title, type, priority, status, module, time)
  VALUES(?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())",
  	$project, ws_self::id(), $title, $type, $priority, self::status, $module);

  $ticket = self::factory(mr_sql::insert_id());

  if($ticket){
  	$note = ws_dev_note::create($ticket->id, ws_self::id());

  	$toch = array(
 		"title",
 		"priority",
 		"type",
 		"status",
 		"module"
 	);
 	foreach($toch as $t) $note->$t = $ticket->$t;
 	$note->content = $content;

 	$note->save();
  }

  return $ticket;
 }

/**
 * Ссылка на тикет
 *
 * @param string $class=null
 * @return string
 */
 public function link($class=null)
 {
  return "<a href=\"".$this->href()."\">$this->title</a>";
 }
 public function __toString()
 {
  return $this->link();
 }

 /**
  * Ссылка на страничку тикета
  *
  * @return string
  */
 public function href()
 {
 	return mr::host("dev")."/ticket-".$this->id.".xml";
 }

 /**
  * Проект, к которому привязан тикет
  *
  * @return ws_dev_project
  */
 public function project()
 {
 	return ws_dev_project::factory($this->project);
 }

 public function status()
 {
 	return self::$statuses[$this->status];
 }

 public function type()
 {
 	return self::$types[$this->type];
 }

 /**
  * Кому привязан
  *
  * @return ws_user or null
  */
 public function assignee()
 {
 	return $this->assignee ? ws_user::factory($this->assignee) : null;
 }

 /**
  * Кто добавил
  *
  * @return ws_user
  */
 public function author()
 {
 	return ws_user::factory($this->user_id);
 }

/**
 * Удаление тикета
 *
 */
 public function delete()
 {
  mr_sql::query("DELETE FROM ".self::sqlTable." WHERE id=".$this->id);
  unset($this);
 }

 /**
  * Модуль, компонента которого -- сие
  *
  * @return ws_dev_ticket or null
  */
 public function module()
 {
 	if($this->module) return self::factory($this->module);
 	return null;
 }

 /**
  * Все потомки сей ноды
  *
  * @param bool $opened
  * @return mr_list
  */
 public function getChilds($opened = true)
 {
 	$r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE module=$this->id AND status IN (".join(",", $opened?self::$stat_opened:self::$stat_closed).") ORDER BY priority, time DESC");
 	$list = new mr_list(__CLASS__);
 	while($a = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		$list[] = self::factory($a);
 	}
 	return $list;
 }

 /**
  * Все заметки по тикету
  *
  * @return mr_list
  */
 public function getNotes()
 {
 	return ws_dev_note::getAll($this->id);
 }

 /**
  * Все открытые или закрытые тикеты
  *
  * @param bool $opened
  * @return mr_list
  */
 static public function getAll($project = null, $opened = true)
 {
 	$q = new mr_sql_query(self::sqlTable);
 	if($opened) $q->test("status", self::$stat_opened);
 	else {
 		$arr = array_diff(array_keys(self::$statuses), self::$stat_opened);
 		$q->test("status", $arr);
 	}
 	if($project) $q->test("project", $project);
 	$q->order("priority, time DESC");
 	return $q->fetch(__CLASS__);
 }

 /**
  * List of open modules
  *
  * @return mr_list
  */
 static public function getModules($project, $opened = true)
 {
 	$q = new mr_sql_query(self::sqlTable);
 	$q->test("project", $project);
 	$q->test("status", $opened?self::$stat_opened:self::$statuses);
 	$q->test("type", self::$types_module);
 	$q->order("priority, time DESC");
 	return $q->fetch(__CLASS__);
 }

 static public function getTypes()
 {
 	return self::$types;
 }

 static public function getType($key)
 {
 	return self::$types[$key];
 }
 static public function getStatus($key)
 {
 	return self::$statuses[$key];
 }
 static public function getPriority($key)
 {
 	return self::$priorities[$key];
 }

 public function getAvailableStatuses()
 {
 	$arr = array($this->status => self::$statuses[$this->status]);
 	$tarr = array();
 	if(in_array($this->status, self::$stat_closed))
 		$tarr[] = self::stat_reopen;
 	else $tarr = self::$stat_closed;
 	foreach($tarr as $k) $arr[$k] = self::$statuses[$k];
 	return $arr;
 }

 public function getAvailableTypes()
 {
 	$arr = Array();
 	foreach(self::$types as $k=>$v){
 		if (($this->isModule() && in_array($k, self::$types_module))
 		|| (!$this->isModule() && !in_array($k, self::$types_module)))
 		$arr[$k] = $v;
 	}
 	return $arr;
 }

 public function isModule()
 {
 	return in_array($this->type, self::$types_module);
 }

 static public function getStatuses($opened=1)
 {
 	if($opened)
 	{
 		$arr = array();
	 	foreach(self::$stat_opened as $s) $arr[$s] = self::$statuses[$s];
	 	return $arr;
 	}
 	return self::$statuses;
 }

 static public function getPriorities()
 {
 	return self::$priorities;
 }

	}?>