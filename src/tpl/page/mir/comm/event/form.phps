<?php class tpl_page_mir_comm_event_form extends tpl_page_mir_comm_inc implements i_tpl_page_ico {
	
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$action = $params[1];
 	
 	if($action == "add")
 	{
 		$ww = ws_comm_event_sec::factory( (int)$params[2] );
 		if( !$ww->can_add_item() )
 			throw new ErrorPageException("Недостаточно прав", 403);
 	} else {
 		
 		$ww = ws_comm_event_item::factory( (int)$params[2] );
 		if( !$ww->can_edit() )
 			throw new ErrorPageException("Недостаточно прав", 403);
 		
 	}
 	
 	$this->title = ($action=="add"?"Новое событие в колонке: ":"Править событие: ").$ww->title;
 	
 	ob_start();
 	
 	if($action == "add"){
 ?>
 <h1>Новое событие</h1>
 <h2>Колонка: <?=$ww?>, <?=$this->comm?></h2>
 <?}else{
 ?>
 <h1>Правка события</h1>
 <h2><?=$ww?>, <?=$this->comm?></h2>
 <?}
 	
 	echo "<br/>";
 	 	
 	$this->handle_ev_ww($ww);
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 }
 
 private function handle_ev_ww($ww)
 {
 	$geo = false;
 	$city = 0;
 	$owner = "protected";
 	$action = "/x/ajax-event/item/";
 	if($ww instanceof ws_comm_event_item)
 	{
 		$title = htmlspecialchars( $ww->title );
 		$description = htmlspecialchars( $ww->description );
 		$name = htmlspecialchars( $ww->name );
 		$content = mr_text_trans::node2text( $ww->content );
 		if( $ww->city )
 		{
 			$geo = true;
 			$city = $ww->city;
 		}
 		elseif( $ww->section()->geo == "yes" ) $geo = true;
 		$owner = $ww->owner;
 		$action .= "save";
 	} else {
 		if( $ww->geo == "yes" ) $geo = true;
 		$action .= "new";
 	}
 	
 	$owners = array("public"=>"Сообщество", "protected"=>"Руководство", "private"=>"Автор");
?>

<form method="post" action="<?=$action?>" onsubmit="javascript:$('ev_submit').disabled='yes';">
<table>
	<tr>
		<td>Заголовок:</td>
		<td><input type="text" name="title" value="<?=$title?>" size="32" maxlength="128"/></td>
	</tr>
	<tr>
		<td>Подзаголовок:</td>
		<td><input type="text" name="description" value="<?=$description?>" size="32" maxlength="128"/></td>
	</tr>
	<tr>
		<td>Основной текст:</td>
		<td>
			<textarea name="content" cols="60" rows="20" id="attach"><?=$content?></textarea>
			<script type="text/javascript">text_markup('attach', 1, 0);</script>
		</td>
	</tr>
	<tr>
		<td>Имя странички:</td>
		<td><input type="text" name="name" value="<?=$name?>" size="32" maxlength="128"/></td>
	</tr>
	<?if($geo){?>
	<tr>
		<td>Город события:</td>
		<td><?=ws_geo_city::form_select("city", "id", $city)?></td>
	</tr>
	<?}?>
	<tr>
		<td>Владелец странички:</td>
		<td><select name="owner">
		<?foreach ($owners as $key => $value) {?>
			<option value="<?=$key?>"<?=($key==$owner?' selected="yes"':'')?>><?=$value?></option>
		<?}?>
		</select></td>
	</tr>
	

</table>
	<center>
		<input type="submit" id="ev_submit" value="Сохранить"/>
			&nbsp;<input type="hidden" name="id" value="<?=$ww->id()?>"/>
		<input type="reset" value="Сбросить"/>
	</center>
</form>

<?
 	
 }

 public function p_ico()
 {
 	return "events";
 }
	
	}
?>