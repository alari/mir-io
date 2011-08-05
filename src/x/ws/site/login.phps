<?php class x_ws_site_login implements i_xmod {

 static public function action($x)
 {
 	if(@$_POST["login"])
 	{
 		ws_self::authorize(
 			$_POST["login"],
 			$_POST["pwd"],
 			$_POST["check_ip"]=="yes"?true:false,
 			$_POST["hide_me"]=="yes"?true:false
 		);
 		if(!ws_self::ok())
 			throw new RedirectException(mr::host("site")."/signupfailed.xml");

 		if($_POST["auto"]=="yes")
 			setcookie("autologin", ws_self::self()->md5, 8640000, "/", ".mir.io");
 		setcookie("login", ws_self::self()->login, 8640000, "/", ".mir.io");


	 	if(!$_POST["url"])
	 		throw new RedirectException(mr::host("own")."/");

	 	throw new RedirectException(mr::host("own")."/", 5, "Сейчас Вы будете перенаправлены в личный раздел. Если Вы хотите вернуться на страницу, где была проведена авторизация, нажмите <a href=\"".$_POST["url"]."\">эту ссылку</a>.", "Авторизация успешна");
 	} else throw new RedirectException(mr::host("site")."/signupfailed.xml");
 }

	}