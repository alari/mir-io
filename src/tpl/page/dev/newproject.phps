<?php class tpl_page_dev_newproject extends tpl_page_dev_inc {
		
 public function __construct($filename="", $params="")
 {
 	if(!ws_self::is_member(27, ws_comm::st_curator))
 		throw new ErrorPageException("Access denied", 403);
 	
 	parent::__construct($filename, $params);
 	
 	ob_start();
 	 	
?>

<h1>Создать новый проект</h1>

<center><form method="post" action="/x/dev-ticket/project" accept-charset="utf-8">
<fieldset><legend>Описание проекта</legend>
<table>
	<tr>
		<td>Название:</td>
		<td><input type="text" name="title" maxlength="128" size="62" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="Создать проект"/>
		</td>
	</tr>
</table>
</fieldset>
</form>
</center>

<?	
 	$this->content = ob_get_clean();
 	
 	$this->title = "Создать новый проект на ".mr::host("dev", false);
 }
	
}?>