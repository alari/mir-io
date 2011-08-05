<?php class x_ws_site_attach extends x implements i_xmod {

 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }

 static public function form()
 {
 	if( !ws_self::is_allowed("attach") )
 		die("Вы не имеете прав на закачку вложений. (MAX 20 Mb хранимых вложений для простого аккаунта)");

 	$update = $_GET["update"];
 	if(!$update) $update = "update";

?>

<html>
<head>
 <title>Закачка нового вложения</title>
 <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
</head>
<body><center>

<form method="post" action="/x/site-attach/upload" enctype="multipart/form-data" onsubmit="document.getElementByID('attach_submit').disabled='yes'">
<fieldset>
<legend>Закачка нового вложения</legend>


Файл: <input type="file" name="attach"/>
<br/><br/>
<input type="submit" value="Закачать" id="attach_submit"/>
<input type="hidden" name="update" value="<?=$update?>"/>

</fieldset>
</form>

<small>
Обратите внимание, что сайт не является файл-сервером. Бессрочное хранение закаченных вложений не гарантируется.
</small>

</center></body></html>

<?
 }

 static public function upload()
 {
 	if( !ws_self::is_allowed("attach") )
 		die("Вы не имеете прав на закачку вложений. (MAX 20 Mb хранимых вложений для простого аккаунта)");

 	$update = $_POST["update"];
 	if(!$update) $update = "attach";

	$filesize = filesize($_FILES["attach"]["tmp_name"]);
  if($filesize > 1024*1024*10) die("Слишком большой файл для вложения!");

  $type = $_FILES["attach"]["type"];

  $ext = explode(".", $_FILES["attach"]["name"]);
  $ext = strtolower($ext[count($ext)-1]);

  $allowed = array(
	"avi",
	"mov",
	"flv",

	"xml",
	"rtf",
	"html",
	"htm",
	"plain",
	"css",

	"doc",
	"docx",

	"aac",
	"wav",
	"mid",
	"mp3",
	"wma",

	"zip",
	"rar",
	"xls",

	"pdf",

	"gif",
	"jpeg",
	"jpg",
	"png"
);

  if(!in_array($ext, $allowed))
  {
   ?><script language="JavaScript" type="text/javascript">alert('Неподдерживаемый формат вложения!\n(Расширение <?=$ext?>)'); window.close();</script><?
  } else {

  	$_FILES["attach"]["name"] = str_replace(array("?", ",", "&", ";", ":", "#", "="), array("_", "-", "_", "_", "-", "_", "-"), $_FILES["attach"]["name"]);

   mr_sql::qw("INSERT INTO mr_attach_stat(user_id, filename, size, type, time) VALUES(?, ?, ?, ?, UNIX_TIMESTAMP())", ws_self::id(), $_FILES["attach"]["name"], $filesize, $type);
   $attachid = mr_sql::insert_id();

   if(!$attachid) die("Ошибка при загрузке файла");

   $dir = "u/".strtolower(substr(ws_self::login(), 0, 1));
   $dir .= "/".strtolower(ws_self::login());
   $dir .= "/a";
   $id = substr($attachid, -2);
   if(strlen($id)<2) $id = "0$id";
   $dir .= "/$id";

   $ftp = new ws_fileserver;

   if(!$ftp->ok()){
   	mr_sql::qw("DELETE FROM mr_attach_stat WHERE id=?", $attachid);
   	die("Закачка вложения невозможна: файл-сервер недоступен. Попробуйте позже.");
   }

   $ftp->mkdir($dir, 1);
   $src = $ftp->put_rename($_FILES["attach"]["tmp_name"], "a", $attachid, $dir."/".$_FILES["attach"]["name"]);

   if(!count($src)){
   	mr_sql::qw("DELETE FROM mr_attach_stat WHERE id=?", $attachid);
   	die("Закачка вложения невозможна: файл-сервер недоступен. Попробуйте позже.");
   }

   mr_sql::qw("UPDATE mr_attach_stat SET full_src=?, prev_src=? WHERE id=?", $src[0], $src[1], $attachid);

   $title = mr_text_string::remove_excess(@$_POST["title"]);
   if(!$title) $title = $_FILES["attach"]["name"];

?>
 <script language="JavaScript" type="text/javascript">
  opener.document.all.<?=$update?>.value += "\n[attachment id='<?=$attachid?>']<?=$title?>[/attachment]\n";
  window.close();
 </script>
<?
  }

 }

	}
?>