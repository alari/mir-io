<?php class tpl_page_dev_ticket extends tpl_page_dev_inc {
	
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$ticket = ws_dev_ticket::factory($params[1]);
 	
 	if(!$ticket->is_showable()&&0)
 		throw new ErrorPageException("Доступ запрещён", 403);
 	
 	$this->project = $ticket->project();
 	
 	ob_start();
?>

<h1><?=$ticket->title?></h1>
<h2>Проект <?=$ticket->project()?></h2>

<?tpl_fr_comment::outlist($ticket->getNotes());?>

<form method="post" action="/x/dev-ticket/modify"><center>
<fieldset>
<legend>Изменить состояние тикета</legend>
<table>
<colgroup>
	<col valign="top"/>
	<col/>
</colgroup>
<tr>
	<td>Название:</td>
	<td><input type="text" name="title" value="<?=htmlspecialchars($ticket->title)?>" size="62"/></td>
</tr><tr>
	<td>Приоритет:</td>
	<td>
		<select name="priority">
			<?foreach(ws_dev_ticket::getPriorities() as $k=>$v){?>
			<option value="<?=$k?>"<?=($k==$ticket->priority?' selected="yes"':"")?>><?=$v?></option>
			<?}?>
		</select>
		<select name="type">
			<?foreach($ticket->getAvailableTypes() as $k=>$v) {?>
			<option value="<?=$k?>"<?=($k==$ticket->type?' selected="yes"':"")?>><?=$v?></option>
			<?}?>
		</select>
	</td>
</tr><tr>
	<td>Статус:</td>
	<td><select name="status">
	<?foreach($ticket->getAvailableStatuses() as $k=>$v){?>
		<option value="<?=$k?>"<?=($k==$ticket->status?' selected="yes"':"")?>><?=$v?></option>
	<?}?>
	</select></td>
</tr>
	<?
	$modules = $this->project->getModules();
	if(count($modules) && !$ticket->isModule()){?>
<tr>
	<td>Модуль:</td>
	<td><select name="parent">
		<option value="0"></option>
		<?foreach($modules as $m){?>
			<option value="<?=$m->id()?>"<?=($m->id()==$ticket->module?' selected="yes"':"")?>><?=$m->title?></option>
		<?}?>
	</select></td>
</tr>
	<?}?>
<tr>
	<td>Комментарий:</td>
	<td><textarea name="content" id="ticket_description" style="width:100%;height:25px;"
	onfocus="javascript:$(this).get('tween', {property:'height',duration: 800, transition: Fx.Transitions.Sine.easeOut}).start(140)"
	onblur="javascript:$(this).get('tween', {property: 'height',duration: 600, transition: Fx.Transitions.Sine.easeIn}).start(25)"></textarea>
	<script type="text/javascript">text_markup('ticket_description', 1, 0);</script></td>
</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="Сохранить"/>
			<input type="hidden" name="ticket" value="<?=$ticket->id()?>"/>
		</td>
	</tr>
</table>
</center>
</fieldset>
</form>

<?	
 	$this->content = ob_get_clean();
 	
 	$this->title = "Тикет: ".$ticket->title.", проект ".$this->project->title;
 	
 	$this->css[] = "comment.css";
 }
	
}?>