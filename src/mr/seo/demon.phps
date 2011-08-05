<?php class mr_seo_demon {
	
  static protected $sqlTable = "mr_seo_demon";
	
 static public function handle(i_tpl_page &$page)
 {
  $referer = parse_url(@$_SERVER["HTTP_REFERER"]);
  if($referer && @$referer["host"])
  {
	  $se = "";
	  $q = self::get(@$referer["query"]);
	  
	  if( strstr($referer["host"], "yandex" ))
	  {
	  	$se = "yandex";
	  	$q = $q["text"];
	  	
	  } elseif( strstr($referer["host"], "google") )
	  {
	  	$se = "google";
	  	$q = $q["q"];
	  	
	  } elseif( strstr($referer["host"], "rambler") )
	  {
	  	$se = "rambler";
	  	$q = $q["words"];
	  	
	  } elseif( strstr($referer["host"], "gogo") )
	  {
	  	$se = "gogo";
	  	$q = $q["q"];
	  	
	  }
	  
	  if($se && $q)
	  {
	  	if( mr_text_string::no_ru_sp( $q ) )
	  		$t = iconv("cp1251", "utf-8", $q);
	  	$q = $t?$t:$q;
	  	
	  	mr_sql::qw("UPDATE ".self::$sqlTable." SET hits=hits+1 WHERE request=? AND engine=? AND url=?",
	  		$q, $se, $_SERVER['REQUEST_URI']);
	  	
	  		
	  	if( !mr_sql::affected_rows() )
	  		mr_sql::qw("INSERT INTO ".self::$sqlTable."(request, engine, url, title) VALUES(?,?,?,?)",
	  			$q, $se, $_SERVER['REQUEST_URI'], $page->title());
	  }
  }
 }
 
 static public function get($string)
 {
  $arr = explode("&", $string);
  $get = array();
  foreach($arr as $a)
  {
  	@list($n, $v) = @explode("=", $a);
  	$get[$n] = urldecode($v);
  }
  return $get;
 }
	
}?>