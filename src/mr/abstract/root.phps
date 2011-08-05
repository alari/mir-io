<?php abstract class mr_abstract_root extends mr_abstract_change2 {
	
 static private $objs = array();
 protected $className, $tableName;
	
/**
 * Конструктор - factory project
 *
 * @param int $id
 * @param string $class
 * @param array $arr
 * @return mr_abstract_tree
 */
 static public function factory($id, $class, array $arr=null)
 {
 	$objs = &self::$objs[$class];
 	if(!($objs[$id] instanceof self))
 		$objs[$id] = new self($id, $class, $arr);
 	return $objs[$id];
 }
 
/**
 * Загрузка по полному sql-запросу
 *
 * @param string $query
 * @param string $class
 * @param int $calcFoundRows
 * @return mr_list
 */
 static public function loadByQuery($query, $class, &$calcFoundRows=false)
 {
 	$r = mr_sql::query($query);
 	if($calcFoundRows !== false) $calcFoundedRows = mr_sql::found_rows();
 	
 	$ids = array();
 	if(mr_sql::num_rows($r))
 	{
	 	while($f = mr_sql::fetch($r, mr_sql::assoc))
	 	{
	 		$ids[] = $f["id"];
	 		self::factory($f["id"], $class, $f);
	 	}
	 	call_user_func(array($class, "_onLoad"), $ids);
 	}
 	return new mr_list($class, $ids);
 }
 
/**
 * Функция для полной подгрузки набора нод пользовательским классом
 *
 * @param array $ids
 */
 static public function _onLoad(Array $ids)
 {
 	return;
 }
 
/**
 * Абстрактный конструктор для одного узла
 *
 * @param int $id
 * @param string $class
 * @param array $arr
 */
 protected function __construct($id, $class, Array $arr=null)
 {
 	$this->className = $class;
 	$this->tableName = constant($class."::sqlTable");
 	$this->id = $id;
 	$this->arr = is_array($arr) ? $arr : mr_sql::fetch(array("SELECT * FROM ".$this->tableName." WHERE id=?", $this->id), mr_sql::assoc);
 	if(!count($this->arr))
 	{
 		$this->id = null;
 		$this->arr = null;
 	}
 }

/**
 * Возвращает путь до этого узла от корня. Сам узел не включает
 *
 * @return mr_list
 */
 public function getPath()
 {
 	return self::loadByQuery(
 		"SELECT *
 		FROM ".$this->tableName."
 		WHERE left_key<".$this->arr["left_key"]
 			." AND right_key>".$this->arr["right_key"]
 		." ORDER BY left_key",
 	$this->className);
 }
 
/**
 * Возвращает ветку, в которой участвует этот узел, от корня
 *
 * @return mr_list
 */
 public function getBranch()
 {
 	return self::loadByQuery(
 		"SELECT *
 		FROM ".$this->tableName."
 		WHERE left_key<".$this->arr["right_key"]
 			." AND right_key>".$this->arr["left_key"]
 		." ORDER BY left_key",
 	$this->className);
 }
 
/**
 * Возвращает родительскую ноду, если она есть
 *
 * @return mr_abstract_tree
 */
 public function getParent()
 {
 	$r = self::loadByQuery(
 		"SELECT *
 		FROM ".$this->tableName."
 		WHERE left_key<".$this->arr["left_key"]
 			." AND right_key>".$this->arr["right_key"]
 			." AND level = ".$this->arr["level"]."-1"
 		." ORDER BY left_key LIMIT 1",
 	$this->className);
 	return count($r) ? $r[0] : null;
 }
 
/**
 * Возвращает всех детей до указанной глубины, сортировка по левому ключу.
 * Если 0, возвращает всех детей
 *
 * @param int $depth
 * @return mr_list
 */
 public function getChilds($depth=0)
 {
 	return self::loadByQuery(
 		"SELECT *
 		FROM ".$this->tableName."
 		WHERE left_key>".$this->arr["left_key"]
 			." AND right_key<".$this->arr["right_key"]
 			.($depth ? " AND level<=".($this->arr["level"]+$depth) : "")
 		." ORDER BY left_key",
 	$this->className);
 }
 
/**
 * Возвращает все листья этого узла, если сам лист -- возвращает null, на ошибку -- false
 *
 * @return mr_list
 */
 public function getLeaves()
 {
 	return self::loadByQuery(
 		"SELECT *
 		FROM ".$this->tableName."
 		WHERE left_key>".$this->arr["left_key"]
 			." AND right_key<".$this->arr["right_key"]
 			." AND right_key-left_key=1"
 		." ORDER BY left_key",
 	$this->className);
 }
 
/**
 * Считает всех детей на указанную глубину
 *
 * @param int $depth
 * @return int
 */
 public function countChilds($depth=0)
 {
 	return mr_sql::fetch(
 		"SELECT COUNT(left_key)
 		FROM ".$this->tableName."
 		WHERE left_key>".$this->arr["left_key"]
 			." AND right_key<".$this->arr["right_key"]
 			.($depth ? " AND level<=".($this->arr["level"]+$depth) : "")
 		." ORDER BY left_key",
 	mr_sql::get);
 }
 
/**
 * Считает все листья. Саму ноду не считает, если она -- лист.
 *
 * @return int
 */
 public function countLeaves()
 {
 	return mr_sql::fetch(
 		"SELECT *
 		FROM ".$this->tableName."
 		WHERE left_key>".$this->arr["left_key"]
 			." AND right_key<".$this->arr["right_key"]
 			." AND right_key-left_key=1"
 		." ORDER BY left_key",
 	mr_sql::get);
 }
 
/**
 * Удаляет ноду со всеми детьми
 *
 */
 public function revoke()
 {
 	mr_sql::query(
 		"DELETE
 		FROM ".$this->tableName."
 		WHERE left_key>".$this->arr["left_key"]
 			." AND right_key<".$this->arr["right_key"]);
 	return mr_sql::affected_rows();
 }
 
/**
 * Перемещает ноду с детьми в новый корень root
 *
 * @param int $newParent
 */
 public function injectInto($newParent)
 {
 	
 }
 
/**
 * Убирает ноду из текущего дерева. Нумерует ключи с единицы, обнуляет parent
 *
 */
 public function remove()
 {
 	
 }
  
/**
 * Вставляет ноду перед собой, на том же уровне
 *
 * @param mr_abstract_tree $node
 */
 public function insertBefore(mr_abstract_tree $node)
 {
 	
 }
 
/**
 * Вставляет ноду после себя на том же уровне
 *
 * @param mr_abstract_tree $node
 */
 public function insertAfter(mr_abstract_tree $node)
 {
 	
 }
 
/**
 * Добавляет дитя, вставляет его перед остальными
 *
 * @param mr_abstract_tree $node
 */
 public function insertTop(mr_abstract_tree $node)
 {
 	
 }
 
/**
 * Добавляет дитя, вставляет его после прочих
 *
 * @param mr_abstract_tree $node
 */
 public function insertBottom(mr_abstract_tree $node)
 {
 	
 }
	
} ?>