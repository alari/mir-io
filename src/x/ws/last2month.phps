<?php class x_ws_last2month implements i_xmod {
	
 static public function action($x)
 {
	$r = mr_sql::qw("SELECT u.* FROM mr_users u LEFT JOIN mr_publications p ON p.author=u.id WHERE p.time>UNIX_TIMESTAMP()-86400*62 AND u.registration_time>UNIX_TIMESTAMP()-86400*62 AND p.type=?", $x);

	if(!mr_sql::num_rows($r)) die("Авторы не найдены");

	$ids = array();
	while($f = mr_sql::fetch($r, mr_sql::assoc)){
		if(!in_array($f["id"], $ids)) $ids[] = $f["id"];
		ws_user::factory($f["id"], $f);
	}
	
	$list = new mr_list("ws_user", $ids);
	
	foreach($list as $u) echo $u, "<br/>";
 }	
	}
?>