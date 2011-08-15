<?php
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
		$sub->worldfileUrl = $subFile.".gwf";
		imagegif(imagecreatefromgif($_FILES['subImage']['tmp_name']), $sub->url);
		break;
	case IMAGETYPE_JPEG:
		$sub->url = $subFile.".jpg";
		$sub->worldfileUrl = $subFile.".jgw";
		imagejpeg(imagecreatefromjpeg($_FILES['subImage']['tmp_name']), $sub->url);
		break;
	case IMAGETYPE_PNG:
		$sub->url = $subFile.".png";
		$sub->worldfileUrl = $subFile.".pgw";
		imagepng(imagecreatefrompng($_FILES['subImage']['tmp_name']), $sub->url);	
}

// Save data in Session
$_SESSION['subCount'] = $_SESSION['subCount'] + 1;
array_push($_SESSION['subImages'], $sub);
?>