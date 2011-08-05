<?php class tpl_page_blank extends tpl_page {
	
	public function appendIterator(Iterator $a)
	{
		foreach($a as $k=>$v) if(isset($this->$k)) $this->$k = $v;
	}
	
	}
?>