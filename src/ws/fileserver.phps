<?php class ws_fileserver extends mr_ftp {

 public function __construct()
 {
 	parent::__construct("s-mirari0.1gb.ru", "http", "1gb_s-mirari0", "afe49129");
 }

 public function put_rename($local_filename, $action, $id, $remote_filename)
 {
 	if(!parent::put("tmp/$action$id.tmp", $local_filename)) return false;

 	$ren = file("http://s-mirari0.1gb.ru/rename.php?type=$action&old=$id&new=".urlencode(iconv("utf-8", "cp1251", $remote_filename)));
 	foreach($ren as &$f) $f = iconv("cp1251", "utf-8", $f);

 	return $ren;
 }

	}