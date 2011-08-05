<?php class tpl_page_real_city extends tpl_page implements i_tpl_page_rightcol, i_locale  {
	
	protected $city;
	
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
 	
 	/* @var $this->city ws_geo_city */
 	$id = (int)$params[2];
 	if(!$id) $id = $params[3];
 	
 	$this->city = ws_geo_city::byID($id);
 	if(!$this->city->name)
 		throw new ErrorPageException("Город не найден", 404);
 	
 	/* @var $map ws_geo_map */
 	$map = $this->city->map("citymap");
 		
 	ob_start();
 	
 ?>
 <h1><?=$this->city->name?></h1>
 
 <center>
 
 <?if($map){
 	
 	$map->setMarkersXml("/xml.php5");
 	$map->setZoom(6);
 	$map->setFunctionName("gmapShow");
 	$this->head .= ws_geo_map::jsInject();
 	
 	?>
 
 <div id="citymap">
  <a href="javascript:void(0)" onclick="javascript:$('citymap').style.height='400px';gmapShow();">Показать город на карте</a>
 </div>
 
 <?=$map?>
 
 <?}?>
 
 <table cellpadding="10">
 <tr>
 	<?if($this->city->pic_src || $this->city->yandex_id){?>
 		<th>
 			<?if($this->city->pic_src){?><img src="<?=mr::host("static")?>/<?=$this->city->pic_src?>" alt="<?=$this->city->name?>, <?=$this->city->country()->name?>" /><?}?>
 			
 				<br/><br/><br/>
 				
 			<?if($this->city->yandex_id){?><a target="_blank" href="http://www.yandex.ru/redir?dtype=stred&pid=7&cid=1228&url=http://weather.yandex.ru/index.xml?city=<?=$this->city->yandex_id?>"><img src="http://info.weather.yandex.net/informer/175x114/<?=$this->city->yandex_id?>.png" border="0" alt="Яндекс.Погода"/><img width="1" height="1" src="http://www.yandex.ru/redir?dtype=stred&pid=7&cid=1227&url=http://img.yandex.ru/i/pix.gif" alt="" border="0"/></a><?}?>
 			
 		</th>
 	<?}?>
 	<td align="center">
 		<strong><?=self::$locale["users"]?>:</strong>
 		<div id="city-usrs" style="height:380px;overflow:auto;padding:0 4em;width:350px;text-align:left;">
 	<?
 		$usrs = ws_user::several("city='".$this->city->name."' AND activity>1 ORDER BY activity DESC");
 		if(count($usrs))
 		{
 			foreach($usrs as $u) echo $u, "<br />";
 			echo "<small>(".self::$locale["only_active"].")</small></div>";
 		}
 		else echo "<i>".self::$locale["no_active"]."</i>";
	?>
		<br /><br />
		<?if($this->city->u_active + $this->city->u_alive + $this->city->u_passive >0){?><a href="javascript:void(0)" onclick="javascript:mr_Ajax({url:'/x/ajax-geo/usrs', update:$('city-usrs'),data:{city:<?=$this->city->id()?>}}).send()"><?=self::$locale["show_all"]?></a><?}?>
		</div>
 	</td>
 </tr>
 </table>
 </center>
 
 <?
 
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->title = self::$locale["city"]." ".$this->city->name.", ".$this->city->country()->name;
 }
 
 public function col_right()
 {
 	/* @var $this->city ws_geo_city */
 	
 	$events = ws_comm_event_anonce::several("city=".$this->city->id(), 12);
 	
 	ob_start();
?>
<p><?=self::$locale["country"]?>: <?=$this->city->flag()?>&nbsp;<?=$this->city->country()?></p>
<?if($this->city->region){?>
<p><?self::rc_print_region($this->city->region)?></p>
<?}

 if(count($events)){?>
 
 <p><?=sprintf(self::$locale["last_events"], '<a href="in-'.($this->city->code?$this->city->code:$this->city->id()).'.xml">', '</a>')?>:
 	<ul>
 	<?foreach($events as $e){?><li><?=$e?></li><?}?>
 	</ul>
 </p>
 
<?}?>

<p><a href="evin.xml"><?=self::$locale["all_events"]?></a></p>
<p><a href="<?=mr::host("real")?>/"><?=self::$locale["geo"]?></a></p>

<?

	if(ws_self::is_allowed("geo")) echo '<p><a href="manage.xml">'.self::$locale["manage"].'</a></p>';

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