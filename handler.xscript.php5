<?php
try {

 if(strpos($_SERVER["REQUEST_URI"], $_SERVER["PHP_SELF"])===0)
 	throw new SecurityException("Попытка доступа к скрипту обработчика запросов!", 403);

 $URI = $_SERVER["REQUEST_URI"];
  if(strpos($URI, "?")!=0) $URI = substr($URI, 0, strpos($URI, "?"));
  if($URI{0}=="/") $URI = substr($URI, 1);

 header("Content-type: text/html; charset=utf-8");

 ob_start("ob_gzhandler", 9);

 if((substr($URI, 0, 8) == "xscript/" || substr($URI, -3) == ".xs") && is_readable($URI.".php"))
 {
  include_once $URI.".php";
  // Обработка единичного скрипта
  exit;
 }

// Ни одного соответствия -- значит, файл не найден
 try {
require_once './O/src/EntryPoint.phps';
O_EntryPoint::processRequest();
 } catch(Exception $e) {

 throw new ErrorPageException("Не удалось найти обработчик для запрошенной странички! (Ofw)", 404);
 }
 throw new ErrorPageException("Не удалось найти обработчик для запрошенной странички!", 404);


}
catch(RedirectException $e)
{
	if(!$e->delay)
	{
		header("Location: ".$e->url);
	} else {
		header("Refresh: ".$e->delay."; URL=".$e->url);
		echo $e;
	}
}
catch(ErrorPageException $e)
{
	echo $e;
}
catch(Exception $e)
{
	Header("HTTP/1.1 500 Internal Server Error");
	Header("Content-type: text/html; charset=utf-8");
?>
	<i>Bad situation...</i> Programm Bag
		<br />
	<i>Error <?=$e->getCode()?>:</i> <?=$e->getMessage()?>
		<br /><?/*
	<i>File:</i> <?=$e->getFile()?>:<?=$e->getLine()?>
		<br />
	<i>Trace:</i> <?=nl2br(str_replace(" ", "&nbsp;", $e->getTraceAsString()))?>
		<br />
	<?=nl2br(str_replace(" ", "&nbsp;", mr::printLog()))?>*/?>
	<i>Пожалуйста, подождите несколько минут. Вероятно, ошибка будет исправлена или исчерпает себя.</i>
<?}?>