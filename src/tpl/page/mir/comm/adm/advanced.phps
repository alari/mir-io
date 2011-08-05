<?php class tpl_page_mir_comm_adm_advanced extends tpl_page_mir_comm_adm_inc implements i_tpl_page_rightcol, i_locale  {
		
	static protected $locale = array(), $lang = "";
	
	static public function locale($data, $lang)
	{
		self::$locale = $data;
		self::$lang = $lang;
	}
		
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	 	 	
 	if( !ws_self::is_allowed("comm_control_advanced", $this->comm->id()) )
 		throw new ErrorPageException("У Вас нет прав на расширенный контроль этого сообщества", 403);
 		
 	ob_start();
 	
 	$this->title = $this->comm->title." - Административный контроль";
 	
 	$sphere = explode(",", $this->comm->org_sphere);
 	$direct = explode(",", $this->comm->org_direct);
 	
 ?>
 <h1>Административный контроль</h1>
 <h2>Сообщество <?=$this->comm?></h2>
 	<br />
 	
 <form method="post" action="/x/comm-adm/advanced" id="adm-form">
 
 <center><table cellpadding="0" cellspacing="5">
 <colgroup>
 	<col width="33%" align="center" valign="middle"/>
 	<col width="33%" align="center" valign="middle"/>
 	<col width="33%" align="center" valign="middle"/>
 </colgroup>
 <tr>
 	<td>Сфера деятельности:</td>
 	<td>Направление деятельности:</td>
 	<td><label for="adv_limit">Рекламных сообщений</label>:</td>
 </tr>
 <tr>
 	<td>
 <select name="sphere[]" multiple="yes" size="<?=count(ws_comm::$org_spheres)?>">
   <?foreach(ws_comm::$org_spheres as $k=>$v){?>
    <option value="<?=$k?>"<?=(in_array($k, $sphere)?' selected="yes"':"")?>><?=$v?></option>
   <?}?>
  </select>
 	</td>
 	
 	<td>
 	<select name="direct[]" multiple="yes" size="<?=count(ws_comm::$org_directs)?>">
   <?foreach(ws_comm::$org_directs as $k=>$v){?>
    <option value="<?=$k?>"<?=(in_array($k, $direct)?' selected="yes"':"")?>><?=$v?></option>
   <?}?>
  </select>
 	</td>
 	
 	<td>
 	<input type="text" size="3" name="adv_limit" id="adv_limit" value="<?=$this->comm->adv_limit?>"/>
 	</td>
 
 </tr>
 
 <tr>
 	<td colspan="3" align="center">
 	<input type="button" value="Сохранить изменения" onclick="$(this).disabled='yes';mr_Ajax_Form($('adm-form'), {update:$('adm-result')})"/>
  	<input type="hidden" name="id" value="<?=$this->comm->id()?>"/>
  	<div id="adm-result"></div>
 	</td>
 </tr>
 
 </table></center>
  
  
 </form>
 	
 <?
 		
 	$this->content = ob_get_clean();
 }
	}
?>