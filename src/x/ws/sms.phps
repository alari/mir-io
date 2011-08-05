<?php class x_ws_sms extends x implements i_xmod, i_initiate {
	
	static private $smsid, $num, $operator, $user_id, $cost, $msg, $skey;
	
	static public function init($ini)
	{
		foreach ($ini as $key => $value) {
			self::$skey[$key] = md5($value);
		}
	}
	
 static public function action($x)
 {
 	self::$smsid = @$_GET["smsid"];
 	self::$num = @$_GET["num"];
 	self::$operator = @$_GET["operator"];
 	self::$user_id = @$_GET["user_id"];
 	self::$cost = @$_GET["cost"];
 	self::$msg = @$_GET["msg"];
 	
 	header("Content-type: text/plain");
 	
   $r = "smsid: " . self::$smsid . "\n";
   $r.= "status: reply\n";
   $r.= "content-type: text/plain\n";
   $r.= "\n";
   
    if(isset(self::$skey[$x]) && self::$skey[$x] != $_GET["skey"])
    	die("Trying to unauthorize request failed.");
 	
 	return $r.self::call($x, __CLASS__)."\n";
 }
 
 static public function club()
 {
	list(, $login, ) = explode(" ", self::$msg, 3);
	$u = ws_user::getByLogin($login);
	
	if(!$u->login)
	 return "Polzovatelya $login ne sushestvuet";
	 
	$price = self::$cost * 24;
	$time = ceil(86400 * 30 * $price / 200);
	
	$u->in_club = ($u->in_club>time() ? $u->in_club : time()) + $time;
	$u->user_group = 7;
	$u->save();
	
	mr_sql::qw("INSERT INTO mr_user_finance(user_id, cash, comment, time) VALUES(?, ?, ?, UNIX_TIMESTAMP())",
 						$u->id(), $price, "sms: ".self::$user_id." / ".self::$operator." / $".self::$cost." / ".self::$num." / ".self::$msg);
	
	return "Vznos zachislen do ".date("d.m.Y", $u->in_club);
 }
 
 static public function pub_vote()
 {
	list(, $id, ) = explode(" ", self::$msg, 3);
	$p = ws_libro_pub_item::factory($id);
	if( !$p->meta )
	 return "Proizvedenie $id ne najdeno";
	 
	$price = self::$cost * 24;
	$time = ceil(86400 * 30 * $price / 200);
	
	$p->sms_cash += self::$cost;
	$p->save();
	
	$u = $p->pub()->author(true);
	
	$u->in_club = ($u->in_club>time() ? $u->in_club : time()) + $time;
	$u->user_group = 7;
	$u->save();
	
	mr_sql::qw("INSERT INTO mr_user_finance(user_id, cash, comment, time) VALUES(?, ?, ?, UNIX_TIMESTAMP())",
 						$u->id(), $price, "pubvote sms: ".self::$user_id." / ".self::$operator." / $".self::$cost." / ".self::$num." / ".self::$msg);
	
	return "Vash golos prinyat";
 }
	
	}
?>