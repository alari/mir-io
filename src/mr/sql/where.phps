<?php class mr_sql_where {
	
 protected $where = array();
 
 const gt = ">";
 const lt = "<";
 const eq = "=";
 const like = " LIKE ";
 const in = " IN ";
 
 /**
  * Любая информация в строковом виде или как объект
  *
  * @param string $where
  * @param mixed $p1 заменители вопросиков
  * @return mr_sql_where
  */
public function where()
{
 	$args = func_get_args();
			
	$q = array_shift($args);
	
 	$this->where[] = count($args) ? mr_sql::queryWrapper($q, $args) : $q;
 	return $this;
}
 
 /**
  * Добавляет в where указанного типа сравнение
  *
  * @param string $column
  * @param mixed $compare
  * @param const $type
  * @return mr_sql_where
  */
 public function test($column, $compare, $type=self::eq)
 {
 	if(is_array($compare) && $type==self::eq) $type = self::in;
 	$this->where[] = array($type, $column, $compare);
 	return $this;
 }

/**
 * Дальнейшие поля группируются в скобки, предыдущая скобка закрыта
 *
 * @return mr_sql_where
 */
public function addOr($where=null)
{
	$this->where[] = array("OR");
	if($where) return $this->where($where);
	return $this;
}

/**
 * Добавляет exists
 *
 * @param mr_sql_query $q
 * @return mr_sql_where
 */
public function exists(mr_sql_query &$q)
{
	$this->where[] = array(" EXISTS ", $q);
	return $this;
}

/**
 * IN для query по ссылке
 *
 * @param mr_sql_query $q
 * @return mr_sql_where
 */
public function in(mr_sql_query &$q)
{
	$this->where[] = array(" IN ", $q);
	return $this;
}

/**
 * Преобразует объект в строку выборки
 *
 * @return string
 */
public function prepare()
{
	if(!is_array($this->where) || !count($this->where)) return "1=0";
	
	$was_or = false;
	
	$return = "";
	
	foreach($this->where as $k=>$v)
	{
		if(is_string($v))
		{
			$return .= ($return ? " AND ":"").$v;
			continue;
		}
		if($v instanceof self)
		{
			$return .= $v->prepare();
		}
		if($v[0] == "OR")
		{
			$return .= ") OR (";
			if(!$was_or)
			{
				$return = "(".$return;
				$was_or = true;
			}
			continue;
		}
		$return .= ($return?" AND ":"").$v[1].($v[0]).mr_sql_query::param($v[2]);
	}
	
	if($was_or) $return .= ")";
	return $return;
}
public function __toString()
{
	return $this->prepare();
}
	
	}
?>