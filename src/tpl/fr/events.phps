<?php class tpl_fr_events implements i_locale {

	static protected $locale = array(), $lang = "";

	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}

 static public function out(ws_comm_event_anonce $anonce, $view="anonce")
 {
 	switch($view):

 		case "list":
?>
<li><b><?=$anonce?></b> - <?if($anonce->description) echo $anonce->description, "<br />";?>
<i><?=$anonce->auth()?>, <small><?=self::$locale["notes"]?>: <?=$anonce->notes()?></small></i>
</li>
<?
 		break;

 		case "anonce": default:
?>

<td class="event-anonce">
<strong><?=$anonce?></strong><br />
<?if($anonce->description){?><em><?=$anonce->description?></em><br /><?}?>
<?=$anonce->comm()?>, <?=$anonce->section()?>, <?=$anonce->auth()?>, <?=date("d.m.y", $anonce->time);
if($anonce->notes() || $anonce->city){?>
<div align="right">
<?if($anonce->city) { $city = ws_geo_city::byID($anonce->city); echo $city->flag(), " ", $city, " &nbsp; &nbsp;"; }
if($anonce->notes()){?><i><?=self::$locale["notes"]?>:</i> <?=$anonce->notes();}?>
</div>
<?}?>
</td>

<?
		break;
	endswitch;
 }

 static public function outlist(mr_list $list, $view="anonce")
 {
 	switch($view):

 		case "list":
?>
<h3><?=self::$locale["contents"]?></h3>:
<ul>
<?foreach($list as $v) if($v instanceof ws_comm_event_anonce) self::out($v, $view);?>
</ul>
<?
	 	break;

 		case "anonce": default:
?>
<table class="events-table">
<?
  foreach($list as $k=>$v) if($v instanceof ws_comm_event_anonce)
  {
  	if($k%2==0) echo "<tr>";
  	self::out($v, $view);
  	if($k%2==1) echo "</tr>";
  }
  if(count($list)%2==1) echo "<td>&nbsp;</td></tr>";
?>
</table>
<?
		break;
	endswitch;
 }


	}
?>