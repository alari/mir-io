<?php class tpl_page_real_country extends tpl_page implements i_tpl_page_rightcol, i_locale  {
	
	protected $country;
	
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
 	$this->country = ws_geo_country::factory($params[1]);
 	
 	$regions = $this->country->regions();
 	
 	ob_start();
 	
 ?>
 <h1><?=($this->country->fullname?$this->country->fullname:$this->country->name)?></h1>
 
 <h2><?=self::$locale["cities_on_project"]?></h2>
 
 <center>
 
 <?if($this->country->maincity){?><strong>Столица: <?=$this->country->maincity()?></strong><?}?>
 
 <table cellpadding="10">
 	<tr>
 		<td width="40%" align="center">
 			<strong><?=self::$locale["whole_cities"]?></strong>
 <div style="height: 360px; overflow: auto; width: 200px; text-align:left;">
 <ul>
 	<?$cities = $this->country->cities();foreach($cities as $c){?><li><?=$c?></li><?}?>
 </ul>
 </div>	
 		</td>
 		
 		<td align="right">
 		
<strong><?=self::$locale["active_cities"]?>:</strong>
	<br />
<?$active = ws_geo_city::several("country='".$this->country->code()."' AND u_active>0", "u_active DESC LIMIT 10");
	if(!count($active)) echo "<i>".self::$locale["no_activity"]."</i><br />";
	else {
	foreach($active as $c) echo $c, " <i>($c->u_active)</i><br />";	
	?>
	
<br /><br />

<strong><?=self::$locale["active_users"]?>:</strong>
	<br />
<?
	$r = mr_sql::qw("SELECT u.id FROM mr_users u LEFT JOIN mr_geo_cities c ON c.name=u.city WHERE c.country=? AND u.activity>1 ORDER BY u.activity DESC LIMIT 10", $this->country->code());
	$uids = array();
	while($uid = mr_sql::fetch($r, mr_sql::get))
		$uids[] = $uid;
	ws_user::several($uids);
	$list = new mr_list("ws_user", $uids);
	foreach($list as $u) echo $u, " <i>($u->activity)</i><br />";
		}
?>
 		
 		</td>
 	</tr>
 </table>
 
 <?if(count($regions)){?>
 
 Регионы:
 <ul>
 	<?foreach($regions as $r){?><li><?=$r?></li><?}?>
 </ul>
 
 <?}?>
 
 </center>
 
 <?
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->title = self::$locale["country"]." ".$this->country->name;
 }
 
 public function col_right()
 {
 	/* @var $this->country ws_geo_country */
 	
 	$cities = $this->country->cities();
 	
 	$events = ws_comm_event_anonce::several("city IN (".join(",", $cities->ids()).")", 12);
 	
 	ob_start();
 	
 if(count($events)){?>
 
 <p><?=self::$locale["last_events"]?>:
 	<ul>
 	<?foreach($events as $e){?><li><?=$e?></li><?}?>
 	</ul>
 </p>
 
<?}

?>
<p><a href="<?=mr::host("real")?>/">География проекта<?=self::$locale["geo_page"]?></a></p>
<?

	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
	
	}
?>