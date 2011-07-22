<?php
/*
 * Copyright � 2011 by Peter Soots
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *
 *
 *
 * main.php
 *
 * Now that we have the images from the user, we need the user to define control
 * points to georeference the images together. Then we'll create a world file and
 * use alterImage.php via AJAX to change the image and we'll the new image to the
 * user.
 * 
 */


session_start();

// Make sure we have our images
if($_FILES['baseImage']['error'] != UPLOAD_ERR_OK ||
	$_FILES['subImage']['error'] != UPLOAD_ERR_OK) {
	header("Location: http://www.petersoots.com/gsoc/index.php?error=true");
	exit;
}

// This is where we'll store our images temporarily
$dataDir = "data/";
		
		
// Get the details about the base image. We'll use these details later in the javascript.
list($bWidth, $bHeight, $bType, $ignored) = getimagesize($_FILES['baseImage']['tmp_name']);
$baseFile = $dataDir."base";
	
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
		$image = imagecreatefrompng($_FILES['baseImage']['tmp_name']);
		imagepng($image, $_SESSION['baseImage']);	
		imagedestroy($image);
		break;
}

// Save data in session
$_SESSION['baseHeight'] = $bHeight;
$_SESSION['baseWidth'] = $bWidth;		
		
// Get the details about the sub image. We'll use these details later in the javascript.
list($sWidth, $sHeight, $sType, $ignored) = getimagesize($_FILES['subImage']['tmp_name']);
$subFile = $dataDir."sub";
		
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
		$image = imagecreatefrompng($_FILES['subImage']['tmp_name']);
		imagepng($image, $_SESSION['subImage']);	
		imagedestroy($image);
		break;
}

// Save data in Session
$_SESSION['subHeight'] = $sHeight;
$_SESSION['subWidth'] = $sWidth;

// While we're in the middle of making files, let's go ahead and make the MapFile too.
$_SESSION['mapFile'] = $dataDir."mapfile.map";


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

	// Calculates a world file and returns a WorldFile object. 
	// The sub image is the one being altered to match the unaltered
	// base image. The calculation requires height and width of both 
	// images and two Point objects on each image: subA will be 
	// referenced to baseA and similarly, subB to baseB. These points
	// are in reference to a top-left origin where x and y are both 
	// positive.
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
	
	
	

	// This function is called when the user click's the 'generate' button.
	// It calculates the world file and sends it via AJAX to alterImage.php 
	// (which already has the images in session variables). 
	function generate() {
		if(global.subCPs.length > 1 && global.baseCPs.length > 1) {
			global.worldFile = writeWorldFile(
														<?php echo $_SESSION['subWidth']; ?>,
														<?php echo $_SESSION['subHeight']; ?>, 
														global.subCPs[0], 
														global.subCPs[1], 
														<?php echo $_SESSION['baseWidth']; ?>,
														<?php echo $_SESSION['baseHeight']; ?>,
														global.baseCPs[0], 
														global.baseCPs[1]);
				
			
			// Let's not change anything while we're testing
			// global.worldFile = new WorldFile(1, 0, 0, -1, 0, 0);
	
			myUtil.POST("alterImage.php", global.worldFile.toData(), function(response) { showNewMap(response); });
		}
		else {
			document.getElementById("error").innerHTML = "<p>Oops! Not enough control points</p>";
		}
	}
	
	// This function handles the AJAX response that we get from alterImage.php.
	// Basically, we are given the MapServer info needed to create an 
	// OpenLayers.Layer.MapServer layer. 
	function showNewMap(response) {
		var data = JSON.parse(response);
		
		document.getElementById("maps").innerHTML = "";
		document.getElementById("maps").className = "smallmap";
		if(data.error != undefined) {
			document.getElementById("text").innerHTML = "error: "+ data.error;
			return;
		} 
		
		document.getElementById("text").innerHTML = "<pre>"+data.mapfile+"</pre>";
		
		// find bounds and resolution like we did before in makeImageLayer();
		var w = data.width;
		var h = data.height;
		var bnds = new OpenLayers.Bounds(0, -h, w, 0);
		//var sz = new OpenLayers.Size(w, h);
		var area = h * w;
		var res = 1;//Math.log(area) * Math.LOG10E; // calculates base 10 of area
	
		var options = { maxExtent : bnds, maxResolution: res};
		
		var map = new OpenLayers.Map( "maps", options);
   	var layer = new OpenLayers.Layer.MapServer( 
            		"All together now!", 
                 	data.mapservURL, 
                  data.mapserverParams
               );
		 map.addLayer(layer);
		 map.addControl(new OpenLayers.Control.MousePosition());
		 map.zoomToMaxExtent(); 
	}

	// Turns an imageObject into a layer (used by makeMapFromImage)
	function makeImageLayer(name, imageObject) {
		var w = imageObject.width;
		var h = imageObject.height;
		
		// In Openlayers coordinates, the origin is in the middle of the
		// image. So, we just have to plan our boundaries accordingly, by
		// dividing our heights and widths by 2 to find the centers.	
		var bounds = new OpenLayers.Bounds(-(w / 2), -(h / 2), (w / 2), (h / 2));
		var size = new OpenLayers.Size(w, h);
		
		
		// We need to determine the max resolution, because small images won't be 
		// zoomed in enough and large images will be zoomed in too far. This 
		// calculation was just a guess, but it does the job perfectly.
		var area = h * w;
		var res = Math.log(area) * Math.LOG10E; // calculates base 10 of area
		
		var opts = {
			numZoomLevels	: 10, // arbitrary
			maxResolution	: res   // one pixel per one map unit
		};
		return new OpenLayers.Layer.Image(name, imageObject.url, bounds, size, opts);
	}
	
	// Turns an imageObject into a map
	function makeMapFromImage(div, name, imageObject) {
		var map = new OpenLayers.Map(div);
		map.addLayer( makeImageLayer(name, imageObject) );
		map.zoomToMaxExtent();
		return map;
	}
	
	// Adds a vector layer to mapObject as an interface that allows
	// the user to add control points to the image. This is where
	// we style the control points.
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
	
	// Capture user's clicks to the vectorLayer and save them in the pointArray
	function collectPoints(vectorLayer, pointArray, outputDiv, imageObj) {
		// Register for featureadded events
		vectorLayer.events.register('featureadded', this, function(event) { 
			
			// Change the points so the origin is at the top left rather than at the center
			var x = event.feature.geometry.x + (imageObj.width / 2); 
			var y = (imageObj.height / 2) - event.feature.geometry.y;
			
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
	
	
	// This where it all begins from body onload.
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
		base.addControl(new OpenLayers.Control.MousePosition());
		
		var sub = new OpenLayers.Map("sub");
		sub.addLayer(subLayer);
		sub.zoomToMaxExtent();
		sub.addControl(new OpenLayers.Control.MousePosition());
		

		/*
		 * Add vector layers for making control points.
		 */
		var baseCPLayer = addControlPointsLayer(base, "base points layer");
		var subCPLayer = addControlPointsLayer(sub, "sub points layer");	
		
		global.baseCPs = new Array();
		global.subCPs = new Array();
		collectPoints(baseCPLayer, global.baseCPs, "basePoints", baseImage);
		collectPoints(subCPLayer, global.subCPs, "subPoints", subImage);
	}
	
 	</script> 
 	
 	<style type="text/css">
 		#sidepanel {
 			float: left;
 			margin-left: 10px;
 			width: 460px;
 		}
 		#originals {
 			float: left;
 		}
 		#genButton {
			width: 460px;
			text-align: center;
 		}
 		.points {
 			height: 170px;
 			overflow: auto;
 		}
 	</style>
</head> 
<body onload="init()"> 
	<h1>Google Summer of Code Sandbox</h1>
	<a href="https://github.com/psoots/GSOC">Source Code on Github</a>
	<div id="maps">
		<div id="originals">
			<div id="base" class="smallmap"></div>
			<div id="sub" class="smallmap"></div>
		</div>
		<div id="sidepanel">
			<p>Once you've made at least two control points per image, click Generate!</p>
			<div id="genButton"><button type="button" onclick="generate();">Generate</a></button></div>
			<div id="error"></div>
			<h2>Base Image Control Points</h2>
			<div id="basePoints" class="points"></div>
			<br>
			<h2>Sub Image Control Points</h2>
			<div id="subPoints" class="points"></div>
		</div>
	</div> 
	<div id="text"></div>
</body>
</head>