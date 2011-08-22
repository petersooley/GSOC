<?php ///////////////////////////////////////////////////////////////
// At this point, the user has added all of his or her sub images to
// the base image. Now we generate a zipfile that contains everything
// the user needs to display all of the images together including the
// images that the user supplied (but renamed) and the world files, 
// mapfiles and the final html file. 



// Part 1. Generate data.html. This is the main file that will display the user's data.
$base = $_SESSION['baseImage'];

$url = 'http://www.petersoots.com/gsoc/dataFileTemplate.php';
$width = $base->width;
$height = $base->height;
$mapservUrl = $_POST['mapservUrl'];
$mapfileUrl = $_POST['serverDirectory']."/data/mapfile.map";
$layers = $_SESSION['layers'];
$fields = array(
            'width'=>urlencode($width),
            'height'=>urlencode($height),
            'mapservUrl'=>urlencode($mapservUrl),
            'mapfileUrl'=>urlencode($mapfileUrl),
            'layers'=>urlencode($layers)
        );

$fields_string = "";
//url-ify the data for the POST
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string,'&');

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_POST,count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

file_put_contents("data.html", $result);

// Part 2. Generate mapfile.map. This is just like we did before in displayResults.php, but 
// customized for the user's server.


// Part 3. Zip the files.

beginBody();
?>

<?php endBody(); ?>