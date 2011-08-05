<?php class x_ws_ajax_event extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
	
 static public function note()
 {
 	tpl_fr_comment::add_form("/x/ajax-event/note/add", "ws_comm_event_note", $_POST["parent"]);
 }

 static public function note_add()
 {
 	$content = trim($_POST["msg"]);
 			
 	if( !ws_self::ok() && mr_security::spamFilter($content) )
 		die("<b>Вы не авторизованы. Сработал спам-фильтр.</b>");
 					
 	$ev = ws_comm_event_item::factory((int)$_POST["parent"]);
 	if( !$ev->can_add_note() )
 		die("<b>Вы не можете оставить отзыв на данное событие.</b>");
 				
 	$rem_type = ws_user_remind::type_comm_event_resp;
 	if(ws_self::ok())
 		$rem_ch = ws_user_remind::change_sub( $rem_type, (int)$_POST["parent"], $_POST["remind"], $_POST["method"] );
 		
 	if(strlen($content)<8)
 	{
 		echo (
 			$rem_ch ?
 			"<b>Состояние Вашей подписки успешно изменено</b>" :
 			"<b>В отзыве обязана присутствовать содержательная часть.</b>"
 			 )."<br/>";
 		tpl_fr_comment::add("/x/ajax-event/note", $ev->id(), false);
 		return;
 	}
 								
 	$r = $ev->addNote(ws_self::id(), $content );
 			
 	if($r->id()) 	
 	{
 		$r->notify_subscribers();
 			
 		tpl_fr_comment::out( $r );
 	} else echo "<b>Не удалось сохранить отзыв из-за программных неполадок.</b>";
 	tpl_fr_comment::add("/x/ajax-event/note", $ev->id(), false);
 }
 
 static public function adm()
 {
 	$ev = ws_comm_event_item::factory((int)$_POST["id"]);
 	
 	if(!$ev->is_showable()) die("Ошибка: недостаточно прав");
 	
 	echo "<ul>";

 	if($ev->can_edit()) echo "<li><a href=\"".$ev->comm()->href("ev-edit-".$ev->id().".xml")."\">Редактировать событие</a></li>";
 	if($ev->can_ch_vis()) echo "<li><a href=\"javascript:void(0)\" onclick=\"javascript:mr_Ajax({url:'/x/ajax-event/hide',data:{id:".$ev->id()."},update:$(this).getParent()}).send()\">".($ev->hidden=="yes"?"Раскрыть":"Спрятать")." событие</a></li>";
 	if($ev->can_close()) echo "<li><a href=\"javascript:void(0)\" onclick=\"javascript:mr_Ajax({url:'/x/ajax-event/close',data:{id:".$ev->id()."},update:$(this).getParent()}).send()\">".($ev->closed=="yes"?"Открыть":"Закрыть")." дискуссию</a></li>";
 	if($ev->can_delete()) echo "<li><a href=\"javascript:void(0)\" onclick=\"javascript:if(confirm('Вы уверены в своём желании необратимого удаления этого события?')) mr_Ajax({url:'/x/ajax-event/delete',data:{id:".$ev->id()."},update:$(this).getParent()}).send()\">Удалить событие</a></li>";
 	
 	echo "</ul>";
 }
 
 static public function delete()
 {
 	$ev = ws_comm_event_item::factory( (int)$_POST["id"] );
 	if(!$ev->can_delete())
 		die("<b>Недостаточно прав</b>");
 	$ev->delete();
 		die("<b>Событие благополучно удалено</b>");
 }
 
 static public function hide()
 {
 	$thread = ws_comm_event_item::factory( (int)$_POST["id"] );
 	if(!$thread->can_ch_vis())
 		die("<b>Недостаточно прав</b>");
 	$thread->hidden = $thread->hidden=="yes"?"no":"yes";
 	echo "<b>", $thread->save() ? "Видимость успешно изменена" : "Сохранить изменения не удалось", "</b>";
 }
 
 static public function close()
 {
 	$thread = ws_comm_event_item::factory( (int)$_POST["id"] );
 	if(!$thread->can_close())
 		die("<b>Недостаточно прав</b>");
 	$thread->closed = $thread->closed=="yes"?"no":"yes";
 	echo "<b>", $thread->save() ? "Статус дискуссии изменён" : "Сохранить изменения не удалось", "</b>";
 }
 
 static public function item_new()
 {
 	$col = ws_comm_event_sec::factory( $_POST["id"] );
 	if( !ws_self::ok() || !$col->can_add_item() )
 		throw new ErrorPageException("Недостаточно прав для совершения данного действия.", 403);
 		
 	$content = trim($_POST["content"]);
 	if(strlen($content)<20)
 		throw new RedirectException( $col->comm()->href("ev-add-".$col->id().".xml"), 5, "Вы не ввели содержимое события.", "Безуспешно" );
 	$title = mr_text_string::remove_excess( trim($_POST["title"]) );
 	if(!$title)
 		throw new RedirectException( $col->comm()->href("ev-add-".$col->id().".xml"), 5, "Вы не ввели заголовок события.", "Безуспешно" );
 		
 	$event = ws_comm_event_item::create( $col->comm()->id(), $col->id(), ws_self::id(), $content, $title );
 		
 	if( !$event->title )
 		throw new RedirectException( $col->comm()->href("ev-add-".$col->id().".xml"), 5, "Ошибка: не удалось произвести сохранение данных нового события.", "Безуспешно" );
 	
 	$event->description = mr_text_string::remove_excess( trim($_POST["description"]) );
 	$name = mr_text_string::remove_excess(trim($_POST["name"]));
 	if($name[0]=="-") $name = null;
 	if( !mr_text_string::no_ru_sp($name) ) $name = null;
 	
 	if($name)
 		$event->name = $name;
 		
 	$event->owner = $_POST["owner"];
 	
 	if($col->geo == "yes")
 		$event->city = (int)$_POST["city"];
 	
 	$event->save();
 	
 	throw new RedirectException( $event->href(), 5, "Новое событие успешно создано!" );
 }
 
 static public function item_save()
 {
  	$event = ws_comm_event_item::factory( $_POST["id"] );
 	if( !ws_self::ok() || !$event->can_edit() )
 		throw new ErrorPageException("Недостаточно прав для совершения данного действия.", 403);
 		
 	$content = trim($_POST["content"]);
 	if(strlen($content)<20)
 		throw new RedirectException( $col->comm()->href("ev-edit-".$col->id().".xml"), 5, "Вы не ввели содержимое события.", "Безуспешно" );
 	$title = mr_text_string::remove_excess( trim($_POST["title"]) );
 	if(!$title)
 		throw new RedirectException( $col->comm()->href("ev-edit-".$col->id().".xml"), 5, "Вы не ввели заголовок события.", "Безуспешно" );
 	$event->title = $title;
 	
 	$tr = new mr_text_trans($content);
 	$tr->t2x( mr_text_trans::prose );
 		
 	$event->content = $tr->finite();
 	$event->size = $tr->finite();
 	$ev->title = $title;
 	$event->description = mr_text_string::remove_excess( trim($_POST["description"]) );
 	$name = mr_text_string::remove_excess(trim($_POST["name"]));
 	if($name[0]=="-") $name = null;
 	if( !mr_text_string::no_ru_sp($name) ) $name = null;
 	
 	if($name)
 		$event->name = $name;
 		
 	$event->owner = $_POST["owner"];
 	
 	if($event->section()->geo == "yes" || $event->city)
 		$event->city = (int)$_POST["city"];
 	
 	$event->save();
 	
 	throw new RedirectException( $event->href(), 5, "Событие было успешно изменено." );
 }
	}
?>