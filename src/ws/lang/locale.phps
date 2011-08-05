<?php class ws_lang_locale {
	
 static public function get_ini_text($addr, $lang)
 {
  return file_get_contents($addr.".".$lang.".ini");
 }
 
 static public function set_ini_text($addr, $lang, $text)
 {
  return file_put_contents($addr.".".$lang.".ini", $text);
 }
	
 static public function get_ini_addr($dir)
 {
 	$ret = array();
 	$d = opendir( $dir );
 	while($f = readdir($d))
 	{
 		if(substr($f, -4) == ".ini")
 		{
 			$i = $dir."/".substr($f, 0, -7);
 			if(!in_array($i, $ret)) $ret[] = $i;
 		}
 	}
 	return $ret;
 }
 	
 static public function get_ini_diff(array $addr, $lang, $base)
 {
 	$ret = array();
 	foreach ($addr as $f) {
 		$b = parse_ini_file($f.".".$base.".ini");
 		$l = is_readable($f.".".$lang.".ini") ? parse_ini_file($f.".".$lang.".ini") : array();
 		
 		$r = array_diff_key($b, $l);
 		if(count($r))
 			$ret[$f]["add"] = $r;
 		$r = array_diff_key($l, $b);
 		if(count($r))
 			$ret[$f]["remove"] = $r;
 	}
 	return $ret;
 }
 
 static public function out_ini_diff($lang, $base)
 {
 	$r = self::get_ini_diff( self::get_ini_addr("locale"), $lang, $base );
 	
 	ob_start();
 	
 	echo "#.".$lang."\n";
 	foreach ($r as $file => $acts) {
 		echo "#:".$file."\n";
 		foreach ($acts as $action => $arr) foreach ($arr as $k=>$v) {
 			echo "#";
 			if($action == "add") echo "+\n$k = \"$v\"\n";
 			if($action == "remove") echo "-$k\n";
 		}
 	}
 	return trim(ob_get_clean());
 }
 
 static public function handle_ini_diff($plain)
 {
 	$f = explode("\n", $plain);
 	$in = array();
 	$lang = "";
 	$file = null;
 	$action = null;
 	foreach($f as $s)
 	{
 		if($s[0]=="#") switch ($s[1]) {
 			case ".":
 				$lang = substr($s, 2, 2);
 			break;
 		
 			case ":":
 				$in[ $file = trim(substr($s, 2))] = array();
 			break;
 			
 			case "+":
 				$action = "add";
 			break;
 			
 			case "-":
 				$in[ $file ][ "remove" ][] = substr($s, 2);
 			break;
 			
 		} elseif($s) {
 			
 			$in[ $file ][ $action ][] = $s;
 			
 		}
 	}
 	 	
 	if(!$lang) return false;
 	
 	foreach ($in as $file => $acts) {
 		$ini = is_readable( $file.".".$lang.".ini" ) ? parse_ini_file( $file.".".$lang.".ini" ) : array();
 		
 		foreach ($acts["add"] as $s)
 		{
 			list($n, $v) = explode(" = ", $s, 2);
 			$v = trim($v);
 			$n = trim($n);
 			if( $v[0] == "\"" ) $v = substr($v, 1);
 			if( $v[ strlen($v)-1 ] == "\"" ) $v = substr($v, 0, -1);
 			$ini[$n] = $v;
 		}
 		
 		foreach ($acts["remove"] as $s)
 		{
 			unset( $ini[ trim($s) ] );
 		}

 		$data = "";
 		foreach ($ini as $key => $value)
 			$data .= $key ." = \"".$value."\"\n";
 		
 		$f = fopen($file.".".$lang.".ini", "wb") or die("cannot open : ".$file.".".$lang.".ini<br/>");
 		fwrite($f, $data);
 		fclose($f);
 	}
 	
 	return true;
 }
	
	}
?>