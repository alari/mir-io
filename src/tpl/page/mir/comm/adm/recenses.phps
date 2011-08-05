<?php class tpl_page_mir_comm_adm_recenses extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol {
			
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	 	
 	if( !ws_self::is_allowed("comm_control", $this->comm->id()) )
 		throw new ErrorPageException("У Вас нет прав на контроль этого сообщества", 403);
 		
 	$this->title = $this->comm->title." - Контроль сообщества. Рецензии";
 		
 	ob_start();
 	
 	$rec_apply = array(
 		"disable"=>"Не предоставляются",
 		"protected"=>"Рецензенты - Участники",
 		"private"=>"Рецензенты - Кураторы"
 	);
 	$rec_method = array(
 		"free"=>"По желанию автора",
 		"censor"=>"Рецензент выбирает произведение",
 		"auth"=>"Автор выбирает рецензента"
 	);
 	
 ?>
 <h1>Контроль сообщества</h1>
 <h2>Рецензии</h2>
 	<br />
 	
 <form method="post" action="/x/comm-adm/recenses" id="rec-form">
 <center>
 
 <fieldset>
 <legend>Правила рецензирования</legend>
 <table>
 <tr>
 	<td><label for="rec-apply">Рецензии в сообществе</label>:</td>
 	<td>
 		<select id="rec-apply" name="apply">
 		<?foreach($rec_apply as $r=>$a){?><option value="<?=$r?>"<?=($r==$this->comm->recense_apply?' selected="yes"':"")?>><?=$a?></option><?}?>
 		</select>
 	</td>
 </tr>
 <tr>
 	<td><label for="rec-method">Распределение рецензий</label>:</td>
 	<td>
 		<select id="rec-method" name="method">
 		<?foreach($rec_method as $r=>$a){?><option value="<?=$r?>"<?=($r==$this->comm->recense_method?' selected="yes"':"")?>><?=$a?></option><?}?>
 		</select>
 	</td>
 </tr>
 </table>
 </fieldset>
 
 	<br/><br/>
 	
 	<input type="button" value="Сохранить изменения"  onclick="$(this).disabled='yes';mr_Ajax_Form($('rec-form'),{update:$('rec-result')})"/>
 	<div id="rec-result"></div><input type="hidden" name="id" value="<?=$this->comm->id()?>"/>
 
 </center>
 </form>
 	
 <?
 		
 	$this->content = ob_get_clean();
 }
	}
?>