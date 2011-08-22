<?php 
// At this point, the user has added all of his or her sub images to
// the base image. Now let's get the data's server data so that we
// can generate customized files for displaying the data on the user's 
// own server.
?>
<script type="text/javascript">
	function checkData() {
		var x=document.forms["myForm"]["mapservUrl"].value;
		var y=document.forms["myForm"]["serverDirectory"].value;
		if (x==null || x=="") {
  			alert("Please enter a location for your mapserv binary.");
  			return false;
  		}
  		else if (y==null || y=="") {
  			alert("Please enter a server location for your website.");
  			return false;
  		}
	}
</script>

<?php beginBody(); ?>
	<p>To generate the files you need to display your data on your own website, we need
	some data about your server. If you don't know this stuff right now, please enter something anyway
	and you'll just have to change it later (in the data.html file).</p>
	<form action="index.php" method="post" name="myForm" onsubmit="return checkData()">
		
		<div><label for="mapservUrl">Absolute location of your mapserv binary (i.e. /home/bob/public_html/cgi-bin/mapserv).</label></div>
		<div><input type="text" name="mapservUrl" id="mapservUrl" size="100"/></div>
		<div><label for="serverDirectory">Absolute location of your website (i.e. /home/bob/public_html/mywebsite). NOTE: No slash at the end.</label></div>
		<div><input type="text" name="serverDirectory" id="serverDirectory" size="100"/></div>
		<input type="hidden" name="state" id="state" value="done" />
		<div><input type="submit" value="submit" /></div>
	</form>
<?php endBody(); ?>