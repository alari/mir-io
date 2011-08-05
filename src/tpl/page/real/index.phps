<?php class tpl_page_real_index extends tpl_page implements i_tpl_page_rightcol, i_locale  {

	protected $country;
	protected $keywords = "Творчество, литература, литературный клуб, читать, я пишу, современные авторы, литературный конкурс, рецензии, критика, статьи, стихи, проза, эссе, авторы, творческие люди";
	protected $description = "Найдите творческих и интересных людей в Вашем городе, области, крае.";

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


 	$ev_r = mr_sql::qw("SELECT DISTINCT city FROM mr_comm_events WHERE city!=0");
 	$ev_codes = array();
 	while($c = mr_sql::fetch($ev_r, mr_sql::get)) $ev_codes[] = $c;

 	$ev_cities = ws_geo_city::several($ev_codes);

 	$active = ws_geo_city::several("1=1", "u_active DESC LIMIT 10");

 	$total = ws_geo_city::several("1=1", "u_active+u_passive+u_alive DESC LIMIT 10");

 	$complete = ws_geo_city::several("1=1");



 	ob_start();

 ?>
 <h1><?=self::$locale["caption"]?></h1>

 <h2><?=self::$locale["descr"]?></h2>

 <div class="geo-section">
 	<strong><?=sprintf(self::$locale["city.with_events"], '<a href="events.xml">', '</a>')?></strong>
 		<br />
 	<?foreach($ev_cities as $c) echo "&nbsp; &nbsp;", $c->flag(), " ", $c, "<br />";?>
 	<em>(<?=sprintf(self::$locale["city.events"], '<a href="events.xml">', '</a>')?>)</em>
 </div>

 <div align="right" class="geo-section">
 	<strong><?=self::$locale["city.active"]?></strong>
 		<br />
 	<?foreach($active as $c) echo $c, " ($c->u_active)", " ", $c->flag(), "<br />";?>
 </div>

 <div class="geo-section">
 	<strong><?=self::$locale["city.huge"]?></strong>
 		<br />
 	<?foreach($total as $c) echo "&nbsp; &nbsp;", $c->flag(), " ", $c, " (".($c->u_active+$c->u_passive+$c->u_alive).")<br />";?>
 </div>

 <div align="center" class="geo-section">
 	<?=self::$locale["city.total"]?>: <b><?=count($complete)?></b>

 		<br /><br />

  	<form onsubmit="javascript:if(this.city.value!=0) window.location.href = '/geo/'+this.city.value+'.xml';return false;">
  		<?=self::$locale["city.pages"]?>:<br /><?=ws_geo_city::form_select("city", "id")?>
  			<br />
  		<input type="submit" value="<?=self::$locale["city.go"]?>" />
  	</form>

 </div>

 <?
 	$this->content = ob_get_contents();
 	ob_end_clean();

 	$this->title = self::$locale["title"];
 	$this->head .= <<<T
<style type="text/css">
	div.geo-section {padding: 0 50px;}
</style>
T;
 }

 public function col_right()
 {
 	$r = mr_sql::query("SELECT DISTINCT country, COUNT(country) AS c FROM mr_geo_cities GROUP BY country ORDER BY c DESC, country");
 	$codes = array();
 	$cs = array();
 	while($c = mr_sql::fetch($r, mr_sql::obj))
 	{
 		if($c->c <= 1) continue;
 		$codes[] = $c->country;
 		$cs[ strtolower($c->country) ] = $c->c;
 	}

 	ws_geo_country::several($codes);
 	$countries = new mr_list("ws_geo_country", $codes);

 	$comms = ws_comm::several("FIND_IN_SET('real', org_sphere)>0");

 	ob_start();

?>
<p>
<?=self::$locale["countries"]?>:<br />
<?foreach($countries as $c){
	echo "&nbsp; &nbsp;", $c->flag(), " ", $c, " (".$cs[$c->code()].")<br />";
}?>
(<?=self::$locale["country.cities"]?>)
</p>

<p>Связанные сообщества:</p>
<?foreach($comms as $c) echo "&nbsp;&nbsp;&nbsp;", $c, "<br/>";?>

<p><a href="events.xml">Все события, связанные с реалом</a></p>
<p><a href="evin.xml">Все события в городах</a></p>

<?


	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }

	}
?>