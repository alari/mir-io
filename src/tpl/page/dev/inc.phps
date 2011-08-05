<?php abstract class tpl_page_dev_inc extends tpl_page implements i_tpl_page_rightcol  {
	
	protected $layout = "rightcol";
	protected $project;
	
 public function col_right()
 {
 	$projects = ws_dev_project::getAll();
 	
 	ob_start();
 	 	
?>

<p><?=ws_comm::factory(27)?></p>

<?if($this->project instanceof ws_dev_project && ws_self::is_member(27)){?>

<p><a href="<?=mr::host("dev")?>/newticket.in-<?=$this->project->id()?>.xml">Создать новый тикет</a></p>

<?}?>

<p>Проекты:<br/>
<ul>
<?foreach($projects as $p){?><li><?=$p?></li><?}?>
</ul>
</p>

<?if(ws_self::is_member(27, ws_comm::st_curator)){?>
<p><a href="<?=mr::host("dev")?>/newproject.xml">Создать новый проект</a></p>
<?}
 	
 	return ob_get_clean();
 }
	
}?>