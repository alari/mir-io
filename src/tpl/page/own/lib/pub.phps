<?php class tpl_page_own_lib_pub extends tpl_page_own_lib_inc implements i_tpl_page_rightcol {

public function __construct($filename="", $params="")
{
	if(!ws_self::ok())
		throw new ErrorPageException("Вы не авторизованы.", 402);

 	parent::__construct($filename, $params);

 	$this->layout = "rightcol";

 	$sph = $params[1];
 	$id = (int)$params[2];

 	if($sph == "pub/pref")
 	{
 		$item = ws_libro_pub_item::factory($id);
 		if($item->author != ws_self::id())
 			throw new ErrorPageException("Произведение не найдено", 404);

 		$this->title = "Настройки произведения: &laquo;".$item->title."&raquo;";
 		$this->content = "<h1>Настройки произведения</h1><h2>".$item->pub()->link()."</h2>";
 	}
 	elseif($sph == "draft/pub")
 	{
 		$item = ws_libro_pub_draft::factory($id);
 		if($item->user_id != ws_self::id())
 			throw new ErrorPageException("Черновик не найден", 404);

 		$this->title = "Публикация черновика: &laquo;".$item->title."&raquo;";
 		$this->content = "<h1>Новая публикация</h1><h2>".$item->link()."</h2>";
 	} else throw new ErrorPageException("Страница не найдена.", 404);

  $this->content .= $this->item_pref($item);

	$this->css[] = "own/pub.css";
}

 protected function item_pref($item)
 {
 	if($item instanceof ws_libro_pub_item)
 	{
 		$action = "/x/own-lib/pub/pref";
 		$old_meta = $item->meta;
 		$old_anchors = $item->pub()->comm_anchors();
 		$old_comms = array();
 		foreach($old_anchors as $a)
 		{
 			$old_comms[] = $a->comm()->id();
 			$anch_by_comm[ $a->comm()->id() ] = $a;
 		}
 		$old_section = $item->section;
 		$old_cycle = $item->cycle;
 		$time = $item->time;
 	} else {
 		$action = "/x/own-lib/draft/pub";
 		$old_meta = null;
 		$old_comms = array();
 		$old_section = 0;
 		$old_cycle = 0;
 		$time = time();
 	}

 	$metas = self::pref_metas($item->type, $time, $old_meta);
 	$comms = self::pref_comms($item->type, $time, $old_comms);

 	$cycles = ws_libro_pub_cycle::byOwner(ws_self::id());

	ob_start();
?>

<form onsubmit="javascript:$('ed_sbm').disabled='yes'" action="<?=$action?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">

<table>
<tr>
	<td valign="top">
		<?$this->handle_cycles($cycles, $old_cycle)?>
		<?$this->handle_sections($item->type, $old_section)?>
		<?$this->handle_other( $item->authmark?$item->authmark:"normal", $item instanceof ws_libro_pub_draft, @$item->auto_contest?$item->auto_contest:"yes", @$item->description )?>

		<?foreach($comms as $c) $this->handle_comm( $c, @$anch_by_comm[$c->id()] );?>
	</td>
	<td width="50%" valign="top">
		<?foreach($metas as $m) $this->handle_meta( $m, $old_meta, $old_meta?$item->discuss:"public" );?>
	</td>
</tr>
</table>

 	<center>
 		<input type="submit" value="Сохранить изменения" id="ed_sbm"/>
 			&nbsp;<input type="hidden" name="id" value="<?=$item->id()?>"/>
 		<input type="reset" value="Сбросить" id="ed_rst"/>
 	</center>
</form>

<?

	return ob_get_clean();
 }

 protected function handle_other($authmark, $can_be_anonymous, $auto_contest, $descr)
 {
 	static $auth = array(
 		"important"=>"Высокая",
 		"normal"=>"Средняя",
 		"low"=>"Низкая");
?>

<div class="pub-other">
	<div class="pub-other-descr">
		Подзаголовок или аннотация: <input type="text" name="descr" maxlength="128" size="34" value="<?=htmlspecialchars($descr)?>"/>
	</div>

	<div class="pub-other-auth">
	<label for="p_auth">Авторская важность произведения</label>:
		&nbsp;
	<select name="authmark" id="p_auth">
	<?foreach($auth as $k=>$v){?><option value="<?=$k?>"<?=($k==$authmark?' selected="yes"':"")?>><?=$v?></option><?}?>
	</select>
	</div>

	<?if($can_be_anonymous){?>
		<div class="pub-other-ano">
	<input type="hidden" name="anonymous" value="no"/>
	<input type="checkbox" name="anonymous" value="yes" id="p_ano"/>
		&ndash; <label for="p_ano">Опубликоваться анонимно</label>
		</div>
	<?}?>

		<div class="pub-other-auto">
	<input type="hidden" name="auto_contest" value="no"/>
	<input type="checkbox" name="auto_contest" value="yes" id="p_auto"<?=($auto_contest?' checked="yes"':"")?>/>
		&ndash; <label for="p_auto">Разрешить участие в &laquo;ЁКЛМН&raquo;</label>
		</div>

</div>

<?
 }

 protected function handle_cycles($cycles, $old_cycle)
 {
?>

	<div class="pub-cycle">
<span class="pub-cycle-cap">Цикл произведения</span>
<?if(count($cycles)){?>
	<div class="pub-cycle-select">
	<label for="cycle_select">Выберите</label>: <select name="cycle" id="cycle_select"><?foreach($cycles as $cycle){?><option value="<?=$cycle->id()?>"<?=($cycle->id()==$old_cycle ? ' selected="yes"':"")?>><?=$cycle->title?></option><?}?></select>
	</div>
<?}?>
<div class="pub-cycle-new">
	<label for="cycle_new"><?=(count($cycles)?"Или введите новый":"Введите название авторского цикла произведений")?></label>: <input type="text" name="cycle_new" id="cycle_new" size="20" maxlength="64"/>
</div>
	</div>

<?
 }

 protected function handle_sections($type, $selected)
 {
?>

	<div class="pub-sec">
<span class="pub-sec-cap"><label for="p_secs">Тематический раздел произведения</label>:</span>
<select name="section" id="p_secs">
<option value="0">&nbsp;</option>
<?=ws_libro_pub_sec::options($type, $selected)?>
</select>

	</div>

<?
 }

 protected function handle_meta(ws_comm $meta, $old_meta=0, $discuss="public")
 {
 	static $disc_st = array("public"=>"Открытое", "protected"=>"Участники доминанты", "private"=>"Круг Чтения", "disable"=>"Закрытое");
?>

<div class="pub-meta">
	<span class="pub-meta-radio"><input type="radio" name="meta" id="meta_<?=$meta->id()?>" value="<?=$meta->id()?>"<?=($meta->id()==$old_meta?" checked=\"yes\"":"")?>/></span>
	<span class="pub-meta-cap"><label for="meta_<?=$meta->id()?>">Доминанта</label>: <?=$meta?></span>
	<label for="meta_<?=$meta->id()?>"><?=$meta->description?></label>

	<?if($meta->can_ch_discuss()){?>
  		<div class="pub-meta-disc">
  	<label for="discuss_<?=$meta->id()?>">Обсуждение произведения</label>:
  		&nbsp;
  	<select name="discuss_<?=$meta->id()?>" id="discuss_<?=$meta->id()?>">
  		<?foreach($disc_st as $st=>$tp){?><option value="<?=$st?>"<?=($st==$discuss?' selected="yes"':"")?>><?=$tp?></option><?}?>
  	</select>
  		</div>
  	<?}?>

</div>

<?
 }

 protected function handle_comm(ws_comm $comm, $anchor=null)
 {
 	// Категории, если есть
 	$categs = ws_comm_pub_categ::several($comm->id(), false, false, true);
 	$categ_selected = $comm->category_default;

 	if($anchor instanceof ws_comm_pub_anchor)
 	{
 		// Значит, выбрано
 		$checked = true;

 		$old_categ = $anchor->category();

 		if($old_categ)
 		{
 			$categ_selected = $old_categ->id();
 			if(!in_array($categ_selected, $categs->ids()))
 				$categs[] = $old_categ;
 		}
 	} else {

		// Обработка рецензий
 		if( $comm->recense_method != "censor" && $comm->recense_apply != "disable" )
 		{
 			if( $comm->recense_apply == "free" ) $members[0] = "- (На выбор рецензента)";
 			$members = ws_comm_member::several($comm->id(), $comm->recense_apply=="private"?ws_comm::st_curator:ws_comm::st_member);
 		}

 	}


?>

<div class="pub-comm">
	<span class="pub-comm-box">
		<input type="hidden" name="comm_<?=$comm->id()?>" value="no"/>
		<input type="checkbox" name="comm_<?=$comm->id()?>" id="comm_<?=$comm->id()?>" value="yes"<?=(@$checked?' checked="yes"':"")?>/>
	</span>
	<span class="pub-comm-cap"><label for="comm_<?=$comm->id()?>">Сообщество</label>: <?=$comm?></span>

	<label for="comm_<?=$comm->id()?>"><?=$comm->description?></label>

	<?if(count($categs)){?>
	<div class="pub-comm-categs">
		<label for="categ_<?=$comm->id()?>">Категория произведения</label>: <select id="categ_<?=$comm->id()?>" name="categ_<?=$comm->id()?>"><?foreach($categs as $c){?><option value="<?=$c->id()?>"<?=($c->description?' title="'.htmlspecialchars($c->description).'"':"").($categ_selected==$c->id()?' selected="yes"':"")?>><?=$c->title?></option><?}?></select>
	</div>
	<?}if(count(@$members)){?>
	<div class="pub-comm-members">
		<label for="editor_<?=$comm->id()?>">Попросить рецензию у</label>: <select id="editor_<?=$comm->id()?>" name="editor_<?=$comm->id()?>"><?foreach($members as $mt){?><option value="<?=$mt->user()->id()?>"<?=($mt->message?' title="'.htmlspecialchars($mt->message).'"':"")?>><?=$mt->user()->name()?></option><?}?></select>
	</div>
	<?}?>

</div>

<?
 }

 static public function pref_comms($type, $time=null, $selected=null)
 {
 	$comms = ws_comm::several( "type IN ('open','members') AND (apply_pubs!='disable'".(count( $selected )?" OR id IN (".join(",", $selected).")":"").")" );
 	$ids = array();
 	foreach($comms as $n=>$c)
 	{
 		if(in_array($c->id(), $selected)) continue;
 		if($c->can_add_pub($type, $time)) continue;
 		$ids[] = $n;
 	}
 	foreach($ids as $offset) unset($comms[$offset]);
 	return $comms;
 }

 static public function pref_metas($type, $time=null, $selected=null)
 {
 	$comms = ws_comm::several( "type IN ('meta','closed') AND (apply_pubs!='disable'".($selected?" OR id=".$selected:"").")" );
 	$ids = array();
 	foreach($comms as $n=>$c) if($c->id()!=$selected && !$c->can_add_pub($type, $time))
 		$ids[]=$n;
 	foreach($ids as $offset) unset($comms[$offset]);
 	return $comms;
 }
	}