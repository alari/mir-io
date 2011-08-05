<?php class mr_remote {
	
	private $ch;
	private $cookies = array();
	private $post = array();
	private $raw_post;
	
/**
 * Создаёт соединение с $url
 *
 * @param string $url
 */
 public function __construct($url=null)
 {
 	$this->ch = curl_init($url);
 	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
 	curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
 	curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
 }
 
 /**
  * Устанавливает URL запроса
  *
  * @param string $url
  * @return mr_remote
  */
 public function setURL($url)
 {
 	curl_setopt($this->ch, CURLOPT_URL, $url);
 	return $this;
 }
  
 /**
  * Метод передачи -- POST
  *
  * @return mr_remote
  */
 public function setMethodPost()
 {
 	curl_setopt($this->ch, CURLOPT_POST, 1);
 	return $this;
 }
 
 /**
  * Текст для поля POST
  *
  * @param string $data
  * @return mr_remote
  */
 public function setPostRaw($data)
 {
 	$this->raw_post = $data;
 	return $this;
 }
 
 /**
  * Одно поле POST вида name=value
  *
  * @param string $name
  * @param string $data
  * @return mr_remote
  */
 public function setPostField($name, $data)
 {
 	$this->post[$name] = $data;
 	return $this;
 }
 
 /**
  * Передаёт кукис
  *
  * @param string $name
  * @param string $data
  * @return mr_remote
  */
 public function setCookie($name, $data)
 {
 	$this->cookies[$name] = $data;
 	return $this;
 }
 
 /**
  * Файл для аплоада
  *
  * @param string $filename
  * @throws Exception
  * @return mr_remote
  */
 public function setFileUpload($filename)
 {
 	if(!is_readable($filename))
 		throw new Exception("Cannot upload $filename");
 		
 	//curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, 1);
 	curl_setopt($this->ch, CURLOPT_UPLOAD, 1);
 	curl_setopt($this->ch, CURLOPT_INFILESIZE, filesize($filename));
 	curl_setopt($this->ch, CURLOPT_INFILE, fopen($filename, "r"));
 	
 	return $this;
 }
	
/**
 * Осуществляет запрос к удалённому серверу. Возвращает тело результата
 *
 * @return string
 */
 public function request()
 {
 	if(count($this->cookies))
 	{
	 	$cookie_raw_data = "";
	 	foreach($this->cookies as $name=>$value)
	 	{
	 		$cookie_raw_data .= urlencode($name)."=".urlencode($value)."; ";
	 	}
	 	curl_setopt($this->ch, CURLOPT_COOKIE, $cookie_raw_data);
 	}
 	if($this->raw_post)
 	{
 		curl_setopt($this->ch, CURLOPT_POSTFIELDS, urlencode($this->raw_post));
 	} elseif(count($this->post)) {
 		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->post));
 	}
 	return curl_exec($this->ch);
 }
 
 /**
  * Последняя ошибка
  *
  * @return string
  */
 public function error()
 {
 	return curl_error($this->ch);
 }
 
 /**
  * Код последней ошибки
  *
  * @return int
  */
 public function errno()
 {
 	return curl_errno($this->ch);
 }
	
	}