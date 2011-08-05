<?php class RedirectException extends Exception {
	
	public $delay, $message, $title, $url;
	
		public function __construct($where="/", $delay=0, $message="Перенаправление", $title="Успешно", $code=303)
		{
			$this->delay = $delay;
			$this->message = $message;
			$this->title = $title;
			$this->url =  substr($where, 0, 7) == "http://" ? $where : "http://".$_SERVER['HTTP_HOST'].$where;
		}
		
		public function __toString()
		{
			ob_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns:mr="http://www.mirari.ru">
 <head>
  <meta http-equiv="Refresh" content="<?=$this->delay?>; url=<?=$this->url?>" />
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <title>Перенаправление: <?=$this->title?></title>
  <link rel="stylesheet" type="text/css" href="/style/css/redirect.css" />  
  <link rel="SHORTCUT ICON" href="/favicon.ico"/>
  </head>
 <body>

 <table><tr><td>
 
  <div id="main">
   <div id="title">Перенаправление: <?=$this->title?></div>
   <div id="msg"><?=$this->message?></div>
   <div id="sub"><a href="<?=$this->url?>">Нажмите, если не хотите ждать...</a></div>
  </div>
  
 </td></tr></table>
 
 </body>
</html>

<?
			return ob_get_clean();
		}
	}
?>