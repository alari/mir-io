<?php class tpl_page_mir_comm_resp extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_tpl_page_submenu {
	
	protected $perpage=20, $page=0, $m;
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "rightcol";
 	
 	$this->page = (int)$params["page"];
 	 		
 	$this->m = $m = $this->comm->type == "meta" || $this->comm->type == "closed";
 	
 	$this->title = "Отзывы в ".($m?"доминанте":"сообществе")." ".$this->comm->title;

 	ob_start();
 	
 ?>
 <h1>Отзывы на произведения</h1>
 <h2><?=($m?"Доминанта":"Сообщество")?>: <?=$this->comm?></h2>
 
 	<br />
	
 <?
 
 	if($m)
 	{
 		$resps = ws_libro_pub_resp::several_query("SELECT SQL_CALC_FOUND_ROWS r.* FROM ".ws_libro_pub_resp::sqlTable." r LEFT JOIN mr_publications p ON p.id=r.pub_id WHERE p.meta=".$this->comm->id()." ORDER BY r.time DESC LIMIT ".($this->page*$this->perpage).", ".$this->perpage, $count);
 	} else {
 		$resps = ws_libro_pub_resp::several_query("SELECT SQL_CALC_FOUND_ROWS r.* FROM ".ws_libro_pub_resp::sqlTable." r LEFT JOIN mr_comm_pubs c ON c.pub_id=r.pub_id WHERE c.comm_id=".$this->comm->id()." ORDER BY r.time DESC LIMIT ".($this->page*$this->perpage).", ".$this->perpage, $count);
 	}
 	
 	$pager = "";
 	
 	if($this->page*$this->perpage+count($resps) < $count || $page)
 	{
 	
 		ob_start();
 		
 		echo "<div class=\"pager\">".ws_pager::title().": ";
 		
 		$pages = ws_pager::arr($this->page, floor($count/$this->perpage)-1);
 		$prev = 0;
 		
 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";
 				
 			echo '&nbsp; ', $p == $this->page ? "<b>".($this->page+1)."</b>" : '<a href="resp'.($p?".page-".($p):"").'.xml">'.($p+1).'</a>';
 			$prev = $p;
 		}
 			
 		echo "</div>";
 		
 		$pager = ob_get_flush();
 	}
 	
 	tpl_fr_comment::outlist( $resps, true );
 	
 	echo $pager;
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "comment.css";
 	$this->head .= "<script src=\"/style/js/comment.js\" type=\"text/javascript\"></script>";
 }
 
 public function col_right()
 {
 	ob_start();
?>
<p><?=($this->m?"Доминанта":"Сообщество")?>: <?=$this->comm?></p>
<p><a href="<?=($m?mr::host("libro")."/list.meta-".$this->comm->name.".xml":"pubs.xml")?>">Произведения <?=($m?"доминанты":"сообщества")?></a></p>
<p><a href="<?=mr::host("libro")?>/list.xml">Произведения сайта</a></p>
<p><a href="<?=mr::host("libro")?>/resp.xml">Все отзывы на сайте</a></p>
<?
	return ob_get_clean();
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