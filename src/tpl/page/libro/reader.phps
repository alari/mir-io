<?php class tpl_page_libro_reader extends tpl_page implements i_tpl_page_rightcol, i_locale, i_tpl_page_submenu   {
	
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
 	 		
 	$this->title = "Сервис для Читателей: случайные произведения с возможностью их независимого восприятия";

 	ob_start();
 	
 ?>
 
 <small><a href="<?=mr::host("libro")?>/reader.xml">Сервис для Читателей</a></small>
 
 <div id="read-text">
 <h1>Сервис для Читателей</h1>
 <h2>Выбор случайных произведений с прикрытым авторством</h2>
 
 <p class="pr">Когда произведений на сайте &ndash; тысячи и десятки тысяч, выбор, что почитать, порой становится действительно сложной задачей.</p>
 
 <p class="pr">Особенно сложно, когда на сайте есть свои авторитеты &ndash; авторы, от предвзятого отношения к которым трудно отказаться.</p>
 
 <p class="pr">Мы предлагаем Вам возможность побыть непредвзятым читателем. Выбрав интересующий Вас тип произведений (Стихи, Проза, Эссе и статьи) и тематические разделы, Вы можете видеть только текст &ndash; вне привязок к авторству, оценкам и рекомендациям других читателей, обсуждению произведения в отзывах. Вы видите только само произведение, и обсуждаете и оцениваете его именно как литературу, независимо.</p>
 
 <p class="pr">При этом, если текст Вас заинтересует, у Вас всегда будет ссылка на обычную страничку произведения на сайте.</p>
 
 </div>
 
 <br /><br />
 <small><a href="<?=mr::host("libro")?>/reader.xml">Сервис для Читателей</a></small>
 
 	
 <?	
 	$this->content = ob_get_contents();
 	ob_end_clean();
 	
 	$this->css[] = "comment.css";
 	$this->css[] = "pub/read.css";
 	$this->head .= "<script src=\"/style/js/comment.js\" type=\"text/javascript\"></script>";
 }
 
 public function col_right()
 {
?>
<p><a href="<?=mr::host("libro")?>/reader.xml">Сервис для Читателей</a></p>

<p>Тип произведения:</p>

<center>
<form method="post" action="/x/ajax-read/sections">
 <select name="type" size="3">
  <option value="prose">Проза</option>
  <option value="stihi">Стихи</option>
  <option value="article">Статьи и эссе</option>
 </select>
 	<br/>
 <input type="button" value="Выбрать тип" onclick="mr_Ajax_Form($(this).getParent(), {update:$('read-secs')})"/>
</form>
</center>

<p><a href="secs.xml">Тематический раздел</a>:</p>
<div id="read-secs" style="overflow:auto">Выберите тип произведения, чтобы увидеть список тематических разделов. Можно выбрать несколько разделов, зажав Shift. Можно не выбрать ни одного раздела, что эквивалентно выбору всех, включая неопределённый.</div>
<?
 }
 
  public function p_submenu()
 {
 	$ret = array();
 	
 	$ret[mr::host("libro")] = "Литературная сфера";
 	$ret[mr::host("libro")."/list.xml"] = "Новые произведения";
 	$ret[mr::host("libro")."/resp.xml"] = "Отзывы";
 	$ret[mr::host("libro")."/comms.xml"] = "Сообщества";
 	$ret[mr::host("libro")."/events.xml"] = "События";
 	$ret[mr::host("libro")."/reader.xml"] = "Для читателей";
 	
 	return $ret;
 }
	
	}
?>