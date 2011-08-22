<?php
function makeMapfile($baseURL){
	$base = $_SESSION['baseImage'];
	
	$extent = "0 -".$base->height." ".$base->width." 0";
	
	$layers = "base";
	
	$mapfile = sprintf("MAP\n");
	$mapfile .= sprintf("    %-36s %s\n", "SHAPEPATH", "'.'");
	$mapfile .= sprintf("    %-36s %s\n", "EXTENT", $extent);
	$mapfile .= sprintf("    %-36s %s\n", "IMAGETYPE", "PNG24");
	$mapfile .= sprintf("    %-36s %s\n", "IMAGECOLOR", "255 255 255");
	$mapfile .= sprintf("    %-36s %s\n", "SIZE", "".$base->width." ".$base->height);
	$mapfile .= sprintf("\n");
	$mapfile .= sprintf("    LAYER\n");
	$mapfile .= sprintf("        %-32s %s\n", "NAME", "'base'");
	$mapfile .= sprintf("        %-32s %s\n", "DATA", "'".$baseURL.$base->url."'");
	$mapfile .= sprintf("        %-32s %s\n", "STATUS", "DEFAULT");
	$mapfile .= sprintf("        %-32s %s\n", "TYPE", "RASTER");
	$mapfile .= sprintf("    END\n");
	foreach($_SESSION['subImages'] as $sub) {
		$mapfile .= sprintf("\n");
		$mapfile .= sprintf("    LAYER\n");
		$mapfile .= sprintf("        %-32s %s\n", "NAME", "'".$sub->layerName."'");
		$mapfile .= sprintf("        %-32s %s\n", "DATA", "'".$baseURL.$sub->url."'");
		$mapfile .= sprintf("        %-32s %s\n", "STATUS", "DEFAULT");
		$mapfile .= sprintf("        %-32s %s\n", "TYPE", "RASTER");
		$mapfile .= sprintf("    END\n");
		$layers .= ", ".$sub->layerName;
	}
	
	$_SESSION['layers'] = $layers; // needed later in done.php
	$mapfile .= sprintf("END\n");
	
	return $mapfile;
}

?>