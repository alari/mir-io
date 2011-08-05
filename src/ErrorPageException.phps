<?php class ErrorPageException extends Exception {
	
	protected $page;
	static protected $codes=array();
	
 public function __construct($message, $code)
 {
 	$code = (int)$code;
 	switch($code)
 	{
 		case 401:
			Header("HTTP/1.1 401 Need Authorization");
		break;
 	 	case 403:
			Header("HTTP/1.1 403 Access Denied");
		break;
		case 404:
			Header("HTTP/1.1 404 File Not Found");
		break;
		case 500:
			Header("HTTP/1.1 500 Internal Server Error");
		break;
 	}
 	
 	if(!@self::$codes[$code])
 	{
 		$this->page = new tpl_page_free("ErrorPageException.xml", array("code"=>$code, "message"=>$message));
 	
 		parent::__construct($message, $code);
 	} else $this->page = false;
 }
		
 public function __toString()
 {
  return $this->page ? $this->page->layout()->__toString() : "Error dubbing ".$code;
 }
	}?>