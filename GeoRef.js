(function() {

	window.GeoRef = {
		 
	};

	GeoRef.Point =	function (x, y, imageObject) {
		var plotUnitWidth = imageObject.width / 360;
		var maxUnitY = (180 * imageObject.height) / imageObject.width;
		var plotUnitHeight = imageObject.height / (2 * maxUnitY);
		var yPosition = ((y - maxUnitY) * -1);
		var xPosition = x + 180;
		this.x = xPosition * plotUnitWidth;
		this.y = yPosition * plotUnitHeight;
		this.image = imageObject;
	}

	
	GeoRef.Image = function (url, width, height) {
		this.url = url;
		this.width = width;
		this.height = height;
	}	
	
	
})();