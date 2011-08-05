<?php abstract class tpl_page_mir_comm_adm_inc extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_locale  {
		
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
 
 public function col_right()
 { 	
 	ob_start();
?>

<p>Контроль: <?=$this->comm?><br/>
<ul>
 <li><a href=".">Описание сообщества</a></li>
 <li><a href="front.xml">Главная страничка</a></li>
 
 <li><a href="members.xml">Участники сообщества</a></li>
 
	<li><a href="pubs.xml">Приём произведений</a></li>
	
<?if($this->comm->type=="open" || $this->comm->type=="members"){?>
	<li><a href="categs.xml">Категории произведений</a></li>
	<li><a href="recenses.xml">Настройка рецензий</a></li>
<?}?>
	<li><a href="discs.xml">Дискуссии</a></li>
	<li><a href="events.xml">Колонки и события</a></li>
	
	<li><a href="adv.xml">Рекламная ротация</a></li>
 
<?if(ws_self::is_allowed("comm_control_advanced", $this->comm->id())){?>

 <li><i><a href="advanced.xml">Административно</a></i></li>

<?}?>
 
</ul>
</p>
<?	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
	}
?>