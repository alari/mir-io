<?php class tpl_layout_ws_print extends tpl_layout {

	protected $css_prefix = "http://mir.io/style/css/", $css=array();
			
	public function __construct(i_tpl_page &$page)
	{
		$this->css = array("default.css");
		
		$this->page = $page;
		
		$this->css = array_merge($this->css, $page->css());
	}
	
 public function realize()
 {
 	ob_start();?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <meta name="Robots" value="NOINDEX;FOLLOW"/>
  <title><?=$this->page->title()?></title>
  <?foreach($this->css as $href){?><link rel="stylesheet" type="text/css" href="<?=$this->css_prefix.$href?>" /><?}?>
  <link rel="SHORTCUT ICON" href="/style/ico/mir.ico"/>
  <style type="text/css">
 	a{color:#111 !important}
  </style>
 </head>
 <body>
 
<div id="content" style="width:17cm;margin:auto">
	<?$this->content();?>
</div>

 </body>
</html>
<?
	$c = ob_get_contents();
	ob_end_clean();
	return $c;
 }
  
 protected function content()
 {
 	echo $this->page->content();
 }
	
	}
?>
