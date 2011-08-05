<?php class x_ws_own_blog extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function add()
 {
 	if(!ws_self::ok())
 		throw new RedirectException(mr::host("own"), 5, "Вы не авторизованы", "Ошибка");
 		
 	$content = $_POST["content"];
 	if(strlen(trim($content))<8)
 		throw new RedirectException(mr::host("own")."/blog.xml", 5, "Не обнаружена содержательная часть записи", "Ошибка");
 		
 	$title = mr_text_string::remove_excess(trim($_POST["title"]));
 	$mood = mr_text_string::remove_excess(trim($_POST["mood"]));
 	$music = mr_text_string::remove_excess(trim($_POST["music"]));
 	
 	$b = ws_blog_item::create(ws_self::id(), $title, $_POST["vis"], $_POST["resp"], $content);
 	if(!$b)
 		throw new RedirectException(mr::host("own")."/blog.xml", 5, "Не удалось сохранить данные новой записи", "Ошибка");
 		
 	$bms = $_POST["bm"];
 	foreach ($bms as $bm) if($bm && is_numeric($bm)) $bm_ids[] = $bm;
 	
 	if(count($bm_ids))
 	{
 		$bms = ws_blog_bm::several($bm_ids);
 		foreach ($bms as $bm) $b->addBM( $bm );
 	}
 	
 	$b->title = $title;
 	$b->mood = $mood;
 	$b->music = $music;
 	
 	$b->save();
 	
 	throw new RedirectException($b->href(), 5, "Запись в дневнике успешно сохранена");
 }
 
 static public function edit()
 {
 	if(!ws_self::ok())
 		throw new RedirectException(mr::host("own"), 5, "Вы не авторизованы", "Ошибка");
 		
 	$ed_id = (int)@$_POST["id"];
 		
 	$item = ws_blog_item::factory($ed_id);
 		if(!$item->time)
 			throw new ErrorPageException("Запись не найдена", 404);
 		
 		if(!$item->is_editable())
 			throw new ErrorPageException("Нет прав на правку этой записи", 404);
 	
 	$content = $_POST["content"];
 	if(strlen(trim($content))<8)
 		throw new RedirectException(mr::host("own")."/blog.edit-$ed_id.xml", 5, "Не обнаружена содержательная часть записи", "Ошибка");
 		
 	$trans = new mr_text_trans($content);
 	$trans->t2x( mr_text_trans::plain );
 	
 	$item->size = $trans->getAuthorSize();
 	ws_attach::checkXML( $item->content, ws_attach::decrement );
 	$c = $trans->finite();
 	ws_attach::checkXML( $c, ws_attach::increment );
 	$item->content = $c;
 		
 	$item->title = mr_text_string::remove_excess(trim($_POST["title"]));
 	$item->mood = mr_text_string::remove_excess(trim($_POST["mood"]));
 	$item->music = mr_text_string::remove_excess(trim($_POST["music"]));
 	
 	$item->visibility = $_POST["vis"];
 	$item->responses = $_POST["resp"];
 	
 	$item->save();
 		
 	$curbms = $item->bms();
 	$bms_checked = array();
 	foreach ($curbms as $cbms) $bms_checked[] = $cbms->bm_id;
 		
 	$bms = $_POST["bm"];
 	foreach ($bms as $bm) if($bm && is_numeric($bm)) $bm_ids[] = $bm;
 	
 	if(count($bm_ids))
 	{
 		$bms = ws_blog_bm::several($bm_ids);
 		foreach ($bms as $bm) if(!in_array($bm, $bms_checked)) $item->addBM( $bm );
 	}
 	foreach ($curbms as $bm) if(!in_array($bm->id(), $bm_ids))
 		$bm->delete();
 	
 	throw new RedirectException($item->href(), 5, "Запись в дневнике успешно сохранена");
 }
 
	}
?>