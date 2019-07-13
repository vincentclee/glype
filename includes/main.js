/*******************************************************************
* Glype is copyright and trademark 2007-2016 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
* This file is the javascript library. The version downloaded by the
* user is compressed to save on bandwidth. This uncompressed version
* is available for you to make your own changes if desired.
******************************************************************/

/*****************************************************************
* Set up variables
******************************************************************/

if(parent.frames.length==0) {
	x=location.href;
	if (x.indexOf('f=frame')!=-1||x.indexOf('&frame')!=-1) {
		x=x.replace(/f=frame/,'');
		x=x.replace(/&frame/,'');
		parent.location.href=x;
	}
}


// Shortcut to <URL TO SCRIPT><SCRIPT NAME>
if (ginf) {
	if (ginf.enc.u && ginf.enc.x) {
		ginf.target.h=arcfour(ginf.enc.u,base64_decode(ginf.target.h));
		ginf.target.p=arcfour(ginf.enc.u,base64_decode(ginf.target.p));
		ginf.target.u=arcfour(ginf.enc.u,base64_decode(ginf.target.u));
	}
	siteURL = ginf.url+'/'+ginf.script;
}

// Convert all the document.domain references to a normal variable
// since our document.domain is obviously not what's expected.
ignore = '';

/*****************************************************************
* Helper functions - mostly javascript equivalents of the PHP
* function with the same name
******************************************************************/

function base64_encode(d){var q='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';var z,y,x,w,v,u,t,s,i=0,j=0,p='',r=[];if(!d){return d;}do{z=d.charCodeAt(i++);y=d.charCodeAt(i++);x=d.charCodeAt(i++);s=z<<16|y<<8|x;w=s>>18&0x3f;v=s>>12&0x3f;u=s>>6&0x3f;t=s&0x3f;r[j++]=q.charAt(w)+q.charAt(v)+q.charAt(u)+q.charAt(t);}while(i<d.length);p=r.join('');var r=d.length%3;return(r?p.slice(0,r-3):p)+'==='.slice(r||3);}
function base64_decode(d){var q='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';var z,y,x,w,v,u,t,s,i=0,j=0,r=[];if(!d){return d;}d+='';do{w=q.indexOf(d.charAt(i++));v=q.indexOf(d.charAt(i++));u=q.indexOf(d.charAt(i++));t=q.indexOf(d.charAt(i++));s=w<<18|v<<12|u<<6|t;z=s>>16&0xff;y=s>>8&0xff;x=s&0xff;if(u==64){r[j++]=String.fromCharCode(z);}else if(t==64){r[j++]=String.fromCharCode(z,y);}else{r[j++]=String.fromCharCode(z,y,x);}}while(i<d.length);return r.join('');}
function arcfour(k,d) {var o='';s=new Array();var n=256;l=k.length;for(var i=0;i<n;i++){s[i]=i;}for(var j=i=0;i<n;i++){j=(j+s[i]+k.charCodeAt(i%l))%n;var x=s[i];s[i]=s[j];s[j]=x;}for(var i=j=y=0;y<d.length;y++){i=(i+1)%n;j=(j+s[i])%n;x=s[i];s[i]=s[j];s[j]=x;o+=String.fromCharCode(d.charCodeAt(y)^s[(s[i]+s[j])%n]);}return o;}


// Make a replacement using position and length values
function substr_replace(str,replacement,start,length) {
	return str.substr(0, start) + replacement + str.substr(start +length);
}

// Find position of needle in haystack
function strpos(haystack, needle, offset) {

	// Look for next occurrence
	var i = haystack.indexOf(needle, offset);

	// indexOf returns -1 if not found, we want false
	return i >= 0 ? i : false
}

// Find length of initial segment matching mask
function strspn(input, mask, offset, length) {

	// Set up starting vars
	var length			= length ? offset + length : input.length;
	var i					= offset ? offset : 0;
	var matched			= 0;

	// Loop through chars
	while ( i < length ) {

		// Does this char match the mask?
		if ( mask.indexOf(input.charAt(i)) == -1 ) {

			// No match, end here
			return matched;
		}

		++matched;
		++i;
	}

	return matched;
}

// Get the AJAX object
function fetchAjaxObject() {
	var xmlHttp;

	try {
	  // Firefox, Opera 8.0+, Safari
	  xmlHttp = new XMLHttpRequest;
	} catch (e) {
		// Internet Explorer
		try {
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				return false;
			}
		}
	}

	return xmlHttp;
}


/*****************************************************************
* URL encoding function - takes an absolute or relative URL and
* converts it so that when requested, the resource will be downloaded
* by the proxy. PHP equivalent is "proxyURL()"
******************************************************************/

function parseURL(input, flag) {

	// First, validate the input
	if (!input) {return '';}

	input = input.toString();

	// Is it an anchor?
	if (input.charAt(0)=='#') {return input;}

	// binary image data
	if (input.toLowerCase().indexOf('data:image')===0) {return input;}

	// Is it javascript?
	if (input.toLowerCase().indexOf('javascript:')===0) {return parseJS(input);}
	if (input.toLowerCase().indexOf('livescript:')===0) {return parseJS(input);}

	// Is it a non-page?
	if (input==='about:blank') {return '';}
	if (input.toLowerCase().indexOf('data:')===0) {return '';}
	if (input.toLowerCase().indexOf('file:')===0) {return '';}
	if (input.toLowerCase().indexOf('res:')===0) {return '';}
	if (input.toLowerCase().indexOf('C:')===0) {return '';}

	// Is it already proxied?
	if ( input.indexOf(siteURL) === 0 ) {
		return input;
	}

	// Ensure a complete URL
	if ( input.indexOf('http://') !== 0 && input.indexOf('https://') !== 0 ) {

		// No change if .
		if ( input == '.' ) {
			input = '';
		}

		// Relative from root
		if ( input.charAt(0) == '/' ) {

			// "//domain.com" is also acceptable so check next char as well
			if ( input.length > 0 && input.charAt(1) == '/' ) {

				// Prefix the HTTP and we're done
				input = 'http:' + input;
			} else {

				// Relative path from root so add scheme+host as prefix and we're done
				input = ginf.target.h + input;
			}
		} else if ( ginf.target.b ) {

			// Relative path from base href
			input = ginf.target.b + input;
		} else {

			// Relative from document
			input = ginf.target.h + ginf.target.p + input;
		}
	}

	// Simplify path
	// Strip ./ (refers to current directory)
	input = input.replace('/./', '/');

	// Strip double slash //
	if ( input.length > 8 && input.substr(8).indexOf('//') ) {
		input = input.replace(/[^:]\/\//g, '/');
	}
	 
	// Simplify path by converting /dir/../ to /
	if ( input.indexOf('/..') > 0 ) {
		var urlparts = input.substring(ginf.target.h.length).split(/\//);
		for (var i in urlparts) {
			if ( urlparts[i] == '..' ) {
				input = input.replace('/'+urlparts[i-1]+'/..','');
			}
		}
	}

	// Extract an #anchor
	var jumpTo = '';

	// Find position of #
	var hashPos = input.indexOf('#');

	if ( hashPos >= 0 ) {

		// Split into jumpTo (append it after proxying) and $url
		jumpTo = input.substr(hashPos);
		input = input.substr(0, hashPos);
	}

	// Add encoding
	if ( ginf.enc.e ) {

		// Part of our encoding is to remove HTTP (saves space and helps avoid detection)
		input = input.substr(4);

		// Are we using unique URLs?
		if ( ginf.enc.u ) {

			// Encrypt
			input = base64_encode(arcfour(ginf.enc.u,input));
		}
	}

	// Protect chars that have other meaning in URLs
	input = encodeURIComponent(input);

	// Return in path info format (only when encoding is on)
	if ( ginf.enc.p && ginf.enc.e ) {
		input = input.replace(/%/g,'_');
		return siteURL + '/' + input + '/b' + ginf.b + '/' + ( flag ? 'f' + flag + '/' : '') + jumpTo;
	}

	// Otherwise, return in 'normal' (query string) format
	return siteURL + '?u=' + input + '&b=' + ginf.b + ( flag ? '&f=' + flag : '' ) + jumpTo;
}


/*****************************************************************
* Read options and URL from our form, convert to proxied URL and load it.
* If javascript is disabled, the form POSTs to /includes/process.php
******************************************************************/

function updateLocation(form) {
	 
	// Reset bitfield
	ginf.b = 0;
	 
	// Array of options
	var options = new Array();
	 
	// Loop through form elements
	for ( i=0; i < form.elements.length; i++ ) {
	 
		if ( form.elements[i].name == 'u' ) {

			// Record URL
			url = form.elements[i].value;
		  
		} else if ( form.elements[i].type == 'checkbox' ) {

			// Add option
			options.push(form.elements[i]);

			// Update encode option (for generating the new URL)
			if ( form.elements[i].name == 'encodeURL' ) {
				ginf.enc.e = form.elements[i].checked;
			}
		}
	}
	 
	// Ensure URL entered
	if ( ! url ) {
		return false;
	}

	// Go through available options and edit bitfield
	for ( i=0; i < options.length; i++ ) {
		if ( options[i].checked == true ) {
			ginf.b = ginf.b | Math.pow(2,i);
		}
	}

	// Ensure the user entered the http://
	if ( url.indexOf('http') !== 0 ) {
		url = 'http://' + url;
	}

	// Update location
	window.top.location = myParseURL(url, 'norefer');
	return true;
}


/*****************************************************************
* HTML Parser (any new HTML from document.write() or .innerHTML =
* should be sent through the parser)
******************************************************************/

function parseHTML(html) {

	// Ensure  string
	if ( typeof(html) != 'string' ) {
		return html;
	}

	 // Extract a base tag
	 if ( (parser = /<base href(?==)=["']?([^"' >]+)['"]?(>|\/>|<\/base>)/i.exec(html)) ) {
		  ginf.target.b = parser[1]; // Update base variable for future parsing
		  if ( ginf.target.b.charAt(ginf.target.b.length-1) != '/' ) // Ensure trailing slash
				ginf.target.b += '/'; 
		  html = html.replace(parser[0],''); // Remove from document since we don't want the unproxied URL
	 }
	 
	 // Meta refresh
	 if ( parser = /content=(["'])?([0-9]+)\s*;\s*url=(['"]?)([^"'>]+)\3\1(.*?)(>|\/>)/i.exec(html) )
		  html = html.replace(parser[0],parser[0].replace(parser[4],parseURL(parser[4])));

	 // Proxy an update to URL based attributes
	 html = html.replace(/\.(action|src|location|href)\s*=\s*([^;}]+)/ig,'.$1=parseURL($2)');
	 
	 // Send innerHTML updates through our parser
	 html = html.replace(/\.innerHTML\s*(\+)?=\s*([^};]+)\s*/ig,'.innerHTML$1=parseHTML($2)');
	 
	 // Proxy iframe, ensuring the frame flag is added
	 parser = /<iframe\s+([^>]*)\s*src\s*=\s*(["']?)([^"']+)\2/ig;
	 while ( match = parser.exec(html) ) 
		  html = html.replace(match[0],'<iframe ' +match[1] +' src'+'=' + match[2] + parseURL(match[3],'frame') + match[2] );

	 // Proxy attributes
	 parser = /\s(href|src|background|action)\s*=\s*(["']?)([^"'\s>]+)/ig;
	 while ( match = parser.exec(html) ) {
		  html = html.replace(match[0],' '+match[1]+'='+match[2]+parseURL(match[3]));
	 }

	 // Convert get to post
	 parser = /<fo(?=r)rm((?:(?!method)[^>])*)(?:\s*method\s*=\s*(["']?)(get|post)\2)?([^>]*)>/ig;
	 while ( match = parser.exec(html) )
		  if ( ! match[3] || match[3].toLowerCase() != 'post' )
				html = html.replace(match[0],'<fo'+'rm'+match[1]+' method="post" '+match[4]+'><input type="hidden" name="convertGET" value="1">');

	 // Proxy CSS: url(someurl.com/image.gif)
	 parser = /url\s*\(['"]?([^'"\)]+)['"]?\)/ig;
	 while ( match = parser.exec(html) )
		  html = html.replace(match[0],'url('+parseURL(match[1])+')');

	 // Proxy CSS importing stylesheets
	 parser = /@import\s*['"]([^'"\(\)]+)['"]/ig;
	 while ( match = parser.exec(html) )
		  html = html.replace(match[0],'@import "'+parseURL(match[1])+'"');

	 // Return changed HTML
	 return html;
}


/*****************************************************************
* Parse javascript on the fly - e.g. from a compressed eval()
******************************************************************/

function parseJS(js,debug) {

	// Check valid input
	if ( typeof(js) != 'string' || js == false )
		return js;

	// Replacer function. Our regexes move past the point of interest by
	// enough to ensure we catch the match. Then we use this "callback" function
	// (similar to PHP's preg_replace_callback()) to find the end of the statement
	// and replace the value with our wrapper.
	// To do this we assume the original regex obeys these rules:
	//	 (1) Only one parenthesis in the expression
	//	 (2) The parenthesis captures everything up to the point where the value
	//		  to be 'parsed' starts.
	function replacer(match, type, offset) {

		// Find start position (all positions here are relative to the matched substring,
		// not the entire original document (which is available as a 4th parameter btw))
		var start = type.length;

		// Ensure we haven't already parsed this line
		if ( match.substr(start, 5) == 'parse' ) {
			return match;
		}

		// And end position
		var end = analyze_js(match, start);

		// Determine the wrapper to use. First clear any whitespace.
		type = type.replace(/\s/g, '');

		// If .innerHTML, parse HTML. Otherwise, it's a URL.
		var wrapperFunc = ( type == '.innerHTML=' ) ? 'parseHTML' : 'parseURL';

		// Create the wrapped statement
		var wrapped = wrapperFunc + '(' + match.substring(start, end) + ')';

		// And make the starting replacement 
		return substr_replace(match, wrapped, start, end-start);
	}

	// Replace all. Because we go past the match by quite a way, we may find
	// other statements nested within the match and these would not be replaced.
	// To avoid this, we repeatedly call the .replace() method until it leaves us
	// with an unchanged string - i.e. all possible changes have been made. 
	// Undoubtedly, not ideal but it works for now.
	function replaceAll(input, regex) {
		for ( var previous = input; input = input.replace(regex, replacer), input != previous; previous = input);
		return input;
	}

	// Always parse location.replace() and .innerHTML
	js = replaceAll(js, /\b(location\s*\.\s*replace\s*\(\s*)[\s\S]{0,500}/g);
	js = replaceAll(js, /(\.\s*innerHTML\s*=(?!=)\s*)[\s\S]{0,500}/g);

	// If the "watched" flag is set, parse location=
	if ( window.failed.watched ) {
		js = replaceAll(js, /\b(location(?:\s*\.\s*href)?\s*=(?!=)\s*)[\s\S]{0,500}/g);
	}

	// If the "setters" flag is set, parse all assignments
	if ( window.failed.setters ) {
		js = replaceAll(js, /\b(\.href\s*=(?!=)\s*)[\s\S]{0,500}/g);
		js = replaceAll(js, /\b(\.background\s*=(?!=)\s*)[\s\S]{0,500}/g);
		js = replaceAll(js, /\b(\.src\s*=(?!=)\s*)[\s\S]{0,500}/g);
		js = replaceAll(js, /\b(\.action\s*=(?!=)\s*)[\s\S]{0,500}/g);
	}

	// Prevent attempts to assign document.domain
	js = js.replace(/\bdocument\s*\.\s*domain\s*=/g, 'ignore=');

	// Return updated code
	return js;
}


// Analyze javascript and return offset positions.
// Default is to find the end of the statement, indicated by:
//	 (1) ; while not in string
//	 (2) newline which, if not there, would create invalid syntax
//	 (3) a closing bracket (object, language construct or function call) for which
//		  no corresponding opening bracket was detected AFTER the passed offset
// If (int) $argPos is true, we return an array of the start and end position
// for the nth argument, where n = $argPos. The $start position must be just inside
// the parenthesis of the function call we're interested in.
function analyze_js(input, start, argPos) {

	// Set up starting variables
	var currentArg		= 1;				 // Only used if extracting argument position
	var i					= start;			 // Current character position
	var length			= input.length; // Length of document
	var end				= false;			 // Have we found the end?
	var openObjects	= 0;				 // Number of objects currently open
	var openBrackets	= 0;				 // Number of brackets currently open
	var openArrays		= 0;				 // Number of arrays currently open

	// Loop through input char by char
	while ( end === false && i < length ) {

		// Extract current char
		var currentChar = input.charAt(i);

		// Examine current char
		switch ( currentChar ) {

			// String syntax
			case '"':
			case "'":

				// Move up to the corresponding end of string position, taking
				// into account and escaping backslashes
				while ( ( i = strpos(input, currentChar, i+1) ) && input.charAt(i-1) == '\\' );

				// False? Closing string delimiter not found... assume end of document 
				// although technically we've screwed up (or the syntax is invalid)
				if ( i === false ) {
					end = length;
				}

				break;


			// End of operation
			case ';':
				end = i;
				break;


			// Newlines
			case "\n":
			case "\r":

				// Newlines are ignored if we have an open bracket or array or object
				if ( openObjects || openBrackets || openArrays || argPos ) {
					break;
				}

				// Newlines are also OK if followed by an opening function OR concatenation
				// e.g. someFunc\n(params) or someVar \n + anotherVar
				// Find next non-whitespace char position
				var nextCharPos = i + strspn(input, " \t\r\n", i+1) + 1;

				// And the char that refers to
				var nextChar = input.charAt(nextCharPos);

				// Ensure not end of document and if not, char is allowed
				if ( nextCharPos <= length && ( nextChar == '(' || nextChar == '+' ) ) {

					// Move up offset to our nextChar position and ignore this newline
					i = nextCharPos;
					break;
				}

				// Still here? Newline not OK, set end to this position
				end = i;
				break;


			// Concatenation
			case '+':
				// Our interest in the + operator is it's use in allowing an expression
				// to span multiple lines. If we come across a +, move past all whitespace,
				// including newlines (which would otherwise indicate end of expression).
				i += strspn(input, " \t\r\n", i+1);
				break;


			// Opening chars (objects, parenthesis and arrays)
			case '{':
				++openObjects;
				break;
			case '(':
				++openBrackets;
				break;
			case '[':
				++openArrays;
				break;

			// Closing chars - is there a corresponding open char? 
			// Yes = reduce stored count. No = end of statement.
			case '}':
				openObjects	  ? --openObjects	  : end = i;
				break;
			case ')':
				openBrackets  ? --openBrackets  : end = i;
				break;
			case ']':
				openArrays	  ? --openArrays	  : end = i;
				break;


			// Comma
			case ',':

				// No interest here if not looking for argPos
				if ( ! argPos ) {
					break;
				}

				// Ignore commas inside other functions or whatnot
				if ( openObjects || openBrackets || openArrays ) {
					break;
				}

				// End now
				if ( currentArg == argPos ) {
					end = i;
				}

				// Increase the current argument number
				++currentArg;

				// If we're not after the first arg, start now?
				if ( currentArg == argPos ) {
					var start = i+1;
				}

				break;

			// Any other characters
			default:
				// Do nothing
		}

		// Increase offset
		++i;
	}

	// End not found? Use end of document
	if ( end === false ) {
		end = length;
	}

	// Return array of start/end if looking for argPos
	if ( argPos ) {
		return [start, end];
	}

	// Return end
	return end;
}


/*****************************************************************
* Override native functions. By overriding the native functions
* with our wrapper functions, we save some very expensive regex
* search/replaces. Unfortunately this interferes with ALL javascript,
* including any of our own scripts that we don't want parsed and
* can't edit (for example: 3rd party tracking, ad codes, etc.).
*
* The overriding functions are used subject to the $override_javascript
* configuration option. If disabled, no functions are overridden client
* -side and all parsing must be done server-side. This is not recommended
* for obvious reasons. If you need to run third-party javascript without
* proxy interference, try the disableOverride() function or use an iframe.
*
* Overriden and tested in Firefox 3 and Internet Explorer 7:
*	 X document.write()
*	 X document.writeln()
*	 X window.open()
*	 X XMLHttpRequest.open()
*	 X eval()
*
* "Watched" by non-standard code, tested in Firefox 3:
*	 X location=
*	 X x.location=
*	 X location.href=
*
* Intercepted with __defineSetter__(), tested in Firefox 3:
*	 X .src=
*	 X .href=
*	 X .background=
*	 X .action=
*
* Not handled automatically (in any browser):
*	 location.replace()
*	 .innerHTML=
*
* ... so what happens to everything that can't get handled by overriding
* the native code? On the first page load, we attempt to run the override code
* and catch any exceptions. We record the failed attempts and send that
* information back to the server. Now our server-side javascript parser
* will take care of anything we can't from here.
******************************************************************/

// Object to store failed overrides
window.failed = {};

// window.open()
window.base_open = window.open;
window.open = function() {

	// Get real array of arguments
	var args = Array.prototype.slice.call(arguments);

	// Do want to interfere?
	if ( ginf.override ) {
		args[0] = parseURL(args[0]); 
	} else if ( args[args.length-1] == 'gl' ) {
		args[0] = parseURL(args[0]); 
		args.splice(args.length-1);
	}

	try {
		return window.base_open(args[0],args[1],args[2]);
	} catch (e) {}
};

// AJAX
try {
	// Firefox 3 and others with native XMLHttpRequest
	XMLHttpRequest.prototype.base_open = XMLHttpRequest.prototype.open;
	XMLHttpRequest.prototype.open = function(method, url, async, user, password) {

		// Get real array of arguments
		var args = Array.prototype.slice.call(arguments);

		// Do want to interfere?
		if ( ginf.override ) {
			args[1] = parseURL(args[1], 'ajax'); 
		} else if ( args[args.length-1] == 'gl' ) {
			args[1] = parseURL(args[1], 'ajax'); 
			args.splice(args.length-1);
		}

		return this.base_open.apply(this, args);
	};
} catch (e) {
	try {
		// Use a cross-browser object
		document.write('<scrip'+'t type="text/javascript">'+(function(p,a,c,k,e,d){for(k=a[d[33]]-1;k>=0;k--)c+=e[d[69]][d[74]](a[d[75]](k)-1);a=c[d[73]](' ');for(k=a[d[33]]-1;k>=0;k--)p=p[d[72]](e[d[71]](k%10+(e[d[69]][d[74]](122-e[d[70]][d[76]](k/10))),'g'),a[k]);e[d[3]]('_',p)(d)})("8y s=6x8x109x;8y b=6w6x8x209x,c=6x8x249x8x149x3w!6x8x449x;9z e2w{5x.a5=s?2y s:2y 6x8x09x(_[7]);5x.a4=0w};0y(b3ws8x679x)e8x679x=s8x679x;e8x99x=0;e8x89x=1;e8x49x=2;e8x59x=3;e8x29x=4;e8x489x8x509x=e8x99x;e8x489x8x539x=\"\";e8x489x8x549x=2x;e8x489x8x599x=0;e8x489x8x609x=\"\";e8x489x8x409x=2x;e8x409x=2x;e8x399x=2x;e8x419x=2x;e8x389x=2x;e8x489x8x439x=9z(t,w,a,x,v){0y(4x8x339x<3)a=3x;5x.a2=a;8y r=5x,m=5x8x509x;0y(c){8y i=9z2w{0y(r.a58x509x7we8x29x){f(r);r8x129x2w}};0y(a)6x8x179x(_[42],i)}5x.a58x409x=9z2w{0y(b3w!a)3y;r8x509x=r.a58x509x;k(r);0y(r.a1){r8x509x=e8x99x;3y}0y(r8x509x5we8x29x){f(r);0y(c3wa)6x8x229x(_[42],i)}0y(m7wr8x509x)j(r);m=r8x509x};0y(e8x399x)e8x399x8x169x(5x,4x);5x.a58x439x(t,w,a,x,v);0y(!a3wb){5x8x509x=e8x89x;j(5x)}};e8x489x8x559x=9z(z){0y(e8x419x)e8x419x8x169x(5x,4x);0y(z3wz8x369x){z=6x8x119x?2y 6x8x119x2w8x569x(z):z8x689x;0y(!5x.a38x19x)5x.a58x579x(_[1],_[15])}5x.a58x559x(z);0y(b3w!5x.a2){5x8x509x=e8x89x;k(5x);9y(5x8x509x<e8x29x){5x8x509x0v;j(5x);0y(5x.a1)3y}}};e8x489x8x129x=9z2w{0y(e8x389x)e8x389x8x169x(5x,4x);0y(5x8x509x>e8x99x)5x.a1=3x;5x.a58x129x2w;f(5x)};e8x489x8x279x=9z2w{3y 5x.a58x279x2w};e8x489x8x289x=9z(u){3y 5x.a58x289x(u)};e8x489x8x579x=9z(u,y){0y(!5x.a3)5x.a3=1w;5x.a3[u]=y;3y 5x.a58x579x(u,y)};e8x489x8x139x=9z(u,h,d){8z(8y l=0,q;q=5x.a4[l];l0v)0y(q[0]5wu3wq[1]5wh3wq[2]5wd)3y;5x.a48x499x([u,h,d])};e8x489x8x529x=9z(u,h,d){8z(8y l=0,q;q=5x.a4[l];l0v)0y(q[0]5wu3wq[1]5wh3wq[2]5wd)1z;0y(q)5x.a48x589x(l,1)};e8x489x8x239x=9z(p){8y p={'type':p8x669x,'target':5x,'currentTarget':5x,'eventPhase':2,'bubbles':p8x189x,'cancelable':p8x199x,'timeStamp':p8x649x,'stopPropagation':9z2w1w,'preventDefault':9z2w1w,'0zitEvent':9z2w1w};0y(p8x669x5w_[51]3w5x8x409x)(5x8x409x8x299x4w5x8x409x)8x169x(5x,[p]);8z(8y l=0,q;q=5x.a4[l];l0v)0y(q[0]5wp8x669x3w!q[2])(q[1]8x299x4wq[1])8x169x(5x,[p])};e8x489x8x659x=9z2w{3y '['+_[37]+' '+_[10]+']'};e8x659x=9z2w{3y '['+_[10]+']'};9z j(r){0y(e8x409x)e8x409x8x169x(r);r8x239x({'type':_[51],'bubbles':1x,'cancelable':1x,'timeStamp':2y Date+0})};9z g(r){8y o=r8x549x;0y(c3wo3w!o8x259x3wr8x289x(_[1])8x359x(/[^\\/]+\\/[^\\+]+\\+xml/)){o=2y 6x8x09x(_[6]);o8x349x(r8x539x)}0y(o)0y((c3wo8x459x7w0)4w(o8x259x3wo8x259x8x629x5w_[46]))3y 2x;3y o};9z k(r){7y{r8x539x=r.a58x539x}3z(e)1w7y{r8x549x=g(r.a5)}3z(e)1w7y{r8x599x=r.a58x599x}3z(e)1w7y{r8x609x=r.a58x609x}3z(e)1w};9z f(r){r.a58x409x=2y 6x8x39x;6z r.a3};0y(!6x8x39x8x489x8x169x){6x8x39x8x489x8x169x=9z(r,n){0y(!n)n=0w;r.a0=5x;r.a0(n[0],n[1],n[2],n[3],n[4]);6z r.a0}};6x8x109x=e;",">?!>=!..!,,!>.!>,!>\"!\"\"!>>!}}!\'\'!*)!~|!^\\!^^!\\`\\!uofnvdpe!xpeojx!tjiu!tuofnvhsb!fvsu!mmvo!ftmbg!iujx!fmjix!sbw!zsu!idujxt!gpfqzu!xpsiu!osvufs!xfo!gpfdobutoj!gj!opjudovg!spg!ftmf!fufmfe!umvbgfe!fvojuopd!idubd!ftbd!lbfsc!oj",'',0,this,'ActiveXObject Content-Type DONE Function HEADERS_RECEIVED LOADING Microsoft.XMLDOM Microsoft.XMLHTTP OPENED UNSENT XMLHttpRequest XMLSerializer abort addEventListener all application/xml apply attachEvent bubbles cancelable controllers currentTarget detachEvent dispatchEvent document documentElement eventPhase getAllResponseHeaders getResponseHeader handleEvent http://www.w3.org/XML/1998/namespace http://www.w3.org/ns/xbl initEvent length loadXML match nodeType object onabort onopen onreadystatechange onsend onunload open opera parseError parsererror preventDefault prototype push readyState readystatechange removeEventListener responseText responseXML send serializeToString setRequestHeader splice status statusText stopPropagation tagName target timeStamp toString type wrapped xml String Math RegExp replace split fromCharCode charCodeAt floor'.split(' '))+'<'+'/script>');
		XMLHttpRequest.prototype.base_open = XMLHttpRequest.prototype.open;
		XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
			// Get real array of arguments
			var args = Array.prototype.slice.call(arguments);

			// Do want to interfere?
			if ( ginf.override ) {
				args[1] = parseURL(args[1], 'ajax'); 
			} else if ( args[args.length-1] == 'gl' ) {
				args[1] = parseURL(args[1], 'ajax'); 
				args.splice(args.length-1);
			}

			return this.base_open.apply(this, args);
		};
	} catch (e) {
		// Still no luck, tell the server side parser to deal with this for us
		failed.ajax = true;
	}
}

// document.write() and .writeln()
document.base_write = document.write;
document.base_writeln = document.writeln;

document.write = function(html) {

	// Get real array of arguments
	var args = Array.prototype.slice.call(arguments);

	// Do want to interfere?
	if ( ginf.override || args[args.length-1] == 'gl' ) {
		html = parseHTML(html);
	}

	document.base_write(html);	 
};

document.writeln = function(html) {

	// Get real array of arguments
	var args = Array.prototype.slice.call(arguments);

	// Do want to interfere?
	if ( ginf.override || args[args.length-1] == 'gl' ) {
		html = parseHTML(html);
	}

	document.base_writeln(html);
};

if ( typeof ginf.override != 'undefined' || typeof ginf.test != 'undefined'  ) {

	// eval() - this is a bit of problem in that eval()ing an assigment
	// will create the variable in the scope of our below function, not the
	// function that called the eval(), as would be expected.
	// Solution?
	base_eval = eval;
	eval = function(str) {
		return base_eval(parseJS(str));
	};

	// Location updates in various forms
	try {
		function locationWatcher(id, oldURL, newURL) {
			return parseURL(newURL);
		}
		location.watch('href', locationWatcher);
		window.watch('location', locationWatcher);
		parent.watch('location', locationWatcher);
		self.watch('location', locationWatcher);
		top.watch('location', locationWatcher);
		document.watch('location', locationWatcher);
	} catch (e) {
		// Not entirely unsurprising if we're here since .watch() is non-standard
		failed.watched = true;
	}

	// Setters (innerHTML, href, etc.)
	try {

		var intercept = [HTMLElement, HTMLHtmlElement, HTMLHeadElement, HTMLLinkElement, HTMLStyleElement, HTMLBodyElement, HTMLFormElement, 
							  HTMLSelectElement, HTMLOptionElement, HTMLInputElement, HTMLTextAreaElement, HTMLButtonElement, HTMLLabelElement,
							  HTMLFieldSetElement, HTMLLegendElement, HTMLUListElement, HTMLOListElement, HTMLDListElement, HTMLDirectoryElement, 
							  HTMLMenuElement, HTMLLIElement, HTMLDivElement, HTMLParagraphElement, HTMLHeadingElement, HTMLQuoteElement, HTMLPreElement,
							  HTMLBRElement, HTMLBaseFontElement, HTMLFontElement, HTMLHRElement, HTMLAnchorElement, HTMLImageElement,
							  HTMLObjectElement, HTMLParamElement, HTMLAppletElement, HTMLMapElement, HTMLModElement, HTMLAreaElement, HTMLScriptElement,
							  HTMLTableElement, HTMLTableCaptionElement, HTMLTableColElement, HTMLTableSectionElement, HTMLTableRowElement,
							  HTMLTableCellElement, HTMLFrameSetElement, HTMLFrameElement, HTMLIFrameElement];

		// New setter functions
		newSrc			 = function(value) { try { this.base_setAttribute('src', parseURL(value));			 } catch(ignore) {} };
		newAction		 = function(value) { try { this.base_setAttribute('action', parseURL(value));		 } catch(ignore) {} };
		newHref			 = function(value) { try { this.base_setAttribute('href', parseURL(value));		 } catch(ignore) {} };
		newBackground	 = function(value) { try { this.base_setAttribute('background', parseURL(value)); } catch(ignore) {} };

		// New setAttribute
		mySetAttribute = function(attr, value) {
			try { 
				type = attr.toLowerCase();
				if ( type == 'src' || type == 'href' || type == 'background' || type == 'action' ) {
					value = parseURL(value);
				}
				this.base_setAttribute(attr, value);
			} catch(ignore) {}
		};

		// Loop through all dom objects and add the methods
		for ( i=0, len=intercept.length; i < len; i++ ) {

			// Ignore if not implemented
			if ( typeof intercept[i].prototype == 'undefined' ) {
				continue;
			}

			// Modify the methods to send URLs back through the proxy
			obj = intercept[i].prototype;

			// setAttribute
			obj.base_setAttribute = obj.setAttribute;
			obj.setAttribute = mySetAttribute;

			// __defineSetter__
			obj.__defineSetter__('src', newSrc);
			obj.__defineSetter__('action', newAction);
			obj.__defineSetter__('href', newHref);
			obj.__defineSetter__('background', newBackground);
		}

	} catch(e) {
		// No luck? Handle it server side then
		failed.setters = true;
	}

	// Is this the first run? (i.e. are we testing our override capabilities?)
	if ( typeof ginf.test != 'undefined' ) {

		// Grab an ajax object
		var req = fetchAjaxObject();

		// Convert failed object to string (must be a better way to do this)
		var failures = '';
		if ( failed.ajax )	 failures += '&ajax=1';
		if ( failed.watched ) failures += '&watch=1';
		if ( failed.setters ) failures += '&setters=1';

		// Prepare to send to the server
		req.base_open('GET', ginf.url + '/includes/process.php?action=jstest&' + failures, true);

		// Go go go!
		req.send('');
	}
}


/*****************************************************************
* Enable/disable the override
* Technically we don't actually do anything to the overridden functions
* but we do update the parsing functions to make no changes
******************************************************************/

// Save the parsing functions under aliases so we don't lose
// them if we override
window.myParseHTML = parseHTML;
window.myParseJS	 = parseJS;
window.myParseURL	 = parseURL;

// Create a "parsing" function that simply returns the
// same string as inputted
function noChange(str) {
	return str;
}

// Replace parsing functions with no change to disable override
function disableOverride() {
	window.parseHTML = noChange;
	window.parseJS	  = noChange;
	window.parseURL  = noChange;
}

// Replace parsing functions with "real" functions to enable override
function enableOverride() {

	// Are we even allowed to turn the override on?
	if ( ! ginf.override ) {
		return;
	}

	window.parseHTML = window.myParseHTML;
	window.parseJS	  = window.myParseJS;
	window.parseURL  = window.myParseURL;
}


/*****************************************************************
* Tooltips
* Thanks to http://lixlpixel.org/javascript-tooltips/
******************************************************************/

// position of the tooltip relative to the mouse in pixel //
var offsetx = 12;
var offsety =	8;

function newelement(newid) { 
	 if(document.createElement) { 
		  var el = document.createElement('div'); 
		  el.id = newid;
		  with(el.style) { 
				display = 'none';
				position = 'absolute';
		  } 
		  el.innerHTML = '&nbsp;'; 
		  document.body.appendChild(el); 
	 } 
} 
var ie5 = (document.getElementById && document.all); 
var ns6 = (document.getElementById && !document.all); 
var ua = navigator.userAgent.toLowerCase();
var isapple = (ua.indexOf('applewebkit') != -1 ? 1 : 0);
function getmouseposition(e) {
	 if(document.getElementById) {
		  var iebody=(document.compatMode && 
			document.compatMode != 'BackCompat') ? 
				document.documentElement : document.body;
		  pagex = (isapple == 1 ? 0:(ie5)?iebody.scrollLeft:window.pageXOffset);
		  pagey = (isapple == 1 ? 0:(ie5)?iebody.scrollTop:window.pageYOffset);
		  mousex = (ie5)?event.x:(ns6)?clientX = e.clientX:false;
		  mousey = (ie5)?event.y:(ns6)?clientY = e.clientY:false;

		  var lixlpixel_tooltip = document.getElementById('tooltip');
		  lixlpixel_tooltip.style.left = (mousex+pagex+offsetx) + 'px';
		  lixlpixel_tooltip.style.top = (mousey+pagey+offsety) + 'px';
	 }
}
function tooltip(tip) {
	 if(!document.getElementById('tooltip')) newelement('tooltip');
	 var lixlpixel_tooltip = document.getElementById('tooltip');
	 lixlpixel_tooltip.innerHTML = tip;
	 lixlpixel_tooltip.style.display = 'block';
	 document.onmousemove = getmouseposition;
}
function exit() {
	 document.getElementById('tooltip').style.display = 'none';
}


/*****************************************************************
* DomReady event
* Credit to Dean Edwards/Matthias Miller/John Resig
* http://dean.edwards.name/weblog/2006/06/again/?full#comment5338
******************************************************************/

window.domReadyFuncs = new Array();
window.addDomReadyFunc = function(func) {
	window.domReadyFuncs.push(func);
};

function init() {
	// quit if this function has already been called
	if (arguments.callee.done) return;

	if (ginf.target.u && document.forms.length) {
		if (typeof(document.forms[0].u)=='object') {
			if (document.forms[0].u.value=='') {
				document.forms[0].u.value=ginf.target.u;
			}
		}
	}

	// flag this function so we don't do the same thing twice
	arguments.callee.done = true;

	// kill the timer
	if (_timer) clearInterval(_timer);

	for ( var i=0; i<window.domReadyFuncs.length; ++i ) {
		try {
			window.domReadyFuncs[i]();
		} catch(ignore) {}
	}
  
}

/* for Mozilla/Opera9 */
if (document.addEventListener) {
  document.addEventListener("DOMContentLoaded", init, false);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
	var proto = "src='javascript:void(0)'";
	if (location.protocol == "https:") proto = "src=//0";
	document.base_write("<scr"+"ipt id=__ie_onload defer " + proto + "><\/scr"+"ipt>");	  
	var script = document.getElementById("__ie_onload");
	script.onreadystatechange = function() {
		if (this.readyState == "complete") {
			init(); // call the onload handler
		}
	};
/*@end @*/

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
  var _timer = setInterval(function() {
	 if (/loaded|complete/.test(document.readyState)) {
		init(); // call the onload handler
	 }
  }, 10);
}

/* for other browsers */
window.onload = init;
