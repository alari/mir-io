<?php class x_ws_ajax_comment implements i_xmod {
	
 static public function action($x)
 {
  list($class, $id, $x) = @explode("/", $x, 3);
  
  if(!is_subclass_of($class, "ws_abstract_comment")) self::err();
  
  /** @var $note ws_abstract_comment */
  $note = call_user_func(array($class, "factory"), $id);
  
  switch($x)
  {
  	case "hide":
  		if(!$note->can_hide()) self::err();
  		$note->hidden = $note->hidden=="yes"?"no":"yes";
  		$note->hidden_time = time();
  		$note->hidden_by = ws_self::id();
  		$note->save();
?>
<script>
$('c<?=$id?>').toggleClass('comment-hidden');
$('c<?=$id?>hide').innerHTML = '<?=($note->hidden=="yes"?"Показать":"Спрятать")?>';
</script>
<?
  	break;
  	
  	case "delete":
  		if(!$note->can_delete()) self::err();
  		$note->delete();
?>
<script>
$('c<?=$id?>').dispose();
</script>
<?
  	break;
  	
  	case "edit":
  		if(!$note->can_edit()) self::err();
  		
  		$cont = new mr_text_trans(trim($_POST["content"]));
  		$cont->t2x();
  		if(!$cont->getAuthorSize(4)) self::err();
  		
  		ws_attach::checkXML($note->content, ws_attach::decrement);
  		
  		$note->content = $cont->finite();
  		
  		ws_attach::checkXML($note->content, ws_attach::increment);
  		
  		$note->size = $cont->getAuthorSize();
  		$note->edit_time = time();
  		$note->edit_by = ws_self::id();
  		
  		$note->save();
  		
  		$fr = new mr_xml_fragment;
  		$fr->loadXML($note->content);
  		echo $fr;
  	break;
  	
  	case "edit/content":
  		if(!$note->is_showable()) self::err();
?>
<form method="post" action="/x/ajax-comment/<?=$class?>/<?=$id?>/edit" id="fe-<?=$id?>" onreset="javascript:comment_edit_refuse(<?=$id?>)">
	<center>
		<textarea name="content" cols="40" rows="12"><?=mr_text_trans::node2text($note->content)?></textarea>
		<br /><br />
		<input type="button" value="Сохранить изменения" onclick="javascript:$(this).disabled='yes';mr_Ajax_Form($('fe-<?=$id?>'),{update: $('c<?=$id?>content')});"/> &nbsp; <input type="reset" value="Отменить" />
	</center>
</form>
<?
  	break;
  }
  
  exit;
 }
 
 static protected function err()
 {
 	echo "<script>alert('Вы не имеете прав на совершение этого действия.');</script>";
 	exit;
 }
	
	}
?>