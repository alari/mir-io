<?

 $cities = ws_geo_city::several("latitude!=0 OR longitude!=0");

 echo "<?xml version='1.0' encoding='utf-8'?>";
?>
<markers>
 <?foreach($cities as $c){
 	$ico = "wc";
 	if($c->u_active) $ico = "rc";
 	?>
  <marker lat="<?=$c->latitude?>" lon="<?=$c->longitude?>" ico="<?=$ico?>" html="<?=htmlspecialchars($c->link())?>"/>
 <?}?>
</markers>