<?php class tpl_page_real_manage extends tpl_page implements i_locale  {
	
	protected $city;
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	if(!ws_self::is_allowed("geo"))
 		throw new ErrorPageException(self::$locale["no_rights"], 403);
 	
 	ob_start();
 	
 ?>
 <h1><?=self::$locale["caption"]?></h1>
 <h2><?=self::$locale["descr"]?></h2>
 	<br />
 	
<?
 
?>

<p class="pr"><strong><?=self::$locale["what_to_do"]?></strong>
 <br />
<ul>
	<li><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-geo/unhandled', update:$('geo-manage')}).send()"><?=self::$locale["do.unhandled"]?></a></li>
	<li><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-geo/handled', update:$('geo-manage')}).send()"><?=self::$locale["do.handled"]?></a></li>
	<li><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-geo/citypic', update:$('geo-manage')}).send()"><?=self::$locale["do.citypic"]?></a></li>
	<li><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-geo/regions', update:$('geo-manage')}).send()">Регионы</a></li>
	<li><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-geo/countries', update:$('geo-manage')}).send()">Страны</a></li>
 </ul>
</p>
<br />
<div id="geo-manage" align="center">

</div>
 	
 <?
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->title = self::$locale["title"];
 }
	}
?>