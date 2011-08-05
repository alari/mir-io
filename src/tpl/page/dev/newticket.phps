<?php class tpl_page_dev_newticket extends tpl_page_dev_inc {
		
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	ob_start();
 	 	
 	$project = (int)$params["in"];
 	if(!$project || !($this->project=ws_dev_project::factory($project)) instanceof ws_dev_project)
 		throw new ErrorPageException("Проект не найден", 404);
 	
 	$modules = $this->project->getModules();
?>

<h1>Создать новый тикет</h1>
<h2>Проект: <?=$this->project?></h2>

<center><form method="post" action="/x/dev-ticket/create" accept-charset="utf-8">
<fieldset><legend>Описание тикета</legend>
<table>
	<tr>
		<td>Приоритет:</td>
		<td>
			<select name="priority">
				<?foreach(ws_dev_ticket::getPriorities() as $k=>$v){?>
				<option value="<?=$k?>"<?=($k==ws_dev_ticket::priority?' selected="yes"':"")?>><?=$v?></option>
				<?}?>
			</select>
			<select name="type">
				<?foreach(ws_dev_ticket::getTypes() as $k=>$v){?>
				<option value="<?=$k?>"<?=($k==ws_dev_ticket::type?' selected="yes"':"")?>><?=$v?></option>
				<?}?>
			</select>
		</td>
	</tr><tr>
		<td>Заголовок:</td>
		<td><input type="text" name="title" size="62"/></td>
	</tr><tr>
		<td>Описание:</td>
		<td><textarea name="content" id="ticket_description" style="width:100%;height:250px;"></textarea>
		<script type="text/javascript">text_markup('ticket_description', 1, 0);</script></td>
	</tr>
	<?if(count($modules)){?>
	<tr>
		<td>Модуль:</td>
		<td><select name="parent">
			<option value="0"></option>
			<?foreach($modules as $m){?>
				<option value="<?=$m->id()?>"><?=$m->title?></option>
			<?}?>
		</select></td>
	</tr>
	<?}?>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="Создать тикет"/>
			<input type="hidden" name="project" value="<?=$this->project->id()?>"/>
		</td>
	</tr>
</table>
</fieldset>
</form>
</center>

<?	
 	$this->content = ob_get_clean();
 	
 	$this->title = "Новый тикет в проекте ".$this->project->title;
 }
	
}?>