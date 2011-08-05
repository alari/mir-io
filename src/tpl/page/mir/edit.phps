<?php class tpl_page_mir_edit extends tpl_page_edit {
	
	static public function can_edit($page)
	{
		return true;
	}
	
	protected function canedit()
	{
		return self::can_edit($this->page);
	}
	
	protected function cancreate()
	{
		return self::can_create();
	}
 
 	static public function can_create()
 	{
 		return true;
 	}
	
	}
?>