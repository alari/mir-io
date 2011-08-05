<?php class mr_list implements Iterator, ArrayAccess, Countable{
	protected $class, $ids=array();
	
	public function __construct($class, array $ids=Array())
	{
		$this->class = $class;
		$this->ids = $ids;
	}
	
/**
 * Возвращает массив ключей списка
 *
 * @return array
 */
	public function ids()
	{
		return $this->ids;
	}
	
	protected function getObj($id)
	{
		return $id?call_user_func(array($this->class, "factory"), $id):null;
	}
	
	public function rewind()
	{
        reset($this->ids);
    }

    public function current()
    {
    	$cur = current($this->ids);
        return $cur ? $this->getObj(current($this->ids)) : false;
    }

    public function key()
    {
        return key($this->ids);
    }

    public function next()
    {
        return $this->getObj(next($this->ids));
    }

    public function valid()
    {
        return $this->current() !== false;
    }
    
    public function count()
    {
    	return count($this->ids);
    }
    
    public function __call($m, $a)
    {
    	$r = "";
    	foreach($this->ids as $id)
    	{
    		$r .= call_user_method_array($m, $this->getObj($id), $a);
    	}
    	return $r;
    }
    
    public function __toString()
    {
    	ob_start();
    	foreach($this->ids as $id) echo $this->getObj($id);
    	return ob_get_clean();
    }
    
/**
 * Проверяет
 *
 * @param int $offset
 * @return bool
 */
 public function offsetExists($offset)
 {
 	return isset($this->ids[$offset]);
 }
 
/**
 * Возвращает
 *
 * @param int $offset
 * @return string
 */
 public function offsetGet($offset)
 {
 	if(!$this->offsetExists($offset)) return false;
 	return $this->getObj($this->ids[$offset]);
 }

/**
 * Устанавливает, если может
 *
 * @param int $offset
 * @param object $obj
 * @return true
 */
 public function offsetSet($offset, $obj)
 {
 	if($obj instanceof $this->class)
 	{
 		if(in_array($obj->id(), $this->ids)) return;
 		if($offset)
 			$this->ids[$offset] = $obj->id();
 		else $this->ids[] = $obj->id();
 	}
 }

/**
 * Удаляет
 *
 * @param int $offset
 */
 public function offsetUnset($offset)
 {
 	unset($this->ids[$offset]);
 }
 
/**
 * Сортирует массив пользовательской функцией
 *
 * @param cmp_fnc $fnc
 * @return bool
 */
 public function usort($fnc)
 {
 	return usort($this->ids, $fnc);
 }

} ?>