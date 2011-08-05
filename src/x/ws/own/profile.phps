<?php class x_ws_own_profile extends x implements i_xmod {
	
 static public function action($x)
 {
 	return self::call($x, __CLASS__);
 }
 
 static public function main()
 {
 	if(!ws_self::ok())
 		die("<strong>В ходе обновления данных профиля потеряна авторизация. Обновление не произведено.</strong>");
 		
 	ws_self::self()->nick = mr_text_string::remove_excess( trim($_POST["nick"]) );
 	ws_self::self()->fio = mr_text_string::remove_excess( trim($_POST["fio"]) );
 	ws_self::self()->set_name_by = $_POST["set_name_by"];
 	
 	ws_self::self()->icq = (int)$_POST["icq"];
 	ws_self::self()->url = mr_text_string::remove_excess( trim($_POST["url"]) );
 	//ws_self::self()->city
 	//ws_self::self()->burthdate
 	ws_attach::checkXML(ws_self::self()->userinfo, ws_attach::decrement);
 	ws_self::self()->userinfo = mr_text_trans::text2xml( trim($_POST["userinfo"]), mr_text_trans::plain );
 	ws_attach::checkXML(ws_self::self()->userinfo, ws_attach::increment);
 	ws_self::self()->signature = mr_text_string::remove_excess( trim($_POST["signature"]) );
 	
 	//ws_self::self()->email
 	//ws_self::self()->md5
 	
 	ws_self::self()->blog_title = mr_text_string::remove_excess( trim($_POST["blog_title"]) );
 	ws_self::self()->blog_perpage = (int)$_POST["blog_perpage"];
 	 	
 	echo ws_self::self()->save() ? "<strong>Изменения успешно сохранены.</strong>" : "<b>Сохранить изменения не удалось</b>";
 } 
	}
?>