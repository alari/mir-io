<?php class x_ws_own_lib extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function draft_delete()
 {
 	if(!ws_self::ok())
 		throw new ErrorPageException("Потеряна авторизация.", 403);
 		
 	$draft = ws_libro_pub_draft::factory( $id=(int)$_POST["id"] );
 	if($draft->user_id != ws_self::id())
 		throw new ErrorPageException("Чужой или несуществующий черновик.", 403);
 		
 	$draft->delete();
 	
 	if($_POST["mode"]=="item")
 		echo "<b>Черновик удалён</b>";
 	else {
?>
$("draft-<?=$id?>").style.display='none';
<?
 	}
 }
 
 static public function draft_edit()
 {
	if(!ws_self::ok())
 		throw new ErrorPageException("Потеряна авторизация.", 403);
 		
 	$draft = ws_libro_pub_draft::factory( (int)$_POST["id"] );
 	if($draft->user_id != ws_self::id())
 		throw new ErrorPageException("Чужой или несуществующий черновик.", 403);
 		
 	self::item_edit($draft);
 	$draft->save();
 	
 	throw new RedirectException($draft->href(), 5, "Изменения успешно сохранены");
 }
 
 static public function pub_edit()
 {
	if(!ws_self::ok())
 		throw new ErrorPageException("Потеряна авторизация.", 403);
 		
 	$pub = ws_libro_pub_item::factory( (int)$_POST["id"] );
 	if($pub->author != ws_self::id())
 		throw new ErrorPageException("Чужое или несуществующее произведение.", 403);
 		
 	self::item_edit($pub);
 	$pub->save();
 	
 	throw new RedirectException($pub->pub()->href(), 5, "Изменения успешно сохранены");
 }
 
 static private function item_edit($item)
 {
 	if($_POST["type"] && $_POST["type"] != $item->type)
 	{
 		$item->type = $_POST["type"];
 		if($item instanceof ws_libro_pub_item)
 			$item->section = 0;
 	}
 	$item->setContent(
 		rtrim($_POST["content"]),
 		rtrim($_POST["epygraph"]),
 		rtrim($_POST["postscriptum"])
 	);
 	$item->write_time = mr_text_string::remove_excess(trim($_POST["write_time"]));
 	$item->write_place = mr_text_string::remove_excess(trim($_POST["write_place"]));
 	$item->first_pub = (int)mr_text_string::remove_excess(trim($_POST["first_pub"]));
 		if($item->first_pub < 1921) $item->first_pub = date("Y");
 	$item->title = mr_text_string::remove_excess(trim($_POST["title"]));
 		if(!$item->title) $item->title = "* * *";
 }
 
 static public function draft_new()
 {
 	if(!ws_self::ok())
 		throw new ErrorPageException("Потеряна авторизация.", 403);
 		
 	$title = mr_text_string::remove_excess(trim($_POST["title"]));
 	if(!$title)
 		$title = "* * *";
 		
 	$item = ws_libro_pub_draft::create(ws_self::id(), $title, $_POST["type"]);
 	if($item->user_id != ws_self::id())
 		throw new RedirectException(mr::host("own")."/draft/new-".$_POST["type"].".xml", 5, "Ошибка: не удалось создать новый черновик", "Безуспешно");
 		
 	$item->setContent(
 		rtrim($_POST["content"]),
 		rtrim($_POST["epygraph"]),
 		rtrim($_POST["postscriptum"])
 	);
 	$item->write_time = mr_text_string::remove_excess(trim($_POST["write_time"]));
 	$item->write_place = mr_text_string::remove_excess(trim($_POST["write_place"]));
 	$item->first_pub = (int)mr_text_string::remove_excess(trim($_POST["first_pub"]));
 		if($item->first_pub < 1921) $item->first_pub = date("Y");
 		
 	$item->save();
 	
 	throw new RedirectException($item->href(), 8, "Новый черновик &laquo;{$item->title}&raquo; успешно создан. Чтобы просмотреть его, подождите несколько секунд. Чтобы сразу перейти к публикации, <a href=\"".mr::host("own")."/draft/pub-".$item->id().".xml\">нажмите сюда</a>.");
 }
 
 static public function draft_pub()
 {
 	if(!ws_self::ok())
 		throw new ErrorPageException("Потеряна авторизация.", 403);
 	
 	$item = ws_libro_pub_draft::factory( (int)$_POST["id"] );
 	if($item->user_id != ws_self::id())
 		throw new ErrorPageException("Чужой или несуществующий черновик.", 403);
 		
 	$meta = (int)$_POST["meta"];
 	if( !$meta )
 		throw new RedirectException(mr::host("own")."/draft/pub-".$item->id().".xml", 5, "Вы не выбрали доминанту. Публикация вне доминант невозможна", "Ошибка");
 	$meta = ws_comm::factory( $meta );
 	if(!$meta->can_add_pub( $item->type, time() ))
 		throw new RedirectException(mr::host("own")."/draft/pub-".$item->id().".xml", 5, "Вы исчерпали временные квоты для публикации в доминанте ".$meta->link().". Пожалуйста, попробуйте опубликоваться в ней позже.", "Ошибка");
 		
 	$anonymous = $_POST["anonymous"];
 	$section = (int)$_POST["section"];
 	$authmark = $_POST["authmark"];
 	if($section)
 	{
 		if( ws_libro_pub_sec::factory($section)->type != $item->type ) $section=0;
 	}
 	$auto_contest = $_POST["auto_contest"];
 		
 	$pub = ws_libro_pub_item::create( $item, $anonymous, $meta->id(), $authmark, $auto_contest, $section );
 	if(!$pub)
 		throw new RedirectException(mr::host("own")."/draft/pub-".$item->id().".xml", 5, "Не удалось сохранить новую публикацию. Техническая ошибка.", "Ошибка");
 	
 	// Цикл если не существует, то создаём, значит, можно после всего
 	$cycle_new = mr_text_string::remove_excess( trim( $_POST["cycle_new"] ) );
 	$cycle_id = (int)$_POST["cycle"];
 	if($cycle_new)
 		$cycle = ws_libro_pub_cycle::byTitle( ws_self::id(), $cycle_new );
 	elseif($cycle_id)
 		$cycle = ws_libro_pub_cycle::factory( $cycle_id );
 	
 	if( !$cycle_new && (!$cycle_id || $cycle->user()->id() != ws_self::id() ) )
 		$cycle = ws_libro_pub_cycle::byTitle( ws_self::id(), "(вне цикла)" );
 		
 	$cycle->addPub( $pub );
 		
 	// Сообщества -- после всего
 	$comms = array();
 	foreach($_POST as $k=>$v) if($v == "yes" && substr($k, 0, 5)=="comm_")
 		$comms[] = (int)substr($k, 5);
 		
 	if(count($comms))
 	{
 		$comms = ws_comm::several( $comms );
 		foreach($comms as $c) if($c->can_add_pub($pub->type, time()))
 		{
 			$editor = (int)@$_POST["editor_".$c->id()];
 			
 			if( $c->recense_method != "censor" && $c->recense_apply != "disable" )
	 		{
	 			if( $c->recense_apply != "free" && $editor==0 ) continue;
	 			if($editor!=0)
	 			{
	 				$ed_user = ws_user::factory( $editor );
	 				if( !$ed_user->is_member( $c->id() ) ) continue;
	 				if( $c->recense_apply=="private" && !$ed_user->is_member( $c->id(), ws_comm::st_curator ) ) continue;
	 			}
	 		} else $editor=0;
	 		
	 		$categ = (int)@$_POST["categ_".$c->id()];
	 		
	 		if($categ) {
	 			$categ_item = ws_comm_pub_categ::factory( $categ );
	 			if($categ_item->comm()->id() != $c->id() || $categ_item->auth_apply == "no")
	 				$categ = 0;
	 		}
	 		if(!$categ)
	 			$categ = $c->category_default;
	 			
	 		ws_comm_pub_anchor::create( $pub->id(), $categ, $c->id(), $editor );
 		}
 	}
 	
 	// В самом конце -- настройки дискуссии в доминанте, если возможны
 	if($meta->can_ch_discuss())
 		$pub->discuss = $_POST["discuss_".$meta->id()];
 		
 	$descr = mr_text_string::remove_excess( trim( $_POST["descr"] ) );
 	if($descr) $pub->description = $descr;
 		
 	$pub->save();
 	
 	throw new RedirectException($pub->pub()->href(), 5, "Ваше произведение успешно опубликовано.");
 }
 
 static public function pub_pref()
 {
 	if(!ws_self::ok())
 		throw new ErrorPageException("Потеряна авторизация.", 403);
 	
 	$item = ws_libro_pub_item::factory( (int)$_POST["id"] );
 	if($item->author != ws_self::id())
 		throw new ErrorPageException("Чужой или несуществующий черновик.", 403);
 		
 	$meta = (int)$_POST["meta"];
 	if( !$meta )
 		throw new RedirectException(mr::host("own")."/pub/pref-".$item->id().".xml", 5, "Вы не выбрали доминанту. Публикация вне доминант невозможна", "Ошибка");
 	$meta = ws_comm::factory( $meta );
 	if($item->meta != $meta->id() && !$meta->can_add_pub( $item->type, $item->time ))
 		throw new RedirectException(mr::host("own")."/pub/pref-".$item->id().".xml", 5, "В тот период Вы исчерпали временные квоты для публикации в доминанте ".$meta->link().". Пожалуйста, выберите другую доминанту.", "Ошибка");
 		
 	$section = (int)$_POST["section"];
 	$authmark = $_POST["authmark"];
 	if($section)
 	{
 		if( ws_libro_pub_sec::factory($section)->type != $item->type ) $section=0;
 	}
 	$auto_contest = $_POST["auto_contest"];
 	
 	$item->meta = $meta->id();
 	$item->authmark = $authmark;
 	$item->auto_contest = $auto_contest;
 	$item->section = $section;
 	
 	// Цикл если не существует, то создаём, значит, можно после всего
 	$cycle_new = mr_text_string::remove_excess( trim( $_POST["cycle_new"] ) );
 	$cycle_id = (int)$_POST["cycle"];
 	if($cycle_new)
 		$cycle = ws_libro_pub_cycle::byTitle( ws_self::id(), $cycle_new );
 	elseif($cycle_id)
 		$cycle = ws_libro_pub_cycle::factory( $cycle_id );
 	
 	if( !$cycle_new && (!$cycle_id || $cycle->user()->id() != ws_self::id() ) )
 		$cycle = ws_libro_pub_cycle::byTitle( ws_self::id(), "(вне цикла)" );
 		
 	if($cycle->id() != $item->cycle)
 		$cycle->addPub( $item );
 		
 	// Сообщества -- после всего
 	$comms = array();
 	foreach($_POST as $k=>$v) if($v == "yes" && substr($k, 0, 5)=="comm_")
 		$comms[] = (int)substr($k, 5);
 	$incomms = $item->pub()->comm_anchors();
 		

 	if(count($comms))
 	{
 		$comms = ws_comm::several( $comms );
 		foreach($comms as $c) if($c->can_add_pub($pub->type, time()))
 		{
 			$editor = (int)@$_POST["editor_".$c->id()];
 			
 			if( $c->recense_method != "censor" && $c->recense_apply != "disable" )
	 		{
	 			if( $c->recense_apply != "free" && $editor==0 ) continue;
	 			if($editor!=0)
	 			{
	 				$ed_user = ws_user::factory( $editor );
	 				if( !$ed_user->is_member( $c->id() ) ) continue;
	 				if( $c->recense_apply=="private" && !$ed_user->is_member( $c->id(), ws_comm::st_curator ) ) continue;
	 			}
	 		} else $editor=0;
	 		
	 		$categ = (int)@$_POST["categ_".$c->id()];
	 		
	 		if($categ) {
	 			$categ_item = ws_comm_pub_categ::factory( $categ );
	 			if($categ_item->comm()->id() != $c->id() || $categ_item->auth_apply == "no")
	 				$categ = 0;
	 		}
	 		if(!$categ)
	 			$categ = $c->category_default;
	 			
	 		if($incomms[$c->id()])
	 		{
	 			
	 			$categ;
	 			
	 		} else ws_comm_pub_anchor::create( $item->id(), $categ, $c->id(), $editor );
 		}
 	}
 	
 	// В самом конце -- настройки дискуссии в доминанте, если возможны
 	if($meta->can_ch_discuss())
 		$item->discuss = $_POST["discuss_".$meta->id()];
 		
 	$descr = mr_text_string::remove_excess( trim( $_POST["descr"] ) );
 	$item->description = $descr;
 		
 	$item->save();
 	
 	throw new RedirectException($item->pub()->href(), 5, "Настройки Вашего произведения успешно изменены.");
 }
 
	}
?>