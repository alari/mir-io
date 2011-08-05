<?php class ws_lang extends mr_abstract_change {
	
	static private $objs = array();
	
/**
 * Factory project
 *
 * @param string $code
 * @param array[opt] $arr
 * @return ws_lang
 */
 static public function factory($code, $arr=false)
 {
   $code = strtolower($code);
   if(!(@self::$objs[$code] instanceof self) )
   	self::$objs[$code] = new self($code, $arr);
   return self::$objs[$code];
 }
 private function __construct($code, $arr)
 {
 	$this->id = $code;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch(array("SELECT * FROM mr_languages WHERE id=?", $code), mr_sql::assoc);
 }
 
 /**
  * Et al
  *
  * @param bool $available=false
  * @return mr_list
  */
 static public function several($available=false)
 {
  $r = mr_sql::query("SELECT * FROM mr_languages".($available?" WHERE available='yes'":""));
  $ids = array();
  while($f = mr_sql::fetch($r, mr_sql::assoc))
  {
  	$ids[] = $f["id"];
  	self::factory($f["id"], $f);
  }
  return new mr_list(__CLASS__, $ids);
 }
 
 /**
  * Ссылка на доступную языковую версию или название языка
  *
  * @return string
  */
 public function link()
 {
  return $this->arr["available"]=="yes"?'<a href="http://'.$this->id.'.mirari.ws/">'.$this->arr["name"].'</a>':$this->arr["name"];
 }
 public function __toString()
 {
  return $this->link();
 }
	
	}
?>