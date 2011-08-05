<?php class x_ws_dev_ticket extends x implements i_xmod {

 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }

 static public function create()
 {
 	$project = (int)$_POST["project"];

 	if(!($projObj=ws_dev_project::factory($project)) instanceof ws_dev_project)
 		throw new ErrorPageException("Проект не найден", 404);

 	$title = trim(mr_text_string::remove_excess( $_POST["title"] ));
 	$content = trim( $_POST["content"] );

 	if(!$title || !$content)
 		throw new RedirectException(mr::host("dev")."/newticket.in-$project.xml", 5, "Ошибка: не указано название или описание тикета", "Ошибка");

 	$priority = (int)$_POST["priority"]; if(!$priority) $priority = ws_dev_ticket::priority;
 	$type = (int)$_POST["type"]; if(!$type) $type = ws_dev_ticket::type;
 	$parent = (int)$_POST["parent"];
 	if($parent)
 	{
 		$p = ws_dev_ticket::factory($parent);
 		if(!$p || !$p->isModule() || $p->project != $project)
 			$parent = 0;
 	}

 	$ticket = ws_dev_ticket::create($project, $title, $content, $type, $priority, $parent);


 	if($ticket && $ticket->id) throw new RedirectException($ticket->href());
 	else throw new RedirectException(mr::host("dev")."/newticket.in-$project.xml", 50, "Не удалось создать тикет", "Ошибка");
 }

 static public function project()
 {
 	$title = trim(mr_text_string::remove_excess( $_POST["title"] ));

 	if(!$title)
 		throw new RedirectException(mr::host("dev")."/newproject.xml", 5, "Ошибка: не указано название нового проекта", "Ошибка");

 	$project = ws_dev_project::create($title);

 	if($project && $project->id) throw new RedirectException($project->href());
 	else throw new RedirectException(mr::host("dev")."/newproject.xml", 5, "Не удалось создать новый проект", "Ошибка");
 }

 static public function modify()
 {
 	$ticket = ws_dev_ticket::factory((int)$_POST["ticket"]);

 	if(!$ticket->is_showable())
 		throw new ErrorPageException("Access denied", 403);

 	$title = trim(mr_text_string::remove_excess($_POST["title"]));

 	$status = $_POST["status"];

 	$priority = (int)$_POST["priority"]; if(!$priority) $priority = $ticket->priority;
 	$type = (int)$_POST["type"]; if(!$type) $type = $ticket->type;
 	$module = (int)$_POST["parent"];
 	if($module)
 	{
 		$p = ws_dev_ticket::factory($module);
 		if(!$p || !$p->isModule() || $p->project != $ticket->project || $ticket->isModule())
 			$module = 0;
 	}

 	$toch = array(
 		"title",
 		"priority",
 		"type",
 		"status",
 		"module"
 	);

 	$ch = array();

 	foreach($toch as $t)
 	if($ticket->$t != $$t)
 	{
 		$ticket->$t = $$t;
 		$ch[$t] = $$t;
 	}

 	if(count($ch)) $ticket->save();

 	$content = trim( $_POST["content"] );

 	if( $content ) $ch["content"] = mr_text_trans::text2xml($content, mr_text_trans::plain);

 	if(!count($ch)) throw new RedirectException($ticket->href(), 5, "Ничего не было изменено", "Ошибка");

 	$note = ws_dev_note::create($ticket->id(), ws_self::id());
 	if($note instanceof ws_dev_note && $note->id())
 	{
 		foreach($ch as $k=>$v) $note->$k = $v;
 		$note->save();
 		throw new RedirectException($ticket->href(), 5, "Изменения сохранены");
 	}
 	throw new RedirectException($ticket->href(), 5, "Ошибка при сохранении логов действия и комментария", "Ошибка");
 }

	}
?>