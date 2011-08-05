<?php class tpl_fr_pubs implements i_locale {
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
 static public function out(ws_libro_pub $anonce, $tag="div", $importance=true, $num=null)
 {
 	if(!$anonce->is_showable()) return;
?>

<<?=$tag?> class="pub-anonce<?=($anonce->author()->is_full()&&(($importance&&$anonce->authmark!="low")||$anonce->authmark=="important")?" pub-anonce-important":"")?>">

	<?if($num){?><small>#<?=$num?></small> &nbsp; <?}?>

	<strong><?=$anonce?></strong> - <?=$anonce->author()?><br />
	
	<span class="pub-anonce-sub">
<?
	echo $anonce->type(), ", ", $anonce->meta();
	if($anonce->section) echo ", ", $anonce->section();
	echo ", ".self::$locale["notes"].": ".($anonce->respCount()?$anonce->respCount():"<b>".self::$locale["no_notes"]."</b>");
	
	if(count($anonce->comm_anchors()))
	{
		echo "<br/>В Сообществах: ";
		foreach($anonce->comm_anchors() as $k=>$c) echo $k?", ":"", $c;
	}
	if(count($anonce->advices()))
	{
		echo "<br/>Рекомендовано: ";
		foreach($anonce->advices() as $k=>$a) echo $k?", ":"", $a;
	}
?>
	</span>
</<?=$tag?>>

<?
 }
 
 static public function outlist(mr_list $list, $importance=true, $start_with=1)
 {
  foreach($list as $k=>$v) if($v instanceof ws_libro_pub)
  	self::out($v, "div", $importance, $start_with+$k);
 }
 
 static public function draftlist(mr_list $list, $start_with=1)
 {
 foreach($list as $k=>$v) if($v instanceof ws_libro_pub_draft)
  	self::outdraft($v, "div", $start_with+$k);
 }
 
 static public function outdraft(ws_libro_pub_draft $anonce, $tag="div", $num=null)
 {
 	if(!$anonce->user_id == ws_self::id()) return;
?>

<<?=$tag?> class="pub-anonce" id="draft-<?=$anonce->id()?>">

	<?if($num){?><small>#<?=$num?></small> &nbsp; <?}?>

	<strong><?=$anonce?></strong><br />
	
	<span class="pub-anonce-sub">
Черновик, <?=date("d/m/Y", $anonce->time)?>, <?=$anonce->type?>, <?=$anonce->size?> а.л.:
	&nbsp;
<a href="<?=$anonce->href()?>">Просмотреть</a>
	'
<a href="<?=mr::host("own")?>/draft/edit-<?=$anonce->id()?>.xml">Редактировать</a>
	'
<a href="<?=mr::host("own")?>/draft/pub-<?=$anonce->id()?>.xml">Опубликовать</a>
	'
<a href="javascript:void(0)" onclick="javascript:if(confirm('Вы уверены, что хотите удалить этот черновик? Действие необратимо!')) mr_Ajax({url: '/x/own-lib/draft/delete',data:{id:<?=$anonce->id()?>},evalResponse:yes, update:$(this).getParent()}).send()">Удалить</a>
	</span>
</<?=$tag?>>

<?
 }
	}
?>