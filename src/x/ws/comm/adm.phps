<?php
class x_ws_comm_adm extends x implements i_xmod {

	static public function action($x) {
		return self::call ( $x, __CLASS__ );
	}

	/**
	 * Расширенные настройки сообщества -- сферы и направления
	 *
	 */
	static public function advanced() {
		self::check_rights ( $comm = ws_comm::factory ( ( int ) $_POST ["id"] ), true );

		$comm->adv_limit = ( int ) $_POST ["adv_limit"];

		$sph = $_POST ["sphere"];
		foreach ( $sph as $k => $v )
			if (! ws_comm::$org_spheres [$v])
				unset ( $sph [$k] );
		$dir = $_POST ["direct"];
		foreach ( $dir as $k => $v )
			if (! ws_comm::$org_directs [$v])
				unset ( $dir [$k] );

		$comm->org_sphere = $sph;
		$comm->org_direct = $dir;

		echo "<b>", ($comm->save () ? "Изменения сохранены успешно" : "Сохранить изменения не удалось"), "</b>";
	}

	/**
	 * Настройки представления и описание сообщества
	 *
	 */
	static public function display() {
		self::check_rights ( $comm = ws_comm::factory ( ( int ) $_POST ["id"] ) );

		$title = mr_text_string::remove_excess ( trim ( $_POST ["title"] ) );
		if (strlen ( $title ) > 2)
			$comm->title = $title;

		$comm->description = mr_text_string::remove_excess ( trim ( $_POST ["description"] ) );
		$comm->descr_medium = mr_text_string::remove_excess ( trim ( $_POST ["descr_medium"] ) );

		$comm->rules = mr_text_trans::text2xml ( trim ( $_POST ["rules"] ), mr_text_trans::prose );

		$comm->display_page_line = $_POST ["display_page_line"];
		$comm->display_discs = $_POST ["display_discs"];
		$comm->display_events = $_POST ["display_events"];
		$comm->display_cols = $_POST ["display_cols"];
		$comm->display_pubs = $_POST ["display_pubs"];

		echo "<b>", ($comm->save () ? "Изменения сохранены успешно" : "Сохранить изменения не удалось"), "</b>";
	}

	/**
	 * Настройки приёма и обсуждения произведений
	 *
	 */
	static public function pubs() {
		self::check_rights ( $comm = ws_comm::factory ( ( int ) $_POST ["id"] ) );

		$comm->apply_prose = $_POST ["apply_prose"];
		$comm->apply_stihi = $_POST ["apply_stihi"];
		$comm->apply_article = $_POST ["apply_article"];

		$comm->apply_pubs = $_POST ["apply_pubs"];
		if ($comm->type == "meta" || $comm->type == "closed") {
			$comm->apply_pubs_disc = $_POST ["apply_pubs_disc"];
			$comm->apply_pubs_adm = $_POST ["apply_pubs_adm"];
		} else {
			$comm->editors_apply = $_POST ["editors_apply"];
		}

		echo "<b>", ($comm->save () ? "Изменения сохранены успешно" : "Сохранить изменения не удалось"), "</b>";
	}

	/**
	 * Настройка правил приёма участников в сообщество
	 *
	 */
	static public function members() {
		self::check_rights ( $comm = ws_comm::factory ( ( int ) $_POST ["id"] ) );

		$comm->apply_members = $_POST ["apply"];

		echo "<b>", ($comm->save () ? "Изменения сохранены успешно" : "Сохранить изменения не удалось"), "</b>";
	}

	/**
	 * Настройка рецензий в сообщество
	 *
	 */
	static public function recenses() {
		self::check_rights ( $comm = ws_comm::factory ( ( int ) $_POST ["id"] ) );

		$comm->recense_apply = $_POST ["apply"];
		$comm->recense_method = $_POST ["method"];

		echo "<b>", ($comm->save () ? "Изменения сохранены успешно" : "Сохранить изменения не удалось"), "</b>";
	}

	/**
	 * Выписка приглашения из контрольной панели по логину
	 *
	 */
	static public function invite() {
		self::check_rights ( $comm = ws_comm::factory ( ( int ) $_POST ["id"] ) );

		$st = $_POST ["st"];
		if ($st > ws_comm::st_curator)
			$st = ws_comm::st_curator;
		if ($st < ws_comm::st_member)
			$st = ws_comm::st_member;

		$login = mr_text_string::remove_excess ( $_POST ["invite"] );
		$u = ws_user::getByLogin ( $login );
		if (! $u || strtolower ( $u->login ) != strtolower ( $login ))
			die ( "Пользователь $login не найден" );

		if (! $u->is_member ( $comm->id () ) && $u->is_member ( $comm->id () ) !== 0) {
			$m = ws_comm_member::create ( $comm->id (), $u->id (), "auth", $st );
			echo ($m && $m->status ? "Приглашение создано: " : "Ошибка при создании приглашения: ") . $u->link () . " <i>(" . $m->status () . ")</i>";
		} else
			echo "Пользователь " . $u->link () . " уже связан с сообществом";
	}

	/**
	 * Настройки главной странички
	 *
	 */
	static public function front() {
		self::check_rights ( $comm = ws_comm::factory ( ( int ) $_POST ["id"] ) );

		foreach ( $_POST as $k => $v )
			if ($comm->__get ( "front_" . $k ) !== null)
				$comm->__set ( "front_" . $k, $v );

		echo "<b>", ($comm->save () ? "Изменения сохранены успешно" : "Сохранить изменения не удалось"), "</b>";
	}

	/**
	 * Создание сообщества
	 *
	 */
	static public function create() {

	}

	static private function check_rights($comm, $advanced = false) {
		if (! $comm->name)
			throw new ErrorPageException ( "Сообщество не найдено", 404 );
		if (! ws_self::is_allowed ( $advanced ? "comm_control_advanced" : "comm_control", $comm->id () ))
			throw new ErrorPageException ( "У Вас нет прав на контроль этого сообщества", 403 );
	}

}