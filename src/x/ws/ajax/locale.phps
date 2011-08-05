<?php class x_ws_ajax_locale extends x implements i_locale {
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
 static public function action($x)
 {
 	if( !ws_self::is_allowed("locale") )
 		die( self::$locale["no_rights"] );
 	return self::call($x, __CLASS__);
 }
 
 static public function main()
 {
 $lang = $_POST["lang"];
 $type = $_POST["type"];
 
	 switch($type)
	 {
	 	case "ini":
	 		
	self::main_ini($lang);
	 		
	 	break;
	 	
	 	case "file":
	 		
	 self::main_file($lang);
	 		
	 	break;
	 	
	 	case "freepages":
	 		
	 self::main_freepages($lang);
	 		
	 	break;
	 }
 }
 
 static public function main_ini($lang)
 {
 	echo sprintf(self::$locale["ini.message"], ws_lang::factory($lang)->link())
?>
<form method="post" action="/x/ajax-locale/ini/save">
 <textarea style="width: 90%; height: 500px" name="ini"><?=ws_lang_locale::out_ini_diff($lang, mr::lang(true))?></textarea>
 <br/>
 <input type="hidden" name="available" value="no"/><input id="loc-set-available" type="checkbox" value="yes" name="available"<?=(ws_lang::factory($lang)->available=="yes"?" checked=\"yes\"":"")?>/> &ndash; <label for="loc-set-available"><?=self::$locale["ini.set_available"]?></label>
 <br/><br/><input type="hidden" name="lang" value="<?=$lang?>"/>
 <input type="button" value="<?=self::$locale["save"]?>" onclick="javascript:$(this).disabled='yes';mr_Ajax_Form($(this).getParent(),{update:$('loc-main')})"/>
</form>
<?
 }
 
 static public function main_file($lang)
 {
?>
<form method="post" action="/x/ajax-locale/file/get" id="lc-fg-form">
<?=self::$locale["file.lang"]?>: <b><?=ws_lang::factory($lang)?></b>. <?=self::$locale["file.name"]?>: <select name="file">
<?
	$addr = ws_lang_locale::get_ini_addr("locale");
	foreach ($addr as $a){?><option><?=$a?></option><?}?>
</select> <input type="hidden" name="lang" value="<?=$lang?>"/> <input type="button" value="<?=self::$locale["file.edit"]?>" onclick="javascript:$(this).disabled='yes';mr_Ajax_Form($('lc-fg-form'),{update:$('loc-text')})"/>
</form>
<div id="loc-text"><?=self::$locale["file.choose"]?>.</div>
<?
 }
 
 static public function main_freepages($lang)
 {
 	echo "Not implemented yet!";
 }
 	
static public function ini_save()
{
 $ini = $_POST["ini"];
 $lang = $_POST["lang"];
 
 ws_lang_locale::handle_ini_diff($ini);
 
 $l = ws_lang::factory($lang);
 $l->available = $_POST["available"];
 $l->save();
 
 echo "<center><b>".self::$locale["ok"]."</b></center>";
}
 
static public function file_get()
{
	$lang = $_POST["lang"];
 	$file = $_POST["file"];
?>
<form method="post" action="/x/ajax-locale/file/save" id="lc-fs-form">
 <textarea style="width: 90%; height: 500px" name="ini"><?=ws_lang_locale::get_ini_text($file, $lang)?></textarea>
 <br/><br/><input type="hidden" name="lang" value="<?=$lang?>"/><input type="hidden" name="file" value="<?=$file?>"/>
 <input type="button" onclick="javascript:$(this).disabled='yes';mr_Ajax_Form($('lc-fs-form'),{update:$('loc-text')})" value="<?=self::$locale["save"]?>"/>
</form>
<?
}

static public function file_save()
{ 			
 	$lang = $_POST["lang"];
 	$file = $_POST["file"];
 	$ini = $_POST["ini"];
 	ws_lang_locale::set_ini_text($file, $lang, $ini);
 	
 	echo "<center><b>".self::$locale["ok"]."</b></center>";
 }
 
	}
?>