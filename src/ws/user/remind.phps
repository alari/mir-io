<?php class ws_user_remind {
	const method_email = 1;
	const method_reminder = 2;
	
	const type_pub_resp = 1;
	const type_society_thread_notes = 2;
	const type_blog_resp = 3;
	const type_comm_rec_request = 4;
	const type_user_pub = 5;
	const type_comm_event_resp = 6;
	const type_dev_note = 6;
	
	static private $is_signed = array(), $check = null;
	
/**
 * Произошло событие типа $type в разделе $chapter. Возможно,
 * это старое событие. Тогда указывается параметр $time
 *
 * @param const $type
 * @param int $chapter
 * @param string $eml_title
 * @param string $eml_body
 * @param int $user
 * @param timestamp[opt] $time
 */
 static public function event($type, $chapter, $eml_title, $eml_body, $user, $time=false)
 {
 	if(!$time) $time = time();
 	mr_sql::qw("UPDATE mr_reminders SET new=new+1 WHERE time<=? AND method=? AND user_id!=? AND type=? AND target=?",
 		$time, self::method_reminder, $user, $type, $chapter);
 		
 	$r = mr_sql::qw("SELECT user_id FROM mr_reminders WHERE time<? AND method=? AND user_id!=? AND type=? AND target=?",
 		$time, self::method_email, $user, $type, $chapter);
 		
 	$usrs = array();
 	while($u = mr_sql::fetch($r, mr_sql::get)) $usrs[] = $u;
 	ws_user::several($usrs);
 	foreach($usrs as $u)
 		mail(ws_user::get($u, "email"), $eml_title, $eml_body, "From: noreply@mirari.ru
Content-type: text/plain; charset=utf-8");
 }
 
/**
 * Удалено или скрыто событие типа $type в разделе $chapter,
 * датированное временем $time
 *
 * @param const $type
 * @param int $chapter
 * @param timestamp $time
 */
 static public function decrement($type, $target, $time)
 {
 	mr_sql::qw("UPDATE mr_reminders SET new = IF(new>0,new-1,0) WHERE type=? AND target=? AND time<?",
 		$type, $target, $time);
 }
 
/**
 * Текущим пользователем просмотрено событие типа $type в
 * разделе $chapter, новинки выставляем в ноль
 *
 * @param const $type
 * @param int $chapter
 */
 static public function zeroize($type, $chapter)
 {
 	mr_sql::qw("UPDATE mr_reminders SET new=0 WHERE user_id=? AND type=? AND target=? AND temp='no' LIMIT 1",
 		ws_self::id(), $type, $chapter);
 	if(!mr_sql::affected_rows())
 		mr_sql::qw("DELETE FROM mr_reminders WHERE user_id=? AND type=? AND target=? AND temp='yes' LIMIT 1",
 			ws_self::id(), $type, $chapter);
 }
 
/**
 * Удалён раздел $chapter типа $type, или пользователь $user
 * отказался от подписки на него
 *
 * @param const $type
 * @param int $chapter
 * @param int[optional] $user
 */
 static public function delete($type, $chapter, $user=null)
 {
	mr_sql::qw("DELETE FROM mr_reminders WHERE type=? AND target=?".($user?" AND user_id=$user LIMIT 1":""),
		$type, $chapter);
 }

/**
 * Создаёт подписку на события типа $type в разделе $chapter
 * для пользователя $user. Если подписка и так существует, то обновляет её.
 * Временные строчки удаляются при zeroize, а не обновляются
 *
 * @param const $type
 * @param int $chapter
 * @param int $user
 * @param const $method
 * @param bool $temp=false Временная строчка
 */
 static public function create($type, $chapter, $user, $method, $temp=false)
 {
 	$c = mr_sql::fetch(array("SELECT COUNT(*) FROM mr_reminders WHERE user_id=? AND type=? AND target=?",
 		$user, $type, $chapter), mr_sql::get);
 	if($c>1) mr_sql::qw("DELETE FROM mr_reminders WHERE user_id=? AND type=? AND target=?",
 		$user, $type, $chapter);
 	if(!$c) mr_sql::qw("INSERT INTO mr_reminders(user_id, type, target, method, temp, time, new) VALUES(?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?)",
 		$user, $type, $chapter, $method, $temp?"yes":"no", $temp?1:0);
 }
 
/**
 * Проверяет, подписался ли пользователь на событие
 * Возвращает тип подписки
 *
 * @param const $type
 * @param int $chapter
 * 
 * @return const
 */
 static public function is_signed($type, $chapter)
 {
 	if(!is_int(self::$is_signed[$type."/".$chapter]))
 	{
 		self::$is_signed[$type."/".$chapter] = (int)mr_sql::fetch(array("SELECT method FROM mr_reminders WHERE user_id=? AND type=? AND target=? LIMIT 1",
 			ws_self::id(), $type, $chapter), mr_sql::get);
 	}
 	return self::$is_signed[$type."/".$chapter];
 }
 
/**
 * Возвращает true, если у пользователя есть непрочитанные оповещения,
 * false иначе
 *
 * @return bool
 */
 static public function check()
 {
 	if(self::$check === null)
 	{
 		self::$check = mr_sql::fetch("SELECT SUM(new) FROM mr_reminders WHERE user_id=".ws_self::id(), mr_sql::get);
 	}
 	return self::$check;
 }
 
 /**
  * Возвращает список подписавшихся на событие
  *
  * @param const $type
  * @param int $chapter
  * @return array
  */
 static public function subscribers($type, $chapter)
 {
 	$ret = array();
 	$r = mr_sql::qw("SELECT user_id FROM mr_reminders WHERE type=? AND target=?", $type, $chapter);
 	while($w = mr_sql::fetch($r, mr_sql::get)) $ret[] = $w;
 	return $ret;
 }
 
/**
 * Возвращает тупейший список подписок пользователя
 *
 * @param int $user_id
 * @param bool $new
 * @return array
 */
 static public function usr_subscribes($user_id, $new=false)
 {
 	$r = mr_sql::qw("SELECT * FROM mr_reminders WHERE user_id=?".($new?" AND new>0":"")." ORDER BY new DESC", $user_id);
 	$arr = array();
 	
 	while($f = mr_sql::fetch($r, mr_sql::obj)) $arr[] = $f;
 	
 	return $arr;
 }
 
/**
 * Меняет статус подписки
 *
 * @param const $type
 * @param int $target
 * @param yes|no $sub
 * @param const $method
 * @param int $user
 * @return bool
 */
 static public function change_sub($type, $target, $sub, $method, $user=0)
 {
 	if(!$user) $user = ws_self::id();
 	
 	$now = self::is_signed($type, $target);
	   
	if($now && $sub=="no")
	  	self::delete($type, $target, $user);
	elseif($sub=="yes" && $method!=$now)
	   self::create($type, $target, $user, $method);
	else
		return false;
		
	return true;
 }
	}
?>