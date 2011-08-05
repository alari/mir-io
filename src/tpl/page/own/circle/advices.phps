<?php class tpl_page_own_circle_advices extends tpl_page implements i_tpl_page_rightcol, i_tpl_page_ico {
	
	private $ch_meta="all", $ch_type="all", $ch_order="time", $ch_club="all", $pagename="advices", $ext="xml",
		$av_types = array( "prose"=>"", "stihi"=>"", "article"=>"", "all"=>"" ),
		$av_metas = null, $inlink = array(), $page_next = null, $page_prev = null,
		$perpage = 30, $section = 0;
			
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	
 	foreach($this->av_types as $k=>&$v) $v = ws_libro_pub::getType($k);
 	
 	if(@$params["meta"] && ws_comm::byName( $params["meta"] )->id())
 		$this->ch_meta = $params["meta"];
 		
 	if(@$params["type"] && $this->av_types[$params["type"]])
 		$this->ch_type = $params["type"];
 		
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
 		"type"=> $this->ch_type=="all"?"":".type-".$this->ch_type
 	);
 		
 	$page = (int)$params["page"];
 	
 	$where = "";
 	$order = "";
 	
 	switch( $this->ch_meta )
 	{
 		case "all":
 			$where = "p.meta IN (".join(",", $this->av_metas->ids()).")";
 		break;
 		
 		default:
 			$where = "p.meta=".ws_comm::byName( $this->ch_meta )->id();
 	}
 	
 	switch( $this->ch_type )
 	{
 		case "all": break;
 		
 		default:
 			$where .= " AND p.type='".$this->ch_type."'";
 	}
 	
 	$order = "a.time DESC";
 		 	 	 	
 	$this->title = "Рекомендовано Кругом Чтения";
 	
 	$pagehref = $this->pagename.$this->inlink["meta"].$this->inlink["type"];
 	
 	//начали вывод
 	ob_start();
 	
 ?>
 <h1><?=$this->title?></h1>
 <h2><?="Произведения"." ".($this->ch_meta!="all"?ws_comm::byName($this->ch_meta)->link():"")?></h2>
 	<br />
 <?
 	$page = (int)@$params["page"];

 	$r = mr_sql::query("SELECT SQL_CALC_FOUND_ROWS DISTINCT p.id
 			FROM mr_publications p
 			LEFT JOIN mr_pub_advices a
 			ON a.pub_id=p.id
 			WHERE
 				EXISTS (SELECT bc.id FROM mr_user_circle bc WHERE bc.owner=".ws_self::id()." AND bc.follow_advices='yes' AND bc.target=a.user_id)
 			AND ($where)
 			AND p.author != ".ws_self::id()."
 			ORDER BY $order
 			LIMIT ".$this->perpage*$page.", ".$this->perpage);
 	$count = mr_sql::found_rows();
 	
 	$ids = array();
 	while($id = mr_sql::fetch($r, mr_sql::get)) $ids[] = $id;
 	
 	$anonces = ws_libro_pub::several_query("SELECT * FROM mr_publications WHERE id IN (".join(",", $ids).")", $tmp, true);
 		
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
?>
<p>Доминанта:<br />
<ul>
<?foreach($this->av_metas as $m){?><li><?=($m->name==$this->ch_meta ? "<strong>".$m->title."</strong>" : "<a href=\"".$this->pagename.".meta-".$m->name.$inlink["order"].$inlink["type"].$inlink["club"].".".$this->ext."\">".$m->title."</a>")?></li><?}?>
<li><?=($this->ch_meta=="all"?"<strong>Все</strong>":"<a href=\"".$this->pagename.$inlink["order"].$inlink["type"].$inlink["club"].".".$this->ext."\">Все</a>")?></li>
</ul>
</p>
<?if(!$this->section || $this->section->type == "old"){?>
<p>Тип произведения:<br />
<ul>
<?foreach($this->av_types as $o=>$t){?><li><?=($o==$this->ch_type?"<strong>$t</strong>":"<a href=\"".$this->pagename.$inlink["meta"].$inlink["order"].($o=="all"?"":".type-".$o).$inlink["club"].".".$this->ext."\">$t</a>")?></li><?}?>
</ul>
</p>
<?}?>
<p>Навигация:
<ul>
  <li><a href="<?=mr::host("libro")?>/list.xml">Все произведения</a></li>
  <li><a href="<?=mr::host("libro")?>/secs.xml">Тематические разделы</a></li>
  <li><a href="<?=mr::host("libro")?>/">Литобъединение Мира Ио</a></li>
</ul></p>
<?
	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
 
 public function p_ico()
 {
 	return "libro";
 }
	
	}
?>