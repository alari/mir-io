<?php class tpl_page_mir_donate extends tpl_page {
		
 public function __construct($filename="", $params="")
 {
 	parent::__construct($filename, $params);
 	
	ob_start();
	
?>

<h1>Поддержать проект</h1>
<h2></h2>

<p class="pr">Вы можете перевести произвольную сумму любым из предложенных способов.</p>
<p class="pr">PayPal: оплата из любой точки мира, с помощью дебетных и кредитных карт.</p>
<center><?=$this->paypal();?></center>

<?

	$this->content = ob_get_clean();
 	
	$this->title = "Поддержать проект";
	
 }
 
 protected function paypal()
 {
?>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>

<?
 }
 
}?>