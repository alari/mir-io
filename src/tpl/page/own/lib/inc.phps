<?php abstract class tpl_page_own_lib_inc extends tpl_page implements i_tpl_page_rightcol, i_locale  {
	
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
 
 public function col_right()
 { 	
 	ob_start();
?>

<p>Ваши произведения:<br/>
<ul>
<li><a href="<?=mr::host("own")?>/draft/">Черновики</a></li>
<li><a href="<?=mr::host("own")?>/pub/">Публикации</a></li>
</ul>
</p>

<p>Новое произведение:<br/>
<ul>
<li><a href="<?=mr::host("own")?>/draft/new-prose.xml">Проза</a></li>
<li><a href="<?=mr::host("own")?>/draft/new-stihi.xml">Стихи</a></li>
<li><a href="<?=mr::host("own")?>/draft/new-article.xml">Эссе или статья</a></li>
</ul>
</p>

<?
	
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
 }
	}
?>