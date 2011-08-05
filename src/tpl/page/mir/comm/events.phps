<?php class tpl_page_mir_comm_events extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_locale {

	protected $section, $prev, $next, $perpage=20, $page;

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

 	/* @var $this->section ws_comm_event_sec */
 	$this->section = @$params[1] ? ws_comm_event_sec::loadByName($params[1], $this->comm->id()) : null;

 	$this->page = (int)@$params["page"];

 	$title = "";
 	$descr = "";

 	if( $this->section )
 	{
 		$this->perpage = $this->section->col_limit;
 		$back = $this->perpage > 0 ? $this->page*$this->perpage : 0;
 		$limit = $this->perpage > 0 ? $this->perpage : 0;
 		$anonces = $this->section->anonces($limit, $back, "time ".$this->section->col_order, $count);
 		$title = $this->section->title;
 		$descr = $this->section->description;
 		$view = $this->section->col_view;
 	}
 	else
 	{
 		$anonces = ws_comm_event_anonce::several("comm_id=".$this->comm->id(), $this->perpage, $this->perpage*$this->page, "time DESC", $count);
 		$title = self::$locale["title"];
 		$descr = $this->comm;
 		$view = "anonce";
 	}

 	$this->title = $title;
 	$this->description = $title . " ".$this->comm->title." ".$this->comm->description;

 	ob_start();

 ?>
 <h1><?=$title?></h1>
 <h2><?=$descr?></h2>
 	<br />
 <?

 	$pager = "";

 	if($this->page*$this->perpage+count($anonces) < $count || $this->page)
 	{

 		ob_start();

 		echo "<div class=\"pager\">".ws_pager::title().": ";

 		$pages = ws_pager::arr($this->page, floor($count/$this->perpage)-1);
 		$prev = 0;

 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";

 			echo '&nbsp; ', $p == $this->page ? "<b>".($p+1)."</b>" : '<a href="'.($this->section ? $this->section->href($p) : "events".($p?".page-$p":"").".xml").'">'.($p+1).'</a>';
 			$prev = $p;
 		}

 		echo "</div>";

 		$pager = ob_get_flush();
 	}

 	tpl_fr_events::outlist($anonces, $view);

 	echo $pager;

 	$this->content = ob_get_contents();
 	ob_end_clean();

 	$this->css[] = "event/anonce.css";
 }

 public function col_right()
 {
 	ob_start();

 	if($this->comm)
 	{
?><p>Сообщество: <?=$this->comm?></p><?

		if($this->section)
		{
?>
<p><?=($this->section->apply=="column"?self::$locale["col"]:self::$locale["sec"])?>: <?=$this->section?></p>
<?if($this->section->apply=="column" && $this->section->owner!=0){?>
<p><?=self::$locale["col_owner"]?>: <?=ws_user::factory($this->section->owner)?></p>
<?}
	$cols = ws_comm_event_sec::several("comm_id=".$this->comm->id()." AND id!=".$this->section->id()." AND display='yes'");
		} else $cols = ws_comm_event_sec::several("comm_id=".$this->comm->id()." AND display='yes'");

if(count($cols)){
?>
<p><?=($this->section ? self::$locale["others"] : self::$locale["comm_others"])?>:
<ul><?foreach($cols as $c){?><li><?=$c?></li><?}?></ul>
</p>
<?}

 	} else {


 		$cids = array();
 		$cids_r = mr_sql::query("SELECT DISTINCT comm_id FROM mr_comm_events");
 		while($i = mr_sql::fetch($cids_r, mr_sql::get)) $cids[] = $i;

 		$comms = ws_comm::several($cids);

?><p><?=self::$locale["secs_in_comms"]?>:<br />
<?foreach($comms as $c){?>&nbsp; &nbsp;<?=$c?><br /><?}?>
</p><?
 	}

?>
<p><?=self::$locale["navigation"]?>:
<ul>
<?if($this->section && ws_self::ok() && $this->section->can_add_item()){?>
<li><a href="<?=$this->section->comm()->href("ev-add-".$this->section->id().".xml")?>">Добавить событие в колонку</a></li>
<?}?>
<?if($this->section){?><li><a href="events.xml"><?=self::$locale["nav.comm_events"]?></a></li><?}?>
<li><a href="/soc/events.xml"><?=self::$locale["nav.events"]?></a></li>
</ul>
</p>
<?

	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }

	}
?>