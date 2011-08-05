<?php class mr_sql_query extends mr_sql_where {

	protected $from = array();
	protected $fields = array();
	protected $update = array();
	protected $offset = 0;
	protected $having;
	protected $order = array();
	protected $limit;
	protected $joins = array();
	protected $sql_options = array();
	
	const calc_found_rows = "SQL_CALC_FOUND_ROWS";
	const cache = "SQL_CACHE";
	
public function __construct($tblName=null, $alias=null)
{
	if($tblName) $this->from[] = $tblName.($alias?" ".$alias:"");
}
 
/**
 * Управление оператором SELECT
 *
 * @param string $opt
 * @return mr_sql_query
 */
public function setSpecialOption($opt)
{
	$this->sql_options[] = $opt;
	return $this;
}
 
/**
 * Добавляет таблицу для выборки FROM
 *
 * @param string $tblName
 * @param string $alias
 * @return mr_sql_query
 */
public function from($tblName, $alias=null)
{
 	$this->from[] = $tblName.($alias?" ".$alias:"");
 	return $this;
}

/**
 * Добавляет поле для выборки
 *
 * @param string $col
 * @param string $as
 * @return mr_sql_query
 */
public function field($col, $as=null)
{
	$this->fields[] = array($col, $as);
	return $this;
}

/**
 * Добавляет таблицу для LEFT JOIN
 *
 * @param string $table
 * @param string|mr_sql_where $on
 * @param string $alias
 * @return mr_sql_query
 */
public function joinLEFT($table, $on, $alias="")
{
	return $this->joinANY($table, $on, $alias, "LEFT");
}

/**
 * Добавляет таблицу для RIGHT JOIN
 *
 * @param string $table
 * @param string|mr_sql_where $on
 * @param string $alias
 * @return mr_sql_query
 */
public function joinRIGHT($table, $on, $alias="")
{
	return $this->joinANY($table, $on, $alias, "RIGHT");
}

/**
 * Добавляет таблицу для INNER JOIN
 *
 * @param string $table
 * @param string|mr_sql_where $on
 * @param string $alias
 * @return mr_sql_query
 */
public function joinINNER($table, $on, $alias="")
{
	return $this->joinANY($table, $on, $alias, "INNER");
}

/**
 * Устраивает JOIN типа $type
 *
 * @param string $table
 * @param string|mr_sql_where $on
 * @param string $alias
 * @param string $type
 * @return mr_sql_query
 */
private function joinANY($table, $on, $alias, $type)
{
	if(!is_string($condition)
	&& !($condition instanceof mr_sql_where))
		return false;
		
	$this->joins[] = array($type, $table.($alias?" ".$alias:""), $on);
	return $this;
}

/**
 * Устанавливает сортировку
 *
 * @param string $field
 * @return mr_sql_query
 */
public function order($field)
{
	$this->order[] = $field;
	return $this;
}

/**
 * Параметры после запроса
 *
 * @param string|mr_sql_where $condition
 * @return mr_sql_query
 */
public function having($condition)
{
	if(is_string($condition)
	|| $condition instanceof mr_sql_where)
		$this->having = $condition;
	else return false;
	
	return $this;
}

/**
 * Добавляет значение поля для запроса UPDATE
 *
 * @param string $column
 * @param mr_sql_query|string $value Замены для ?
 * @return mr_sql_query
 */
public function updateCol()
{
	$this->update[] = func_get_args();
	return $this;
}

/**
 * Подготавливает SELECT-запрос и возвращает как строку
 *
 * @return string
 */
public function prepare_select()
{
	// Оператор
	$return = "SELECT ";
	
	// Особые команды типа SQL_CACHE
	foreach($this->sql_options as $o) $return .= $o." ";
	
	// Поля для выборки или *
	if(count($this->fields))
	{
		$flds = "";
		foreach($this->fields as $col) $flds .= ($flds?", ":"").$col[0].($col[1]?" AS ".$col[1]:"");
		$return .= $flds;
	}
	else $return .= "* ";
	
	// Таблицы выборки. FROM надо вставить, остальное -- в другой функции
	if(count($this->from)){
		$return .= "FROM ";
	}
	
	// Все прочие параметры
	$return = $this->prepare_query($return);
	
	return $return;	
}

/**
 * Производит выборку по запросу
 *
 * @param int $mode
 * @return mixed
 */
public function select($mode=0)
{
	if(!$mode)
		return mr_sql::query($this->prepare_select());
	if($mode == mr_sql::get || $mode == mr_sql::obj || $mode == mr_sql::assoc || $mode == mr_sql::num)
		return mr_sql::fetch($this->prepare_select(), $mode);
	return false;
}

/**
 * Подготавливает DELETE-запрос и возвращает как строку
 *
 * @return string
 */
public function prepare_delete()
{
	// Оператор
	$return = "DELETE ";
	
	// Таблицы выборки. FROM надо вставить, остальное -- в другой функции
	if(count($this->from)){
		$return .= "FROM ";
	}
	
	// Все прочие параметры
	$return = $this->prepare_query($return);
	
	return $return;	
}

/**
 * Удаляет выборку
 *
 * @return int
 */
public function delete()
{
	mr_sql::query($this->prepare_delete());
	return mr_sql::affected_rows();
}

/**
 * Подготавливает запрос на обновление
 *
 * @return string
 */
public function prepare_update()
{
	// Оператор
	$query = "UPDATE ";
	
	// Таблица
	foreach($this->from as $k=>$from)
		$query .= ($k?", ":"").$from;
		
	$query .= " ";
		
	if(!count($this->update))
		return false;
		
	$query .= "SET ";
	foreach($this->update as $k=>$v)
	{
		$col = array_shift($v);
		$query .= ($k?", ":"").(count($v)?mr_sql::queryWrapper($col, $v):$col);
	}
	
	// переданные параметры WHERE
	if(count($this->where))
		$query .= "WHERE ".parent::prepare();
		
	// сортировки
	if(count($this->order))
	{
		$query .= " ORDER BY";
		foreach($this->order as $i=>$o) $query .= ($i?",":"")." ".$o;
	}
	
	// Смещение и лимит, если есть
	if($this->limit)
		$query .= "LIMIT ".($this->offset?$this->offset.", ":"").$this->limit;
		
	// В конце всего запроса -- HAVING
	if($this->having)
		$query .= "HAVING ".($this->having instanceof mr_sql_where ? $this->having->prepare() : $this->having);
		
	return $query;
}

/**
 * Update-запрос
 *
 * @return int
 */
public function update()
{
	mr_sql::query($this->prepare_update());
	return mr_sql::affected_rows();
}

/**
 * SELECT и ::factory(Array $assoc)
 *
 * @param string $intoClass
 * @return mr_list
 */
public function fetch($intoClass)
{
	$r = $this->select();
	$list = new mr_list($intoClass);
	while($a = mr_sql::fetch($r, mr_sql::assoc)){
		$list[] = call_user_func(array($intoClass, "factory"), $a);
	}
	return $list;
}

/**
 * Подготавливает строчку начиная с имён таблиц
 *
 * @param string $query
 * @return string
 */
private function prepare_query($query)
{
	// Список таблиц для выборки
	foreach($this->from as $k=>$from)
		$query .= ($k?", ":"").$from;
	
	$query .= " ";
		
	// Джойны. Как ON используется строка или mr_sql_where
	foreach($this->joins as $join)
		$query .= $join[0]." JOIN ".$join[1]." ON ".($join[2] instanceof mr_sql_where ? $join[2]->prepare() : $join[2]);
		
	// переданные параметры WHERE
	if(count($this->where))
		$query .= "WHERE ".parent::prepare();
		
	// сортировки
	if(count($this->order))
	{
		$query .= " ORDER BY";
		foreach($this->order as $i=>$o) $query .= ($i?",":"")." ".$o;
	}
	
	// Смещение и лимит, если есть
	if($this->limit)
		$query .= "LIMIT ".($this->offset?$this->offset.", ":"").$this->limit;
		
	// В конце всего запроса -- HAVING
	if($this->having)
		$query .= "HAVING ".($this->having instanceof mr_sql_where ? $this->having->prepare() : $this->having);

	return $query;
}

/**
 * Подготавливает параметр для вставки, каким бы он ни был
 *
 * @param mixed $param
 * @return string
 */
static public function param($param)
{
	if(is_null($param))
		return "NULL";
		
	if($param === '')
		return "''";
		
	if(is_array($param)) return "(".join(",", $param).")";
	if(is_numeric($param)) return $param;
	if(is_double($param)) return str_replace(",", ".", $param);
	if($param instanceof self)
		return "(".$param->prepare_select().")";
	if($param instanceof mr_sql_where)
		return "(".$param->prepare().")";
	return "'".mysql_escape_string($param)."'";
}
	
	}
?>