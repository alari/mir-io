<?php
/**
 * Класс обработки текста в xml и обратно с вычислением его объёма в авторских листах.
 * 
 * @package mr
 * @author Dmitry Kourinski (cogito@mirari.ru)
 * @copyright (c) 2006-2008
 */

	class mr_text_trans implements i_initiate {
 const plain=1;
 const prose=2;
 const stihi=3;

 static protected $tags=array();

 protected $initial;
 protected $linedata=0;
 protected $authorsize=0;
 protected $chardata=0;
 protected $finite="";
 protected $isChanged = false;
 protected $error = false;

 /**
  * Передача данных конфигурации
  * 
  * allowed.tags = tag1,tag2,etc -- список разрешённых тегов, набранных через запятую
  *
  * @param array $ini
  */
 static public function init($ini)
 {
 	self::$tags = explode(",", @$ini["allowed.tags"]);
 }
 
 /**
  * Конструктор. На вход принимает xml или plain-текст
  *
  * @param string $initial
  */
 public function __construct($initial)
 {
  $this->initial=str_replace("\r", "", $initial);
 }

 /**
  * Преобразование из xml-разметки в plain-разметку
  *
  */
 public function x2t()
 {
  if($this->isChanged) return false;
 	
  $this->finite=preg_replace("/<e n=\"([^\"]+)\"\/>/", "&$1;", trim($this->initial));
  $this->finite=trim(preg_replace("/<\?xml.+\?>/", "", $this->finite));
  $this->finite=preg_replace("/ xmlns=\"[^\"]+\"/", "", $this->finite);
  $this->finite=strtr($this->finite, array("&gt;"=>">", "<l>"=>"", "</l>"=>"\n", "<l/>"=>"\n", "<separator/>"=>"\n", "<"=>"[", ">"=>"]"));
  $this->finite=substr($this->finite, 7, -8);
  $this->finite=preg_replace("/\[l sp=\"[0-9]+\"\]/", "", $this->finite);
      
  //$this->finite=preg_replace("/\[([^\]\s]+)(\s[^\]]+)?\]((.*)\n(.*))[\/\1]/xsi", "[$1$2]\n$3\n[/$1]\n", $this->finite);
  
  $this->isChanged = true;
 }

 /**
  * Преобразование из текстовой в xml-разметку
  *
  * @param int $mode=self::plain -- режим преобразования, проза, стихи, текст как есть
  * @return bool -- удалось ли совершить преобразование
  */
 public function t2x($mode=self::plain)
 {
  if($this->isChanged) return false;
 	
  switch($mode)
  {
   case self::prose: $tag="prose"; break;
   case self::stihi: $tag="stihi"; break;
   default: $tag="plain";
  }
  $x="<$tag>";

  $this->initial=htmlspecialchars($this->initial);
  $this->initial=str_replace("&quot;", "\"", $this->initial);
  $this->initial=str_replace("-- ", "– ", $this->initial);
  $this->initial=preg_replace("/&([^;]+);/i", "<e n=\"$1\"/>", $this->initial);
  $this->initial=preg_replace("/<e n=\"amp\"\/>([^;]+);/i", "<e n=\"$1\"/>", $this->initial);
  $this->initial=preg_replace("/\[([^\s\d\[]+)=/i", "[$1 $1=", $this->initial);
  $this->initial=preg_replace("/\[([^\s\d])/i", "<$1", $this->initial);
  $this->initial=preg_replace("/([^\s\d])\/\]/i", "$1/>", $this->initial);
  $this->initial=preg_replace("/([^\s\d])\]/i", "$1>", $this->initial);
  $lines = explode("\n", preg_replace("/\n\n+/", "\n<separator />\n", $this->initial));

  foreach($lines as $line)
  	if(trim(preg_replace("/<[^>]+>/", "", $line)))
  	{
  		$x.="<l>$line</l>";
  	} else {
  		$x.=$line."\n";
  	}

  $x.="</$tag>";

  $xdom=new DomDocument("1.0", "utf-8");
  @$xdom->loadHTML("<html xmlns=\"http://www.mirari.ru/\"><head><meta http-equiv=\"Content-type\" content=\"text/html; charset=utf-8\" /></head><body>$x</body></html>");
   $checkdom = new DomDocument("1.0", "utf-8");
   if(!@$checkdom->loadXML($xdom->saveXML()))
   {
   	$this->error = true;
   	return false;
   } else unset($checkdom);
   
  $workwith=$xdom->getElementsByTagName($tag)->item(0);
  $wwelements = $workwith->getElementsByTagName("*");
   if($wwelements->length == 0)
   {
   	$this->error = true;
   	return false;
   }
  foreach($wwelements as $element)
   if($element->nodeName!="plain" && $element->nodeName!="separator" && $element->nodeName!="l" && $element->nodeName!="e" && $element->nodeName!="prose" && $element->nodeName!="stihi" && !in_array($element->nodeName, self::$tags))
    $element->parentNode->removeChild($element);

  $this->handleX($workwith, $mode);
  
  $urls = $workwith->getElementsByTagName("url");
  foreach($urls as $u)
  {
   $href = $u->getAttribute("href");
   if($href) $href_src = "attr";
   else {
   	$href = $u->nodeValue;
   	if($href) $href_src = "value";
   }
 	if(strpos($href, "script:"))
 		$href = "http://mir.io/";
 	if($href_src == "attr")
 		$u->setAttribute("href", $href);
 	elseif($href_src == "value")
 		$u->nodeValue = $href;
  }

  $this->finite=&$workwith;
  return $this->isChanged=true;
 }

 /**
  * Были ли произведены преобразования -- или нет
  *
  * @return bool
  */
 public function isChanged()
 {
  return $this->isChanged;
 }

 /**
  * Случилась ошибка при преобразовании -- или нет
  *
  * @return bool
  */
 public function error()
 {
 	return $this->error;
 }
 
 protected function handleX($xml, $mode)
 {
  foreach($xml->childNodes as $element)
   switch($element->tagName)
   {
    case "plain": $this->handleX($element, self::plain); break;
    case "prose": $this->handleX($element, self::prose); break;
    case "stihi": $this->handleX($element, self::stihi); break;
    case "l":
     if($mode == self::stihi)
     {
      $this->linedata++;
      $sp=iconv_strlen(trim($element->nodeValue))-iconv_strlen(ltrim($element->nodeValue));
      if($sp>0) $element->setAttribute("sp", $sp);
     } else {
      $this->chardata+=iconv_strlen($element->nodeValue);
     }
    break;
    default: $this->handleX($element, $mode);
   }
 }

 /**
  * Возвращает объём в Авторских Листах с указанной точностью
  *
  * @param [int] $precision=3
  * @return string|double
  */
 public function getAuthorSize($precision=3)
 {
  if(!$this->authorsize) $this->authorsize = str_replace(",", ".", (string)($this->chardata/40000 + $this->linedata/700));
  return str_replace(",", ".", sprintf("%01.{$precision}f", round($this->authorsize, $precision)));
 }

 /**
  * Возвращает результат преобразований -- в виде текста или объекта
  *
  * @param bool $xml_as_text=true
  * @return string|DomDocument
  */
 public function finite($xml_as_text=true)
 {
  if(!$xml_as_text || !is_object($this->finite)) return $this->finite;
  else {
   $d = new DomDocument("1.0", "utf-8");
   $d -> appendChild($d->importNode($this->finite, true));
   return trim(preg_replace("/<\?xml.+\?>/", "", $d->saveXML()));
  }
 }
 
/**
 * Преобразовать текст из xml в plain
 *
 * @param string|DOMElement $xml Исходник
 * @return string
 */
 static public function node2text($xml)
 {
 	if(is_array($xml) && is_object($xml[0]))
 	{
 		$d = new DOMDocument("1.0", "utf-8");
 		$d->appendChild($d->importNode($xml[0], true));
 		$xml = $d->saveXML();
 	}
 	$t = new self($xml);
 	$t->x2t();
 	return $t->finite();
 }
 
/**
 * Преобразует текст в xml, возвращает результат
 *
 * @param string $text
 * @param int $mode Как оформлять текст
 * @param bool $astext Вернуть как текст или как DOM
 * @return string
 */
 static public function text2xml($text, $mode=self::plain, $astext=true)
 {
   $t = new self($text);
   $t->t2x($mode);
   return $t->finite($astext);
 }
	}
?>