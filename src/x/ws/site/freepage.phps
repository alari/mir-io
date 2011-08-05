<?php class x_ws_site_freepage implements i_xmod {
	static public function action($x)
	{
		if($x == "save")
		{
			$id = $_POST["id"];
			if($id != "create")
			{
				$page = mr_sql::fetch(array("SELECT * FROM mr_site_freepages WHERE id=?", (int)$_POST["id"]), mr_sql::obj);
			
				if(!is_object($page) || !tpl_page_ws_edit::can_edit($page))
					throw new ErrorPageException("Невозможно сохранить изменения странички", 403);
					
			} elseif(!tpl_page_ws_edit::can_create()) {
				
				throw new ErrorPageException("Невозможно создание странички", 403);
				
			}
				
			foreach($_POST as $key => $value) {
				$_POST[$key] = stripcslashes($value);
			}
			
			if($id != "create")
				mr_sql::qw("UPDATE mr_site_freepages SET url=?, site=?, layout=?, css=?, type=?, xsltransform=?, keywords=?, description=?, title=?, head=?, content=? WHERE id=?",
					$_POST["url"], $_POST["site"], $_POST["layout"], $_POST["css"], $_POST["type"], $_POST["xsltransform"], $_POST["keywords"], $_POST["description"], $_POST["title"], $_POST["head"], $_POST["content"], $_POST["id"]);
			elseif($_POST["url"] && $_POST["layout"] && $_POST["content"]) {
				mr_sql::qw("INSERT INTO mr_site_freepages(url, site, layout, css, type, xsltransform, keywords, description, title, head, content) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					$_POST["url"], $_POST["site"], $_POST["layout"], $_POST["css"], $_POST["type"], $_POST["xsltransform"], $_POST["keywords"], $_POST["description"], $_POST["title"], $_POST["head"], $_POST["content"]);
				$id = mr_sql::insert_id();
			}
				
			if(mr_sql::affected_rows())
				throw new RedirectException("/edit.id-".$id.".xml", 5, "Изменения успешно сохранены");
			else
				throw new RedirectException("/edit.id-".$id.".xml", 5, "Сохранить изменения не удалось или не было изменений.", "Ошибка");
		} elseif($x == "delete") {
			
			$id = (int)$_REQUEST["id"];			
			if(!tpl_page_ws_edit::can_create())
				throw new ErrorPageException("Невозможно удаление странички", 403);

			mr_sql::qw("DELETE FROM mr_site_freepages WHERE id=?", $id);
			
			throw new RedirectException("/edit.xml", 5, "Страничка успешно удалена");
			
		}
	}
}
?>