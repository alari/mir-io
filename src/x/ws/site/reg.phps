<?php class x_ws_site_reg extends x implements i_xmod {



 static public function action($x)
 {
 	parent::call($x, __CLASS__);
 }

 static public function submit(){
 	if(ws_self::ok()) die("Вы уже зарегистрированы и авторизованы.");

 	$errors = Array();

 	$login = $_POST["login"];
 	if(!$login) {
 		$errors[] = "Вы не ввели логин";
 	} elseif(ws_user::getByLogin($login)->id() != ws_user::anonime) {
 		$errors[] = "Пользователь с таким логином уже существует";
 	} elseif(strlen($login)<3 || strlen($login) > 16) {
 		$errors[] = "Логин должен содержать от 3 до 16 символов";
 	} elseif(!mr_text_string::no_ru_sp($login)) {
 		$errors[] = "Логин может содержать только буквы латинского алфавита, цифры, символ подчерка и дефис.";
 	}

 	$pwd = $_POST["pwd"];
 	if(strlen($pwd)<2) $errors[] = "Пароль должен содержать не менее 2-х символов.";

 	$email = $_POST["email"];
 	if(ws_user::getByEmail($email)->id() != ws_user::anonime){
 		$errors[] = "Пользователь с указанным адресом email уже существует. Может, это Вы?";
 	} elseif(!preg_match("/^[-_a-z0-9\.]{2,}@[-a-z0-9\.]{2,}\.[a-z]{2,4}$/", $email)) {
 		$errors[] = "Проверьте правильность ввода email.";
 	}

 	if(!count($errors)){
 		$u = ws_user::create($login, $pwd, $email);
 		if(!$u || $u->id() == ws_user::anonime)
 			$errors[] = "Невозможно создать пользователя.";
 	}

 	echo "<div align='left'>";
 	if(count($errors)){
 		echo "<b>Регистрация невозможна, так как регистрационная форма заполнена неверно. Ошибки:</b><br/><ol>";
 		foreach($errors as $v) echo "<li>", $v, "</li>";
 		echo "</ol>";
 		echo '<script type="text/javascript">$("reg-submit").disabled=false;</script>';
 	} else {
 		$u->email_confirmation_code = md5($u->id().mr::time().join("/", $_POST));

 		$u->userinfo = mr_text_trans::text2xml($_POST["userinfo"]);
 		$u->nick = mr_text_string::remove_excess($_POST["nick"]);
 		$u->fio = mr_text_string::remove_excess($_POST["fio"]);
 		$u->set_name_by = $_POST["set_name_by"];
 		$u->icq = (int)$_POST["icq"];
 		$u->url = mr_text_string::remove_excess($u->url);
 		$u->city = mr_text_string::remove_excess($_POST["city"]);
 		$u->signature = mr_text_string::remove_excess($_POST["signature"]);

 		$u->referer = (int)$_POST["from"];

 		$u->user_group = 3;

 		if(!$u->save()) {
 			die("Не удалось сохранить изменения. Свяжитесь, пожалуйста, с координатором сайта.");
 		}

 		$url = mr::host("mir")."/x/site-reg/confirm?email=".$u->email."&code=".$u->email_confirmation_code;

 		$msg = <<<A
 Здравствуйте, {$u->login}.
 Чтобы активировать аккаунт на сайте Мир Ио, пожалуйста, пройдите по следующей ссылке:
 	$url
 Если Вы считаете, что это ошибка, просто проигнорируйте письмо.

 С уважением,
 Администрация сайта
A;

 		mail($u->email, "Подтверждение регистрации на Мире Ио", $msg, "From: noreply@mirari.ru
Content-type: text/plain; charset=utf-8");

 		echo "<h2>Регистрация успешна</h2>Однако войти на сайт невозможно, пока Вы не подтвердите свой адрес электронной почты. Пожалуйста, проверьте ящик $u->email (в том числе папку для спама).";
 	}
 	echo "</div>";
 }

 static public function confirm()
 {
 	$email = $_GET["email"];
 	$code = $_GET["code"];

 	$u = ws_user::getByEmail($email);

 	if(strlen($code)==32 && $u->email_confirmation_code == $code)
 	{
 		$u->email_confirmed = "yes";
 		if($u->save())
 			throw new RedirectException(mr::host("mir"), 7, "Ваш адрес электронной почты успешно подтверждён. Вы можете войти на сайт, используя Ваш логин и пароль.");
 	}
 	throw new RedirectException(mr::host("mir"), 5, "Вы перешли по неверной ссылке на подтверждение электронной почты.", "Безуспешно");
 }

	}
?>