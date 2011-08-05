<?php class tpl_fr_threads implements i_locale {
	
 	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
 static public function out(ws_comm_disc_thread $item)
 {
 	if(!$item->is_showable()) return;
?>

<div class="thread">

	<span class="thread-last">
		<a href="<?=$item->href(floor(($item->notes()-1)/20))?>#note<?=$item->last_id()?>">Последнее</a>:
		<nobr><?=$item->last_user()?></nobr>
			<br/>
		<nobr><?=date("d/m/y H:i:s", $item->last_time())?></nobr>
	</span>

	<strong><?=$item?></strong>

	<br/>
	
	<small>
		<nobr>Ветка: <?=date("d/m/y H:i:s", $item->time)?>, <?=ws_user::factory($item->user_id)?>,</nobr>
		<nobr>Отзывов: <b><?=$item->notes()?></b>, просмотров: <?=$item->views?></nobr>
	</small>
	
	<br clear="all"/>
	
</div>

<?
 }
 
 static public function outlist(mr_list $list)
 {
  foreach($list as $v) if($v instanceof ws_comm_disc_thread) self::out($v);
 }	
	}
?>