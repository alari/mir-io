<?php class tpl_page_libro_events extends tpl_page_evsphere implements i_tpl_page_submenu  {
		
public function __construct($filename="", $params="")
 {
 	$this->sphere = "libro";
 	$this->page_title = "События в <a href=\"/\">Литературной Сфере</a>";
 	$this->title = "События в Литературной Сфере";
 	
 	$this->direct = $params[1];
 	
 	if($this->direct == "events" || !$this->direct) $this->descr = "Все события сферы";
 	else {
 		$this->descr = "Направление: ".ws_comm::$org_directs[$this->direct];
 		$this->title .= ", ".ws_comm::$org_directs[$this->direct];
 	}
 	
 	parent::__construct($filename, $params);
 }	
 
 public function p_submenu()
 {
 	$ret = array();
 	
 	$ret[mr::host("libro")] = "Литературная сфера";
 	$ret[mr::host("libro")."/list.xml"] = "Новые произведения";
 	$ret[mr::host("libro")."/resp.xml"] = "Отзывы";
 	$ret[mr::host("libro")."/comms.xml"] = "Сообщества";
 	$ret[mr::host("libro")."/events.xml"] = "События";
 	$ret[mr::host("libro")."/reader.xml"] = "Для читателей";
 	
 	return $ret;
 }
	}
?>