<?php class tpl_page_mir_list extends tpl_page implements i_locale, i_tpl_page_rightcol  {
	
	protected $comms, $sphere, $direct;
	
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
 	
 	$direct = "";
 	 	
 	if(isset(ws_comm::$org_directs[$params[1]]))
 		$direct = $params[1];
 	 	
 	$where = "1=1";
 	if($direct)
 		$where = "FIND_IN_SET('$direct', org_direct)>0";
 	
 	$this->comms = ws_comm::several($where);
 	 	
 	ob_start();
 	
 	echo "<h1>", "Направление", ": ", ws_comm::$org_directs[$direct], "</h1>";
 	echo "<h2><a href=\"/list.xml\">", "Сообщества Мира<sup>Ио</sup>", "</a></h2>";
 	
 	tpl_fr_comms::outlist($this->comms);
 	
 	$this->content = ob_get_clean();

 	$this->title = self::$locale["default.title"]. ": ". ws_comm::$org_directs[$direct];
 	
 	$this->css[] = "comms.css";
 }
 
 public function col_right()
 {
 	ob_start();
 	
 	$ev = ws_comm_event_anonce::several("comm_id IN (".join(",", $this->comms->ids()).")", 12);
?>

<p>Последние события:<br/>
<ul>
<?foreach($ev as $e){?><li><?=$e?></li><?}?>
</ul>
</p>

<p>Другие направления:<br/>
<ul>
<?foreach(ws_comm::$org_directs as $k=>$v) echo "<li><a href=\"$k.xml\">$v</a></li>";?>
</ul>
</p>

<?
 	
 	return ob_get_clean();
 }
	
}?>