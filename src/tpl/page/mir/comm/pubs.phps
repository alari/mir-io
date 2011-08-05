<?php class tpl_page_mir_comm_pubs extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_locale  {
	
	private $ch_type="all", $ch_order="time",
		$av_types = array( "prose"=>"", "stihi"=>"", "article"=>"", "all"=>"" ),
		$av_orders = array( "time"=>"", "rating"=>"" ),
		$av_metas = null, $inlink = array(), $page_next = null, $page_prev = null,
		$perpage = 30, $categ = 0, $pagehref;
	
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
 	 	 	
 	// имена сортировок -- из локали
 	foreach($this->av_types as $k=>&$v) $v = self::$locale["type.".$k];
 	foreach($this->av_orders as $k=>&$v) $v = self::$locale["order.".$k];
 	
 	// выбранные сортировки
 	if(@$params["type"] && $this->av_types[$params["type"]])
 		$this->ch_type = $params["type"];
 		
 	if(@$params["order"] && $this->av_orders[$params["order"]])
 		$this->ch_order = $params["order"];
 		
 	$this->inlink = array(
 		"order"=> $this->ch_order=="time"?"":".order-".$this->ch_order,
 		"type"=> $this->ch_type=="all"?"":".type-".$this->ch_type
 	);
 		
 	$page = (int)@$params["page"];
 	
 	$order = "";
 	 	 	 	
 	switch($this->ch_order)
 	{
 		case "time":
 			$order = "p.time DESC";
 		break;
 		
 		case "rating":
 			$order = "p.rating DESC";
 		break;
 	}
 	 
 	
 	// если указана категория, и она существует
 	if(is_numeric(@$params[2]))
 	{
 		
 		$this->categ = ws_comm_pub_categ::factory($params[2]);
 		if( !$this->categ->id() || $this->categ->comm()->id() != $this->comm->id() )
 			throw new ErrorPageException("Категория произведений не найдена.", 404);
 			
 		$title = $this->title = $this->categ->title;
 		$descr = $this->categ->description;
 		$this->title .= " - ".$this->comm->title;
 		
 		$anonces = $this->categ->pubs($this->ch_type=="all"?null:$this->ch_type, $order, $this->perpage*$page, $this->perpage, $count, true);
 		
 		if( count($anonces) == $this->perpage && $count > $this->perpage*$page )
 		{
 			$this->page_next = $this->categ->href( $this->inlink["order"].$this->inlink["type"].".page-".($page+1) );
 		}
 		if($page)
 		{
 			$this->page_prev = $this->categ->href( $this->inlink["order"].$this->inlink["type"].($page>1?".page-".($page-1):"") );
 		}
 		
 		$this->pagehref = substr( $this->categ->href(), 0, -4 );
 		
 	// если отображаются произведения без категории
 	} elseif( @$params[2] == "n" ) {
 		
 		$title = self::$locale["title.n"];
 		$this->title = $title." - ".$this->comm->title;
 		$descr = self::$locale["descr.n"]." ".$this->comm->link();
 		
 		$anonces = ws_comm_pub_anchor::pubs(0, $this->comm->id(), $this->ch_type=="all"?null:$this->ch_type, $order, $this->perpage*$page, $this->perpage, $count, true);
 		
 		if( count($anonces) == $this->perpage && $count > $this->perpage*$page )
 		{
 			$this->page_next = "pubs-n".$this->inlink["order"].$this->inlink["type"].".page-".($page+1).".xml";
 		}
 		if($page)
 		{
 			$this->page_prev = "pubs-n".$this->inlink["order"].$this->inlink["type"].($page>1?".page-".($page-1):"").".xml";
 		}
 		
 		$this->pagehref = "pubs-n";
 		
 	// произведения во всех категориях
 	} else {
 		
 		$title = self::$locale["title.all"];
 		$this->title = $title." - ".$this->comm->title;
 		$descr = self::$locale["descr.all"]." ".$this->comm->link();
 		
 		$anonces = ws_comm_pub_anchor::pubs(null, $this->comm->id(), $this->ch_type=="all"?null:$this->ch_type, $order, $this->perpage*$page, $this->perpage, $count, true);
 		
 		if( count($anonces) == $this->perpage && $count > $this->perpage*$page )
 		{
 			$this->page_next = "pubs".$this->inlink["order"].$this->inlink["type"].".page-".($page+1).".xml";
 		}
 		if($page)
 		{
 			$this->page_prev = "pubs".$this->inlink["order"].$this->inlink["type"].($page>1?".page-".($page-1):"").".xml";
 		}
 		
 		$this->pagehref = "pubs";
 		
 	}
 		
 	ob_start();
 	
 ?>
 <h1><?=$title.($this->ch_type!="all" ? ", ".$this->av_types[$this->ch_type] : "")?></h1>
 <h2><?=$descr?></h2>
 	<br />
 <?
 	tpl_fr_pubs::outlist($anonces, true, $page*$this->perpage+1);
 	
 	if($this->page_next || $this->page_prev)
 	{
?>
	<div class="pager">
		<?if($this->page_prev){?><a href="<?=$this->page_prev?>"><?=sprintf(self::$locale["back"], $this->perpage)?></a><?}?>
		
		<?if($this->page_next){?><a href="<?=$this->page_next?>"><?=sprintf(self::$locale["front"], $this->perpage)?></a><?}?>
	</div>
<?
 	}
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "pub/anonce.css";
 }
 
 public function col_right()
 {
 	
 	ob_start();
 	
 	$inlink = &$this->inlink;

 	?>
<p><?=self::$locale["comm"]?>: <?=$this->comm?></p>
<?
	$categs = ws_comm_pub_categ::several( $this->comm->id(), true );
	
	if($this->categ)
	{
		$discs = ws_comm_disc_thread::several("category=".$this->categ->id());
		if(count($discs))
		{
?>
<p>Связанные дискуссии:<br/>
<ul>
<?foreach($discs as $d) echo "<li>", $d, "</li>";?>
</ul>
</p>
<?
		}
	}
	
?>
<p><?=self::$locale["comm.categs"]?>:<br />
<ul>
<?foreach($categs as $c){?>
<li><?=($this->categ && $this->categ->id()==$c->id() ? "<b>".$c->link()."</b>" : $c)?></li>
<?}?>
<li><a href="pubs-n.xml"><?=($this->pagehref == "pubs-n" ? "<b>".self::$locale["title.n"]."</b>" : self::$locale["title.n"])?></a></li>
<li><a href="pubs.xml"><?=($this->pagehref == "pubs" ? "<b>".self::$locale["title.all"]."</b>" : self::$locale["title.all"])?></a></li>
</ul>
</p>
<p><?=self::$locale["add.order"]?>:<br />
<ul>
<?foreach($this->av_orders as $o=>$t){?><li><?=($o==$this->ch_order?"<strong>$t</strong>":"<a href=\"".$this->pagehref.($o=="time"?"":".order-".$o).$inlink["type"].".xml\">$t</a>")?></li><?}?>
</ul>
</p>
<?if(!$this->section || $this->section->type == "old"){?>
<p><?=self::$locale["add.pub_types"]?>:<br />
<ul>
<?foreach($this->av_types as $o=>$t){?><li><?=($o==$this->ch_type?"<strong>$t</strong>":"<a href=\"".$this->pagehref.$inlink["order"].($o=="all"?"":".type-".$o).".xml\">$t</a>")?></li><?}?>
</ul>
</p>
<?}?>
<p><?=self::$locale["add.navigation"]?>:
<ul>
<?if($this->page_next){?><li><a href="<?=$this->page_next?>"><?=self::$locale["nav.next"]?></a></li><?}
  if($this->page_prev){?><li><a href="<?=$this->page_prev?>"><?=self::$locale["nav.prev"]?></a></li><?}?>
  <li><a href="<?=mr::host("libro")?>/"><?=self::$locale["nav.whole"]?></a></li>
  <li><a href="<?=mr::host("libro")?>/secs.xml"><?=self::$locale["nav.secs"]?></a></li>
</ul></p>
<?
	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
	
	}
?>