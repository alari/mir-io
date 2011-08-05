<?php class tpl_page_mir_comm_adm_events extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol, i_locale  {
		
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
 		
 	$this->title = $this->comm->title." - Контроль сообщества. Ленты событий";
 		 	
 	ob_start();
 	
 	$cols = ws_comm_event_sec::several("comm_id=".$this->comm->id());
 	
 ?>
 <h1>Контроль сообщества</h1>
 <h2>Ленты событий (колонки)</h2>
 	<br />
 	
 <fieldset>
 	<legend><a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-events/ajax/new', data:{comm:<?=$this->comm->id()?>},update:$('new-col')}).send()"><b>Создать новую ленту событий</b></a></legend>
 	<div align="right" id="new-col"></div>
 </fieldset>
 
 <?foreach($cols as $c){?>
 
 <fieldset>
 	<legend><?=$c?></legend>
 	
 	<div align="right" id="up-<?=$c->id()?>">
 	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-events/ajax/edit', data:{comm:<?=$this->comm->id()?>,col:<?=$c->id()?>},update:$(this).getParent()}).send()">Настройки</a>
 		&nbsp; &bull; &nbsp;
 	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/comm-adm-events/ajax/delete', data:{comm:<?=$this->comm->id()?>,col:<?=$c->id()?>},update:$(this).getParent()}).send()">Удалить</a>
 	</div>
 </fieldset>
 
 <?}?>
 	
 <?
 		
 	$this->content = ob_get_clean();
 }
	}
?>