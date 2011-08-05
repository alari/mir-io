<?php
	abstract class mr_abstract_change2 {
 protected $arr=array(), $merged=null, $changed=array(), $id=0;
  
 /**
 * magic function
 *
 * @param string $name
 * @return string
 */
 public function __get($name)
 {
 	if(isset($this->changed[$name])) return $this->changed[$name];
 	if(isset($this->arr[$name])) return $this->arr[$name];
 	if(!is_array($this->merged))
 		$this->merge();
 	return @$this->merged[$name];
 }
 
/**
 * magic function
 *
 * @param string $name
 * @param string $value
 * @return string
 */
 public function __set($name, $value)
 {
 	return $this->changed[$name] = $value;
 }
 
/**
 * Сохраняет изменения, сделанные с помощью __set()
 *
 * @return bool
 */
 public function save()
 {
 	if(!count($this->changed)) return false;
 	
 	$arrtable = array();
 	$mergetable = array();

 	foreach($this->arr as $k=>&$v) if(!isset($v)) $v = false;
 	
 	foreach($this->changed as $k=>$v)
 		if(isset($this->arr[$k]))
 			$arrtable[$k] = $v;
 			
 	if(count($arrtable) < count($this->changed))
 	{
 		if(!is_array($this->merged))
 			$this->merge();
	 	foreach($this->merged as $k=>$v)
	 	{
	 		if(isset($this->changed[$k]))
 				$mergetable[$k] = $this->changed[$k];
	 	}
 	}
 	
 	if(count($arrtable))
 	{
 		$params=array("UPDATE ".constant(get_class($this)."::sqlTable")." SET ".join("=?, ", array_keys($arrtable))."=? WHERE id=? LIMIT 1");
 		$params = array_merge($params, array_values($arrtable));
 		$params[] = $this->id;
 		call_user_func_array(array("mr_sql", "qw"), $params);
 	}
 	
 	if(count($mergetable))
 	{
 		$params=array("UPDATE ".constant(get_class($this)."::sqlTableMerge")." SET ".join("=?, ", array_keys($mergetable))."=? WHERE id=? LIMIT 1");
 		$params = array_merge($params, array_values($mergetable));
 		$params[] = $this->id;
 		call_user_func_array(array("mr_sql", "qw"), $params);
 	}
 	
 	return true;
 }
 
/**
 * Айди записи
 *
 * @return int
 */
 public function id()
 {
 	return $this->id;
 }
 
/**
 * Настоятельное дополнение
 *
 */
 public function merge()
 {
 	if(!is_array($this->merged))
 		$this->merged = mr_sql::fetch("SELECT * FROM ".constant(get_class($this)."::sqlTableMerge")." WHERE id=".$this->id, mr_sql::assoc);
 }
	}
?>
