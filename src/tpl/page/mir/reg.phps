<?php class tpl_page_mir_reg extends tpl_page {
		
 public function __construct($filename="", $params="")
 {
 	$this->head .= '<script type="text/javascript" src="/style/js/stringInput.js"></script>';
 	
 	parent::__construct($filename, $params);
 	
 	if(ws_self::ok())
 		throw new RedirectException(mr::host("own"), 4, "Вы уже зарегистрированы и сейчас будете перенаправлены в личный кабинет.");
 	
 	$this->title = "Регистрация на сайте";
 	
	$from = @$params["from"];
	if($from)
		$from = ws_user::getByLogin($from);
 	
 	ob_start();
 	 
?>

<h1>Регистрация на сайте</h1>

<form method="post" action="/x/site-reg/submit" id="reg-form" enctype="multipart/form-data">
<fieldset>
 <legend>Подпись на сайте</legend>
 <center><table cellpadding="5"> 
  <tr><td><b>Логин</b>:</td><td><input type="text" name="login" maxlength="16" onkeyup="this.value=this.value.replace(/[^-_a-z0-9]/, '')" size="32"/></td><td><input type="radio" name="set_name_by" value="login" checked="yes"/></td></tr>
  <tr><td>Ник:</td><td><input type="text" name="nick" onblur="if(!this.value){$('reg-form').set_name_by.value='login';}" maxlength="128" size="32"/></td><td><input type="radio" name="set_name_by" value="nick"/></td></tr>
  <tr><td>ФИО:</td><td><input type="text" name="fio" onblur="if(!this.value){$('reg-form').set_name_by.value='login';}" maxlength="128" size="32"/></td><td><input type="radio" name="set_name_by" value="fio"/></td></tr>
 </table></center>
</fieldset>

<fieldset>
 <legend>Электронная почта и пароль</legend>
 <center><table cellpadding="5">
  <tr><td><b>E-mail</b>:</td><td><input type="text" name="email" size="32" maxlength="48"/></td></tr>
  <tr><td><b>Пароль</b>:</td><td><input type="password" name="pwd" size="32" id="reg-pwd"/></td></tr>
  <tr><td><b>Повторите пароль</b>:</td><td><input type="password" name="pwd_2" size="32" onblur="if(this.value != $('reg-pwd').value) {alert('Пароли не совпадают. Пожалуйста, повторите ввод.');this.value='';$('reg-pwd').value='';$('reg-pwd').focus();}"/></td></tr>
 </table></center>
</fieldset>

<fieldset>
 <legend>Личная информация</legend>
 <table width="100%" cellpadding="5">
  <colgroup>
   <col width="30%" align="right" valign="top"/>
   <col/>
  </colgroup>
  <tr><td>Номер ICQ:</td><td><input type="text" name="icq" onblur="if(this.value) this.value=this.value.replace(/[^0-9]/g, '');" maxlength="28" size="52"/></td></tr>
  <tr><td>Web-сайт:</td><td><input type="text" name="url" maxlength="128" onblur="if(!this.value.match(/^http:\/\/[a-z0-9\.]{2,}\.[a-z]{2,4}/)) this.value=''" size="52"/></td></tr>
  <tr><td>Город:</td><td>  		
  	<input type="text" name="city" maxlength="128" size="20" id="reg-city"/> &nbsp; <small>Введите первые символы названия города, чтобы получить список описанных на сайте городов.</small><br/>
  	<div style="background:white;padding:3px;width:350px;" id="reg-city-check"></div>
  	<script type="text/javascript">
  		new stringInput("reg-city", "reg-city-check", "/x/ajax-geo/findcity", "Подождите, идёт поиск...");
  	</script>
  	
  </td></tr>
  <!--<tr><td>Дата рождения:</td><td>
  	<input type="text" name="burth_day" maxlength="2" size="4"/>.<input type="text" name="burth_month" maxlength="2" size="4"/>.<input type="text" name="burth_year" maxlength="4" size="8"/> ДД.ММ.ГГГГ
  </td></tr>-->
  <tr><td>Свободный комментарий:
  <td align="center" rowspan="2">
	<textarea name="userinfo" id="user_info" style="width:100%;height:250px;"></textarea>
	<script type="text/javascript">text_markup('user_info', 0, 0);</script>
  </td></tr>
  <tr><td valign="bottom">
  	<small>Эта информация будет размещена в самом верху на странице Вашего профиля и поможет другим участникам проекта лучше понять, кто Вы есть. Содержание может быть свободным, хоть краткий рассказ о себе, хоть пара любимых цитат или мыслей. Главное &ndash; не входить в конфликт с Правилами Сайта</small></td>
  </td></tr>
  <tr><td>Подпись:</td><td><input type="text" name="signature" maxlength="256" size="52"/></td></tr>
 </table>
</fieldset>

<fieldset>
<legend>Важно для регистрации</legend>
<small>
	<p class="pr">Обязательно укажите действительно существующий адрес электронной почты. После того, как Вы нажмёте "Зарегистрироваться", по этому адресу будет отправлено сообщение, без которого активация созданного профиля будет невозможна. Проверьте папку со спамом, письмо может по ошибке попасть туда.</p>
	<p class="pr">На сайте одному человеку разрешено иметь только один профиль. При обнаружении повторных регистраций ("клонов") более новые профили будут блокироваться или удаляться.</p>
	<p class="pr">Регистрируясь, Вы подтверждаете, что согласны с <a href="<?=mr::host("mir")?>/rules.xml">Правилами сайта</a> и обязуетесь их исполнять.</p>
</small>
</fieldset>

<?if($from){?><input type="hidden" name="from" value="<?=$from->id()?>"/><?}?>

 <center>
 	<input type="button" value="Зарегистрироваться" onclick="$('reg-result').set('html', '<i>Пожалуйста, подождите...</i>');this.disabled='yes';mr_Ajax_Form($('reg-form'),{update:$('reg-result')});" id="reg-submit"/>
 	<br/><br/><div id="reg-result"></div>
 </center>
</form>

<?
 	$this->content = ob_get_clean();
 }
	
}?>