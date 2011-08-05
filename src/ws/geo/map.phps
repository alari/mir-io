<?php class ws_geo_map implements i_initiate {
	
 static private $keys = array();
	
 protected $canvas;
 protected $lat;
 protected $lon;
 protected $zoom = 2;
 protected $markersfile;
 protected $fnc;
 
 const ZOOM_COUNTRY = 1;
 const ZOOM_REGION = 2;
 const ZOOM_SUBREGION = 3;
 const ZOOM_TOWN = 4;
	
 static public function init($keys)
 {
 	self::$keys = $keys;
 }
 
 public function __construct($canvasElementId)
 {
 	$this->canvas = $canvasElementId;
 }
 
 public function setLatLon($lat, $lon)
 {
 	$this->lat = $lat;
 	$this->lon = $lon;
 	return $this;
 }
 
 public function setCity(ws_geo_city $city)
 {
 	$this->lat = $city->latitude;
 	$this->lon = $city->longitude;
 	return $this;
 }
 
 public function setZoom($zoom)
 {
 	$this->zoom = $zoom;
 	return $this;
 }
 
 public function setFunctionName($fnc)
 {
 	$this->fnc = $fnc;
 	return $this;
 }
 
 public function setMarkersXml($filename)
 {
 	$this->markersfile = $filename;
 	return $this;
 }
 
 public function __toString()
 {
 	ob_start();
 	
 	$map = "m_".str_replace("-", "_", $this->canvas);
?>

<script type="text/javascript">

<?if($this->fnc){?>function <?=$this->fnc?>(){<?}?>

 	var <?=$map?> = new GMap2(document.getElementById("<?=$this->canvas?>"));
 	<?=$map?>.setCenter(new GLatLng(<?=$this->lat?>, <?=$this->lon?>), <?=$this->zoom?>);
 	
 	
 <?=$map?>.addControl(new GSmallMapControl());
 <?=$map?>.addControl(new GMapTypeControl());
 	
 	
 	<?if($this->markersfile){?>
 	
 	var baseIcon = new GIcon();
    baseIcon.iconSize=new GSize(32,32);
    baseIcon.shadowSize=new GSize(56,32);
    baseIcon.iconAnchor=new GPoint(16,32);
    baseIcon.infoWindowAnchor=new GPoint(16,0);
      
   var icons = {};    
   icons["rs"] = new GIcon(baseIcon, "http://maps.google.com/mapfiles/kml/pal4/icon48.png", null, "http://maps.google.com/mapfiles/kml/pal4/icon48s.png");
   icons["ws"] = new GIcon(baseIcon, "http://maps.google.com/mapfiles/kml/pal4/icon56.png", null, "http://maps.google.com/mapfiles/kml/pal4/icon56s.png");
   icons["rc"] = new GIcon(baseIcon, "http://maps.google.com/mapfiles/kml/pal4/icon49.png", null, "http://maps.google.com/mapfiles/kml/pal4/icon49s.png");
   icons["wc"] = new GIcon(baseIcon, "http://maps.google.com/mapfiles/kml/pal4/icon57.png", null, "http://maps.google.com/mapfiles/kml/pal4/icon57s.png");
 	
 	function createGMarker(point,ico,html) {
        var marker = new GMarker(point,icons[ico]);
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
        });
        return marker;
      }
 	
 GDownloadUrl("<?=$this->markersfile?>", function(data, responseCode) {
  var xml = GXml.parse(data);
  var markers = xml.documentElement.getElementsByTagName("marker");
  for (var i = 0; i < markers.length; i++) {
  	
    var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
                            parseFloat(markers[i].getAttribute("lon")));
    
    <?=$map?>.addOverlay(createGMarker(point, markers[i].getAttribute("ico"), markers[i].getAttribute("html")));
    
  }
});	
 	<?}?>
 	
 	
<?if($this->fnc){?>}<?}?>

</script>

<?
 	
 	return ob_get_clean();
 }
 
 static public function cityUpdateLatLon(ws_geo_city $city)
 {
 	$q = urlencode($city->name.", ".$city->country()->name);
	$r = file_get_contents("http://maps.google.com/maps/geo?q=$q&output=csv&key=".self::$keys[mr::site()]."&gl=".$city->country);
	if(!$r) return false;
	
	list($code, $acc, $lat, $lon) = @explode(",", $r, 4);
	if($code != 200) return false;
	mr_sql::qw(
		"UPDATE ".ws_geo_city::sqlTable."
		SET latitude=?, longitude=?
		WHERE id=?",
		$lat, $lon, $city->id()
	);
	return mr_sql::affected_rows();
 }
 
 static public function jsInject()
 {
 	return '<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.self::$keys[mr::site()].(mr::lang()?"&amp;hl=".mr::lang():"").'"></script>';
 }
	
}