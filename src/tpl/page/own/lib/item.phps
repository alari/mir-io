<?php class tpl_page_own_lib_item extends tpl_page_own_lib_inc implements i_tpl_page_rightcol {
	
	private $draft;
	
public function __construct($filename="", $params="")
{
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";

 	
 	$this->draft = $draft = ws_libro_pub_draft::factory( (int)$params[1] );
 	
 	if($draft->user_id != ws_self::id())
 		throw new ErrorPageException("Черновик не существует или принадлежит другому пользователю.", 403);
 	
	$this->title = "&laquo;".$draft->title."&raquo; - Ваш черновик";
 	
 	ob_start();
 	
 	?>
	
<h1><?=$draft->title?></h1>
 <?if($draft->description){?><h2><?=$draft->description?></h2><?}?>
 
 	<br />
 	<div class="pub-main">
 	
 <?
 	$f = new mr_xml_fragment;
 if($draft->epygraph){ $f->loadXML($draft->epygraph);?><blockquote class="epygraph" align="right"><?=$f?></blockquote><?}
 	$f->loadXML($draft->content);
 	echo $f;
 if($draft->postscriptum){ $f->loadXML($draft->postscriptum);?><p class="postscriptum"><span class="postscriptum">Postscriptum:</span><?=$f?></p><?}
 
 	
 
 if( $draft->write_time || $draft->write_place ){
 	echo "<p class=\"pub-written\">";
 	if( $draft->write_time ) echo "<span>", $draft->write_time, "</span>";
 	if( $draft->write_place ) echo "<span>", $draft->write_place, "</span>";
 	echo "</p>";
 }
 
 ?>
 <p class="pub-author">&copy; <?=$draft->first_pub?> <?=ws_user::factory($draft->user_id)?></p>
 	</div>
 	
 <? 	
 	
  $this->content = ob_get_clean();
  
  $this->css[] = "pub/read.css";
}

public function col_right()
{
	ob_start();
?>
<p>Черновик:<br/>
<ul>
<li><a href="<?=mr::host("own")?>/draft/edit-<?=$this->draft->id()?>.xml">Редактировать</a></li>
<li><a href="<?=mr::host("own")?>/draft/pub-<?=$this->draft->id()?>.xml">Опубликовать</a></li>
<li><a href="javascript:void(0)" onclick="javascript:if(confirm('Вы уверены, что хотите удалить этот черновик? Действие необратимо!')) mr_Ajax({url:'/x/own-lib/draft/delete', data:{id:<?=$this->draft->id()?>,mode:'item'},update:$(this.parentNode)}).send()">Удалить</a></li>
<li>Объём: <?=$this->draft->size?> а.л.</li>
<li><?=date("d/m/Y", $this->draft->time)?></li>
</ul>
</p>
<?
	return ob_get_clean().parent::col_right();
}
	}