<?php class mr_remote_request extends mr_remote_http {
	
	private $http;
	private $xml = '<?xml version="1.0" encoding="UTF-8"?>
<request xmlns="http://www.mirari.ru" version="0.9">';
	
	private $encoding = "UTF-8";
	
	private $modules = array();
	private $procedures = array();
	
/**
 * Конструктор
 *
 * @param string $url Включая http и иже с ним
 * @param string $encoding См. iconv
 */
 public function __construct($url, $encoding="UTF-8")
 {
 	parent::__construct($url);
 	$this->encoding = $encoding;
 }
 
 /**
  * Добавляет вызов процедуры, если нужно -- в модуле
  *
  * @param string $proc_name
  * @param string $module
  * @param array $params ((string)name => (string)value)
  * @throws Exception If module, procedure or param name is wrong
  * @return mr_remote_request
  */
 public function addProcedure($proc_name, $module, $params=array())
 { 	 		
 	if(!preg_match("/^[a-z][_a-z0-9]+$/i", $proc_name))
 		throw new Exception("Procedure name '$proc_name' is not valid for ".__CLASS__);
 		
 	if(!preg_match("/^[a-z][ a-z0-9]+$/i", $module))
 		throw new Exception("Module name '$module' is not valid for ".__CLASS__);
 
 	foreach($params as $k=>$v)
 	if(!is_int($k) && !preg_match("^/[_a-z][ _a-z0-9]+$/i", $k))
 		throw new Exception("Param name '$k' is not valid for ".__CLASS__);
 	
 	$this->procedures[] = array($proc_name, $module, $params);
 		
 	return $this;
 }
 
 /**
  * Добавляет авторизационные данные, если нужно
  *
  * @param string $login
  * @param string $pwd
  * @return mr_remote_request
  */
 public function authorize($login, $password)
 {
 	$this->setCookie("mr:login", $login);
 	$this->setCookie("mr:pwd", $password);
 	return $this;
 }
 
 /**
  * Отправляет запрос на удалённый сервер, парсит ответ от него
  *
  * @return mr_remote_request
  */
 public function send()
 {
 	foreach($this->procedures as $proc) $this->xml .= $this->handle_procedure($proc);
 	 	
 	$this->xml .= "</request>";
 	if($this->encoding != "UTF-8") $this->xml = iconv($this->encoding, "UTF-8", $this->xml);
 	
 	$this->setPostField("mr:xml", $this->xml);
 	$this->setMethodPost();
 	
 	return $this->request()->body();
 }
 
 /**
  * Подгатавливает xml для вызова процедуры
  *
  * @access private
  * @param array $proc
  * @return string
  */
 private function handle_procedure(array $proc)
 {
 	list($name, $module, $params) = $proc;
 	$return = "<procedure name=\"$name\" module=\"$module\"";
 	
 	if(count($params))
 	{
 		$return .= ">";
 		foreach($params as $name=>$value)
	 	{
	 		$return .= "<param".($name?" name=\"$name\"":"").">";
	 		$return .= htmlspecialchars((string)$value);
	 		$return .= "</param>";
	 	}
	 	$return .= "</procedure>";
 	} else $return .= "/>";
 	
 	return $return;
 }
	
	}