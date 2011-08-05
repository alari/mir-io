<?php class tpl_page_own_lib_cycles extends tpl_page_own_lib_inc implements i_tpl_page_rightcol {
	
	private $draft;
	
public function __construct($filename="", $params="")
{
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";

 	if(!ws_self::ok())
 		throw new ErrorPageException("Вы не авторизованы.", 401);
 	
	$this->title = "Настройка циклов произведений";
 	
	$cycles = ws_libro_pub_cycle::byOwner( ws_self::id() );
	
 	ob_start();
 	
 	?>
	
<h1>Настройка циклов произведений</h1>

<script>
 function move_cycle(item)
 {
 	
 }
</script>

<ul>
<?foreach($cycles as $c){?><li><?=$c?> <span onclick="move_cycle($(this).getParent())">вв</span></li><?}?>
</ul>

 <? 	
 	
  $this->content = ob_get_clean();
}

public function col_right()
{
	ob_start();
?>

<?
	return ob_get_clean().parent::col_right();
}
	}