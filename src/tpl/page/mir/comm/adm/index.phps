<?php class tpl_page_mir_comm_adm_index extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol, i_locale  {
		
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
 		
 	$this->title = $this->comm->title." - Контроль сообщества. Настройки представления";
 		
 	ob_start();
 	
 ?>
 <h1>Контроль сообщества</h1>
 <h2>Настройки представления</h2>
 	<br />
 	
 <form method="post" action="/x/comm-adm/display" id="disp-form">
 <center>
 
 <fieldset>
 <legend>Краткое описание сообщества</legend>
 <table>
 <tr>
 	<td><label for="disp-title">Название сообщества</label>:</td>
 	<td><input type="text" id="disp-title" name="title" value="<?=htmlspecialchars($this->comm->title)?>" size="32" maxlength="16"/></td>
 </tr>
 <tr>
 	<td><label for="disp-descr">Краткое описание</label>:</td>
 	<td><input type="text" id="disp-descr" name="description" value="<?=htmlspecialchars($this->comm->description)?>" size="32" maxlength="127"/></td>
 </tr>
 <tr>
 	<td><label for="disp-descr">Среднее описание</label>:</td>
 	<td><input type="text" id="disp-descr" name="descr_medium" value="<?=htmlspecialchars($this->comm->descr_medium)?>" size="52" maxlength="511"/></td>
 </tr>
 </table>
 </fieldset>
 
 <fieldset>
 <legend>Полное описание или правила сообщества</legend>
 
 <textarea cols="60" rows="15" name="rules" id="rules"><?=mr_text_trans::node2text($this->comm->rules)?></textarea>
 <script type="text/javascript">text_markup('rules', 1, 0);</script>
 </fieldset>
 
 <fieldset>
 <legend>Строчка навигации внутри сообщества</legend>
 
 	<table><tr><td>
 <input type="hidden" name="display_page_line" value="no"/>
 <input type="checkbox" name="display_page_line" id="display_page_line" value="yes"<?=($this->comm->display_page_line=="yes"?' checked="yes"':"")?>/>
 	&ndash; <label for="display_page_line">Отображать строчку навигации на страницах сообщества</label>;
 		<br/>
 		
<input type="hidden" name="display_discs" value="no"/>
 <input type="checkbox" name="display_discs" id="display_discs" value="yes"<?=($this->comm->display_discs=="yes"?' checked="yes"':"")?>/>
 	&ndash; <label for="display_discs">Показывать ссылку на дискуссии сообщества</label>;
 		<br/>
 		
<input type="hidden" name="display_events" value="no"/>
 <input type="checkbox" name="display_events" id="display_events" value="yes"<?=($this->comm->display_events=="yes"?' checked="yes"':"")?>/>
 	&ndash; <label for="display_events">Показывать ссылку на события сообщества</label>;
 		<br/>
 		
<input type="hidden" name="display_cols" value="no"/>
 <input type="checkbox" name="display_cols" id="display_cols" value="yes"<?=($this->comm->display_cols=="yes"?' checked="yes"':"")?>/>
 	&ndash; <label for="display_cols">Показывать ссылку на колонки сообщества</label>;
 		<br/>
 		
<input type="hidden" name="display_pubs" value="no"/>
 <input type="checkbox" name="display_pubs" id="display_pubs" value="yes"<?=($this->comm->display_pubs=="yes"?' checked="yes"':"")?>/>
 	&ndash; <label for="display_pubs">Показывать ссылку на произведения сообщества</label>;
 		<br/>
 	</td></tr></table>
 		
 </fieldset>
 
 	<br/><br/>
 	
 	<input type="button" value="Сохранить изменения"  onclick="$(this).disabled='yes';mr_Ajax_Form($('disp-form'), {update:$('disp-result')})"/>
 	<div id="disp-result"></div><input type="hidden" name="id" value="<?=$this->comm->id()?>"/>
 
 </center>
 </form>
 	
 <?
 		
 	$this->content = ob_get_clean();
 }
	}
?>