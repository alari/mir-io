<?php class tpl_page_cols_index extends tpl_page implements i_locale, i_tpl_page_rightcol  {

	protected $comms, $secs;
	protected $keywords = "Тематические колонки, интересно, читать, современные авторы, литература, конкурсы, творчество, слово, сообщества";
	protected $description = "Тематические колонки, в которых авторы или сообщества авторов рассказывают о проводящихся конкурсах, о кино, литературе, живописи и вообще обо всём, что их интересует.";

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

 	$this->comms = ws_comm::several("FIND_IN_SET('cols', org_sphere)>0");
 	$this->secs = ws_comm_event_sec::several("strong='yes' AND comm_id IN (".join(",", $this->comms->ids()).")");

 	ob_start();

 	echo "<h1>", "Колонки", "</h1>";
 	echo "<h2>", "Тематические статьи или подборки статей", "</h2>";

 	$secs = array();
 	foreach($this->secs as $s) $secs[ $s->comm()->id() ][] = $s;

?>

<center>
<table class="soc-table">
<?
	$i = 0;
	foreach($this->comms as $c) if(isset($secs[$c->id()]) && count($sc=$secs[$c->id()]))
 	{
 		if( $i%2==0 ) echo "<tr>";

 		echo "<td class=\"soc-discs\">";

 		echo "<span>", $c, "</span>";

 		echo "<ul>";

 		foreach($sc as $s)
 		{
 			echo "<li>", $s->apply=="column"?"<em>":"", $s, $s->apply=="column"?"</em>":"";
 			if($s->apply=="column" && $s->owner) echo " - <small>", ws_user::factory($s->owner), "</small>";
 			echo "</li>";
 		}

 		echo "</ul></td>";

 		if( $i%2 ) echo "</tr>";

 		$i++;
 	}
 	if( $i%2==0 ) echo "<td>&nbsp;</td></tr>";
?>

</table>
</center>

<?
 	$this->content = ob_get_clean();

 	$this->title = "Авторские колонки - тематические статьи или подборки статей";

 	$this->css[] = "soc/disc.css";
 }

 public function col_right()
 {
 	ob_start();

 	$ev = ws_comm_event_anonce::several("section IN (".join(",", $this->secs->ids()).")", 12);

?>

<p>Последние записи в колонках:<br/>
<ul>
<?foreach($ev as $e){?><li><?=$e?></li><?}?>
</ul>
</p>
<?

 	return ob_get_clean();
 }

}?>