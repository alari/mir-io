<?php abstract class tpl_layout implements i_tpl_layout {
	
	/*@var i_tpl_page $page*/
	protected $page;
	
	public function __toString()
	{
		return $this->realize();
	}
	
	//abstract public function realize();
	
	public function __construct(i_tpl_page &$page)
	{
		$this->page = $page;
	}
	
	public function __set($name, $value)
	{
		$this->page->$name = $value;
	}
	
	public function __get($name)
	{
		return $this->page->$name;
	}
	
	}
?>