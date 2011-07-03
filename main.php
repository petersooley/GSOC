<?php 
session_start();

// Make sure we have our images
if($_FILES['baseImage']['error'] != UPLOAD_ERR_OK ||
	$_FILES['subImage']['error'] != UPLOAD_ERR_OK) {
	header("Location: http://www.petersoots.com/gsoc/index.php?error=true");
	exit;
}

// This is where we'll store our images temporarily
$dataDir = "data";
		
		
// Get the details about the base image. We'll use these details later in the javascript.
list($_SESSION['baseWidth'], $_SESSION['baseHeight'], $bType, $ignored) = getimagesize($_FILES['baseImage']['tmp_name']);
$baseFile = $dataDir."/base".time();
	
// Store the image url in a session variable and save the image to the data directory.
switch($bType) {
	case IMAGETYPE_GIF:
		$_SESSION['baseType'] = "GIF";
		$_SESSION['baseImage'] = $baseFile.".gif";
		$_SESSION['baseWorldFile'] = $baseFile.".gfw";
		imagegif(imagecreatefromgif($_FILES['baseImage']['tmp_name']), $_SESSION['baseImage']);
		break;
	case IMAGETYPE_JPEG:
		$_SESSION['baseType'] = "JPEG";
		$_SESSION['baseImage'] = $baseFile.".jpg";
		$_SESSION['baseWorldFile'] = $baseFile.".jgw";
		imagejpeg(imagecreatefromjpeg($_FILES['baseImage']['tmp_name']), $_SESSION['baseImage']);	
		break;
	case IMAGETYPE_PNG:
		$_SESSION['baseType'] = "PNG";
		$_SESSION['baseImage'] = $baseFile.".png";
		$_SESSION['baseWorldFile'] = $baseFile.".pgw";		
		imagepng(imagecreatefrompng($_FILES['baseImage']['tmp_name']), $_SESSION['baseImage']);	
		break;
}
		
// Get the details about the sub image. We'll use these details later in the javascript.
list($_SESSION['subWidth'], $_SESSION['subHeight'], $sType, $ignored) = getimagesize($_FILES['subImage']['tmp_name']);
$subFile = $dataDir."/sub".time();
		
// Store the image url in a session variable and save the image to the images directory.
switch($sType) {
	case IMAGETYPE_GIF:
		$_SESSION['subType'] = "GIF";
		$_SESSION['subImage'] = $subFile.".gif";
		$_SESSION['subWorldFile'] = $subFile.".gwf";
		imagegif(imagecreatefromgif($_FILES['subImage']['tmp_name']), $_SESSION['subImage']);
		break;
	case IMAGETYPE_JPEG:
		$_SESSION['subType'] = "JPEG";	
		$_SESSION['subImage'] = $subFile.".jpg";
		$_SESSION['subWorldFile'] = $subFile.".jgw";
		imagejpeg(imagecreatefromjpeg($_FILES['subImage']['tmp_name']), $_SESSION['subImage']);	
		break;
	case IMAGETYPE_PNG:
		$_SESSION['subType'] = "PNG";	
		$_SESSION['subImage'] = $subFile.".png";
		$_SESSION['subWorldFile'] = $subFile.".pgw";
		imagepng(imagecreatefrompng($_FILES['subImage']['tmp_name']), $_SESSION['subImage']);	
		break;
}

// While we're in the middle of making files, let's go ahead and make the MapFile too.
$_SESSION['mapFile'] = $dataDir."/mapfile.map";


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
	
	var global = {}; // Generic object for global variables.
	
	function WorldFile(A, D, B, E, C, F) {
		this.A = A;
		this.D = D;
		this.B = B;
		this.E = E;
		this.C = C;
		this.F = F;
		
	}
	WorldFile.prototype.display = function(div) {
		document.getElementById(div).innerHTML = 
			"<h3>World File (pixels)</h3>"+this.A+"<br>"+this.D+"<br>"+
				this.B+"<br>"+this.E+"<br>"+this.C+"<br>"+this.F;
	}
	WorldFile.prototype.toData = function() {
		return { "A": this.A, 
					"D": this.D, 
					"B": this.B, 
					"E": this.E, 
					"C": this.C, 
					"F": this.F };
	}

	function Point(x, y) {
		this.x = x;
		this.y = y;
	}
	Point.prototype.getXComponent = function (PointObj) {
		return PointObj.x - this.x;
	}
	Point.prototype.getYComponent = function (PointObj) {
		return this.y - PointObj.y; // negative if downwards 
	}
	Point.prototype.moveXDirection = function (distance) {
		this.x += distance;
		return this.x;
	}
	Point.prototype.moveYDirection = function (distance) {
		this.y += distance;
		return this.y;
	}
	Point.prototype.clone = function () {
		return new Point(this.x, this.y);
	}

	function Image(url, width, height) {
		this.url = url;
		this.width = width;
		this.height = height;
	}

	// returns a world file object.
	function writeWorldFile(subW, subH, subA, subB, baseW, baseH, baseA, baseB) {
		/*
		 * First we need to find the angles from the x axis starting at point A.
		 * To do this, we need the x and y components between the points and then we
		 * need to use a tangent function to determine the angle. The the ratio of x and y
		 * components compared to the sub image will allow us to determine lines A and E 
		 * of the world file and scale the image appropriately.
		 *
		 * The tangent function will return an angle between pi/2 and -pi/2, so if the
		 * x component is negative we need to be sure to flip the final image to reflect
		 * this. This is easily done by making the world file lines A or E negative or 
		 * positive as needed, but this happens automatically when we calculate the scales.
		 *
		 * Then, we just need to calculate the top left coordinates for lines C and F.
		 */
		 
		/*
		 * Get the components of the line from A to B for the sub and base images.
		 */
		var subXComponent = subA.getXComponent(subB);
		var subYComponent = subA.getYComponent(subB);
		var baseXComponent = baseA.getXComponent(baseB);
		var baseYComponent = baseA.getYComponent(baseB);
		
		/*
		 * We can determine how much we need to scale the sub image by looking at the 
		 * components between point A and B. These are the world file lines A and E.
		 * If these end up negative or positive, that's fine because that will flip the
		 * image appropriately.
		 */
		var xScale = baseXComponent / subXComponent;
		var yScale = baseYComponent / subYComponent;
		
		/*
		 * Find the angles for the AB line.
		 */
		var subAngle = Math.atan(subYComponent / subXComponent);
		var baseAngle = Math.atan(baseYComponent / baseXComponent);
		/*
		 * The angle of rotation is how much we need to rotate the sub image
		 * to fit on the base image. This will help us determine lines B and D
		 * of the world file. We can figure out the rotation by doing a simple
		 * subtraction because all the hard work of angle geometry is solved 
		 * when the xScale and yScale flip the image leaving us with just one
		 * quadrant to work in. However, we have to be careful that negative
		 * angles are subtracted correctly.
		 */
		var angleOfRotation;
		if((xScale < 0 || yScale < 0) && !(xScale < 0 && yScale < 0))
			angleOfRotation = baseAngle + subAngle; // one of the angles is negative
		else
			angleOfRotation = baseAngle - subAngle; // the angles are either both pos. or both neg.
		
		/*
		 * Calculate lines B and D using trig.
		 */
		ySkew = xScale * Math.tan(angleOfRotation);
		xSkew = yScale * Math.tan(angleOfRotation);
		
		/*
		 * Find the top left point of the sub image within the base image. To
		 * do this, find the distance from point A in the sub image to its 
		 * upper left corner but with the pixels scaled to the size of the
		 * base image's pixels. Keep in mind that the upper left corner could
		 * truly be any of the corners after the rotation, but we want the 
		 * nominal origin from which the affine translation can do it's work.
		 * We need the angle that the line from A to the origin makes with the
		 * x-axis. Then we need to add the rotation angle to that angle. This
		 * gives us a complete angle and a distance that we can apply to  
		 * point A in the base image to get the final coordinate of the sub 
		 * image's origin placed on the base image.
		 *
		 *
		 * NEEDS TESTING! MAY NOT BE WORKING PROPERLY!
		 */
		 var origin = new Point(0,0);
		 var x = origin.getXComponent(subA);
		 var y = origin.getYComponent(subA); // It's a negative component, but we'll square it.
		 
		 var distance = Math.sqrt( 
		 						Math.pow(x * Math.abs(xScale), 2) + 
		 						Math.pow(y * Math.abs(yScale), 2) ); // pythagorean 
		
		var angle = angleOfRotation + Math.tan(y/x);
		
		var xComponent = distance * Math.cos(angle);
		var yComponent = distance * Math.sin(angle);
			
		var temp = baseA.clone();
		var upperLeftX = temp.moveXDirection(xComponent);
		var upperLeftY = temp.moveYDirection(yComponent);

		return new WorldFile(xScale, ySkew, xSkew, yScale, upperLeftX, upperLeftY);
		
	}
	
	function post_to_url(url, params) {
   	var form = document.createElement('form');
   	form.action = url;
   	form.method = 'POST';

    	for (var i in params) {
        if (params.hasOwnProperty(i)) {
            var input = document.createElement('input');
            input.type = 'hidden';
         	input.name = i;
      		input.value = params[i];
         	form.appendChild(input);
      	}
   	}

   	form.submit();
	}

	function calculateWorldFile() {
		with(global) {
			if(subCPs.length > 1 && baseCPs.length > 1) {
				global.worldFile = writeWorldFile(2000, 1000, subCPs[0], subCPs[1], 2000, 1000, baseCPs[0], baseCPs[1]);
		 		post_to_url("alterImage.php", worldFile.toData());
			}
			else {
				document.getElementById("error").innerHTML = "<p>Oops! Not enough control points</p>";
			}
		}
	};

	function makeImageLayer(name, imageObject) {
		var w = imageObject.width;
		var h = imageObject.height;
		// The default projection is EPSG WGS 84, so the bounds are -180, -90, 180, 90. 
		var bounds = new OpenLayers.Bounds(-180, -90, 180, 90);
		var size = new OpenLayers.Size(w, h);
		var opts = {
			numZoomLevels	: 10, // arbitrary
			maxResolution	: 1 // one pixel per one map unit
		};
		return new OpenLayers.Layer.Image(name, imageObject.url, bounds, size, opts);
	}
	
	function makeMapFromImage(div, name, imageObject) {
		var map = new OpenLayers.Map(div);
		map.addLayer( makeImageLayer(name, imageObject) );
		map.zoomToMaxExtent();
		return map;
	}
	function addControlPointsLayer(mapObject, layerName) {
		var vl = new OpenLayers.Layer.Vector(layerName);
		mapObject.addLayer(vl);
		
		var count = (function() { 
			var i = 0; 
			return function() { 
				return ++i; 
			};
		})();
		
		// Increment the label each time a point is added
		vl.preFeatureInsert = function(feature) { 
			var string = "";
			feature.attributes = {
				label	: count()
			};
		}
		
		
		// Add styles to the vector layer (to display the label)
		// These settings are all default (but if you change one setting, they
		// all go away, so you have to repeat them like I am doing here.)
		var vs = new OpenLayers.Style({
			'label'			: '${label}', // <--This is the whole point of the Style object
			'fillColor'		: '#ee9900',
			'fillOpacity'	: .6,
			'strokeWidth'	: 2,
			'strokeColor'	: '#ee9900',
			'strokeOpacity': .7, 
			'pointRadius'	: 6,
			'labelXOffset'	: -10,
			'labelYOffset'	: -10
		});
		var vsm = new OpenLayers.StyleMap({
			'default'		: vs
		});
		
		vl.styleMap = vsm;
		
		// Add the DrawPoints Control
		var control = new OpenLayers.Control.DrawFeature(vl, OpenLayers.Handler.Point);
		mapObject.addControl(control);
		control.activate();

		
		return vl;
	}
	
	function collectPoints(vectorLayer, pointArray, outputDiv, imageObj) {
		// Register for featureadded events
		vectorLayer.events.register('featureadded', this, function(event) { 
			
			// Convert the projected points into pixels
			// 	- set the origin from the center to the top left
			var x = event.feature.geometry.x + 180; 
			var y = 90 - event.feature.geometry.y;
			//		- find the dimensions of the map unit
			var mapUnitWidth = imageObj.width / 360;
			var mapUnitHeight = imageObj.height / 180;
			// 	- determine the actual pixel distance
			x *= mapUnitWidth;
			y *= mapUnitHeight;
			
			// add point to the array
			var p = new Point(x, y);
			pointArray[pointArray.length] = p;
			
			// Temporarily display the points
			var string = "";
			for(var i = 0; i < pointArray.length; ++i) {
				string = string + "<p>Point #"+(i+1)+"	x: "+pointArray[i].x+"	y: "+pointArray[i].y +"</p>";
			}
			document.getElementById(outputDiv).innerHTML = string; 
		});
	}
	
	
	
	function init() {
	
		var baseImage = new Image(
			"<?php echo($_SESSION['baseImage']); ?>",
			<?php echo($_SESSION['baseWidth']); ?>,
			<?php echo($_SESSION['baseHeight']); ?>
		);
		var subImage = new Image(
			"<?php echo($_SESSION['subImage']); ?>",
			<?php echo($_SESSION['subWidth']); ?>,
			<?php echo($_SESSION['subHeight']); ?>
		);
		
		
		var subLayer = makeImageLayer("sub image", subImage);
		var baseLayer = makeImageLayer("base image", baseImage);
		
		var base = new OpenLayers.Map("base");
		base.addLayer(baseLayer);
		base.zoomToMaxExtent();
		
		var sub = new OpenLayers.Map("sub");
		sub.addLayer(subLayer);
		sub.zoomToMaxExtent();
		

		/*
		 * Add vector layers for making control points.
		 */
		var baseCPLayer = addControlPointsLayer(base, "base points layer");
		var subCPLayer = addControlPointsLayer(sub, "sub points layer");	
		
		global.baseCPs = new Array();
		global.subCPs = new Array();
		collectPoints(baseCPLayer, global.baseCPs, "basePoints", baseImage);
		collectPoints(subCPLayer, global.subCPs, "subPoints", subImage);
		
		var wf = new WorldFile(1, 0, 0, -.5, 49, 39);
		
		/*
		 * Eventually, we'd like to add a new layer of the adjusted sub
		 * image to the map. We're not that far yet. But it may look 
		 * something like this.
		 */
		 /*
		myUtil.POST("alterImage.php", wf.toData(), function(response){
			document.getElementById("text").innerHTML = response; // for testing
			
			// now that we've altered the sub image...
			var sub2Image = new Image(
				"alteredSub.png",
				2000,
				1000
			);		
			// make the altered image into a layer
			var sub2Layer = makeImageLayer("sub altered", sub2Image);
			sub2Layer.setIsBaseLayer(false);
			
			// add the altered image to the base image
			base.addLayer(sub2Layer);

			base.addControl(new OpenLayers.Control.LayerSwitcher());
		
		} ); */
	 
	}
	
 	</script> 
</head> 
<body onload="init()"> 
<h1>Google Summer of Code Sandbox</h1>
<a href="https://github.com/psoots/GSOC">Source Code on Github</a>
<div id="text"></div>
<div id="base" class="smallmap"></div>
<div id="sub" class="smallmap"></div>
<button type="button" onclick="calculateWorldFile();">Calculate World File</a></button>
<p>Currently the world file is calculated with just the first two control points</p>
<div id="error"></div>
<h3>Base Image Control Points</h3>
<div id="basePoints"></div>
<br>
<h3>Sub Image Control Points</h3>
<div id="subPoints"></div>
</body>
</head>