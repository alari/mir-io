<?php class tpl_page_mir_comm_adm_front extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol {
				
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	 	
 	if( !ws_self::is_allowed("comm_control", $this->comm->id()) )
 		throw new ErrorPageException("У Вас нет прав на контроль этого сообщества", 403);
 		
 	$this->title = $this->comm->title." - Контроль сообщества. Главная страничка";
 		 	
 	ob_start();
 	
 ?>
 <h1>Контроль сообщества</h1>
 <h2>Настройки главной странички</h2>
 
 <form method="post" action="/x/comm-adm/front" id="front-form">
 
 <fieldset>
 <legend>Первичные настройки</legend>
 <center><table>
 
 <tr>
 	<td>Отображать шапку сообщества:</td>
 	<td><?$this->c_pos("head", 0);?></td>
 </tr>
 <tr>
 	<td>Статус пользователя в сообществе:</td>
 	<td><?$this->c_pos("incomm", 0);?></td>
 </tr>
 
 
 </table></center>
 </fieldset>
 
 <fieldset>
 <legend>Отображение участников</legend>
 <center><table>
 
 <tr>
 	<td>Показывать список участников:</td>
 	<td><?$this->c_pos("members");?></td>
 </tr>
 <tr>
 	<td>Ограничить список числом:</td>
 	<td><?$this->c_num("members_limit");?></td>
 </tr>
 <tr>
 	<td>Отображать список кураторов:</td>
 	<td><?$this->c_disp("mem_curators");?></td>
 </tr>
 <tr>
 	<td>Отображать претендентов на вступление:</td>
 	<td><?$this->c_disp("mem_pretendents");?></td>
 </tr>
 <tr>
 	<td>Ограничить список претендентов числом:</td>
 	<td><?$this->c_num("mem_pret_limit");?></td>
 </tr>
 
 
 </table></center>
 </fieldset>
 
 <fieldset>
 <legend>Колонки и события</legend>
 <center><table>
 
 <tr>
 	<td>Отображать список колонок:</td>
 	<td><?$this->c_pos("cols");?></td>
 </tr>
 <tr>
 	<td>Показывать последние события:</td>
 	<td><?$this->c_pos("events");?></td>
 </tr>
 <tr>
 	<td>Ограничить список событий числом:</td>
 	<td><?$this->c_num("events_limit");?></td>
 </tr>
 
 </table></center>
 </fieldset>
 
 <fieldset>
 <legend>Библиотека сообщества</legend>
 <center><table>
 
 <tr>
 	<td>Отображать блок библиотеки:</td>
 	<td><?$this->c_pos("libro");?></td>
 </tr>
 <tr>
 	<td>Показывать ссылку на прозу:</td>
 	<td><?$this->c_disp("libro_prose");?></td>
 </tr>
 <tr>
 	<td>Показывать ссылку на стихи:</td>
 	<td><?$this->c_disp("libro_stihi");?></td>
 </tr>
 <tr>
 	<td>Показывать ссылку на статьи и эссе:</td>
 	<td><?$this->c_disp("libro_article");?></td>
 </tr>
 <tr>
 	<td>Отображать категории произведений:</td>
 	<td><?$this->c_pos("categs");?></td>
 </tr>
 
 </table></center>
 </fieldset>
 
 <fieldset>
 <legend>Дискуссии сообщества</legend>
 <center><table>
 
 <tr>
 	<td>Отображать список дискуссионных разделов:</td>
 	<td><?$this->c_pos("disc");?></td>
 </tr>
 <tr>
 	<td>Показывать последние дискуссионные ветки:</td>
 	<td><?$this->c_disp("disc_last");?></td>
 </tr>
 <tr>
 	<td>Ограничить числом:</td>
 	<td><?$this->c_num("disc_limit");?></td>
 </tr>
 
 </table></center>
 </fieldset>
 
 <br/><br/>
 	<center>
 	<input type="button" value="Сохранить изменения"  onclick="$(this).disabled='yes';mr_Ajax_Form($('front-form'),{update:$('front-result')})"/>
 	<div id="front-result"></div><input type="hidden" name="id" value="<?=$this->comm->id()?>"/>
 </center>
 </form>
 	
 <?
 		
 	$this->content = ob_get_clean();
 }
 
 private function c_pos($name, $none=1)
 {
 	static $pos = array(
 		"right"=>"Справа",
 		"left"=>"Слева",
 		"none"=>"Отключить"
 	);
 	echo "<select name=\"$name\">";
 	$p = "front_".$name;
 	foreach($pos as $k=>$v) if($k!="none" || $none)
 		echo "<option value=\"$k\"".($this->comm->$p == $k ? ' selected="yes"':"").">$v</option>";
 	
 	echo "</select>";
 }
 
 private function c_disp($name)
 {
 	static $pos = array(
 		"yes"=>"Включить",
 		"no"=>"Выключить"
 	);
 	echo "<select name=\"$name\">";
 	$p = "front_".$name;
 	foreach($pos as $k=>$v) if($k!="none" || $none)
 		echo "<option value=\"$k\"".($this->comm->$p == $k ? ' selected="yes"':"").">$v</option>";
 	
 	echo "</select>";
 }
 
 private function c_num($name)
 {
 	$p = "front_".$name;
 	echo "<input type=\"text\" name=\"$name\" value=\"".$this->comm->$p."\" size=\"4\" maxlength=\"2\"/>";
 }
	}
?>