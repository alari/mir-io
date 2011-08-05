<?php class tpl_page_real_evin extends tpl_page implements i_tpl_page_rightcol, i_locale  {
	
	protected $city, $perpage = 20, $pagehref;
	
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
 	
 	$in = $params[1];
 	
 	$this->pagehref = "/evin";
 	
 	if($in)
 	{
 		$this->city = ws_geo_city::byID($in);
 		if(!$this->city->name)
 			throw new ErrorPageException("Город не найден", 404);
 			
 		$this->title = self::$locale["in_city"]." ".$this->city->name.", ".$this->city->country()->name;
 		$this->pagehref = "/in-".$in;
 	} else $this->title = self::$locale["in_cities"];
 	
 	
 	ob_start();
 	
 ?>
 <h1><?=($in?$this->city:self::$locale["in_cities"])?></h1>
 <?if($in){?><h2><?=self::$locale["in_city"]?></h2><?}?>
 	<br />
 <?
 	$page = (int)@$params["page"];
 	$back = $page * $this->perpage;
 
 	$anonces = ws_comm_event_anonce::several($in?"city=".$this->city->id():"city!=0", $this->perpage, $back, "time DESC", $count);
 	
 	$pager = "";
 
 	if($back || $back+count($anonces) < $count)
 	{
 		ob_start();
 		
 		echo "<div class=\"pager\">".ws_pager::title().": ";
 		
 		$pages = ws_pager::arr($page, floor($count/$this->perpage));
 		$prev = 0;
 		
 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";
 				
 			echo '&nbsp; ', $p == $page ? "<b>".($page+1)."</b>" : '<a href="'.($this->pagehref.($p?".page-".$p:"").".xml").'">'.($p+1).'</a>';
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

 	if($this->city instanceof ws_geo_city){
?>
<p><?=self::$locale["country"]?>: <?=$this->city->flag()?>&nbsp;<?=$this->city->country()?></p>
<p><?=self::$locale["city"]?>: <?=$this->city?></p>
<p><a href="evin.xml"><?=self::$locale["in_others"]?></a></p>
<?
 	} else {
$city_ids = mr_sql::query("SELECT DISTINCT city FROM mr_comm_events WHERE city!=0");
$ids = array();
while($id = mr_sql::fetch($city_ids, mr_sql::get)) $ids[] = $id;
$cities = ws_geo_city::several($ids);
?>
<p>
<?=self::$locale["ev_in_cities"]?>:<br />
	 <?foreach($cities as $c){?>&nbsp; &nbsp; <?=$c->flag()?>&nbsp;<?=$c;?><br /><?}?>
</p>
<?
 	}
 	
?>


<p><a href="<?=mr::host("real")?>/events.xml">Все события, связанные с реалом</a></p>
<p><a href="<?=mr::host("real")?>/"><?=self::$locale["geo"]?></a></p>

<?
 	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
	
	}
?>