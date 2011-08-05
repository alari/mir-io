<?php
/**
 * Класс дискуссионных веток
 *
 */
	class ws_comm_disc_thread extends mr_abstract_change  {
		
		const sqlTable = "mr_disc_threads";
		
		static protected $objs = array();
		
		protected $notes=null, $last_user=null, $last_time=null, $last_id=null;
		public $perpage = 20;

/**
 * Проект factory
 *
 * @param int $id Идентификатор элемента
 * @param array[opt] $arr Ассоциативный массив инфы о элементе
 * @return ws_comm_disc_thread
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
 	if(!(@self::$objs[$id] instanceof self))
 		self::$objs[$id] = new self($id, $arr);
 	return self::$objs[$id];
 }
 protected function __construct($id, $arr=false, $resps=false)
 {
	$this->id = $id;
 	if(is_array($arr))
 		$this->arr = $arr;
 	else
 		$this->arr = mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);
 
 	if(!is_array($this->arr)) return false;
 	if($resps !== false) $this->notes = $resps;
 }
 
 
/**
 * Позволяет загружать много веток
 *
 * @param string|array $cond="1=1" Строка -- where, массив -- список id.
 * @param int[opt] $limit Сколько штук
 * @param int[opt] $offset Смещение в результатах
 * @param string[opt] $order="time DESC" Ключ для сортировки
 * @return mr_list
 */
 static public function several($where="1=1", $limit=0, $offset=0, $order="last_time DESC", &$calcResult=false)
 {
 	if(is_array($where) && !count($where)) return;
	if(is_array($where)) $where = "id IN (".join(", ", $where).")";
 	$r = mr_sql::qw("SELECT ".($calcResult!==false?"SQL_CALC_FOUND_ROWS ":"")."* FROM ".self::sqlTable." WHERE $where".($order?" ORDER BY $order":"").($limit?" LIMIT ".($offset?"$offset, ":"").$limit:""));

 	if($calcResult !== false) $calcResult = mr_sql::found_rows();

 	$ids = array();

 	$usrs = array();
 	$comms = array();
 	$discs = array();
 	$lasts = array();
 	$array = array();

 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		$array[] = $f;
 		$ids[] = $f["id"];
 		if(!in_array($f["user_id"], $usrs)) $usrs[] = $f["user_id"];
 		if(!in_array($f["comm_id"], $comms)) $comms[] = $f["comm_id"];
 		if(!in_array($f["disc_id"], $discs)) $discs[] = $f["disc_id"];
 		$lasts[] = $f["last_id"];
 	}

 	ws_comm::several($comms);
 	ws_comm_disc::several($comms);
 	
 	$resps = mr_sql::query("SELECT COUNT(id) AS c, thread_id FROM mr_disc_notes WHERE thread_id IN (".join(", ", $ids).") GROUP BY thread_id");
 	
 	$ra = array();
 	while($r = mr_sql::fetch($resps, mr_sql::obj)) $ra[$r->thread_id]["notes"]=$r->c;
 	$resps = mr_sql::query("SELECT id, user_id, time, thread_id FROM mr_disc_notes WHERE id IN (".join(", ", $lasts).")");
 	
 	while($r = mr_sql::fetch($resps, mr_sql::obj))
 	{
 		$ra[$r->thread_id]["id"]=$r->id;
 		$ra[$r->thread_id]["time"]=$r->time;
 		$ra[$r->thread_id]["user"]=$r->user_id;
 	}
 	
 	$ret = array();
 	
 	foreach($array as $a)
 	{
 		$o = self::factory($a["id"], $a);
 		if(!$o->is_showable()) continue;
 		$o->last_id = (int)$ra[$a["id"]]["id"];
 		$o->last_time = (int)$ra[$a["id"]]["time"];
 		$o->last_user = (int)$ra[$a["id"]]["user"];
 		
 		if($o->last_user && !in_array($o->last_user, $usrs)) $usrs[] = $o->last_user;
 		
 		$o->notes = $ra[$a["id"]]["notes"];
 		$ret[] = $a["id"];
 	}
 	
 	ws_user::several($usrs);
 	return new mr_list(__CLASS__, $ret);
 }
 
/**
 * Засчитывает просмотр ветки
 *
 */
 public function increment_view()
 {
 	mr_sql::qw("UPDATE ".self::sqlTable." SET views=views+1 WHERE id=? LIMIT 1", $this->id);
 	if(ws_self::ok()) ws_user_remind::zeroize( ws_user_remind::type_society_thread_notes, $this->id );
 }
  
/**
 * Создать объект ветки (нулёвый)
 *
 * @param int $comm_id
 * @param int $disc_id
 * @param int $user_id
 * @return ws_comm_disc_thread
 */
 static public function create($comm_id, $disc_id, $user_id, $title)
 {
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(comm_id, disc_id, user_id, title, time, description) VALUES(?, ?, ?, ?, UNIX_TIMESTAMP(), '')", $comm_id, $disc_id, $user_id, $title);
 	return self::factory(mr_sql::insert_id());
 }
 
/**
 * Удалить текущее событие со всеми детьми
 *
 */
 public function delete()
 {
 	$this->getNotes(0, $this->notes()+1)->delete();
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=?", $this->id);
 	ws_user_remind::delete( ws_user_remind::type_society_thread_notes, $this->id );
 	unset($this);
 }
 
/**
 * Добавляет отзыв на событие. Возвращает объект отзыва, чтобы, например, дёрнуть оповещения
 *
 * @param int $user_id
 * @param string $message
 * @return ws_comm_disc_note
 */
 public function addNote($user_id, $message)
 {
 	return ws_comm_disc_note::create($this->id, $user_id, $message);
 }
 
/**
 * Возвращает отзывы на событие как объекты
 *
 * @param $page=0 Страница
 * @param $perpage=20 На страницу
 * @return mr_list
 */
 public function getNotes($page=0, $perpage=20, &$calcResult=false)
 {
 	return ws_comm_disc_note::several("thread_id=".$this->id, $perpage, $perpage*$page, "time", $calcResult);
 }
 
/**
 * Сообщество, владеющее веткой
 *
 * @return ws_comm
 */
 public function comm()
 {
 	return ws_comm::factory($this->arr["comm_id"]);
 }
 
/**
 * Ссылка на дискуссионную ветку
 *
 * @param int[opt] $page
 * @param string[opt] $class
 * @return string
 */
 public function link($page=0, $class="")
 {
 	return "<a href=\"".$this->href($page)."\"".($class?' class="'.$class.'"':"").($this->arr["description"]?' title="'.htmlspecialchars($this->arr["description"]).'"':"").">".$this->arr["title"]."</a>";
 }
 public function __toString()
 {
 	return $this->link();
 }
 
/**
 * Адрес от корня, с номером страницы, если надо
 *
 * @param int[opt] $page
 * @return string
 */
 public function href($page=0)
 {
 	return $this->comm()->href("thread-{$this->id}".($page?".page-".$page:"").".xml");
 }
 
/**
 * Объект родительской дискуссии
 *
 * @return ws_comm_disc
 */
 public function disc()
 {
 	return ws_comm_disc::factory($this->arr["disc_id"]);
 }
 
/**
 * Возвращает дату последнего сообщения в ветке
 *
 * @return int
 */
 public function last_time()
 {
 	if($this->last_time === null)
	 	$this->find_last();
	 return $this->last_time;
 }
 
/**
 * Объект пользователя, написавшего последнее видимое сообщение
 *
 * @return ws_user
 */
 public function last_user()
 {
 	if($this->last_user === null)
	 	$this->find_last();
	 return ws_user::factory($this->last_user);
 }
 
/**
 * Возвращает id последнего видимого сообщения
 *
 * @return int
 */
 public function last_id()
 {
 	if($this->last_id === null)
	 	$this->find_last();
	 return $this->last_id;
 }
 
/**
 * Ссылка на последнюю запись
 *
 * @return string
 */
 public function last_link($class="")
 {
 	return "<a href=\"".$this->href(ceil($this->notes/$this->perpage)-1)."#note".$this->last_id()."\"".($class?' class="'.$class.'"':"").($this->arr["description"]?' title="'.htmlspecialchars($this->arr["description"]).'"':"").">".$this->arr["title"]."</a>";
 }
 
/**
 * Возвращает количество сообщений в ветке
 *
 * @return int
 */
 public function notes()
 {
 	if($this->notes === null)
	 	$this->find_last();
	 return $this->notes;
 }
 
/**
 * Инициатор данной ветки
 *
 * @return ws_user
 */
 public function user()
 {
 	return ws_user::factory( $this->arr["user_id"] );
 }
 
/**
 * Связанная с дискуссией категория
 *
 * @return ws_comm_pub_categ
 */
 public function category()
 {
 	return $this->arr["category"] ? ws_comm_pub_categ::factory($this->arr["category"]) : null;
 }
 
/**
 * Ищет данные о последней записи и считает все записи
 *
 */
 private function find_last()
 {
 	$this->notes = mr_sql::fetch("SELECT COUNT(id) FROM mr_disc_notes WHERE thread_id=".$this->id, mr_sql::get);
 	$o = mr_sql::fetch("SELECT id, time, user_id FROM mr_disc_notes WHERE id=".$this->arr["last_id"], mr_sql::obj);
 	$this->last_id = $o->id;
 	$this->last_time = $o->time;
 	$this->last_user = $o->user_id;
 }
 
/**
 * Видима ли сия ветка дискуссий
 *
 * @return bool
 */
 public function is_showable()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	if(!$this->disc()->is_showable()) return false;
 	
 	switch($this->arr["visibility"])
 	{
 		case "public": return true;
 		case "protected": return ws_self::is_member($this->comm()->id());
 		case "private": return ws_self::is_member($this->comm()->id(), ws_comm::st_curator);
 	}
 }
 
/**
 * Можно ли добавить сообщение
 *
 * @return bool
 */
 public function can_add_note()
 {
 	if(ws_self::is_allowed("adm")) return true;
 	if(!$this->is_showable()) return false;
 	if($this->arr["closed"]=="yes") return false;
 	return true;
 }
 
/**
 * Можно ли административно удалить ветку
 *
 * @return bool
 */
 public function can_delete()
 {
 	if(ws_self::is_allowed("to_delete_pubs", $this->comm()->id())) return true;
 	return false;
 }
 
/**
 * Можно ли менять видимость ветки
 *
 * @return bool
 */
 public function can_ch_vis()
 {
 	if(ws_self::is_allowed("to_hide", $this->comm()->id())) return true;
 	return false;
 }
 
/**
 * Можно ли закрыть ветку
 *
 * @return bool
 */
 public function can_close()
 {
 	if(ws_self::id()==$this->arr["user_id"]) return true;
 	return $this->can_ch_vis();
 }
	}
?>