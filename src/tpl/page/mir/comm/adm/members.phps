<?php class tpl_page_mir_comm_adm_members extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol {
				
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	 	
 	if( !ws_self::is_allowed("comm_control", $this->comm->id()) )
 		throw new ErrorPageException("У Вас нет прав на контроль этого сообщества", 403);
 		
 	$this->title = $this->comm->title." - Контроль сообщества. Список участников";
 		 	
 	ob_start();
 	
 	$whole = ws_comm_member::several($this->comm->id(), null, null);
 	
 	$pretendents = new mr_list("ws_comm_member", array());
 	$members = new mr_list("ws_comm_member", array());
 	$curators = new mr_list("ws_comm_member", array());
 	$auth = new mr_list("ws_comm_member", array());
 	$leader = 0;
 	foreach($whole as $m){
 		if($m->confirmed=="no") $pretendents[] = $m;
 		elseif($m->confirmed=="auth") $auth[] = $m;
 		elseif($m->status == ws_comm::st_member)
 			$members[] = $m;
 		elseif($m->status == ws_comm::st_curator)
 			$curators[] = $m;
 		else $leader = $m;
 	}
 	
 	$apply_members = array(
 		"free" => "Принимать автоматически",
 		"yes" => "Рассматривать",
 		"no" => "Не принимать"
 	);
 	
 ?>
 <h1>Контроль сообщества</h1>
 <h2>Список участников</h2>
 
 <?if(count($pretendents)){?>
<fieldset>
 <legend>Претенденты</legend>
 <b>Заявок: <?=count($pretendents)?></b> / <a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/mlist', data:{comm:<?=$this->comm->id()?>,st:0},update:$('pretendents')}).send()">Рассмотреть</a>
  <ul id="pretendents"></ul>
</fieldset> 
 <?}?>
 
<fieldset>
 <legend>Руководство сообщества</legend>
 <b>Лидер</b>: <?=$leader?><br/>
 <?if(count($curators)){?>Кураторов: <?=count($curators)?> / <a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/mlist', data:{comm:<?=$this->comm->id()?>,st:<?=ws_comm::st_curator?>},update:$('curators')}).send()">Настройки</a>
  <ul id="curators"></ul><?} else echo "Кураторов нет";?>
</fieldset>

 <?if(count($members)){?>
<fieldset>
 <legend>Участники</legend>
 Всего участников: <?=count($members)?> / <a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/mlist', data:{comm:<?=$this->comm->id()?>,st:<?=ws_comm::st_member?>},update:$('members')}).send()">Настройки</a>
  <ul id="members"></ul>
</fieldset> 
 <?}?>
 
<fieldset>
 <legend>Приглашения</legend>
 <?if(count($auth)){?>
 Ожидают подтверждения: <?=count($auth)?> / <a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-members/mlist', data:{comm:<?=$this->comm->id()?>,st:'auth'},update:$('auth')}).send()">Смотреть</a>
 <ul id="auth"></ul>
 	<br/>
 <?}?>

 <form method="post" action="/x/comm-adm-members/invite" id="inv-form">
 <center>
 	Пригласить: <input type="text" name="invite" size="40"/>
 		<br/>
 	<small>Введите логин пользователя, которому хотите выслать приглашение</small>
 		<br/>
 	Статус: <select name="st">
 		<option value="<?=ws_comm::st_member?>"><?=ws_comm::mem_status(ws_comm::st_member)?></option>
 		<option value="<?=ws_comm::st_curator?>"><?=ws_comm::mem_status(ws_comm::st_curator)?></option>
 	</select>
 	
 	<br/><br/>
 	
		<input type="button" onclick="mr_Ajax_Form($('inv-form'), {update:$('inv-result')})" value="Отправить приглашение"/>

		<div id="inv-result"></div>
 </center>
 	<input type="hidden" name="id" value="<?=$this->comm->id()?>" />
 </form>
</fieldset>
 
<form method="post" action="/x/comm-adm/members" id="mms-form">
<fieldset>
 <legend>Правила приёма</legend>
 
 <center>
 	Заявки на участие в сообществе:
 	<select name="apply">
 		<?foreach($apply_members as $a=>$me){?><option value="<?=$a?>"<?=($a==$this->comm->apply_members ? ' selected="yes"':"")?>><?=$me?></option><?}?>
 	</select>
 	
 	<br/><br/>
 	
		<input type="button" onclick="mr_Ajax_Form($('mms-form'),{update:$('mms-result')})" value="Сохранить изменения"/>

		<div id="mms-result"></div>
		
 </center>
	<input type="hidden" name="id" value="<?=$this->comm->id()?>" />
</fieldset>
</form>
 	
 <?
 		
 	$this->content = ob_get_clean();
 }
	}
?>