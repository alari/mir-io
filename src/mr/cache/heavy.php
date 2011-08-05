<?php
	class mr_cache_heavy implements mrConfig, ArrayAccess {
		static protected $basedir = "data/cache/heavy", $ext = "txt", $lifetime = 86400;
		
		protected $dir, $key, $offsets=false, $refresh = array();
/**
 * Реализация интерфейса.
 * Параметры конфигурации:
 * basedir = data/cache/heavy Коренная директория для хранения кэша
 * ext = txt Расширение для кэшированных файлов
 * lifetime = 86400 Время жизни кэша по умолчанию
 *
 * @param array $ini
 */
 static public function mrConfig($ini)
 {
	foreach($ini as $k=>$v) if(isset(self::$$k) && self::$$k !== $v)
		self::$$k = $v;
 }

/**
 * Конструктор
 *
 * @param string $dir Директория хранения кэша
 * @param string $key Уникальный идентификатор массива кэша
 * @param int[opt] $key_length=0 Ориентировочная максимальная длина идентификатора
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
 * Проверка существования в массиве кэша -- блока
 *
 * @param string $offset
 * @return bool
 */
 public function offsetExists($offset)
 {
 	if(!$this->offsets) $this->offsets();
 	return isset($this->offsets[$offset]);
 }
 
/**
 * Возвращает содержимое блока кэша
 *
 * @param string $offset
 * @return string
 */
 public function offsetGet($offset)
 {
 	if($this->offsetExists($offset))
 	{
 		if(!$this->offsets[$offset])
 			$this->offsets[$offset] = file_get_contents($this->dir."/".$this->key.".".$this->refresh[$offset].".".$offset.".".self::$ext);
 		return $this->offsets[$offset];
 	} else return false;
 }

/**
 * Устанавливает значение блока в кэше. Если значение передаётся как массив,
 * то $data[0] есть значение блока, $data[1] -- время его актуальности в секундах
 *
 * @param string $offset
 * @param string|array $data
 * @return true
 */
 public function offsetSet($offset, $data)
 {
 	if(is_array($data))
 	{
 		$lifetime = $data[1];
 		$data = $data[0];
 	} else $lifetime = self::$lifetime;
 	
 	if($this->offsetExists($offset)) $this->offsetUnset($offset);
 	
 	$this->refresh[$offset] = $lifetime;
 	file_put_contents($this->dir."/".$this->key.".".$lifetime.".".$offset.".".self::$ext, $data);
 	$this->offsets[$offset] = $data;
 	return true;
 }

/**
 * Удаляет блок из кэша
 *
 * @param string $offset
 * @return bool
 */
 public function offsetUnset($offset)
 {
 	if($this->offsetExists($offset))
 	{
 		@unlink($this->dir."/".$this->key.".".$this->refresh[$offset].".".$offset.".".self::$ext);
 		unset($this->refresh[$offset]);
 		unset($this->offsets[$offset]);
 		return true;
 	} else return false;
 }
 
/**
 * Удаляет весь массив кэша с диска
 *
 */
 public function delete()
 {
 	if(!$this->offsets) $this->offsets();
 	foreach($this->offsets as $k=>$v)
 	{
 		unlink($this->dir."/".$this->key.".".$this->refresh[$k].".".$k.".".self::$ext);
 	}
 	$this->offsets = false;
 	$this->refresh = array();
 }
 
 private function offsets()
 {
 	$this->offsets = array();
 	$d = opendir($this->dir);
 	while($f = readdir($d)) if(strpos($f, $this->key) === 0)
 	{
 		list(, $lifetime, $other) = explode(".", $f, 3);
 		if(is_numeric($lifetime) && filemtime($this->dir."/".$f)+$lifetime < time())
 		{
 			unlink($this->dir."/".$f);
 			continue;
 		}
 		$p = substr($other, 0, -(strlen(self::$ext)+1));
 		$this->offsets[$p] = false;
 		$this->refresh[$p] = $lifetime;
 	}
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