<?php class x_ws_own_circle extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function delete()
 {
 	if(!ws_self::ok() || !ws_self::is_allowed("circle"))
 		die(" alert('Ошибка!'); ");
 		
 	$id = $_POST["id"];
 	$c = ws_user_circle::factory($id);
 	
 	if(!$c || !$c->id() || $c->user()->id() != ws_self::id())
 		die(" alert('Ошибка!'); ");

 	if(!$c->delete())
 		die(" alert('Ошибка!'); ");
?>
$('c-<?=$c->id()?>').remove();
<?


 }
 
 static public function follow()
 {
 	if(!ws_self::ok() || !ws_self::is_allowed("circle"))
 		die(" alert('Ошибка!'); ");
 		
 	$id = $_POST["id"];
 	$c = ws_user_circle::factory($id);
 	
 	if(!$c || !$c->id() || $c->user()->id() != ws_self::id())
 		die(" alert('Ошибка!'); ");
 		
 	$t = $_POST["t"];
 	$d = "follow_".$t;
 	
 	$new = $c->$d=="yes" ? "no" : "yes";
 	
 	$c->$d = $new;
 	$c->save();
 	
?>
$('<?=$t?>-<?=$id?>').setHTML('<?=($new=="yes"?"Да":"Нет")?>');
<?
 }
 
 static public function trust()
 {
 	if(!ws_self::ok() || !ws_self::is_allowed("circle"))
 		die(" alert('Ошибка!'); ");
 		
 	$id = $_POST["id"];
 	$c = ws_user_circle::factory($id);
 	
 	if(!$c || !$c->id() || $c->user()->id() != ws_self::id())
 		die(" alert('Ошибка!'); ");
 		
 	$t = $_POST["t"];
 	$d = "trust_".$t;
 	
 	$new = $c->$d=="yes" ? "no" : "yes";
 	
 	$c->$d = $new;
 	$c->save();
 	
?>
$('t-<?=$t?>-<?=$id?>').setHTML('<?=($new=="yes"?"Да":"Нет")?>');
<?
 }
 
	}
?>