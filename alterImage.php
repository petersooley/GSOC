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
 * alterImage.php
 *
 * This file simply takes a world file from JavaScript, makes alterations to 
 * the images and produces a MapServer environment that JavaScript will later
 * need to create an OpenLayers.Layer.MapServer object.
 * 
 */
 
session_start();

// Important locations
$baseURL = "/home1/petersoo/public_html/gsoc/";
$mapfileURL = $baseURL.$_SESSION['mapFile'];
$mapservURL = "http://www.petersoots.com/cgi-bin/mapserv";

$mapFactor = 1;


// Check for errors 
function getErrors($e_type, $e_message, $e_file, $e_line) {
	$error = array("error" => "<p>".$e_type." ".$e_message." ".$e_file." ".$e_line."</p>");
	echo json_encode($error);
	
}
error_reporting(E_ALL);
set_error_handler("getErrors");

// Get the world file variables sent from javascript
extract($_POST); // $A, $B, $C, $D, $E, $F

// Build the world file that we created in JavaScript
$string = $A."\n".$D."\n".$B."\n".$E."\n".$C."\n".$F."\n";

// For testing....
$rotation_only = "1\n".$D."\n".$B."\n-1\n0\n0\n";
$scaling_only = $A."\n0\n0\n".$E."\n0\n0\n";
$translation_only = "1\n0\n0\n-1\n".$C."\n".$F."\n";
$scale_trans_only = $A."\n0\n0\n".$E."\n".$C."\n".$F."\n";

// A no-change worldfile, for the base image
$worldFile = "1\n0\n0\n-1\n0\n0\n";

//file_put_contents($_SESSION['subWorldFile'], $string); // original
file_put_contents($_SESSION['subWorldFile'], $scale_trans_only);   // testing only, removed eventually

// the user may one day want to associate a world file with the base image (for now, it's just a default one)
file_put_contents($_SESSION['baseWorldFile'], $worldFile); 

$extent = "0 -".$_SESSION['baseHeight']." ".$_SESSION['baseWidth']." 0";

$mapfile = sprintf("MAP\n");
$mapfile .= sprintf("    %-36s %s\n", "SHAPEPATH", "'.'");
$mapfile .= sprintf("    %-36s %s\n", "EXTENT", $extent);
$mapfile .= sprintf("    %-36s %s\n", "IMAGETYPE", "PNG24");
$mapfile .= sprintf("    %-36s %s\n", "IMAGECOLOR", "255 255 255");
$mapfile .= sprintf("    %-36s %s\n", "SIZE", "".$_SESSION['baseWidth']." ".$_SESSION['baseHeight']);
$mapfile .= sprintf("\n");
$mapfile .= sprintf("    LAYER\n");
$mapfile .= sprintf("        %-32s %s\n", "NAME", "'base'");
$mapfile .= sprintf("        %-32s %s\n", "DATA", "'".$baseURL.$_SESSION['baseImage']."'");
$mapfile .= sprintf("        %-32s %s\n", "STATUS", "DEFAULT");
$mapfile .= sprintf("        %-32s %s\n", "TYPE", "RASTER");
$mapfile .= sprintf("    END\n");
$mapfile .= sprintf("\n");
$mapfile .= sprintf("    LAYER\n");
$mapfile .= sprintf("        %-32s %s\n", "NAME", "'sub'");
$mapfile .= sprintf("        %-32s %s\n", "DATA", "'".$baseURL.$_SESSION['subImage']."'");
$mapfile .= sprintf("        %-32s %s\n", "STATUS", "DEFAULT");
$mapfile .= sprintf("        %-32s %s\n", "TYPE", "RASTER");
$mapfile .= sprintf("    END\n");
$mapfile .= sprintf("END\n");

file_put_contents($_SESSION['mapFile'], $mapfile);

$json = array( 'mapfile' => $mapfile, 
					'mapservURL' => $mapservURL, 
					'width' => $_SESSION['baseWidth'],
					'height' => $_SESSION['baseHeight'],
					'mapserverParams' => array('map' => $mapfileURL,  // it's best to do any real manipulation on the javascript side
														'layers' => 'base, sub',
														'mode' => 'map')); 

/*

// We need to warp the image using gdalwarp because MapServer unfortunately doesn't
// properly support full rotations in world files.

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
*/

echo json_encode($json);


?>
