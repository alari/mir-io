<?php class x_ws_own_msgs extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function send()
 {
 	if(!ws_self::ok())
 		throw new ErrorPageException("Потеряна авторизация.", 403);
 		
 	$to = ws_user::getByLogin($_POST["to"]);
 	if(!$to || !($to instanceof ws_user) || !@$to->login || $to->id() == ws_user::anonime)
 		throw new RedirectException(mr::host("own")."/msg/new.xml", 5, "Адресат не найден! Проверьте правильность написания логина адресата; учтите, что логин не всегда идентичен с ником пользователя.");
 		
 	$title = mr_text_string::remove_excess( trim( $_POST["title"] ) );
 	if(!$title) $title = "New Private Message";
 	
 	$msg = trim($_POST["msg"]);
 	if(!$msg)
 		throw new RedirectException(mr::host("own")."/msg/new.xml", 5, "Вы отправляете пустое сообщение. Это нелогично. Сообщение не будет отправлено.");
 		
 	$tr = new mr_text_trans($msg);
 	$tr->t2x( mr_text_trans::plain );
 		
 	$msg = new ws_user_msg_item(false,
 		array(
 			"owner"=>$to->id(),
 			"target"=>ws_self::id(),
 			"box"=>"inbox",
 			"title"=>$title,
 			"flagged"=>"no",
 			"readen"=>"no",
 			"content"=>$tr->finite(),
 			"size"=>$tr->getAuthorSize()
 			)
 		);
 	$msg->save();
 	$own = clone $msg;
 	$own->save();
 	 	
 	throw new RedirectException(mr::host("own")."/msg/", 5, "Сообщение для ".$to->link()." успешно доставлено.", "Спасибо!");
 }
 
 static public function list_action()
 {
	$ids = array();
 	foreach($_POST as $k=>$v) if($v == "yes" && strpos($k, "sg_") === 1)
 		$ids[] = substr($k, 4);
 		
	$page = (int)@$_POST["page"];
 	$onpage = (int)@$_POST["onpage"];
 	$box = @$_POST["box"];
 			
 	if(!in_array($box, array("inbox", "sent", "recycled", "flagged")))
 		$box = "inbox";
 	
 	if( !count($ids) )
 	 	throw new RedirectException(mr::host("own")."/msg/$box".($page>0?".page-$page":"").".xml", 5, "Вы не выбрали ни одного сообщения.", "Действие не произведено");
 	
 	$list = ws_user_msg_item::several_ids(ws_self::id(), $ids);
 	 	
 	// удаление сообщений
 	if($_POST["delete"])
 	{
 		$list->delete();
 		
 		if(count($onpage) == count($ids) && @$_POST["lastpage"]=="yes")
 			--$page;
 	
 		throw new RedirectException(mr::host("own")."/msg/$box".($page>0?".page-$page":"").".xml");
 	}
 		
 	// прочтение или нет
 	elseif($_POST["readen"] || $_POST["notreaden"])
 	{
 		$readen = $_POST["readen"] ? "yes" : "no";
 		$list->__call("__set", array("readen", $readen));
 		$list->save();
 		
 		throw new RedirectException(mr::host("own")."/msg/$box".($page>0?".page-$page":"").".xml");
 	}
 }
 
 static public function delete()
 {
 	$id = (int)$_GET["id"];
 	$msg = ws_user_msg_item::factory($id);
 	if($msg->owner()->id() != ws_self::id())
 		throw new ErrorPageException("Ошибка: попытка удаления чужого или несуществующего сообщения", 403);
 		
 	$msg->delete();
 	throw new RedirectException(mr::host("own")."/msg/", 5, "Сообщение успешно удалено");
 }
 
 static public function flag()
 {
 	$id = (int)$_POST["id"];
 	$msg = ws_user_msg_item::factory($id);
 	if($msg->owner()->id() != ws_self::id())
 		die("Ошибка");
 	$new = $msg->flagged=="yes"?"no":"yes";
 	$msg->flagged = $new;
 	$msg->save();
 	
 	echo $new=="yes"?"Убрать":"Поставить", " флажок";
 	exit;
 }
 
	}
?>