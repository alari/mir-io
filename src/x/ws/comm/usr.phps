<?php class x_ws_comm_usr extends x implements i_xmod {
	
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
	
/**
 * Приём приглашения в сообщество
 *
 */
 static public function invite_apply()
 {
 	$id = (int)$_POST["id"];
 	$m = ws_comm_member::factory($id);
 	
 	if(!$m || !$m->status)
 		die("Ошибка: приглашение не найдено");
 	
 	if($m->user()->id() == ws_self::id() && $m->confirmed == "auth")
 	{
 		$m->confirmed = "yes";
 		echo $m->save() ? "Приглашение принято" : "Ошибка при принятии";
 	} else echo "Ошибка";
 }
 
/**
 * Аяксовое вступление или заявка от пользователя
 *
 */
 static public function apply()
 {
 	$comm = (int)$_POST["comm"];
 	if(!$comm || !($comm = ws_comm::factory($comm)) || !$comm->name) die("Сообщество не найдено");
 	
 	if(ws_self::is_member($comm->id()) || ws_self::is_member($comm->id())===0)
 		die("Вы уже связаны с сообществом ".$comm->link());
 		
 	switch($comm->apply_members)
 	{
 		case "no": die("Приём в сообщество закрыт");
 		case "yes": die( ws_comm_member::create($comm->id(), ws_self::id()) ? "Заявка создана и ожидает подтверждения" : "Ошибка при создании заявки" );
 		case "free": die( ws_comm_member::create($comm->id(), ws_self::id(), "yes") ? "Вы приняты в сообщество" : "Ошибка при создании заявки" );
 	}
 	die("Ошибка");
 }
 
/**
 * Выход пользователя из сообщества
 *
 */
 static public function reject()
 {
 	$comm = (int)$_POST["comm"];
 	if(!$comm || !($comm = ws_comm::factory($comm)) || !$comm->name) die("Сообщество не найдено");
 	
 	if(!ws_self::ok()) die("Вы не авторизованы.");
 	
 	$m = ws_comm_member::item(ws_self::id(), $comm->id());
 	if(!$m) die("Вас уже нет в сообществе");
 	if($m->status >= ws_comm::st_leader) die("Вы руководите этим сообществом. Нельзя покинуть его, не передав пост.");
 	
 	$m->delete();
 	
 	echo "Вы вышли из сообщества ".$comm->link();
 }
 
	}
?>