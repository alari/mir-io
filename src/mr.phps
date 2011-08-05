<?php
/**
 * Коренной класс движка. В одной директории с ним должен быть файл mr.ini
 * с конфигурационными настройками сайта.
 *
 * @package mr
 * @author Dmitry Kourinski (cogito@mirari.ru)
 * @copyright (c) 2007
 */

	class mr{
		static private
			$runned = false,
			$time,
			$log=array(),
			$included=array(),
			$ini=array(),
			$inc_ext="phps",

			$hosts = array(),

			$layout = "www",
			$subdir = "sub",
			$site = "www",
			$scripts = "www",

			$url = "",

			$subname = "",

			$param = "",

			$default_lang = "ru",
			$current_lang = "";

		/**
		 * Функция входит в пространство класса
		 *
		 * [mr]
		 * locale =
		 * use = mod(,mod)*
		 * inc_ext = phps
		 * subname = sub
		 * subname = ~sub1,-sub2,*
		 * default.lang = ru
		 *
		 * [mr.hosts]
		 * HTTP_HOST = site/layout/lang
		 *
		 * mask.$name = regexp
		 * $name.$i = site|layout|lang
		 *
		 * [site.$site]
		 * reg.$name = $expression
		 * match.$name = $class
		 * page.$url = $class
		 *
		 * @access private
		 */
		static public function run()
		{
			if(!self::$runned)
			{
				self::$time = microtime(true);

				self::$included[__CLASS__] = true;

				self::$runned = true;

				self::$ini = @parse_ini_file(dirname(__FILE__)."/".__CLASS__.".ini", true);
				if(!self::$ini) throw new exception("Cannot parse main ini file");

				// Базовая конфигурация
				$mr = &self::$ini["mr"];
				if(is_array($mr))
				{
					// Установка локали
					if($mr["locale"]){
						$loc = "";
						if(is_array($mr["locale"])) foreach($mr["locale"] as $l)
						{
							$loc = setlocale(LC_ALL, $l);
							if($loc) break;
						} else $loc = setlocale(LC_ALL, $mr["locale"]);

						self::log("Locale: ".$loc);
					}
					// Расширение включений
					if($mr["inc_ext"]) self::$inc_ext = $mr["inc_ext"];
					// Языковая локаль по умолчанию
					if($mr["default.lang"]) self::$default_lang = $mr["default.lang"];
					// Установка include_path под конкретную ОС
					if($mr["include_path"]) ini_set("include_path", str_replace(" ", PATH_SEPARATOR, $mr["include_path"]));

					// Сайторазличение
					// отыскивает директиву [mr.hosts]
					// HTTP_HOST = site/layout/scripts/param/lang

					// ДОМЕНЫ ОБРАБАТЫВАТЬ ЗДЕСЬ

					$host = substr_count($_SERVER['HTTP_HOST'], ".") == 1 ? "www.".$_SERVER['HTTP_HOST'] : $_SERVER['HTTP_HOST'];

					if(is_array(self::$ini["mr.hosts"])) foreach(self::$ini["mr.hosts"] as $k=>$v)
					{
						if($host == $k)
						{
							list(self::$site, self::$layout, self::$scripts, self::$param, self::$current_lang) = @explode("/", $v, 4);
							if(!self::$hosts[self::$site]) self::$hosts[self::$site] = $host;
						} elseif( !self::$site && substr($k, 0, 5) == "mask." && eregi("^".$v."$", $host, $POCKETS))
						{
							$mask = substr($k, 5);
							foreach($POCKETS as $pk=>$pv) if( @self::$ini["mr.hosts"][ $mask.".".$pk ] )
							switch( $pv )
							{
								case "lang": self::$current_lang = $POCKETS[$pk]; break;
								case "site": self::$site = $POCKETS[$pk]; break;
								case "layout": self::$layout = $POCKETS[$pk]; break;
								case "scripts": self::$scripts = $POCKETS[$pk]; break;
								case "param": self::$param = $POCKETS[$pk]; break;
							}
							if(@self::$ini["mr.hosts"][ $mask.".lang" ] && !self::$current_lang)
								self::$current_lang = self::$ini["mr.hosts"][ $mask.".lang" ];
							if(@self::$ini["mr.hosts"][ $mask.".site" ] && !self::$site)
								self::$site = self::$ini["mr.hosts"][ $mask.".site" ];
							if(@self::$ini["mr.hosts"][ $mask.".layout" ] && !self::$layout)
								self::$layout = self::$ini["mr.hosts"][ $mask.".layout" ];
							if(@self::$ini["mr.hosts"][ $mask.".scripts" ] && !self::$scripts)
								self::$scripts = self::$ini["mr.hosts"][ $mask.".scripts" ];
							break;
						} else {
							list($h, ) = @explode("/", $v, 2);
							$sh = @explode(",", $h);
							foreach($sh as $h)
								if(!self::$hosts[$h]) self::$hosts[$h] = $k;
						}
					}

					if(!self::$site) self::$site = "www";
					if(!self::$layout) self::$layout = self::$site;
					if(!self::$scripts) self::$scripts = self::$site;

					// Обработка субимён
					if(!self::$subname)
					{
						self::$subname = "sub";

						$sbn = @self::$ini["site.".self::$site]["subname"];
						if(!$sbn) $sbn = $mr["subname"];
						if($sbn)
						{
							$subname = @explode(",", $sbn);
							if(is_array($subname))
							{
								self::$subname = array();
								foreach($subname as $sub)
									self::$subname[ $sub[0] ] = substr($sub, 1);
							}
							else self::$subname = $subname;
						}
					}

					// Подключение дополнительных модулей
					if($mr["use"])
					{
						$use = explode(",", $mr["use"]);
						foreach($use as $m) self::uses($m);
					}

					// Обработка урла
					self::set_url();
				}
			}
		}

		/**
		 * Устанавливает url странички для класса
		 *
		 * @param string $url
		 * @return string
		 */
		static public function set_url($url = null)
		{
			if($url) self::$url = $url;
			else list(self::$url, ) = explode("?", $_SERVER['REQUEST_URI'], 2);

			if(self::$url[0]=="/") self::$url = substr(self::$url, 1);

			if((self::$url[0] == "~" && !is_array(self::$subname)) || (is_array(self::$subname) && @self::$subname[ self::$url[0] ]))
			{
				$subname = is_array(self::$subname) ? self::$subname[ self::$url[0] ] : self::$subname;
				list(self::$subdir, self::$url) = explode("/", substr(self::$url, 1), 2);
				self::$url = $subname."/".self::$url;
			}

			return self::$url;
		}

		/**
		 * Текущий язык или null, если не выбран
		 *
		 * @return string
		 */
		static public function lang($default = false)
		{
			return $default ? self::$default_lang : self::$current_lang;
		}

		/**
		 * Установить текущий язык
		 *
		 * @param char[2] $lang
		 * @return char[2]|null
		 */
		static public function set_lang($lang)
		{
			if(strlen($lang)==2) return self::$current_lang = $lang;
		}

		/**
		 * Имя сайта-обработчика домена
		 *
		 * @param string[opt] $compare Образец для сравнения
		 * @return bool|string
		 */
		static public function site($compare=false)
		{
			return $compare ? $compare==self::$site : self::$site;
		}

		/**
		 * Адрес текущего хоста
		 *
		 * @param string $name
		 * @return string
		 */
		static public function host($name=false, $http=true)
		{
			return ($http?"http://":"").($name ? self::$hosts[$name] : self::$hosts[self::$site]);
		}

		/**
		 * Раздел скриптов данного домена
		 *
		 * @param string[opt] $compare Образец для сравнения
		 * @return bool|string
		 */
		static public function scripts($compare=false)
		{
			return $compare ? $compare==self::$scripts : self::$scripts;
		}

		/**
		 * Параметр сайта - для мелкой функциональности
		 *
		 * @param string $compare
		 * @return bool
		 */
		static public function param($compare=false)
		{
			return $compare ? $compare==self::$param : self::$param;
		}

		/**
		 * Имя лэйаута для домена
		 *
		 * @param string[opt] $compare Образец для сравнения
		 * @return bool|string
		 */
		static public function layout($compare=false)
		{
			return $compare ? $compare==self::$layout : self::$layout;
		}

		/**
		 * userdir -- слово между /~ и /
		 *
		 * @return string
		 */
		static public function subdir()
		{
			return self::$subdir;
		}

		/**
		 * Модифицированный адрес: / в начале убрана, /~/ преобразована в sub или по шаблону хоста.
		 *
		 * @return string
		 */
		static public function url()
		{
			return self::$url;
		}

		static private function handle_host($name)
		{
			static $subname = "sub";

			$host = &self::$ini["host.".$name];
			if(is_array($host))
			{
				if($host["site"])
				{
					self::$site = $host["site"];
					if(is_array(self::$ini["host.".self::$site]))
						self::handle_host(self::$site);
				}
				if($host["layout"]) self::$layout = $host["layout"];
				if($host["subname"]) $subname = $host["subname"];
				if(self::$url{0}=="~" && !self::$subdir)
				{
					list(self::$subdir, self::$url) = explode("/", substr(self::$url, 1), 2);
					self::$url = $subname."/".self::$url;
				}
			}
		}

		static public function nocache()
		{
			Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			Header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
			Header("Cache-Control: no-cache, must-revalidate");
			Header("Cache-Control: post-check=0,pre-check=0");
			Header("Cache-Control: max-age=0");
			Header("Pragma: no-cache");
		}

		/**
		 * Реализация модульности в программе. Подключает переданный модуль, трансформируя
		 * символы подчерка _ в слеши /. Если подключен класс, то проверяет, реализует ли
		 * модуль интерфейс i_initiate, если да, то передаёт по интерфейсу массив конфигурационных
		 * параметров, что хранятся в ini-файле для этого модуля.
		 *
		 * @param string $module Имя модуля или класса
		 * @return mixed Возвращаемое подключаемым файлом значение, если есть
		 */
		static public function uses($module)
		{
			$lower = strtolower($module);
		  if(!isset(self::$included[$lower]))
		  {
		  	self::$included[$lower] = @include_once str_replace("_", "/", $module).".".self::$inc_ext;
		  	if(self::$included[$lower])
		  	{
		  		self::log("Including ".$module);
		  		if(class_exists($module, false))
		  		{
					$Reflection = new ReflectionClass($module);

					// параметры конфигурации
					if($Reflection->implementsInterface("i_initiate"))
						call_user_func(array($module, "init"), @self::$ini[$lower]);

					// файл локализации
					if($Reflection->implementsInterface("i_locale"))
					{
						// адрес файла локали
						$loc_file = "";
						// адрес базового файла
						$loc_def = "";
						// реальный язык файла
						$loc_lang = self::$current_lang ? self::$current_lang : self::$default_lang;

						$loc_file = explode("_", $module);
						$loc_def = array_pop($loc_file);
						$loc_file = $loc_def = "src/".join("/", $loc_file)."/!locale/".$loc_def;

						// существует локаль для текущего языка
						if( is_readable($loc_file.".".$loc_lang.".ini") )
						{
							$loc_file .= ".".$loc_lang.".ini";
							$loc_def = $loc_lang!=self::$default_lang && is_readable($loc_def.".".self::$default_lang.".ini") ? $loc_def.".".self::$default_lang.".ini" : $loc_file;
						}
						// есть локаль только для языка по умолчанию
						elseif( is_readable($loc_file.".".self::$default_lang.".ini") )
						{
							$loc_lang = self::$default_lang;
							$loc_def = ($loc_file .= ".".self::$default_lang.".ini");
						// файл не найден
						} else return mr::log("Locale not found: ".$loc_file);

						// файл по умолчанию
						$data = parse_ini_file( $loc_def );

						// достаём данные из файла
						if( $loc_def != $loc_file )
						{
							$data = array_merge($data,
								parse_ini_file( $loc_file ) );
						}

						// передаём локаль классу
						call_user_func_array(array($module, "locale"), array($data, $loc_lang));
					}
		  		}
		  	}
		  }

		  return self::$included[$lower];
		}

		/**
		 * С указанной точностью возвращает время с момента подключения этого класса
		 *
		 * @param [int] $precision=12
		 * @return string|double
		 */
		static public function time($precision = 12)
		{
			$time = microtime(true);
			return sprintf("%01.{$precision}f", round($time-self::$time, $precision));
		}

		/**
		 * Сохраняет сообщение в массив логов программы. Удобно для отладки
		 *
		 * @param string $message
		 */
		static public function log($message)
		{
			self::$log[ (string)self::time() ] = $message;
		}

		/**
		 * Возвращает массив лога в формате время=>сообщение
		 *
		 * @return array
		 */
		static public function getLog()
		{
			return self::$log;
		}

		/**
		 * Возвращает текст логов или печатает их
		 *
		 * @param [bool] $out=false Осуществить ли вывод логов
		 * @return [string]
		 */
		static public function printLog($out = false)
		{
			$return = "Programm log:\n";
			foreach(self::$log as $time => $message)
			{
				$return .= " $time: $message\n";
			}
			$return .= " ".(string)self::time().": Формирование и вывод логов программы";
			if($out) print $return;
			return $return;
		}

		/**
		 * Возвращает массив конфигурационных параметров, заданный для того или иного
		 * модуля
		 *
		 * @param string $block Имя модуля
		 * @return array
		 */
		static public function ini($block)
		{
			return @self::$ini[$block];
		}
	}

	function __autoload($module)
	{
		mr::uses($module);
	}

	mr::run();

	/**
	 * Интерфейс передачи подключаемым модулям их конфигурационных параметров
	 *
	 */
		interface i_initiate {
			static public function init($ini);
		}

		interface i_locale {
			// const locale_file
			static public function locale($ini, $current_lang);
		}

	/**
	 * Интерфейс шаблона отображения
	 *
	 */
		interface i_tpl_layout {
public function __toString(); // псевдоним следующей функции для вызова через echo
public function realize(); // возвращает полностью готовый текст странички
public function __construct(i_tpl_page &$page); // конструктор требует объект контента
public function __set($name, $value); // для установки спец. блоков контента
public function __get($name); // возврат внутренних переменных
		}

	/**
	 * Интерфейс шаблона генератора странички
	 *
	 */
		interface i_tpl_page {
public function __construct($filename="", $params=""); // конструктор требует имя запрошенного файла и список переданных параметров (параметры разбираются классом tpl)
public function __get($name);
public function __set($name, $value);
public function layout(); // возвращает объект лэйаута, если он специально определён для этой странички, или null, если нужно использовать отображение по умолчанию (if ($p->layout() instanceof i_tpl_layout) ... )
// далее вероятно что-то вроде
public function title(); // название странички -- жёстко
public function content(); // контент странички -- жёстко
public function head(); // добавки в <head>
/**
 * @return array
 *
 */
public function css(); // массив адресов цссок не от корня -- чтобы лэйаут подставлял корень
public function keywords(); // ключевые слова поисковику
public function description(); // описание поисковику
// дополнительный интерфейс можно делать для конкретного сайта. очевидно, все функции, кроме первой, удобно наследовать
		}

	/**
	 * Интерфейс php-html-шаблона многоразового использования
	 *
	 */
		interface i_tpl_fragment {
// не знаю, что с ним делать... просто -- пусть будет. что юзаешь -- знаешь, как устроено (удобно)
		}

	/**
	 * Интерфейс скриптовых модулей
	 *
	 */
		interface i_xmod {
 static public function action($x);
		}
