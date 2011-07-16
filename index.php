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
 * This is the entry to our application. We just need to get some images from
 * the user and pass it on to main.php.
 * 
 */

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