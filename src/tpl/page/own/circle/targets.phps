<?php class tpl_page_own_circle_targets extends tpl_page {
	
public function __construct($filename="", $params="")
 {
 	if(!ws_self::is_allowed("circle"))
 		throw new ErrorPageException("Недостаточно прав для контроля Круга Чтения", 403);
 	
 	parent::__construct($filename, $params);
 		
 	$this->title = "Круг Чтения: настройки";

	$circle = ws_user_circle::byUser( ws_self::id() );
 	
 	ob_start();
 	
?>

	<h1>Настройки: Круг Чтения</h1>

	<?foreach($circle as $c){?>
	
<div id="c-<?=$c->id()?>" style="border: 1px solid silver; margin: 1em">
	<strong><?=$c->target()?></strong><br/>
	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/own-circle/follow', evalResponse:true,data:{id:<?=$c->id()?>,t:'blogs'}}).send()">Дневники: <b id="blogs-<?=$c->id()?>"><?=($c->follow_blogs=="yes" ? "Да" : "Нет")?></b></a>
		&bull;
	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/own-circle/follow', evalResponse:true,data:{id:<?=$c->id()?>,t:'pubs'}}).send()">Произведения: <b id="pubs-<?=$c->id()?>"><?=($c->follow_pubs=="yes" ? "Да" : "Нет")?></b></a>
		&bull;
	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/own-circle/follow', evalResponse:true,data:{id:<?=$c->id()?>,t:'advices'}}).send()">Рекомендации: <b id="advices-<?=$c->id()?>"><?=($c->follow_advices=="yes" ? "Да" : "Нет")?></b></a>
		&bull;
	<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/own-circle/trust', evalResponse:true,data:{id:<?=$c->id()?>,t:'blogs'}}).send()">Доверять закрытые в Дневнике: <b id="t-blogs-<?=$c->id()?>"><?=($c->trust_blogs=="yes" ? "Да" : "Нет")?></b></a>
		&bull;
	<a href="javascript:void(0)" onclick="if(!confirm('Точно убрать из Круга Чтения?')) return; $('c-<?=$c->id()?>').style.border='1px solid darkred';mr_Ajax({url:'/x/own-circle/delete', evalResponse:true,data:{id:<?=$c->id()?>}}).send()">Удалить</a>
</div>
	
	<?}?>
	
<?

		
	
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 }
	}
?>