<?php class tpl_page_own_lib_drafts extends tpl_page_own_lib_inc implements i_tpl_page_rightcol {
	
public function __construct($filename="", $params="")
{
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";

	$this->title = "Ваши черновики";
 	
 	$this->content = "<h1>Ваши черновики</h1>";
 	
 	$this->css[] = "pub/anonce.css";
 	
 	$drafts = ws_libro_pub_draft::several(ws_self::id());
 	
 	ob_start();
	
 	tpl_fr_pubs::draftlist($drafts);
 	
  $this->content .= ob_get_clean();
}
	}