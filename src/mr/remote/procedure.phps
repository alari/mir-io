<?php class mr_remote_procedure {
		
	protected $name, $module, $params = array(),
	$isCallable = null, $methodReflection = null,
	$login, $pwd,
	$result, $isInvoked=false;
	
 public function __construct(SimpleXMLElement $xml, $login, $pwd)
 {
 	$this->login = $login;
 	$this->pwd = $pwd;
 	
 	$this->name = $xml["name"];
 	$this->module = @$xml["module"];
 	
 	$params = $xml->xpath("//param");
 	foreach($params as $p) $this->params[@$p["name"]?$p["name"]:null] = (string)$p;
 }
 
 /**
  * Checks if this module has this method and it's able to be called.
  * 
  * This method must be public and static, it first parameter must be an array,
  * it must have one or three parameters, other two is login and password, if needed.
  * Doc comment must include "* @mr:remote\n" substring
  *
  * @return bool
  */
 public function isCallable()
 {
 	if(!empty($this->isCallable)) return $this->isCallable;
 	
 	if(!class_exists(str_replace(" ", "_", $this->module), true)) return $this->isCallable=false;
 	
 	$refl = new ReflectionClass($this->module);
 	if(!$refl->hasMethod(str_replace(" ", "_", $this->name))) return $this->isCallable=false;
 	
 	$m = $refl->getMethod(str_replace(" ", "_", $this->name));
 	if(!$m->isPublic() || !$m->isStatic()) return $this->isCallable=false;
 	
 	$n = $m->getNumberOfParameters();
 	if($n!=1 && $n!=3) return $this->isCallable=false;
 	
 	if(!strpos($m->getDocComment(), "* @mr:remote\n")) return $this->isCallable=false;
 	
 	$params = $m->getParameters();
 	
 	/* @var $p ReflectionParameter */
 	
 	$p = $params[0];
 	if(!$p->isArray()) return $this->isCallable=false;
 	
 	$this->isCallable = true;
 	$this->methodReflection = $m;
 	
 	return true;
 }
 
 /**
  * Array of passed parameters
  *
  * @return array
  */
 public function getParams()
 {
 	return $this->params;
 }
 
 /**
  * Module name
  *
  * @return string
  */
 public function getModule()
 {
 	return $this->module;
 }
 
 /**
  * Procedure name
  *
  * @return string
  */
 public function getName()
 {
 	return $this->name;
 }
 
 /**
  * Invokes the callable, if available. Returns false on fail.
  *
  * @return bool
  */
 public function invoke()
 {
 	$this->isInvoked = true;
 	
 	if(!$this->isCallable())
 	{
 		$this->result = "<error>Procedure not implemented on server</error>";
 		return false;
 	}
 	
 	try {
	 	if($this->methodReflection->getNumberOfParameters()==1)
	 		$this->result = $this->methodReflection->invoke($this->params);
	 	else $this->result = $this->methodReflection->invoke($this->params, $this->login, $this->pwd);
 	} catch (Exception $e) {
 		$this->result = "<error>Procedure call failed on server</error>";
 		return false;
 	}
 	
 	return true;
 }
 
 /**
  * Result XML to return client
  *
  * @return unknown
  */
 public function returnXML()
 {
 	if(!$this->isInvoked) $this->invoke();
 	
 	return "<procedure name=\"$this->name\" module=\"$this->module\">$this->result</procedure>";
 }
	
	}