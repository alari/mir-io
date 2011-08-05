<?php class ws_pager implements i_locale {
	
 static protected $locale = array(), $lang = "";
	
 static public function locale($data, $lang)
 {
	self::$locale = $data;
	self::$lang = $lang;
 }
	
 static public function arr($current, $total, $disp=3)
 {
 	$arr = array();
 	
 	for($i=0; $i<$disp && $i<=$total; $i++)
 		$arr[] = $i;
 	
 	if($current-$disp - $arr[count($arr)-1] == 2)
 		$arr[] = $arr[count($arr)-1]+1;
 		
 	for($i=max($current-$disp, $disp); $i<$current+$disp+1 && $i<=$total; $i++)
 		$arr[] = $i;
 	
 	if($total-$disp - $arr[count($arr)-1] == 1)
 		$arr[] = $arr[count($arr)-1]+1;
 		
 	for($i=max($total-$disp+1, $current+$disp+1); $i<=$total; $i++)
 		$arr[] = $i;
 	
 	return $arr;
 	
 }
 
 static public function title()
 {
 	return self::$locale["pages"];
 }
	
	}
?>