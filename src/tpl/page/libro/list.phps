<?php class tpl_page_libro_list extends tpl_page implements i_tpl_page_rightcol, i_locale, i_tpl_page_submenu {
	
	private $ch_meta="all", $ch_type="all", $ch_order="time", $ch_club="all", $pagename="list", $ext="xml",
		$av_types = array( "prose"=>"", "stihi"=>"", "article"=>"", "all"=>"" ),
		$av_orders = array( "time"=>"", "recent"=>"", "rating"=>"" ),
		$av_club = array("otl"=>"", "hor"=>"", "all"=>""),
		$av_metas = null, $inlink = array(), $page_next = null, $page_prev = null,
		$perpage = 30, $section = 0;
	
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
 	
 	foreach($this->av_types as $k=>&$v) $v = self::$locale["type.".$k];
 	foreach($this->av_orders as $k=>&$v) $v = self::$locale["order.".$k];
 	foreach($this->av_club as $k=>&$v) $v = $k=="all"?self::$locale["club.all"]:ws_libro_pub::getClubscore($k);
 	
 	if(@$params["meta"] && ws_comm::byName( $params["meta"] )->id())
 		$this->ch_meta = $params["meta"];
 		
 	if(@$params["type"] && $this->av_types[$params["type"]])
 		$this->ch_type = $params["type"];
 		
 	if(@$params["order"] && $this->av_orders[$params["order"]])
 		$this->ch_order = $params["order"];
 		
 	if(@$params["club"] && $this->av_club[$params["club"]])
 		$this->ch_club = $params["club"];
 		
 	if(is_numeric(@$params[1]))
 	{
 		$this->section = ws_libro_pub_sec::factory($params[1]);
 		$this->pagename = $this->section->id();
 		$this->ext = "ml";
 	}
 		
 	$this->av_metas = ws_comm::several("(type='meta' OR (type='closed' AND (id IN (SELECT comm_id FROM mr_comm_members WHERE user_id=".ws_self::id().") OR name='".$this->ch_meta."'))) AND (apply_prose!=0 OR apply_stihi!=0 OR apply_article != 0)");
 	
 	$this->inlink = array(
 		"meta"=> $this->ch_meta=="all"?"":".meta-".$this->ch_meta,
 		"order"=> $this->ch_order=="time"?"":".order-".$this->ch_order,
 		"type"=> $this->ch_type=="all"?"":".type-".$this->ch_type,
 		"club"=> $this->ch_club=="all"?"":".club-".$this->ch_club
 	);
 		
 	$page = (int)$params["page"];
 	
 	$where = "";
 	$order = "";
 	
 	switch( $this->ch_meta )
 	{
 		case "all":
 			$where = "meta IN (".join(",", $this->av_metas->ids()).")";
 		break;
 		
 		default:
 			$where = "meta=".ws_comm::byName( $this->ch_meta )->id();
 	}
 	
 	switch( $this->ch_type )
 	{
 		case "all": break;
 		
 		default:
 			$where .= " AND type='".$this->ch_type."'";
 	}
 	
 	switch( $this->ch_club )
 	{
 		case "all": break;
 		
 		default:
 			$where .= " AND clubscore=".ws_libro_pub::scoreFromMark($this->ch_club);
 	}
 	
 	$this->title = self::$locale["title.pubs"];
 	
 	switch($this->ch_order)
 	{
 		case "recent":
 			$where .= " AND time>UNIX_TIMESTAMP()-7*86400";
 			$order = "rating DESC";
 			$this->title .= $this->av_orders[$this->ch_order];
 		break;
 	
 		case "time":
 			$order = "time DESC";
 		break;
 		
 		case "rating":
 			$order = "rating DESC";
 			$this->title .= $this->av_orders[$this->ch_order];
 		break;
 	}
 	
 	if($this->section)
 		$where .= " AND section=".$this->section->id();
 	
 	$this->title = ($this->section ? $this->section->title : ($this->ch_type=="all"? self::$locale["title.pubs"] :$this->av_types[$this->ch_type])).($this->ch_order=="time"?" ".self::$locale["title.modern_auth"]:", ".$this->av_orders[$this->ch_order]);
 	
 	$pagehref = $this->pagename.$this->inlink["meta"].$this->inlink["order"].$this->inlink["type"].$this->inlink["club"];
 	
 	//начали вывод
 	ob_start();
 	
 ?>
 <h1><?=$this->title?></h1>
 <h2><?=self::$locale["descr"]." ".($this->ch_meta!="all"?ws_comm::byName($this->ch_meta)->link():"")?></h2>
 	<br />
 <?
 	$page = (int)@$params["page"];
 
 	$anonces = ws_libro_pub::several($where, $this->perpage, $this->perpage*$page, $order, $count, true);
 	
 	$pager = "";
 	
 	if($page*$this->perpage+count($anonces) < $count || $page)
 	{
 	
 		ob_start();
 		
 		echo "<div class=\"pager\">".ws_pager::title().": ";
 		
 		$pages = ws_pager::arr($page, floor($count/$this->perpage)-1);
 		$prev = 0;
 		
 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";
 				
 			echo '&nbsp; ', $p == $page ? "<b>".($page+1)."</b>" : '<a href="'.$pagehref.($p?".page-".($p):"").".".$this->ext.'">'.($p+1).'</a>';
 			$prev = $p;
 		}
 	
 		if($page) $this->page_prev = $pagehref.($page>1?".page-".($page-1):"").".".$this->ext;
		
		if($page*$this->perpage+count($anonces) < $count) $this->page_next = $pagehref.".page-".($page+1).".".$this->ext;
 			
 		echo "</div>";
 		
 		$pager = ob_get_flush();
 	}
 	
 	tpl_fr_pubs::outlist($anonces, true, $page*$this->perpage+1);
 	
 	echo $pager;
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "pub/anonce.css";
 }
 
 public function col_right()
 {
 	
 	ob_start();
 	
 	$inlink = &$this->inlink;

 	if($this->section){
?>
<p><?=self::$locale["add.section"]?>:<br/>
<ul><li><strong><?=$this->section?></strong></li></ul></p>
<?}?>
<p><?=self::$locale["add.dominant"]?>:<br />
<ul>
<?foreach($this->av_metas as $m){?><li><?=($m->name==$this->ch_meta ? "<strong>".$m->title."</strong>" : "<a href=\"".$this->pagename.".meta-".$m->name.$inlink["order"].$inlink["type"].$inlink["club"].".".$this->ext."\">".$m->title."</a>")?></li><?}?>
<li><?=($this->ch_meta=="all"?"<strong>".self::$locale["meta.all"]."</strong>":"<a href=\"".$this->pagename.$inlink["order"].$inlink["type"].$inlink["club"].".".$this->ext."\">".self::$locale["meta.all"]."</a>")?></li>
</ul>
</p>
<p>
<?if($this->ch_meta == "all"){?>
<a href="resp.xml">Лента отзывов</a>
<?} else {?>
<a href="<?=ws_comm::byName($this->ch_meta)->href("resp.xml")?>">Отзывы в доминанте</a>
<?}?>
</p>
<p><?=self::$locale["add.order"]?>:<br />
<ul>
<?foreach($this->av_orders as $o=>$t){?><li><?=($o==$this->ch_order?"<strong>$t</strong>":"<a href=\"".$this->pagename.$inlink["meta"].($o=="time"?"":".order-".$o).$inlink["type"].$inlink["club"].".".$this->ext."\">$t</a>")?></li><?}?>
</ul>
</p>
<?if(!$this->section || $this->section->type == "old"){?>
<p><?=self::$locale["add.pub_types"]?>:<br />
<ul>
<?foreach($this->av_types as $o=>$t){?><li><?=($o==$this->ch_type?"<strong>$t</strong>":"<a href=\"".$this->pagename.$inlink["meta"].$inlink["order"].($o=="all"?"":".type-".$o).$inlink["club"].".".$this->ext."\">$t</a>")?></li><?}?>
</ul>
</p>
<?}?>
<p><?=self::$locale["add.clubvote"]?>:<br />
<ul>
<?foreach($this->av_club as $o=>$t){?><li><?=($o==$this->ch_club?"<strong>$t</strong>":"<a href=\"".$this->pagename.$inlink["meta"].$inlink["order"].$inlink["type"].($o=="all"?"":".club-".$o).".".$this->ext."\">$t</a>")?></li><?}?>
</ul>
</p>
<p><?=self::$locale["add.navigation"]?>:
<ul>
<?if($this->page_next){?><li><a href="<?=$this->page_next?>"><?=self::$locale["nav.next"]?></a></li><?}
  if($this->page_prev){?><li><a href="<?=$this->page_prev?>"><?=self::$locale["nav.prev"]?></a></li><?}?>
  <li><a href="<?=mr::host("libro")?>/list.xml"><?=self::$locale["nav.whole"]?></a></li>
  <li><a href="<?=mr::host("libro")?>/secs.xml"><?=self::$locale["nav.secs"]?></a></li>
  <li><a href="resp.xml"><?=self::$locale["nav.resp"]?></a></li>
  <li><a href="<?=mr::host("libro")?>/"><?=self::$locale["nav.lib"]?></a></li>
</ul></p>
<?
	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
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