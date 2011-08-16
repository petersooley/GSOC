<?php
// This is the beginning state where we get the base image
// from the user. This state only happens once. 

// Iniatilize some variables since this is the very beginning of the session.

// Variables for keeping track of possibly many different sub images.
$_SESSION['subImages'] = array();
$_SESSION['subCount'] = 0;
$_SESSION['baseImage'] = new Image();

// This is the location of our temporary folder to store images and files.
$_SESSION['dataDir'] = "data/";	
$_SESSION['mapfile'] = $_SESSION['dataDir']."mapfile.map";
$_SESSION['mapservUrl'] = "../cgi-bin/mapserv";

beginBody();
?>

<p>
The purpose of this application is to spatially relate images together
in a way that can be displayed easily on the web. </p>
<p>Originially, this project will be used to display thin-section images 
collected with a microscope "on top of" higher resolution images obtained by 
electromicroscopy, but this application is general enough to be used
for other purposes too. The final method for displaying all of the images
together uses a zoomable, map-like interface that gives the viewer 
<ol><li>a way to spatially understand how the lower resolution images relate
to the higher resolution images and</li><li>easy access to viewing the higher
resolution images within the proper spatial context.</li></ol>
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
To get started, you need a base image (typically of the lowest resolution).
This will not be <i>changed</i>. That is, all other images will be scaled and
translated onto this one image. Later, you will add as many (higher resolution) 
images that you want and carefully place them onto this one base image. In the
end we will produce all the files you need to display the final composite.
</p>
<form action="index.php" method="post" enctype="multipart/form-data">
	<label for="baseImage">Base Image (not changed)</label>
	<input type="file" name="baseImage" id="baseImage"/>
	<input type="hidden" name="state" id="state" value="gotBase" />
	<input type="submit" value="submit" />
</form>
<?php endBody(); ?>