<?php class tpl {
 static public function handle($nonhandled_url=null)
 { 	
  // Ставим урл в окружение для разбора
  $url = mr::set_url($nonhandled_url);
  
  // Разбираем параметры
  eregi("([^.]+)\.?(.*)?(\.xml|\.ml)", $url, $POCKETS);

  $filename = $POCKETS[1].$POCKETS[3];
  
  $params = array();
  
  if($filename)
  {
	  $paramsstring = $POCKETS[2];
	  
	  if($paramsstring)
	  {
	  	$paramsstring=@explode(".", $paramsstring);
	   foreach($paramsstring as $p)
	   {
	    @list($param_name, $param_value)=@explode("-", $p, 2);
	    $params[$param_name] = urldecode($param_value);
	   }
	  }
  } else $filename = $url;
  // Поиск класса, ответственного за страничку
  $ini = mr::ini("site.".mr::site());
  $class = "";
  if(is_array($ini)) foreach($ini as $k=>$v) if(strpos($k,"."))
  {
  	@list($type, $p) = @explode(".", $k, 2);
  	if($type == "match") continue;
  	if($type == "page" && $p == $filename)
  	{
  		$class = $v;
  		break;
  	}
  	if($type == "reg" && eregi("^".$v."$", $filename, $POCKETS))
  	{
  		$params = array_merge($POCKETS, $params);
  		$class = $ini["match.".$p];
  		break;
  	}
  	if($type == "class" && eregi("^".$v."$", $filename, $POCKETS))
  	{
  		$params = array_merge($POCKETS, $params);
  		$class = $p;
  		break;
  	}
  }
  if(!$class)
  {
  	if(mr_sql::fetch(array("SELECT COUNT(*) FROM mr_site_freepages WHERE url=? AND site IN(?, '')", $filename, mr::site()), mr_sql::get))
  	{
  		$class = "tpl_page_free";
  	} else {
  		list($c, ) = explode(".", $filename, 2);
  		$class = "tpl_page_".mr::site()."_".str_replace("/", "_", $c);
  	}
  } else $class = "tpl_page_".mr::site()."_".$class;
  
  // Обработка странички с проверкой всех интерфейсов
    
  if(class_exists($class, true))
  {
  	$page = new $class($filename, $params);
  	if($page instanceof i_tpl_page)
  	{
  		$layout = $page->layout();
  		if($layout instanceof i_tpl_layout)
  		{
  			return $layout->realize();
  		}
  	}
  }
  
  // Обработка не удалась -- ошибка 404
  throw new ErrorPageException("Страничка не найдена", 404);
 }
	}
	
	interface i_tpl_page_rightcol{
		public function col_right();
	}
	
	interface i_tpl_page_leftcol{
		public function col_left();
	}
	
	interface i_tpl_page_comm{
		public function p_comm();
	}
	interface i_tpl_page_user{
		public function p_user();
	}
	interface i_tpl_page_ico{
		public function p_ico();
	}
	interface i_tpl_page_submenu{
		public function p_submenu();
	}
?>