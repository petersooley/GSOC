<?php
// This is where we create a mapfile and then use OpenLayers to bring
// the sub images together with the base image. 

include('makeMapfile.php');

// Get the world file variables sent from javascript
extract($_POST); // $A, $B, $C, $D, $E, $F

// Build the world file that we created in JavaScript
$worldFile = $A."\n".$D."\n".$B."\n".$E."\n".$C."\n".$F."\n";

// A no-change worldfile, for the base image
$worldFileNormal = "1\n0\n0\n-1\n0\n0\n";

// Save the current sub image 
$subCurrent = $_SESSION['subImages'][$_SESSION['subCount'] - 1];
$subCurrent->worldFile = $worldFile;
file_put_contents($subCurrent->worldFileUrl, $subCurrent->worldFile); 

$base = $_SESSION['baseImage'];
$base->worldFile = $worldFileNormal;
file_put_contents($base->worldFileUrl, $base->worldFile);  // We write the same world file over and over, but oh well.


$baseURL = "/home1/petersoo/public_html/gsoc/";
$mapfileURL = $baseURL.$_SESSION['mapfile'];
$mapservURL = "/cgi-bin/mapserv";

$mapfile = makeMapfile($baseURL);

file_put_contents($_SESSION['mapfile'], $mapfile);

getOpenLayersHeader();
?>

<script type="text/javascript">
	function init() {
		document.getElementById('addMore').addEventListener("click", function (e) {
			myUtil.postToUrl(<?php echo "'".$_SERVER['PHP_SELF']."'"; ?>, { state: "getSub" });
		});
		
		document.getElementById('finished').addEventListener("click", function (e) {
			myUtil.postToUrl(<?php echo "'".$_SERVER['PHP_SELF']."'"; ?>, { state: "getUserData" });
		});
		
		var h = <?php echo $base->height; ?>;
		var w = <?php echo $base->width; ?>;
		
		var bnds = new OpenLayers.Bounds(0, -h, w, 0);
		var area = h * w;
		var res = Math.log(area) * Math.LOG10E; // calculates base 10 of area
	
		var options = { maxExtent : bnds, maxResolution: res};
		
		var map = new OpenLayers.Map( "results", options);
   	var layer = new OpenLayers.Layer.MapServer( 
            		"All together now!", 
                 	<?php echo '"'.$_SESSION['mapservUrl'].'"'; ?>, 
                  {
                  	map: <?php echo '"'.$baseURL.$_SESSION['mapfile'].'"'; ?>,
                  	layers: <?php echo '"'.$_SESSION['layers'].'"'; ?>,
                  	mode: "map"
                  }
               );
		 map.addLayer(layer);
		 map.addControl(new OpenLayers.Control.MousePosition());
		 map.addControl(new OpenLayers.Control.LayerSwitcher());
		 map.zoomToMaxExtent(); 
		
		
	}
</script>

<?php beginBody(); ?>

<div id="results" class="smallmap"></div>
<div id="buttons">
	<button id="addMore" type="button">Add more sub images</button>
	<button id="finished" type="button">Finished</button>
</div>

<?php endBody(); ?>