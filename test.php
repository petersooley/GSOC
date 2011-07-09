<?php

function pre($string) {
	echo "<pre>".$string."</pre>";
}

// $ret = list($bWidth, $bHeight, $bType, $ignored) = getimagesize($_FILES['baseImage']['tmp_name']);

echo $_FILES['baseImage']['tmp_name'];
$image = imagecreatefrompng($_FILES['baseImage']['tmp_name']);
pre(print_r($image));
$image_url = "data/savedImage".time().".png";
$ret = imagepng($image, $image_url);
pre(print_r($ret));


?>

<html>
<head>
	<title>Example 1: Displaying a map</title>
</head>
<body>
	<img src="<?php echo $image_url; ?>" />
</body>
</html>