<?php class x_ws_site_ban extends x implements i_xmod {

	static $user;

 static public function action($x)
 {
 	if(!ws_self::is_member(1, ws_comm::st_curator))
 		die("#1/Недостаточно прав.");

 	self::$user = ws_user::factory((int)$_POST["user"]);

 	if(self::$user->is_member(1, ws_comm::st_curator))
 		die("#2/Недостаточно прав.");

 	if(self::$user->id() == ws_user::anonime)
 		die("#3/Недостаточно прав.");

 	parent::call($x, __CLASS__);
 }

 static public function forever() {
 	$reason = $_POST["reason"];

 	if(!$reason) $reason = "Высокоэнтропийность (См. Правила Сайта, 5.3 / 5.4)";

	$ban = ws_user_ban::create(self::$user->id(), ws_self::id(), $reason);
	if(!$ban)
		die("#4/Ошибка");

	$ban->till = time()*2;
	$ban->save();

	self::$user->banned_till = $ban->till;
	self::$user->banned_reason = $ban->reason;

	self::$user->save();

	mr_sql::query("DELETE FROM mr_sessions WHERE user_id=".self::$user->id());

	die("Побанен по причине '$reason'");
 }

 static public function eventually() {
 	$reason = $_POST["reason"];

 	if(!$reason)
 		die("Необходимо указать причину бана со ссылкой на Правила Сайта");

 	$ban = ws_user_ban::create(self::$user->id(), ws_self::id(), $reason);
	if(!$ban)
		die("#5/Ошибка");

	self::$user->banned_till = $ban->till;
	self::$user->banned_reason = $ban->reason;

	self::$user->save();

	mr_sql::query("DELETE FROM mr_sessions WHERE user_id=".self::$user->id());

	die("Побанен по причине '$reason'");
 }

	}