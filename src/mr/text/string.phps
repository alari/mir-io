<?php
/**
 * Класс проверки и основных преобразований строк
 * 
 * @package mr
 * @author Dmitry Kourinski (cogito@mirari.ru)
 * @copyright (c) 2006
 */
	class mr_text_string{
/**
 * Проверяет отсутствие в строке кириллических и пробельных символов и других знаков,
 * невозможных к использованию в логине
 *
 * @param string $word
 * @return bool
 */
 static public function no_ru_sp($word)
 {
  if(strpos($word, ".")!=false||strpos($word, " ")!=false||strpos($word, "]")!=false||strpos($word, "[")!=false||strpos($word, "(")!=false||strpos($word, ")")!=false||!preg_match("/(\b[a-zA-Z0-9_]+\b)/i", $word)) return false;
  if(ereg("[&\!\?\.\*\(\)\^%\\\/\$#@\"'=]+", $word)) return false;
  return true;
 }

 /**
  * Убирает опасные комбинации. Полезно для проверки данных, вводимых пользователем
  *
  * @param string $string
  * @param [bool] $removenl=true -- нужно ли убирать переводы строк
  * @return string
  */
 static public function remove_excess($string, $removenl=true)
 {
  $search=array("'<script[^>]*?>.*?</script>'si", "'<[\/\!]*?[^<>]*?>'si", '\'[\r|'.($removenl?'\n':'').']+\'', "'&(quot|#34);'i", "'&(amp|#38);'i", "'&(nbsp|#160);'i", "'<'i", "'>'i");
  $replace=array("", "", " ", "\"", "&", " ", "&lt;", "&gt;");
  	$r = trim(preg_replace($search, $replace, stripcslashes($string)));
  return ereg_replace("[ ]+", " ", $r);
 }
 
 static public function word_wrap($string, $br_after = 40, $br = "\n", $force_br = false)
 {
  $l = explode(" ", $string);
  
  $line = 0; $r = "";
  foreach($l as $w)
  {
  	if($line + iconv_strlen($w) > $br_after && $force_br)
  	{
  		$r .= $br;
  		$line = 0;
  	}
  	while(strlen($w) > $br_after)
  	{
  	 $r .= " ".$br.iconv_substr($w, 0, $br_after-2);
  	 $w = iconv_substr($w, $br_after-2);
  	 if($line) $line = 0;
  	}
  	$r .= " ";
  	$line += iconv_strlen($w);
  	$r .= $w;
  }
  
  return $r;
 }
	}
?>