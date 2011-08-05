<?php class x_ws_site_sidenav extends x implements i_xmod, i_locale {

 static protected $locale = array(), $lang = "";

 static public function locale($data, $lang)
 {
	self::$locale = $data;
	self::$lang = $lang;
 }

 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }

 static public function chat()
 {
?>
 	<strong><a href="javascript:void(0)" onclick="var chat_msg=prompt('<?=@self::$locale["chat.prompt"]?>'); if(!chat_msg) return; mr_Ajax({url:'/x/site-sidenav/chat/add',data:{msg:chat_msg},update:$('subsidenav-chat')}).send();"><?=self::$locale["chat.say"]?></a>:</strong>
<?

 	$msgs = ws_chat_msg::several(0, 18);
 	foreach ($msgs as $m)
 	{
?>

<div class="chat-msg">
	<span class="chat-time"><?=date("d.m.y H:i:s", $m->time)?> <?=$m->user()?></span>
		<br/>
	<?=$m?>
</div>

<?
 	}

?>
<a href="javascript:void(0)" onclick="window.open('/x/site-sidenav/chat/window','','menubar=0,scrollbars=1,status=0,width='+$('subsidenav-chat').getCoordinates().width+',height='+$('subsidenav-chat').getCoordinates().height);return false;"><?=self::$locale["chat.window"]?></a>

<?
 }

 static public function chat_window()
 {
 	$page = (int)@$_GET["page"];
 	$perpage = 25;

 	if($_SERVER['REQUEST_METHOD'] == "POST")
 	{
 		$msg = mr_text_string::remove_excess(trim($_POST["msg"]));
 		if(  !$msg || !ws_chat_msg::can_add() || !ws_chat_msg::add($msg) )
 	    echo "<b>Недостаточно прав для добавления нового сообщения</b>";
 	}
 ?>
<html>
 <head>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
  <title>Чат на Мире Ио</title>
  <link rel="stylesheet" href="http://mir.io/style/css/default.css" type="text/css"/>
 </head>
 <body style="width:auto !important;overflow:auto">
 <div id="subsidenav-chat">
 	<small><a href="javascript:void(0)" onclick="window.location.reload(true)">Обновить</a></small>
  	<center><form method="post">
  	 <input type="text" name="msg"/><input type="submit" value="Сказать"/>
  	</form></center>
<?

 	$msgs = ws_chat_msg::several($page, $perpage);
 	foreach ($msgs as $m)
 	{
 		/* @var $m ws_chat_msg */
?>

<div class="chat-msg">
<?if(ws_self::is_allowed("adm_chat")){?> <a style="color:red;font-size:5px;" href="/x/site-sidenav/chat/delete?id=<?=$m->id()?>&page=<?=$page?>" title="delete">x</a><?}?>
	<span class="chat-time"><?=date("d.m.y H:i:s", $m->time)?> <a href="javascript:void(0)" onclick="javascript:opener.window.location.href='<?=$m->user()->href()?>';window.close()"><?=$m->user()->name()?></a></span>
		<br/>
	<?=$m?>
</div>

<?
 	}

?>

<hr/>
<center>
Страница: <?=($page+1)?>
<br/>
<?if($page) echo "&nbsp;<a href='?page=".($page-1)."'>Назад</a>";?>
<?if(count($msgs)==$perpage) echo "&nbsp;<a href='?page=".($page+1)."'>Вперёд</a>";?>
 </center>

 </div>
 </body>
</html>
 <?
 }

 static public function chat_add()
 {
 	$msg = mr_text_string::remove_excess(trim($_POST["msg"]));
 	if( $msg && ws_chat_msg::can_add() && ws_chat_msg::add($msg) )
 		self::chat();
 	else die("<b>Недостаточно прав для добавления нового сообщения</b>");
 }

 static public function chat_delete()
 {
 	if(!ws_self::is_allowed("chat_adm")) die("<b>Access denied</b>");

 	ws_chat_msg::factory($_GET["id"])->delete();

 	self::chat_window();
 }

 static public function online()
 {

 	echo "<strong>".self::$locale["online.title"].":</strong>";
 	echo '<div id="usrs-online">';

	 $online = mr_sql::query("SELECT user_id, status, position, time FROM mr_sessions WHERE time>".(time()-900)." AND status".(ws_self::id()==2?"!=":">")."0 ORDER BY status DESC, user_id");

	 $count = mr_sql::fetch("SELECT COUNT(IF(status>0,1,null)) AS auth, COUNT(IF(status<0,1,null)) AS hidden, COUNT(IF(status=0,1,null)) AS guests FROM mr_sessions WHERE time>UNIX_TIMESTAMP()-900", mr_sql::obj);

	 $usersArr = array();
	 $onlineArr = array();
	 while($f = mr_sql::fetch($online, mr_sql::obj))
	 {
	  $usersArr[] = $f->user_id;
	  $onlineArr[] = $f;
	 }

	 ws_user::several($usersArr);

	 if(!count($onlineArr)) echo "&nbsp;Никого нет";
	 else {
	  echo "&nbsp;&nbsp;&nbsp;Авторизовано: <b>", $count->auth+$count->hidden, "</b><br />\n";
	  foreach($onlineArr as $f)
	  {
	   $u = ws_user::factory($f->user_id);
	   echo "&nbsp;&bull; ".$u->link("profile", "status-".$f->status);
	   echo "<br/>\n";
	  }

	  $online = $count->hidden+$count->auth;
	  if($online) mr_sql::qw("UPDATE mr_site_stat SET max_online=? WHERE date=? AND max_online<? LIMIT 1", $online, date("ymd"), $online);


	  if($count->hidden) echo "&nbsp;&nbsp;&nbsp;<i>Из них спрятались: ", $count->hidden, "</i><br />\n";
	  if($count->guests>0) echo "&nbsp;&nbsp;&nbsp;<i>Гостей: ", $count->guests, "</i><br />\n";
	 }


	 echo "</div>";

 }

 static public function adm()
 {
 	if(!ws_self::is_allowed("metaadm")) die("<div class='admresp-server'>Access denied</div>");
?>

<form onsubmit="adm_act();return false;">
<div id="sidenav-adm-log" style="
	height: 270px;
	overflow: auto;
	padding-left: 2em;
	white-space: pre;
	font-weight: bold;
"></div>
<span style="
	width: 10%;
	text-align: center;
	font-weight: bold;
	color: red;">~/:</span>
<input type="text" id="sidenav-adm-msg" name="act" style="
	width: 80%;
	border: 0;
	border-bottom: 1px solid green;
	background: black;
	color: lightgreen;
"/>
<input type="submit" value="Act" style="
	color: red;
	background: black;
	border: 0;
	width: 10%;
"/>
</form>
<style type="text/css">
 .admresp-server
 {
  color: lightblue;
  padding-left: 2px;
  margin: 0;
 }
 .admresp-client
 {
  margin: 0;
 }
</style>

<script type="text/javascript">

 sidenav_omit_overloading["adm"] = true;

 function adm_act()
 {
 	var act = $('sidenav-adm-msg').value;
 	mr_Ajax_Request({url:'/x/site-sidenav/adm/action',data:{query:act},onSuccess:adm_act_response}).send();
 }
 function adm_act_response(respText, respXML)
 {
 	mr_Ajax_defaults.onSuccess(respText, respXML);

 	$('sidenav-adm-msg').value = '';
 	$('sidenav-adm-log').set('html', $('sidenav-adm-log').get('html')+respText);
 	$('sidenav-adm-log').scrollTo(0, $('sidenav-adm-log').getScrollSize().y);
 }
 $('sidenav-adm-msg').focus();
</script>

<?
 }

 static public function adm_action()
 {
 	if(!ws_self::is_allowed("metaadm")) die("<div class='admresp-server'>Access denied</div>");

 	$query = mr_text_string::remove_excess($_POST["query"]);
 	if(!$query) die("<div class='admresp-server'>No command given</div>");

 	echo "<div class='admresp-client'>$query</div>";

 	echo "<div class=\"admresp-server\"><pre>";

 	list($action, $query) = explode(" ", $query, 2);

 	switch($action)
 	{
 		case "user":
 			if($query == "help")
 			{
 ?>
 	mr:command promt
 		USER HELP
 	Print "user help" to get this message;
 	Print "user $login $command $attrs" to deal with $login the following:
 		set: "set $field $value" sets the value to users database
 		get: "get $field" returns a value of a field
 		inclub: "inclub $day.$month.$year" for paid users
 		getallows: returns users allows and denies
 		allow: "allow $what ($comm=0)?" delete deny row or create allow row. Print comm name in $comm if you wish
 		deny: "deny $what ($comm=0)?" create deny row or delete allow row. Print comm name in $comm if you wish
 		ban: "ban $day.$month.$year ($reason='')" ban user till date by the reason
 		finance: "finance +-$cash $comment=''"
 <?
 				break;
 			}
 			list($login, $deal, $query) = explode(" ", $query, 3);
 			$uid = ws_user::getIdByLogin($login);
 			if(!$uid)
 			{
 				echo "Пользователь $login не найден.";
 				break;
 			}
 			$u = ws_user::factory($uid);
 			switch($deal)
 			{
 				case "set":
 					list($deal, $query) = explode(" ", $query, 2);
 					if($deal == "id" || $deal == "login") return;
 					if($deal == "md5") $query = md5($query);
 					$u->$deal = $query;
 					$u->save();
 					echo "$login-&gt;$deal updated";
 				break;
 				case "get":
 					$r = $u->$query;
 					if($query == "lastlogged" || $query == "registration_time" || $query == "in_club")
 						$r .= " (".date("d m Y", $r).")";
 					echo $login."-&gt;".$query.": { ".htmlspecialchars($r)." }";
 				break;
 				case "inclub":

 					#HARD-CODE
 					list($d, $m, $y) = explode(".", $query, 3);
 					$u->in_club = mktime(0, 0, 0, $m, $d, $y);
 					$u->save();
 					echo "$u->login is in club till $query";
 				break;
 				case "getallows":

 					#NOT IMPLEMENTED
 					echo $u->login." allows:\nnot implemented yet";
 				break;
 				case "allow": case "deny":

 					#HARD-CODE
 					list($what, $comm) = @explode(" ", $query, 2);
 					if($comm) $comm = mr_sql::fetch(array("SELECT id FROM mr_communities WHERE name=?", $comm), mr_sql::get);
 					mr_sql::qw("DELETE FROM mr_comm_allows WHERE user_id=? AND comm_id=? AND name=?", $u->id(), $comm, $what);
 					$need = $deal == "deny" ? false : true;
 					if($u->is_allowed($what, $comm) != $need)
 						mr_sql::qw("INSERT INTO mr_comm_allows(user_id, comm_id, name, value) VALUES(?, ?, ?, ?)", $u->id(), $comm, $what, $need?"yes":"no");
 					print "Done. Print \"user $u->login getallows\" to spectate the result";

 					echo "Not implemented yet";
 				break;
 				case "ban":

 					#HARD-CODE
 					list($date, $reason) = explode(" ", $query, 2);
 					$reason = mr_text_string::remove_excess(trim($reason));
 					list($d, $m, $y) = explode(".", $date, 3);
 					$t = mktime(0, 0, 0, $m, $d, $y);
 					if($t < time()) $t = 0;
 					$u->banned_till = $t;
 					if($reason) $u->banned_reason = $reason;
 					$u->save();
 					if($t) mr_sql::qw("DELETE FROM mr_sessions WHERE user_id=?", $u->id());
 					print $t ? "Done. User $u->login banned." : "Done. User $u->login is not banned anymore.";
 				break;
 				case "finance":

 					#HARD-CODE
 					list($cash, $comment) = explode(" ", $query, 2);
 					mr_sql::qw("INSERT INTO mr_user_finance(user_id, cash, comment, time) VALUES(?, ?, ?, UNIX_TIMESTAMP())",
 						$uid, $cash, $comment);
 					echo "Inserted #".mr_sql::insert_id()." (to ".$u->link().")";
 				break;
 			}
 		break;

 		case "system":

 			if($query == "update")
 			{
 				system("sh ../sh/update.sh");
 			} else echo "Undefined system command";
 		break;

 		case "help":
?>
	mr:command promt
		HELP
	core commands:
		user: print "user help" for details
		help: displays this message
<?
 		break;

 		default:
 			echo "Undefined command. Try to print help.";
 	}
 		echo "</pre></div>";
 }
	}
?>