<?php class mr_abstract_node {
	
 protected $tableName;
 protected $node;
 protected $root;
	
 /**
  * Создаёт имплементацию для nested set конкретного класса.
  *
  * @param string $classOrNode
  * @param string $root Корень, если нет узла и корень есть
  */
 public function __construct($classOrNode, $root=null)
 {
 	if($tableOrNode instanceof mr_abstract_change || $tableOrNode instanceof mr_abstract_change2)
 	{
 		$this->node = $tableOrNode;
 		$this->tableName = constant(get_class($this->node)."::sqlTable");
 		$this->root = $this->node->root;
 	} else {
 		$r = new ReflectionClass($classOrNode);
 		if($r->isSubclassOf("mr_abstract_change") || $r->isSubclassOf("mr_abstract_change2"))
 		{
 			$this->tableName = constant($classOrNode."::sqlTable");
 			$this->root = $root;
 		} else throw new Exception("Wrong class for nested nodes: $classOrNode");
 	}
 }
 
 public function __get($name)
 {
 	return $this->node->$name;
 }
 
 public function __set($name, $value)
 {
 	return $this->node->$name=$value;
 }
 
 public function save()
 {
 	return $this->node->save();
 }

/**
 * Возвращает путь до этого узла от корня. Сам узел не включает. Работает только с node.
 *
 * @return mr_sql_query
 */
 public function getPath()
 {
 	$q = new mr_sql_query($this->tableName);
 	$q->where("left_key<? AND right_key>?",
 		$this->left_key, $this->right_key);
 		
 	if(is_int($this->root)) $q->where("root=".$this->root);
 		
 	$q->order("left_key");
 	return $q;
 }
 
/**
 * Возвращает ветку, в которой участвует этот узел, от корня. Работает только с node.
 *
 * @return mr_sql_query
 */
 public function getBranch()
 {
 	if(!$this->node) return false;
 	$q = new mr_sql_query($this->tableName);
 	$q->where("left_key<? AND right_key>?",
 		$this->right_key, $this->left_key);
 		
 	if(is_int($this->root)) $q->where("root=".$this->root);
 	
 	$q->order("left_key");
 	return $q;
 }
 
/**
 * Возвращает родительскую ноду, если она есть. Работает только с node.
 *
 * @return mr_sql_query
 */
 public function getParent()
 {
 	if(!$this->node || $this->level <= 1) return false;
 	$q = new mr_sql_query($this->tableName);
 	$q->where("left_key<? AND right_key>? AND level=?",
 			$this->left_key, $this->right_key, $this->level-1);
 			
 	if(is_int($this->root)) $q->where("root=".$this->root);
 			
 	return $q;
 }
 
/**
 * Возвращает всех детей до указанной глубины, сортировка по левому ключу.
 * Если 0, возвращает всех детей. Может работать без node.
 *
 * @param int $depth
 * @return mr_sql_query
 */
 public function getChilds($depth=0)
 {
 	$q = new mr_sql_query($this->tableName);
 	
 	if($this->node) $q->where("left_key>? AND right_key<?",
 		$this->left_key, $this->right_key);
 	if(is_int($this->root)) $q->where("root=".$this->root);
 	if($depth>0) $q->where("level<=?",
 		$this->node?($this->level+$depth):$depth);
 	
 	$q->order("left_key");
 		
 	return $q;
 }
 
/**
 * Возвращает все листья этого узла, если узел указан.
 *
 * @return mr_sql_query
 */
 public function getLeaves()
 {
 	$q = new mr_sql_query($this->tableName);
 	$q->where("right_key-left_key=1");
 	if(is_int($this->root)) $q->where("root=".$this->root);
 	if($this->node) $q->where("left_key>? AND right_key<?",
 		$this->left_key, $this->right_key);
 		
 	$q->order("left_key");
 	
 	return $q;
 }
 
/**
 * Считает всех детей на указанную глубину
 *
 * @param int $depth
 * @return int
 */
 public function countChilds($depth=0)
 {
 	$q = $this->getChilds($depth);
 	$q->field("COUNT(left_key) AS childs");
 	return mr_sql::fetch(
 		$q->prepare_select(), mr_sql::get
 	);
 }
 
/**
 * Считает все листья. Саму ноду не считает, если она -- лист.
 *
 * @return int
 */
 public function countLeaves()
 {
 	$q = $this->getLeaves();
 	$q->field("COUNT(left_key) AS leaves");
 	return $q->select(mr_sql::get);
 }
 
/**
 * Удаляет ноду со всеми детьми
 *
 */
 public function revoke()
 {
 	$q = $this->getBranch();
 	$q->whereOR("id=".$this->id);
 	return $q->delete();
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
 * @param mr_abstract_node $child
 */
 public function injectBefore(mr_abstract_node $child)
 {
 	
 }
 
/**
 * Вставляет ноду после себя на том же уровне
 *
 * @param mr_abstract_node $child
 */
 public function injectAfter(mr_abstract_node $child)
 {
 	
 }
 
/**
 * Добавляет дитя, вставляет его перед остальными
 *
 * @param mr_abstract_node $child
 */
 public function injectTop(mr_abstract_node $child)
 {
 	$q = new mr_sql_query($this->tableName);
 	
 	$old_left = $child->left_key;
 	$old_right = $child->right_key;
 	
 	$new_level = $this->node?$this->level+1:0;
 	
 	$_2N = $old_right-$old_left+1;
 	
	$move_old_levels = $new_level-$old_level;
 		
	// Вставка в узел с тем же корнем
 	if($this->node && (!$this->root || $this->root==$child->root))
 	{
 		// Совпадают
 		if($this->id == $child->id()) return true;
 		// Вставка родителя в дитя
 		if( $child->left_key<$this->left_key
 		 && $child->right_key>$this->right_key)
 			return false;
 		
 		// Учёт корня
 		if($this->root) $q->where("root=".$this->root);
 		
 		// Общие родители неизменны
 		$min_left = min($old_left, $this->left_key);
 		$max_right = max($old_right, $this->right_key);
 		$q->where("left_key>=? OR right_key<=?)",
 			$min_left, $max_right);
 		
 		// Изменение уровня одинаково
 		$q->updateCol(
	 		"level=IF(
	 			right_key<=? AND left_key>=?,
	 			level+(?),
	 			level
	 		)", $old_right, $old_left, $move_old_levels);
 			
	 	// Попробовать сделать запрос однотипным
	 		
 		// Смещение вправо
 		if($old_left<$this->left_key)
 		{
 			$new_left = $this->left_key-$old_right+$old_left;
			$new_right = $new_left + $old_right - $old_left;
		 	
			$move_old_keys = $new_right-$old_right;
			
			if(!$move_old_keys) return true;
 			
 			$q->updateCol(
	 			"left_key=IF(
 					right_key<=? AND left_key>=?,
 					left_key+?,
 					IF(
 						left_key>?,
 						left_key-?,
 						left_key
 				))", $old_right, $old_left, $move_old_keys, $old_right, $_2N
 			);
 			$q->updateCol(
 				"right_key=IF(
 					right_key<=? AND left_key>=?,
 					right_key+?,
 					IF(
 						right_key<?,
 						right_key-?,
 						right_key
 				))", $old_right, $old_left, $move_old_keys, $this->left_key, $_2N
 			);
 			$q->where("right_key>? AND left_key<=?",
 			 	$old_left, $this->left_key);
 			 	
 		// Смещение влево
 		} else {
 			$new_left = $this->left_key+1;
			$new_right = $new_left + $old_right - $old_left;
		 	
			$move_old_keys = $new_right-$old_right;
			
			if(!$move_old_keys) return true;
 			
			$q->updateCol(
 				"right_key=IF(
 					right_key<=? AND left_key>=?,
 					right_key+(?),
 					IF(
 						right_key<?,
 						right_key+?,
 						right_key
 				))", $old_right, $old_left, $move_old_keys, $old_left, $_2N
 			);
 			$q->updateCol(
	 			"left_key=IF(
 					right_key<=? AND left_key>=?,
 					left_key+(?),
 					IF(
 						left_key>?,
 						left_key+?,
 						left_key
 				))", $old_right, $old_left, $move_old_keys, $this->right_key, $_2N
 			);
 			$q->where("right_key>=? AND left_key<?",
 			 	$this->right_key, $old_right);
 		}
 		
 	// Вставка в узел с другим корнем
 	} elseif($this->node) {
 		
 	// Вставка в старый корень
 	} elseif(!$this->root || $this->root == $child->root) {
 		
 	// Вставка в новый корень
 	} elseif($this->root != $child->root) {
 		
 	}
 	
 	return false;
 	
	// Вставляем в нового родителя
 	/*
 	if($new_root != $old_root)
 	{
 		$new_left = $this->arr["left_key"]+1;
 		$new_right = $new_left+$_2N-1;
 		
	 	$move_old_keys = $new_right-$old_right;
 		
 		mr_sql::query(
 			"UPDATE ".$this->tableName.
 			"SET left_key=IF(left_key<=".$this->arr["left_key"].",left_key,left_key+$_2N), right_key=right_key+$_2N
 			WHERE right_key>".$this->arr["left_key"]." AND root=".$this->arr["root"]
 		);
 		mr_sql::query(
 			"UPDATE ".$this->tableName.
 			"SET left_key=left_key+($move_old_keys), right_key=right_key+($move_old_keys), level=level+($move_old_levels), root=$new_root
 			WHERE left_key>=$old_left AND right_key<=$old_right AND root=$old_root"
 		);
 		mr_sql::query(
 			"UPDATE ".$this->tableName.
 			"SET left_key=IF(left_key<".$old_left.",left_key,left_key-$_2N), right_key=right_key-$_2N
 			WHERE right_key>".$old_right." AND root=".$old_root
 		);
 		return;
 	}
 	*/
 }
 
/**
 * Добавляет дитя, вставляет его после прочих
 *
 * @param mr_abstract_node $child
 */
 public function injectBottom(mr_abstract_node $child)
 {
 	
 }
	
} ?>