<?php class tpl_page_mir_comm_index extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_tpl_page_leftcol, i_locale  {

	static protected $locale = array(), $lang = "";

	protected $leader, $curators, $members, $pretendents,
		$front_left = array(), $front_right = array(),
		$front_blocks = array(
			"front_head",
			"front_incomm",
			"front_cols",
			"front_events",
			"front_libro",
			"front_categs",
			"front_disc",
			"front_members"
		);

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "rightcol";
if(!$this->comm) die("test");
 	$mem = $this->comm->members();

 	if($this->comm->description) {
 		$this->description = $this->comm->title ." - ".$this->comm->description. " - сообщество творческих людей на Мире Ио";
 	} else {
 		$this->description = $this->comm->title . " - сообщество творческих людей на Мире Ио.";
 	}

 	$this->leader = 0;

 	$this->curators = array();
 	$this->members = array();
 	$this->pretendents = array();
 	foreach($mem as $uid=>$status) switch($status)
 	{
 		case ws_comm::st_leader: case ws_comm::st_coord: $this->leader = ws_user::factory($uid); continue;
 		case ws_comm::st_curator: $this->curators[] = ws_user::factory($uid); continue;
 		case ws_comm::st_member: $this->members[] = ws_user::factory($uid); continue;
 		default: $this->pretendents[] = ws_user::factory($uid); continue;
 	}

 	foreach($this->front_blocks as $f)
 	{
 		$d = $this->comm->$f;
 		if($d == "none" || !$d) continue;
 		if($d == "left") $this->front_left[] = $f;
 		if($d == "right") $this->front_right[] = $f;
 	}
 	if(count($this->front_left)) $this->layout = "fullcol";


 	$this->title = $this->comm->title.($this->comm->description?" - ".$this->comm->description:"");

 	$this->content = "<h1>".$this->comm->title."</h1><h2>".$this->comm->description."</h2>";
 	if($this->comm->rules)
 	{
	 	$f = new mr_xml_fragment;
	 	$f->loadXML($this->comm->rules);

	 	$this->content .= $f->realize();
 	}
 }

 public function col_right()
 {
 	ob_start();

	foreach($this->front_right as $f) if(method_exists($this, $f)) $this->$f();

 	return ob_get_clean();
 }

 public function col_left()
 {

 	ob_start();

 	foreach($this->front_left as $f) if(method_exists($this, $f)) $this->$f();

 	return ob_get_clean();
 }

/**
 * Дискуссии сообщества
 *
 */
 public function front_disc()
 {
 	echo tpl_page_mir_comm_disc_inc::make_rc($this->comm->id(), 0);
 	if($this->comm->front_disc_last == "yes")
 	{
 		$limit = $this->comm->front_disc_limit;
 		$last = ws_comm_disc_thread::several("comm_id=".$this->comm->id(), $limit, 0, "time DESC");
 		if(count($last))
 		{
?>
<p>Последние ветки:
<ul>
<?foreach($last as $l){?><li><?=$l?></li><?}?>
</ul>
</p>
<?
 		}
 	}
 }

/**
 * Колонки событий, которые на главной странице
 *
 */
 public function front_cols()
 {
 	$cols = ws_comm_event_sec::several("comm_id=".$this->comm->id()." AND display='yes'");
 	if(count($cols))
 	{
?>
<p><?=self::$locale["cols"]?>:
<ul><?foreach($cols as $c){?><li><?=$c?></li><?}?></ul>
</p>
<p><a href="events.xml"><?=self::$locale["all_events"]?></a></p>
<?
 	}
 }

/**
 * Последние события сообщества
 *
 */
 public function front_events()
 {
 	$r = ws_comm_event_anonce::several("comm_id=".$this->comm->id(), $this->comm->front_events_limit);
 	if(count($r))
 	{
?>
<p><a href="events.xml">Последние события:</a>
<ul>
<?foreach($r as $e){?><li><?=$e?></li><?}?>
</ul>
</p>
<?
 	}
 }

/**
 * Категории произведений
 *
 */
 public function front_categs()
 {
	$categs = ws_comm_pub_categ::several( $this->comm->id(), true );
	if(count($categs)){

?>
<p>Категории произведений:<br />
<ul>
<?foreach($categs as $c){?>
<li><?=$c?></li>
<?}?>
<?if($this->comm->category_none_display == "yes"){?><li><a href="pubs-n.xml">Вне категорий</a></li><?}?>
<li><a href="pubs.xml">Все произведения</a></li>
</ul>
</p>
	<?
	}
 }

/**
 * Вступление или выход из сообщества
 *
 */
 public function front_incomm()
 {
 	if(!ws_self::ok()) return;

 	if(ws_self::is_member($this->comm->id()) || ws_self::is_member($this->comm->id())===0)
 	{
?>
<p>Вы уже есть в этом сообществе.</p>
<center>
	<i><a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-usr/reject', data:{comm:<?=$this->comm->id()?>},update:$(this).getParent()}).send()">Покинуть сообщество</a></i>
</center>
<?
 	} else switch($this->comm->apply_members) {
 		case "no": echo "<p>Приём в сообщество закрыт</p>"; break;
 		case "yes":
?>
<p>Вы можете вступить в сообщество:</p>
<center>
	<i><a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-usr/apply', data:{comm:<?=$this->comm->id()?>},update:$(this).getParent()}).send()">Оставить заявку</a></i>
</center>
<?
 		break;
 		case "free":
?>
<p>Вы можете свободно вступить в сообщество:</p>
<center>
	<i><a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-usr/apply', data:{comm:<?=$this->comm->id()?>},update:$(this).getParent()}).send()">Стать участником</a></i>
</center>
<?
 		break;
 	}
 }

/**
 * Название и лидер сообщества
 *
 */
 public function front_head()
 {
?>
<p><?=self::$locale["comm"]?>: <?=$this->comm?></p>
<p>Лидер: <?=$this->leader?></p>
<?if(ws_self::is_allowed("comm_control", $this->comm->id())){?>
<p><a href="adm/">Контрольная панель</a></p>
<?}
 }

/**
 * Блок с участниками
 *
 */
 public function front_members()
 {
	if(count($this->curators)){
?>
<p><?=self::$locale["curators"]?> (<?=count($this->curators)?>):
<?if($this->comm->front_mem_curators == "yes"){?><ul><?foreach($this->curators as $c){?><li><?=$c?></li><?}?></ul><?}?>
</p>
<?
}
if(count($this->members)){
?>
<p><?=self::$locale["members"]?> (<?=count($this->members)?>):
<ul><?foreach($this->members as $i=>$c) if(
	$this->comm->front_members_limit < 0
  ||$this->comm->front_members_limit > $i
) {?><li><?=$c?></li><?}?></ul>
</p>
<?
}
if(count($this->pretendents) && $this->comm->front_mem_pretendents == "yes"){
?>
<p><?=self::$locale["pretendents"]?> (<?=count($this->pretendents)?>):
<ul><?foreach($this->pretendents as $i=>$c)
	if($this->comm->front_mem_pret_limit <0
	 || $this->comm->front_mem_pret_limit > $i )
	 	{?><li><?=$c?></li><?}?>
	 	</ul>
</p>
<?
}
 }

/**
 * Библиотека сообщества
 *
 */
 public function front_libro()
 {
 	$m = $this->comm->type == "meta" || $this->comm->type == "closed";
 	$href = array();
 	$href["pubs"] = $m ? mr::host("libro")."/list.meta-".$this->comm->name : "pubs";
 	$href["prose"] = $href["pubs"].".type-prose";
 	$href["stihi"] = $href["pubs"].".type-stihi";
 	$href["article"] = $href["pubs"].".type-article";
 	$count = array();
 	foreach($href as $t=>&$h){
 		$h.=".xml";
 		$u = "front_libro_".$t;
 		if($this->comm->$u == "yes")
 		{
 			$count[$t] = mr_sql::fetch(
 				$m ?
 				"SELECT COUNT(meta) FROM mr_publications WHERE meta=".$this->comm->id()." AND type='$t'" :
 				"SELECT COUNT(p.id) FROM mr_publications p LEFT JOIN mr_comm_pubs c ON c.pub_id=p.id WHERE c.comm_id=".$this->comm->id()." AND p.type='$t'"
 			, mr_sql::get);
 		}
 	}
?>
<p><a href="<?=$href["pubs"]?>">Библиотека сообщества</a>
<?if(count($count)){?>
<ul>
<?foreach($count as $t=>$c){?>
<li><a href="<?=$href[$t]?>"><?=ws_libro_pub::getType($t)?></a> (<?=$c?>)</li>
<?}?>
</ul>
<?}?>
</p>
<?
	if($m){?>
<p><a href="resp.xml">Отзывы в доминанте</a></p>
	<?}
 }

	}
?>
