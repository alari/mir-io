<?php class tpl_layout_ws extends tpl_layout implements i_locale {

	protected $css_prefix = "http://mir.io/style/css/", $css=array("default.css"), $ico="mir";
	static protected $locale = array(), $lang = "";

	protected $left = false;
	protected $right = false;

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

	public function __construct(i_tpl_page &$page)
	{
		$this->page = $page;

		$this->css = array_merge($this->css, $page->css());

		$this->ico = mr::site();
		if($this->page instanceof i_tpl_page_ico)
			$this->ico = $this->page->p_ico();

		if(!is_readable("style/ico/".$this->ico.".ico"))
			$this->ico = "mir";

		mr_seo_demon::handle($page);
	}

 public function realize()
 {
 	$class = "";
 	if($this->left) $class = "lc";
 	if($this->right) $class = ($class?$class." ":"")."rc";
 	if($class) $class = " class=\"$class\"";

 	ob_start();?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <title><?=$this->page->title()?></title>
  <meta name="description" content="<?=$this->page->description()?>"/>
  <meta name="keywords" content="<?=$this->page->keywords()?>"/>
  <?foreach($this->css as $href){?><link rel="stylesheet" type="text/css" href="<?=$this->css_prefix.$href?>" /><?}?>
  <link rel="SHORTCUT ICON" href="/style/ico/<?=$this->ico?>.ico"/>
  <script type="text/javascript" src="http://mir.io/style/js/core.js"></script>
  <script type="text/javascript" src="http://mir.io/style/js/more.js"></script>
  <script type="text/javascript" src="http://mir.io/style/js/mr.js"></script>
  <?$this->head()?>
  <?=$this->page->head()?>
</head><body>

<div id="container">
	<div id="header">
		<?$this->main_menu();?>
		<?$this->header();?>
		<?$this->sub_menu();?>
	</div>
	<div id="envelop">
		<div id="centrer"<?=$class?>>
			<div id="wrapper">
				<div id="content">
					<div class="inner">
					<?if(!ws_self::ok()){?>
<script type="text/javascript"><!--
google_ad_client = "pub-8588802530070446";
/* 468x60, создано 22.10.09 */
google_ad_slot = "7914836422";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<?}?>

		<?=$this->page->content()?>

<?if(!ws_self::ok()){?>
<script type="text/javascript"><!--
google_ad_client = "pub-8588802530070446";
/* 468x60, создано 22.10.09 */
google_ad_slot = "7914836422";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<?}?>
					</div>
				</div>
			</div>

			<?if($this->left) echo '<div id="leftcol">', $this->page->col_left(), '</div>'?>

			<?if($this->right) echo '<div id="rightcol">', $this->page->col_right(), '</div>'?>

			<div class="clear"><!-- --></div>
		</div>
	</div>
	<script type="text/javascript">$('header-<?=(ws_self::ok()?'on':'off')?>').style.display='block';ajax_login_request();</script>
	<div id="footer">
		<?$this->footer();?>
	</div>
</div>

<div id="leftfixed">
	<?$this->sidenav_buttons();?>
</div>
<div id="rightfixed">

</div>

</body>
</html>

<?
	$c = ob_get_contents();
	ob_end_clean();
	return $c;
 }

 protected function sub_menu()
 {
 	if(!($this->page instanceof i_tpl_page_submenu)) return;
 	$menu = $this->page->p_submenu();
 	if(!is_array($menu) || !count($menu)) return;

 	echo "<ul id=\"sub-menu\">";
 	foreach ($menu as $href => $title) {?>
 		<li><a href="<?=$href?>"><?=$title?></a></li>
 	<?}
 	echo "</ul>";
 }

 protected function sidenav_buttons()
 {
 	$buttons = array(
 		"chat",
 		"online"
 	);

 	if(ws_self::is_allowed("metaadm")) $buttons[] = "adm";

 	foreach($buttons as $k=>$b){
?>

<div class="sidenav" style="top:<?=($k*30)?>px;" title="<?=self::$locale["sidenav.$b"]?>" onclick="mr_sidenav('<?=$b?>')" id="sidenav-<?=$b?>"><img src="http://mir.io/style/img/sidenav/<?=$b?>.gif" width="30" height="30" alt="<?=self::$locale["sidenav.$b"]?>"/></div>

<?}
 }

 protected function head()
 {

 }

 private function header()
 {
?>

 <!--
 <?if($this->page instanceof i_tpl_page_comm) $this->page_comm()?>
 <?if($this->page instanceof i_tpl_page_user) $this->page_comm()?>
 -->

<table width="100%" height="70">
<tr>
	<th width="70" rowspan="2">
	<a href="<?=mr::host($this->ico)?>"><img src="/style/img/corner/<?=$this->ico?>.gif" width="35" height="35" alt="<?=(self::$locale["menu.".$this->ico]?self::$locale["menu.".$this->ico]:self::$locale["menu.mir"])?>" border="0"/></a>
	</th>
 	<td class="header-adv"><div><?=$this->inner_banner(2)?></div></td>
 	<td class="header-adv"><div><?=$this->inner_banner()?></div></td>
 	<td id="header-login">

		<div id="header-off">
			<b><a href="javascript:void(0)" onclick="$('header-off').style.display='none';$('header-form').style.display='block'">Вход</a></b>
				&nbsp;
			<a href="<?=mr::host("mir")?>/reg.xml">Регистрация</a>
				&nbsp;
			<a href="javascript:void(0)">Забыли&nbsp;пароль?</a><br/>
		</div>

		<form method="post" id="header-form" action="/x/site-login/sign" onsubmit="$('in_sbm').disabled=true">
			<label for="in_login"><?=self::$locale["lb.login"]?>: <input id="in_login" type="text" name="login" size="10" value="<?=(isset($_COOKIE["login"])?htmlspecialchars($_COOKIE["login"]):"")?>"/></label>
			<label for="in_pwd"><?=self::$locale["lb.pwd"]?>: <input id="in_pwd" type="password" name="pwd" size="10"/></label>

			<br/>

			<input type="hidden" name="hide_me" value="no" />
			<label for="in_hide_me"><input name="hide_me" type="checkbox" value="yes" id="in_hide_me" /> &ndash; <?=self::$locale["lb.hide_me"]?></label>

			<input type="hidden" name="auto" value="no" />
 			<label for="in_auto"><input name="auto" type="checkbox" value="yes" id="in_auto" /> &ndash; <?=self::$locale["lb.auto"]?></label>

			<input type="submit" value="Вход" id="in_sbm"/>
			<input type="hidden" name="url" value="http://<?=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']?>"/>
		</form>

		<div id="header-on">
			<?=self::$locale["lb.hello"]?>, <span id="login-self"><?=ws_self::self()?></span><br/>
  			<b><a href="<?=mr::host("own")?>"><?=self::$locale["lb.own"]?></a></b><br/>
  			<a href="<?=mr::host("own")?>/msg/" id="lb-msg">Личная&nbsp;почта</a>
  			<a href="<?=mr::host("own")?>/reminder/" id="lb-reminder">Оповещения</a>
			<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/ajax-login/out',evalScripts:true,method:'GET'}).send()"><?=self::$locale["lb.logout"]?></a>
		</div>
	</td>
</tr>
</table>
<?
 }

 private function page_comm()
 {
 	/* @var $comm ws_comm */
 	$comm = $this->page->p_comm();
 	if(!$comm) return;
 	if($comm->display_page_line == "no") return;
?>
<div id="page-comm">
	<span id="p-comm-link"><?=self::$locale["comm"]?>: <?=$comm?></span>
	<?if($comm->display_discs == "yes"){?><span><a href="<?=$comm->href("discs.xml")?>"><?=self::$locale["comm.discs"]?></a></span><?}?>
	<?if($comm->display_events == "yes"){?><span><a href="<?=$comm->href("events.xml")?>"><?=self::$locale["comm.events"]?></a></span><?}?>
	<?if($comm->display_cols == "yes"){?><span><a href="<?=$comm->href("cols.xml")?>"><?=self::$locale["comm.cols"]?></a></span><?}?>
	<?if($comm->display_pubs == "yes"){?><span><a href="<?=($comm->type=="meta"?mr::host("libro")."/list.meta-".$comm->name.".xml":$comm->href("pubs.xml"))?>"><?=self::$locale["comm.pubs"]?></a></span><?}?>
</div>
<?
 }

 private function page_user()
 {
 	/* @var $comm ws_comm */
 	$user = $this->page->p_user();
?>
<div id="page-user">
	<?=$user?>
</div>
<?
 }

 private function main_menu()
 {
?>

	<ul id="main-menu">
		<li id="mm-mir"><a href="<?=mr::host("mir")?>"><?=self::$locale["menu.mir"]?></a></li>
		<li id="mm-libro"><a href="<?=mr::host("libro")?>"><?=self::$locale["menu.libro"]?></a></li>
		<li id="mm-real"><a href="<?=mr::host("real")?>"><?=self::$locale["menu.real"]?></a></li>
		<li id="mm-cols"><a href="<?=mr::host("cols")?>"><?=self::$locale["menu.cols"]?></a></li>
		<li id="mm-events"><a href="<?=mr::host("events")?>"><?=self::$locale["menu.events"]?></a></li>
		<li id="mm-comms"><a href="<?=mr::host("mir")?>/list.xml"><?=self::$locale["menu.comms"]?></a></li>
		<li id="mm-disc"><a href="<?=mr::host("disc")?>"><?=self::$locale["menu.disc"]?></a></li>
		<li id="mm-blogs"><a href="<?=mr::host("blogs")?>"><?=self::$locale["menu.blogs"]?></a></li>
	</ul>

	<span id="ajax-process">
		&nbsp;
	</span>

<?
 }

 public function inner_banner($prepare=0)
 {
 	static $bns;
 	static $c = 0;
 	if($prepare)
 	{
 		$bns = ws_comm_adv::several($prepare);
 		$c = 0;
 	}
 	if(count($bns)>$c)
 		return $bns[ $c++ ];
 	$b = ws_comm_adv::several(1);
 	return $b[0];
 }

 private function footer()
 {
?>

<span class="footer-right">
<!--LiveInternet counter--><script type="text/javascript">document.write("<a href='http://www.liveinternet.ru/click;mir-io' target=_blank><img src='http://counter.yadro.ru/hit;mir-io?t26.1;r" + escape(document.referrer) + ((typeof(screen)=="undefined")?"":";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?screen.colorDepth:screen.pixelDepth)) + ";u" + escape(document.URL) +";i" + escape("Жж"+document.title.substring(0,80)) + ";" + Math.random() + "' border=0 width=88 height=15 alt='' title='LiveInternet'><\/a>")</script><!--/LiveInternet-->
</span>

	<span class="footer-blue">
<?=self::$locale["footer.lead"]?> &ndash; <a href="http://alari.name/" target="_blank"><?=self::$locale["footer.messire"]?></a> &copy; 2004-2009
	</span>

	&bull;

	<span class="footer-green">
<?=self::$locale["footer.design"]?> &ndash; <a href="http://iroi.name/" target="_blank"><?=self::$locale["footer.luniel"]?></a> &copy; 2008
	</span>

	&bull;

	<span class="footer-red">
<a href="http://1gb.ru" target="_blank"><?=self::$locale["footer.hosting"]?></a>
	</span>

	&bull;

<?=mr::time(2)?> sec. / <?=mr_sql::queries()?> / <?=(is_readable("src/version.txt")?"v0.".trim(file_get_contents("src/version.txt")):"v0.dev")?>

	<br/>

<a href="<?=mr::host("mir")?>/rules.xml" style="color:black">Правила Сайта</a>

<?
 }
	}