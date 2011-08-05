<?php class tpl_page_mir_comm_disc_inc {
	 
 static public function make_rc($comm_id, $disc_id, $other=false)
 {
 	$comm = ws_comm::factory($comm_id);
 	if($disc_id) $disc = ws_comm_disc::factory($disc_id);
 	
 	$discs = ws_comm_disc::byComm($comm->id(), false, true);
	
	if(count($discs))
	{
		foreach($discs as $k=>$d) if(!$d->is_showable()) unset($discs[$k]);
	}
	if(count($discs))
	{
?>
<p><a href="<?=$comm->href("discs.xml")?>">Дискуссии сообщества</a>:<br/>
<ul>
<?foreach($discs as $d) echo "<li>", $d->id()==$disc_id?"<b>":"", $d, $d->id()==$disc_id?"</b>":"", "</li>";?>
</ul>
</p>
<?
	}
	
		if($other){
	
	$odiscs = ws_comm_disc::several("comm_id!=".$comm->id()." AND strong='yes'");
	
	if(count($odiscs))
	{
		foreach($odiscs as $k=>$d) if(!$d->is_showable()) unset($odiscs[$k]);
	}
	if(count($odiscs))
	{
		function cmp_comms_in($a, $b)
		{
			$a = ws_comm_disc::factory($a)->comm();
			$b = ws_comm_disc::factory($b)->comm();
			if(!$a->is_sphere("disc")) return 1;
 			if(!$b->is_sphere("disc")) return -1;
 			return 0;
		}
		$a = $odiscs->ids();
		usort($a, "cmp_comms_in");
?>
<p><a href="/soc/disc.xml">Другие дискуссии</a>:<br/>
<ul>
<?foreach($a as $id) echo "<li>", ws_comm_disc::factory($id), "</li>";?>
</ul>
</p>
<?
	}
	
		}
	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
	
	}
?>