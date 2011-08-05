<?php class tpl_page_own_lib_stat extends tpl_page_own_lib_inc {
	
public function __construct($filename="", $params="")
{
 	parent::__construct($filename, $params);
 	
 	$pub = ws_libro_pub::factory( (int)$params["id"] );
 	
 	if($pub->author(true)->id() != ws_self::id() || !ws_self::is_allowed("pub_stat"))
 		throw new ErrorPageException("Вы не можете просматривать статистику этого произведения.", 403);
 	
 	$this->layout = "rightcol";

	$this->title = "Статистика просмотров произведения: &laquo;".$pub->title."&raquo;";
 	
 	$this->content = "<h1>".$pub->link()."</h1><h2>Статистика просмотров произведения</h2>";
 	
 	$list = ws_libro_pub_stat::byPub($pub->id());
 	
 	ob_start();
	?>

<table width="100%">
<colgroup>
<col align="center"/>
<col align="center"/>
<col align="center"/>
<col align="center"/>
<col align="center"/>
<col align="center"/>
</colgroup>
<tr>
	<th>#</th>
	<th>Читатель</th>
	<th>Просмотров</th>
	<th>Первый</th>
	<th>Последний</th>
	<th>Отзывы</th>
</tr>

<?foreach($list as $k=>$v){?>

<tr>
	<td><?=$k?></td>
	<td><?=$v->user()?></td>
	<td><?=$v->views?></td>
	<td><?=date("d/m/y H:i:s", $v->first_view)?></td>
	<td><?=date("d/m/y H:i:s", $v->last_view)?></td>
	<td><?=$v->resp_count?> / <?=$v->resp_size?></td>
</tr>
  	
<?}?>

<tr>
	<th>#</th>
	<th>Читатель</th>
	<th>Просмотров</th>
	<th>Первый</th>
	<th>Последний</th>
	<th>Отзывы</th>
</tr>

</table>

<?
  $this->content .= ob_get_clean();
}	
	}