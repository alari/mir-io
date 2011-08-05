<?php class tpl_page_mir_users_allpubs extends tpl_page implements i_tpl_page_ico {

	protected $user;

public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);

 	$this->layout = "print";

 	/* @var $user ws_user */
 	$user = $this->user = ws_user::getByLogin(mr::subdir());
	if(!$user) throw new ErrorPageException("Автор не найден");

 	$this->title = $user->name()." - все произведения";

 	ob_start();

 	echo "<style type='text/css'>body{overflow:auto!important}</style>";

	if(@$params[2]){
		$cycle = ws_libro_pub_cycle::factory($params[2]);
		if(!$cycle || $cycle->user()->id() != $user->id()) throw new ErrorPageException("Цикл не найден", 404);
		$pubs = $cycle->publist();
		if(count($pubs)){
			echo "<h3>Цикл «".$cycle->link()."», ".$user->link()."</h3>";
			foreach($pubs as $p) if($p->is_showable()) {
				echo "<hr/>";
				$tpl = new tpl_page_libro_read("", array(1=>$p->id(), "view"=>"print"));
				echo $tpl->content();
			}
		} else throw new ErrorPageException("Произведения цикла не найдены");
	} else {
		$cycles = ws_libro_pub_cycle::byOwner($user->id(), true);
		foreach($cycles as $cycle) if($cycle->is_showable() && count($cycle->publist())) {
			echo "<hr/><h3>Цикл «".$cycle->link()."», ".$user->link()."</h3>";
			foreach($cycle->publist() as $p) if($p->is_showable()) {
				echo "<hr/>";
				$tpl = new tpl_page_libro_read("", array(1=>$p->id(), "view"=>"print"));
				echo $tpl->content();
			}
		}
	}

 	$this->content = ob_get_clean();
	$this->css[] = "pub/read.css";
 }

 public function p_ico()
 {
 	return "libro";
 }
	}
?>