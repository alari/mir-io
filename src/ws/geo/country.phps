<?php class ws_geo_country {

	static protected $objs=array();
	
	protected $arr=array(), $code, $flag, $cities;
	
	const sqlTable = "mr_geo_country";
	
/**
 * Возвращает объект страны
 *
 * @param string $code
 * @return ws_geo_country
 */
 static public function factory($code, $arr=null)
 {
 	$code = strtolower($code);
  if(!isset(self::$objs[$code]))
  	self::$objs[$code] = new self($code, $arr);
  	
  return self::$objs[$code];
 }
 private function __construct($code, $arr)
 {
 	$this->code = $code;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE code=?", $code), mr_sql::assoc);
 }
	
/**
 * Возвращает картинку с флагом страны -- ссылкой или нет
 *
 * @param bool $link=true
 * @return string
 */
 public function flag($link=true)
 {
  if(!$this->flag)
  {
  	$this->flag = '<img src="'.mr::host("iface").'/img/country/'.$this->code.'.gif" alt="'.htmlspecialchars($this->arr["name"]).'" width="23" height="15" title="'.htmlspecialchars($this->arr["name"]).'" border="0" />';
  }
  
  return $link ? '<a href="'.$this->href().'">'.$this->flag.'</a>' : $this->flag;
 }
 
/**
 * Ссылка на страничку страны -- с названием
 *
 * @param string[opt] $class
 * @return string
 */
 public function link($class=null)
 {
  return '<a href="'.$this->href().'"'.($class?" class=\"$class\"":"").'>'.$this->arr["name"].'</a>';
 }
 public function __toString()
 {
  return $this->link();
 }
 
 public function href()
 {
 	return mr::host("real")."/".$this->code.".xml";
 }
 
/**
 * Все города в стране в алфавитном порядке
 *
 * @param string $order="name"
 * @return mr_list
 */
 public function cities($order="name")
 {
  if(!($this->cities instanceof mr_list))
  	$this->cities = ws_geo_city::several("country='$this->code'", $order);
    
  return $this->cities;
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
 	return $this->arr["region"] ? ws_geo_region::factory($this->arr["region"]) : null;
 }
 
/**
 * Регионы, привязанные к этой стране
 *
 * @param string $order
 * @return mr_list
 */
 public function regions($order="title")
 {
 	return ws_geo_region::several("country='".$this->code."'", $order);
 }
 
/**
 * Селектор страны для форм
 *
 * @param string $selectName="country"
 * @param string[opt] $selectedCODE
 * @return string
 */
 static public function form_select($selectName="country", $selectedCODE=null)
 {
   $r = mr_sql::qw("SELECT * FROM ".self::sqlTable);

   $arr = array();  
   while($f = mr_sql::fetch($r, mr_sql::assoc)) 
   {
   	$arr[] = $f;
   	self::factory($f["code"], $f);
   }
   
   $return = "<select name=\"$selectName\"><option value=''></option>";
   
   foreach($arr as $c)
   {
   	$return .= "<option value=\"".$c["code"]."\"".($c["code"]==$selectedCODE?" selected='yes'":"").">".$c["name"]."</option>";
   }
   
   $return.= "</select>";
   
   return $return;
 }
 
/**
 * Загрузка разом множества стран
 *
 * @param string|array $where
 * @param string $order="name"
 * @return mr_list
 */
 static public function several($where, $order="name")
 {
  if(is_array($where)) $where = "code IN ('".join("','", $where)."')";
  $r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE $where ORDER BY $order");
  
  $ids = array();
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["code"];
  	self::factory($f["code"], $f);
  }
  
  return new mr_list(__CLASS__, $ids);
 }
 
/**
 * Magic function
 *
 * @param string $name
 * @return string
 */
 public function __get($name)
 {
 	return $this->arr[$name];
 }
 
 public function code()
 {
 	return $this->code;
 }
	
	}
?>