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
 * myUtil.js
 *
 * These are just a few utility functions that make JavaScript a little easier.
 *
 * Some of this code is modified from the book JavaScript: The Definitive Guide, 
 * 5th Edition, by David Flanagan. Copyright 2006 O'Reilly Media, Inc. (ISBN #0596101996)
 * 
 */

(function(){

	// PRIVATE:
	/////////////////////////////////////////////////////////////////
	var _factories = [
		function() { return new XMLHttpRequest(); },
		function() { return new ActiveXObject("Microsoft.XMLHTTP"); },
		function() { return new ActiveXObject("MSXML2.XMLHTTP.3.0"); },
		function() { return new ActiveXObject("MSXML2.XMLHTTP"); }
	];
	var _factory = null;
	function _newRequest() {
		if (_factory != null) return _factory();
		
		for(var i = 0; i < _factories.length; ++i) {
			try {
				var factory = _factories[i];
				var request = factory();
				if(request != null) {
					_factory = factory;
					return request;
				}
			} catch (e) {
				continue;
			}
		}
		
		_factory = function() { 
			throw new Error("XMLHttpRequest not supported");
		}
		_factory();
	}

	function _encodeFormData(data) {
   	var pairs = [];
    	var regexp = /%20/g; // A regular expression to match an encoded space

    	for(var name in data) {
   		var value = data[name].toString();
        	// Create a name/value pair, but encode name and value first
        	// The global function encodeURIComponent does almost what we want,
        	// but it encodes spaces as %20 instead of as "+". We have to
        	// fix that with String.replace()
        	var pair = encodeURIComponent(name).replace(regexp,"+") + '=' +
            encodeURIComponent(value).replace(regexp,"+");
        	pairs.push(pair);
    	}

    	// Concatenate all the name/value pairs, separating them with &
    	return pairs.join('&');
};

	function _getResponse(request) {
		switch(request.getResponseHeader("Content-Type")) {
			case "text/xml": 
				return request.responseXML;
			case "text/json":
			case "text/javascript":
			case "application/javascript":
			case "application/x-javascript":
				return eval('(' + request.responseText + ')'); // not safe!!!!!
			default:
				return request.responseText;
		}
	}
	

	// PUBLIC:
	//////////////////////////////////////////////////////////////////
	window.myUtil = {
		/*
		 * POST() delivers an ajax call with the "POST" method. Currently,
		 * all we're dealing with is plain text.
		 */
		// Examle ajax request:
		// var params = { "data" : "hello" };
		// myUtil.POST("test.php", params, function(response) {
		//		alert(response);
		// });
		POST: 			
			function(url, values, callback) { 
				 var request = _newRequest();
				 request.onreadystatechange = function() {
				 	if(request.readyState == 4) // request is finished
						if(request.status == 200) // request is successful
							callback(_getResponse(request));
				 }
				 
				 request.open("POST", url);
				 request.setRequestHeader(
				 	"Content-Type",
				 	"application/x-www-form-urlencoded");
				 request.send(_encodeFormData(values));				 
			},
			
		/*
		 * GET() delivers an ajax call with the "GET" method. Currently,
		 * all we're dealing with is plain text.
		 */
		GET:
			function(url, callback) {
				var request  = _newRequest();
				request.onreadystatechange = function() {
					if(request.readyState == 4) // request is finished
						if(request.status == 200) // request is successful
							callback(request.responseText);
				}
				request.open("GET", url);
				request.send(null);
			},
		
		/*
		 * displayProps() displays the properties and their values of the given object.
		 */
		displayProps:
			function (object, div) {
				for(var prop in object) 
					document.getElementById(div).innerHTML = 
						document.getElementById(div).innerHTML + prop + " : " + object[prop] + "<br><br>";
			}
		
		/*
		 * Send POST while skipping html altogether.
		 */
		post_to_url:
			function (url, params) {
   			var form = document.createElement('form');
   			form.action = url;
   			form.method = 'POST';

    			for (var i in params) {
        			if (params.hasOwnProperty(i)) {
         		   var input = document.createElement('input');
            		input.type = 'hidden';
         			input.name = i;
      				input.value = params[i];
         			form.appendChild(input);
      			}
   			}

   			form.submit();
			}
	};
})();