<?php class ws_geo_region extends mr_abstract_change {
	
	static protected $objs=array();
	
	const sqlTable = "mr_geo_regions";
	
/**
 * Объект региона по ай-ди
 *
 * @param int $id
 * @param array[opt] $arr
 * @return ws_geo_region
 */
 static public function factory($id, $arr=false)
 {
 	$id = (int)$id;
 	if(!(@self::$objs[$id] instanceof self))
 		self::$objs[$id] = new self($id, $arr);
 	return self::$objs[$id];
 }
 protected function __construct($id, $arr=false)
 {
	$this->id = $id;
 	if(is_array($arr))
 		$this->arr = $arr;
 	else
 		$this->arr = mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);
 
 	if(!is_array($this->arr)) return false;
 }
 
/**
 * Ссылка на страничку региона (без флажка)
 *
 * @param string[opt] $class
 * @return string
 */
 public function link($class=null)
 {
 	return '<a href="'.$this->href().'"'.($class?" class=\"$class\"":"").($this->arr["description"]?" title=\"".htmlspecialchars($this->arr["description"])."\"":"").'>'.$this->arr["title"].'</a>';

 }
 public function __toString()
 {
 	return $this->link();
 }
 
 public function href()
 {
 	return mr::host("real")."/reg-".$this->arr["id"].".xml";
 }

/**
  * Возвращает страну, в которой регион, если указана
  * 
  * @return ws_geo_country
 
 */
 public function country()
 {
 	return ws_geo_country::factory($this->arr["country"]);
 }
 
/**
 * Столица региона
 *
 * @return ws_geo_city
 */
 public function maincity()
 {
 	return ws_geo_city::factory( $this->arr["maincity"] );
 }
 
/**
 * Родительский регион, если указан
 *
 * @return unknown
 */
 public function region()
 {
 	return $this->arr["region"] ? self::factory($this->arr["region"]) : null;
 }
 
/**
 * Города, для которых это -- родительский регион
 *
 * @param string $order
 * @param bool $active
 * @return mr_list
 */
 public function cities($order="name", $active=false)
 {
 	return ws_geo_city::several("region=".$this->id.($active?" AND u_active>0":""), $order);
 }
 
/**
 * Регионы, для которых это -- родительский регион
 *
 * @param string $order
 * @return mr_list
 */
 public function regions($order="title")
 {
 	return self::several("region=".$this->id, $order);
 }
 
/**
 * Страны, для которых это -- родительский регион
 *
 * @param string $order
 * @return mr_list
 */
 public function countries($order="name")
 {
 	return ws_geo_country::several("region=".$this->id, $order);
 }
 
/**
 * Список пользователей из региона
 *
 * @param bool $active=false
 * @return mr_list
 */
 public function users($active=false)
 {
  return null;ws_user::several("city='".$this->name."'".($active?" AND activity>0":"")." ORDER BY lastlogged DESC");
 }
 
/**
 * Загружает регионы по запросу
 *
 * @param string|array $where
 * @param string $order="title"
 * @return mr_list
 */
 static public function several($where, $order="title")
 {
  if(is_array($where) && !count($where)) return;
 	if(is_array($where)) $where = "id IN (".join(",", $where).")";
 	
  $ids = array();
  
  $r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE $where ORDER BY ".$order);
  
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["id"];
  	self::factory($f["id"], $f);
  }
  
  return new mr_list(__CLASS__, $ids);
 }
	
/**
 * Создание нового региона
 *
 * @param string $title
 * @param string $description
 * @param char(2) $country
 * @param int $region
 * @param int $maincity
 * @return mr_geo_region
 */
 static public function create($title, $description="", $country="", $region=0, $maincity=0)
 {
 	mr_sql::qw("INSERT INTO ".self::sqlTable."(title, description, country, region, maincity) VALUES(?, ?, ?, ?, ?)",
 		$title, $description, $country, $region, $maincity);
 	return self::factory( mr_sql::insert_id() );
 }
 /*
 static public function form_select()
 {
 	$ctree = array();
 	$regs = self::several("1=1", "country, region");
 	
 	$countries = array("");
 	
 	foreach($regs as $r)
 	{
 		if($r->country) $countries[]=$r->country;
 		$ctree[$r->country][] = $r;
 	}
 	$countries = ws_geo_country::several($countries);
 	$tree = array();
 	foreach($ctree as $c=>$regs)
 	{
 		$tree[$c] = self::maketree($regs);
 	}
 	
?>
<select name="region">
 <?foreach($tree as $c=>$t){?>
 
 <optgroup label="<?=($c?ws_geo_country::factory($c)->name:"Вне государств")?>">
  <?self::printtree($t)?>
 </optgroup>
 
 <?}?>
</select>
<?
 }
 
 static public function printtree($tree, $level=0)
 {
  foreach($tree as $v)
  {
?>
 <option value="<?=$v["id"]?>"><?=(str_repeat("-", $level)).self::factory($v["id"])->title?></option>
 <?
 	self::printtree($v["childs"], $level+1);
  }
 }
 
 static public function maketree($regs)
 {
  $parents = array();
  foreach($regs as $r)
  {
  	if($r->region) $parents[$r->region][] = $r->id();
  }
  
  return self::maketree_sub(0, $parents);
 }
 
 static public function maketree_sub($r, &$parents)
 {
  $return = array();
  foreach($parents[$r] as $reg)
  {
  	$return[] = array("id"=>$reg, "childs"=>self::maketree_sub($reg, $parents));
  }
  return $return;
 }*/
  
/**
 * Флажок страны города
 *
 * @return string
 */
 public function flag()
 {
 	return $this->arr["country"] ? ws_geo_country::factory($this->arr["country"])->flag() : "";
 }
 
/**
 * Удаление города
 *
 * @param ws_geo_city[opt] $target
 *//*
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
 *//*
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
 
 */
	}
?>