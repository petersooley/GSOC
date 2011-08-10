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
<!-- This is the html that is displayed every time we load this page. -->
<!doctype html public "-//w3c//dtd html 4.0 transitional//en"> 
<html> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"> 
	<title>Georeferencing Images</title> 
	
<?php if(isset($_SESSION['needOpenLayers'])) { ?>
			<script type="text/javascript" src="imports.js"></script> 
			<script src="OpenLayers/lib/OpenLayers.js"></script>	
			
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
<?php } ?>

</head>
<body>

<h1>Georeferencing Images</h1>

<?php


// This is our state machine that takes the user through the application.
// The states are changed as the user clicks on forms (or buttons that
// eventually send forms via JavaScript) which contain hidden input fields
// that request the next state. 
if(!isset($_POST['state']))
	goto getBase;
switch($_POST['state']) {
	case 'gotBase':		goto saveBase;
	case 'getSub':			goto getSub;
	case 'gotSub':			goto saveSub;
	case 'gotWorldFile':	goto alterImage;
	case 'done':			goto done;
}


// This is a struct not a class. It just makes passing data WAY easier.
class Image {
	public $height;
	public $width;
	public $url;
	public $worldFile;
}
?>



<?php ///////////////////////////////////////////////////////////////
getBase: 
// This is the beginning state where we get the base image
// from the user. This state only happens once. 

// Iniatilize some variables since this is the very beginning of the session.

// Variables for keeping track of possibly many different sub images.
$_SESSION['subImages'] = array();
$_SESSION['subCount'] = 0;
$_SESSION['baseImage'] = new Image();

// This is the location of our temporary folder to store images and files.
$_SESSION['dataDir'] = "data/";	
$_SESSION['mapFile'] = $_SESSION['dataDir']."mapfile.map";

?>

<p>
The purpose of this application is to spatially relate images together
in a way that can be displayed easily on the web. Originially, this 
project will be used to display thin-section images collected with a 
microscope "on top of" higher resolution images obtained by 
electromicroscopy, but this application is general enough to be used
for other purposes too. The final method for displaying all of the images
together uses a zoomable, map-like interface that gives the viewer 1) 
a way to spatially understand how the lower resolution images relate
to the higher resolution images and 2) easy access to viewing the higher
resolution images within the proper spatial context.
</p>
</p>
The technologies used are:
	<ul>
		<li>PHP,</li>
		<li>JavaScript,</li>
		<li>MapServer, and</li>
		<li>OpenLayers.</li>
	</ul>
</p>
<p>
To get started, you need a base image (typically of the highest resolution).
This will not be <i>changed</i>. That is, all other images will be scaled and
translated onto this one image. Later, you will add more (lower resolution) 
images that you will carefully place onto this one base image.
</p>
<form action="index.php" method="post" enctype="multipart/form-data">
	<label for="baseImage">Base Image (not changed)</label>
	<input type="file" name="baseImage" id="baseImage"/>
	<input type="hidden" name="state" id="state" value="gotBase" />
	<input type="submit" value="submit" />
</form>
<?php goto endPage; ?>

<?php ///////////////////////////////////////////////////////////////
saveBase: 
// Save the base image to disk so that we can use it throughout the 
// application. This state only happens once. 

// Get the details about the base image. We'll use these details later in the javascript.
list($w, $h, $type, $ignored) = getimagesize($_FILES['baseImage']['tmp_name']);
$baseFile = $_SESSION['dataDir']."base";
$base = new Image();
$base->height = $h;
$base->width = $w;
	
// Store the image url in a session variable and save the image to the data directory.
switch($type) {
	case IMAGETYPE_GIF:
		$base->url = $baseFile.".gif";
		$base->worldfile = $baseFile.".gfw";
		imagegif(imagecreatefromgif($_FILES['baseImage']['tmp_name']), $base->url);
		break;
	case IMAGETYPE_JPEG:
		$base->url = $baseFile.".jpg";
		$base->worldfile = $baseFile.".jgw";
		imagejpeg(imagecreatefromjpeg($_FILES['baseImage']['tmp_name']), $base->url);	
		break;
	case IMAGETYPE_PNG:
		$base->url = $baseFile.".png";
		$base->worldfile = $baseFile.".pgw";		
		imagepng(imagecreatefrompng($_FILES['baseImage']['tmp_name']), $base->url);
		break;
}

$_SESSION['baseImage'] = $base;


/////////////////////////////////////////////////////////////////////
getSub: 
// We get a sub image from the user. We re-enter this state whenever a 
// user wants to add more sub images on top of the base image. 

?>

<p>
Select an image to place onto the base image.
</p>
<form action="index.php" method="post" enctype="multipart/form-data">
	<label for="subImage">Sub image</label>
	<input type="file" name="subImage" id="subImage"/>
	<input type="hidden" name="state" id="state" value="gotSub" />
	<input type="submit" value="submit" />
</form>
<?php 
// The next reload will eventually use the OpenLayers JavaScript library. So
// we need to make sure it gets loaded.
$_SESSION['needOpenLayers'] = true;
goto endPage; 
?>

<?php ///////////////////////////////////////////////////////////////
saveSub:
// Save the sub image that the user just uploaded. Also, we maintain
// the session variables that keep track of how many sub images the
// user has uploaded.


// Get the details about the sub image. 
list($w, $h, $type, $ignored) = getimagesize($_FILES['subImage']['tmp_name']);
$_SESSION['subName'] = "sub";
$subFile = $_SESSION['dataDir'].$_SESSION['subName'].$_SESSION['subCount'];
$sub = new Image();
$sub->height = $h;
$sub->width = $w;

// Store the image url in a session variable and save the image to the images directory.
switch($type) {
	case IMAGETYPE_GIF:
		$sub->url = $subFile.".gif";
		$sub->worldfile = $subFile.".gwf";
		imagegif(imagecreatefromgif($_FILES['subImage']['tmp_name']), $sub->url);
		break;
	case IMAGETYPE_JPEG:
		$sub->url = $subFile.".jpg";
		$sub->worldfile = $subFile.".jgw";
		imagejpeg(imagecreatefromjpeg($_FILES['subImage']['tmp_name']), $sub->url);
		break;
	case IMAGETYPE_PNG:
		$sub->url = $subFile.".png";
		$sub->worldfile = $subFile.".pgw";
		imagepng(imagecreatefrompng($_FILES['subImage']['tmp_name']), $sub->url);	
}

// Save data in Session
$_SESSION['subCount'] = $_SESSION['subCount'] + 1;
array_push($_SESSION['subImages'], $sub);





/////////////////////////////////////////////////////////////////////
// collectPoints: 
// We display the base image (with any previously altered sub images)
// and we display the new sub image to be altered. We use OpenLayers 
// to give the user an interface to add control points to the base
// and sub images. Then we use the control points to calculate a 
// world file. 



?>


<?php goto endPage; ?>







<?php ///////////////////////////////////////////////////////////////
alterImage: 
// This is where we create a mapfile and then use OpenLayers to bring
// the sub images together with the base image. ?>



<?php goto endPage; ?>






<?php ///////////////////////////////////////////////////////////////
done: 
// At this point, the user has added all of his or her sub images to
// the base image. Now we generate a zipfile that contains everything
// the user needs to display all of the images together including the
// images that the user supplied (but renamed) and the world files, 
// mapfiles and the final html file. 



?>


<?php goto endPage; ?>
















<?php ///////////////////////////////////////////////////////////////
endPage: 
?>
<a href="https://github.com/psoots/GSOC">Source Code on Github</a>
</body>
</html>