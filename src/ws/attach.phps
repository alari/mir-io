<?php class ws_attach {
	
	const increment="+", decrement="-";
 
/**
 * Проверяет, есть ли в форматированном xml-тексте вложения вида <attachment id=""
 * Если установлен аттрибут $action, то считает в плюс или в минус вложения
 *
 * @param string $xmltext
 * @param const[opt] $action
 * @return array
 */
 static public function checkXML($xmltext, $action=0)
 {
 	preg_match_all("/<attachment id=\"(\d+)\"/im", $xmltext, $packets);
 	$a = @$packets[1];
 	if(count($a) && ($action==self::increment || $action==self::decrement))
 	{
 		mr_sql::query("UPDATE mr_attach_stat SET used=used".$action."1 WHERE id IN (".join(",", $a).")");
 		return mr_sql::affected_rows();
 	}
 	return $a;
 }
 
/**
 * Выводит ссылку на вложение, если это картинка -- то с картинкой
 *
 * @param int $id
 * @param string[opt] $title
 * @return string
 */
 static public function link($id, $title=null)
 {
 	$att = mr_sql::fetch(array("SELECT * FROM mr_attach_stat WHERE id=?", $id), mr_sql::obj);
 	
 	if(substr($att->type, 0, 6) == "image/")
 	{
 		return ' <a href="'.mr::host("static")."/".htmlspecialchars($att->full_src).'" title="'.$title.'" target="_blank"><img border="0" alt="'.$title.' ('.round($att->size/1000).' kb)" src="'.mr::host("static").'/'.$att->prev_src.'" /></a> ';
 		
 	} elseif(substr($att->filename, -4) == ".mp3") {
 		return ' <a href="'.mr::host("static")."/".htmlspecialchars($att->full_src).'" title="'.$title.'" target="_blank"><b>Вложение</b>: '.$att->filename.' ('.round($att->size/1000).' kb)</a>'.
 		'&nbsp;<object type="application/x-shockwave-flash"
data="'.mr::host("static").'/button/musicplayer.swf?song_title='.urlencode($title).'&song_url='.mr::host("static").'/'.htmlspecialchars($att->full_src).'" 
width="17" height="17">
<param name="movie" 
value="'.mr::host("static").'/button/musicplayer.swf?song_title='.urlencode($title).'&song_url='.mr::host("static").'/'.htmlspecialchars($att->full_src).'" />
</object>
';
 		
 	} elseif(is_object($att)) {
 		return ' <a href="'.mr::host("static").'/'.htmlspecialchars($att->full_src).'" title="'.$title.'" target="_blank"><b>Вложение</b>: '.$att->filename.' ('.round($att->size/1000).' kb)</a> ';
 	} else return " (Вложение #$id удалено) ";
 }
	
	}
?>