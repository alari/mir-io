<?php class tpl_page_own_blog extends tpl_page implements i_tpl_page_submenu {
	
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	if(!ws_self::ok()) throw new ErrorPageException("Вы не авторизованы", 402);
 	
 	if(@$params["edit"])
 	{
 		
 		$ed_id = (int)$params["edit"];
 		$item = ws_blog_item::factory($ed_id);
 		if(!$item->time)
 			throw new ErrorPageException("Запись не найдена", 404);
 		
 		if(!$item->is_editable())
 			throw new ErrorPageException("Нет прав на правку этой записи", 404);
 			
 		$content = mr_text_trans::node2text( $item->content );
 		$title = htmlspecialchars( $item->title );
 		$mood = htmlspecialchars( $item->mood );
 		$music = htmlspecialchars( $item->music );
 		
 		$resp_ch = $item->responses;
 		$vis_ch = $item->visibility;
 		
 		$this->title = "Правка дневниковой записи";
 		
 		$bms = ws_blog_bm::byUser( $item->auth()->id() );
 		
 		$curbms = $item->bms();
 		$bms_checked = array();
 		foreach ($curbms as $cbms) $bms_checked[] = $cbms->bm_id;
 		
 	} else {
 		
 		$this->title = "Новая запись в дневнике";
		$bms = ws_blog_bm::byUser( ws_self::id() );
		
		$bms_checked = array();
		
 	}
 	
 	
 	
 	
 	ob_start();
 	
 	$vis = array(
 		"public" => "Всем",
 		"protected" => "Кругу Чтения",
 		"private" => "Никому"
 	);
 	$resp = array(
 		"yes" => "Разрешены",
 		"hidden" => "Прятать",
 		"no" => "Запрещены"
 	);
 	
 	echo "<h1>", $this->title, "</h1>";
 
?>

<center>
<form method="post" action="/x/own-blog/<?=($ed_id?"edit":"add")?>" enctype="multipart/form-data" onsubmit="javascript:$('b-submit').disabled='yes'">
 <table width="20%">
 <colgroup>
 	<col width="50%"/>
 	<col/>
 </colgroup>
  <tr><td>Заголовок:</td><td><input type="text" name="title" size="32" maxlength="127" value="<?=$title?>"/></td></tr>
  <tr><td>Настроение:</td><td><input type="text" name="mood" maxlength="128" size="32" value="<?=$mood?>"/></td></tr>
  <tr><td>Музыка:</td><td><input type="text" name="music" maxlength="128" size="32" value="<?=$music?>"/></td></tr>
  <tr><td colspan="2" align="center">Текст сообщения:
  	<br/>
  	<textarea name="content" cols="70" rows="22" id="attach"><?=$content?></textarea>
  	<script type="text/javascript">text_markup('attach', 1, 1);</script>
  </td></tr>
  
  <tr>
  	<td colspan="2" align="center">
  	
  	Видимость: &nbsp;
  	<select name="vis">
  	<?foreach ($vis as $key => $value) {?>
  		<option value="<?=$key?>"<?=($vis_ch == $key ? ' selected="yes"' : "")?>><?=$value?></option>
  	<?}?>
  	</select>
  	
  		<br/>
  		
  	Отзывы: &nbsp;
  	<select name="resp">
  	<?foreach ($resp as $key => $value) {?>
  		<option value="<?=$key?>"<?=($resp_ch == $key ? ' selected="yes"' : "")?>><?=$value?></option>
  	<?}?>
  	</select>
  	
  	</td>
  </tr>

  <tr>
  	<td colspan="2">
  		<b>Метки:</b><br/>
  	
  		 <?
	foreach($bms as $bk=>$b){ if($bk) echo ", "; ?><input type="checkbox" name="bm[]" value="<?=$b->id()?>" id="bm_<?=$b->id()?>"<?=(in_array($b->id(), $bms_checked)?' checked="yes"':"")?>/>&nbsp;&ndash;&nbsp;<label for="bm_<?=$b->id()?>"><?=($b->title)?></label><?}
 ?>
  		
  	</td>
  </tr>
  
  <tr>
  	<td colspan="2" align="center">
  	
  	
 <input type="submit" value="Сохранить изменения" id="b-submit"/>
 <?if($ed_id){?><input type="hidden" name="id" value="<?=$ed_id?>"/><?}?>
  	
  	</td>
  </tr>
 
 </table>
</form>
</center>

<?
 	
 	
 	$this->content = ob_get_clean();
 }
 
 public function p_submenu()
 {
 	$ret = array();
 	
 	$ret[ mr::host("blogs") ] = "Новое в Дневниках";
 	$ret[ ws_self::self()->href("") ] = ws_self::self()->blog_title ? ws_self::self()->blog_title : "Ваш дневник";
 	
 	if(ws_self::self()->is_allowed("circle"))
 			$ret[ mr::host("own")."/circle/blogs.xml" ] = "Дневники Круга Чтения";
 	
 	return $ret;
 }
	
}?>