<?php class ws_libro_pub {
	static protected $objs = array();

	protected $id, $arr=array(), $respcount=null, $advices=null;

/**
 * Project factory
 *
 * @param int $id
 * @param array $arr
 * @return ws_libro_pub
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
 	if(!$id) return false;
 	if(!isset(self::$objs[$id]) || !self::$objs[$id] instanceof self) self::$objs[$id] = new self($id, $arr);
 	return self::$objs[$id];
 }

/**
 * Выборка набора анонсов на произведения с подгрузкой рекомендаций и подсчётом отзывов
 *
 * @param string|array $where="1=1" Выражение WHERE или массив id произведений
 * @param int[opt] $limit Макс. количество в результате
 * @param int $offset Смещение
 * @param string $order="time DESC" Сортировка
 * @param int &$calcResult Ссылка для подсчёта полного количества строк
 * @param bool $loadExternal=false Подгружать ли рекомендации, считать ли отзывы
 * @return mr_list
 */
 static public function several($where="1=1", $limit=0, $offset=0, $order="time DESC", &$calcResult=false, $loadExternal=false)
 {
	if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	$query = "SELECT ".($calcResult!==false?"SQL_CALC_FOUND_ROWS ":"")."* FROM mr_publications WHERE $where".($order?" ORDER BY $order":"").($limit?" LIMIT ".($offset?"$offset, ":"").$limit:"");

 	return self::several_query($query, $calcResult, $loadExternal);
 }

/**
 * Рассматривает $query как запрос, возвращающий результат произведений
 *
 * @param string $query
 * @param int[opt] &$calcResult
 * @param bool $loadExternal=false
 * @return ws_list
 */
 static public function several_query($query, &$calcResult=false, $loadExternal=false)
 {
	$r = mr_sql::query($query);
 	if($calcResult !== false) $calcResult = mr_sql::found_rows();
 	$array = array();
 	$ids = array();

 	$usrs = array();

 	if($loadExternal)
 	{
 		$comms = array();
 		$sections = array();
 	}

 	while($f = mr_sql::fetch($r, mr_sql::assoc))
 	{
 		$array[] = $f;
 		$ids[] = $f["id"];
 		if(!in_array($f["author"], $usrs)) $usrs[] = $f["author"];
 		if($loadExternal)
 		{
 			if(!in_array($f["meta"], $comms)) $comms[] = $f["meta"];
 			if(!in_array($f["section"], $sections)) $sections[] = $f["section"];
 		}
 	}

 	if($loadExternal)
 	{
	 	ws_comm::several($comms);
	 	ws_libro_pub_sec::several($sections);
	 	ws_comm_pub_anchor::loadByPubs($ids);

 		$resps = mr_sql::qw("SELECT COUNT(id) AS c, pub_id FROM mr_pub_responses WHERE pub_id IN (".join(", ", $ids).") GROUP BY pub_id");
 		$ra = array();
 		while($r = mr_sql::fetch($resps, mr_sql::obj)) $ra[$r->pub_id]=$r->c;

 		ws_libro_pub_advice::loadByPubs($ids);
 	}

 	ws_user::several($usrs);

 	$ret = array();

 	foreach($array as $a)
 	{
 		$o = self::factory($a["id"], $a);
 		$ret[] = $a["id"];
 		if($loadExternal)
 		{
 			$o->respcount = isset($ra[ $a["id"] ]) ? (int)$ra[ $a["id"] ] : 0;
 		}
 	}

 	return new mr_list(__CLASS__, $ret);
 }

 private function __construct($id, $arr=false)
 {
 	$this->id = $id;
 	$this->arr = $arr;
 	if(!is_array($this->arr)) $this->arr = mr_sql::fetch("SELECT * FROM mr_publications WHERE id=".$this->id, mr_sql::assoc);
 }

/**
 * Оформленная ссылка на произведение
 *
 * @return string
 */
 public function link($class = null)
 {
 	return "&laquo;<a href=\"".$this->href()."\"".($class?" class=\"$class\"":"").($this->arr["description"]?" title=\"".htmlspecialchars($this->arr["description"])."\"":"").">".$this->arr["title"]."</a>&raquo;";
 }
 public function __toString()
 {
 	return $this->link();
 }

/**
 * Адрес произведения
 *
 * @param string $add
 * @return string
 */
 public function href($add="")
 {
 	return mr::host("libro")."/".$this->id.($add?".".$add:"").".xml";
 }

/**
 * Может ли произведение быть показано читателю
 *
 * @return bool
 */
 public function is_showable()
 {
 	if(ws_self::ok() && $this->arr["author"] == ws_self::id()) return true;
 	if($this->arr["hidden"] == "auth") return false;
 	if(!$this->arr["id"]) return false;
 	if($this->arr["hidden"] == "yes") return ws_self::is_allowed("see_hidden", $this->arr["meta"]);
 	return true;
 }

/**
 * Объект автора произведения
 *
 * @param bool $real=false
 * @return ws_user
 */
 public function author($real=false)
 {
 	if($real || $this->arr["anonymous"]=="no") return ws_user::factory($this->arr["author"]);
 	return ws_user::factory(ws_user::anonime);
 }

/**
 * Идентификатор произведения
 *
 * @return int
 */
 public function id()
 {
 	return $this->id;
 }

/**
 * Полное количество отзывов на произведение
 *
 * @return int
 */
 public function respCount()
 {
 	if($this->respcount === null)
 		$this->respcount = mr_sql::fetch("SELECT COUNT(pub_id) FROM mr_pub_responses WHERE pub_id=".$this->id, mr_sql::get);
 	return $this->respcount;
 }

/**
 * Список рекомендаций на произведение
 *
 * @return ws_list
 */
 public function advices()
 {
 	return ws_libro_pub_advice::byPub($this->id);
 }

/**
 * Метасообщество, где опубликовано произведение
 *
 * @return ws_comm
 */
 public function meta()
 {
 	return ws_comm::factory($this->arr["meta"]);
 }

 /**
 * Возвращает список открытых сообществ, где размещено произведение
 *
 * @return mr_list
 */
 public function comm_anchors()
 {
  return ws_comm_pub_anchor::byPub($this->id);
 }

/**
 * Возвращает полный объект произведения
 *
 * @return ws_libro_pub_item
 */
 public function fullItem()
 {
 	return ws_libro_pub_item::factory($this->id, $this->arr);
 }

/**
 * Строчка с названием типа произведения
 *
 * @return string
 */
 public function type()
 {
 	return self::getType($this->arr["type"]);
 }

/**
 * Текстовое название типа по prose|stihi|article
 *
 * @param string $string
 * @return string
 */
 static public function getType($string)
 {
 	switch($string)
 	{
 		case "prose": return "Проза";
 		case "stihi": return "Стихи";
 		case "article": return "Эссе";
 	}
 }

/**
 * Клубная оценка в текстовом виде
 *
 * @return string
 */
 public function clubscore()
 {
  return self::getClubscore( $this->arr["clubscore"] );
 }

/**
 * Переводит баллы в текст
 *
 * @param int $mark
 * @return string
 */
 static public function getClubscore($mark)
 {
 	if(!is_numeric($mark))
 		$mark = self::scoreFromMark($mark);

  if($mark >= 5) return "Отлично";
  elseif($mark >= 2) return "Хорошо";
  elseif($mark > 0) return "Удовлетв.";
  elseif($mark < 0) return "Неудовл.";
  else return "Нет оценки";
 }

/**
 * Возвращает число по названию оценки
 *
 * @param string $mark
 * @return int
 */
 static public function scoreFromMark($mark)
 {
 	switch($mark)
 	{
 		case "otl": return 6;
 		case "hor": return 3;
 		case "udovl": return 1;
 		case "neud": return -1;
 	}
 }

/**
 * Тематический раздел, содержащий произведение
 *
 * @return ws_libro_pub_sec
 */
 public function section()
 {
 	return $this->arr["section"] ? ws_libro_pub_sec::factory($this->arr["section"]) : null;
 }

 public function __get($name)
 {
 	return $this->arr[$name];
 }
	}
?>