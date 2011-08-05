<?php class tpl_page_free extends tpl_page {

	static protected $current, $curr_params;
	
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$page = mr_sql::fetch(array("SELECT * FROM mr_site_freepages WHERE url=? AND site IN(?, '')", $filename, mr::site()), mr_sql::obj);
 	if(is_object($page))
 	{
 		$prev_current = self::$current;
 		self::$current =& $this;
 		
 		if($page->title) $this->title = $page->title;
 		if($page->layout) $this->layout = $page->layout;
 		if($page->keywords) $this->keywords = $page->keywords;
 		if($page->description) $this->description = $page->description;
 		if($page->css) $this->css = explode(",", $page->css);
 		if($page->head) $this->head = $page->head;
 		
 		switch($page->type)
 		{
 			case "xml":
 				
 				$x = new mr_xml_fragment;
 				$x->loadXML($page->content);
 				if($page->xsltransform) $x->loadTransform($page->xsltransform);
 				$this->content = $x->realize();
 				
 				break;
 			case "php":
 				
 				ob_start();
 				eval($page->content);
 				$this->content = ob_get_contents();
 				ob_end_clean();
 				
 				break;
 			case "html":
 				
 				$this->content = $page->content;
 				
 				break;
 		}
 		
 		self::$current = $prev_current;
 		
 	} else throw new ErrorPageException(404, "Страничка не найдена");
 }
 
 static public function set_title($title)
 {
  self::$current->title = $title;
 }
 
 static public function set_layout($layout)
 {
  self::$current->layout = $layout;
 }
 
 static public function set_head($head)
 {
  self::$current->head = $head;
 }
 
 static public function set_css($link)
 {
  self::$current->css[] = $link;
 }
 
 static public function set_keywords($keywords)
 {
  self::$current->keywords = $keywords;
 }
 
 static public function set_description($descr)
 {
  self::$current->description = $descr;
 }
 
 static public function get_param($name)
 {
  return @self::$current->params[$name];
 }
	}
?>