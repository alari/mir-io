<?php class ws_libro_pub_sec extends mr_abstract_change {

 const sqlTable = "mr_pub_sections";
 
 static private $objs = array();
	
/**
 * Загрузка рекомендации
 *
 * @param int $id
 * @param array $arr
 * @return ws_libro_pub_sec
 */
 static public function factory($id, $arr=false)
 {
  if(!(@self::$objs[$id] instanceof self))
  	self::$objs[$id] = new self($id, $arr);
  return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM mr_pub_sections WHERE id=".$this->id, mr_sql::assoc);	
 }
 
/**
 * Загрузка кучи разделов
 *
 * @param string $where="1=1
 * @param string $order="title="
 * @return mr_list
 */
 static public function several($where="1=1", $order="title")
 {
  if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
  $r = mr_sql::query("SELECT * FROM mr_pub_sections WHERE $where ORDER BY $order");
  $ids = array();
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["id"];
  	self::factory($f["id"], $f);
  }
  return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Ссылка на раздел
 *
 * @param string $class=null
 * @return string
 */
 public function link($class=null)
 {
  return "<a href=\"".$this->href()."\"".($class?" class=\"$class\"":"").">{$this->arr['title']}</a>";
 }
 public function __toString()
 {
  return $this->link();
 }
 
 public function href()
 {
 	return mr::host("libro")."/".$this->id.".ml";
 }
 
/**
 * Для селектора -- опции-разделы
 *
 * @param string $type
 * @param int $selected
 * @return string
 */
 static public function options($type, $selected=null)
 {
  $a = self::several("type='$type'");
  $ret = "";
  foreach($a as $s)
  	$ret .= "<option value=\"".$s->id()."\"".($selected==$s->id()?" selected=\"yes\"":"").">".$s->arr["title"]."</option>";
  return $ret;
 }
 
/**
 * Все или часть произведений в разделе
 *
 * @param string $order="time DESC"
 * @param int &$calcResult=false
 * @param int $limit
 * @param int $offset
 * @return mr_list
 */
 public function pubs($order="time DESC", &$calcResult=false, $limit=0, $offset=0)
 {
 	return ws_libro_pub::several("section=".$this->id, $limit, $offset, $order, $calcResult, true);
 }
	}?>