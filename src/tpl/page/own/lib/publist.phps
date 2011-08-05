<?php class tpl_page_own_lib_publist extends tpl_page_own_lib_inc implements i_tpl_page_rightcol {
	
public function __construct($filename="", $params="")
{
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";

 	if(!ws_self::ok())
 		throw new ErrorPageException("Вы не авторизованы.", 401);
 	
	$this->title = "Ваши произведения";
 	
	$cycles = ws_libro_pub_cycle::byOwner( ws_self::id(), true );
	
	ob_start();
	?>

<style type="text/css">

.cycle li
{
	background-color: #f8fffa;
	margin-bottom: 2px;
}
.cycle li img.cmov
{
	cursor: move;
}
.cycle li img.chidden
{
	cursor: pointer;
}
.cycle li img
{
	position: relative;
	top: 2px;
	margin-left: 1px;
}
.cycle li.hidden
{
	color: gray;
}
.cycle li.hidden a
{
	color: #898988 !important;
}
.cycle li.anonymous
{
	background: #fffafa;
}
	
</style>
	
	<?
	$this->head .= ob_get_clean();
 	ob_start();
 	?>
	
<h1>Ваши произведения</h1>

<?foreach ($cycles as $c){?>

<div id="cycle-<?=$c->id()?>-container">
<h3><?=$c?></h3>

<ol id="cycle-<?=$c->id()?>" class="cycle">

 <?foreach($c->publist() as $p){?>
  <li id="pub-<?=$p->id()?>" class="<?=($p->author()->id() != $p->author ? "anonymous ":"").($p->hidden == "no" ? "" : "hidden ")?>">
   <img onclick="mr_Ajax({url:'/x/own-pubs/hidden',data:{pid:<?=$p->id()?>},evalResponse:true,evalScripts:true}).send()" src="/style/img/own/<?=($p->hidden=="no" ? "hide" : "show")?>.gif" title="<?=($p->hidden=="no"?"Скрыть произведение":"Показать произведение")?>" class="chidden"/>
   <a href="pref-<?=$p->id()?>.xml"><img src="/style/img/own/preferences.gif" width="16" height="16" alt="Настройки произведения"/></a>
   <a href="edit-<?=$p->id()?>.xml"><img src="/style/img/own/edit.gif" width="16" height="16" alt="Править"/></a>
   <img src="/style/img/own/arrows.gif" width="16" height="16" alt="Переместить произведение" class="cmov"/>
   <?if($p->anonymous=="yes"){?><img onclick="if('Вы уверены, что хотите снять анонимность с этого произведения?') mr_Ajax({url:'/x/own-pubs/anonymous',data:{pid:<?=$p->id()?>},evalResponse:true,evalScripts:true}).send()" src="/style/img/own/anonymous.gif" title="Снять анонимность" class="canonymous"/><?}?>
   	&nbsp;
   <?=$p?>
  </li>
 <?}?> 
</ol>
</div>

<?}?>

<script type="text/javascript">
 var oldParent;
 var oldPosition;
 var mrScroller = new Scroller(window);
 new Sortables("ol.cycle", {clone:true, opacity:0.8, handle:"img.cmov", revert:true, onStart:function(el){
 	el = $(el);
 	oldParent = el.getParent().get("id").replace(/^cycle-([0-9]+)$/, "$1");
 	oldPosition = el.getAllPrevious().length;
 	mrScroller.start();
 }, onComplete:function(el){
 	el = $(el);
 	mrScroller.stop();
 	var newParent = el.getParent().get("id").replace(/^cycle-([0-9]+)$/, "$1");
 	var newPosition = el.getAllPrevious().length;
 	
 	var elId = el.get("id").replace(/^pub-([0-9]+)$/, "$1");
 	
 	if(newPosition != oldPosition || newParent != oldParent)
 		mr_Ajax({url:'/x/own-pubs/move/pub',evalResponse:true,data:{newIndex:newPosition,newParentID:newParent,itemID:elId}}).send();
 }});
</script>

 <? 	
 	
  $this->content = ob_get_clean();
}

public function col_right()
{
	ob_start();
?>

<?
	return ob_get_clean().parent::col_right();
}
	}