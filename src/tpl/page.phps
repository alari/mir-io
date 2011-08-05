<?php abstract class tpl_page implements i_tpl_page {

	protected $params=array(), $filename="", $css=array(),
		$content="Content", $title="Page Title", $head="",
		$layout="", $layout_site, $keywords="творчество, современных, авторов, стихи, проза, критика, статьи, изобразительное искусство, фотография, сообщества, конкурсы, рейтинги, блогосфера, форум, блог, дискуссионное пространство",
		$description="Все грани творчества современных авторов: стихи, проза, критика, статьи, изобразительное искусство, фотография. Сообщества, конкурсы, рейтинги, блогосфера и дискуссионное пространство.";

 public function __construct($filename="", $params="")
 {
 	$this->filename = $filename;
 	if(is_array($params)) $this->params = $params;
 }

 public function __get($name)
 {
 	return @$this->params[$name];
 }

 public function __set($name, $value)
 {
 	return $this->params[$name] = $value;
 }

/**
 * Возвращает объект лэйаута
 *
 * @return i_tpl_layout
 */
 public function layout()
 {
 	$layout = "tpl_layout_";
 	if($this->layout)
 	{
 		if($this->layout_site === false)
 			$layout .= $this->layout;
 		else
 			$layout .= ($this->layout_site?$this->layout_site:mr::layout())."_".$this->layout;
 	}
 	else $layout .= $this->layout_site?$this->layout_site:mr::layout();

 	if(class_exists($layout, true))
 		return new $layout( $this );
 	return null;
 }

/**
 * Заголовок странички
 *
 * @return string
 */
 public function title()
 {
 	return $this->title;
 }

/**
 * Основной контент странички
 *
 * @return string
 */
 public function content()
 {
 	return $this->content;
 }

/**
 * Добавки в голову странички
 *
 * @return string
 */
 public function head()
 {
 	return $this->head;
 }

/**
 * Возвращает массив css-ок от корня
 *
 * @return array
 */
 public function css()
 {
 	return $this->css;
 }

 public function keywords()
 {
 	return $this->keywords;
 }

 public function description()
 {
 	return $this->description;
 }
	}
?>