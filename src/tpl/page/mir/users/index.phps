<?php class tpl_page_mir_users_index extends tpl_page implements i_tpl_page_ico, i_tpl_page_submenu {
	
	protected $user, $page, $perpage=20;
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	/* @var $user ws_user */
 	$user = ($this->user = ws_user::getByLogin(mr::subdir()));

 	if(!$user)
 		throw new ErrorPageException("Пользователь не найден", 404);
 	
 	$bt = $user->blog_title;
 	$bt = $bt ? $bt : "Дневник";
 		
 	$this->title = $bt;
 	
 	$this->page = (int)@$params[2];
 	if(ws_self::ok() && ws_self::self()->blog_perpage > 0)
 		$this->perpage = ws_self::self()->blog_perpage;
 	 	
	$items = ws_blog_item::several_visible( $user->id(), $this->perpage, $this->page*$this->perpage, $count );
	
	if(!count($items))
		throw new RedirectException($user->href($count ? "" : "profile"));
		
 	ob_start();
 	
?>

	<h1><?=$bt?></h1>
	<h2>Ведёт: <?=$this->user?></h2>

<?
	
	if($this->page*$this->perpage+count($items) < $count || $this->page)
 	{
 	
 		ob_start();
 		
 		echo "<div class=\"pager\">".ws_pager::title().": ";
 		
 		$pages = ws_pager::arr($this->page, floor($count/$this->perpage)-1);
 		$prev = 0;
 		
 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";
 				
 			echo '&nbsp; ', $p == $this->page ? "<b>".($this->page+1)."</b>" : '<a href="'.$user->href($p?"page-".($p):"").'">'.($p+1).'</a>';
 			$prev = $p;
 		}
 			
 		echo "</div>";
 		
 		$pager = ob_get_flush();
 	}
	
	
	tpl_fr_blog::outlist($items);
	
	echo $pager;
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "blog/list.css";
 }
 
 public function p_ico()
 {
 	return "blogs";
 }
 	
 public function p_submenu()
 {
 	$ret = array();
 	
 	$ret[ mr::host("blogs") ] = "Новое в Дневниках";
 	
 	if(ws_self::ok())
 	{
 		$ret[mr::host("own")."/blog.xml"] = "Добавить запись";
 		$ret[ ws_self::self()->href("") ] = ws_self::self()->blog_title ? ws_self::self()->blog_title : "Ваш дневник";
 		
 		if(ws_self::self()->is_allowed("circle"))
 			$ret[ mr::host("own")."/circle/blogs.xml" ] = "Дневники Круга Чтения";
 	}
 	
 	return $ret;
 }
	}
?>