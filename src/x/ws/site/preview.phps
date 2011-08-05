<?php class x_ws_site_preview implements i_xmod {
	
 static public function action($x)
 {
 	if($x == "get")
 	{
?>
<html>
 <head>
  <title>Предпросмотр оформленного текста</title>
  <script type="text/javascript" src="/style/js/core.js"></script>
  <!--<script type="text/javascript" src="/style/js/more.js"></script>-->
  <link rel="stylesheet" type="text/css" href="/style/css/default.css"/>
 </head>
 <body>
  <div id="content">
   <script type="text/javascript" language="JavaScript">
   
    new Request.HTML({url:"/x/site-preview/show",update:$('content'),data:{text:opener.document.all.<?=$_GET["id"]?>.value}}).send();
   </script>
   Загрузка контента...
  </div>
 </body>
</html>
<?
 	} else {

 		$text = $_POST["text"];
 		if(!$text) die("Текст не найден");
 		
 		$x = mr_text_trans::text2xml($text, mr_text_trans::prose);
 		
 		$fr = new mr_xml_fragment;
 		$fr->loadXML($x);
 		
 		echo $fr->realize();
 	
 	}
 	
 }
 
	}
?>