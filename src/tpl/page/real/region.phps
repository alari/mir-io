<?php class tpl_page_real_region extends tpl_page implements i_tpl_page_rightcol, i_locale  {
	
	protected $region;
	
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
 	
 	/* @var $this->country ws_geo_country */
 	$this->region = ws_geo_region::factory($params[1]);
 	
 	ob_start();
 	
 ?>
 <h1><?=$this->region->title?></h1>
 <?if($this->region->description){?><h2><?=$this->region->description?></h2><?}?>
 
  
 <?
 	$cs = $this->region->cities();
 	foreach($cs as $c) echo $c, "<br/>";
 
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->title = $this->region->title;
 }
 
 public function col_right()
 {
 	/* @var $this->region ws_geo_region */
 	
 	$countries = $this->region->countries();
 	
 	$cities = $this->region->cities();
 	
 	$events = ws_comm_event_anonce::several("city IN (".join(",", $cities->ids()).")", 12);
 	
 	$regions = $this->region->regions();
 	
 	ob_start();
 	
 	if($this->region->country)
 	{
?>
	<p>Страна: <?=($this->region->country()->flag()." ".$this->region->country()->link())?></p>
<?
 	}
 	
 	if($this->region->region)
 	{
?>
	<p><?self::rc_print_region($this->region->region)?></p>
<?
 	}
 	
 	if($this->region->maincity)
 	{
?>
	<p>Главный город: <?=$this->region->maincity()?></p>
<?
 	}
 	
 if(count($regions)){?>

 <p>Регионы: <br/>
 <ul>
 	<?foreach($regions as $r){?><li><?=$r?></li><?}?>
 </ul>
 </p>
 
 <?}
 
 if(count($countries)){?>

 <p>Страны: <br/>
 <ul>
 	<?foreach($countries as $r){?><li><?=($r->flag()." ".$r)?></li><?}?>
 </ul>
 </p>
 
 <?}
 	
 if(count($events)){?>
 
 <p>Последние события<?=self::$locale["last_events"]?>:
 	<ul>
 	<?foreach($events as $e){?><li><?=$e?></li><?}?>
 	</ul>
 </p>
 
<?}

?>
<p><a href="/geo/">География проекта<?=self::$locale["geo_page"]?></a></p>
<?

	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
 
 static public function rc_print_region($id)
 {
  $region = ws_geo_region::factory($id);
  echo $region;
  if($region->region)
  {
  	echo "<br/>";
  	self::rc_print_region($region->region);
  }
 }
	
	}
?>