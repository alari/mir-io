<?php abstract class x implements i_xmod {	
	static protected function call($x, $class)
	{
		$x = str_replace("/", "_", $x);
		
		$r = new ReflectionClass($class);
		if($r->hasMethod($x))
			return call_user_func(array($class, $x));
		return false;
	}
}
	?>