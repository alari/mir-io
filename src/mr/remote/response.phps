<?php class mr_remote_response implements i_initiate {
	
 static private $login, $pwd, $body, $xml, $procedures = array();
	
 /**
  * Entry point. Parses the request POST and COOKIE data.
  *
  * @param array $ini
  */
 static public function init($ini)
 {
 	self::$login = isset($_COOKIE["mr:login"])?$_COOKIE["mr:login"]:null;
 	self::$pwd = isset($_COOKIE["mr:pwd"])?$_COOKIE["mr:pwd"]:null;
 	
 	self::$body = $_POST["mr:xml"];
 	self::$xml = new SimpleXMLElement(self::$body);
 	
 	$procs = self::$xml->xpath("//procedure");
 	foreach($procs as $proc) self::$procedures[] = new mr_remote_procedure($proc, self::$login, self::$pwd);
 }
	
 /**
  * Returns the login client use
  *
  * @return string
  */
 static public function getLogin()
 {
    return self::$login;
 }
 
 /**
  * Returns the password client use
  *
  * @return string
  */
 static public function getPassword()
 {
 	return self::$pwd;
 }
 
 /**
  * Returns array of procedures client wish to call
  *
  * @return mr_remote_procedure[]
  */
 static public function getProcedures()
 {
 	return self::$procedures;
 }
 
 /**
  * Invokes all procedures, creates an XML doc to return to client
  *
  * @return string
  */
 static public function getReturnXML()
 {
 	$xml = "<?xml version='1.0' encoding='UTF-8'?>\n<result>";
 	foreach(self::$procedures as $p) $xml .= $p->returnXML();
 	$xml .= "</result>";
 	return $xml;
 }
 
 /**
  * Parse all data, call all callables, return to client, exit
  *
  */
 static public function process()
 {
 	print self::getReturnXML();
 	exit;
 }
	
	}