<?php
/*
 * Copyright © 2011 by Peter Soots
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
 * index.php
 *
 * This is our entire application. Details to come...
 * 
 */
 
session_start(); 

ini_set('display_errors',1); 
error_reporting(E_ALL);
?>
<!-- This is the beginning html that is displayed every time we load this page. -->
<!doctype html public "-//w3c//dtd html 4.0 transitional//en"> 
<html> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"> 
	<title>Georeferencing Images</title> 

<?php

// This is a struct not a class. It just makes passing data WAY easier.
class Image {
	public $height;
	public $width;
	public $url;
	public $worldFileUrl;
	public $worldFile;
	public $layerName;
}

// Functions for HTML

function beginBody() { 
?>
	<script type="text/javascript">
		if(typeof init != "undefined") 
			window.onload = init;
	</script>
	</head>
	<body>
	<h1><a href="http://www.petersoots.com/gsoc/index.php">Georeferencing Images</a></h1>
<?php
}
function endBody() {
?>
	<a href="https://github.com/psoots/GSOC">Source Code on Github</a>
	</body>
	</html>
<?php 
	exit(0); 
}
function getOpenLayersHeader() {?>
	<script type="text/javascript" src="imports.js"></script> 
	<script src="OpenLayers/lib/OpenLayers.js"></script>	
	<script src="geoFuncs.js"></script>
	<script src="myUtil.js"></script>
	
	<style type="text/css">
		.smallmap {
			width: 512px;
			height: 256px;
			border: 1px solid #ccc;
		}
		#sidepanel {
			float: left;
			margin-left: 10px;
			width: 460px;
		}
		#maps {
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
		.clearboth {
			clear: both;
		}
		#errors {
			color: red;
		}
	</style>
<?php
}


// This is our state machine that takes the user through the application.
// The states are changed as the user clicks on forms (or buttons that
// eventually send forms via JavaScript) which contain hidden input fields
// that request the next state. 

// #1 Start here, please submit a base image.
if(!isset($_POST['state'])) 
	include 'getBase.php'; 
	
switch($_POST['state']) {
	// #2 Thank you for submitting a base image. We're going to save it.
	case 'gotBase':		
		include 'saveBase.php';
		
	// #3 Please submit a sub image.
	case 'getSub':	
		include 'getSub.php';
	
	// #4 Thank you for submitting a sub image. We're going to save it.
	// Please add control points to the images. 
	case 'gotSub':			
		include 'saveSub.php';
		include 'collectPoints.php';
	
	// #5 Nice control points! We'll create the MapServer data and show 
	// you the results.
	case 'gotWorldFile':	
		include 'displayResults.php';
		
	// Do you want to add another sub image? Go to #3. 
	
	// #6 No more sub images? Ok, grab the user's server data so that we
	// can generate the customized files.
	case 'getUserData':
		include 'getUserServerData.php';
		
	// #7 Now that we have the user's server data we can generate and present 
	// a zip file of files needed to display the user's data
	case 'done':			
		include 'done.php';
}
?>
