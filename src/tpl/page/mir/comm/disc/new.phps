<?php class tpl_page_mir_comm_disc_new extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_locale, i_tpl_page_ico {
	
	protected $disc;
	
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
 	
 	$this->disc = ws_comm_disc::factory($params["in"]);
 	
 	if(!$this->disc->is_showable())
 		throw new ErrorPageException(self::$locale["not_found"], 404);
 		
 	if($this->disc->comm()->id() != $this->comm->id() || !$this->disc->can_add_thread())
 		throw new RedirectException($this->disc->href());
 		
 	$this->title = "Новая дискуссия в разделе ".$this->disc->title."; Сообщество ".$this->comm->title;
 	
 	ob_start();
 	
 	$categs = ws_comm_pub_categ::several($this->comm->id());
 	
 	$discs = ws_comm_disc::several("comm_id=".$this->comm->id());
 	
 ?>
 <h1>Новая дискуссия</h1>
 <h2><?=$this->disc?>, <?=$this->comm?></h2>
 
 	<br />
 	
 <form method="post" action="/x/ajax-disc/thread/new" onsubmit="javascript:$('f_submit').disabled='yes'">
 
 <fieldset>
 	<legend>Информация о новой ветке</legend>
 	<center>
 <table>
 	<colgroup>
 		<col/>
 		<col width="35%"/>
 	</colgroup>
 	<tr>
 		<td>Тема дискуссионной ветки:</td>
 		<td><input type="text" name="title" size="32" maxlength="48"/></td>
 	</tr>
 	<tr>
 		<td>Комментарий или подзаголовок:</td>
 		<td><input type="text" name="description" size="32" maxlength="128"/></td>
 	</tr>
 	<tr>
 		<td>Дискуссионный раздел:</td>
 		<td>
 			<select name="disc">
 			<?foreach ($discs as $d) if($d->can_add_thread()) {?>
 		<option value="<?=$d->id()?>"<?=($d->id()==$this->disc->id()?' selected="yes"':"")?>><?=$d->title?></option>
 			<?}?>
 			</select>
 		</td>
 	</tr>
 	<tr>
 		<td colspan="2">Первое сообщение:</td>
 	</tr><tr>
 		<td colspan="2" align="center">
 			<textarea name="content" cols="60" rows="25" id="attach"></textarea>
 			<script type="text/javascript">text_markup('attach', 1, 0);</script>
 		</td>
 	</tr>
 	<?if(count($categs)){?>
 	<tr>
 		<td>Связанная категория произведений:</td>
 		<td>
 			<select name="category">
 				<option value="0">&nbsp;</option>
<?foreach($categs as $c){?><option value="<?=$c->id()?>"><?=$c->title?></option><?}?>
 			</select>
 		</td>
 	</tr>
 	<?}?>
 	
 	<tr>
 		<th colspan="2"><input id="f_submit" type="submit" value="Создать дискуссионную ветку"/></th>
 	</tr>
 
 </table>
</center>
</fieldset>

 </form>
 	
 <? 	
 	$this->content = ob_get_clean();
 }
 
 public function col_right()
 {
 	ob_start();
	
 	echo "<p>Сообщество: ", $this->comm, "</p>";
 	echo "<p>Раздел: ", $this->disc, "</p>";
 	
 	echo tpl_page_mir_comm_disc_inc::make_rc($this->disc->comm()->id(), $this->disc->id(), true);
 	
	$r = ob_get_clean();
	return $r;
 }
 
 public function p_ico()
 {
 	return "disc";
 }
 
	}
?>