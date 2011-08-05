<?php class tpl_page_events_list extends tpl_page implements i_tpl_page_rightcol {
	
	protected $direct, $perpage=20, $page;
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	
 	$this->page = (int)$params["page"];
 	
 	$this->direct = $params[1];
 	if($this->direct == "list")
 	{
 		$anonces = ws_comm_event_anonce::several("hidden='no'", $this->perpage, $this->page*$this->perpage, "time DESC", $count);
 		$title = "Последние события на сайте";
 		$descr = "Отображаются все события";
 	} else {
	 	$fields = "e.".str_replace(", ", ", e.", ws_comm_event_anonce::fields);
	 	$anonces = ws_comm_event_anonce::several_query("SELECT SQL_CALC_FOUND_ROWS $fields FROM ".ws_comm_event_anonce::sqlTable." e LEFT JOIN ".ws_comm_event_sec::sqlTable." s ON e.section=s.id WHERE e.hidden='no' AND FIND_IN_SET('$this->direct', s.org_direct)>0 ORDER BY e.time DESC LIMIT ".($this->perpage*$this->page).", ".$this->perpage, $count);
	 	$title = "События в направлении: ".ws_comm::$org_directs[$this->direct];
	 	$descr = "Отображаются события из колонок с подходящей меткой";
 	}
 	
 	$href =& $this->direct;
 	
 	$this->title = $title;
 	
 	ob_start();
 	
 ?>
 <h1><?=$title?></h1>
 <h2><?=$descr?></h2>
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
?>
<p>Другие направления:
<ul>
<?foreach(ws_comm::$org_directs as $o=>$d){?>
	<li><?if($o==$this->direct) echo "<b>"?><a href="<?=$o?>.xml"><?=$d?></a><?if($o==$this->direct) echo "</b>"?></li>
<?}?>
</ul>
</p>
<p><a href="list.xml">Все события</a></p>
<p><a href=".">Сообщества и разделы</a></p>
<?
 	
 	
	return ob_get_clean();
 }
	
	}
?>