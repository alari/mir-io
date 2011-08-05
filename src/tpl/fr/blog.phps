<?php class tpl_fr_blog {
	
	static protected $xsl;
	
 static public function out(ws_blog_item $item, $ownerLink=false)
 {
 	if(!$item->is_showable()) return;
	
 	if(!self::$xsl)
 	{
 		self::$xsl = new mr_xml_fragment;
 		self::$xsl->loadTransform("xsl/crease.xsl");
 	}
 	
 	self::$xsl -> loadXML($item->content);
 	
 	$bms = ws_blog_anchor::byItem( $item->id() );
 	
 	$bt = $item->auth()->blog_title;
 	$bt = $bt ? $bt : "Дневник";
?>

<div class="blog-item">
<h2><?=$item?></h2>
<?if($ownerLink){?>
	<div class="blog-own"><em><a href="<?=$item->auth()->href("")?>"><?=$bt?></a></em> / <b><?=$item->auth()?></b></div>
<?}?>
<div class="blog-props">
	<span class="blog-ava">
		<?=$item->auth()->avatar()?>
	</span>
<?if(count($bms)){?>
	Метки: <i><?foreach($bms as $k=>$bm) echo $k?", ":"", $bm;?></i><br/>
<?}?>
	<?if($item->mood){?>Настроение: <i><?=$item->mood?></i><br/><?}?>
	<?if($item->music){?>Музыка: <i><?=$item->music?></i><br/><?}?>
	Дата: <i><?=date("d.m.y H:i:s", $item->time)?></i>
	<?if($item->visibility!="public"){?><br/>Видимость: <i><?switch($item->visibility){
		case "protected": echo "Круг Чтения"; break;
		case "private": echo "Закрытое"; break;
	}
		?></i><?}?>

	</div>
	
	<div class="blog-content">
	
		<?=self::$xsl?>
		
	</div>

	
	<div class="blog-notes">
		<?if($item->notes()){?><i><a href="<?=$item->href()?>">Комментариев: <?=$item->notes()?></a></i>
		<?}else{?>&nbsp;<?}?>
	</div>
</div>

<?
 }
 
 static public function outlist(mr_list $list, $ownerLink=false)
 {
  foreach($list as $v) if($v instanceof ws_blog_item && $v->is_showable()) self::out($v, $ownerLink);
 }
	
	
	}
?>