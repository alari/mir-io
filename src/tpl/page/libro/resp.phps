<?php class tpl_page_libro_resp extends tpl_page implements i_tpl_page_rightcol, i_locale, i_tpl_page_submenu   {
	
	static protected $locale = array(), $lang = "";
	
	protected $perpage=20, $page=0, $next, $prev;
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->page = (int)$params["page"];
 	
 	$this->layout = "rightcol";
 		
 	$this->title = self::$locale["title"];

 	ob_start();
 	
 ?>
 <h1><?=self::$locale["title"]?></h1>
 <h2><?=self::$locale["descr"]?></h2>
 
 	<br />
	
 <?
 
 	$resps = ws_libro_pub_resp::several("1=1", $this->perpage, $this->page*$this->perpage, "time DESC", $count);
 
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
 	$metas
?>
<p>
<ul>
<li><a href="resp.xml"><?=self::$locale["nav.resp"]?></a></li>
<li><a href="secs.xml"><?=self::$locale["nav.secs"]?></a></li>
<li><a href="list.xml"><?=self::$locale["nav.whole"]?></a></li>
<li><a href="."><?=self::$locale["nav.lib"]?></a></li>
</ul>
</p>

<?

	$metas = ws_comm::several("FIND_IN_SET('libro', org_sphere)>0 AND type IN ('meta', 'closed')");
?>
<p>Отзывы в доминантах:</p>
<?foreach($metas as $m) echo "&nbsp;&nbsp;&nbsp;", $m->link(null, "resp.xml"), "<br/>";

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