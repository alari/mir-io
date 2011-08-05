<?php class tpl_page_mir_comm_adm_adv extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol {
				
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	 	
 	if( !ws_self::is_allowed("comm_control", $this->comm->id()) )
 		throw new ErrorPageException("У Вас нет прав на контроль этого сообщества", 403);
 		
 	$this->title = $this->comm->title." - Контроль сообщества. Рекламная ротация";
 		 	
 	ob_start();
 	
 	$advs = ws_comm_adv::several(0, $this->comm->id);
 	
 ?>
 <h1>Контроль сообщества</h1>
 <h2>Рекламная ротация</h2>
 
<?if(count($advs) < $this->comm->adv_limit){ ?>
 
 <form method="post" action="/x/comm-adm-adv/create">
 <fieldset>
 	<legend>Создать новое рекламное сообщение</legend>
 	
 	<center>
 	
 	<table>
 	
 	<tr>
 		<td>Текст ссылки:</td>
 		<td><input type="text" maxlength="64" size="40" name="link"/></td>
 	</tr>
 	<tr>
 		<td>Адрес ссылки:</td>
 		<td><input type="text" maxlength="127" size="40" name="url"/></td>
 	</tr>
 	<tr>
 		<td>Комментарий:</td>
 		<td><input type="text" maxlength="64" size="40" name="comment"/></td>
 	</tr>
 	<tr>
 		<td colspan="2">
 			<input type="Submit" value="Добавить в ротацию"/>
 			<input type="hidden" name="comm" value="<?=$this->comm->id?>"/>
 		</td>
 	</tr>
 	
 	</table>
 	
 	</center>
 </fieldset>
 </form>
 	
 <?} ?>
 	
 <?if(count($advs)){ ?>
 	
 <fieldset>
 	<legend>Сообщения в ротации от сообщества</legend>
 	<center>
 	
 	<table>
 	<?foreach($advs as $adv){ ?>
 	
 	<tr>
 		<td><?=$adv ?></td>
 		<td><small>
 			<a href="/x/comm-adm-adv/delete?adv=<?=$adv->id?>">Удалить</a>
 		</small></td>
 	</tr>
 	
 	<?} ?>
 	</table>
 	
 	</center>
 </fieldset>
 	
 <?}
 		
 	$this->content = ob_get_clean();
 }
	}
?>