<?php class x_ws_ajax_login implements i_xmod, i_locale {

	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

 static public function action($x)
 {
 	if(@$_POST["login"])
 		ws_self::authorize($_POST["login"], $_POST["pwd"], $_POST["check_ip"]=="yes"?true:false, $_POST["hide_me"]=="yes"?true:false);
//TODO: divide logic into several parts; make response JSON
 	if($x == "out" && ws_self::ok())
 	{
 		ws_self::end();
 		setcookie("autologin", null, null, "/", ".mir.io");
 	}

 	if(ws_self::ok()) switch($x)
 	{
 		case "remind":

			self::reminder();

 		break;

 		case "rem/delete":
 			self::rem_delete();
 			break;
 		case "rem/zeroize":
 			self::rem_zeroize();
 			break;


 		default:
 		$msgs_new = ws_self::self()->msgs()->count();
 		$rem_new = ws_user_remind::check();
?>

<script type="text/javascript">
	$('header-off').style.display = 'none';
	$('header-form').style.display = 'none';
	$('header-on').style.display = 'block';
	$('login-self').set( 'html', '<?=str_replace("'", "\\'", ws_self::self()->link())?>' );
	$('lb-msg').set( 'html', '<?=self::$locale["msgs"].($msgs_new?"&nbsp;<b>(+$msgs_new)</b>":"")?>' );
	$('lb-reminder').set( 'html', '<?=self::$locale["reminder"].($rem_new?"&nbsp;<b>(+$rem_new)</b>":"")?>' );

	<?if($rem_new){?>
	$('lb-reminder')
	.set('href', 'javascript:void(0)')
	.removeEvents("click")
	.addEvent("click", function(){
		window.open('/x/ajax-login/remind','','menubar=0,scrollbars=1,status=0,width=300,height=150');
	});
	<?} else {?>
	$('lb-reminder')
	.set('href', '<?=mr::host("own")?>/reminders/')
	.removeEvents("click");
	<?}?>

</script>

<?
 	} else {
?>

<script type="text/javascript">
	$('header-on').style.display = 'none';
	if($('header-form').style.display == 'none' && $('header-off').style.display == 'none')
		$('header-off').style.display = 'block';
	if(!$('in_login').value) $('in_login').value = Cookie.read('login')?Cookie.read('login'):"";
</script>

<?
 	}
 }

 static public function reminder()
 {
 	$sbs = ws_user_remind::usr_subscribes(ws_self::id(), true);

?>
<html>
 <head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <title>Оповещения на Мире Ио</title>
 </head>
<body>
<?

	if(count($sbs))
	{
 	foreach($sbs as $s) self::rem_item($s);
	?>

<script type="text/javascript">
 var arr = document.getElementsByTagName("a");
 for(var i=0; i<arr.length; i++)
 {
 	var a = arr[i];
 	if(!a.modified && a.className!='rem-sub')
 	{
 		a.href = 'javascript:opener.window.location.href=\''+a.href+'\';window.close()';
 		a.modified = 1;
 	}
 }
</script>
<?} else {?>
<script type="text/javascript">
window.close();
</script>
<?}?>
</body></html>
<?
 }

 static public function rem_item($rem)
 {

 	switch ($rem->type) {
 		case ws_user_remind::type_blog_resp:
 			$obj = ws_blog_anonce::factory( $rem->target );
 		break;
 		case ws_user_remind::type_pub_resp:
 			$obj = ws_libro_pub::factory( $rem->target );
 		break;
 		case ws_user_remind::type_comm_event_resp:
 			$obj = ws_comm_event_anonce::factory( $rem->target );
 		break;
 		case ws_user_remind::type_society_thread_notes:
 			$obj = ws_comm_disc_thread::factory( $rem->target );
 			$obj = $obj->link( floor(($obj->notes()-$rem->new)/$obj->perpage) );
 		break;
 		case ws_user_remind::type_blog_resp:
 			$obj = ws_blog_anonce::factory( $rem->target );
 		break;

 		default:
 			$obj = "Unknown Event";
 	}

 	echo $obj, "(+", $rem->new, ")<br/>";
	echo "<small><a href='/x/ajax-login/rem/zeroize?type={$rem->type}&chapter={$rem->target}' class='rem-sub'>Снять оповещение</a> &bull; <a href='/x/ajax-login/rem/delete?type={$rem->type}&chapter={$rem->target}' class='rem-sub'>Отписаться</a></small>";
	echo "<br/>";
 }

 static public function rem_zeroize()
 {
	ws_user_remind::zeroize((int)@$_GET["type"], (int)@$_GET["chapter"]);
	throw new RedirectException("/x/ajax-login/remind");
 }

 static public function rem_delete()
 {
	ws_user_remind::delete((int)@$_GET["type"], (int)@$_GET["chapter"], ws_self::id());
	throw new RedirectException("/x/ajax-login/remind");
 }

	}
?>