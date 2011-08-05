<?php class tpl_page_disc_index extends tpl_page implements i_locale, i_tpl_page_rightcol  {

	protected $comms = array();
	protected $keywords = "Дискуссии, форумы, общение, творческие люди, литература, творчество";
	protected $description = "Форумное пространство Мира Ио. Дискуссионные разделы, обсуждение вопросов творчества, произведений, конкурсов и разных инициатив.";

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

 	ob_start();

 	echo "<h1>", "Дискуссии на сайте", "</h1>";


 	$this->handle_discs();

 	$this->content = ob_get_clean();

 	$this->title = "Дискуссии на сайте";

 	$this->css[] = "soc/disc.css";
 }

 protected function handle_discs()
 {
?>

<table class="soc-table">
<?
 	$discs = ws_comm_disc::byComm();

 	$this->comms = array_keys($discs);
 	function cmp_comms_disc($a, $b)
 	{
 		if(!ws_comm::factory($a)->is_sphere("disc")) return 1;
 		if(!ws_comm::factory($b)->is_sphere("disc")) return -1;
 		return 0;
 	}
 	usort($this->comms, "cmp_comms_disc");

 	$i = 0;
 	foreach($this->comms as $comm) if(count($disc=$discs[$comm]))
 	{
 		if( $i%2==0 ) echo "<tr>";

 		$c = ws_comm::factory($comm);


 		echo "<td class=\"soc-discs", ($c->is_sphere("disc")?" soc-key":""), "\">";

 		echo "<span>", $c, "</span>";

 		echo "<ul>";

 		foreach($disc as $d) echo "<li>", $d, "</li>";

 		echo "</ul></td>";

 		if( $i%2 ) echo "</tr>";

 		$i++;
 	}
 	if( $i%2==0 ) echo "<td>&nbsp;</td></tr>";
?>
</table>
<?
 }




 public function col_right()
 {
 	$last_notes = ws_comm_disc_thread::several("comm_id IN (".join(",", $this->comms).")", 12);
 	$last_threads = ws_comm_disc_thread::several("comm_id IN (".join(",", $this->comms).")", 8, 0, "time DESC");

 	ob_start();
?>
<p>Последние сообщения в ветках:<br/>
<ul>
<?foreach($last_notes as $l){?><li><?=$l->last_link()?> - <i><?=$l->last_user()?></i></li><?}?>
</ul>
</p>

<p>Новые ветки:<br/>
<ul>
<?foreach($last_threads as $l){?><li><?=$l?> - <i><?=$l->user()?></i></li><?}?>
</ul>
</p>

<?
 	return ob_get_clean();
 }

}?>