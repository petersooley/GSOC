<?php
// We display the base image (with any previously altered sub images)
// and we display the new sub image to be altered. We use OpenLayers 
// to give the user an interface to add control points to the base
// and sub images. Then we use the control points to calculate a 
// world file. 

getOpenLayersHeader();

// 1. Make map from Base Image
// 2. Add MapServer layers to Base image
// 3. Make map from latest sub image
// 4. Add control points to both maps
?>
	<script type="text/javascript">
	
	// This is where it all begins from body onload.
	function init() {
		
		var baseImage = new Image( 
			<?php 
				$base = $_SESSION["baseImage"];
				echo "\"$base->url\", $base->width, $base->height";
			?>
		);
		var subImage = new Image(
			<?php
				$sub = $_SESSION['subImages'][$_SESSION['subCount'] - 1];
				echo "\"$sub->url\", $sub->width, $sub->height";
			?>
		);
		
		
		var subLayer = makeImageLayer("sub image", subImage);
		var baseLayer = makeImageLayer("base image", baseImage);
		
		var base = new OpenLayers.Map("base");
		base.addLayer(baseLayer);
		base.zoomToMaxExtent();
		base.addControl(new OpenLayers.Control.MousePosition());
		base.addControl(new OpenLayers.Control.LayerSwitcher());
		
		// TODO:
		// add MapServer layers to base map here
		
		var sub = new OpenLayers.Map("sub");
		sub.addLayer(subLayer);
		sub.zoomToMaxExtent();
		sub.addControl(new OpenLayers.Control.MousePosition());
		sub.addControl(new OpenLayers.Control.LayerSwitcher());
		

		// Add vector layers for making control points.
		var baseCPLayer = addControlPointsLayer(base, "base points layer");
		var subCPLayer = addControlPointsLayer(sub, "sub points layer");	
		
		var baseCPs = new Array();
		var subCPs = new Array();
		collectPoints(baseCPLayer, baseCPs, "basePoints", baseImage);
		collectPoints(subCPLayer, subCPs, "subPoints", subImage);
		
		document.getElementById('genButton').addEventListener("click", function(e) {
			if(baseCPs.length < 2 || subCPs.length < 2) 
				document.getElementById('errors').innerHTML = "You need at least two control points.";
		
			// Loop through ALL of the points and calculate the average world file
			var len = subCPs.length < baseCPs.length ? subCPs.length : baseCPs.length;
			
			// Create the first world file:
			var wf = writeWorldFile(subImage, subCPs[0], subCPs[1], baseImage, baseCPs[0], baseCPs[1]);

			// Loop through each permutation of Points
			for(var j = 0; j < len; ++j) { 
				for(var k = 0; k < len; ++k) {
					if((j == 0 && k == 1) || j >= k )
						continue;
					wf.average(writeWorldFile(subImage, subCPs[j], subCPs[k], baseImage, baseCPs[j], baseCPs[k]));
				}
			}
			var params = wf.toData();
			params.state = "gotWorldFile";
		
			myUtil.postToUrl("<?php echo $_SERVER['PHP_SELF']; ?>", params);
			
		});
		
	}
	</script>

<?php beginBody(); ?>

	<div id="errors"></div>
	<div id="maps">
		<div id="base" class="smallmap"></div>
		<div id="sub" class="smallmap"></div>
	</div>
	<div id="sidepanel">
		<p>Once you've made at least two control points per image, click generate.</p>
		<div id="genButton"><button type="button">Generate</a></button></div>
		<div id="error"></div>
		<h2>Base Image Control Points</h2>
		<div id="basePoints" class="points"></div>
		<br>
		<h2>Sub Image Control Points</h2>
		<div id="subPoints" class="points"></div>
	</div>
	<div class="clearboth"></div>
	<div id="log"></div>
	<div id="worldfile"></div>
	<div id="text"></div>

<?php endBody(); ?>
