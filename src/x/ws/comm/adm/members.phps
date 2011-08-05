<?php class x_ws_comm_adm_members extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
/**
 * Приём заявки на вступление в сообщество
 *
 */
 static public function apply()
 {
	self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
 		
 	$pid = $_POST["pid"];
 	
 	$m = ws_comm_member::factory($pid);
 	if($m->confirmed == "yes")
 		die($m->link()." уже действующий участник");
 	if($m->comm()->id() != $comm->id())
 		die("Ошибка: несовпадение сообществ");
 	
 	$m->confirmed = "yes";
 	$m->status = 1;
 	die( $m->save() ? $m->link()." &ndash; Принят" : "Ошибка при сохранении" );
 }
 
/**
 * Поставить участника куратором или наоборот
 *
 */
 static public function st()
 {
	self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
 		
 	$pid = (int)$_POST["pid"];
 	$st = (int)$_POST["st"];
 	
 	$m = ws_comm_member::factory($pid);
 	if($m->status >= ws_comm::st_leader)
 		die($m->link()." нельзя исключить без замены");
 	if($m->comm()->id() != $comm->id())
 		die("Ошибка: несовпадение сообществ");
 	if($st <= 0 || $st >= ws_comm::st_leader)
 		die("Недопустимое изменение статуса");
 		
 	$m->status = $st;
 	
 	die( $m->save() ? $m->link()." &ndash; теперь ".ws_comm::mem_status($st) : "Ошибка при сохранении" );
 }
 
/**
 * Поставить куратора лидером
 *
 */
 static public function leader()
 {
	self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
 		
 	$pid = (int)$_POST["pid"];
 	
 	$m = ws_comm_member::factory($pid);
 	if($m->status >= ws_comm::st_leader)
 		die($m->link()." уже является лидером");
 	if($m->comm()->id() != $comm->id())
 		die("Ошибка: несовпадение сообществ");
 	if($m->status < ws_comm::st_curator || $m->confirmed != "yes")
 		die("Недопустимое изменение статуса");
 		
 	$leader = ws_comm_member::several($comm->id(), ws_comm::st_leader);
 	if(!count($leader))
 		die("Возможно, у этого сообщества нет лидера. Обратитесь к Координатору.");
 	else $leader = $leader[0];
 		
 	$m->status = ws_comm::st_leader;
 	$leader->status = ws_comm::st_curator;
 	
 	$m->save();
 	$leader->save();
 	
 	die( $m->link()." &ndash; новый лидер сообщества" );
 }
 
/**
 * Исключение пользователя
 *
 */
 static public function reject()
 {
	self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
 		
 	$pid = (int)$_POST["pid"];
 	
 	$m = ws_comm_member::factory($pid);
 	if($m->status >= ws_comm::st_leader)
 		die($m->link()." нельзя исключить без замены");
 	if($m->comm()->id() != $comm->id())
 		die("Ошибка: несовпадение сообществ");
 		
 	$m->delete();
 	
 	die( "Исключён" );
 }
 
/**
 * Списки для контрольной панели -- приглашения, кураторы, участники, претенденты
 * с соответствующим функционалом
 *
 */
 static public function mlist()
 {
	self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
 		
 	$st = $_POST["st"];
 	$act = "";
 	
 	switch($st)
 	{
 		case ws_comm::st_curator: $act = "curators"; $mms = ws_comm_member::several($comm->id(), $st, "yes"); break;
 		case ws_comm::st_member: $act = "members"; $mms = ws_comm_member::several($comm->id(), $st, "yes"); break;
 		case "auth": $act = "auth"; $mms = ws_comm_member::several($comm->id(), null, "auth"); break;
 		case 0: $act = "pretendents"; $mms = ws_comm_member::several($comm->id(), null, "no"); break;
 		default: die("<li>Ошибка</li>");
 	}
 	
 	foreach ($mms as $m) if($m->status == $st || ($m->confirmed == "no" && $st == 0) || ($m->confirmed == $st)){
 		echo "<li id=\"m-".$m->id()."\">", $m;
 		echo "<small> &ndash; ";
 		
 		switch($act){
 			case "pretendents":
?>
	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/apply', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>},update:$('m-<?=$m->id()?>')}).send()">Принять</a>
 	  &bull; <a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/reject', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>},update:$('m-<?=$m->id()?>')}).send()">Отказать</a>
<?
 			break;
 			
 			case "members":
?>
	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/st', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>,st:<?=ws_comm::st_curator?>},update:$('m-<?=$m->id()?>')}).send()">В Кураторы</a>
 	  &bull; <a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/reject', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>},update:$('m-<?=$m->id()?>')}).send()">Исключить</a>
<?
 			break;
 			
 			case "curators":
?>
	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/st', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>,st:<?=ws_comm::st_member?>},update:$('m-<?=$m->id()?>')}).send()">В Участники</a>
	  &bull; <a href="javascript:void(0)" onclick="if(confirm('Вы уверены? После такого решения контрольная панель сообщества может стать недоступна.')) mr_Ajax({url:'/x/comm-adm-members/leader', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>},update:$('m-<?=$m->id()?>')}).send()">Назначить Лидером</a>
 	  &bull; <a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/reject', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>},update:$('m-<?=$m->id()?>')}).send()">Исключить</a>
<?
 			break;
 			
 			case "auth":
?> 				
 	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/reject', data:{pid:<?=$m->id()?>,comm:<?=$comm->id()?>},update:$('m-<?=$m->id()?>')}).send()">Исключить</a>
 	&bull; Статус: <?=$m->status()?>
<?
 			break;
 		}
 		
 		echo "</small></li>";
 	}
 }
 
/**
 * Выписка приглашения из контрольной панели по логину
 *
 */
 static public function invite()
 {
	self::check_rights($comm = ws_comm::factory( (int)$_POST["id"] ));
 		
 	$st = $_POST["st"];
 	if($st > ws_comm::st_curator) $st = ws_comm::st_curator;
 	if($st < ws_comm::st_member) $st = ws_comm::st_member;
 	
 	$login = mr_text_string::remove_excess($_POST["invite"]);
 	$u = ws_user::getByLogin($login);
 	if(!$u || strtolower($u->login)!=strtolower($login))
 		die("Пользователь $login не найден");
 		
 	if(!$u->is_member($comm->id()) && $u->is_member($comm->id())!==0)
 	{
 		$m = ws_comm_member::create($comm->id(), $u->id(), "auth", $st);
 		echo ($m && $m->status ? "Приглашение создано: " : "Ошибка при создании приглашения: ").$u->link()." <i>(".$m->status().")</i>";
 	} else echo "Пользователь ".$u->link()." уже связан с сообществом";
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