<?php abstract class tpl_page_edit extends tpl_page {
	
	protected $freepage = 0, $page, $action = "/x/site-freepage/save";
	
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->freepage = (int)$params["id"];
 	if(!$this->layout)
 	{
 		$this->layout = "asis";
 		$this->layout_site = false;
 	}
 	
 	if($this->freepage) $this->page = mr_sql::fetch(array("SELECT * FROM mr_site_freepages WHERE id=?", $this->freepage), mr_sql::obj);
 	
 	if((is_object($this->page) && $this->canedit()) || $this->cancreate())
 	{
 		
 		$this->title = $this->freepage ? "Правка странички №$this->freepage: \"$this->page->title\"" : "Создание новой странички";

 		$types = array("xml", "php", "html");
 		
 		ob_start();
 		?>
 		
 <form method="post" action="<?=$this->action?>">
 <table width="100%" height="100%">
 <colgroup>
 	<col width="50%" />
 	<col align="center" />
 </colgroup>
 	<tr><td>Заголовок:</td><td><input type="text" size="40" name="title" value="<?=htmlspecialchars($this->page->title)?>" maxlength="128" /></td></tr>
 	<tr><td>Тип странички:</td><td><select name="type"><?foreach ($types as $t){?><option<?=($t==$this->page->type?' selected="yes"':"")?>><?=$t?></option><?}?></select></td></tr>
 	<tr><td>CSS через запятую:</td><td><input type="text" size="40" name="css" value="<?=htmlspecialchars($this->page->css)?>" maxlength="255" /></td></tr>
 	<tr><td>Лэйаут:</td><td>tpl_layout_<input type="text" size="16" name="layout" value="<?=htmlspecialchars($this->page->layout)?>" maxlength="48" /></td></tr>
 	<tr><td>XSLT для XML-странички:</td><td><input type="text" size="40" name="xsltransform" value="<?=htmlspecialchars($this->page->xsltransform)?>" maxlength="255" /></td></tr>
 	<tr><td>Keywords:</td><td><input type="text" size="40" name="keywords" value="<?=htmlspecialchars($this->page->keywords)?>" maxlength="255" /></td></tr>
 	<tr><td>Description:</td><td><input type="text" size="40" name="description" value="<?=htmlspecialchars($this->page->description)?>" maxlength="255" /></td></tr>
 	<tr><td>Сайт:</td><td><input type="text" size="40" name="site" value="<?=htmlspecialchars($this->page->site)?>" maxlength="48" /></td></tr>
 	<tr><td>Адрес:</td><td>/<input type="text" size="20" name="url" value="<?=htmlspecialchars($this->page->url)?>" maxlength="255" /></td></tr>
 	<tr><td>Добавки в head:</td><td><textarea name="head"><?=$this->page->head?></textarea></td></tr>
 	<tr><td colspan="2" align="center">Текст странички:</td></tr></tr>
 	<tr><td colspan="2" align="center"><textarea name="content" style="width:90%;height:300px"><?=$this->page->content?></textarea></td></tr></tr>
 	<tr><td colspan="2" align="center"><input type="submit" value="Сохранить изменения" /> &nbsp; <input type="hidden" name="id" value="<?=($this->freepage?$this->freepage:"create")?>"> &nbsp; <input type="reset" value="Сбросить" /></td></tr></tr>
 </table>
 </form>
 		
 		<?

 		$this->content = ob_get_contents();
 		ob_end_clean();
 		
 		$this->head = "";
 		
 	} else throw new ErrorPageException("Невозможно отредактировать страничку", 403);
 }
 
 abstract protected function canedit();
 
 abstract static public function can_edit($page);
 
 abstract protected function cancreate();
 
 abstract static public function can_create();
	
	}
?>