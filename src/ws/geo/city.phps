<?php class ws_geo_city extends mr_abstract_change {
	
	static protected $byName=array(), $objs=array();
	
	protected $name;
	
	const sqlTable = "mr_geo_cities";
	
/**
 * Объект города по его названию
 *
 * @param string $name
 * @return ws_geo_city
 */
 static public function byName($name)
 {
   if(!isset(self::$byName[$name]))
   {
   	$a = mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE name=?", $name), mr_sql::assoc);
   	self::$byName[$name] = new self($name, is_array($a)?$a:array());
   	self::$objs[$a["id"]] =& self::$byName[$name];
   }
   return self::$byName[$name];
 }

 static public function factory($id, $arr=null)
 {
  return self::byID($id, $arr);
 }
/**
 * Объект города по ай-ди
 *
 * @param int $id
 * @param array[opt] $arr
 * @return ws_geo_city
 */
 static public function byID($id, $arr=null)
 {
   if(!isset(self::$objs[$id]))
   {
   	if(is_array($arr)) $a = $arr;
   	else $a = mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE id=? OR code=?", $id, $id), mr_sql::assoc);
   	self::$byName[$a["name"]] = new self($a["name"], $a);
   	return self::$objs[$a["id"]] =& self::$byName[$a["name"]];
   }
   return self::$objs[$id];
 }
 
 private function __construct($name, $arr=null)
 {
 	$this->name = $name;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE name=?", $name), mr_sql::assoc);
 	$this->id = @$this->arr["id"];
 }
 
/**
 * Ссылка на страничку города (без флажка)
 *
 * @param string[opt] $class
 * @return string
 */
 public function link($class=null)
 {
 	if($this->id) return '<a href="'.$this->href().'"'.($class?" class=\"$class\"":"").'>'.$this->name.'</a>';
 	else return '<i>'.$this->name.'</i>';
 }
 public function __toString()
 {
 	return $this->link();
 }
 
 public function href()
 {
 	return mr::host("real")."/".($this->arr["code"] ? $this->arr["code"] : $this->arr["id"].".xml");
 }
 
/**
 * Флажок страны города
 *
 * @return string
 */
 public function flag()
 {
 	if(!is_array($this->arr)) return "";
 	else return ws_geo_country::factory($this->arr["country"])->flag();
 }
 
/**
 * Родительский регион, если указан
 *
 * @return ws_geo_region
 */
 public function region()
 {
 	return $this->arr["region"] ? ws_geo_region::factory($this->arr["region"]) : null;
 }
 
/**
 * Создаёт запись города в указанном государстве
 *
 * @param unknown_type $country
 */
 public function create($country)
 {
 	if(!$this->id)
 	{
 		mr_sql::qw("INSERT INTO ".self::sqlTable."(name, country) VALUES(?,?)", $this->name, $country);
 		$this->arr = mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE name=?", $this->name), mr_sql::assoc);
 		$this->id = $this->arr["id"];
 	}
 }
 
/**
 * Удаление города
 *
 * @param ws_geo_city[opt] $target
 */
 public function delete($target=null)
 {
 	$t_id = 0;
 	$t_name = "";
 	if($target instanceof self)
 	{
 		$t_id = $target->id();
 		$t_name = $target->arr["name"];
 	}
 	
 	mr_sql::qw("UPDATE mr_users SET city=? WHERE city=?", $t_name, $this->arr["name"]);
 	mr_sql::qw("UPDATE mr_comm_events SET city=? WHERE city=?", $t_id, $this->id);
 	
 	if($this->arr["pic_src"])
 	{
 		$ftp = new ws_fileserver;
 		$ftp->delete($this->arr["pic_src"]);
 	}
 	
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $this->id);
 	
 	unset($this);
 }
 
/**
 * Возвращает селектор с городами и странами
 *
 * @param string $selectName="city"
 * @param string $selectType="name"
 * @param string|int[opt] $selected
 * @return string
 */
 static public function form_select($selectName="city", $selectType="name", $selected=null)
 {
   $r = mr_sql::qw("SELECT * FROM ".self::sqlTable." ORDER BY country, name");
  
   $blocks = array();
   $countr = array();
   
   while($f = mr_sql::fetch($r, mr_sql::assoc))
   {
   	$blocks[ strtolower($f["country"]) ][] = self::byID($f["id"], $f);
   	if(!in_array($f["country"], $countr)) $countr[] = $f["country"];
   }
   
   $cs = ws_geo_country::several($countr);
   
   $return = "<select name=\"$selectName\"><option value=''> </option>";
   
   foreach($cs as $country)
   {
   	$arr = $blocks[$country->code()];
   	$return.= "<optgroup label=\"".$country->name."\">";
   	foreach($arr as $c)
   	{
   		if($selectType == "name") $return .= "<option".($c->__get("name")==$selected?' selected="yes"':'').">".$c->__get("name")."</option>";
   		else $return.= "<option value=\"".$c->id()."\"".($c->id()==$selected?" selected='yes'":"").">".$c->__get("name")."</option>";
   	}
   	$return.= "</optgroup>";
   }
   
   $return.= "</select>";
   
   return $return;
 }
 
/**
 * Возвращает страну, в которой город
 *
 * @return ws_geo_country
 */
 public function country()
 {
 	return ws_geo_country::factory($this->arr["country"]);
 }
 
/**
 * Возвращает карту с отмеченным городом
 *
 * @param string $canvasElementId
 * @return ws_geo_map
 */
 public function map($canvasElementId)
 {
 	if(!$this->arr["latitude"] && !$this->arr["longitude"]) return false;
 	
 	$map = new ws_geo_map($canvasElementId);
 	$map->setCity($this);
 	return $map;
 }
 
/**
 * Список пользователей из города
 *
 * @param bool $active=false
 * @return mr_list
 */
 public function users($active=false)
 {
  return ws_user::several("city='".$this->name."'".($active?" AND activity>0":"")." ORDER BY lastlogged DESC");
 }
 
/**
 * Загружает города по запросу
 *
 * @param string|array $where
 * @param string $order="name"
 * @return mr_list
 */
 static public function several($where, $order="name")
 {
  if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	
  $ids = array();
  
  $r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE $where ORDER BY ".$order);
  
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["id"];
  	self::byID($f["id"], $f);
  }
  
  return new mr_list(__CLASS__, $ids);
 }
	
	}
?>