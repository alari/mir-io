<?php class mr_ftp {
	
		private $id, $logged=true;
		
/**
 * Соединяется с ftp-сервером
 *
 * @param string $host
 * @param string[opt] $startdir
 * @param string[opt] $login
 * @param string[opt] $password
 */
 public function __construct($host, $startdir=null, $login=null, $password=null)
 {
 	$this->id = ftp_connect($host);
 	if($login)
 		$this->logged = ftp_login($this->id, $login, $password);
 		
 	if($startdir) $this->chdir($startdir);
 }
 
/**
 * Удалось ли подсоединиться к серверу
 *
 * @return bool
 */
 public function ok()
 {
 	return $this->id && $this->logged;
 }
 
/**
 * Смена текущей директории
 *
 * @param string $dir
 * @return bool
 */
 public function chdir($dir)
 {
 	return ftp_chdir($this->id, $dir);
 }
 
/**
 * Создание директории на сервере
 *
 * @param string $dir
 * @param bool $recursive=false
 */
 public function mkdir($dir, $recursive=null)
 {
 	if($recursive)
 	{
 		$dirs = explode("/", $dir);
 		$cur = "";
 		foreach($dirs as $d)
 		{
 			$cur .= ($cur ? "/" : "").$d;
 			$this->mkdir($cur);
 		}
 	} else ftp_mkdir($this->id, $dir);
 }
 
/**
 * Закачивает файл с локальной машины на ftp-серве
 *
 * @param string $remote_filename
 * @param string $local_filename
 * @return bool
 */
 public function put($remote_filename, $local_filename)
 {
 	return ftp_put($this->id, $remote_filename, $local_filename, FTP_BINARY);
 }
 
/**
 * Удаляет файл на ftp-сервере
 *
 * @param string $remote_filename
 * @return bool
 */
 public function delete($remote_filename)
 {
 	return ftp_delete($this->id, $remote_filename);
 }
 
/**
 * Закрытие соединения с сервером
 *
 */
 public function __destruct()
 {
 	ftp_close($this->id);
 }
	
	}?>