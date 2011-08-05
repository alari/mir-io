<?php class tpl_page_mir_comm_event_item extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_locale, i_tpl_page_ico {

	protected $ev_section, $event;

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

 	/* @var $event ws_comm_event_item */
 	$event = is_numeric($params[1]) ? ws_comm_event_item::factory($params[1]) : ws_comm_event_item::byName($params[1], $this->comm->id());
 	$this->event = $event;

 	$this->description = $this->event->title." ".$this->event->description." ".$this->event->comm()->title." ".$this->event->comm()->description;

 	if(!$event->comm_id || !$event->is_showable())
 		throw new ErrorPageException(self::$locale["not_found"], 404);

 	$event->increment_view();

 	$this->title = $event->title.($event->description?" - ".$event->description:"");

 	$this->ev_section = $event->section();

 	ob_start();

 ?>
 <h1><?=$event->title?></h1>
 <h2><?=$event->description?></h2>
 	<br />
 <?
 	$f = new mr_xml_fragment;
 	$f->loadXML($event->content);
 	echo $f;

 	tpl_fr_comment::outlist($event->getNotes());
 	tpl_fr_comment::add("/x/ajax-event/note", $event->id());

 	$this->content = ob_get_contents();
 	ob_end_clean();

 	$this->css[] = "comment.css";
 	$this->head .= "<script src=\"/style/js/comment.js\" type=\"text/javascript\"></script>";
 }

 public function col_right()
 {
 	/* @var $section ws_comm_event_sec */
 	$section = $this->ev_section;

 	$lim = $section->last_limit;

 	if($lim!=0) $last_an = $section->anonces( $lim>0?$lim:0, 0, "time ".$section->last_order );

 	$cols = ws_comm_event_sec::several("comm_id=".$section->comm()->id()." AND id!=".$section->id." AND display='yes'");

 	ob_start();
?>
<p><?=self::$locale["comm"]?>: <?=$this->comm?></p>
<p><?=self::$locale["added"]?>: <?=date("d.m.Y", $this->event->time)?>, <?=$this->event->auth()?></p>
<?if($this->event->city){ $city = ws_geo_city::byID($this->event->city); ?>
<p>Город: <?=$city->flag()." ".$city->link()?><br />
<a href="<?=mr::host("real")?>/in-<?=($city->code?$city->code:$city->id())?>.xml">Все события в городе <?=$city->name?></a></p>
<?}?>
<p><?=($section->apply=="column"?self::$locale["col"]:self::$locale["sec"])?>: <?=$section?></p>
<?if($section->apply=="column" && $section->owner!=0){?>
<p><?=self::$locale["col_owner"]?>: <?=ws_user::factory($section->owner)?></p>
<?}
if(count($last_an)){?>
<p><?=self::$locale["last.".$section->last_order]?>:
<ul>
<?foreach($last_an as $an){?><li><?=$an?></li><?}?>
</ul>
</p>
<?}

	if(count($cols)){
?>
<p><?=self::$locale["comm_cols"]?>:
<ul><?foreach($cols as $c){?><li><?=$c?></li><?}?></ul>
</p>
<?
	}

?>
<p><a href="events.xml"><?=self::$locale["comm_all_events"]?></a></p>
<?if( ws_self::ok() && ( $this->event->can_edit() || $this->event->can_ch_vis() || $this->event->can_close() || $this->event->can_delete ) ){?>

<p><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-event/adm', data:{id:<?=$this->event->id()?>},update:$(this).getParent()}).send()">Администрирование события</a></p>

<?}
if(ws_self::ok() && $this->ev_section->can_add_item()){?>

<p><a href="<?=$this->event->comm()->href("ev-add-".$this->ev_section->id().".xml")?>">Добавить событие в колонку</a></p>

<?}

	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }

 public function p_ico()
 {
 	return "events";
 }

	}
?>