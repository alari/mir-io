<?php
try {

 		if($_SERVER["HTTP_HOST"]=="local.mir.io") error_reporting(E_ALL);

 if(strpos($_SERVER["REQUEST_URI"], $_SERVER["PHP_SELF"])===0)
 	throw new SecurityException("Попытка доступа к скрипту обработчика запросов!", 403);

 $URI = $_SERVER["REQUEST_URI"];
  if(strpos($URI, "?")!=0) $URI = substr($URI, 0, strpos($URI, "?"));

 mr::uses("mr_sql");

 if(!mr_sql::connected())
 	throw new CoreErrorException("Сайт временно недоступен по техническим причинам (невозможно соединение с СУБД). Пожалуйста, обновите страничку через несколько минут, вероятно, проблема исчерпает сама себя. Надеемся на Ваше понимание.", 500);

 mr::nocache();

 header("Content-type: text/html; charset=utf-8");

 ob_start("ob_gzhandler", 9);

 if($URI[strlen($URI)-1] == "/") $URI .= "index.xml";

 if(substr($URI, -4) == ".xml" || substr($URI, -3) == ".ml" || $URI[1] == "~" || preg_match("/^\/[a-z]+$/", $URI))
 {
  echo tpl::handle($URI);
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
		<br /><?if(false&&ws_self::id()==2){?>
	<i>Error <?=$e->getCode()?>:</i> <?=$e->getMessage()?>
		<br />
	<i>File:</i> <?=$e->getFile()?>:<?=$e->getLine()?>
		<br />
	<i>Trace:</i> <?=nl2br(str_replace(" ", "&nbsp;", $e->getTraceAsString()))?>
		<br /><?}?>
	<?=nl2br(str_replace(" ", "&nbsp;", mr::printLog()))?>
	<i>Пожалуйста, подождите несколько минут. Вероятно, ошибка будет исправлена или исчерпает себя.</i>
<?}?>
