<?php abstract class tpl_page_own_msg_inc extends tpl_page implements i_tpl_page_rightcol, i_locale  {
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
 
 public function col_right()
 { 	
 	ob_start();
?>

<p><?=self::$locale["msgs"]?>:
<ul>
<li><a href="inbox.xml"><?=self::$locale["inbox"]?></a></li>
<li><a href="sent.xml"><?=self::$locale["sent"]?></a></li>
<li><a href="recycled.xml"><?=self::$locale["recycled"]?></a></li>
<li><a href="flagged.xml"><?=self::$locale["flagged"]?></a></li>
</ul>
</p><p>
<ul>
<li><a href="new.xml"><?=self::$locale["new_msg"]?></a></li>
</ul>
</p>

<?
	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
 
 /**
 * Подготавливает заголовок ответа для письма
 *
 * @param string $title
 * @return string
 */
 static public function reply_title($title)
 {
  $l = explode("Re:", $title);
  $l = "Re".(count($l)>1?"[".(count($l)-1)."]":"").": ".trim($l[count($l)-1]);
  $l = explode("Re[", $l, 2);
  if(count($l)==1) return $l[0];
  else {
   $l = explode("]", $l[1], 2);
   $l[0]++;
   return "Re[".$l[0]."]".$l[1];
  }
 }
 
/**
 * Подготавливает текст с цитатой для ответа на письмо
 *
 * @param string $xmltext
 * @return string
 */
 static public function reply_text($text)
 {
  return "> ".wordwrap(str_replace("\n", "\n> ", $text), 80, "\n> ");
 }
 
	}
?>