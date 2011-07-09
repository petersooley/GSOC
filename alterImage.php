<?php
session_start();

// Important locations
$baseURL = "/home1/petersoo/public_html/gsoc/";
$mapfileURL = $baseURL.$_SESSION['mapFile'];
$mapservURL = "http://www.petersoots.com/cgi-bin/mapserv";

$mapFactor = 1;


// Check for errors 
function getErrors($e_type, $e_message, $e_file, $e_line) {
	echo "<p>".$e_type." ".$e_message." ".$e_file." ".$e_line."</p>";
}
error_reporting(E_ALL);
set_error_handler("getErrors");

// Get the world file variables sent from javascript
extract($_POST); // $A, $B, $C, $D, $E, $F

// Build the world file that we created in JavaScript
$string = $A."\n".$D."\n".$B."\n".$E."\n".$C."\n".$F."\n";

$subPixelHeight = $_SESSION['subHeight'] > 180 ? 180 / ($_SESSION['subHeight'] * $mapFactor) : ($_SESSION['subHeight'] * $mapFactor) / 180;
$subPixelWidth = $_SESSION['subWidth'] > 360 ? 360 / ($_SESSION['subWidth'] * $mapFactor) : ($_SESSION['subWidth'] * $mapFactor)/ 360;
file_put_contents($_SESSION['subWorldFile'], $subPixelWidth."\n0\n0\n-".$subPixelHeight."\n-180\n90\n"); // this one is temporary, of course

$basePixelHeight = $_SESSION['baseHeight'] > 180 ? 180 / ($_SESSION['baseHeight'] * $mapFactor) : ($_SESSION['baseHeight'] * $mapFactor) / 180;
$basePixelWidth = $_SESSION['baseWidth'] > 360 ? 360 / ($_SESSION['baseWidth'] * $mapFactor) : ($_SESSION['baseWidth'] * $mapFactor)/ 360;
file_put_contents($_SESSION['baseWorldFile'], $basePixelWidth."\n0\n0\n-".$basePixelHeight."\n-180\n90\n");

$mapfile = sprintf("MAP\n");
$mapfile .= sprintf("    %-36s %s\n", "SHAPEPATH", "'.'");
$mapfile .= sprintf("    %-36s %s\n", "EXTENT", "-180 -90 180 90");
$mapfile .= sprintf("    %-36s %s\n", "IMAGETYPE", "PNG24");
$mapfile .= sprintf("    %-36s %s\n", "IMAGECOLOR", "255 255 255");
$mapfile .= sprintf("    %-36s %s\n", "SIZE", "".$_SESSION['baseWidth']." ".$_SESSION['baseHeight']);
$mapfile .= sprintf("\n");
$mapfile .= sprintf("    LAYER\n");
$mapfile .= sprintf("        %-32s %s\n", "NAME", "'base'");
$mapfile .= sprintf("        %-32s %s\n", "DATA", "'".$baseURL.$_SESSION['baseImage']."'");
$mapfile .= sprintf("        %-32s %s\n", "STATUS", "DEFAULT");
$mapfile .= sprintf("        %-32s %s\n", "TYPE", "RASTER");
$mapfile .= sprintf("    END\n");
$mapfile .= sprintf("\n");
$mapfile .= sprintf("    LAYER\n");
$mapfile .= sprintf("        %-32s %s\n", "NAME", "'sub'");
$mapfile .= sprintf("        %-32s %s\n", "DATA", "'".$baseURL.$_SESSION['subImage']."'");
$mapfile .= sprintf("        %-32s %s\n", "STATUS", "DEFAULT");
$mapfile .= sprintf("        %-32s %s\n", "TYPE", "RASTER");
$mapfile .= sprintf("    END\n");
$mapfile .= sprintf("END\n");

file_put_contents($_SESSION['mapFile'], $mapfile);

/*

// Warp the image
exec("/home1/petersoo/bin/gdalwarp -of GTiff sub.png alteredSub.gtiff 2>&1", $output, $return);
echo $return;
if($return != 0) 
	trigger_error("gdalwarp failed");

// Turn the image back into a png
exec("/home1/petersoo/bin/gdal_translate -of PNG alteredSub.gtiff alteredSub.png 2>&1", $output, $return);
echo $return;
if($return != 0) 
	trigger_error("gdal_translate failed");
*/

?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en"> 
<html> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"> 
	<title>Google Summer of Code Sandbox</title> 
	
	<!-- Sytlesheets -->
	<link rel="stylesheet" href="styles/style.css" type="text/css" /> 
   <link rel="stylesheet" href="styles/examples.css" type="text/css" /> 
	
	<!-- Libraries -->
	<script type="text/javascript" src="imports.js"></script> 
	<script src="OpenLayers/lib/OpenLayers.js"></script> 

	<!-- Our code -->
	<script src="myUtil.js"></script>
	<script type="text/javascript"> 
	
	 function init(){
            map = new OpenLayers.Map( 'map' );
            layer = new OpenLayers.Layer.MapServer( 
            		"Your base layer.", 
                  "<?php echo($mapservURL) ?>", 
                  {
                  	layers: 'base, sub',
                  	map: '<?php echo($mapfileURL) ?>',
                  	mode: 'map'
                  });
		 map.addLayer(layer);
		 map.addControl(new OpenLayers.Control.MousePosition());
		 map.zoomToMaxExtent();
	}
	</script>
</head>
<body onload="init();">
<h1>Google Summer of Code Sandbox</h1>
<a href="https://github.com/psoots/GSOC">Source Code on Github</a>
<div id="map"></div>


</body>