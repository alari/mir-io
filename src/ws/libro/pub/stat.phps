<?php class ws_libro_pub_stat extends mr_abstract_change {

	const sqlTable = "mr_pub_stat";
	
	static protected $objs=array(), $byPub=array(), $pubStat=array(), $current;
	
/**
 * Factory project
 *
 * @param int $id
 * @param array $arr
 * @return ws_libro_pub_stat
 */
 static public function factory($id, $arr=false)
 {
  if(!(@self::$objs[$id] instanceof self))
  	self::$objs[$id] = new self($id, $arr);
  return self::$objs[$id];
 }
 private function __construct($id, $arr)
 {
  $this->id = (int)$id;
  $this->arr = is_array($arr) ? $arr : mr_sql::fetch("SELECT * FROM ".self::sqlTable." WHERE id=".$this->id, mr_sql::assoc);
 }
 
/**
 * Текущий просмотр. Засчитывать ли?
 *
 * @param int $pub_id
 * @param bool $encount=false
 * @return ws_libro_pub_stat
 */
 static public function current($pub_id, $encount=false)
 {
  if(!(self::$current instanceof self))
  {
  	$a = mr_sql::fetch(array("SELECT * FROM ".self::sqlTable." WHERE pub_id=? AND user_id=?", $pub_id, ws_self::id()?ws_self::id():ws_user::anonime ), mr_sql::assoc);
  	if(is_array($a))
  	{
  		self::$current = self::factory( $a["id"], $a );
  		if( $encount )
  		{
  			self::$current->views += 1;
  			self::$current->last_view = time();
  			self::$current->save();
  		}
  	} else {
  		mr_sql::qw("INSERT INTO ".self::sqlTable."(pub_id, user_id, first_view, last_view) VALUES(?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
  			$pub_id, ws_self::id()?ws_self::id():ws_user::anonime);
  		self::$current = self::factory( mr_sql::insert_id() );
  	}
  }
  return self::$current;
 }
 
/**
 * Вся статистика по произведению
 *
 * @param int $id
 * @return mr_list
 */
 static public function byPub($id)
 {
  if(!(self::$byPub[$id] instanceof mr_list))
  {
  	$r = mr_sql::query("SELECT * FROM ".self::sqlTable." WHERE pub_id=".$id);
  	$ids = array();
  	while($f = mr_sql::fetch($r, mr_sql::assoc))
  	{
  		$ids[] = $f["id"];
  		self::factory($f["id"], $f);
  	}
  	self::$byPub[$id] = new mr_list(__CLASS__, $ids);
  }
  return self::$byPub[$id];
 }

/**
 * Основная статистика произведения: views, users, votes, clubvotes
 *
 * @param int $pub_id
 * @return object
 */
 static public function pubStat($pub_id)
 {
  if( !isset(self::$pubStat[$pub_id]) || !is_object(self::$pubStat[$pub_id]) )
  	self::$pubStat[$pub_id] = mr_sql::fetch(array("SELECT SUM(views) AS views, COUNT(pub_id) AS users, COUNT(IF(vote!=0,1,NULL)) AS votes, COUNT(IF(clubvote!=0,1,NULL)) AS clubvotes FROM ".self::sqlTable." WHERE pub_id=?", $pub_id), mr_sql::obj);
  return self::$pubStat[$pub_id];
 }
 
/**
 * Строчки статистики только с теми, кто голосовал в клубном
 *
 * @param int $pub_id
 * @return array of mr_list
 */
 static public function pubClubStat($pub_id)
 {
  $ss = self::byPub($pub_id);
  $return = array(
  	6 => null,
  	3 => null,
  	1 => null,
  	-1 => null
  );
  foreach($ss as $s) if( $s->clubvote!=0 ) $return[ $s->clubvote ][] = $s->id();
  foreach($return as &$v) if(count($v)) $v = new mr_list(__CLASS__, $v);
  return $return;
 }
 
/**
 * Засчитывает голос за произведение
 *
 * @param int $pub_id
 * @param int $vote
 * @param int $clubvote
 */
 static public function vote($pub_id, $vote, $clubvote)
 {
  $c = self::current($pub_id);
  if($vote) $c->vote = abs($vote)<=4 ? $vote : 0;
  $c->clubvote = $clubvote;
  $c->save();
  
  $p = $c->pub()->fullItem();
  $p->rating = 10+mr_sql::fetch(array("SELECT SUM(vote) FROM mr_pub_stat WHERE pub_id=?", $pub_id), mr_sql::get)/100;
  
  $p->clubscore = self::clubscore($pub_id);
  $p->save();
 }
 
 /**
 * Возвращает клубный рейтинг произведения
 */
 static protected function clubscore($id)
 {
  $qc = mr_sql::fetch("SELECT COUNT(*) FROM mr_pub_stat WHERE pub_id=$id AND clubvote!=0", mr_sql::get);
  if($qc<2) return 0;
   $qoffset = ceil($qc/2)-1;
   $qlimit = $qc%2 ? 1 : 2;
  $qm = mr_sql::query("SELECT clubvote FROM mr_pub_stat WHERE pub_id=$id  AND clubvote!=0 ORDER BY clubvote LIMIT $qoffset, $qlimit");
  if($qlimit == 1) return mr_sql::fetch($qm, mr_sql::get);
  else {
   $q1 = mr_sql::fetch($qm, mr_sql::get);
   $q2 = mr_sql::fetch($qm, mr_sql::get);
   if($q1==-1 && $q2==1) return -1;
   if($q1==-1 && $q2==3) return 1;
   if($q1==-1 && $q2==6) return 3;
   if($q1==1 && $q2==3) return 1;
   if($q1==1 && $q2==6) return 3;
   if($q1==3 && $q2==6) return 3;
   if($q1==$q2) return $q1;
   return 0;
  }
 }
	
/**
 * Анонс произведения
 *
 * @return ws_libro_pub
 */
 public function pub()
 {
  return ws_libro_pub::factory($this->arr["pub_id"]);
 }
	
/**
 * Пользователь, с которого просмотр
 *
 * @return ws_user
 */
 public function user()
 {
  return ws_user::factory($this->arr["user_id"]);
 }
 
/**
 * Обновляет сумму отзывов и наличие рекомендации
 *
 */
 public function cache()
 {
  	$resp = mr_sql::fetch(array("SELECT SUM(size) AS s, COUNT(*) AS c FROM mr_pub_responses WHERE pub_id=? AND user_id=?", $this->arr["pub_id"], $this->arr["user_id"]), mr_sql::obj);
  	$advice = mr_sql::fetch(array("SELECT COUNT(*) FROM mr_pub_advices WHERE pub_id=? AND user_id=?", $this->arr["pub_id"], $this->arr["user_id"]), mr_sql::get);
  	
  	$this->__set("resp_size", $resp->s);
  	$this->__set("resp_count", $resp->c);
  	$this->__set("last_view", time());
  	$this->__set("advice", $advice?"yes":"no");
  	
  	$this->save();
 }
 
/**
 * Удаляет строчку статистики
 *
 */
 public function delete()
 {
 	mr_sql::qw("DELETE FROM ".self::sqlTable." WHERE id=? LIMIT 1", $this->id());
 	unset($this);
 }
	}?>
