<?php class tpl_page_libro_secs extends tpl_page implements i_locale, i_tpl_page_rightcol, i_tpl_page_submenu  {
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	
 	$this->title = self::$locale["title"];
 	
 	ob_start();
 	
 	echo "<h1>", $this->title, "</h1>";
 	
 	echo "<br/><table cellpadding=\"10\"><tr>";
 	
 	$types = array("prose", "stihi", "article");
 	
 	foreach($types as $t)
 	{
 	 $secs = ws_libro_pub_sec::several("type='$t'");
 	 echo "<td valign=\"top\" width=\"33%\"><h2>".self::$locale["type.".$t]."</h2><ul>";
 	 foreach($secs as $s) echo "<li>", $s, "</li>";
 	 echo "</ul></td>";
 	}
 	echo "</tr></table>";
 	
 	$this->content = ob_get_clean();
 }
 
 public function col_right()
 {
 	ob_start();
 	
?>
<ul>
	<li><a href="list.xml"><?=self::$locale["nav.all"]?></a></li>
	<li><a href="resp.xml"><?=self::$locale["nav.resp"]?></a></li>
	<li><a href="<?=mr::host("libro")?>/"><?=self::$locale["nav.lib"]?></a></li>
</ul>
<?
 	
 	return ob_get_clean();
 }
 
  public function p_submenu()
 {
 	$ret = array();
 	
 	$ret[mr::host("libro")] = "Литературная сфера";
 	$ret[mr::host("libro")."/list.xml"] = "Новые произведения";
 	$ret[mr::host("libro")."/resp.xml"] = "Отзывы";
 	$ret[mr::host("libro")."/comms.xml"] = "Сообщества";
 	$ret[mr::host("libro")."/events.xml"] = "События";
 	$ret[mr::host("libro")."/reader.xml"] = "Для читателей";
 	
 	return $ret;
 }
	
}?>