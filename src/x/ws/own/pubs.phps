<?php class x_ws_own_pubs extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function move_pub()
 {
 	if(!ws_self::ok()) die("alert('Авторизация утеряна. Изменения не сохранены.');");
 	
 	$pid = (int)$_POST["itemID"];
 	
 	$pub = ws_libro_pub_item::factory( (int)$pid );
 	if($pub->author != ws_self::id())
 		 die("alert('Произведение не найдено. Изменения не сохранены.');");
 		 
 	$newIndex = (int)$_POST["newIndex"];
 	
 	$cid = (int)$_POST["newParentID"];
 	$newCycle = ws_libro_pub_cycle::factory( (int)$cid );
 	
 	if(!$newCycle || $newCycle->user()->id() !=ws_self::id())
 		die("alert('Новый цикл не найден. Изменения не сохранены.');");
 		
 	if($newCycle->id() == $pub->cycle()->id())
 	{
 		
 		$pub->cycle()->movePub( $pub, $newIndex );
 		
 	} else { 		
 		$pub->cycle()->removePub( $pub );
 		$newCycle->addPub( $pub );
 		$newCycle->movePub( $pub, $newIndex );
 		
 		if(!$oldCycle->id())
 			echo "\$('cycle-$oCid-container').dispose();"; 		
 	}
 }
 
 static public function hidden()
 {
 	$pid = (int)$_POST["pid"];

 	if(!$pid) die("//no pid");
 	$pub = ws_libro_pub_item::factory($pid);

 	if($pub->author != ws_self::id()) die("//wrong author");

 	if($pub->hidden == "yes") {
?>
<script>
alert("Это произведение было скрыто модератором.");
</script>
<?
 	} elseif($pub->hidden == "auth") {
 		$pub->hidden = "no";
 		if($pub->save()){
?>
<script>
$('pub-<?=$pid?>').removeClass("hidden").getElement(".chidden").set("src", "/style/img/own/hide.gif").set("title", "Скрыть произведение");
</script>
<?		}
 	} elseif($pub->hidden == "no") {
 		$pub->hidden = "auth";
 		if($pub->save()){
?>
<script>
$('pub-<?=$pid?>').addClass("hidden").getElement(".chidden").set("src", "/style/img/own/show.gif").set("title", "Показать произведение");
</script>
<? 		}
 	}
 }
 
 static public function anonymous()
 {
 	$pid = (int)$_POST["pid"];

 	if(!$pid) die("//no pid");
 	$pub = ws_libro_pub_item::factory($pid);

 	if($pub->author != ws_self::id()) die("//wrong author");
 	
 	$pub->anonymous = "no";
 	if($pub->save()){
?>
<script>
$('pub-<?=$pid?>').removeClass("anonymous").getElement(".canonymous").dispose();
</script>
<?
 	}
 }
 
	}
?>