<?php class x_ws_redirect implements i_xmod {
	
 static public function action($x)
 {
 	
 	$arr = explode("/", $_SERVER['QUERY_STRING']);
 	
 	$href = "http://mir.io/";
 	
 	switch($arr[0])
 	{
 		case "disc":
 			switch($arr[1])
 			{
 				case "thread":
 					$href = ws_comm_disc_thread::factory( $arr[2] )->href( (int)$arr[3] );
 				break;
 				case "chapter":
 					$href = ws_comm_disc::factory( $arr[2] )->href( (int)$arr[3] );
 				break;
 			}
 		break;
 	}
 	
 	throw new RedirectException($href);
 	
 }	
	}
?>