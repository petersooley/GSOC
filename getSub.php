<?php
// We get a sub image from the user. We re-enter this state whenever a 
// user wants to add more sub images on top of the base image. 

beginBody();
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
endBody(); 
?>