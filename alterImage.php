<?php


// Check for errors 
function getErrors($e_type, $e_message, $e_file, $e_line) {
	echo "<p>".$e_type." ".$e_message." ".$e_file." ".$e_line."</p>";
}
error_reporting(E_ALL);
set_error_handler("getErrors");

// Get the world file variables sent from javascript
extract($_POST); // $A, $B, $C, $D, $E, $F

// Build the world file
$string = $A."\n".$B."\n".$C."\n".$D."\n".$E."\n".$F."\n";

// Write the world file
file_put_contents( "sub.pgw", $string);

// remove the old outputs
exec("rm alteredSub*", $output, $return);
echo $return;
// Warp the image
exec("/home1/petersoo/bin/gdalwarp -of GTiff sub.png alteredSub.gtiff 2>&1", $output, $return);
echo $return;
if($return != 0) 
	trigger_error("gdalwarp failed");

// Turn the image back into a png
exec("/home1/petersoo/bin/gdal_translate -of PNG alteredSub.gtiff alteredSub.png 2>&1", $output, $return);
echo $return;
if($return != 0) 
	trigger_error("gdal_translate failed");

?>