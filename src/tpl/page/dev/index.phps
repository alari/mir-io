<?php class tpl_page_dev_index extends tpl_page_dev_inc {
		
 public function __construct($filename="", $params="")
 {
 	$this->head = '<script type="text/javascript" src="/style/js/dev.js"></script>';
 	 		
 	ob_start();
 	
 	$tickets = ws_dev_ticket::getAll(null, !@$params["closed"]);
 	$mod = new mr_list("ws_dev_ticket");
 	$tick = new mr_list("ws_dev_ticket");
 	foreach($tickets as $t)
 	{
 		if($t->isModule()) continue;
 		$tick[] = $t;
 		if($t->module && !in_array($t->module, $mod->ids())) $mod[] = $t->module();
 	}
 	
?>

<h1>Тикеты</h1>
<h2>Все проекты</h2>

<a href="javascript:void(0)" onclick="dev.displayByModules('tickets')">По модулям</a>
<a href="javascript:void(0)" onclick="dev.displayByPriorities('tickets')">По приоритетам</a>
<a href="javascript:void(0)" onclick="dev.displayByTime('tickets')">По времени</a>
<a href="<?=(@$params["closed"]?"/":"/index.closed-1.xml")?>"><?=(@$params["closed"]?"Показать открытые":"Показать закрытые")?></a>

<div id="tickets"></div>

<script type="text/javascript">
<?foreach(ws_dev_ticket::getStatuses(0) as $k=>$v){?>
dev.statuses[<?=$k?>] = '<?=$v?>';
<?}?>
<?foreach(ws_dev_ticket::getTypes() as $k=>$v){?>
dev.types[<?=$k?>] = '<?=$v?>';
<?}?>
<?foreach(ws_dev_ticket::getPriorities() as $k=>$v){?>
dev.addPriority(<?=$k?>, '<?=$v?>');
<?}?>
<?foreach($mod as $m){?>
dev.addModule(new dev.module(<?=$m->id()?>, '<?=htmlspecialchars($m->title)?>', <?=$m->type?>, <?=$m->priority?>, '<?=date("d.m.Y", $m->time)?>'));
<?}?>
<?foreach($tick as $t){?>
dev.addTicket(new dev.ticket(<?=$t->id()?>, '<?=htmlspecialchars($t->title)?>', <?=$t->type?>, <?=$t->priority?>, '<?=date("d.m.y H:i:s", $t->time)?>', <?=$t->module?>));
<?}?>
dev.displayByModules("tickets");
</script>

<?	
 	$this->content = ob_get_clean();
 	
 	$this->title = "Разработка сайта";
 	
 	$this->css[] = "dev/tickets.css";
 }
	
}?>