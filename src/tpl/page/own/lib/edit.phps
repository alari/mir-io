<?php class tpl_page_own_lib_edit extends tpl_page_own_lib_inc implements i_tpl_page_rightcol {
	
public function __construct($filename="", $params="")
{
	if(!ws_self::ok())
		throw new ErrorPageException("Вы не авторизованы.", 402);
	
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";

 	$sph = $params[1];
 	$pag = $params[2];
 	$id = $params[4];
 	
 	if($pag == "edit")
 	{
 		$id = (int)$id;
 		if($sph == "pub")
 		{
 			$item = ws_libro_pub_item::factory($id);
 			if($item->author != ws_self::id())
 				throw new ErrorPageException("Произведение не найдено", 404);
 				
 			$this->title = "Правка произведения: &laquo;".$item->title."&raquo;";
 			$this->content = "<h1>Правка произведения</h1>";
 		}
 		elseif($sph == "draft")
 		{
 			$item = ws_libro_pub_draft::factory($id);
 			if($item->user_id != ws_self::id())
 				throw new ErrorPageException("Черновик не найден", 404);
 				
 			$this->title = "Правка черновика: &laquo;".$item->title."&raquo;";
 			$this->content = "<h1>Правка черновика</h1>";
 		}
 	} elseif($pag == "new") {
 		$item = $params[4];
 		
 		$this->title = "Новое произведение";
 		$this->content = "<h1>Новое произведение</h1>";
 	} else throw new ErrorPageException("Страница не найдена.", 404);
 	
  $this->content .= $this->item_edit($item);
  
  $this->css[] = "pub/read.css";
}

 protected function item_edit($item)
 {
 	$action = "/x/own-lib/draft/new";
 	$usr = ws_self::id();
 	if(is_object($item))
 	{
 		$epygraph = $item->epygraph ? mr_text_trans::node2text( $item->epygraph ) : "";
 		$postscriptum = $item->postscriptum ? mr_text_trans::node2text( $item->postscriptum ) : "";
 		$content = $item->content ? mr_text_trans::node2text( $item->content ) : "";
 		
 		$title = htmlspecialchars($item->title);
 		$write_place = htmlspecialchars($item->write_place);
 		$write_time = htmlspecialchars($item->write_time);
 		$first_pub = htmlspecialchars($item->first_pub);
 		
 		if($item->user_id) $usr = $item->user_id;
 		if($item->author) $usr = $item->author;
 		
 		$id = $item->id();
 		if($item instanceof ws_libro_pub_item)
 			$action = "/x/own-lib/pub/edit";
 		else
 			$action = "/x/own-lib/draft/edit";
 			
 		$type = $item->type;
 	} else $type = $item;
 	
 	$types = array(
 		"prose"=>"Проза",
 		"stihi"=>"Стихи",
 		"article"=>"Эссе/статья"
 	);
 	
	ob_start();
?>

<form onsubmit="javascript:$('ed_sbm').disabled='yes'" action="<?=$action?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
<h2><label for="ed_title">Название:</label> <input type="text" name="title" size="40" value="<?=$title?>" id="ed_title"/></h2>

<center>Тип произведения: <select name="type">
	<?foreach($types as $t=>$v){?><option value="<?=$t?>"<?=($t==$type?' selected="yes"':"")?>><?=$v?></option><?}?></select></center>

 
 	<br />
 	<div class="pub-main">
 	
 	<blockquote class="epygraph" align="right">
 	<em><label for="ed_epy">Эпиграф:</label></em>
 		<br/>
 		<textarea cols="20" rows="5" name="epygraph" id="ed_epy"><?=$epygraph?></textarea>
 	</blockquote>
 	
 	<center><b><label for="ed_con">Основной текст произведения:</label></b></center>
 	<textarea cols="50" rows="30" name="content" id="ed_con" style="width: 95%"><?=$content?></textarea>
 	<script type="text/javascript">text_markup('ed_con', 1, 0);</script>
 	
 	<p class="postscriptum"><span class="postscriptum">Postscriptum:</span>
 		<textarea cols="20" rows="5" name="postscriptum" style="margin-left: 2em; width: 80%"><?=$postscriptum?></textarea>
 	</p>
 
 	<p class="pub-written">
 	<span><label for="ed_wt">Время написания:</label> <input type="text" size="16" name="write_time" id="ed_wt" value="<?=$write_time?>"/></span>
 	<span><label for="ed_wp">Место написания:</label> <input type="text" size="16" name="write_place" id="ed_wp" value="<?=$write_place?>"/></span>
 	</p>
 <p class="pub-author">
 	&copy; <input type="text" id="ed_fp" name="first_pub" size="4" maxlength="4" value="<?=$first_pub?>"/> <?=ws_user::factory($usr)?>
 </p>
 <div align="right">
 	<small><label for="ed_fp">Год первой публикации. Если прежде не публиковалось, оставьте поле пустым.</label></small>
 </div>
 	
 	</div>
 	
 	<center>
 		<input type="submit" value="Сохранить изменения" id="ed_sbm"/>
 			&nbsp;<?if($id){?><input type="hidden" name="id" value="<?=$id?>"/><?}?>
 		<input type="reset" value="Сбросить"/>
 	</center>
</form>

<?
	
	return ob_get_clean();
 }
	}