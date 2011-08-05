<?php class x_ws_ajax_read extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function out($pid=false)
 {
 	$pid = (int)($pid ? $pid : (int)$_GET["pub"]);
 	
 	$item = ws_libro_pub::factory($pid);
 	if(!$item->size)
 		die("Text not found");
 	
 	mr::uses("tpl");
 		
 	$pub = new tpl_page_libro_read("", array(1=>$pid, "-resp"=>"no", "-author"=>"no", "-control"=>"no"));
 	
 	$p_page = '<div align="right"><i><a href="'.$item->href().'">Страничка произведения</a></i></div>';
 	
 	$item->fullItem()->currentStat(true);
 	
 	echo $p_page, $pub->content(), $p_page;
 }
 
 static public function sections()
 {
  $type = $_POST["type"];
  if( !in_array($type, array("prose", "stihi", "article")) ) die("Undefined type");
  
  $secs = ws_libro_pub_sec::several("type='$type'");
  
?>

<center><form method="post" action="/x/ajax-read/find">
 <select multiple="multiple" name="secs[]" size="<?=(count($secs)>8?8:count($secs))?>">
  <?foreach($secs as $s){?><option value="<?=$s->id()?>"><?=$s->title?></option><?}?>
 </select>
 <br/>
 <input type="hidden" name="type" value="<?=$type?>"/>
 <input type="button" onclick="javascript:mr_Ajax_Form($(this).getParent(),{update:$('read-text')})" value="Подобрать"/>
</form></center>

<?
 }
 
 static public function find()
 {
  $secs = $_POST["secs"];
  foreach($secs as &$s) $s = is_numeric($s) ? (int)$s : null;
  
  $type = $_POST["type"];
  if( !in_array($type, array("prose", "stihi", "article")) ) die("Undefined type");
  
  do {
  	$p = ws_libro_pub::several(count($secs) ? "section IN (".join(",", $secs).")" : "type='$type'", 1, 0, "RAND()");
  	$p = $p[0];
  } while(!$p->is_showable());
  
  self::out( $p->id() );
 }
 
	}
?>