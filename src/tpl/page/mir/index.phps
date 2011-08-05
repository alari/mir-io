<?php class tpl_page_mir_index extends tpl_page implements i_locale {

	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "asis";
 	$this->layout_site = false;

 	$this->title = "Мир Ио";

 	ob_start();
?>
 <script type="text/javascript" src="/style/js/core.js"></script>
 <script type="text/javascript" src="/style/js/more.js"></script>
 <link rel="SHORTCUT ICON" href="/style/ico/mir.ico"/>

  <style>

body, html
{
	padding: 0;
	margin: 0;
	width: 100%;
	height: 100%;
	background: white;
}

#container
{
	width: 100%;
	height: 100%;
}
a img, a:link img
{
	border: 0;
}
a
{
	color: black;
}
a:hover
{
	text-decoration: none;
}

#footer
{
	text-align: center;
	color: #cecece;
	font-size: 12px;
}
#footer a
{
	color: #c0c0c0;
	text-decoration: none;
}
#footer a:hover
{
	text-decoration: underline;
}
#footer span
{
	float: right;
}
.no-display
{
	display: none;
}

#in_login, #in_pwd, #in_hide_me, #in_auto
{
	border: 1px solid #c0c0c0;
}
#login-submit
{
	background-color: black;
	color: white;
	border: 0;
	font-weight: bold;
	width: 60%;
	padding: 3px;
}
#con-own
{
	font-family: Verdana;
	font-size: 14px;
}
#con-own a
{
	font-size: 90%;
}

td table, td p
{
	width: 200px;
}

.tip-item
{
	background: wheat;
	border: 1px solid red;
	padding: 4px;
}
.tip-title
{
	font-weight: bold;
}

  </style>

<?
 	$this->head = ob_get_clean();

 	ob_start();

?>

 <div class="no-display" id="con-what-src">
 	<p><a href="<?=mr::host("whole")?>">Мир Ио</a> &ndash; проект, позволяющий раскрыть свой творческий потенциал во всех направлениях: Литературе, Музыке, Фотографии, Живописи или Графике, Публицистике, и других.</p>
 </div>

 <?if(!ws_self::ok()){?>
 <div class="no-display" id="con-own-src">

<form method="post" action="/x/site-login/sign" onsubmit="$('login-submit').disabled='yes'">
	<table>
<tr>
	<td><label for="in_login">Логин</label>:</td>
	<td><input type="text" size="12" name="login" id="in_login" /></td>
</tr>
<tr>
	<td><label for="in_pwd">Пароль</label>:</td>
	<td><input type="password" size="12" name="password" id="in_pwd" /></td>
</tr>
<tr>
	<td colspan="2" align="center">
		<input type="hidden" name="hide_me" value="no" /><input name="hide_me" type="checkbox" value="yes" id="in_hide_me" /> &ndash; <label for="in_hide_me">Спрятаться</label>
			<br/>
		<input type="hidden" name="auto" value="no" /><input name="auto" type="checkbox" value="yes" id="in_auto" /> &ndash; <label for="in_auto">Запомнить</label>
	</td>
</tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" value="Войти" id="login-submit"/>
	</td>
</tr>
<tr>
	<td colspan="2" align="right">
		<i><a href="/reg.xml">Регистрация</a></i> &bull;
			<br/>
		<i><a href="/reminder.xml">Восстановление пароля</a></i> &bull;
	</td>
</tr>
	</table>
</form>

 </div>
 <?}?>

 <script>

 function con_ch(con_id, con)
 {
 	var tmpm = new Fx.Tween($(con_id), {property:'opacity'});
 	tmpm.addEvent('complete',
 	 function(e){
 	 	$(con_id).set('html', con);
 	 	$(con_id).tween('opacity', [0, 1]);
 	});
 	tmpm.start(1, 0);
 }

 var con_what = $('con-what-src').innerHTML;
 <?if(!ws_self::ok()){?>
 var con_own = $('con-own-src').innerHTML;
 $('in_login').value = Cookie.read('login')?Cookie.read('login'):"";
 <?}?>

 var Robj = {
 	onFailure: function(inst){
 		alert('failed');
 	},
 	onRequest: function(inst){
 		$('test-process').set('html', 'in process...');
 	},
 	onComplete: function(txt,xml){
 		$('test-process').set('html', '');
 	}
 };

 </script>

 <table id="container" cellpadding="0" cellspacing="0">
 <tr>
 	<td height="50%" width="33%" align="center" id="con-what">
 		<a href="javascript:void(0)" onclick="con_ch('con-what', con_what)"><img src="/style/img/front/whole.gif" width="150" height="180" alt="Творческое Что"/></a>
 	</td>
 	<td align="center">

 		<a href="<?=mr::host("whole")?>"><img src="/style/img/front/mir.gif" width="150" height="180" alt="Мир Ио"/></a>

 	</td>
 	<td align="center" width="33%" id="con-own">
 		<a <?if(ws_self::ok()){?>href="<?=mr::host("own")?>"<?}else{?>href="javascript:void(0)" onclick="con_ch('con-own', con_own)"<?}?>>
 		<img src="/style/img/front/own.gif" width="150" height="180" alt="Быть Внутри"/></a>
 	</td>
 </tr>

 <tr>
 	<td colspan="3" align="center">

 	<a href="<?=mr::host("libro")?>"><img src="/style/img/front/lito.gif" width="150" height="180" alt="Литературное Объединение"/></a>
 	<a href="<?=mr::host("blogs")?>"><img src="/style/img/front/blogs.gif" width="150" height="180" alt="Дневники и Блоги"/></a>
 	<a href="<?=mr::host("real")?>"><img src="/style/img/front/real.gif" width="150" height="180" alt="Реальный Мир"/></a>
 	<a href="http://artenclave.ru/"><img src="/style/img/front/art.gif" width="150" height="180" alt="Объединение Художников" onload="$(this).getParent().fade(0.4)"/></a>
 	<a id="photo-under" href="<?=mr::host("photo")?>"><img src="/style/img/front/photo.gif" width="150" height="180" alt="Фотография" onload="$(this).getParent().fade(0.4)"/></a>
 	<a href="<?=mr::host("disc")?>"><img src="/style/img/front/disc.gif" width="150" height="180" alt="Дискуссии и Форумы"/></a>
 	<a href="<?=mr::host("events")?>"><img src="/style/img/front/events.gif" width="150" height="180" alt="События"/></a>

 	</td>
 </tr>
 <tr>
 	<td height="20" colspan="3" align="center" id="footer">


 		<span>
 		<!--LiveInternet counter--><script type="text/javascript">document.write("<a href='http://www.liveinternet.ru/click;mir-io' target=_blank><img src='http://counter.yadro.ru/hit;mir-io?t26.1;r" + escape(document.referrer) + ((typeof(screen)=="undefined")?"":";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?screen.colorDepth:screen.pixelDepth)) + ";u" + escape(document.URL) +";i" + escape("Жж"+document.title.substring(0,80)) + ";" + Math.random() + "' border=0 width=88 height=15 alt='' title='LiveInternet: показано число посетителей за сегодня'><\/a>")</script><!--/LiveInternet-->
 		</span>

 		Идейное руководство и разработка сайта &ndash; <a href="http://alari.name">Дмитрий Куринский</a> &copy; 2004-2008
 			&bull;
 		Дизайн &ndash; <a href="http://iroi.name">Ирина Кузнецова</a> &copy; 2008

 	</td>
 </tr>
 </table>

 <script type="text/javascript">
 $$('a').each(function(prnt){
 	var itm = prnt.getFirst("img");
 	if(itm)
 	{
 		if(itm.get('alt')) prnt.set('title', itm.get('alt'));
 		prnt.store('tip:text', prnt.get('href')=='javascript:void(0)'||prnt.get('href')=='/'?"":prnt.get('href'));

 		if(!prnt.get('onclick') && !itm.get('onclick')) itm.addEvent("click", function(e){
 			$(this).set('tween', {duration:200}).fade('out');
 		});
 	}
 });
 </script>

<?

 	$this->content = ob_get_clean();
 }

	}?>
