<?php class x_ws_ajax_pub extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
/**
 * Список в клубном рейтинге
 *
 */
 static public function clubvotes()
 {
 	$v = ws_libro_pub_stat::pubClubStat((int)$_POST["id"]);
	echo "<p>Голоса в Клубном рейтинге:</p>";
	foreach($v as $k=>$a) if($a)
	{
		echo "<p><i>", ws_libro_pub::getClubscore($k) ,"</i><ul>";
		foreach($a as $u) echo "<li>", $u->user(), "</li>";
		echo "</ul></p>";
	}
 }
 	
/**
 * Форма добавления отзыва
 *
 */
 static public function resp()
 {
 	tpl_fr_comment::add_form("/x/ajax-pub/resp/add", "ws_libro_pub_resp", $_POST["parent"]);
 }

/**
 * Сохранение отзыва
 *
 */
 static public function resp_add()
 {
 	$content = trim($_POST["msg"]);
 			
 	if( !ws_self::ok() && mr_security::spamFilter($content) )
 		echo("<b>Вы не авторизованы. Сработал спам-фильтр.</b>");
 					
 	$pub = ws_libro_pub_item::factory((int)$_POST["parent"]);
 	if( !$pub->can_resp() )
 		die("<b>Вы не можете оставить отзыв на данное произведение.</b>");
 		
 	$rem_type = ws_user_remind::type_pub_resp;
 	if(ws_self::ok())
 		$rem_ch = ws_user_remind::change_sub( $rem_type, (int)$_POST["parent"], $_POST["remind"], $_POST["method"] );
 		
 	if(strlen($content)<8)
 	{
 		echo (
 			$rem_ch ?
 			"<b>Состояние Вашей подписки успешно изменено</b>" :
 			"<b>В отзыве обязана присутствовать содержательная часть.</b>"
 			 )."<br/>";
 		tpl_fr_comment::add("/x/ajax-pub/resp", $pub->id(), false);
 		return;
 	}
 			
 				
 	$r = ws_libro_pub_resp::create( $_POST["parent"], ws_self::id(), $content );

 	// Рецензии: начали
 	$rec_comms = new mr_list("ws_comm", array());
 	foreach($_POST as $k=>$v) if(substr($k, 0, 4)=="rec-" && $v=="yes")
 	{
 		list(, $comm) = @explode("-", $k, 2);
 		$comm = (int)$comm;
 		if(!$comm) continue;
 		$comm = ws_comm::factory($comm);
 		if(!$comm || !$comm->can_make_recense()) continue;
 		
 		$rec_comms[] = $comm;
 	}
 	if(count($rec_comms))
 	{
 		$anchors = $r->pub()->comm_anchors();
 		foreach($anchors as $ca) if(
 			in_array($ca->comm()->id(), $rec_comms->ids())
 		 && !$ca->resp_id
 		 && (
 		 	$ca->editor == ws_self::id()
 		 || !$ca->editor
 		 	)
 		 		)
 		{
 			$ca->editor = ws_self::id();
 			$ca->resp_id = $r->id();
 			$categ = (int)@$_POST["categ-".$ca->comm()->id()];
 			if($categ)
 			{
 				$categ = ws_comm_pub_categ::factory($categ);
 				if($categ
 				&& $categ->comm()->id() == $ca->comm()->id()
 				&& $categ->id() != $ca->category()->id()
 				&& $categ->apply == "yes")
 					$ca->category = $categ->id();
 			}
 			$ca->save();
 		}
 	}
 	// Рецензии: закончили

 	$r->notify_subscribers();

 	$pub->currentStat()->cache();

 	echo "<b>Ваш комментарий успешно сохранён</b><br />";	
 	tpl_fr_comment::out( $r );
 	tpl_fr_comment::add("/x/ajax-pub/resp", $pub->id(), false);
 }
 
/**
 * Осуществление голосования
 *
 */
 static public function vote()
 { 	
 	$pub = ws_libro_pub_item::factory( (int)@$_POST["pub_id"] );
 	
 		if(!$pub->can_vote())
 			die("Вы не можете голосовать за это произведение.");
 	
 	$vote = (int)@$_POST["vote"];
 	$clubvote = @$_POST["clubvote"];
 		if( !ws_self::is_allowed("clubvote") )
 			$clubvote = 0;
 		else {
 			$vo = array(
	   			"empty"=>0,
	   			"neud"=>-1,
	   			"udovl"=>1,
	   			"hor"=>3,
   				"otl"=>6
   			);
   			$clubvote = $vo[$clubvote];
 		}
 			
 	$pub->currentStat()->vote( $pub->id(), $vote, $clubvote );
 	
 	echo "Ваш голос принят. Спасибо!";
 }
 
/**
 * Сохранение рекомендации
 *
 */
 static public function advice()
 {
 	$pub = ws_libro_pub_item::factory( (int)@$_POST["pub_id"] );
 	if( !ws_libro_pub_advice::can_advice($pub) )
 		die("Вы не можете оставить рекомендацию на это произведение.");
 		
 	$reason = trim(@$_POST["reason"]);
 	if(strlen($reason)<4)
 		die("Пожалуйста, обоснуйте причину рекомендации хоть сколь-нибудь более полно.");
 		
 	ws_libro_pub_advice::create($pub->id(), ws_self::id(), $reason);
 	
 	echo "Ваша рекомендация принята. Спасибо!";
 }
 
/**
 * Ссылки административного контроля
 *
 */
 static public function control_admx()
 {
 	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	if( !$pub->author )
 		die("Произведение ".$pub->id()." не найдено!");
 	if( !ws_self::is_allowed("to_hide", $pub->meta) )
 		die("Недостаточно прав.");
 		
?>
Произведение:
<a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-pub/control/admx/hide', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()"><?=($pub->hidden == "yes" ? "Показать":"Спрятать")?></a>
<?if(ws_self::is_allowed("to_delete_pubs", $pub->meta)){

	$target_metas = ws_comm::several("FIND_IN_SET('libro', org_sphere) AND apply_".$pub->type."!=0 AND apply_pubs='public' AND type IN ('meta','closed')");
	
	?>

	&bull;
<a href="javascript:void(0)" onclick="javascript:if(confirm('Требуется подтверждение необратимого удаления произведения.')) mr_Ajax({url:'/x/ajax-pub/control/admx/delete', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()">Удалить</a>
	&bull;
Переместить в: <select onchange="javascript:if(this.value) mr_Ajax({url:'/x/ajax-pub/control/admx/move', update:$('pub-control'),data:{pub:<?=$pub->id()?>,target:this.value}}).send()">
<option value="0">-</option>
<?foreach($target_metas as $tm){?><option value="<?=$tm->id()?>"><?=$tm->title?></option><?}?>
</select>
<?}
	
?>

<?
 }
 
/**
 * Смена доминанты
 *
 */
 static public function control_admx_move()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	if( !$pub->author )
 		die("Произведение ".$pub->id()." не найдено!");
 	if( !ws_self::is_allowed("to_delete_pubs", $pub->meta) )
 		die("Недостаточно прав.");
 	$target_metas = ws_comm::several("FIND_IN_SET('libro', org_sphere) AND apply_".$pub->type."!=0 AND apply_pubs='public' AND type IN ('meta','closed')");
 	$tm = (int)$_POST["target"];
 	if(!$tm || !in_array($tm, $target_metas->ids()) || $pub->meta == $tm)
 		die("Неверная доминанта. Перемещение не произошло.");
 		
 	$pub->meta = $tm;
 	echo $pub->save() ? "Доминанта изменена успешно" : "Сохранить изменения не удалось";
 }
 
/**
 * Административное сокрытие произведения
 *
 */
 static public function control_admx_hide()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	if( !$pub->author )
 		die("Произведение ".$pub->id()." не найдено!");
 	if( !ws_self::is_allowed("to_hide", $pub->meta) )
 		die("Недостаточно прав.");
 		
 	$pub->hidden = $pub->hidden == "yes" ? "no" : "yes";
 	$pub->save();
	echo "Публичная видимость произведения успешно изменена.";
 }
 
/**
 * Административное удаление произведения
 *
 */
 static public function control_admx_delete()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	if( !$pub->author )
 		die("Произведение ".$pub->id()." не найдено!");
 	if( !ws_self::is_allowed("to_delete_pubs", $pub->meta) && $pub->author != ws_self::id() )
 		die("Недостаточно прав.");
 		
 	$pub->delete();
	echo "Произведение успешно удалено. Перейдите на другую страничку сайта.";
 }
 
/**
 * Список сообществ для контроля
 *
 */
 static public function control_comm()
 {
 	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	
 	// собираем комьюнити
 	$comms = array();
 	// где есть произведение
 	$in_comms = $pub->pub()->comm_anchors();
 	foreach($in_comms as $c)
 		if(
 			($c->editor == ws_self::id() && ws_self::is_member($c->comm_id))
 			 || ws_self::is_member($c->comm_id, ws_comm::st_curator)
 			)
 		$comms[] = $c->comm_id;
 		
 	// где есть пользователь и может принимать произведения
 	$self_in_comms = ws_self::self()->memberof(1);
 	foreach ($self_in_comms as $comm=>$status)
 		if($status >= ws_comm::st_curator && ws_comm::factory($comm)->editors_apply == "yes")
 			if(!in_array($comm, $comms))
 				$comms[] = $comm;
 				
 	if(!count($comms)) return;
 	$list = new mr_list("ws_comm", $comms);
 	
 	// выводим
 	foreach($list as $c)
 		echo $c->link(null, null, "javascript:mr_Ajax({url:'/x/ajax-pub/control/comm/adm',update:$('pub-control'),data:{pub:".$pub->id().",comm:".$c->id()."}}).send()");
 }
 
/**
 * Контрольная панель выбранного сообщества
 *
 */
 static public function control_comm_adm($in_comm = null)
 {
 	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	$comm = ws_comm::factory((int)$_POST["comm"]);
 	
 	// Включено ли уже в сообщество произведение? Если да, один вид контрольной панели
 	/* @var $in_comm ws_comm_pub_anchor */
 	if(!($in_comm instanceof ws_comm_pub_anchor))
 	{
	 	foreach($pub->pub()->comm_anchors() as $a) if($a->comm_id == $comm->id())
	 	{
	 		$in_comm = $a;
	 		break;
	 	}
 	}
 	if($in_comm && (
 			($in_comm->editor == ws_self::id() && ws_self::is_member($in_comm->comm_id))
 			 || ws_self::is_member($in_comm->comm_id, ws_comm::st_curator)
 		))
 	{
?>

<?=$in_comm?>: 
 <a href="javascript:void(0)" onclick="javascript:if(confirm('Требуется подтверждение')) mr_Ajax({url:'/x/ajax-pub/control/comm/delete', update:$('pub-control', data:{comm:<?=$comm->id()?>,pub:<?=$pub->id()?>})}).send()">Отклонить произведение</a>

<form method="post" action="/x/ajax-pub/control/comm/anchor" id="pub-comm-anchor-form">
 
 <?if(ws_self::is_member($in_comm->comm_id, ws_comm::st_curator)){
 	
 	$editors = array();
 	foreach($comm->members() as $uid=>$st) if($st) $editors[] = $uid;
 	$editors = new mr_list("ws_user", $editors);
 	
 	?>
 	Редактор:&nbsp;
 	<select name="editor">
 		<option value="0">(# Нет)</option>
 		<?foreach($editors as $e){?><option value="<?=$e->id()?>"<?=($e->id()==$in_comm->editor?' selected="yes"':"")?>><?=$e->name()?></option><?}?>
 		<?if($in_comm->editor && !in_array($in_comm->editor, $editors->ids())){?>
 			<option value="<?=$in_comm->editor?>" selected="yes" title="Выбыл из сообщества">## <?=ws_user::factory($in_comm->editor)->name()?></option>
 		<?}?>
 	</select>
 <?}?>
 
 Категория:&nbsp;
 <select name="category">
 	<option value="0">(# Нет)</option>
 <?
 	$cats = ws_comm_pub_categ::several($comm->id(), false, true);
 	foreach($cats as $c){?><option value="<?=$c->id()?>"<?=($c->id()==$in_comm->category?' selected="yes"':"")?>><?=$c->title?></option>
 	<?}

 	if($in_comm->category && !in_array($in_comm->category, $cats->ids()))
 	{
 		echo "<option value=\"", $in_comm->category, "\" selected=\"yes\">", $in_comm->category()->title, "</option>";
 	}
 	
 ?>
 </select>
 
 <input type="hidden" name="comm" value="<?=$comm->id()?>" />
 <input type="hidden" name="pub" value="<?=$pub->id()?>" />
 
 <input type="button" value="Сохранить" onclick="javascript:$(this).disabled='yes';mr_Ajax_Form($('pub-comm-anchor-form'),{update:$('pub-control')});return false;" />
 
</form>

<?
		return;
 	}
 	
 	// Произведение ещё не находится в сообществе -- другая контрольная панель
 	if($comm->editors_apply == "yes" && ws_self::is_member($comm->id(), ws_comm::st_curator))
 	{
?>
	<?=$comm?>: <a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-pub/control/comm/apply',update:$('pub-control'),data:{pub:<?=$pub->id()?>,comm:<?=$comm->id()?>}}).send()">Принять произведение в сообщество</a>
<?
 		
 		return;
 	}
 	
 	die("Вы не можете контролировать произведение в этом сообществе.");
 }
 
/**
 * Приём произведения в сообщество
 *
 */
 static public function control_comm_apply()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	$comm = ws_comm::factory((int)$_POST["comm"]);
 	
 	if($comm->editors_apply == "yes" && ws_self::is_member($comm->id(), ws_comm::st_curator))
 	{
 		
 		// Создаём связь
 		$a = ws_comm_pub_anchor::create($pub->id(), $comm->category_default, $comm->id);
 		// Принято сиим, редактор -- сей
 		$a->user_id = ws_self::id();
 		$a->editor = ws_self::id();
 		$a->save();
 		
 		echo "Произведение успешно принято в сообщество<br/>";
 		// Форма обработки
 		self::control_comm_adm($a);
 		
 	} else die("Вы не имеете прав на приём произведений в сообщество ".$comm->link());
 }
 
/**
 * Удаление связи произведение-сообщество
 *
 */
 static public function control_comm_delete()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	$comm = ws_comm::factory((int)$_POST["comm"]);
 	
 	/* @var $in_comm ws_comm_pub_anchor */
 	$in_comm = false;
 	
	foreach($pub->pub()->comm_anchors() as $a) if($a->comm_id == $comm->id())
	{
		$in_comm = $a;
		break;
	}
 	if($in_comm && (
 			($in_comm->editor == ws_self::id() && ws_self::is_member($in_comm->comm_id))
 			 || ws_self::is_member($in_comm->comm_id, ws_comm::st_curator)
 		))
 	{
 		
 		$in_comm->delete();
 		echo "Связь произведения с сообществом ", $comm, " удалена";
 		
 	} else echo "Удаление произведения из сообщества невозможно.";
 }
 
/**
 * Правка данных связи произведение-сообщество
 *
 */
 static public function control_comm_anchor()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	$comm = ws_comm::factory((int)$_POST["comm"]);
 	
 	/* @var $in_comm ws_comm_pub_anchor */
 	$in_comm = false;
 	
	foreach($pub->pub()->comm_anchors() as $a) if($a->comm_id == $comm->id())
	{
		$in_comm = $a;
		break;
	}
 	if($in_comm && (
 			($in_comm->editor == ws_self::id() && ws_self::is_member($in_comm->comm_id))
 			 || ws_self::is_member($in_comm->comm_id, ws_comm::st_curator)
 		))
 	{
 		
 		$editor = (int)$_POST["editor"];
 		$cat = (int)$_POST["category"];
 		
 		if($cat)
 		{
 			$categ = ws_comm_pub_categ::factory($cat);
 			if($categ->apply != "yes" || $categ->comm_id != $comm->id())
 			$cat = $in_comm->category;
 		}
 		
 		$in_comm->category = $cat;
 		
 		if(ws_self::is_member($in_comm->comm_id, ws_comm::st_curator))
 		{
 			$ed = ws_user::factory($editor);
 			if($ed->is_member($comm->id(), ws_comm::st_member))
 				$in_comm->editor = $editor;
 		}
 		
 		$in_comm->save();
 		echo "Изменения успешно сохранены";
 		
 	} else echo "Ошибка: невозможно редактировать данные связи произведение-сообщество";
 }
 
/**
 * Список ссылок авторского контроля
 *
 */
 static public function control_auth()
 {
 	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	if( $pub->author != ws_self::id() )
 		die("Авторский контроль произведения недоступен.");
 		
 	$discs = array(
 		"public"=>"Открытое",
 		"protected"=>"Участники доминанты",
 		"private"=>"Круг Чтения",
 		"disable"=>"Закрытое"
 	);
 		
?>

<a href="<?=mr::host("own")?>/pub/pref-<?=$pub->id()?>.xml">Настройки</a>
	&nbsp;
<?if(ws_self::is_allowed("pub_stat")){?><a href="<?=mr::host("own")?>/pub/stat.id-<?=$pub->id()?>.xml">Статистика</a><?}?>
	&nbsp;
<a href="<?=mr::host("own")?>/pub/edit-<?=$pub->id()?>.xml">Править текст</a>
	&nbsp;
<?if($pub->hidden != "yes"){?><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-pub/control/auth/hide', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()"><?=($pub->hidden=="no"?"Спрятать":"Показать")?></a><?}?>
	&nbsp;
<?if(ws_comm::factory($pub->meta)->can_ch_discuss()){?>
Обсуждение: 
<select onchange="mr_Ajax({url:'/x/ajax-pub/control/auth/discuss', update:$('pub-control'),data:{pub:<?=$pub->id()?>,target:this.value}}).send()">
<?foreach($discs as $d=>$v){?><option value="<?=$d?>"<?=($pub->discuss==$d?' selected="yes"':"")?>><?=$v?></option><?}?>
</select>
<?}else{?>
<a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-pub/control/auth/discuss', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()"><?=($pub->discuss!="disable"?"Закрыть":"Открыть")?> обсуждение</a>

<?}?>
<br/><a href="javascript:void(0)" onclick="javascript:if(confirm('Требуется подтверждение необратимого удаления произведения.')) mr_Ajax({url:'/x/ajax-pub/control/admx/delete', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()">Удалить произведение</a>
<?
 }
 
/**
 * Авторское сокрытие произведения
 *
 */
 static public function control_auth_hide()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	if( $pub->author != ws_self::id() )
 		die("Вы не можете управлять видимостью этого произведения!");
 		
 	if($pub->hidden == "yes")
 		die("Открыть произведение может только администрация доминанты ".ws_comm::link($pub->meta));
 		
 	$pub->hidden = $pub->hidden == "auth" ? "no" : "auth";
 	$pub->save();
 	
 	// кэширование цикла
 	
	echo "Публичная видимость произведения успешно изменена.";
 }
 
/**
 * Авторское изменение обсуждения
 *
 */
 static public function control_auth_discuss()
 {
	$pub = ws_libro_pub_item::factory((int)$_POST["pub"]);
 	if( $pub->author != ws_self::id() )
 		die("Недостаточно прав");
 		
 	if(ws_comm::factory($pub->meta)->can_ch_discuss())
 		$pub->discuss = $_POST["target"];
 	else
	 	$pub->discuss = $pub->discuss == "disable" ? "public" : "disable";
 	$pub->save();
	echo "Статус обсуждения произведения успешно изменён.";
 }
 
	}
?>