<?php class tpl_layout_def_3col extends tpl_layout {
	
	protected $css_prefix = "/style/css/";
	
 public function realize()
 {
 	ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
 <head>
  <title><?=$this->page->title()?></title>
  <link rel="stylesheet" type="text/css" href="<?=$this->css_prefix?>3col.css" />
  <?foreach($this->page->css() as $href){?><link rel="stylesheet" type="text/css" href="<?=$this->css_prefix.$href?>" /><?}?>
  <?=$this->page->head()?>
 </head>
 <body xmlns:mr="http://www.mirari.ru">
 
<div id="container">
<div id="header"><?$this->col_header();?></div>
<div id="wrapper">
<div id="content">
<?$this->col_content();?>
</div>
</div>
<div id="left-col">
<?$this->col_left();?>
</div>
<div id="right-col">
<?$this->col_right();?>
</div>
<div id="footer"><?$this->col_footer();?></div>
</div>
 
  <?$this->page->content()?>
 </body>
</html>
<?
	$c = ob_get_contents();
	ob_end_clean();
	return $c;
 }
 
 protected function col_left()
 {
?>
<p><strong>2) Navigation here.</strong> long long fill filler very fill column column silly filler very filler fill fill filler text fill very silly fill text filler silly silly filler fill very make fill column text column very very column fill fill very silly column silly silly fill fill long filler </p>
<?
 }
 
 protected function col_right()
 {
?>
<p><strong>3) More stuff here.</strong> very text make long silly make text very very text make long filler very make column make silly column fill silly column long make silly filler column filler silly long long column fill silly column very </p>
<?
 }
 
 protected function col_header()
 {
?>
<h1>Header</h1>
<?
 }
 
 protected function col_footer()
 {
?>
<p>Here it goes the footer</p>
<?
 }
 
 protected function col_content()
 {
?>
<p><strong>1) Content here.</strong> column long long column very long fill fill fill long text text column text silly very make long very fill silly make make long make text fill very long text column silly silly very column long very column filler fill long make filler long silly very long silly silly silly long filler make column filler make silly long long fill very.</p>
<p>very make make fill silly long long filler column long make silly silly column filler fill fill very filler text fill filler column make fill make text very make make very fill fill long make very filler column very long very filler silly very make filler silly make make column column </p>
<p>fill long make long text very make long fill column make text very silly column filler silly text fill text filler filler filler make make make make text filler fill column filler make silly make text text fill make very filler column very </p>
<p>column text long column make silly long text filler silly very very very long filler fill very fill silly very make make filler text filler text make silly text text long fill fill make text fill long text very silly long long filler filler fill silly long make column make silly long column long make very </p>
<?
 }
	
	}
?>