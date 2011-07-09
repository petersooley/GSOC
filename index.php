<?php session_start(); ?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en"> 
<html> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"> 
	<title>Google Summer of Code Sandbox</title> 
	<!-- Sytlesheets -->
	<link rel="stylesheet" href="styles/style.css" type="text/css" /> 
   <link rel="stylesheet" href="styles/examples.css" type="text/css" /> 
</head>
<body>

<h1>Google Summer of Code Sandbox</h1>
<a href="https://github.com/psoots/GSOC">Source Code on Github</a>
<form action="main.php" method="post" enctype="multipart/form-data">
<label for="inputBaseImage">Base Image</label>
<input type="file" name="baseImage" id="inputBaseImage"/>
<label for="inputSubImage">Sub Image</label>
<input type="file" name="subImage" id="inputSubImage"/>
<input type="submit" value="submit" />
</form>
<div id="error">
<?php
if(isset($_GET['error']))
	echo("There was an error uploading your files. Try again.");
?>

</div>

</body>