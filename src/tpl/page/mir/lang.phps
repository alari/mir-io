<?php class tpl_page_mir_lang extends tpl_page implements i_tpl_page_rightcol {
	
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";

 	if( !ws_self::is_allowed("locale") )
 		throw new ErrorPageException("Вы не имеете права на работу с локализацией сайта", 403);
 	
	ob_start();
	
?>

<h1>Работа с локализацией</h1>
<h2>Перевод основ сайта на другие языки</h2>
<div id="loc-main" align="center">Выберите режим работы из правой колонки.</div>

<?
	
	$this->content = ob_get_clean();
 	
	$this->title = "Работа с локализацией";
	
 }
 
 public function col_right()
 {
?>

<form method="post" action="/x/ajax-locale/main" id="loc-main-form">
 <p>
 Локализация: <select name="lang">
 <?
 $arr = ws_lang::several();
 foreach ($arr as $l) if($l->id() != mr::lang(true)) {?><option value="<?=$l->id()?>"><?=$l->name?></option><?}?>
?>
 </select>
 </p>
 <p>
 Редактировать: <select name="type">
 	<option value="ini">Все ini-файлы</option>
 	<option value="file">Текст одного ini</option>
 	<option value="freepages">Свободные странички</option>
 </select>
 </p>
 <center><input type="button" value="Локаль" onclick="javascript:mr_Ajax_Form($('loc-main-form'),{update:$('loc-main')})"/></center>
</form>

<?
 }
	
}?>