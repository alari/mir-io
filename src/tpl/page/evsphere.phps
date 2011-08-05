<?php abstract class tpl_page_evsphere extends tpl_page implements i_tpl_page_rightcol {
	
	protected $direct, $sphere, $perpage=20, $page_title, $descr;
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	
 	$this->page = (int)$params["page"];
 	
 	$this->direct = $params[1];
 	if($this->direct == "events")
 		$this->direct = 0;
 	
 	$fields = "e.".str_replace(", ", ", e.", ws_comm_event_anonce::fields);
	$anonces = ws_comm_event_anonce::several_query("SELECT SQL_CALC_FOUND_ROWS $fields FROM ".ws_comm_event_anonce::sqlTable." e LEFT JOIN ".ws_comm_event_sec::sqlTable." s ON e.section=s.id WHERE e.hidden='no'".($this->direct?" AND FIND_IN_SET('$this->direct', s.org_direct)>0":"")." AND FIND_IN_SET('$this->sphere', s.org_sphere)>0 ORDER BY e.time DESC LIMIT ".($this->perpage*$this->page).", ".$this->perpage, $count);
 	
 	$href =& $this->direct;
 	
 	ob_start();
 	
 ?>
 <h1><?=$this->title?></h1>
 <h2><?=$this->descr?></h2>
 	<br />
 <?
 
 	$pager = "";
 
 	if($this->page || $this->page*$this->perpage+count($anonces) < $count)
 	{
 		ob_start();
 		
 		echo "<div class=\"pager\">".ws_pager::title().": ";
 		
 		$pages = ws_pager::arr($this->page, floor(($count-1)/$this->perpage));
 		$prev = 0;
 		
 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";
 				
 			echo '&nbsp; ', $p == $this->page ? "<b>".($this->page+1)."</b>" : '<a href="'.($href.($p?".page-".$p:"").".xml").'">'.($p+1).'</a>';
 			$prev = $p;
 		}
 			
 		echo "</div>";
 		
 		$pager = ob_get_flush();
 	}
 
 	tpl_fr_events::outlist($anonces);
 	
 	echo $pager;
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "event/anonce.css";
 }
 
 public function col_right()
 {
 	ob_start();
 	
 	$comms = ws_comm::several("FIND_IN_SET('$this->sphere', org_sphere)>0".($this->direct?" AND FIND_IN_SET('$this->direct', org_direct)>0":""));
 	
?>
<p>Другие направления:
<ul>
<?foreach(ws_comm::$org_directs as $o=>$d){?>
	<li><?if($o==$this->direct) echo "<b>"?><a href="<?=$o?>.xml"><?=$d?></a><?if($o==$this->direct) echo "</b>"?></li>
<?}?>
</ul>
</p>
<p><a href="events.xml">Все события сферы</a></p>
<?if(count($comms)){?>
<p><a href="<?=($this->direct?"-".$this->direct:"comms")?>.xml">Сообщества <?=($this->direct?"направления":"сферы")?></a>:</p>
<?foreach($comms as $c) echo "&nbsp;&nbsp;&nbsp;", $c, "<br/>";
}
 	
 	
	return ob_get_clean();
 }
	
	}
?>