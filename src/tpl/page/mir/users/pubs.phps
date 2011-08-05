<?php class tpl_page_mir_users_pubs extends tpl_page implements i_tpl_page_rightcol, i_locale, i_tpl_page_ico {
	
	protected $user, $cycle;
	
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
 	
 	/* @var $user ws_user */
 	$user = $this->user = ws_user::getByLogin(mr::subdir());

 	$this->title = $user->name();
 	
 	ob_start();
 	
 	$action = $params[1];
 	if($action == "pubs") $action = $params[3];
 	if(is_numeric($action))
 	{
 		$this->cycle = ws_libro_pub_cycle::factory((int)$action);
 		if($this->cycle->user()->id() != $user->id())
 			throw new ErrorPageException(self::$locale["no_cycle"], 404);
 		$pubs = $this->cycle->publist(true);
 		if(!$this->cycle->is_showable())
 			throw new RedirectException( $user->href("pubs") );
 			
 		echo "<h1>".$this->cycle->title."</h1>";
 		if($this->cycle->description) echo "<center><em>".$this->cycle->description."</em></center>";
 		echo "<h2>Автор: ".$user->link()."</h2>";
		echo "<center><small><i><a href=\"".$user->href("allpubs-".$this->cycle->id())."\">Версия для печати</a></i></small></center>";
 		
 		$this->title = $user->name().", ".$this->cycle->title;
 		
 	} elseif(in_array($action, array("prose", "stihi", "article"))) {
 		$calc = false;
 		$pubs = ws_libro_pub::several("author=".$user->id()." AND type='".$action."' AND anonymous='no'", 0, 0, "time DESC", $calc, true);
 		
 		echo "<h1>";
 		echo $type = self::$locale[$action];
 		
 		echo " ".self::$locale["by_auth"]." ".$user->link()."</h1>";
 		
 		$this->title = $user->name().", ".$type;
 		
 	} else {
 		$calc = false;
 		$pubs = ws_libro_pub::several("author=".$user->id()." AND anonymous='no'", 0, 0, "time DESC", $calc, true);
 		
 		echo "<h1>".sprintf(self::$locale["auth_pubs"], $user->link())."</h1>";
		echo "<center><small><i><a href=\"".$user->href("allpubs")."\">Версия для печати</a></i></small></center>";
 		
 		$this->title = $user->name().", ".self::$locale["all_auth_pubs"];
 	}
 	
	tpl_fr_pubs::outlist( $pubs, false );
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "pub/anonce.css";
 }
 
 public function col_right()
 {
 	/* @var $user ws_user */
 	$user = $this->user;
 	
 	ob_start();
?>
<p><?=self::$locale["author"]?>: <?=$user?></p>
<?
	$cycles = ws_libro_pub_cycle::byOwner($user->id(), 1);
?>
<p><?=self::$locale["cycles"]?>:
<ul>
<?foreach($cycles as $c) if($c->is_showable()){
	echo "<li>";
	if($this->cycle instanceof ws_libro_pub_cycle)
	{
		if($c->id() == $this->cycle->id()) echo "<strong>";
		echo $c;
		if($c->id() == $this->cycle->id()) echo "</strong>";
	} else echo $c;
	echo "</li>";
}?>
</ul>
</p>
<p>
<a href="<?=$user->href("pubs")?>"><?=self::$locale["all_pubs"]?></a>
<ul>
	<li><a href="<?=$user->href("prose")?>"><?=self::$locale["prose"]?></a></li>
	<li><a href="<?=$user->href("stihi")?>"><?=self::$locale["stihi"]?></a></li>
	<li><a href="<?=$user->href("article")?>"><?=self::$locale["article"]?></a></li>
</ul>
</p>
<?
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
 
 public function p_ico()
 {
 	return "libro";
 }
	}
?>