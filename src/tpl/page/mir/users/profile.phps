<?php class tpl_page_mir_users_profile extends tpl_page implements i_tpl_page_rightcol, i_locale   {

	protected $user;

	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "rightcol";

 	/* @var $user ws_user */
 	$user = $this->user = ws_user::getByLogin(mr::subdir());

 	$this->title = $user->name();

 	$this->content = "<h1>".$user->name()."</h1>";

 	if($user->userinfo){
	 	$f = new mr_xml_fragment;
	 	$f->loadXML($user->userinfo);

	 	$this->content .= $f->realize();
 	}

 	ob_start();
 	if(ws_self::is_allowed("see ip users") && $user->login != 'moderator') {
 		$usrs = ws_user::several("remote_addr='".$user->remote_addr."' AND id!=".$user->id()." AND login!='moderator'");
 		if(count($usrs)) {
 			echo "<hr/>IP совпадает с: ";
 			foreach($usrs as $n=>$u) echo ($n?", ":""), $u;
 		}
 	}
 	$this->content .= ob_get_clean();
 }

 public function col_right()
 {
 	/* @var $user ws_user */
 	$user = $this->user;

 	$memberof = $user->memberof(1);

 	$cols = ws_comm_event_sec::several("owner=".$user->id());

 	if(ws_self::ok())
 	{
?><p><a href="<?=mr::host("own")?>/msg/new.to-<?=$user->login?>.xml">Написать Личное Сообщение</a></p><?
 	}

 	if(count(ws_blog_item::several_visible($user->id(), 1))) {
 		echo "<p><a href=\"".$user->href("")."\">Блог".($user->blog_title ?  ": ".$user->blog_title:"")."</a></p>";
 	}

 	if($user->city)
 	{
 		$city = ws_geo_city::byName($user->city);
 		echo "<p>".self::$locale["city"].": ", $city;
 		if($city->id())
 		{
 			echo " ".$city->flag();
 			$region = $city->region();
 			if($region && $city->id()!=$region->maincity) echo "<br/>", $region;
 		}
 		echo "</p>";
 	}

 	if(count($memberof))
 	{
?>
<p><?=self::$locale["memberof"]?>:
<?foreach($memberof as $comm=>$status){?>
<br />&nbsp; &nbsp; <?=ws_comm::factory($comm)?> <i>(<?=ws_comm::mem_status($status)?>)</i>
<?}?>
</p>
<?
 	}
 	if(count($cols))
 	{
?>
<p><?=self::$locale["cols"]?>:
<ul>
<?foreach($cols as $c){?>
<li><?=$c?> <i>(<?=$c->comm()?>)</i></li>
<?}?>
</ul>
</p>
<?
 	}

 	$cycles = ws_libro_pub_cycle::byOwner($user->id(), 1);
 	if(count($cycles)){
?>
<p><a href="<?=$user->href("pubs")?>"><?=self::$locale["cycles"]?></a>:
<ul>
<?foreach($cycles as $c) if($c->is_showable()){
	echo "<li>";
	echo $c;
	echo "</li>";
}?>
</ul>
</p>
<?
 	}

 	if($user->banned_till > time()){
 ?>

<p>Забанен до <?=date("d.m.Y", $user->banned_till)?> <i>(<?=$user->banned_reason?>)</i></p>

<?
 	} elseif(ws_self::is_member(1,ws_comm::st_curator)){
 ?>

<div>
 <form method="get" action="/x/site-ban/eventually" onsubmit="this['ok'].disabled=true; mr_Ajax_Form($(this), {update:$(this).getParent()});return false;">
 <p>Забанить пользователя на время. Причина:</p>
 <input type="text" name="reason"/>
  <input type="hidden" name="user" value="<?=$user->id()?>"/>
  <input type="submit" value="ok" name="ok"/>
 </form>
 <?if($user->registration_time + 86400*14 > time() || ws_self::is_member(1,ws_comm::st_leader)){?>
 <form method="get" action="/x/site-ban/forever" onsubmit="this['ok'].disabled=true; mr_Ajax_Form($(this), {update:$(this).getParent()});return false;">
 <p>Забанить пользователя навсегда</p>
 <input type="text" name="reason"/>
  <input type="hidden" name="user" value="<?=$user->id()?>"/>
  <input type="submit" value="ok" name="ok"/>
 </form>
 <?}?>
</div>

 <?
 	}

 }
	}
?>