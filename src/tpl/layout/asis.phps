<?php class tpl_layout_asis extends tpl_layout {
	
 public function realize()
 {
 	ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
 <head>
  <title><?=$this->page->title()?></title>
  <meta name="keywords" content="<?=$this->page->keywords()?>" />
  <meta name="description" content="<?=$this->page->description()?>" />
  <?foreach($this->page->css() as $href){?><link rel="stylesheet" type="text/css" href="<?=$href?>" /><?}?>
  <?=$this->page->head()?>
 </head>
 <body xmlns:mr="http://www.mirari.ru">
  <?=$this->page->content()?>
 </body>
</html>
<?
	$c = ob_get_contents();
	ob_end_clean();
	return $c;
 }
	
	}
?>