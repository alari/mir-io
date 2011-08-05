<?php
	class mr_cache_medium implements mrConfig {
		static protected $basedir = "data/cache/medium", $ext = "txt", $lifetime = 86400;
		
		protected $dir, $key, $refresh, $contents;

/**
 * Реализация интерфейса.
 * Параметры конфигурации:
 * basedir = data/cache/medium Коренная директория для хранения кэша
 * ext = txt Расширение для кэшированных файлов
 * lifetime = 86400 Время жизни кэша по умолчанию
 *
 * @param array $ini
 */
 static public function mrConfig($ini)
 {
 	if(!is_array($ini)) return;
	foreach($ini as $k=>$v) if(isset(self::$$k) && self::$$k !== $v)
		self::$$k = $v;
 }

/**
 * Конструктор
 *
 * @param string $dir Директория хранения кэша
 * @param string $key Имя кэшированного блока
 * @param int[opt] $key_length=0 Ориентировочная максимальная длина имени блока
 * @param int[opt] $subdir_step=0 Порядок количества кэшированных файлов в директории
 */
 public function __construct($dir, $key, $key_length=0, $subdir_step = 0)
 {
 	$key = strtr($key, array("."=>"_", "/"=>"_"));
 	if($key_length) while(strlen($key)<$key_length) $key = "0$key";
 	if($subdir_step)
 	{
 		$dir .= "/".chunk_split(substr($key, 0, -$subdir_step), $subdir_step, "/");
 		$dir = substr($dir, 0, -1);
 	}
 	$dir = self::$basedir."/".$dir;
 	self::mkdir($dir);
 	$this->dir = $dir;
 	$this->key = $key;
 }
 
/**
 * Возвращает содержимое блока, если оно актуально
 *
 * @return string
 */
 public function get()
 {
 	if($this->contents === null)
 	{
 		$d = opendir($this->dir);
 		while($f = readdir($d)) if(strpos($f, $this->key)===0)
 		{
 			list(, $lifetime, ) = explode(".", $f, 3);
 			if(filemtime($this->dir."/".$f)+$lifetime < time())
 			{
 				unlink($this->dir."/".$f);
 				$this->contents = false;
 			} else
 				$this->contents = file_get_contents($this->dir."/".$f);
 			
 			$this->refresh = $lifetime;
 			
 			break;
 		}
 		if(!$this->contents) $this->contents = false;
 	}
 	
 	return $this->contents;
 }
 
/**
 * Устанавливает новое содержимое для блока кэша
 *
 * @param string $data
 * @param int[opt] $lifetime Время актуальности блока
 */
 public function set($data, $lifetime=0)
 {
 	if(!$lifetime) $lifetime = $this->refresh ? $this->refresh : self::$lifetime;
 	file_put_contents($this->dir."/".$this->key.".".$lifetime.".".self::$ext, $data);
 	$this->contents = $data;
 	$this->refresh = $lifetime;
 }
 
/**
 * Удаляет блок из кэша
 *
 */
 public function delete()
 {
 	if(!$this->refresh)
 	{
 		$d = opendir($this->dir);
 		while($f = readdir($d)) if(strpos($f, $this->key)===0)
 		{
 			unlink($this->dir."/".$f);
 			break;
 		}
 	} else unlink($this->dir."/".$this->key.".".$this->refresh.".".self::$ext);
 	
 	$this->contents = null;
 	$this->refresh = false;
 }
 
/**
 * Рекурсивная функция для создания каталога $dirname
 *
 * @param string $dirname
 */
 static public function mkdir($dirname)
 {
 	if(!$dirname || is_dir($dirname))
 		return true;
 	$dirs = explode("/", $dirname);
 	array_pop($dirs);
 	self::mkdir(join("/", $dirs));
 	mkdir($dirname);
 }
 
 static private function checkdir($dirname)
 {
 	$a = scandir(getcwd()."/".$dirname);
 	foreach($a as $s) if($s!="." && $s!="..") return false;
 	rmdir(getcwd()."/".$dirname);
 	$d = explode("/", $dirname);
 	unset($d[count($d)-1]);
 	return self::checkdir(join("/", $d));
 }
 
 public function __destruct()
 {
 	self::checkdir($this->dir);
 }
	}
?>