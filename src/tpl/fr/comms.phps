<?php class tpl_fr_comms implements i_locale {
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
 static public function out(ws_comm $comm, $tag="div")
 {
?>

<<?=$tag?> class="comm-descr">
<strong><?=$comm?></strong>
<?=$comm->descr_medium?>
</<?=$tag?>>

<?
 }
 
 static public function outlist(mr_list $list)
 {
?>
<table class="comms-table">
<?
  foreach($list as $k=>$v) if($v instanceof ws_comm)
  {
  	if($k%2==0) echo "<tr>";
  	self::out($v, "td");
  	if($k%2==1) echo "</tr>";
  }
  if(count($list)%2==1) echo "<td>&nbsp;</td></tr>";
?>
</table>
<?
 }
	
	
	}
?>