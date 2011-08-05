<?php class x_ws_comm_adm_adv extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function create()
 {
 	$comm = ws_comm::factory((int)$_POST["comm"]);
 	self::check_rights($comm);
 	
 	$url = mr_text_string::remove_excess($_POST["url"]);
 	$link = mr_text_string::remove_excess($_POST["link"]);
 	$comment = mr_text_string::remove_excess($_POST["comment"]);
 	
 	$adv = ws_comm_adv::create($comm, $url, $link, $comment);
 	
 	if($adv){
 		throw new RedirectException($comm->href("adm/adv.xml"), 4, "Рекламное сообщение успешно создано: ".((string)$adv));
 	} else {
 		throw new RedirectException($comm->href("adm/adv.xml"), 4, "Создать новое рекламное сообщение не удалось. У сообщения обязательно должны быть адрес и текст ссылки, квоты на рекламные сообщения не должны быть превышены.", "Ошибка");
 	}
 }
 
 static public function delete()
 {
 	$adv = ws_comm_adv::factory((int)$_GET["adv"]);
 	
 	self::check_rights($comm = $adv->comm());
 	$adv->delete();
 	
 	throw new RedirectException($comm->href("adm/adv.xml"));
 }
 
 
 static private function check_rights($comm, $advanced=false)
 {
	if(!$comm->name)
		throw new ErrorPageException("Сообщество не найдено", 404);
	if(!ws_self::is_allowed($advanced?"comm_control_advanced":"comm_control", $comm->id()))
		throw new ErrorPageException("У Вас нет прав на контроль этого сообщества", 403);
 }

	}
?>