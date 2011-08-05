<?php class mr_xml_fragment implements i_initiate {
	
 static protected $ini = array(), $default_transform="xsl/text.xsl";
 protected $dom, $xsl, $result, $param_ns="http://www.mirari.ru", $param_pr="mr", $params=array();

/**
 * Реализация интерфейса. Параметры инициализации:
 * transform = xsl/common.xsl
 * param_namespace = http://www.mirari.ru
 * param_prefix = mr
 *
 * @param array $ini
 */
 static public function init($ini)
 {
 	self::$ini = $ini;
 	if(isset(self::$ini["transform"])) self::$default_transform = self::$ini["transform"];
 }
 
/**
 * Конструктор. Не делает, по сути, ничего.
 *
 */
 public function __construct()
 {
 	$this->param_ns = self::$ini["param_namespace"] ? self::$ini["param_namespace"] : "http://www.mirari.ru";
 	$this->param_pr = self::$ini["param_prefix"] ? self::$ini["param_prefix"] : "mr";
 }
 
/**
 * Установка пространства имён для xsl-параметров
 *
 * @param string $ns url пространства имён
 * @param string $prefix используемый префикс
 */
 public function setNS($ns, $prefix)
 {
 	$this->param_ns = $ns;
 	$this->param_pr = $prefix;
 }
 
/**
 * Установка множества параметров сразу. Обнуляет старые параметры
 *
 * @param array $params
 */
 public function setParams(array $params)
 {
 	$this->params = $params;
 }
 
/**
 * Установка параметра. Никакие префиксы не требуются.
 *
 * @param string $name
 * @param string $value
 */
 public function setParam($name, $value)
 {
 	$this->params[$name] = $value;
 }
 
/**
 * Загружает обрабатываемый xml-документ из файла
 *
 * @param ustring $src
 */
 public function load($src)
 {
 	$this->dom = new DOMDocument;
 	$this->dom->load($src);
 }
 
/**
 * Загружает обрабатываемый xml-документ из текста
 *
 * @param xmltext $xml
 */
 public function loadXML($xml)
 {
 	$this->dom = new DOMDocument;
 	$this->dom->loadXML($xml);
 }
 
/**
 * Загружает обрабатываемый xml-документ из готорого дом-объекта
 *
 * @param DomDocument $dom
 */
 public function loadDoc(DomDocument $dom)
 {
 	$this->dom = $dom;
 }
 
/**
 * Загружает xsl-файл
 *
 * @param string $src
 */
 public function loadTransform($src)
 {
 	$this->xsl = new DOMDocument;
 	$this->xsl->load($src);
 }
 
/**
 * Загружает xsl-трансформацию из текста
 *
 * @param xmltext $xml
 */
 public function loadTransformXML($xml)
 {
 	$this->xsl = new DOMDocument;
 	$this->xsl->loadXML($xml);
 }
 
/**
 * Загружает xsl-трансформацию из дом-документа
 *
 * @param DomDocument $d
 */
 public function loadTransformDoc(DomDocument $d)
 {
 	$this->xsl = $d;
 }
 
/**
 * Алиас для realize()
 *
 * @return string
 */
 public function __toString()
 {
 	return $this->realize();
 }
 
/**
 * Производит трансформацию
 *
 * @param bool $clearResult=true Нужно провести трансформацию заново, или можно взять кэш
 * @return string
 */
 public function realize($clearResult = true)
 {
 	if(!is_object($this->dom)) return "Error: dom required";
 	if(!is_object($this->xsl))
 	{
 		$this->xsl = new DOMDocument;
 		$this->xsl->load(self::$default_transform);
 		if(!is_object($this->xsl)) return "Error: xsl required";
 	}
 	
 	if($this->result && !$clearResult)
 		return $this->result;
 	elseif($clearResult)
 		$this->result = "";
 
  $processor=new xsltprocessor;
  $processor->registerPhpFunctions();
  $processor->importStyleSheet($this->xsl);
  if(count($this->params)>0)
   foreach($this->params as $name=>$value)
    $processor->setParameter($this->param_ns, $this->param_pr.":".$name, $value);

   return $this->result = trim($processor->transformToXML($this->dom));
 }
	
	}
?>