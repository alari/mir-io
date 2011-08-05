<?php class tpl_page_mir_users_blog_item extends tpl_page implements i_tpl_page_rightcol, i_locale, i_tpl_page_ico, i_tpl_page_submenu {

	protected $event;

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

 	$usr = ws_user::getByLogin( mr::subdir() );

 	/* @var $event ws_blog_item */
 	$event = ws_blog_item::factory((int)$params[1]);
 	$this->event = $event;

 	if(!$event || !$event->is_showable() || !$usr || $usr->id() != $event->auth()->id())
 		throw new ErrorPageException(self::$locale["not_found"], 404);

 	ws_user_remind::zeroize( ws_user_remind::type_blog_resp, $this->event->id() );

 	$this->title = $event->title ? $event->title : ($event->auth()->blog_title ? $event->auth()->blog_title : "Запись от ".date("d.m.Y H:i:s", $event->time));

 	$this->description = $this->title;

 	ob_start();

 ?>
 <h1><?=$event->title?></h1>
 	<br />
 <?
 	$f = new mr_xml_fragment;
 	$f->loadXML($event->content);
 	echo $f;

 	tpl_fr_comment::outlist($event->getNotes());
 	if($event->can_add_note()) tpl_fr_comment::add("/x/ajax-blog/note", $event->id());

 	$this->content = ob_get_contents();
 	ob_end_clean();

 	$this->css[] = "comment.css";
 	$this->head .= "<script src=\"/style/js/comment.js\" type=\"text/javascript\"></script>";
 }

 public function col_right()
 {
	ob_start();

	$bt = $this->event->auth()->blog_title;
 	$bt = $bt ? $bt : "Дневник";

	echo "<center><b><a href=\"", $this->event->auth()->href(""), "\">$bt</a></b><br/>", $this->event->auth()->avatar(), "<br/>", $this->event->auth(), "</center>";
	echo "<p>Дата: <i>", date("d.m.y H:i:s", $this->event->time), "</i></p>";

	$bms = $this->event->bms();
	if(count($bms))
	{
		echo "<p>Метки:<br/><ul>";

		foreach($bms as $bm) echo "<li>", $bm, "</li>";

		echo "</ul></p>";
	}

	if($this->event->mood) echo "<p>Настроение: <i>", $this->event->mood, "</i></p>";
	if($this->event->music) echo "<p>Музыка: <i>", $this->event->music, "</i></p>";

	if($this->event->can_edit() || $this->event->can_delete())
	{
		echo "<p>Администрирование:<br/><ul>";
		if($this->event->can_edit()) echo "<li><a href=\"".mr::host("own")."/blog.edit-".$this->event->id().".xml\">Править запись</a></li>";
		if($this->event->can_delete()) echo "<li><a href=\"javascript:void(0)\" onclick=\"if(confirm('Вы уверены, что хотите удалить эту дневниковую запись?')) mr_Ajax({url:'/x/ajax-blog/delete', data:{id:".$this->event->id()."},update:$(this).getParent()}).send()\">Удалить</a></li>";
		echo "</ul></p>";
	}

	return ob_get_clean();
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