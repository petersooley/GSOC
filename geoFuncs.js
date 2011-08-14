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
 * geoFuncs.js
 *
 * These are useful functions for working with OpenLayers to create an application
 * for georeferencing.
 *
 */


// A world file is
// A: x scale
// E: y scale (negative is the normative value)
// C: Upperleft X coordinate
// F: Upperleft Y coordinate
// D & B: rotation...these are complicated, look at wikipedia
function WorldFile(A, D, B, E, C, F) {
	this.A = A;
	this.D = D;
	this.B = B;
	this.E = E;
	this.C = C;
	this.F = F;
	
}
WorldFile.prototype.display = function(div) {
	document.getElementById(div).innerHTML = 
		"<h3>World File (pixels)</h3>"+this.A+"<br>"+this.D+"<br>"+
			this.B+"<br>"+this.E+"<br>"+this.C+"<br>"+this.F;
}
WorldFile.prototype.toData = function() {
	return { "A": this.A, 
				"D": this.D, 
				"B": this.B, 
				"E": this.E, 
				"C": this.C, 
				"F": this.F };
}

function Point(x, y) {
	this.x = x;
	this.y = y;
}
Point.prototype.getXComponent = function (PointObj) {
	return PointObj.x - this.x;
}
Point.prototype.getYComponent = function (PointObj) {
	return this.y - PointObj.y; // negative if downwards 
}
Point.prototype.moveXDirection = function (distance) {
	this.x += distance;
	return this.x;
}
Point.prototype.moveYDirection = function (distance) {
	this.y += distance;
	return this.y;
}
Point.prototype.clone = function () {
	return new Point(this.x, this.y);
}
Point.prototype.toString = function() {
	return this.x+", "+this.y;
}

function Image(url, width, height) {
	this.url = url;
	this.width = width;
	this.height = height;
}

// This is a temporary debugging function.
function l(msg) { 
	document.getElementById('log').innerHTML += "<p>"+msg+"</p>";
}

// Calculates a world file and returns a WorldFile object. 
// The sub image is the one being altered to match the unaltered
// base image. The calculation requires height and width of both 
// images and two Point objects on each image: subA will be 
// referenced to baseA and similarly, subB to baseB. These points
// are in reference to a top-left origin where x and y are both 
// positive.
function writeWorldFile(subImage, subA, subB, baseImage, baseA, baseB) {
	var subW = subImage.width;
	var subH = subImage.height;
	var baseW = baseImage.width;
	var baseH = baseImage.height;
	//l("subW: "+subW); 
	//l("subH: "+subH);
	//l("subA: "+subA);
	//l("subB: "+subB);
	//l("baseW: "+baseW);
	//l("baseH: "+baseH);
	//l("baseA: "+baseA);
	//l("baseB: "+baseB);
	 
	// Scaling
	///////////////////////////////////////////////////////////////////////////////
	// To scale the image we just need to calculate the X and Y components between
	// point A and point B in both images. To figure out how much to scale the sub
	// image we just figure out the right ratio between the base image's components
	// and the sub image's components.
	var subXComponent = subA.getXComponent(subB);
	var subYComponent = subA.getYComponent(subB);
	var baseXComponent = baseA.getXComponent(baseB);
	var baseYComponent = baseA.getYComponent(baseB);
	//l("subXComponent: "+subXComponent);
	//l("subYComponent: "+subYComponent);
	//l("baseXComponent: "+baseXComponent);
	//l("baseYComponent: "+baseYComponent);
	
	// Calculate the ratio. NOTE: Our math uses the Cartesian plane, but internet
	// images don't, so the yScale is opposite from what you'd think it should be.
	var xScale = baseXComponent / subXComponent;
	var yScale = baseYComponent / subYComponent * -1;
	//l("xScale: "+xScale);
	//l("yScale: "+yScale);
	
	
	// Translation 
	////////////////////////////////////////////////////////////////////////////////////
	// Get the X and Y of SubA. Once we place the origin of the sub image onto BaseA
	// we'll need to "move back" so that SubA is on BaseA. But now that we're working in 
	// the base image's space, we need to use its scaling.
	
	var origin = new Point(0,0);
	//l("x: "+origin.getXComponent(subA));
	//l("y: "+origin.getYComponent(subA));
	var moveX = origin.getXComponent(subA) * xScale;
	var moveY = origin.getYComponent(subA) * yScale; 
	// No matter what, we need to move up and to the left. 
	if(moveX < 0) moveX *= -1;
	if(moveY > 0) moveY *= -1;
	//l("moveX: "+moveX);
	//l("moveY: "+moveY);
	 
	// If we place the sub image so that it's origin is on BaseA, we then need to counter
	// that movement by moving SubA back to its origin. So, get the X and Y of BaseA and
	// subtract the X and Y of SubA (in the base image's scaling).
	var upperleftX = origin.getXComponent(baseA) - moveX;
	var upperleftY = origin.getYComponent(baseA) - moveY;
	//l("upperleftX: "+upperleftX);
	//l("upperleftY: "+upperleftY);
	
	/* Skipping rotation for now
	
	// Rotation
	////////////////////////////////////////////////////////////////////////// 
	// Find the angles that the AB line makes with the x-axis. By using tangent
	// we'll only get values from pi/2 to -pi/2. This is exactly what we want.
	// We don't need to carry on the calculation to find out the real angle,
	// because the image will be flipped in the transformation according to 
	// the positive or negative sign of the xScale and yScale values that we
	// already calculated. It makes the math really simple.
	var subAngle = Math.atan(subYComponent / subXComponent);
	var baseAngle = Math.atan(baseYComponent / baseXComponent);
	l("subAngle (degrees): "+(subAngle * (180 / Math.PI)));
	l("baseAngle (degrees): "+(baseAngle*(180 / Math.PI)));
	
	
	// The angle of rotation is how much we need to rotate the sub image
	// to fit on the base image. This will help us determine lines B and D
	// of the world file. We can figure out the rotation by doing a simple
	// subtraction because all the hard work of angle geometry is solved 
	// when the xScale and yScale flip the image leaving us with just one
	// quadrant to work in. However, we have to be careful that negative
	// angles are subtracted correctly.
	
	var angleOfRotation;
	if((xScale < 0 || yScale < 0) && !(xScale < 0 && yScale < 0))
		angleOfRotation = baseAngle + subAngle; // one of the angles is negative
	else
		angleOfRotation = baseAngle - subAngle; // the angles are either both pos. or both neg.
	l("angleOfRotation (degrees): "+(angleOfRotation * (180 / Math.PI)));
	
	
	// Now that we're doing rotation, we need to rethink the xScale and yScale 
	// variables, lines A and E. So far, these variables have simply represented
	// the pixel width and height.
	var pixelWidth = xScale;
	var pixelHeight = yScale;
	l("pixelWidth: "+pixelWidth);
	l("pixelHeight: "+pixelHeight);
	
	// But with rotation, pixel width and height aren't needed in the world file. 
	// Instead lines A and E represent the x and y components of the rotated pixel, 
	// respectively.
	xScale = pixelHeight * Math.cos(angleOfRotation);
	yScale = pixelWidth * Math.cos(angleOfRotation);
	l("xScale: "+xScale);
	l("yScale: "+yScale);
	
	// Lastly, the xSkew and ySkew variables in the world file, lines B and D, 
	// are the other components of the rotated pixel. ySkew is the y component
	// of the pixelWidth and the xSkew is the x component of the pixelHeight.
	var ySkew = pixelWidth * Math.sin(angleOfRotation);
	var xSkew = pixelHeight * Math.sin(angleOfRotation);
	l("xSkew: "+xSkew);
	l("ySkew: "+ySkew);
	
	*/
	var xSkew = 0;
	var ySkew = 0;

	return new WorldFile(xScale, ySkew, xSkew, yScale, upperleftX, upperleftY);
	
}

// Turns an imageObject into a layer (used by makeMapFromImage)
function makeImageLayer(name, imageObject) {
	var w = imageObject.width;
	var h = imageObject.height;
	
	// In Openlayers coordinates, the origin is in the middle of the
	// image. So, we just have to plan our boundaries accordingly, by
	// dividing our heights and widths by 2 to find the centers.	
	var bounds = new OpenLayers.Bounds(-(w / 2), -(h / 2), (w / 2), (h / 2));
	var size = new OpenLayers.Size(w, h);
	
	
	// We need to determine the max resolution, because small images won't be 
	// zoomed in enough and large images will be zoomed in too far. This 
	// calculation was just a guess, but it does the job perfectly.
	var area = h * w;
	var res = Math.log(area) * Math.LOG10E; // calculates base 10 of area
	
	var opts = {
		numZoomLevels	: 10, // arbitrary
		maxResolution	: res   // one pixel per one map unit
	};
	return new OpenLayers.Layer.Image(name, imageObject.url, bounds, size, opts);
}

// Turns an imageObject into a map
function makeMapFromImage(div, name, imageObject) {
	var map = new OpenLayers.Map(div);
	map.addLayer( makeImageLayer(name, imageObject) );
	map.zoomToMaxExtent();
	return map;
}

// Adds a vector layer to mapObject as an interface that allows
// the user to add control points to the image. This is where
// we style the control points.
function addControlPointsLayer(mapObject, layerName) {
	var vl = new OpenLayers.Layer.Vector(layerName);
	mapObject.addLayer(vl);
	
	var count = (function() { 
		var i = 0; 
		return function() { 
			return ++i; 
		};
	})();
	
	// Increment the label each time a point is added
	vl.preFeatureInsert = function(feature) { 
		var string = "";
		feature.attributes = {
			label	: count()
		};
	}
	
	
	// Add styles to the vector layer (to display the label)
	// These settings are all default (but if you change one setting, they
	// all go away, so you have to repeat them like I am doing here.)
	var vs = new OpenLayers.Style({
		'label'			: '${label}', // <--This is the whole point of the Style object
		'fillColor'		: '#ee9900',
		'fillOpacity'	: .6,
		'strokeWidth'	: 2,
		'strokeColor'	: '#ee9900',
		'strokeOpacity': .7, 
		'pointRadius'	: 6,
		'labelXOffset'	: -10,
		'labelYOffset'	: -10
	});
	var vsm = new OpenLayers.StyleMap({
		'default'		: vs
	});
	
	vl.styleMap = vsm;
	
	// Add the DrawPoints Control
	var control = new OpenLayers.Control.DrawFeature(vl, OpenLayers.Handler.Point);
	mapObject.addControl(control);
	control.activate();

	
	return vl;
}

// Capture user's clicks to the vectorLayer and save them in the pointArray
function collectPoints(vectorLayer, pointArray, outputDiv, imageObj) {
	// Register for featureadded events
	vectorLayer.events.register('featureadded', this, function(event) { 
		
		// Change the points so the origin is at the top left rather than at the center
		var x = event.feature.geometry.x + (imageObj.width / 2); 
		var y = (imageObj.height / 2) - event.feature.geometry.y;
		
		// add point to the array
		var p = new Point(x, y);
		pointArray[pointArray.length] = p;
		
		// Temporarily display the points
		var string = "";
		for(var i = 0; i < pointArray.length; ++i) {
			string = string + "<p>Point #"+(i+1)+"	x: "+pointArray[i].x+"	y: "+pointArray[i].y +"</p>";
		}
		document.getElementById(outputDiv).innerHTML = string; 
	});
}