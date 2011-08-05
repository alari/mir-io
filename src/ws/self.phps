<?php class ws_self extends mr_ses implements i_initiate {

	static protected $ini, $self, $id;

 static public function init($ini)
 {
  self::$ini = $ini;
  if(isset($_GET["logout"])) setcookie("autologin", null, null, "/", ".mir.io");

  if(!parent::id() && strlen(@$_COOKIE["autologin"])==32 && @$_COOKIE["login"] && !isset($_GET["logout"]))
  {
   $f = @mr_sql::fetch(
    array("SELECT id FROM mr_users WHERE md5=? AND login=?", $_COOKIE["autologin"], $_COOKIE['login']),
     mr_sql::obj);

   if(is_object($f) && $f->remote_addr == $_SERVER["REMOTE_ADDR"])
    self::start($f->id);
   else setcookie("autologin", null, null, "/", ".mir.io");
  } elseif(parent::id()) {

   self::$id = parent::id();
  }

  if(self::$id)
  {
   self::$self = ws_user::factory(self::$id);
  }
 }

/**
 * Можно ли текущему пользователю
 *
 * @param string $what
 * @param int $comm=0
 * @return bool
 */
 static public function is_allowed($what, $comm=0)
 {
  return self::$self instanceof ws_user ? self::$self->is_allowed($what, $comm) : false;
 }

 /**
 * Начало новой сессии пользователя по его идентификатору
 *
 * @param int $id
 * @param bool[optional] $check_ip=true
 */
 static public function start($id, $check_ip=true, $hide_me=false)
 {
  if($id)
  {
   self::$id = (int)$id;
   self::$self = ws_user::factory(self::$id);
   $status = $hide_me? -1: (self::$self->is_meta()?self::$self->is_meta():1);
   if(self::$self instanceof ws_user && self::$self->is_allowed("auth"))
   	parent::start($id, $check_ip, $status);
  }

  if( self::ok() )
   mr_sql::qw("UPDATE mr_users SET lastlogged=?, remote_addr=? WHERE id=? LIMIT 1", time(), $_SERVER['REMOTE_ADDR'], $id);
 }

/**
 * Авторизация пользователя по логину и паролю
 *
 * @param string $login
 * @param string $password
 * @param bool[optional] $check_ip=true
 * @param bool[optional] $hide_me=false
 */
 static public function authorize($login, $password, $check_ip=true, $hide_me=false)
 {
  $md5 = md5($password);
  $r = mr_sql::qw("SELECT id FROM mr_users WHERE login=? AND md5=?", $login, $md5);
  $id = mr_sql::num_rows($r) == 1 ? mr_sql::fetch($r, mr_sql::get) : false;

  if(@$id) self::start($id, $check_ip, $hide_me);
  if(self::ok())
   setcookie("login", $login, time()+8640000, "/", $_SERVER["HTTP_HOST"]);
 }

/**
 * Enter description here...
 *
 * @return ws_user
 */
 static public function self()
 {
  return self::$self;
 }

/**
 * Ссылка на профиль или слово "Гость", если не авторизован
 *
 * @param string $href="profile"
 * @return string
 */
 static public function link($href="profile")
 {
  static $link = false;
  if(!$link) $link = self::ok() ? self::$self->link($href) : "Гость";
  return $link;
 }

/**
 * Пройдена ли аутентификация пользователем
 *
 * @return bool
 */
 static public function ok()
 {
  if(self::$self instanceof ws_user && parent::id()) return true;
  return false;
 }

/**
 * Возвращает статус юзера в комьюнити
 *
 * @param int $comm
 * @param int $status=0
 * @return int|bool
 */
 static public function is_member($comm, $status=null)
 {
  return self::ok() ? self::$self->is_member($comm, $status) : false;
 }

/**
 * Возвращает статус юзера в метакомьюнити
 *
 * @param int $comm
 * @return int
 */
 static public function is_meta($comm=0)
 {
  return self::ok() ? self::$self->is_meta($comm) : false;
 }

/**
 * Есть ли новые (или старые) письма
 *
 * @param string[optional] $box="inbox"
 * @param string[optional] $type="new"
 * @return int
 */
 static public function letters($box="inbox", $type="new")
 {
  return self::ok() ? (int)self::msgs()->count($box, $type) : 0;
 }

/**
 * Возвращает объект сообщений сего пользователя
 *
 * @return ws_user_msgs
 */
 static public function msgs()
 {
  return self::ok() ? self::$self->msgs() : null;
 }

/**
 * Возвращает логин сего пользователя
 *
 * @return string
 */
 static public function login()
 {
  return self::$self->login;
 }

 static public function id()
 {
  return (int)mr_ses::id();
 }

	}
?>