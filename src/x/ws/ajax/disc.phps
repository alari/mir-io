<?php class x_ws_ajax_disc extends x implements i_xmod {
	
static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
	
 static public function note()
 {
 	tpl_fr_comment::add_form("/x/ajax-disc/note/add", "ws_comm_disc_note", $_POST["parent"]);
 }
 
 static public function note_add()
 {		
 	$content = trim($_POST["msg"]);
 			
 	if( !ws_self::ok() && mr_security::spamFilter($content) )
 	die("<b>Вы не авторизованы. Сработал спам-фильтр.</b>");
 			
 	$ev = ws_comm_disc_thread::factory((int)$_POST["parent"]);
 	if( !$ev->can_add_note() )
 		die("<b>Вы не можете оставить сообщение в данной дискуссионной ветке.</b>");
 				
 	$rem_type = ws_user_remind::type_society_thread_notes;
 	if(ws_self::ok())
 		$rem_ch = ws_user_remind::change_sub( $rem_type, (int)$_POST["parent"], $_POST["remind"], $_POST["method"] );
 		
 	if(strlen($content)<8)
 	{
 		echo (
 			$rem_ch ?
 			"<b>Состояние Вашей подписки успешно изменено</b>" :
 			"<b>В отзыве обязана присутствовать содержательная часть.</b>"
 			 )."<br/>";
 		tpl_fr_comment::add("/x/ajax-disc/note", $ev->id(), false);
 		return;
 	} 				
 	$r = $ev->addNote(ws_self::id(), $content );
 			
 	if($r->id()) 	
 	{
 		$r->notify_subscribers();
 			
 		tpl_fr_comment::out( $r );
 	} else echo "<b>Не удалось сохранить отзыв из-за программных неполадок.</b>";
 	tpl_fr_comment::add("/x/ajax-disc/note", $ev->id(), false);
 }
 
 static public function thread_new()
 {
 
 	$disc = ws_comm_disc::factory( (int)$_POST["disc"] );
 	if(!$disc->can_add_thread())
 		throw new RedirectException($disc->comm()->href(), 5, "Вы не имеете прав на совершение этого действия", "Ошибка");
 		
 	$title = mr_text_string::remove_excess( trim($_POST["title"]) );
 	if(!$title)
 		throw new RedirectException( $disc->comm()->href("new-thread.id-".$disc->id().".xml"), 5, "Невозможно создание дискуссионной ветки без заголовка", "Безуспешно" );
 	
 	$content = trim($_POST["content"]);
 	if( strlen($content)<12 )
 		throw new RedirectException( $disc->comm()->href("new-thread.id-".$disc->id().".xml"), 5, "Проверьте, пожалуйста, ввели ли Вы первое сообщение", "Безуспешно" );
 		
 	$descr = mr_text_string::remove_excess( trim($_POST["description"]) );
 	
 	$thread = ws_comm_disc_thread::create($disc->comm()->id(), $disc->id(), ws_self::id(), $title);
 	if(!$thread->id())
 		throw new RedirectException( $disc->comm()->href("new-thread.id-".$disc->id().".xml"), 5, "Не удалось сохранить данные дискуссионной ветки", "Безуспешно" );
 	
 	$note = $thread->addNote(ws_self::id(), $content );
 	if(!$note->id())
 	{
 		$thread->delete();
 		throw new RedirectException( $disc->comm()->href("new-thread.id-".$disc->id().".xml"), 5, "Не удалось сохранить данные первого сообщения. Создание ветки обращено.", "Безуспешно" );
 	}
 	
 	if($descr)
 	{
 		$thread->description = $descr;
 	}
 	$categ = (int)$_POST["category"];
 	if($categ){
 		$categ = ws_comm_pub_categ::factory($categ);
 		if($categ->comm()->id() == $thread->comm()->id())
 			$thread->category = $categ->id();
 	}
 	$thread->save();
 	
 	throw new RedirectException( $thread->href(), 5, "Новая дискуссионная ветка успешно создана!" );
 }
 
 static public function thread_adm()
 {
 	$thread = ws_comm_disc_thread::factory( (int)$_POST["id"] );
 	
 	if(!$thread->is_showable()) die("Ошибка: недостаточно прав");
 	
 	echo "<ul>";
 	
 	if($thread->can_ch_vis()) {
 		
 		$vis = array("public"=>"Всем", "protected"=>"Участникам", "private"=>"Руководству");
 		
 		?>
 		<li id="disc-vis">
 			Видимость:
 			<form method="post" id="disc-vis-form" action="/x/ajax-disc/thread/vis">
 				<select name="visibility" onchange="javascript:mr_Ajax_Form($('disc-vis-form'),{update:$('disc-vis')})">
 					<?foreach($vis as $k=>$v){?><option value="<?=$k?>"<?=($k==$thread->visibility?' selected="yes"':"")?>><?=$v?></option><?}?>
 				</select>
 				<input type="hidden" name="id" value="<?=$thread->id()?>"/>
 			</form>
 		</li>
 		<li>
 			<a href="javascript:void(0)" onclick="mr_Ajax({url:'/x/ajax-disc/thread/category',data:{id:<?=$thread->id()?>},update:$(this).getParent()}).send()">Связанная категория</a>
 		</li>
 		<?
 	}
 	if($thread->can_close()) echo "<li><a href=\"javascript:void(0)\" onclick=\"javascript:mr_Ajax({url:'/x/ajax-disc/thread/close',data:{id:".$thread->id()."},update:$(this).getParent()}).send()\">".($thread->closed=="yes"?"Открыть":"Закрыть")." дискуссию</a></li>";
 	if($thread->can_delete()) echo "<li><a href=\"javascript:void(0)\" onclick=\"javascript:if(confirm('Вы уверены в своём желании необратимого удаления дискуссионной ветки?')) mr_Ajax({url:'/x/ajax-disc/thread/delete',data:{id:".$thread->id()."},update:$(this).getParent()}).send()\">Удалить дискуссию</a></li>";
 	
 	echo "</ul>";
 }
 
 static public function thread_category()
 {
	$thread = ws_comm_disc_thread::factory( (int)$_POST["id"] );
 	
 	if(!$thread->is_showable() || !$thread->can_ch_vis()) die("Ошибка: недостаточно прав");
 	
 	$categs = ws_comm_pub_categ::several($thread->comm()->id());
 	
?>
	Категория: <br/>
	<select onchange="mr_Ajax({url:'/x/ajax-disc/thread/category/save',data:{id:<?=$thread->id()?>,category:this.value},update:$(this).getParent()}).send()">
 		<option value="0">&nbsp;</option>
<?foreach($categs as $c){?><option value="<?=$c->id()?>"<?=($c->id()==$thread->category ? ' selected="yes"':"")?>><?=$c->title?></option><?}?>
 	</select>
<?
 }
 
 static public function thread_category_save()
 {
 	$thread = ws_comm_disc_thread::factory( (int)$_POST["id"] );
 	
 	if(!$thread->is_showable() || !$thread->can_ch_vis()) die("Ошибка: недостаточно прав");
 	
 	$categ = (int)$_POST["category"];
 	if($categ){
 		$categ = ws_comm_pub_categ::factory($categ);
 		if($categ->comm()->id() == $thread->comm()->id())
 			$thread->category = $categ->id();
 	}
 	else $thread->category = 0;
 	echo $thread->save() ? "Теперь: ".$thread->category()->link() : "Изменения не сохранены";
 }
 
 static public function thread_delete()
 {
 	$thread = ws_comm_disc_thread::factory( (int)$_POST["id"] );
 	if(!$thread->can_delete())
 		die("<b>Недостаточно прав</b>");
 	$thread->delete();
 		die("<b>Дискуссионная ветка благополучно удалена</b>");
 }
 
 static public function thread_vis()
 {
 	$thread = ws_comm_disc_thread::factory( (int)$_POST["id"] );
 	if(!$thread->can_ch_vis())
 		die("<b>Недостаточно прав</b>");
 	$thread->visibility = $_POST["visibility"];
 	// BACKWARD COMPARTIBILITY HACK
 	if($_POST["visibility"] == "public") $thread->hidden = "yes";
 	else $thread->hidden = "no";
 	// END OF HACK
 	echo "<b>", $thread->save() ? "Видимость успешно изменена" : "Сохранить изменения не удалось", "</b>";
 }
 
 static public function thread_close()
 {
 	$thread = ws_comm_disc_thread::factory( (int)$_POST["id"] );
 	if(!$thread->can_close())
 		die("<b>Недостаточно прав</b>");
 	$thread->closed = $thread->closed=="yes"?"no":"yes";
 	echo "<b>", $thread->save() ? "Статус дискуссии изменён" : "Сохранить изменения не удалось", "</b>";
 }
 
	}
?>