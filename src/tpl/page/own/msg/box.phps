<?php class tpl_page_own_msg_box extends tpl_page_own_msg_inc implements i_tpl_page_rightcol {
	
	protected $perpage = 20;
		
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	
 	// локализуемся
 	$box = @$params[2] ? $params[2] : "inbox";
 	$page = (int)@$params["page"];
 	
 	// выбираем нужные сообщения
 	$msgs = ws_user_msg_item::several(ws_self::id(), $box, $this->perpage, $page*$this->perpage, true, $count);

 	// оцениваем общее количество страниц
 	$pages_total = floor($count/$this->perpage)-1;
 	
 	// установка заголовка
 	$this->title = self::$locale["box"]." ".self::$locale[$box].", ".self::$locale["msgs"];
 	
 	// начинаем вывод
 	$this->content = "<h1>".self::$locale[$box]."</h1>";
 	
 	ob_start();
 	
 	// листалка страниц
 	if($pages_total>0)
 	{
 		ob_start();
 	
 		echo "<div class=\"pager\">".ws_pager::title().": ";
 		
 		$pages = ws_pager::arr($page, $pages_total, 3);
 		$prev = 0;
 		
 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";
 				
 			echo '&nbsp; ', $p == $page ? "<b>".($page+1)."</b>" : '<a href="'.mr::host("own").'/msg/'.$box.($p?".page-".($p):"").'.xml">'.($p+1).'</a>';
 			$prev = $p;
 		}
 			
 		echo "</div>";
 		
 		$pager = ob_get_flush();
 	}
 	
 	// вывод таблицы со всеми сообщениями
 	$this->outlist( $msgs, 1+$this->perpage*$page, $box, $page, $pages_total==$page );
 	
 	echo $pager;
 	
 	$this->content .= ob_get_clean();
 	
 	$this->css[] = "own/msgs.css";
 }
 
 public function outlist(mr_list $list, $start_with=1, $box=null, $page=null, $lastpage=false)
 {
?>
<form method="post" action="/x/own-msgs/list/action">
<table class="msgs">
<tr class="msgs-caption">
	<th colspan="2">#</th>
	<th>Кто</th>
	<th>Тема</th>
	<th>Дата</th>
</tr>
<?
	if(count($list))
	{
  foreach($list as $k=>$v) if($v instanceof ws_user_msg_item)
  	$this->outitem($v, $start_with+$k);
	}
	else echo '<tr class="msgs-caption"><th colspan="5"><em>Нет сообщений в данном представлении</em></th></tr>';
  	
?>
<tr class="msgs-caption">
	<th colspan="2">#</th>
	<th>Кто</th>
	<th>Тема</th>
	<th>Дата</th>
</tr>
<tr class="msgs-actions">
 <td colspan="5">
 
 	Выбранные: <input name="delete" type="Submit" value="Удалить"/>
 
 	&nbsp;
 	
 	Отметить как: <input type="submit" name="readen" value="Прочитанные"/>
 	<input type="submit" name="notreaden" value="Непрочитанные"/>
 	
 	<?if($box){?><input type="hidden" name="box" value="<?=$box?>"/><?}?>
 	<?if($page!==null){?>
 		<input type="hidden" name="page" value="<?=$page?>"/>
 		<input type="hidden" name="onpage" value="<?=count($list)?>"/>
 		<input type="hidden" name="lastpage" value="<?=($lastpage?"yes":"no")?>"/>
 	<?}?>
 	
 </td>
</tr>
</table>
</form>
<?
 }
 
 public function outitem(ws_user_msg_item $anonce, $num)
 {
?>

<tr class="msgs-item<?=($anonce->readen=="no"?' msgs-unreaden':"").($anonce->flagged=="yes"?' msgs-flagged':"")?>">

	<td><input type="hidden" name="msg_<?=$anonce->id()?>" value="no"/><input type="checkbox" name="msg_<?=$anonce->id()?>" value="yes"/></td>

	<td>#<?=$num?></td>

	<td><?=$anonce->target()?></td>
	
	<td><?=$anonce?></td>
	
	<td><?=date("d.m.y H:i:s", $anonce->time)?></td>
</tr>

<?
 }
	
	}
?>