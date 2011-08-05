<?php class tpl_page_own_msg_item extends tpl_page_own_msg_inc implements i_tpl_page_rightcol  {
	
	protected $msg;
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	
 	$msg = ws_user_msg_item::factory($params[1]);
 	if($msg->owner != ws_self::id())
 		throw new ErrorPageException("Сообщение не найдено", 404);
 		
 	$this->msg = $msg;
 	
 	$this->title = $msg->title." - личное сообщение";
 	
 	$this->content = "<h1>$msg->title</h1>";
 	$f = new mr_xml_fragment;
 	$f->loadXML($msg->content);
 	
 	$this->content .= $f->realize();
 	
 	if($this->msg->box == "inbox")
 	{
 		ob_start();
?>

<form id="fast-reply" method="post" action="/x/own-msgs/send" enctype="multipart/form-data" accept-charset="utf-8" onsubmit="javascript:$('msg_submit').disabled='yes'">
	
<i>Быстрый ответ:</i>

  <input type="hidden" name="to" value="<?=$msg->target()->login?>"/>
  <input type="hidden" name="title" value="<?=htmlspecialchars(parent::reply_title($msg->title))?>"/>
 
   <br/>
  
   <textarea name="msg"
	onfocus="javascript:$(this).get('tween', {property:'height',duration: 800, transition: Fx.Transitions.Sine.easeOut}).start(240)"
	onblur="javascript:$(this).get('tween', {property: 'height',duration: 600, transition: Fx.Transitions.Sine.easeIn}).start(20)" style="height:20px; width: 450px;"></textarea>
 
   <br />
   
  <input type="submit" id="msg_submit" value="Отправить сообщение"/></th>

</form>

<?
		$this->content .= ob_get_clean();
 	}
 	
 	if($msg->readen=="no")
 	{
 		$msg->readen = "yes";
 		$msg->save();
 	}
 	
 	$this->css[] = "own/msgs.css";
 }
 
 public function col_right()
 { 	
 	ob_start();
?>

<p>Сообщение:
<ul>
<li><?=$this->msg->target()?>
<?if($this->msg->box == "sent"){?><br/><i>(<a href="<?=mr::host("own")?>/msg/new.to-<?=$this->msg->target()->login?>.xml">Написать ещё</a>)</i><?}?>
</li>
<li><?=date("d.m.y H:i:s", $this->msg->time)?></li>
<li><?=self::$locale["in_box"]?>: <i><a href="<?=$this->msg->box?>.xml"><?=self::$locale[$this->msg->box]?></a></i></li>
</ul>
</p>
<p>Действия:
<ul>
<li><a href="/x/own-msgs/delete?id=<?=$this->msg->id()?>">Удалить</a></li>
<?if($this->msg->box == "inbox"){?><li><a href="<?=mr::host("own")?>/msg/reply.to-<?=$this->msg->id()?>.xml">Ответить</a></li><?}?>
<li><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/own-msgs/flag', data:{id:<?=$this->msg->id()?>}, update:$(this)}).send()"><?=($this->msg->flagged=="yes"?"Снять":"Поставить")?> флажок</a></li>
</ul>
</p>

<?
	$r = ob_get_clean();
	$r .= parent::col_right();
	return $r;
 }
	
	}
?>