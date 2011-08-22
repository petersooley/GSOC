<?php extract($_POST); ?>
<html>
<head>
	<script src="OpenLayers/lib/OpenLayers.js"></script>	
	
	<style type="text/css">
		.smallmap {
			width: 512px;
			height: 256px;
			border: 1px solid #ccc;
		}
	</style>

	<script type="text/javascript">
		function init() {
			
			var h = <?php echo $height; ?>;
			var w = <?php echo $width; ?>;
			
			var bnds = new OpenLayers.Bounds(0, -h, w, 0);
			var area = h * w;
			var res = Math.log(area) * Math.LOG10E; // calculates base 10 of area
		
			var options = { maxExtent : bnds, maxResolution: res};
			
			var map = new OpenLayers.Map( "results", options);
			var layer = new OpenLayers.Layer.MapServer( 
							"All together now!", 
							<?php echo '"'.$mapservUrl.'"'; ?>, 
							{
								map: <?php echo '"'.$mapfileUrl.'"'; ?>,
								layers: <?php echo '"'.$layers.'"'; ?>,
								mode: "map"
							}
						);
			 map.addLayer(layer);
			 map.addControl(new OpenLayers.Control.MousePosition());
			 map.addControl(new OpenLayers.Control.LayerSwitcher());
			 map.zoomToMaxExtent(); 
			
			
		}
	</script>
</head>
<body>

	<div id="map" class="smallmap"></div>

</body>