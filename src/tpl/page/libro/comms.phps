<?php class tpl_page_libro_comms extends tpl_page implements i_tpl_page_rightcol, i_tpl_page_submenu   {
	
	protected $comms, $mode, $sphere="libro", $direct;
		
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	 	 	
 	if($params[1]) $this->direct = $params[1];
 	
 	$this->comms = ws_comm::several("FIND_IN_SET('$this->sphere', org_sphere)>0".($this->direct?" AND FIND_IN_SET('$this->direct', org_direct)>0":""));
 
 	ob_start();
 	
 	echo "<h1>", "Сообщества Литературной Сферы", "</h1>";
 	$this->title = "Сообщества Литературной Сферы".($this->direct?", ".ws_comm::$org_directs[$this->direct]:"");
 	if($this->direct) echo "<h2>Направление: ".ws_comm::$org_directs[$this->direct]."</h2>";
 	
 	tpl_fr_comms::outlist($this->comms);
 	
 	$this->content = ob_get_clean();
 	
 	$this->css[] = "comms.css";
 }
 
 public function col_right()
 {
 	ob_start();
 	
 	$ev = ws_comm_event_anonce::several("comm_id IN (".join(",", $this->comms->ids()).")", 12);
 	
 	
 	
 	
?>

<p><a href="<?=($this->direct?$this->direct:"events")?>.xml">Последние события</a>:<br/>
<ul>
<?foreach($ev as $e){?><li><?=$e?></li><?}?>
</ul>
</p>

<p>Другие направления сферы:<br/>
<ul>
<?foreach(ws_comm::$org_directs as $k=>$v) echo "<li><a href=\"-$k.xml\">$v</a></li>";?>
</ul>
</p>

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
}?>