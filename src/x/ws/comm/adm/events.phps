<?php class x_ws_comm_adm_events extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
/**
 * Форма редактирования колонки
 *
 */
 static public function ajax_edit($col=null)
 {
 	if(!($col instanceof ws_comm_event_sec))
 	{
		self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
	 		
	 	$col = ws_comm_event_sec::factory( (int)$_POST["col"] );
	 	if($col->comm_id != $comm->id())
	 		die("Несовпадение сообществ");
 	} else {
 		$comm = $col->comm();
 	}
 	
 	$av_spheres = explode(",", $comm->org_sphere);
 	$av_directs = explode(",", $comm->org_direct);
 	
 	$col_spheres = explode(",", $col->org_sphere);
 	$col_directs = explode(",", $col->org_direct);
 	
 	$apply = array(
 		"public"=>"Все желающие",
 		"protected"=>"Участники сообщества",
 		"private"=>"Руководство сообщества",
 		"disable"=>"Никто",
 		"column"=>"Владелец колонки"
 	);
 	
 	$order = array(
 		"DESC" => "Новые - выше",
 		"ASC" => "Старые - выше"
 	);
 	
 	$view = array(
 		"anonce" => "Анонсы",
 		"list" => "Список-Содержание",
 		"blog" => "Лента-блог"
 	);
 	
 	$owner = "";
 	if($col->apply == "column" && $col->owner)
 		$owner = ws_user::factory($col->owner)->login;
 	
?>

<form method="post" action="/x/comm-adm-events/edit" id="col-<?=$col->id()?>">
<center><table>

		<tr>
<td align="center">
	Адрес: <input type="text" name="name" maxlength="12" size="8" value="<?=$col->name?>"/>
		<br/>
	Название:
		<br/>
	<input type="text" name="title" value="<?=htmlspecialchars($col->title)?>" size="16" maxlength="36"/>
		</td>
		<td>
	<input type="hidden" name="display" value="no"/><input type="checkbox" name="display" value="yes" id="disp_<?=$col->id()?>"<?=($col->display=="yes"?' checked="yes"':"")?>/> &ndash; <label for="disp_<?=$col->id()?>">показывать в сообществе;</label>
		<br/>
	<input type="hidden" name="strong" value="no"/><input type="checkbox" name="strong" value="yes" id="strong_<?=$col->id()?>"<?=($col->strong=="yes"?' checked="yes"':"")?>/> &ndash; <label for="strong_<?=$col->id()?>">показывать на сайте;</label>
		<br/>
	<input type="hidden" name="geo" value="no"/>
	<?if(in_array("real", $av_spheres)){?>
	<input type="checkbox" name="geo" value="yes" id="geo_<?=$col->id()?>"<?=($col->geo=="yes"?' checked="yes"':"")?>/> &ndash; <label for="geo_<?=$col->id()?>">привязывать к городам;</label>
	<?}?>
		</td>
		<td align="center">
Сфера:<br/>
<select multiple="yes" name="sphere[]" size="<?=count($av_spheres)?>">
<?foreach($av_spheres as $s){?>
	<option value="<?=$s?>"<?=(in_array($s, $col_spheres)?' selected="yes"':"")?>><?=ws_comm::$org_spheres[$s]?></option>
<?}?>
</select>
	</td>
	<td align="center">
Направление:<br/>
<select multiple="yes" name="direct[]" size="<?=count($av_directs)?>">
<?foreach($av_directs as $s){?>
	<option value="<?=$s?>"<?=(in_array($s, $col_directs)?' selected="yes"':"")?>><?=ws_comm::$org_directs[$s]?></option>
<?}?>
</select>
	</td>

		</tr>
		<tr>
<td colspan="4">
	Краткое описание / подзаголовок: <input type="text" name="descr" size="36" value="<?=htmlspecialchars($col->description)?>" maxlength="127"/>
</td>
		</tr>
		<tr>
<td colspan="4" align="center">

	События могут добавлять: <select name="apply">
<?foreach($apply as $a=>$t){?><option value="<?=$a?>"<?=($col->apply == $a ? ' selected="yes"':"")?>><?=$t?></option><?}?>
	</select>,
	Логин владельца: <input type="text" size="14" maxlength="24" name="owner" value="<?=$owner?>"/>

</td>
		
		</tr>
		<tr>
<td colspan="4" align="right">
	<i>На страничке события</i>: анонсов других событий ленты: <input type="text" name="last_limit" value="<?=$col->last_limit?>" size="4" maxlength="2"/>
		<br/>
	Сортировка: <select name="last_order">
		<?foreach($order as $o=>$v){?><option value="<?=$o?>"<?=($col->last_order==$o?' selected="yes"':"")?>><?=$v?></option><?}?>
	</select>
</td>
		</tr>
		<tr>
<td colspan="4" align="left">
	<i>На страничке колонки</i>: анонсов других событий: <input type="text" name="col_limit" value="<?=$col->col_limit?>" size="4" maxlength="2"/>
		<br/>
	Сортировка: <select name="col_order">
		<?foreach($order as $o=>$v){?><option value="<?=$o?>"<?=($col->col_order==$o?' selected="yes"':"")?>><?=$v?></option><?}?>
	</select>
		<br/>
	Стиль отображения записей: <select name="col_view">
		<?foreach($view as $o=>$v){?><option value="<?=$o?>"<?=($col->col_view==$o?' selected="yes"':"")?>><?=$v?></option><?}?>
	</select>
</td>
		</tr>


</table>

	<input type="hidden" name="col" value="<?=$col->id()?>"/>
	<input type="button" value="Сохранить изменения" onclick="$(this).disabled='yes';mr_Ajax_Form($('col-<?=$col->id()?>'),{update:$('col-<?=$col->id()?>').getParent()})"/>

</center>
</form>

<?
 }
 
/**
 * Сохраняет данные колонки
 *
 */
 static public function edit()
 {
 	$col = ws_comm_event_sec::factory( (int)$_POST["col"] );
 	if(!$col->comm_id) die("Колонка не найдена");
 	
 	/* @var $comm ws_comm */
 	$comm = $col->comm();
 	self::check_rights($comm);
 		
 	$title = trim(mr_text_string::remove_excess($_POST["title"]));
 	if(!$title) die("Вы не ввели название колонки. Изменения не сохранены.");
 	$col->title = $title;
 	
 	$name = trim(mr_text_string::remove_excess($_POST["name"]));
 	if(!$name || !mr_text_string::no_ru_sp($name)) die("Неверный формат адреса колонки. Изменения не сохранены.");
 	
 	$evc = ws_comm_event_sec::loadByName($name, $comm->id());
 	if($evc->name && $evc->id() != $col->id())
 		die("Колонка с таким адресом уже есть в сообществе. Изменения не сохранены.");
 	$col->name = $name;
 	
 	$col->description = trim(mr_text_string::remove_excess($_POST["descr"]));
 	
 	$av_spheres = explode(",", $comm->org_sphere);
 	$av_directs = explode(",", $comm->org_direct);
 	
 	$sent_sph = $_POST["sphere"];
 	$sent_dir = $_POST["direct"];
 	
 	$sph = array();
 	$dir = array();
 	
 	foreach($sent_sph as $s) if(in_array($s, $av_spheres)) $sph[] = $s;
 	foreach($sent_dir as $d) if(in_array($d, $av_directs)) $dir[] = $d;
 	
 	$col->org_sphere = join(",", $sph);
 	$col->org_direct = join(",", $dir);
 	
 	$col->display = $_POST["display"];
 	$col->strong = $_POST["strong"];
 	$col->geo = in_array("real", $av_spheres) ? $_POST["geo"] : "no";
 	
 	$col->apply = $_POST["apply"];
 	if($col->apply == "column")
 	{
 		
 		$owner = $_POST["owner"];
 		if(!$owner) $col->owner = 0;
 		else {
 			$owner = ws_user::getByLogin($owner);
 			if(!$owner->is_member($comm->id())) $col->owner = 0;
 			else $col->owner = $owner->id();
 		}
 		
 	} else $col->owner = 0;
 	
 	$col->last_limit = $_POST["last_limit"];
 	$col->last_order = $_POST["last_order"];
 	
 	$col->col_limit = $_POST["col_limit"];
 	$col->col_order = $_POST["col_order"];
 	$col->col_view = $_POST["col_view"];
 	
 	echo $col->save() ? "Изменения сохранены успешно." : "Сохранить изменения не удалось.";
 }
 
/**
 * Форма создания новой колонки
 *
 */
 static public function ajax_new()
 {
 	self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
		
?>
<form method="post" action="/x/comm-adm-events/create" id="ncform"><center>

Адрес колонки: <input type="text" name="name" size="8" maxlength="12"/>, Название: <input type="text" name="title" size="16" maxlength="36"/>

<input type="hidden" name="comm" value="<?=$comm->id()?>"/>
	<input type="button" value="Создать колонку" onclick="$(this).disabled='yes';mr_Ajax_Form($('ncform'),{update:$('new-col')})"/>


</center></form>
<?
 }
 
/**
 * Создание новой колонки
 *
 */
 static public function create()
 {
	self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
	
	$title = trim(mr_text_string::remove_excess($_POST["title"]));
 	if(!$title) die("Вы не ввели название колонки. Колонка не была создана.");
 	
 	$name = trim(mr_text_string::remove_excess($_POST["name"]));
 	if(!$name || !mr_text_string::no_ru_sp($name)) die("Неверный формат адреса колонки.");
 	
 	$evc = ws_comm_event_sec::loadByName($name, $comm->id());
 	if($evc->name)
 		die("Колонка с таким адресом уже есть в сообществе.");
 		
 	$col = ws_comm_event_sec::create($comm->id(), $name, $title);
	if(!$col->name) die("Не удалось сохранить новую колонку.");
	
	echo "Колонка успешно создана";
	self::ajax_edit($col);
 }
 
/**
 * Удаление колонки - варианты
 *
 */
 static public function ajax_delete()
 {
  self::check_rights($comm = ws_comm::factory( (int)$_POST["comm"] ));
  
  $col = ws_comm_event_sec::factory( (int)$_POST["col"] );
 	if($col->comm_id != $comm->id()) die("Колонка не найдена");
 	
?>

<form method="post" action="/x/comm-adm-events/unite">
	Колонка может быть объединена с другой. Введите адрес колонки этого сообщества: <input type="text" size="6" name="target"/> <input type="button" value="Объединить" onclick="$(this).disabled='yes';mr_Ajax_Form($(this).getParent(),{update:$('up-<?=$col->id()?>')})"/>
	<input type="hidden" name="col" value="<?=$col->id()?>"/>
</form>
<form method="post" action="/x/comm-adm-events/delegate">
	Колонка может быть передана другому сообществу. Введите адрес (имя) сообщества: /-<input type="text" size="6" name="target"/> <input type="button" value="Передать" onclick="$(this).disabled='yes';mr_Ajax_Form($(this).getParent(),{update:$('up-<?=$col->id()?>')})"/>
	<input type="hidden" name="col" value="<?=$col->id()?>"/>
</form>
<form method="post" action="/x/comm-adm-events/delete">
	Колонка может быть удалена со всем своим содержимым. Чтобы поступить так, нажмите кнопку: 
		<input type="button" value="Удалить" onclick="$(this).disabled='yes'; if(confirm('Вы уверены, что хотите произвести необратимое удаление данной колонки?')) mr_Ajax_Form($(this).getParent(),{update:$('up-<?=$col->id()?>')})"/>
	<input type="hidden" name="col" value="<?=$col->id()?>"/>
</form>

<?
 }
 
/**
 * Безвозвратное удаление
 *
 */
 static public function delete()
 {
  $col = ws_comm_event_sec::factory( (int)$_POST["col"] );
 	if(!$col->comm_id) die("Колонка не найдена");
  self::check_rights($comm = $col->comm());
  
  $col->delete();
  echo "Колонка безвозвратно удалена.";
 }
 
/**
 * Объединение с другой колонкой
 *
 */
 static public function unite()
 {
  $col = ws_comm_event_sec::factory( (int)$_POST["col"] );
 	if(!$col->comm_id) die("Колонка не найдена");
  self::check_rights($comm = $col->comm());
  
  $target = ws_comm_event_sec::loadByName( $_POST["target"], $comm->id() );
  if(!$target->name || $target->id() == $col->id())
  	die("Целевая колонка не найдена или совпадает с исходной.");
  	
  $col->delete( $target );
  echo "Колонка объединена с колонкой ", $target, " и больше не существует независимо.";
 }
 
/**
 * Передать колонку другому сообществу -- и с концами
 *
 */
 static public function delegate()
 {
  $col = ws_comm_event_sec::factory( (int)$_POST["col"] );
 	if(!$col->comm_id) die("Колонка не найдена");
  self::check_rights($comm = $col->comm());
  
  $target = ws_comm::byName( $_POST["target"] );
  if(!$target->name || $target->id() == $comm->id())
  	die("Целевое сообщество не найдено или оно совпадает с исходным.");
  	
  	$col->delete( $target );
 	
 	echo "Колонка передана сообществу ", $target;
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