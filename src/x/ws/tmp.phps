<?php class x_ws_tmp implements i_xmod {
	
 static public function action($x)
 {
 	$r = mr_sql::query( "SELECT id, disc_id, comm_id FROM mr_disc_threads" );
 	 	
 	$d = mr_sql::query(" SELECT id, comm_id FROM mr_discussions ");
 	$discs = array();
 	$dcomms = array();
 	while($dd = mr_sql::fetch($d, mr_sql::obj ))
 	{
 		$discs[ $dd->comm_id ][] = $dd->id;
 		$dcomms[ $dd->id ] = $dd->comm_id;
 	}
 	
 	while ($f = mr_sql::fetch($r, mr_sql::obj)) if($f->comm_id != $dcomms[$f->disc_id]) {
 		$disc_id = $discs[ $dd->comm_id ];
 		if(count($disc_id)==1) $disc_id = $disc_id[0];
 		elseif(count($disc_id)>1) $disc_id = 5;
 		else continue;
 		
 		mr_sql::qw("UPDATE mr_disc_threads SET disc_id=? WHERE id=?", $disc_id, $f->id);
 	}
 	
 	return;
 	
 	$country="by";
 	
 	$tmp_2=array(
		"Брестская область"=>"Брест",
		"Гомельская область"=>"Гомель",
		"Гродненская область"=>"Гродно",
		"Могилёвская область"=>"Могилёв",
		"Минская область"=>"Минск",
		"Витебская область"=>"Витебск",
		"Город Минск"=>"Минск"
 	);
	/*
	foreach($tmp_2 as $reg=>$center)
	{
		$c = ws_geo_city::byName($center);
		if(!$c->id()) $c->create($country);
		$reg = ws_geo_region::create($reg, "", $country, 0, $c->id());
		$c->region = $reg->id();
	}*/
 
 	$regs = ws_geo_region::several("country='$country'");
 	
 	$cities = ws_geo_city::several("region=0 AND country='$country'");
 	
 	function reg_form($city, $regs)
 	{
?>
<select name="region_<?=$city->id()?>">
	<option value="0">-</option>
	<?foreach ($regs as $r){?><option value="<?=$r->id()?>"<?=(strpos($r->title, $city->name)!==false?' selected="yes"':"")?>><?=$r->title?></option><?}?>
	<input type="hidden" name="main_<?=$city->id()?>" value="no"/><input type="checkbox" name="main_<?=$city->id()?>" value="yes"/>
	<input type="hidden" name="delete_<?=$city->id()?>" value="no"/><input type="checkbox" name="delete_<?=$city->id()?>" value="yes"/>
</select>
<?
 	}
 	
 	if($x!="save"){
 	
?>
<form action="/x/tmp/save" method="post" enctype="multipart/form-data">
<?
 	
 	foreach($cities as $c){
?>
	<div>
	
	<?=$c?>
	<?reg_form($c, $regs);?>
	
	</div>
<?
 	}
?>

<input type="submit" value="Сохранить"/></form>

<?
 	return;
 	
 	}
 	
 	foreach($cities as $c)
 	{
 		$reg = (int)$_POST["region_".$c->id()];
 		if($reg)
 		{
 			$reg = ws_geo_region::factory($reg);
 			if(!$reg->title) continue;
 			$c->region = $reg->id();
 			$c->save();
 			if($_POST["main_".$c->id()]=="yes")
 			{
 				$reg->maincity = $c->id();
 				$reg->save();
 			}
 		} elseif($_POST["delete_".$c->id()] == "yes")
 			$c->delete();
 	}
 	
 	echo "ok";
 	
 }	
	}
?>