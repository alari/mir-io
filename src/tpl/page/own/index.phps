<?php class tpl_page_own_index extends tpl_page implements i_locale, i_tpl_page_rightcol, i_tpl_page_leftcol {

	protected $metas;

	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "fullcol";

 	$this->title = "Ваш кабинет";

 	if(!ws_self::ok()) throw new ErrorPageException("Вы не авторизованы", 402);

 	ob_start();

 	echo "<h1>", "Ваш кабинет", "</h1>";
 	echo "<h2>", "Здесь ваше всё", "</h2>";

 	$city = ws_geo_city::byName( ws_self::self()->city );
 	if($city->id()>0) echo "Ваш город: ", $city;
 	if($city->yandex_id){

	?>
	<br/>
<a target="_blank" href="http://www.yandex.ru/redir?dtype=stred&pid=7&cid=1228&url=http://weather.yandex.ru/index.xml?city=<?=$city->yandex_id?>"><img src="http://info.weather.yandex.net/informer/175x114/<?=$city->yandex_id?>.png" border="0" alt="Яндекс.Погода"/><img width="1" height="1" src="http://www.yandex.ru/redir?dtype=stred&pid=7&cid=1227&url=http://img.yandex.ru/i/pix.gif" alt="" border="0"/></a>

	<? 	}

	$evs = ws_comm_event_anonce::several("hidden='no'", 10);
	?>
<h2>Последние события:</h2>
<ul>

<?foreach ($evs as $ev) {?>
	<li><b><?=$ev?></b> &ndash; <small><?=$ev->comm()?>, <?=$ev->auth()?></small></li>
<?}?>

</ul>
	<?

	$user_comms = ws_comm::memberof(ws_self::id(), 1);

	$rec_comms = Array();
	foreach($user_comms as $c=>$cc) {
		$co = ws_comm::factory($c);
		if($co->can_make_recense()) $rec_comms[] = $c;
	}

	if(count($rec_comms)){
		$pubs = ws_comm_pub_anchor::recByUser(ws_self::id(), $rec_comms, true, 0, 0, $calcResult=false, true);
		if(count($pubs)){
			$this->css[] = "pub/anonce.css";
			echo "<h2>Ожидают рецензии:</h2>";
			tpl_fr_pubs::outlist($pubs, true, 1);
		}
	}

	$users_newreg = ws_user::several("registration_time>UNIX_TIMESTAMP()-86400 AND NOT banned_till");
	if(count($users_newreg)) {
		echo "<h2>Приветствуем новичков</h2>";
		foreach($users_newreg as $n=>$u) echo ($n?", ":"").$u;
	}

 	$this->content = ob_get_clean();
 }

 public function col_right()
 {
 	ob_start();

?>

<ul>
<li><a href="<?=mr::host("own")?>/msg/">Личная почта</a></li>
<li><a href="<?=mr::host("own")?>/draft/">Черновики</a></li>
<li><a href="<?=mr::host("own")?>/pub/">Произведения</a></li>
<li><a href="<?=mr::host("own")?>/profile.xml">Данные профиля</a></li>
<li><a href="<?=mr::host("own")?>/blog.xml">Написать в дневник</a></li>
</ul>

<?if(ws_self::is_allowed("circle")){?>
<p>Круг Чтения:<br/>
<ul>
<li><a href="<?=mr::host("own")?>/circle/blogs.xml">Дневники</a></li>
<li><a href="<?=mr::host("own")?>/circle/pubs.xml">Произведения</a></li>
<li><a href="<?=mr::host("own")?>/circle/advices.xml">Рекомендации</a></li>
<li><a href="<?=mr::host("own")?>/circle/targets.xml">Настройки</a></li>
</ul>
</p>

<?}?>

<?

 	return ob_get_clean();
 }

 public function col_left()
 {

 	ob_start();

 	$mm = ws_comm_member::byUser(ws_self::id(), 1);

 	if(count($mm))
 	{
 		$mem = new mr_list("ws_comm_member", array());
 		$pret = new mr_list("ws_comm_member", array());
 		$auth = new mr_list("ws_comm_member", array());
 		foreach($mm as $m) switch($m->confirmed)
 		{
 			case "yes": $mem[] = $m; continue;
 			case "no": $pret[] = $m; continue;
 			case "auth": $auth[] = $m; continue;
 		}

 		if(count($mem))
 		{
?>

	<p>Вы участвуете в сообществах:</p>
	<?foreach($mem as $m) echo "&nbsp;&nbsp;&nbsp;", $m->comm(), "<i>(".$m->status().")</i><br/>";

 		}
 		if(count($pret))
 		{
?>

	<p>Вы претендент в сообщества:</p>
	<?foreach($pret as $m) echo "&nbsp;&nbsp;&nbsp;", $m->comm(), "<br/>";

 		}
 		if(count($auth))
 		{
?>

	<p>Приглашения в сообщества:</p>
	<?foreach($auth as $m) echo "&nbsp;&nbsp;&nbsp;", $m->comm(), '<i id="m-'.$m->id().'">(<a href="javascript:void(0)" onclick="mr_Ajax({url:\'/x/comm-usr/invite/apply\', data:{id:'.$m->id().'},update:$(\'m-'.$m->id().'\')}).send()">Принять приглашение</a>)</i><br/>';


 		}
 	} else echo "<p>Нет сообществ</p>";

 	return ob_get_clean();
 }

}?>