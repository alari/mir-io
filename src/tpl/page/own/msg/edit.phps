<?php class tpl_page_own_msg_edit extends tpl_page_own_msg_inc implements i_tpl_page_rightcol {
		
public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
 	$this->layout = "rightcol";
 	
 	if(!ws_self::ok())
 		throw new ErrorPageException("Требуется авторизация", 403);
 	
 	ob_start();
 	
 	// Новое сообщение
 	if($params[1] == "new")
 	{
	 	$this->title = "Новое личное сообщение";
	 	
	 	echo "<h1>Новое сообщение</h1>";
	 	
	 	$this->msg_form(@$params["to"]);
	 	
	// Ответ на сообщение
 	} elseif($params[1] == "reply") {
 		
 		$msg = ws_user_msg_item::factory((int)$params["to"]);
 		if($msg->owner()->id() != ws_self::id())
 			throw new ErrorPageException("Сообщение не найдено", 404);
 			
 		$this->title = "Ответ на личное сообщение";
 		
 		echo "<h1>Ответ на личное сообщение</h1>";
 		
 		$this->msg_form( $msg->target()->login, parent::reply_text( mr_text_trans::node2text($msg->content) ), parent::reply_title($msg->title) );
 		
 	}
 	
 	$this->content = ob_get_clean();
 	
 	$this->css[] = "own/msgs.css";
 }
 
 protected function msg_form($to="", $msg_content="", $title="")
 {
 	
 	$msg_title = $title ? htmlspecialchars($title) : "";
 	$msg_to = htmlspecialchars($to);
 	
?>

<form method="post" action="/x/own-msgs/send" enctype="multipart/form-data" accept-charset="utf-8" onsubmit="javascript:$('msg_submit').disabled='yes'">

<table id="msgs-edit">
 <colgroup>
  <col align="right" width="40%"/>
  <col/>
 </colgroup>

 <tr>
  <th><label for="msg_to">Логин адресата:</label>&nbsp;&nbsp;&nbsp;</th>
  <td><input type="text" name="to" maxlength="127" size="40" value="<?=$msg_to?>" id="msg_to"/></td>
 </tr>

 <tr>
  <th><label for="msg_title">Тема сообщения:</label>&nbsp;&nbsp;&nbsp;</th>
  <td><input type="text" name="title" maxlength="127" size="40" value="<?=$msg_title?>" id="msg_title"/></td>
 </tr>

 <tr>
  <td colspan="2" align="center" id="msg_textarea">
   <textarea name="msg" cols="65" rows="25" id="attach"><?=$msg_content?></textarea>
   <script type="text/javascript">text_markup('attach', 1, 0);</script>
  </td>
 </tr>
 
 <tr>
  <th colspan="2" align="center"><input type="submit" id="msg_submit" value="Отправить сообщение"/></th>
 </tr>
 
</table>

</form>

<?
 	
 }
 
 static public function msg_reply($msg)
 {
 
 }
	}
?>