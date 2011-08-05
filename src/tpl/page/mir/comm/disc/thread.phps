<?php class tpl_page_mir_comm_disc_thread extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_locale, i_tpl_page_ico {
	
	protected $thread, $perpage=20, $page=0, $total=0;
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
	
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	
 	/* @var $thread ws_comm_thread_item */
 	$thread = ws_comm_disc_thread::factory($params[1]);
 	$this->thread = $thread;
 	
 	if(!$thread->is_showable())
 		throw new ErrorPageException(self::$locale["not_found"], 404);
 		
 	if($thread->comm()->id() != $this->comm->id())
 		throw new RedirectException($thread->href());
 	
 	$thread->increment_view();
 		
 	$this->title = $thread->title.($thread->description?" - ".$thread->description:"");
 	
 	ob_start();
 	
 ?>
 <h1><?=$thread->title?></h1>
 <h2><?=$thread->description?></h2>
 	<br />
 <?
 
 	$notes = $thread->getNotes($this->page=(int)$params["page"], $this->perpage, $calc);
 
 	$this->total = floor(($calc-1) / $this->perpage);
 	
 	if($this->page || $this->total > $this->page)
 	{
 		ob_start();
 		
 		echo "<div class=\"pager\">Страницы: ";
 		
 		$pages = ws_pager::arr($this->page, $this->total);
 		$prev = 0;
 		
 		foreach($pages as $p)
 		{
 			if($p-$prev > 2)
 				echo " ...";
 				
 			echo '&nbsp; ', $p == $this->page ? "<b>".($this->page+1)."</b>" : '<a href="'.$this->thread->href($p).'">'.($p+1).'</a>';
 			$prev = $p;
 		}
 		
 		echo "</div>";
 		
 		$pager = ob_get_flush();
 	}
 
 	tpl_fr_comment::outlist($notes);
 	tpl_fr_comment::add("/x/ajax-disc/note", $thread->id());
 	
 	echo $pager;
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "comment.css";
 	$this->head .= "<script src=\"/style/js/comment.js\" type=\"text/javascript\"></script>";
 }
 
 public function col_right()
 {
 	ob_start();
	
 	echo "<p>Сообщество: ", $this->comm, "</p>";
 	if($this->thread->category())
 	{
?>
<p>Обсуждаемые произведения:</p>
<center><b><?=$this->thread->category()?></b></center>
<?
 	}
 	echo "<p>Раздел: ", $this->thread->disc(), "</p>";
 	
 	if( ws_self::ok() && ($this->thread->can_ch_vis() || $this->thread->can_close() || $this->thread->can_delete()) )
 		echo "<p><a href=\"javascript:void(0)\" onclick=\"javascript:mr_Ajax({url:'/x/ajax-disc/thread/adm', data:{id:".$this->thread->id()."},update:$(this).getParent()}).send()\">Администрирование ветки</a></p>";
 	
 	if($this->thread->disc()->can_add_thread())
 		echo "<p>", "<a href=\"new-thread.in-".$this->thread->disc()->id().".xml\">Создать новую дискуссионную ветку</a>", "</p>";
 	
 	echo tpl_page_mir_comm_disc_inc::make_rc($this->comm->id(), $this->thread->disc()->id(), true);
 	
	$r = ob_get_clean();
	return $r;
 }
 
 public function p_ico()
 {
 	return "disc";
 }
	
	}
?>