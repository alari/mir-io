<?php
	class ws_dev_note extends ws_abstract_comment {
  
		const sqlTable = "mr_dev_notes";

 static public function getAll($ticket)
 {
 	$calcResult = false;
 	return parent::sub_several(__CLASS__, "ticket_id=".$ticket, 0, 0, "time", $calcResult);
 }
 
/**
 * Factory project
 *
 * @param int $id
 * @param array $arr
 * @return ws_dev_note
 */
 static public function factory($id, $arr=false)
 {
 	return parent::sub_factory(__CLASS__, $id, $arr);
 }

/**
 * Создаёт новое сообщение
 *
 * @param int $ticket_id Родительский тикет
 * @param int $user_id
 * @return ws_dev_note
 */
 static public function create($ticket_id, $user_id)
 {
  mr_sql::qw("INSERT INTO ".self::sqlTable."(user_id, ticket_id, remote_addr, time) VALUES(?, ?, ?, UNIX_TIMESTAMP())",
  	$user_id, $ticket_id, $_SERVER['REMOTE_ADDR']);
  	
  	return self::factory(mr_sql::insert_id());
 }
     
/**
 * Родительский тикет
 *
 * @return ws_dev_ticket
 */
 public function ticket()
 {
  return ws_dev_ticket::factory($this->arr["ticket_id"]);
 }
   
/**
 * Отправляет оповещение подписчикам об этом отзыве
 *
 */
 public function notify_subscribers()
 {
 	
 }
 
/**
 * Удаляет отзыв
 *
 * @return false
 */
 public function delete()
 {
	return false;
 }
 
 /**
 * Проверяет права пользователя и выводит, может ли сообщение быть показано
 *
 */
 public function is_showable()
 {
 	return $this->ticket()->is_showable();
 }
 
/**
 * Права администратора
 *
 */
 public function can_edit()
 {
 	return false;
 }
 
 public function can_hide()
 {
 	return false;
 }
 
 public function can_delete()
 {
 	return false;
 }
 
 public function parent_link()
 {
 	return "Тикет: ".$this->ticket()->link();
 }
 
 public function out_pre()
 {
 	$toch = array(
 		"title" => "Название",
 		"priority" => "Приоритет",
 		"type" => "Тип",
 		"status" => "Статус",
 		"module" => "Модуль",
 		"assignee" => "Ответственный"
 	);
 	$output_started = false;
 	
 	foreach($toch as $k=>&$t) if($this->$k){
 		
 		if(!$output_started)
 		{
 			ob_start();
 			$output_started = 1;
 			echo "<p class=\"comment-rec\"><ul>";
 		}
 		
 		echo "<li>", $t, ": <i>";
 		
 		if($k == "title") echo $this->title;
 		elseif($k == "priority") echo ws_dev_ticket::getPriority($this->priority);
 		elseif($k == "type") echo ws_dev_ticket::getType($this->type);
 		elseif($k == "status") echo ws_dev_ticket::getStatus($this->status);
 		elseif($k == "module") echo ws_dev_ticket::factory($this->module);
 		elseif($k == "assignee") echo ws_user::factory($this->assignee);
 		
 		echo "</i></li>";
 	}
 	
  	if($output_started) return ob_get_clean()."</ul></p>";
 
 	 return "";
 }
 
 /**
 * Осуществляет выборку нескольких записей одним запросом
 *
 * @param string|array $where="1=1" where-запрос или массив полей id
 * @param int $limit Количество выбранных записей
 * @param int $offset Смещение в результате
 * @param string $order="time DESC"
 * @param int &$calcResult=false Если переменная передана, то в неё запишется полное количество строчек в запросе
 * @return mr_list массив объектов сообщений
 */
 static public function several($where="1=1", $limit=0, $offset=0, $order="time DESC", &$calcResult=false)
 {
 	return;
 }
 
/**
 * Осуществляет выборку нескольких записей одним запросом
 *
 * @param string $query
 * @param int &$calcResult=false Если переменная передана, то в неё запишется полное количество строчек в запросе
 * @return mr_list массив объектов сообщений
 */
 static public function several_query($query, &$calcResult=false)
 {
 	return;
 }
		}
?>