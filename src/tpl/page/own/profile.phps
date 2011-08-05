<?php class tpl_page_own_profile extends tpl_page implements i_locale, i_tpl_page_rightcol  {
	
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
 	
 	//$this->layout = "rightcol";
 	
 	$this->title = "Ваш кабинет, данные профиля";
 	
 	if(!ws_self::ok()) throw new ErrorPageException("Вы не авторизованы", 402);
 	
 	ob_start();
 	
 	echo "<h1>", "Данные профиля", "</h1>";
 	echo "<h2>", "Здесь можно изменить Вашу личную информацию", "</h2>";
 
?>

<form method="post" id="prof-form" action="/x/own-profile/main" enctype="multipart/form-data">
<fieldset>
 <legend>Подпись на сайте</legend>
 <center><table cellpadding="5"> 
  <tr><td>Логин:</td><td><strong><?=ws_self::self()->login?></strong></td><td><input type="radio" name="set_name_by" value="login"<?=(ws_self::self()->set_name_by=="login"?' checked="yes"':"")?>/></td></tr>
  <tr><td>Ник:</td><td><input type="text" name="nick" value="<?=htmlspecialchars(ws_self::self()->nick)?>" onblur="if(!this.value){$('prof-form').set_name_by.value='login';}" maxlength="128" size="32"/></td><td><input type="radio" name="set_name_by" value="nick"<?=(ws_self::self()->set_name_by=="nick"?' checked="yes"':"")?>/></td></tr>
  <tr><td>ФИО:</td><td><input type="text" name="fio" value="<?=htmlspecialchars(ws_self::self()->fio)?>" onblur="if(!this.value){$('prof-form').set_name_by.value='login';}" maxlength="128" size="32"/></td><td><input type="radio" name="set_name_by" value="fio"<?=(ws_self::self()->set_name_by=="fio"?' checked="yes"':"")?>/></td></tr>
 </table></center>
</fieldset>

<fieldset>
 <legend>Личная информация</legend>
 <table width="100%" cellpadding="5">
  <colgroup>
   <col width="30%" align="right" valign="top"/>
   <col/>
  </colgroup>
  <tr><td>Номер ICQ:</td><td><input type="text" name="icq" value="<?=ws_self::self()->icq?>" onblur="if(this.value) this.value=this.value.replace(/[^0-9]/g, '');" maxlength="28" size="52"/></td></tr>
  <tr><td>Web-сайт:</td><td><input type="text" name="url" value="<?=ws_self::self()->url?>" maxlength="128" size="52"/></td></tr>
  <tr><td>Город:</td><td><?=ws_self::self()->city?></td></tr>
  <tr><td>Дата рождения:</td><td><?=ws_self::self()->burthdate?></td></tr>
  <tr><td>Свободный комментарий:
  <td align="center" rowspan="2">
	<textarea name="userinfo" id="user_info" style="width:100%;height:250px;"><?=mr_text_trans::node2text(ws_self::self()->userinfo)?></textarea>
	<script type="text/javascript">text_markup('user_info', 1, 0);</script>
  </td></tr>
  <tr><td valign="bottom">
  	<small>Эта информация будет размещена в самом верху на странице Вашего профиля и поможет другим участникам проекта лучше понять, кто Вы есть. Содержание может быть свободным, хоть краткий рассказ о себе, хоть пара любимых цитат или мыслей. Главное &ndash; не входить в конфликт с Правилами Сайта</small></td>
  </td></tr>
  <tr><td>Подпись:</td><td><input type="text" name="signature" value="<?=ws_self::self()->signature?>" maxlength="256" size="52"/></td></tr>
 </table>
</fieldset>

<fieldset>
 <legend>Электронная почта и пароль</legend>
 <table>
  
 </table>
 not implemented yet
</fieldset>

<fieldset>
 <legend>Настройки дневника</legend>
 <center><table cellpadding="5">
  <tr><td>Название дневника:</td><td><input type="text" name="blog_title" value="<?=htmlspecialchars(ws_self::self()->blog_title)?>" maxlength="128" size="32"/></td></tr>
  <tr><td>Записей на страницу:</td><td><input type="text" name="blog_perpage" value="<?=(ws_self::self()->blog_perpage?ws_self::self()->blog_perpage:"")?>" maxlength="2" size="4"/></td></tr>
 </table></center>
</fieldset>

 <center><input type="button" value="Сохранить изменения" onclick="javascript:mr_Ajax_Form($('prof-form'),{update:$('prof-result')});"/></center>
 
 <div id="prof-result"></div>
</form>

<?
 	
 	
 	$this->content = ob_get_clean();
 }
 
 public function col_right()
 {
 	return "cab";
 }
	
}?>