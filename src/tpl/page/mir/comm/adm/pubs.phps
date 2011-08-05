<?php class tpl_page_mir_comm_adm_pubs extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol, i_locale  {
		
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
		
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	 	
 	if( !ws_self::is_allowed("comm_control", $this->comm->id()) )
 		throw new ErrorPageException("У Вас нет прав на контроль этого сообщества", 403);
 		
 	$this->title = $this->comm->title." - Контроль сообщества. Приём произведений";
 		
 	$r = array(
 		"disable"=>"Отключить",
 		"private"=>"Руководство",
 		"protected"=>"Участники",
 		"public"=>"Все"
 	);
 	
 	ob_start();
 	
 ?>
 <h1>Контроль сообщества</h1>
 <h2>Приём произведений</h2>
 	<br />
 	
 <form method="post" action="/x/comm-adm/pubs" id="pubs-form">
 <center>
 
 <fieldset>
 <legend>Настройка прав публикации</legend>
 
 	<table>
 	
 	<tr>
 		<td><label for="apply_pubs">Подавать произведения могут</label>:</td>
 		<td><select name="apply_pubs" id="apply_pubs">
 			<?foreach($r as $k=>$v){?><option value="<?=$k?>"<?=($k==$this->comm->apply_pubs?' selected="yes"':"")?>><?=$v?></option><?}?>
 		</select></td>
 	</tr>
 	
 	<?if($this->comm->type=="meta" || $this->comm->type=="closed"){?>
 	
 	<tr>
 		<td><label for="apply_pubs_disc">Выбирать круг отзывающихся на произведения могут</label>:</td>
 		<td><select name="apply_pubs_disc" id="apply_pubs_disc">
 			<?foreach($r as $k=>$v){?><option value="<?=$k?>"<?=($k==$this->comm->apply_pubs_disc?' selected="yes"':"")?>><?=$v?></option><?}?>
 		</select></td>
 	</tr>
 	<tr>
 		<td colspan="2" align="center">
 			<input type="hidden" name="apply_pubs_adm" value="no"/>
 			<input type="checkbox" name="apply_pubs_adm" id="apply_pubs_adm" value="yes"<?=($this->comm->apply_pubs_adm=="yes"?' checked="yes"':"")?>/>
 				&ndash; <label for="apply_pubs_adm">Разрешить авторам самостоятельно модерировать отзывы</label>
 		</td>
 	</tr>
 	
 	<?} else {?>
 	
 	<tr>
 		<td colspan="2" align="center">
 			<input type="hidden" name="editors_apply" value="no"/>
 			<input type="checkbox" name="editors_apply" id="editors_apply" value="yes"<?=($this->comm->editors_apply=="yes"?' checked="yes"':"")?>/>
 				&ndash; <label for="editors_apply">Разрешить руководству сообщества принимать произведения</label>
 		</td>
 	</tr>
 	
 	<?}?>
 	
 	</table>
 
 </fieldset>
 
 <fieldset>
 <legend>Квоты на приём произведений</legend>
 <table>
 <tr>
 	<td><label for="pubs-prose">Проза, произведений в день</label>:</td>
 	<td><input type="text" id="pubs-prose" name="apply_prose" value="<?=$this->comm->apply_prose?>" size="4" maxlength="5"/></td>
 </tr>
 <tr>
 	<td><label for="pubs-stihi">Стихи, произведений в день</label>:</td>
 	<td><input type="text" id="pubs-stihi" name="apply_stihi" value="<?=$this->comm->apply_stihi?>" size="4" maxlength="5"/></td>
 </tr>
 <tr>
 	<td><label for="pubs-article">Статьи и эссе, в день</label>:</td>
 	<td><input type="text" id="pubs-article" name="apply_article" value="<?=$this->comm->apply_article?>" size="4" maxlength="5"/></td>
 </tr>
 </table>
 </fieldset>
 
 	<br/><br/>
 	
 	<input type="button" value="Сохранить изменения"  onclick="$(this).disabled='yes';mr_Ajax_Form($('pubs-form'),{update:$('pubs-result')});return false;"/>
 	<div id="pubs-result"></div><input type="hidden" name="id" value="<?=$this->comm->id()?>"/>
 
 </center>
 </form>
 	
 <?
 		
 	$this->content = ob_get_clean();
 }
	}
?>