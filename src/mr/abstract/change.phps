<?php
	abstract class mr_abstract_change {
 protected $arr=array(), $changed=array(), $id=0;
 
 public function __set($name, $value)
 {
 	if(isset($this->arr[$name])) return $this->changed[$name] = $value;
 	return null;
 }
 
 public function __get($name)
 {
 	if(isset($this->changed[$name])) return $this->changed[$name];
 	if(isset($this->arr[$name])) return $this->arr[$name];
 	return null;
 }
 
 public function save()
 { 	
	if(count($this->changed) && $this->id)
 	{
 		$params=array("UPDATE ".constant(get_class($this)."::sqlTable")." SET ".join("=?, ", array_keys($this->changed))."=? WHERE id=? LIMIT 1");
 		$params = array_merge($params, array_values($this->changed));
 		$params[] = $this->id;
 		call_user_func_array(array("mr_sql", "qw"), $params);
 		if(mr_sql::affected_rows())
 		{
 		 foreach ($this->changed as $k=>$v) $this->arr[$k] = $v;
 		 $this->changed = array();
 		 return 1;
 		}
 	}
 	return 0;
 }
 
/**
 * ID
 *
 * @return int
 */
 public function id()
 {
 	return $this->id;
 }
	}
?>