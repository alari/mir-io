<?php
	class ws_libro_pub_resp extends ws_abstract_comment implements i_comment_reminder, i_comment_afterform {
  
		const sqlTable = "mr_pub_responses";

 static public function several($where="1=1", $limit=0, $offset=0, $order="time DESC", &$calcResult=false)
 {
 	$return = parent::sub_several(__CLASS__, $where, $limit, $offset, $order, $calcResult);
 	$pubs = array();
 	foreach($return as $r)
 	{
 		if(!in_array($r->pub_id, $pubs)) $pubs[] = $r->pub_id;
 	}
 	if(count($pubs)>1) ws_libro_pub::several($pubs);
 	ws_comm_pub_anchor::loadByResps($return->ids());
 	return $return;
 }
 
/**
 * Интерпретирует результат запроса $query как массивы для отзывов
 *
 * @param string $query
 * @param bool $loadUsersVsAvas
 * @return array
 */
 static public function several_query($query, &$calcFoundRows=false)
 {
 	$return = parent::sub_several_query(__CLASS__, $query, $calcFoundRows);
 	$pubs = array();
 	foreach($return as $r)
 	{
 		if(!in_array($r->pub_id, $pubs)) $pubs[] = $r->pub_id;
 	}
 	if(count($pubs)>1) ws_libro_pub::several($pubs);
 	ws_comm_pub_anchor::loadByResps($return->ids());
 	return $return;
 }
 
/**
 * Сообщества, где это -- рецензия
 *
 * @return array of ws_comm_pub_anchor
 */
 public function rec()
 {
 	return ws_comm_pub_anchor::byResp($this->id);
 }
 
/**
 * Factory project
 *
 * @param int $id
 * @param array $arr
 * @return ws_libro_pub_resp
 */
 static public function factory($id, $arr=false)
 {
 	return parent::sub_factory(__CLASS__, $id, $arr);
 }
		
/**
 * Создаёт новое сообщение, которое потом нужно обработать!
 *
 * @param int $pub_id Родительское произведение
 * @param int $user_id Автор комментария
 * @param text $content текст контента
 * @return ws_libro_pub_resp
 */
 static public function create($pub_id, $user_id, $content)
 {
 	$t = new mr_text_trans($content);
 	$t->t2x(mr_text_trans::plain);
  mr_sql::qw("INSERT INTO ".self::sqlTable."(pub_id, user_id, content, size, time, remote_addr) VALUES(?, ?, ?, ?, UNIX_TIMESTAMP(), ?)",
  	$pub_id, $user_id?$user_id:ws_user::anonime, $t->finite(), $t->getAuthorSize(4), $_SERVER['REMOTE_ADDR']);
  
  	return self::factory(mr_sql::insert_id());
 }

/**
 * Устанавливает связки рецензий на сообщение. Обязательно должна быть связка с сообществом
 *
 * @param array $rec($comm=>[strong(yes|no),category])
 */
 public function set_recenses(array $rec)
 {
 	$comms = array_keys($rec);
 	$anchors = $this->rec();
 	foreach ($anchors as $a) {
 		if(!in_array($a->id(), $comms))
 			$a->resp_id = 0;
 		else {
 			$a->category = $rec[ $a->comm_id ][1];
 			$a->editor = $this->arr["user_id"];
 		}
 		$a->save();
 		unset( $rec[ $a->comm_id ] );
 	}
 }
     
/**
 * Родительское произведение (анонс)
 *
 * @return ws_libro_pub
 */
 public function pub()
 {
  return ws_libro_pub::factory($this->arr["pub_id"]);
 }
  
 public function xml($tagName="resp")
 {
 	$x = parent::xml($tagName);
 	if(count($this->rec))
 	{
 		list($x, $a) = explode(">", $x, 2);
 		$x.="><recense>";
 		foreach($this->rec as $r) $x .= "<rec-comm id=\"$r->comm_id\"/>";
 		$x.="</recense>".$a;
 	}
 	return $x;
 }
 
/**
 * Отправляет оповещение подписчикам об этом отзыве
 *
 */
 public function notify_subscribers()
 {
 	$eml_title = "Новый отзыв на произведение: ".$this->pub()->title;
 	
 	$u = ws_user::factory($this->arr["user_id"]);
 	
 	$eml_body = mr_text_trans::node2text($this->arr["content"])."
 	
 	Произведение: ".$this->pub()->href()."
 	Отзыв оставлен: ".$u->name()." (".$u->href().")
 	
 Вы получили это сообщение потому, что оформили подписку на новые отзывы в данном обсуждении. Чтобы отписаться, снимите галочку внизу странички.";
 	
 	ws_user_remind::event(ws_user_remind::type_pub_resp, $this->arr["pub_id"], $eml_title, $eml_body, $u->id(), $this->arr["time"]);
 	
 	$p = $this->pub();
 	// Поступок с автором
 	if($p->author(true)->id() != $this->arr["user_id"])
 	{
 		$auth = $p->author(true);
 		if($auth->set_responses_notify == "email")
 		{
 			@mail($auth->email, "Новый отзыв на вашу публикацию \"".$p->title."\" - Мирари", $eml_body, "From: noreply@mirari.ru
To: ".$auth->email."
Content-type: text/plain; charset=utf-8");
 		} elseif($auth->set_responses_notify == "reminder") {
 			ws_user_remind::create(ws_user_remind::type_pub_resp, $p->id(), $p->author(true)->id(), ws_user_remind::method_reminder, true);
 		}
 	}
 }
 
/**
 * Удаляет отзыв
 *
 * @return ищщд
 */
 public function delete()
 {
	mr_sql::qw("DELETE FROM mr_pub_resp_votes WHERE resp_id=?", $this->id);
 	mr_sql::qw("UPDATE mr_comm_pubs SET resp_id=0 WHERE resp_id=?", $this->id);
 	mr_sql::qw("DELETE FROM mr_pub_responses WHERE id=? LIMIT 1", $this->id);
 		$a = mr_sql::fetch("SELECT SUM(size) AS s, COUNT(*) AS c FROM mr_pub_responses WHERE pub_id=".$this->arr["pub_id"]." AND user_id=".$this->arr["user_id"], mr_sql::obj);
 	mr_sql::qw("UPDATE mr_pub_stat SET resp_size=?, resp_count=? WHERE user_id=? AND pub_id=?", $a->s, $a->c, $this->arr["user_id"], $this->arr["pub_id"]);
 	return parent::delete();
 }
 
 /**
 * Проверяет права пользователя и выводит, может ли сообщение быть показано
 *
 */
 public function is_showable()
 {
 	if(!$this->pub()->is_showable()) return false;
 	if($this->arr["hidden"] == "no" || $this->arr["user_id"]==ws_self::id()) return true;
 	if($this->arr["hidden"] == "yes" && ws_self::is_allowed("see_hidden", $this->pub()->meta)) return true;
 	if($this->pub()->author(true)->id()==ws_self::id() && $this->pub()->meta()->apply_pub_adm=="yes") return true;
 	return false;
 }
 
/**
 * Права администратора
 *
 */
 public function can_edit()
 {
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	if(ws_self::is_allowed("to_delete_notes", $this->pub()->meta)) return true;
 	return false;
 }
 
 public function can_hide()
 {
 	if(ws_self::is_allowed("to_hide", $this->pub()->meta)) return true;
 	if($this->pub()->author(true)->id()==ws_self::id() && $this->pub()->meta()->apply_pubs_adm == "yes") return true;
 	return false;
 }
 
 public function can_delete()
 {
 	if($this->arr["user_id"] == ws_self::id()) return true;
 	if(ws_self::is_allowed("to_delete_notes", $this->pub()->meta)) return true;
 	//if($this->pub()->author(true)->id() == ws_self::id() && ws_self::is_member($this->pub()->meta) && $this->pub()->meta()->apply_pubs_adm == "yes") return true;
 	return false;
 }
 
 public function parent_link()
 {
 	return "Отзыв на произведение: ".$this->pub()->link().", ".$this->pub()->author()->link();
 }
 
 public function out_pre()
 {
 	 if(count($this->rec())){
 	 	ob_start();
 	 	?><p class="comment-rec">Рецензия в: <em><?foreach($this->rec() as $k=>$c) echo $k?", ":"", $c;?></em></p><?
 	 	return ob_get_clean();
 	 }
 	 return "";
 }
 
 public function out_adm()
 {
 	return '<a href="javascript:void(0)">'.self::mark_name($this->arr["score"]).'</a>';
 }
 
 static public function mark_name($m)
 {
 	switch($m)
 	{
 		case 2: return "Очень хорошо (+2)";
 		case 1: return "Хорошо (+1)";
 		case 0: return "Нет оценки (0)";
 		case -1: return "Плохо (-1)";
 		case -2: return "Очень плохо (-2)";
 	}
 }
 
/**
 * Для ремайндера
 *
 * @param int $parent
 * @return int
 */
 static public function reminder( $parent )
 {
 	if(!$parent) return false;
 	$parent = ws_libro_pub::factory($parent);
 	if(!$parent) return false;
 	if( $parent->author(true)->id() != ws_self::id() )
 		return ws_user_remind::type_pub_resp;
 }
 
/**
 * Рецензии
 *
 * @param int $parent
 * @return string
 */
 static public function afterform( $parent )
 {
 	if(!ws_self::ok()) return "";
 	if(!$parent) return "";
 	$parent = ws_libro_pub::factory($parent);
 	if(!$parent) return "";
 	if($parent->author(true)->id() == ws_self::id()) return "";
 	$anch = $parent->comm_anchors();
 	if(!count($anch)) return "";
 	
 	$comms = new mr_list("ws_comm", array());
 	$ccs = new mr_list("ws_comm_pub_categ", array());
 	foreach($anch as $a)
 	{
 		if($a->resp_id) continue;
 		/* @var $c ws_comm */
 		$c = $a->comm();
 		if(!$c->can_make_recense()) continue;
 		
 		if($a->editor == ws_self::id() || !$a->editor)
 		{
 			$comms[] = $c;
 			if($a->category) $ccs[ $c->id() ] = $a->category();
 		}
 	}
 	// Теперь у нас есть $comms, где все сообщества, где могут быть рецензии
 	if(!count($comms)) return "";
 	
 	foreach($comms as $c)
 	{
 		$categs = ws_comm_pub_categ::several($c->id(), false, true);
?>
	<div class="comment-rec">
		<input type="hidden" name="rec-<?=$c->id()?>" value="no"/>
		<input type="checkbox" name="rec-<?=$c->id()?>" value="yes"/>
			&ndash; Рецензия в сообществе <?=$c?>
		<?if(count($categs)){?>
		<div align="right">
			Категория:
			<select name="categ-<?=$c->id()?>">
				<?if($ccs[$c->id()] && !in_array($ccs[$c->id()]->id(), $categs->ids())){?>
					<option value="<?=$ccs[$c->id()]?>"><?=$ccs[$c->id()]->title?></option>
				<?}?>
				<?foreach($categs as $ca){?>
					<option value="<?=$ca->id()?>"><?=$ca->title?></option>
				<?}?>
			</select>
		</div>
		<?}?>
	
	</div>
<?
 	}
 }
		}
?>