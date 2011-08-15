<?php 
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
		$base->worldFileUrl = $baseFile.".gfw";
		imagegif(imagecreatefromgif($_FILES['baseImage']['tmp_name']), $base->url);
		break;
	case IMAGETYPE_JPEG:
		$base->url = $baseFile.".jpg";
		$base->worldFileUrl = $baseFile.".jgw";
		imagejpeg(imagecreatefromjpeg($_FILES['baseImage']['tmp_name']), $base->url);	
		break;
	case IMAGETYPE_PNG:
		$base->url = $baseFile.".png";
		$base->worldFileUrl = $baseFile.".pgw";		
		imagepng(imagecreatefrompng($_FILES['baseImage']['tmp_name']), $base->url);
		break;
}

$_SESSION['baseImage'] = $base;
?>