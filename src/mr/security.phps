<?php class mr_security implements i_initiate {

	static private $spam_substr = array(" | ", "[/url]", "</a>", "http://", "@");
	

 static public function init($ini)
 {
 	if(is_array($ini)) foreach($ini as $k=>$v) if(self::$$k != $v)
 		self::$$k = $v;
 }
 	
/**
 * Проверяет исходный текст на предмет наличия спама.
 * Возвращает true при срабатывании фильтра, false иначе
 *
 * @param string $xmltext
 * @return bool
 */
 static public function spamFilter($text)
 {
 	foreach(self::$spam_substr as $t)
 		if(strpos($text, $t)!==false)
 			return true;
 	return false;
 }
	}
?>