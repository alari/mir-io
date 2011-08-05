<?php class x_ws_ajax_blog extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
	
 static public function note()
 {
 	tpl_fr_comment::add_form("/x/ajax-blog/note/add", "ws_blog_note", $_POST["parent"]);
 }

 static public function note_add()
 {
 	$content = trim($_POST["msg"]);

 	if( !ws_self::ok() && mr_security::spamFilter($content) )
 		die("<b>Вы не авторизованы. Сработал спам-фильтр.</b>");

 	$ev = ws_blog_item::factory((int)$_POST["parent"]);
 	if( !$ev || !$ev->can_add_note() )
 		die("<b>Вы не можете оставить отзыв на данное событие.</b>");

 	$rem_type = ws_user_remind::type_blog_resp;
 	if(ws_self::ok())
 		$rem_ch = ws_user_remind::change_sub( $rem_type, (int)$_POST["parent"], $_POST["remind"], $_POST["method"] );
 		
 	if(strlen($content)<8)
 	{
 		echo (
 			$rem_ch ?
 			"<b>Состояние Вашей подписки успешно изменено</b>" :
 			"<b>В отзыве обязана присутствовать содержательная часть.</b>"
 			 )."<br/>";
 		tpl_fr_comment::add("/x/ajax-blog/note", $ev->id(), false);
 		return;
 	}
 								
 	$r = $ev->addNote(ws_self::id(), $content );
 			
 	if($r->id()) 	
 	{
 		$r->notify_subscribers();
 			
 		tpl_fr_comment::out( $r );
 	} else echo "<b>Не удалось сохранить отзыв из-за программных неполадок.</b>";
 	tpl_fr_comment::add("/x/ajax-blog/note", $ev->id(), false);
 }
 
 static public function delete()
 {
 	$item = ws_blog_item::factory( (int)$_POST["id"] );
 	if($item->can_delete())
 	{
 		$item->delete();
 		echo "<b>Запись удалена</b>";
 	} else echo "<b>Нет прав на удаление записи</b>";
 }
	}
?>