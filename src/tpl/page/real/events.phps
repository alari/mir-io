<?php class tpl_page_real_events extends tpl_page_evsphere {
		
public function __construct($filename="", $params="")
 {
 	$this->sphere = "real";
 	$this->title = "События на Самом Деле";
 	
 	$this->direct = $params[1];
 	
 	if($this->direct == "events" || !$this->direct) $this->descr = "Все события сферы";
 	else $this->descr = "Направление: ".ws_comm::$org_directs[$this->direct];
 	
 	parent::__construct($filename, $params);
 }	
	}
?>