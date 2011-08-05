<?php class tpl_page_mir_users_advices extends tpl_page implements i_tpl_page_rightcol, i_locale, i_tpl_page_ico {
	
	protected $user, $fr;
	
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

 	$this->title = $user->name().": Рекомендации к прочтению";
 	
 	ob_start();
 	
?>

	<h1>Рекомендации</h1>
	<h2>Прочесть рекомендует: <?=$this->user?></h2>

<?

	$this->handle_advices();
 	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "user/advices.css";
 }
 
 protected function handle_advices()
 {
 	
 	$this->fr = new mr_xml_fragment;
 	$advices = ws_libro_pub_advice::byUser( $this->user->id() );
 	foreach($advices as $a) $this->handle_adv($a);
 	
 }
 
 protected function handle_adv(ws_libro_pub_advice $a)
 {
 	if(!$a->pub()->is_showable()) return;
 	
 	$this->fr->loadXML( $a->reason );
?>
 	<div class="advice"><a name="adv<?=$a->id()?>"></a>
 		<span class="adv-cap"><strong><?=$a->pub()?></strong> &ndash; <?=$a->pub()->author()?></span>
 		<?=$this->fr?>
 	</div>
<?	
 }
 
 public function col_right()
 {
 	/* @var $user ws_user */
 	$user = $this->user;
 	
 	ob_start();
?>
<p><?=self::$locale["author"]?>: <?=$user?></p>
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