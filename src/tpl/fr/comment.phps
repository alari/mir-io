<?php class tpl_fr_comment implements i_locale {

	static protected $xsl;

 	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

 static public function out(ws_abstract_comment $item, $parentlink=false)
 {

 	if(!self::$xsl)
 		self::$xsl = new mr_xml_fragment;

 	self::$xsl -> loadXML($item->content);
?>

<div class="comment<?=($item->hidden=="yes"||!$item->is_showable()?" comment-hidden":"")?>" id="c<?=$item->id()?>">

 <div class="comment-fl">
 	<span><?=$item->user()?></span>
 	<?=date("d.m.y H:i:s", $item->time)?><a name="note<?=$item->id()?>"></a>
 </div>
 <div class="comment-ml">
 	<span class="comment-ava"><?=$item->user()->avatar()?><br /><?=$item->user()->status()?></span>

 	<?if($parentlink){?><div class="comment-parent"><?=$item->parent_link()?></div><?}?>

 	<br clear="right" />

 	<?=$item->out_pre()?>

  	<div id="c<?=$item->id()?>content" class="comment-content"><?if($item->is_showable()) echo self::$xsl; elseif($item->hidden=="yes") echo sprintf(self::$locale["hidden.by"], ws_user::factory($item->hidden_by?$item->hidden_by:1)->link(), date("d.m.y H:i:s", $item->hidden_time)); else echo self::$locale["hidden.parent"];?></div>

  	<?if($item->edit_time > 0 && $item->edit_by > 0){?>
  		<p class="pr"><i>Исправлено: <?=ws_user::factory($item->edit_by)?>, <?=date("d.m.y H:i:s", $item->edit_time)?></i></p>
  	<?}?>

  	<?if($item->hidden == "yes" && $item->is_showable()) echo sprintf(self::$locale["hidden.by"], ws_user::factory($item->hidden_by?$item->hidden_by:1)->link(), date("d.m.y H:i:s", $item->hidden_time))?>

 	<?if($item->user()->signature){?><div class="signature"><?=$item->user()->signature?></div><?}?>

 	<br clear="left"/>

 	</div>
 <div class="comment-ll">
  <?=$item->out_adm()?>
  <?if($item->can_hide()){?><a href="javascript:void(0)" onclick="javascript:comment_hide('<?=get_class($item)?>', <?=$item->id()?>)" id="c<?=$item->id()?>hide"><?=($item->hidden=="yes"?self::$locale["adm.show"]:self::$locale["adm.hide"])?></a><?}?>
  <?if($item->can_edit()){?><a href="javascript:void(0)" onclick="javascript:comment_edit('<?=get_class($item)?>', <?=$item->id()?>)"><?=self::$locale["adm.edit"]?></a><?}?>
  <?if($item->can_delete()){?><a href="javascript:void(0)" onclick="javascript:comment_delete('<?=get_class($item)?>', <?=$item->id()?>)"><?=self::$locale["adm.delete"]?></a><?}?>
 </div>

</div>

<?
 }

 static public function outlist(mr_list $list, $parentlink=false)
 {
  foreach($list as $v) if($v instanceof ws_abstract_comment) self::out($v, $parentlink);
 }

 static public function add($action, $parent, $div=true)
 {
 	if($div){?><div class="comment-add"><?}
 	if(!ws_self::ok()){
 		echo "Вы не авторизованы. Вы не можете добавлять комментарии.";
 	} else {?>
 	<a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'<?=$action?>',update:$(this).getParent(),evalScripts:true,data:{parent:<?=$parent?>}}).send()"><?=self::$locale["add.note"]?></a>
 	<?}
 	if($div){?></div><?}
 }

 static public function add_form($action, $class, $parent)
 {
 	$reflection = new ReflectionClass( $class );
?>

<form method="post" action="<?=$action?>" accept-charset="utf-8" id="comment-add-form">
	<?if(ws_self::ok()){?>
		Вы авторизованы как <b><?=ws_self::self()?></b> и можете оставить отзыв:
	<?}else{?>
		Вы не авторизованы. Настоятельно рекомендуется авторизоваться прежде, чем писать отзыв.
	<?}?>
	<br/>
 <textarea name="msg" id="cattach"></textarea>

 <div id="ctextmarkup"></div>

 <script type="text/javascript">text_markup('cattach', 1, 0, 'ctextmarkup');</script>
 <input type="hidden" name="parent" value="<?=$parent?>"/>
 	<?if($reflection->implementsInterface( "i_comment_afterform" )){?>
 		<?=call_user_func(array($class, "afterform"), $parent)?>
 	<?}

 	if(ws_self::ok() && $reflection->implementsInterface( "i_comment_reminder" )){

 		$rem_type = call_user_func(array($class, "reminder"), $parent);
 		if($rem_type){

 		?>

 			<div class="comment-reminder">
 		<input type="hidden" name="remind" value="no"/>
 		<input type="checkbox" name="remind" value="yes"<?=(ws_user_remind::is_signed($rem_type, $parent)?" checked='yes'":"")?>/>
         — подписаться на все новые отзывы
        <select name="method">
        	<option value="<?=ws_user_remind::method_reminder?>"<?=(ws_user_remind::is_signed($rem_type, $parent)==ws_user_remind::method_reminder ? " selected='yes'":"")?>>Оповещениями</option>
        	<option value="<?=ws_user_remind::method_email?>"<?=(ws_user_remind::is_signed($rem_type, $parent)==ws_user_remind::method_email ? " selected='yes'":"")?>>По электронной почте</option>
        </select><br/>Вы можете отправить пустое сообщение, если хотите только изменить состояние Вашей подписки
        	</div>

 	<?}}?>
 <div class="comment-submit"><button id="comment-add-submit"><?=self::$locale["add.note"]?></button></div>
</form>

<script>
 $('comment-add-submit').addEvent("click", function(e){
	 e.stop();
 	$(this).disabled = 'yes';
 	mr_Ajax_Form($('comment-add-form'), {update:$('comment-add-form').getParent()});
 });
</script>

<?
 }


	}
?>