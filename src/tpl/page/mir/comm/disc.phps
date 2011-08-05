<?php class tpl_page_mir_comm_disc extends tpl_page_mir_comm_inc implements i_tpl_page_rightcol, i_locale, i_tpl_page_ico {

	protected $disc, $perpage=20, $page=0, $total=0;

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

 	$disc = ws_comm_disc::factory($params[1]);
 	$this->disc = $disc;

 	if(!$disc->is_showable())
 		throw new ErrorPageException(self::$locale["not_found"], 404);

 	if($disc->comm()->id() != $this->comm->id())
 		throw new RedirectException($disc->href());


 	$this->title = $disc->title.($disc->description?" - ".$disc->description:"");

 	$this->description = $this->title . " - ".$disc->comm()->title." ".$disc->comm()->description;

 	ob_start();

 ?>
 <h1><?=$disc->title?></h1>
 <h2><?=$disc->description?></h2>
 	<br />
 <?

 	$threads = $disc->getThreads($this->page=(int)$params["page"], $this->perpage, $calc);

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

 			echo '&nbsp; ', $p == $this->page ? "<b>".($this->page+1)."</b>" : '<a href="'.$disc->href($p).'">'.($p+1).'</a>';
 			$prev = $p;
 		}

 		echo "</div>";

 		$pager = ob_get_flush();
 	}

 	tpl_fr_threads::outlist($threads);

 	echo $pager;

 	$this->content = ob_get_contents();
 	ob_end_clean();

 	$this->css[] = "soc/thread.css";
 }

 public function col_right()
 {
 	ob_start();

 	echo "<p>Сообщество: ", $this->comm, "</p>";
 	echo "<p>Раздел: ", $this->disc, "</p>";

 	if($this->disc->can_add_thread())
 		echo "<p>", "<a href=\"new-thread.in-".$this->disc->id().".xml\">Создать новую дискуссионную ветку</a>", "</p>";

 	echo tpl_page_mir_comm_disc_inc::make_rc($this->comm->id(), $this->disc->id(), true);

	$r = ob_get_clean();
	return $r;
 }

 public function p_ico()
 {
 	return "disc";
 }

	}
?>