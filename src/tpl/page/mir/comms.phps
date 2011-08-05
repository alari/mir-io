<?php class tpl_page_mir_comms extends tpl_page implements i_locale, i_tpl_page_rightcol  {
	
	protected $comms, $mode, $sphere, $direct;
	
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
 	 	
 	if(isset(ws_comm::$org_sphere[$params[1]]))
 		$sphere = $params[1];
 	 	
 	$where = "1=1";
 	if($sphere)
 		$where = "FIND_IN_SET('$sphere', org_sphere)>0";
 	
 	$this->comms = ws_comm::several($where);
 	
 	ob_start();
 	
 	echo "<h1>", "Сообщества на сайте", "</h1>";
 	echo "<h2><a href=\"/init.xml\">", "Направленная инициатива", "</a></h2>";
 	
 	tpl_fr_comms::outlist($this->comms);
 	
 	$this->content = ob_get_clean();
 	
 	$this->css[] = "init/comms.css";
 }
 
 public function col_right()
 {
 	ob_start();
 	
 	$ev = ws_comm_event_anonce::several("comm_id IN (".join(",", $this->comms->ids()).")", 12);
 	
 	
 	
 	
?>

<p><?=self::$locale["nav.last_ev"]?>:<br/>
<ul>
<?foreach($ev as $e){?><li><?=$e?></li><?}?>
</ul>
</p>

<p><?=self::$locale["nav.other"]?>:<br/>
<ul>
<?foreach(ws_comm::$org_spheres as $k=>$v) echo "<li><a href=\"$k.xml\">$v</a></li>";?>
</ul>
</p>

<?
 	
 	return ob_get_clean();
 }
	
}?>