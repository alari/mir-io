<?php class x_ws_ajax_geo implements i_xmod {
	
 static public function action($x)
 {
 	if($x == "usrs")
 	{
		$city = ws_geo_city::factory((int)$_POST["city"]);
 		if(!$city->name) die("Неизвестный город");
 		$usrs = ws_user::several("city='$city->name' ORDER BY activity DESC, lastlogged DESC");
 		$prevact = -1;
 		$small = 0;
 		$italic = 0;
 		foreach($usrs as $u)
 		{
 			if($prevact>0 && $u->activity == 0)
 			{
 				echo "<br /><center><b>Пассивные</b></center><small>";
 				$small = 1;
 			} elseif($prevact>1 && $u->activity == 1)
 			{
 				echo "<br /><center><b>Приходящие</b></center><i>";
 				$italic = 1;
 			}
 			echo $u, "<br />";
 			$prevact = $u->activity;
 		}
 		if($italic) echo "</i>";
 		if($small) echo "</small>";
 		exit;
 	}
 	
 	if($x == "findcity")
 	{
 		$startwith = addslashes(ucfirst(mr_text_string::remove_excess( $_POST["str"] )));
 		$insertin = mr_text_string::remove_excess( $_POST["insert"] );
 		$cities = ws_geo_city::several("name LIKE '$startwith%'", "u_active DESC LIMIT 7");
 		if(count($cities))
 		{
 			foreach ($cities as $c)
	 		{
	 			/* @var $c ws_geo_city */
	 			
	 			echo "<div onclick=\"$('$insertin').value='".$c->name."';$('$insertin').onblur()\">";
	 			echo "<b>".$c->name."</b>, ".($c->region()?"<small>".$c->region()->title."</small>,":"")." <i>".$c->country()->name."</i>";
	 			echo "</div>";
	 		}
 		} else echo "Городов, начинающихся с $startwith, в базе не найдено.";
 		
 		echo '<script type="text/javascript">$("'.$insertin.'").stringInput.cacheDiv("'.$startwith.'");</script>';
 		
 		exit;
 	}
 	
 	if(!ws_self::is_allowed("geo")) die("У вас нет прав на манипуляции с городами");
 	
 	switch($x)
 	{
 		case "unhandled":
 			$r = mr_sql::query("SELECT DISTINCT u.city FROM mr_users u LEFT JOIN mr_geo_cities c ON c.name=u.city WHERE c.id IS NULL AND u.city!=''");
 			
 			if(!mr_sql::num_rows($r)) die("<strong>Нет неописанных городов</strong>");
?>
	<form method="post" action="/x/ajax-geo/unhandled/action" id="unh-form">
Неописанные города:
	&nbsp; &nbsp;
<select name="un"><?while($c = mr_sql::fetch($r, mr_sql::get)){?><option><?=$c?></option><?}?></select>
	<br /><br />
Выберите описанный город:
	&nbsp; &nbsp;
<?=ws_geo_city::form_select("city", "id")?>
	<br /><br />
Или опишите его:<br />
<input type="text" name="new_name" size="30" />
	&nbsp; &nbsp;
<?=ws_geo_country::form_select()?>
	<br /><br />
<input type="button" value="Сохранить изменения"  onclick="javascript:$(this).disabled='yes';mr_Ajax_Form($('unh-form'),{update:$('geo-manage')});return false;"/>
	</form>
<?
 			
 		break;
 		
 		case "unhandled/action":
 			$old = $_POST["un"];
 			if(!$old) throw new RedirectException("/x/ajax-geo/unhandled");
 			
 			$cid = (int)$_POST["city"];
 			if(!$cid)
 			{
 				$new_name = mr_text_string::remove_excess(trim($_POST["new_name"]));
 					if(!$new_name) $new_name = $old;
 				$new_code = mr_text_string::remove_excess(trim($_POST["country"]));
 				if(strlen($new_code) != 2)
 					 throw new RedirectException("/x/ajax-geo/unhandled");
 					 
 				$city = ws_geo_city::byName($new_name);
 				if($city->id()<=0) $city->create($new_code);
 				$cid = $city->id();
 			} else $city = ws_geo_city::byID($cid);
 			
 			mr_sql::qw("UPDATE mr_users SET city=? WHERE city=?", $city->name, $old);
 			
 			throw new RedirectException("/x/ajax-geo/unhandled");
 			
 		break;
 			 		
 		case "citypic":
 			?><iframe width="90%" frameborder="0" height="290" align="absmiddle" src="/x/ajax-geo/citypic/iframe<?=($cid?"?cid=".$cid:"")?>" scrolling="No"></iframe><?
 		break;
 			
 		case "citypic/iframe":
?>
<center>
<form method="post" action="/x/ajax-geo/citypic/action" enctype="multipart/form-data">
	<strong>Картинка для города</strong>
	<br /><?=ws_geo_city::form_select("city", "id", (int)@$_GET["cid"])?>
	<br /><br />Изображение: &nbsp; &nbsp; <input type="file" name="pic" />
	<br /><br />
	<input type="submit" value="Закачать картинку" onclick="$(this).disabled='yes'" />
</form>
</center>
<?
 		break;
 		
 		case "citypic/action":
 			$city = ws_geo_city::byID((int)$_POST["city"]);
 			if(!$city->name)
 				throw new RedirectException("/x/ajax-geo/citypic");
 				
 			$ftp = new ws_fileserver;
 			if(!$ftp->ok()) die("<strong>Не удаётся соединиться с файл-сервером</strong>");
 				
 			if($city->pic_src)
 			{
 				if($ftp->delete($city->pic_src))
 					echo "Старое изображение удалено успешно<br />";
 				else echo("<strong>Не удаётся удалить старое изображение на файл-сервере</strong>");
 			}
 			
 			if(!$_FILES["pic"]["tmp_name"]) die("Новый файл картинки не закачен");
 			
 			
			list(, , $type) = getimagesize($_FILES["pic"]["tmp_name"]);
			$filename = "city/".$city->name;
			switch($type){
				case 1: $filename .= ".gif"; break;
				case 2: $filename .= ".jpg"; break;
				case 3: $filename .= ".png"; break;
				default: die("Неподдерживаемый формат рисунка");
			}
			
			if(!$ftp->put($_FILES["pic"]["tmp_name"], "city", $city->id(), $filename)) die("Закачка файла картинки на удалённый сервер завершилась провалом");
			
			$city->pic_src = $filename;
			$city->save();
			
			die('<center>Картинка для города успешно закачена<br /><br /><a style="color:black" href="/x/ajax-geo/citypic">Закачать ещё картинку</a></center>');
 			
 		break;
 		
 		case "handled":
?>
<strong>Правка существующего города</strong>
<form method="post" action="/x/ajax-geo/handled/action" id="han-form">
Город: <?=ws_geo_city::form_select("city", "id")?>
<br /><br />
Сменить страну: <?=ws_geo_country::form_select()?>
<br /><br />
Сменить название: <input type="text" name="name" size="30" />
<br /><br />
<input type="hidden" name="delete" value="no" /><input type="checkbox" name="delete" value="yes" /> &ndash; Удалить город
<br /><br />
<input type="button" value="Сохранить изменения" onclick="javascript:$(this).disabled='yes';mr_Ajax_Form($('han-form'),{update:$('geo-manage')})" />
</form>
<?
 		break;
 		
 		case "handled/action":
 			$city = ws_geo_city::factory((int)$_POST["city"]);
 			if(!$city->name) die("Город не выбран");
 			
 			echo $city->flag(), " ", $city;
 			
 			if($_POST["delete"] == "yes")
 			{
 				$city->delete();
 				die(" - Запись о городе была удалена");
 			}
 			
 			$new_country = $_POST["country"];
 			$new_name = mr_text_string::remove_excess(trim($_POST["name"]));
 			
 			if($new_name)
 			{
	 			$new = ws_geo_city::byName($new_name);
	 			if($new->id()>0) {
	 				$city->delete($new);
	 				$city = $new;
	 			} else {
	 				mr_sql::qw("UPDATE mr_users SET city=? WHERE city=?", $new_name, $city->name);
	 				$city->name = $new_name;
	 			}
 			}
 			if(strlen($new_country) == 2)
	 			$city -> country = $new_country;
	 			
	 		$city->save();
 			
	 		
	 		echo " - Превратился в ", $city->flag(), " ", $city, "<br />";
 			die("Изменения сохранены");
 			
 		break;
 		
 		case "regions":
 			
			$regs = ws_geo_region::several("1=1");
			
 			
 		break;
 		
 		case "countries":
 			
 		break;
 		
 		default: die("Undefined action: $x");
 		
 	}
 }
 
	}
?>