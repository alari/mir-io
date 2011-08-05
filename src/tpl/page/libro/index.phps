<?php class tpl_page_libro_index extends tpl_page implements i_locale, i_tpl_page_rightcol, i_tpl_page_leftcol, i_tpl_page_submenu  {

	protected $metas;
	protected $keywords = "Творчество, литература, литературный клуб, читать, я пишу, современные авторы, литературный конкурс, рецензии, критика, статьи, стихи, проза, эссе, авторы, творческие люди";


	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "fullcol";

 	$this->title = self::$locale["title"];

 	$metas = $this->metas = ws_comm::several("type='meta' AND FIND_IN_SET('libro', org_sphere)>0 AND FIND_IN_SET('publish', org_direct)>0");

 	$hard = new mr_list("ws_comm", array());
 	$light = new mr_list("ws_comm", array());
 	foreach($metas as $m)
 		if($m->apply_pubs == "public") $light[] = $m;
 		else $hard[] = $m;

	$week_top_prose = ws_libro_pub::several("type!='stihi' AND hidden='no' AND meta IN (".join(",", $metas->ids()).") AND time>UNIX_TIMESTAMP()-86400*7", 4, 0, "rating DESC");
 	$week_top_stihi = ws_libro_pub::several("type='stihi' AND hidden='no' AND meta IN (".join(",", $metas->ids()).") AND time>UNIX_TIMESTAMP()-86400*7", 4, 0, "rating DESC");

 	ob_start();

 	echo "<h1>", $this->title, "</h1>";


 	$this->last_pubs($metas->ids());
?>


<table class="libro-front">
 <colgroup>
  <col width="50%"/>
  <col/>
 </colgroup>
 <caption><h2><a href="list.order-recent.xml">Рейтинг недели</a></h2></caption>
 <tr>
  <th><a href="list.type-prose.order-recent.xml">Проза</a></th>
  <th><a href="list.type-stihi.order-recent.xml">Стихи</a></th>
 </tr>
 <tr>
  <td valign="top">
    <ol>
	 <?foreach($week_top_prose as $p){?><li><?=$p?> - <i><small><?=$p->author()?></small></i> (<?=$p->rating?>)</li><?}?>
	</ol>
  </td>
  <td valign="top">
    <ol>
	 <?foreach($week_top_stihi as $p){?><li><?=$p?> - <i><small><?=$p->author()?></small></i> (<?=$p->rating?>)</li><?}?>
	</ol>
  </td>
 </tr>
</table>

<?
 	$this->content = ob_get_clean();
 	$this->css[] = "pub/frontpage.css";
 }

 protected function last_pubs($comm_ids, $limit=20)
 {
 	$last_anonce_prose = ws_libro_pub::several("type!='stihi' AND hidden='no' AND meta IN (".join(",", $comm_ids).")", $limit, 0, "time DESC");
 	$last_anonce_stihi = ws_libro_pub::several("type='stihi' AND hidden='no' AND meta IN (".join(",", $comm_ids).")", $limit, 0, "time DESC");

?>
<table class="libro-front">
 <colgroup>
  <col width="50%"/>
  <col/>
 </colgroup>
 <caption><h2><a href="list.xml">Последние поступления</a></h2></caption>
 <tr>
  <th><a href="list.type-prose.xml">Проза</a></th>
  <th><a href="list.type-stihi.xml">Стихи</a></th>
 </tr>
 <tr>
  <td valign="top">
    <ul>
	 <?foreach($last_anonce_prose as $p){?><li><?=$p?> - <i><small><?=$p->author()?></small></i></li><?}?>
	</ul>
  </td>
  <td valign="top">
    <ul>
	 <?foreach($last_anonce_stihi as $p){?><li><?=$p?> - <i><small><?=$p->author()?></small></i></li><?}?>
	</ul>
  </td>
 </tr>
</table>
<?
 }

 public function col_right()
 {
 	ob_start();

?>
<p>
<ul>
<li><a href="list.club-otl.xml">Клубные рейтинги</a></li>
<li>Рекомендации</li>
<li><b><a href="resp.xml">Лента отзывов</a></b></li>

<li><a href="publish.xml">Доминанты</a></li>
<li><a href="review.xml">Обозреватели</a></li>
<li><a href="recense.xml">Рецензенты</a></li>
<li><a href="contest.xml">Конкурсы</a></li>
<li><a href="theme.xml">Тематические сообщества</a></li>
</ul>
</p>
<p><a href="secs.xml">Тематические разделы</a></p>
<center>
	<strong>
		<a href="reader.xml">Сервис для Читателей</a>
	</strong>
		<br/>
	<em>Непредвзятое Прочтение с Прикрытым Авторством</em>
</center>
<?

	$r = mr_sql::query("SELECT DISTINCT pub_id FROM mr_pub_responses ORDER BY time DESC LIMIT 4");
 	$lr = array();
 	while($l = mr_sql::fetch($r, mr_sql::get)) $lr[] = $l;
 	$resps = ws_libro_pub::several($lr);
?>
<p><a href="resp.xml">Последние отзывы</a>:
<ul>
<?foreach($resps as $r){?><li><?=$r?></li><?}?>
</ul>
</p>
<?

	$rec_comm = ws_comm::several("recense_strong='yes'");

 	$last_res = ws_comm_pub_anchor::rec($rec_comm->ids(), 0, 4);
?>
<p>Последние рецензии:
<ul>
<?foreach($last_res as $r){?><li><?=$r?></li><?}?>
</ul>
</p>
<?

 	return ob_get_clean();
 }

 public function col_left()
 {
 	$fields = "e.".str_replace(", ", ", e.", ws_comm_event_anonce::fields);
	$anonces = ws_comm_event_anonce::several_query("SELECT $fields FROM ".ws_comm_event_anonce::sqlTable." e LEFT JOIN ".ws_comm_event_sec::sqlTable." s ON e.section=s.id WHERE e.hidden='no' AND FIND_IN_SET('libro', s.org_sphere)>0 ORDER BY e.time DESC LIMIT 6");

	$comms = ws_comm::several("FIND_IN_SET('libro', org_sphere)>0");

	if(count($anonces)){
?>
<p><a href="events.xml">Последние события сферы</a>:
<ul>
<?foreach($anonces as $a){?><li><?=$a?></li><?}?>
</ul>
</p>
<?
	}

	if(count($comms)){
?>
<p><a href="comms.xml">Сообщества сферы</a>:</p>
<?foreach($comms as $c) echo "&nbsp;&nbsp;&nbsp;", $c, "<br/>";
	}
?>
<p>Направления деятельности:
<ul>
<?foreach(ws_comm::$org_directs as $o=>$d){?><li><a href="-<?=$o?>.xml"><?=$d?></a></li><?}?>
</ul>
</p>
<?
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