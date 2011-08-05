<?php class tpl_page_libro_read extends tpl_page implements i_tpl_page_rightcol, i_locale, i_tpl_page_submenu   {

	protected $pub;
	protected $keywords = "Творчество, литература, литературный клуб, читать, я пишу, современные авторы, литературный конкурс, рецензии, критика, статьи, стихи, проза, эссе, авторы, творческие люди";

	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "rightcol";



 	/* @var $pub ws_libro_pub_item */
 	$pub = ($this->pub = ws_libro_pub_item::factory((int)$params[1]));
 	if(!$pub->pub()->is_showable())
 		throw new ErrorPageException(self::$locale["not_found"], 404);

 	// Зачитываем просмотр
 	$pub->currentStat(true);
 	ws_user_remind::zeroize(ws_user_remind::type_pub_resp, $pub->id());

 	$this->title = "&laquo;".$pub->title."&raquo;".($pub->description?" - ".$pub->description:"").", ".$pub->pub()->author()->name();

 	ob_start();


 	if(@$params["view"]=="print")
 	{
 		$this->layout = "print";
 		$params["text"] = "only";

 		echo "<div>".self::$locale["print.auth"].": <a href=\"".$this->pub->pub()->author()->href()."\">".$this->pub->pub()->author()->href()."</a></div>";
 		echo "<div>".self::$locale["print.page"].": <a href=\"".$this->pub->pub()->href()."\">".$this->pub->pub()->href()."</a></div>";
 	}

 ?>
 <h1><?=$pub->title?></h1>
 <?if($pub->description){?><h2><?=$pub->description?></h2><?}?>

 	<br />
 	<div class="pub-main">

 <?
 	$f = new mr_xml_fragment;
 if($pub->epygraph){ $f->loadXML($pub->epygraph);?><blockquote class="epygraph" align="right"><?=$f?></blockquote><?}
 	$f->loadXML($pub->content);
 	echo $f;
 if($pub->postscriptum){ $f->loadXML($pub->postscriptum);?><p class="postscriptum"><span class="postscriptum">Postscriptum:</span><?=$f?></p><?}

 	// Не показывать авторскую информацию
 	if(!isset($params["-author"]) || $params["-author"] != "no")
 	{

 if( $pub->write_time || $pub->write_place ){
 	echo "<p class=\"pub-written\">";
 	if( $pub->write_time ) echo "<span>", $pub->write_time, "</span>";
 	if( $pub->write_place ) echo "<span>", $pub->write_place, "</span>";
 	echo "</p>";
 }

 ?>
 <p class="pub-author">&copy; <?=$pub->first_pub?> <?=$pub->pub()->author()?></p>
 	<?}?>
 	</div>

 <?
 	// Если не нужно, не выводим!
 	if(!isset($params["text"]) || $params["text"] != "only")
 	{

 	// голосование
 	if(!isset($params["-voting"]) || $params["-voting"] != "no") $this->pub_voting($pub);
 	// рекомендация к прочтению
 	if(!isset($params["-advice"]) || $params["-advice"] != "no") $this->pub_advice($pub);
 	// панель контроля
 	if(!isset($params["-control"]) || $params["-control"] != "no") $this->pub_control($pub);

 	/*if(!isset($params["-voting"]) || $params["-voting"] != "no"){
 ?>
 	<center>
 		<?=sprintf(self::$locale["sms"], $pub->id())?>
 	</center>
 <?} */

 		// Отключение отображения отзывов
 		if(!isset($params["-resp"]) || $params["-resp"] != "no"){

 		tpl_fr_comment::outlist($pub->getNotes());
 	}

 	if((!isset($params["-resp-add"]) || $params["-resp-add"] != "no") && $pub->can_resp()) tpl_fr_comment::add("/x/ajax-pub/resp", $pub->id());

 		}


 	$this->content = ob_get_contents();
 	ob_end_clean();

 	$this->css[] = "comment.css";
 	$this->css[] = "pub/read.css";
 	$this->head .= "<script src=\"/style/js/comment.js\" type=\"text/javascript\"></script>";
 }

 public function col_right()
 {
 	/* @var $pub ws_libro_pub */
 	$pub = $this->pub->pub();


 	ob_start();
 	// Доминанта и навигация вверх
?>
<p><?=self::$locale["auth"]?>: <?=$pub->author()?></p>

<p><?=self::$locale["dominant"]?>: <?=$pub->meta();?>
<ul>
	<li><a href="list.meta-<?=$pub->meta()->name?>.xml">Произведения доминанты</a></li>
	<li><a href="list.xml"><?=self::$locale["nav.all"]?></a></li>
	<li><a href="secs.xml"><?=self::$locale["nav.secs"]?></a></li>
	<li><a href="<?=mr::host("libro")?>/">Литературная сфера</a></li>
</ul>
</p>
<?
	// Автор и циклы автора

	if($pub->author()->id() == $pub->author(true)->id())
	{
		$cycles = ws_libro_pub_cycle::byOwner($pub->author()->id(), 1);
?>
<p><a href="<?=$pub->author()->href("pubs")?>"><?=self::$locale["auth_cycles"]?></a>:
<ul>
<?foreach($cycles as $c) if($c->is_showable()){
	echo "<li>";
	if($c->id() == $pub->cycle) echo "<strong>";
	echo $c;
	if($c->id() == $pub->cycle) echo "</strong>";
	echo "</li>";
}?>
</ul>
</p>
<?
	}

	// Сообщества, где есть
	$comms = $pub->comm_anchors();
	if( count($comms) )
	{
?>
<p><?=self::$locale["in_comms"]?>:</p>
<?foreach( $comms as $c ){?>
<p>
<?=$c->comm()?>
	<br/>
	<center><em><?=( $c->category() ? $c->category() : "<a href=\"".$c->comm()->href("pubs-n.xml")."\">".self::$locale["no_categ"]."</a>" )?></em></center>
</p>
<?}
	}

	// Статистика произведения
?>
<p><?=self::$locale["stat"]?>:<br/>
<ul>
<?if($pub->section){?><li><?=self::$locale["stat.sec"]?>: <em><?=$pub->section()?></em></li><?}?>
<li><?=self::$locale["stat.rating"]?>: <?=$pub->rating?></li>
<li><?=self::$locale["stat.size"]?>: <?=$pub->size." ".self::$locale["stat.size_al"]?></li>
<li><?=self::$locale["stat.added"]?>: <?=date("d.m.Y", $pub->time)?></li>
<li><?=self::$locale["stat.views"]?>: <?=ws_libro_pub_stat::pubStat($pub->id())->views?></li>
<li><?=self::$locale["stat.votes"]?>: <?=ws_libro_pub_stat::pubStat($pub->id())->votes?></li>
<?if($pub->clubscore){?><li><?=self::$locale["stat.club"]?>: <a href="javascript:void(0)" title="<?=self::$locale["stat.club_info"]?>" onclick="javascript:mr_Ajax({url:'/x/ajax-pub/clubvotes', update: $('pub-clubvotes'),data:{id:<?=$pub->id()?>}}).send()"><?=$pub->clubscore()?></a></li><?}?>
</ul>
</p>
<?if($pub->clubscore){?><p id="pub-clubvotes"></p><?}

	// Рекомендации
	$adv = $pub->advices();
	if(count($adv))
	{
?>
<p><?=self::$locale["advices"]?>:<br/>
<?foreach ($adv as $n=>$a) echo $n>0?", ":"", $a;?>
</p>
<?
	}

?>

<p><center><i><a href="<?=$pub->href("view-print")?>">Версия для печати</a></i></center></p>

<?

	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }

 public function pub_voting(ws_libro_pub_item $pub)
 {
 	if(!$pub->can_vote()) return;
 	?>
	<div class="comment-add">
 		<form method="post" action="/x/ajax-pub/vote">

 			<!-- обычная -->
 			<?if($pub->currentStat()->vote == 0){?>
   		<?=self::$locale["vote.rait"]?>:
 				<select name="vote">
      <option value="4" selected="yes"><?=self::$locale["vote.rait.best"]?></option>
      <option value="3"><?=self::$locale["vote.rait.good"]?></option>
      <option value="2"><?=self::$locale["vote.rait.well"]?></option>
      <option value="1"><?=self::$locale["vote.rait.med"]?></option>
      <option value="-1"><?=self::$locale["vote.rait.soso"]?></option>
      <option value="-3"><?=self::$locale["vote.rait.shit"]?></option>
     			</select>
 			<?}?>

 			&nbsp;&nbsp;&nbsp;

 			<?if(ws_self::is_allowed("clubvote")){

 		$vo = array(
   			0 => "empty",
   			-1 => "neud",
   			1 => "udovl",
   			3 => "hor",
   			6 => "otl"
   		);
   		$vo_s = $pub->currentStat()->clubvote;

 				?>
	 			<?=self::$locale["vote.club"]?>:

	 			<select name="clubvote">
	 			<?foreach ($vo as $k => $v) {?>
	 				<option value="<?=$v?>"<?=($k==$vo_s?' selected="yes"':"")?>><?=ws_libro_pub::getClubscore($k)?></option>
	 			<?}?>
	 			</select>

	 			&nbsp;&nbsp;&nbsp;
 			<?}?>

 			<input type="button" value="<?=self::$locale["vote.do"]?>" onclick="$(this).disabled='yes';mr_Ajax_Form($(this).getParent(), {update:$(this).getParent().getParent()})"/>
 			<input type="hidden" name="pub_id" value="<?=$pub->id()?>"/>

 			<?if(ws_self::is_allowed("clubvote")){?><br/><i><?=self::$locale["vote.club.text"]?></i><?}?>
 		</form>
 	</div>
 <?
 }

 public function pub_advice(ws_libro_pub_item $pub)
 {
	if(!ws_libro_pub_advice::can_advice($pub)) return;
?>
	<div class="comment-add">
		<form method="post" action="/x/ajax-pub/advice">

		<?=self::$locale["advice.reason"]?>:<br/>
		<textarea cols="16" rows="2" name="reason"
			onfocus="javascript:$(this).get('tween', {property:'height',duration: 500, transition: Fx.Transitions.Sine.easeOut}).start(140)"
	onblur="javascript:$(this).get('tween', {property: 'height',duration: 400, transition: Fx.Transitions.Sine.easeIn}).start(40)"

			style="height:40px; width: 250px;"></textarea>

		&nbsp;&nbsp;&nbsp;<input type="button" value="<?=self::$locale["advice.do"]?>" onclick="$(this).disabled='yes';mr_Ajax_Form($(this).getParent(), {update: $(this).getParent().getParent()}); return false;"/>

		<input type="hidden" name="pub_id" value="<?=$pub->id()?>"/>
		</form>

	</div>
<?
 }

 public function pub_control(ws_libro_pub_item $pub)
 {
 	if(!ws_self::ok()) return;

 	$auth_control = false;
 	$admx_control = false;
 	$comm_control = false;

 	if(ws_self::id() == $pub->pub()->author(true)->id())
 		$auth_control = true;

 	$in_comms = $pub->pub()->comm_anchors();
 	foreach($in_comms as $c) if(($c->editor == ws_self::id() && ws_self::is_member($c->comm_id)) || ws_self::is_member($c->comm_id, ws_comm::st_leader))
 	{
 		$comm_control = true;
 		break;
 	}
 	if(!$comm_control)
 	{
 		$self_in_comms = ws_self::self()->memberof(1);
 		foreach ($self_in_comms as $comm=>$status) if($status >= ws_comm::st_curator && ws_comm::factory($comm)->editors_apply == "yes")
 		{
 			$comm_control = true;
 			break;
 		}
 	}

 	if(ws_self::is_allowed("to_hide", $pub->meta))
 		$admx_control = true;

 	if($admx_control || $auth_control || $comm_control)
 	{
?>
<div class="comment-add">
	<?=self::$locale["control"]?>:
	<?if($auth_control){?><a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/ajax-pub/control/auth', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()"><?=self::$locale["control.auth"]?></a><?}?>
	<?if($admx_control){?><a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/ajax-pub/control/admx', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()"><?=self::$locale["control.adm"]?></a><?}?>
	<?if($comm_control){?><a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/ajax-pub/control/comm', update:$('pub-control'),data:{pub:<?=$pub->id()?>}}).send()"><?=self::$locale["control.comm"]?></a><?}?>


	<div id="pub-control"></div>
</div>
<?
 	}
 }

  public function p_submenu()
 {
 	$ret = array();

 	$ret[mr::host("libro")] = "Литературная сфера";
 	$ret[mr::host("libro")."/list.xml"] = "Новые произведения";
 	$ret[mr::host("libro")."/resp.xml"] = "Отзывы";
 	$ret[mr::host("libro")."/comms.xml"] = "Сообщества";
 	$ret[mr::host("libro")."/events.xml"] = "События";
 	$ret[mr::host("libro")."/reader.xml"] = "Для читателей";

 	return $ret;
 }

	}
?>