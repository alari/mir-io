<?php
	class mr_cache_light implements mrConfig {
		static private
			$gabbage_clean = 3,
			$data_dir = "data/cache/light",
			$cwd = "";
		static private
			$opened = array();
			
		private $data, $refresh, $isChanged=false, $filename;
			
 /**
  * Параметры конфигурации:
  * gabbage_clean = 3 Процент вероятности удаления старых данных кэша
  * data_dir = data/cache/light Директория для хранения временных файлов кэша
  *
  * @param array $mr_config_array
  */
 static public function mrConfig($mr_config_array)
 {
 	self::$cwd = getcwd();
	foreach($mr_config_array as $name => $value)
	{
		if(isset(self::$$name) && $value!=self::$$name) self::$$name = $value;
	}
 }
 
 /**
  * Проект Factory. Создаёт объект этого класса для работы с ним. Производит,
  * если нужно, разовые операции.
  *
  * @param string $filename Имя файла для кэша
  * @param int $refresh Время актуальности строк в файле
  * 
  * @return mr_cache_light
  */
 static public function factory($filename, $refresh=0)
 {
 	$filename = str_replace("/", "-", $filename);
 	if(!count(self::$opened) && !is_dir(self::$data_dir))
 	{
 		$dirs = explode("/", self::$data_dir);
 		$curdir = getcwd();
 		foreach($dirs as $d)
 		{
 			$curdir .= "/".$d;
 			if(!is_dir($curdir)) mkdir($curdir);
 		}
 	}
 	if(!isset(self::$opened[$filename]))
 	{
 		self::$opened[$filename] = new self($filename, $refresh);
 	}
 	return self::$opened[$filename];
 }
 
 /**
  * Конструктор. Private.
  *
  * @param string $filename
  * @param int $refresh
  */
 private function __construct($filename, $refresh=0)
 {
 	if(!is_readable(self::$data_dir."/".$filename.".txt"))
		fclose(@fopen(self::$data_dir."/".$filename.".txt", "x"));
	$this->data = unserialize( @file_get_contents(self::$data_dir."/".$filename.".txt") );
	if(!$this->data) $this->data = array();
 	
	$this->filename = $filename;
 	$this->refresh = $refresh;
 }
 
 /**
  * Magic function
  *
  * @param string $name
  * @return mixed
  */
 public function __get($name)
 {
 	$a = @$this->data[$name];
 		if(!is_array($a))
 		{
 			unset($this->data[$name]);
 			$this->isChanged = true;
 			return false;
 		}
 	if($this->refresh && $a[0] > time()-$this->refresh)
 	{
 		unset($this->data[$name]);
 		$this->isChanged = true;
 		return false;
 	} else {
 		return $a[1];
 	}
 }
 
 /**
  * Magic function
  *
  * @param string $name
  * @param mixed $value
  */
 public function __set($name, $value)
 {
 	$this->isChanged = true;
 	if($value)
 	 $this->data[$name] = array(time(), $value);
 	else
 	 unset($this->data[$name]);
 }
 
 /**
  * Magic function
  *
  * @param string $name
  */
 public function __unset($name)
 {
 	unset($this->data[$name]);
 }
 
 public function __destruct()
 {
 	if($this->isChanged)
 		file_put_contents(self::$cwd."/".self::$data_dir."/".$this->filename.".txt", serialize($this->data));
 }
	}
?>