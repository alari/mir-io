<?php abstract class tpl_page_mir_comm_inc extends tpl_page implements i_tpl_page_comm {
	 
	/* @var $_comm ws_comm */
	protected $_comm=false;
	
/**
 * interface fnc
 *
 * @return ws_comm
 */
 final public function p_comm()
 {
 	return $this->comm;
 }
 
 public function __get($name)
 {
 	if($name == "comm") {
 		if($this->_comm === false)
 			$this->_comm = ws_comm::byName( mr::subdir() );
 		if(!$this->_comm || !$this->_comm->id())
 			throw new ErrorPageException("Сообщество не найдено", 404);
 		return $this->_comm;
 	}
 	return null;
 }
 
 public function setComm(ws_comm $comm)
 {
 	if(!mr::subdir()) $this->_comm = $comm;
 }
	
	}
?>