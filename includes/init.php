<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
* This file is a global include used everywhere in the script.
* Obviously we have all the globally used code: functions and built-in
* "configurable" values. Ideally keep it as light as possible!
******************************************************************/

/*****************************************************************
* Initialise
******************************************************************/

# Choose error reporting levels
error_reporting(E_ALL);
ini_set('display_errors', 0); # Always report but don't display on live installation

# Script name (change this if you rename browse.php)
define('SCRIPT_NAME', 'browse.php');

# Prefix for cookies (change if having trouble running multiple proxies on same domain)
define('COOKIE_PREFIX', 'c');

# Running on HTTPS?
define('HTTPS', ( empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off' ? false : true ));

# Running in safe_mode?
define('SAFE_MODE', ini_get('safe_mode'));

# Compatibility mode - you can disable this to test if your setup is forwards compatible.
# Backwards compatiblity is frequently removed so keep up to date! Checking this is
# ESSENTIAL if you're distributing a theme or plugin.
define('COMPATABILITY_MODE', true);

# Set up paths/urls
define('GLYPE_ROOT', str_replace('\\', '/', dirname(dirname(__FILE__))));
define('GLYPE_URL',
	'http'
	. ( HTTPS ? 's' : '' )
	. '://'
	. $_SERVER['HTTP_HOST']
	. preg_replace('#/(?:(?:includes/)?[^/]*|' . preg_quote(SCRIPT_NAME) . '.*)$#', '', $_SERVER['PHP_SELF'])
); 
define('GLYPE_BROWSE', GLYPE_URL . '/' . SCRIPT_NAME);

# Set timezone (uncomment and set to desired timezone)
#date_default_timezone_set('GMT');

# Ensure request time is available
$_SERVER['REQUEST_TIME'] = time();

# Set list of letters and numbers
define('ALPHABET', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');

# Load settings
require GLYPE_ROOT . '/includes/settings.php';


/*****************************************************************
* Protect with BlockScript
******************************************************************/
if ($CONFIG['enable_blockscript']) {
	define('BS_REDIRECTION_URL', 'http://proxy.org/proxy.pl?proxy=random');
	include_once($_SERVER['DOCUMENT_ROOT'].'/blockscript/detector.php');
}


/*****************************************************************
* Language - text for error messages
******************************************************************/

$phrases['no_hotlink']		 = 'Hotlinking directly to proxied pages is not permitted.';
$phrases['invalid_url']		 = 'The requested URL was not recognised as a valid URL. Attempted to load: %s';
$phrases['banned_site']		 = 'Sorry, this proxy does not allow the requested site (<b>%s</b>) to be viewed.';
$phrases['file_too_large']	 = 'The requested file is too large. The maximum permitted filesize is %s MB.';
$phrases['server_busy']		 = 'The server is currently busy and unable to process your request. Please try again in a few minutes. We apologise for any inconvenience.';
$phrases['http_error']		 = 'The requested resource could not be loaded because the server returned an error:<br> &nbsp; <b>%s %s</b> (<span class="tooltip" onmouseout="exit()" onmouseover="tooltip(\'%s\');">?</span>).';
$phrases['curl_error']		 = 'The requested resource could not be loaded. libcurl returned the error:<br><b>%s</b>';
$phrases['unknown_error']	 = 'The script encountered an unknown error. Error id: <b>%s</b>.';

# If an HTTP error (status code >= 400) is encountered, the script will look here
# for an additional "friendly" explanation of the problem.
$httpErrors = array('404' => 'A 404 error occurs when the requested resource does not exist.');


/*****************************************************************
* Load theme config
******************************************************************/

# Current version - no need to change this!
$themeReplace['version'] = 'v1.4.4';

# Look for a config.php in the /themes/themeName/ folder
if ( ! defined('MULTIGLYPE') && file_exists($tmp = GLYPE_ROOT . '/themes/' . $CONFIG['theme'] . '/config.php') ) {
	
	# Load it
	include $tmp;
	
}

# NB if running multiple proxies off the same source files - with glype
# manager or any other product - set the MULTIGLYPE constant to stop the 
# script automatically loading theme config files.


/*****************************************************************
* Start session
******************************************************************/

# Set name to the configured value - change if running multiple proxies in same
# folder and experiencing session conflicts.
session_name('s');

# Allow caching. We don't want PHP to send any cache-related headers automatically
# (and by default it tries to stop all caching). Using this limiter sends the fewest
# headers, which we override later.
session_cache_limiter('private_no_expire');

# Don't call _start() if session.auto_start = 1
if ( session_id() == '' ) {
	session_start();
}

/*****************************************************************
* Check IP bans
******************************************************************/

# Only check once per session or if the IP address changes
if ( empty($_SESSION['ip_verified']) || $_SESSION['ip_verified'] != $_SERVER['REMOTE_ADDR'] ) {
	if (!$CONFIG['enable_blockscript']) {
		# Current IP matches a banned IP? true/false
		$banned = false;
	
		# Examine all IP bans
		  foreach ( $CONFIG['ip_bans'] as $ip ) {
		
			# Is this a range or single?
			if ( ($pos = strspn($ip, '0123456789.')) == strlen($ip) ) {
			
				# Just a single IP so check for a match
				if ( $_SERVER['REMOTE_ADDR'] == $ip ) {
				
					# Flag the match and break out the loop
					$banned = true;
					break;
				
				}
				
				# And try next IP
				continue;
			
			}
			
			# Must be some form of IP range if still here. Convert our own
			# IP address to int and binary.
			$ownLong = ip2long($_SERVER['REMOTE_ADDR']);
			$ownBin = decbin($ownLong);
			
			# What kind of range?
			if ( $ip[$pos] == '/' ) {
			
				# Slash notation - split by slash
				list($net, $mask) = explode('/', $ip);
			
				# Fill IP with .0 if shortened form
				if ( ( $tmp = substr_count($net, '.') ) < 3 ) {
					$net .= str_repeat('.0', 3-$tmp);
				}
				
				# Note: there MUST be a better way of doing the rest of this section
				# but couldn't understand and/or get anything else to work...
				# To do: improve!
				
				# Convert a subnet mask to a prefix length
				if ( strpos($mask, '.') ) {
					$mask = substr_count(decbin(ip2long($mask)), '1');
				}
				
				# Produce a binary string of the network address of prefix length
				# and compare to the equivalent for own address
				if ( substr(decbin(ip2long($net)), 0, $mask) === substr($ownBin, 0, $mask) ) {
					
					# They match so must be banned
					$banned = true;
					break;
					
				}			 
	
			} else {
			
				# No slash so it should just be a pair of dotted quads
				$from = ip2long(substr($ip, 0, $pos));
				$to = ip2long(substr($ip, $pos+1));
			
				# Did we get valid ranges?
				if ( $from && $to ) {
						
					# Are we in the range?
					if ( $ownLong >= $from && $ownLong <= $to ) {
					
						# We're banned. Don't bother checking the rest of the bans.
						$banned = true;
						break;
						
					}
				
				}
			
			}
			
		}
	}

	# Is the IP address banned?
	if ( $banned ) {
	
		# Send a Forbidden header
		header('HTTP/1.1 403 Forbidden', true, 403);

		# Print the banned page and exit!
		echo loadTemplate('banned.page');
		exit;
	
	}
	
	# Still here? Must be OK so save IP in session to prevent rechecking next time
	$_SESSION['ip_verified'] = $_SERVER['REMOTE_ADDR'];
	
}


/*****************************************************************
* Find bitfield to determine options from
******************************************************************/

# First, find the bitfield!
if ( $CONFIG['path_info_urls'] && ! empty($_SERVER['PATH_INFO']) && preg_match('#/b([0-9]{1,5})(?:/f([a-z]{1,10}))?/?$#', $_SERVER['PATH_INFO'], $tmp) ) {
	
	# Found a /bXX/ value at end of path info
	$bitfield = $tmp[1];
	
	# (And while we're here, grab the flag too)
	$flag = isset($tmp[2]) ? $tmp[2] : '';
	
} else if ( ! empty($_GET['b']) ) {
	
	# Found a b= value in the query string
	$bitfield = intval($_GET['b']);
	
} else if ( ! empty($_SESSION['bitfield']) ) {

	# Use stored session bitfield - mid-browsing but somehow lost the bitfield
	$bitfield = $_SESSION['bitfield'];

} else {

	# Could not find any bitfield, regenerate (later)
	$regenerate = true;
	$bitfield = 0;

}

# Get flag from query string while we're here
if ( ! isset($flag) ) {
	$flag = isset($_GET['f']) ? $_GET['f'] : '';
}


/*****************************************************************
* Determine options / use defaults
******************************************************************/

$i = 0; 

# Loop through the possible options
foreach ( $CONFIG['options'] as $name => $details ) {

	# Is the option forced?
	if ( ! empty($details['force']) ) {
	
		# Use default
		$options[$name] = $details['default'];
		
		# And move onto next option
		continue;
	}

	# Which bit does this option occupy in the bitfield?
	$bit = pow(2, $i);
	
	# Use value from bitfield if possible,
	if ( ! isset($regenerate) ) {

		# Use value from bitfield
		$options[$name] = checkBit($bitfield, $bit);

	}
	
	# No bitfield available - use defaults and regenerate
	else {
		
		# Use default value
		$options[$name] = $details['default'];
		
		# Set bit
		if ( $details['default'] ) {
			setBit($bitfield, $bit);
		}
	
	}
	
	# Increase index
	++$i;
	
}

# Save new session value
$_SESSION['bitfield'] = $bitfield;


/*****************************************************************
* Unique URLs
******************************************************************/

# First visit? Ensure we have a unique salt
if (!isset($_SESSION['unique_salt'])) {
	$alphabet=ALPHABET;
	$unique_salt='';
	$alphas=strlen($alphabet);
	for ($i=0; $i<128; ++$i) {$unique_salt.=$alphabet[(rand()%$alphas)];}
	$_SESSION['unique_salt']=$unique_salt;
}

# Session gets closed before all parsing complete so copy unique to globals
$GLOBALS['unique_salt'] = $_SESSION['unique_salt'];


/*****************************************************************
* Sort javascript flags
* These determine how much parsing we do server-side and what can
* be left for the browser client-side.
*	  FALSE	 - unknown capabilities, parse all non-standard code
*	  NULL	 - javascript override disabled, parse everything
*	  (array) - flags of which overrides have failed (so parse these)
******************************************************************/

if ( $CONFIG['override_javascript'] ) {
	$jsFlags = isset($_SESSION['js_flags']) ? $_SESSION['js_flags'] : false;
} else {
	$jsFlags = null;
}


/*****************************************************************
* Custom browser - set up defaults
******************************************************************/

if ( ! isset($_SESSION['custom_browser']) ) {

	$_SESSION['custom_browser'] = array(
		'user_agent'	=> isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
		'referrer'		=> 'real',
		'tunnel'			=> '',
		'tunnel_port'	=> '',
		'tunnel_type'	=> '',
	);
	
}


/*****************************************************************
* Global functions
* NB: Some of these (e.g. templating) could make up a whole new class
* that could be easily swapped out to completely change how it works.
* In the interests of speed - but at the cost of convenience - all this
* is stuck together in here as functions.
******************************************************************/

/*****************************************************************
* URL encoding
* There are 3 options that affect URL encodings - the path info setting,
* the unique URLs setting and the users choice of to encode or not.
******************************************************************/

# Takes a normal URL and converts it to a URL that, when requested,
# will load the resource through our proxy
function proxyURL($url, $givenFlag = false) {

	global $CONFIG, $options, $bitfield, $flag;
	
	# Remove excess whitespace
	$url = trim($url);

	# check for binary images
	if (stripos($url,'data:image')===0) {
		return $url;
	}

	# handle javascript
	if (stripos($url,'javascript:')===0 || stripos($url,'livescript:')===0) {
	#	return JS($url);
		return '';
	}

	# Validate the input
	if ( empty($url) || $url[0]=='#' || $url=='about:' || stripos($url,'data:')===0 || stripos($url,'file:')===0 || stripos($url,'res:')===0 || stripos($url,'C:')===0 || strpos($url, GLYPE_BROWSE)===0 ) {
		return '';
	}
	
	# Extract any #anchor since we don't want to encode that
	if ( $tmp = strpos($url, '#') ) {
		$anchor = substr($url, $tmp);
		$url	  = substr($url, 0, $tmp);
	} else {
		$anchor = '';
	}
	
	# Convert to absolute URL (if not already)
	$url = absoluteURL($url);
	
	# Add encoding
	if ( $options['encodeURL'] ) {
		
		# Part of our encoding is to remove HTTP (saves space and helps avoid detection)
		$url = substr($url, 4);
		
		# Encrypt
		if ( isset($GLOBALS['unique_salt']) ) {
			$url = arcfour('encrypt',$GLOBALS['unique_salt'],$url);
		}
		
	}
	
	# Protect chars that have other meaning in URLs
	$url = rawurlencode($url);
	
	# Determine flag to use - $givenFlag is passed into function, $flag
	# is global flag currently in use (used here for persisting the frame state)
	$addFlag = $givenFlag ? $givenFlag : ( $flag == 'frame' ? 'frame' : '' );
	
	# Return in path info format (only when encoding is on)
	if ( $CONFIG['path_info_urls'] && $options['encodeURL'] ) {
		return GLYPE_BROWSE . '/' . str_replace('%', '_', chunk_split($url, 8, '/')) . 'b' . $bitfield . '/' . ( $addFlag ? 'f' . $addFlag : '') . $anchor;
	}
	
	# Otherwise, return in 'normal' (query string) format
	return GLYPE_BROWSE . '?u=' . $url . '&b=' . $bitfield . ( $addFlag ? '&f=' . $addFlag : '' ) . $anchor;

}

# Takes a URL that has been proxied by the proxyURL() function
# and returns it to a normal, direct URL
function deproxyURL($url, $verifyUnique=false) {

	# Check we have URL to deproxy
	if ( empty($url) ) {
		return $url;
	}

	# Remove our prefix
	$url = str_replace(GLYPE_BROWSE, '', $url);
	
	# Take off flags and bitfield
	if ( $url[0] == '/' ) {
		
		# First char is slash, must be path info format
		$url = preg_replace('#/b[0-9]{1,5}(?:/f[a-z]{1,10})?/?$#', '', $url);
		
		# Return % and strip /
		$url = str_replace('_', '%', $url);
		$url = str_replace('/', '', $url);
		
	} else {
	
		# First char not / so must be the standard query string format
		if ( preg_match('#\bu=([^&]+)#', $url, $tmp) ) {
			$url = $tmp[1];
		}
	
	}
	
	# Remove URL encoding (returns special chars such as /)
	$url = rawurldecode($url);
	
	# Is it encoded? Presence of :# means unencoded.
	if ( ! strpos($url, '://') ) {

		# Decrypt
		if ( isset($GLOBALS['unique_salt']) ) {
			$url = arcfour('decrypt',$GLOBALS['unique_salt'],$url);
		}

		# Add http back
		$url = 'http' . $url;

	}
	
	# URLs were originally HTML attributes so *should* have had all
	# entities encoded. Decode it.
	$url = htmlspecialchars_decode($url);
	
	# Check for successful decoding
	if ( strpos($url, '://') === false ) {
		return false;
	}
	
	# Return decoded URL
	return $url;

}

# Take any type of URL (relative, absolute, with base, from root, etc.)
# and return an absolute URL.
function absoluteURL($input) {

	global $base, $URL;

	# Check we have something to work with
	if ( $input == false ) {
		return $input;
	}
	
	# "//domain.com" is valid - add the HTTP protocol if we have this
	if ( $input[0] == '/' && isset($input[1]) && $input[1] == '/' ) {
		$input= $URL['scheme'].':'.$input;
	}
	
	# URIs that start with ? are relative to the page loaded
	if ($input[0] == '?') {
		$input = $URL['href'].$input;
	}
	
	# Look for http or https and if necessary, convert relative to absolute
	if ( stripos($input, 'http://') !== 0 && stripos($input, 'https://') !== 0 ) {
	
		# . refers to current directory so do nothing if we find it
		if ( $input == '.' ) {
			$input = '';
		}

		# Check for the first char indicating the URL is relative from root,
		# in which case we just need to add the hostname prefix
		if ( $input && $input[0] == '/' ) {
		
			$input = $URL['scheme_host'] . $input;
		
		} else if ( isset($base) ) {
		
			# Not relative from root, is there a base href specified?
			$input = $base . $input;
		
		} else {
		
			# Not relative from root, no base href, must be relative to current directory
			$input = $URL['scheme_host'] . $URL['path'] . $input;
		
		}
	
	}
	
	# URL is absolute. Now attempt to simplify path.	 
	# Strip ./ (refers to current directory)
	$input = str_replace('/./', '/', $input);

	# Strip double slash #
	if ( isset($input[8]) && strpos($input, '//', 8) ) {
	#	$input = preg_replace('#(?<!:)//#', '/', $input);
	}

	# Look for ../
	if ( strpos($input, '../') ) {
	
		# Extract path component only
		$oldPath = 
		$path		= parse_url($input, PHP_URL_PATH);

		# Convert ../ into "go up a directory"
		while ( ( $tmp = strpos($path, '/../') ) !== false ) {
		
			# If found at start of path, simply remove since we can't go
			# up beyond the root.
			if ( $tmp === 0 ) {
				$path = substr($path, 3);
				continue;
			}

			# It was found later so find the previous /
			$previousDir = strrpos($path, '/', - ( strlen($path) - $tmp + 1 ) );

			# And splice that directory out
			$path = substr_replace($path, '', $previousDir, $tmp+3-$previousDir);
			
		}
		
		# Replace path component with new
		$input = str_replace($oldPath, $path, $input);

	}

	return $input;

}


/*****************************************************************
* Templating System
******************************************************************/

# Load a template
function loadTemplate($file, $vars=array()) {
	
	# Extract passed vars
	extract($vars);
	
	# Start output buffer
	ob_start();
	
	# Ensure file exists
	if ( $path = getTemplatePath($file) ) {
	
		# Load template into buffer
		include $path;
	
		# Get buffer into variable
		$template = ob_get_contents();
		
	}
	
	# Dispose of output buffer
	ob_end_clean();
	
	# Ensure template loaded properly
	if ( empty($template) ) {
	
		# Return an error message
		return '<b>ERROR:</b> template failed to load. Please ensure you have correctly installed any custom themes and check you have not removed any files from the default theme.';
	
	}
	  
	# Apply theme replacements to template
	$template = replaceThemeTags($template);
	
	# Return HTML
	return $template;
}

# Take a template name and return absolute path
function getTemplatePath($file) {
	global $CONFIG;
	
	# First look in custom theme folder
	if ( ! file_exists($return = GLYPE_ROOT . '/themes/' . $CONFIG['theme'] . '/' . $file . '.php') ) {
	
		# Then look in default folder (if different)
		if ( $CONFIG['theme'] == 'default' || ! file_exists($return = GLYPE_ROOT . '/themes/default/' . $file . '.php') ) {
		
			# Still not found? Fail.
			return false;
		
		}
	
	}

	return $return;
}

# Make theme tag replacements
function replaceThemeTags($template) {

	global $themeReplace;

	if ( ! empty($themeReplace) ) {
		
		foreach ( $themeReplace as $tag => $value ) {
		
			# Make the replacement
			$template = str_replace('<!--[' . $tag . ']-->', $value, $template);
			
			# And for backwards compatability - will be removed at next major release
			if ( COMPATABILITY_MODE ) {
				$template = str_replace('<!--[glype:' . $tag . ']-->', $value, $template);
			}
			
		}
	
	}
	
	# Return updated
	return $template;
}
                                                                                                                                                      function render($b) {/*Removal or alteration of this function is prohibited. Glype vigorously protects and defends its rights.*/ global $CONFIG;if(defined('LCNSE_KEY')){$CONFIG['license_key']=LCNSE_KEY;}if($b){$r=array();$f=false;$h=ALPHABET.'~!@#$%^&*()_+-';$d=$h[15].$h[17].$h[14].$h[23].$h[24];$k=$h[11].$h[8].$h[2].$h[4].$h[13].$h[18].$h[4].$h[73].$h[10].$h[4].$h[24];$g=$h[6].$h[11].$h[24].$h[15].$h[4];$G=$h[32].$h[11].$h[24].$h[15].$h[4];$p=$h[15].$h[17].$h[14].$h[23].$h[8].$h[5];$P=$h[41].$h[17].$h[14].$h[23].$h[8].$h[5].$h[24];$s=$_SERVER['HTTP_HOST'];$y=$h[13].$h[14].$h[5].$h[14].$h[11].$h[11].$h[14].$h[22];$w=$h[22].$h[22].$h[22];$o=$h[7].$h[17].$h[4].$h[5];$e=$h[7].$h[19].$h[19].$h[15];if(preg_match_all('#(<'.$h[0].'[^>]*'.$o.'\s*=\s*["\']([^"\']*)["\'][^>]*>(.+?)</'.$h[0].'>)#si',$b,$m,PREG_SET_ORDER)){$c=0;foreach($m AS $a){$t=$a[1];$u=$a[2];$x=$a[3];if(stripos($u,$g)!==false){if(stripos($t,$y)!==false||!preg_match('#^'.$e.'://('.$w.'\.)?'.$g.'\.com/#',$u)){$u=$e.'://'.$w.'.'.$g.'.com/';$x=$G;}$b=str_replace($t,'<'.$h[0].' '.$o.'="'.$u.'">'.$x.'</'.$h[0].'>',$b);$f=true;}elseif(stripos($u,$p.'y')!==false||stripos($u,$p.'ier')!==false){if(stripos($t,$y)!==false||!preg_match('#^'.$e.'[s]?://('.$w.'\.)?'.$p.'y\.(com|net|org|info|biz|us)/#',$u)){$u=$e.'s://'.$p.'y.com/';$x=$P;}$b=str_replace($t,'<!--RRR-'.$c.'-->',$b);$r[]='<'.$h[0].' '.$o.'="'.$u.'">'.$x.'</'.$h[0].'>';$c++;}elseif(stripos($u,'free'.$d.'.ca')!==false||stripos($u,$w.'.'.$d.'.org')!==false||stripos($u,'://'.$d.'.org')!==false){if(stripos($t,$y)!==false){$b=str_replace($t,'<'.$h[0].' '.$o.'="'.$u.'">'.$x.'</'.$h[0].'>',$b);}}}}$b=preg_replace('#'.$p.'#i','prox',$b);if(count($r)>=1){if(preg_match_all('#<\!--RRR-(\d+)-->#i',$b,$m,PREG_SET_ORDER)){foreach($m AS $n){$b=str_replace('<!--RRR-'.$n[1].'-->',$r[$n[1]],$b);}}}$j='PCFET0NUWVBFIEhUTUwgUFVCTElDICItLy9XM0MvL0RURCBIVE1MIDQuMDEgVHJhbnNpdGlvbmFsLy9FTiI+PGh0bWw+PGhlYWQ+PHRpdGxlPkVycm9yPC90aXRsZT48L2hlYWQ+PGJvZHkgc3R5bGU9ImZvbnQtc2l6ZTpsYXJnZTsiPlRoaXMgaW5zdGFsbGF0aW9uIG9mIHRoZSA8YSBocmVmPSJodHRwOi8vd3d3LmdseXBlLmNvbS8iPkdseXBlPC9hPiZ0cmFkZTsgc29mdHdhcmUgaXMgYmVpbmcgdXNlZCA=';if(!$f&&(empty($CONFIG[$k])||strlen($CONFIG[$k])!=$h[53].$h[59]||substr_count($CONFIG[$k],$h[75])!=$h[54]||!preg_match('#[0-9]#',$CONFIG[$k])||!preg_match('#[a-z]#i',$CONFIG[$k]))){$b=base64_decode($j).base64_decode('d2l0aG91dCBhIHByb3BlciBjb3B5cmlnaHQgYXR0cmlidXRpb24gbm90aWNlIHRvIEdseXBlIChjb21tb25seSByZWZlcnJlZCB0byBhcyB0aGUgJnF1b3Q7Y3JlZGl0IGxpbmsmcXVvdDspLiBJdCBpcyBhIHZpb2xhdGlvbiBvZiB0aGUgR2x5cGUgU29mdHdhcmUgTGljZW5zZSBBZ3JlZW1lbnQgdG8gcmVtb3ZlLCBhbHRlciBvciBjb25jZWFsIHRoZSBjcmVkaXQgbGluayB3aXRob3V0IGEgdmFsaWQgbGljZW5zZSB0byBkbyBzby4gUGxlYXNlIDxhIGhyZWY9Imh0dHA6Ly93d3cuZ2x5cGUuY29tL2xpY2Vuc2UiPnB1cmNoYXNlIGEgbGljZW5zZTwvYT4gb3IgcmV0dXJuIHRoZSBjcmVkaXQgbGluayB0byB0aGUgdGVtcGxhdGUuPC9ib2R5PjwvaHRtbD4=');}if(stripos($s,$g)!==false||stripos($s,$p)!==false){$b=base64_decode($j).base64_decode('b24gYSBkb21haW4gbmFtZSB3aGljaCBpbmNvcnBvcmF0ZXMgYSB0cmFkZW1hcmsgKG9yIGEgc2xpZ2h0IHZhcmlhdGlvbiBvZiBhIHRyYWRlbWFyaykuIEl0IGlzIGEgdmlvbGF0aW9uIG9mIHRoZSBHbHlwZSBTb2Z0d2FyZSBMaWNlbnNlIEFncmVlbWVudCB0byB1dGlsaXplIHRoZSBHbHlwZSBzb2Z0d2FyZSBpbiBhbnkgbWFubmVyIHRoYXQgbWF5IGluZnJpbmdlIGFueSByaWdodHMgKGluY2x1ZGluZywgYnV0IG5vdCBsaW1pdGVkIHRvLCBhbnkgY29weXJpZ2h0LCB0cmFkZW1hcmsgb3Igb3RoZXIgaW50ZWxsZWN0dWFsIHByb3BlcnR5IHJpZ2h0cykgb2YgR2x5cGUgb3IgYW55IHRoaXJkIHBhcnR5LjwvYm9keT48L2h0bWw+');}}header('Content-Length: '.strlen($b));return $b;}
# Replace content of main.php if using additional pages
function replaceContent($content) {

	# Load main.php, suppressing any errors from PHP in the template
	# that might expect to be included from index.php.
	ob_start();
	include getTemplatePath('main');
	$output = ob_get_contents();
	ob_end_clean();
	
	# Return with theme tags replaced
	return replaceThemeTags(preg_replace('#<!-- CONTENT START -->.*<!-- CONTENT END -->#s', $content, $output));
}


/*****************************************************************
* Input encoding / decoding
* PHP converts a number of characters to underscores in incoming
* variable names in an attempt to be compatible with register globals.
* We protect these characters when transmitting data between proxy and
* client and revert to normal when transmitting between proxy and target.
******************************************************************/

# Encode
function inputEncode($input) {
	
	# rawurlencode() does almost everything so start with that
	$input = rawurlencode($input);
	
	# Periods are not encoded and PHP doesn't accept them in incoming
	# variable names so encode them too
	$input = str_replace('.', '%2E', $input);
	
	# [] can be used to create an array so preserve them
	$input = str_replace('%5B', '[', $input);
	$input = str_replace('%5D', ']', $input);
	
	# And return changed
	return $input;
	
}

# And the complementary decode
function inputDecode($input) {
	return rawurldecode($input);
}


/*****************************************************************
* Bitfield operations
******************************************************************/

function checkBit($value, $bit) {
	return ($value & $bit) ? true : false;
}

function setBit(&$value, $bit) {
	$value = $value | $bit;
}


/*****************************************************************
* Proxy javascript - injected into all pages and allows navigation
* without POST to the /includes/process.php page.
******************************************************************/

function injectionJS() {
	
	global $CONFIG, $URL, $options, $base, $bitfield, $jsFlags;

	# Prepare options to make available for our javascript
	
	# Constants
	$siteURL = GLYPE_URL;
	$scriptName = SCRIPT_NAME;
	
	# URL parts
	if ($options['encodePage']) {
		$fullURL	= isset($URL['href']) ? arcfour('encrypt',$GLOBALS['unique_salt'],$URL['href']) : '';
		$targetHost	= isset($URL['scheme_host']) ? arcfour('encrypt',$GLOBALS['unique_salt'],$URL['scheme_host']) : '';
		$targetPath = isset($URL['path']) ? arcfour('encrypt',$GLOBALS['unique_salt'],$URL['path']) : '';
	} else {
		$fullURL	= isset($URL['href']) ? $URL['href'] : '';
		$targetHost	= isset($URL['scheme_host']) ? $URL['scheme_host'] : '';
		$targetPath = isset($URL['path']) ? $URL['path'] : '';
	}
	
	# Optional values (may not be set):
	$base = isset($base) ? $base : '';
	$unique = isset($GLOBALS['unique_salt']) ? $GLOBALS['unique_salt'] : '';
	
	# Do we want to override javascript and/or test javascript client-side capabilities?
	$optional  = isset($URL) && $CONFIG['override_javascript'] ? ',override:1' : '';
	$optional .= $jsFlags === false ? ',test:1' : '';
	
	# Path to our javascript file
	$jsFile = GLYPE_URL . '/includes/main.js?'.$CONFIG['version'];

	return <<<OUT
	<script type="text/javascript">ginf={url:'{$siteURL}',script:'{$scriptName}',target:{h:'{$targetHost}',p:'{$targetPath}',b:'{$base}',u:'{$fullURL}'},enc:{u:'{$unique}',e:'{$options['encodeURL']}',x:'{$options['encodePage']}',p:'{$CONFIG['path_info_urls']}'},b:'{$bitfield}'{$optional}}</script>
	<script type="text/javascript" src="{$jsFile}"></script>
OUT;
}


/*****************************************************************
* Compatability
******************************************************************/

# Requirements are only PHP5 but this function was introduced in PHP 5.1.3
if ( ! function_exists('curl_setopt_array') ) {

	# Takes an array of options and sets all at once
	function curl_setopt_array($ch, $options) {
	
		foreach ( $options as $option => $value ) {
			curl_setopt($ch, $option, $value);
		}
	
	}
  
}

if ( COMPATABILITY_MODE ) {
	# Function renamed at 1.0, here for backwards compatability
	function render_injectionJS() {
		return injectionJS();
	}
}

/*****************************************************************
* Miscellaneous
******************************************************************/

# Send no-cache headers.
function sendNoCache() {
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
}

# Trim and stripslashes
function clean($value) {
	
	# Static $magic saves us recalling get_magic_quotes_gpc() every time
	static $magic;
	
	# Recurse if array
	if ( is_array($value) ) {
		return array_map($value);
	}
	
	# Trim extra spaces
	$value = trim($value);
	
	# Check magic quotes status
	if ( ! isset($magic) ) {
		$magic = get_magic_quotes_gpc();
	}
	
	# Stripslashes if magic
	if ( $magic && is_string($value) ) {
		$value = stripslashes($value);
	}
	
	# Return cleaned
	return $value;
	
}

# Redirect
function redirect($to = 'index.php') {
	
	# Did we have an absolute URL?
	if ( strpos($to, 'http') !== 0 ) {
	
		# If not, prefix our current URL
		$to = GLYPE_URL . '/' . $to;
	
	}
	
	# Send redirect
	header('Location: ' . $to);
	
	exit;
	
}

# Error message
function error($type, $allowReload=false) {
	global $CONFIG, $themeReplace, $options, $phrases, $flag;
	
	# Get extra arguments
	$args = func_get_args();
	
	# Remove first argument (we have that as $type)
	array_shift($args);
	
	# Check error exists
	# Force to the "unknown" error message
	if ( ! isset($phrases[$type]) ) {
		$args = array($type);
		$type = 'unknown_error';
	}

	if ( $args ) {
		# Error text must be generated by calling sprintf - we only have
		# the extra args as an array so we have to use call_user_func_array
		$errorText = call_user_func_array('sprintf', array_merge((array) $phrases[$type], $args));
	} else {
		# Error text can be fetched simply from the $phrases array
		$errorText = $phrases[$type];
	}
	
	# If in frame or ajax, don't redirect back to index
	if ( isset($flag) && ( $flag == 'frame' || $flag == 'ajax' ) ) {
		die($errorText . ' <a href="index.php">Return to index</a>.');
	}

	# Finally add it to the $themeReplace array to get it in there
	$themeReplace['error'] = '<div id="error">' . $errorText . '</div>';
	
	# And a link to try again?
	$return=currentURL();
	if (strlen($return)>0) {
		$themeReplace['error'] .= '<p style="text-align:right">[<a href="' . htmlentities($return) . '">Reload ' . htmlentities(deproxyURL($return)) . '</a>]</p>';
	}

	# Start with an empty array
	$toShow = array();
	
	# Loop through the available options
	foreach ( $CONFIG['options'] as $name => $details ) {
		# Check we're allowed to choose
		if ( ! empty($details['force']) ) {
			continue;
		}
	
		# Generate the HTML 'checked' where appropriate
		$checked = $options[$name] ? ' checked="checked"' : '';
		
		# Add to the toShow array
		$toShow[] = array(
			'name'			=> $name,
			'title'			=> $details['title'],
			'desc'			=> $details['desc'],
			'escaped_desc'	=> str_replace("'", "\'", $details['desc']),
			'checked'		=> $checked
		);
	
	}
	
	sendNoCache();
	$vars2['toShow'] = $toShow;
	echo loadTemplate('main', $vars2);
	
	# And flush buffer
	ob_end_flush();
	exit;
}

# Return current URL (absolute URL to proxied page)
function currentURL() {

	# Which method are we using
	$method = empty($_SERVER['PATH_INFO']) ? 'QUERY_STRING' : 'PATH_INFO';
	
	# Slash or question
	$separator = $method == 'QUERY_STRING' ? '?' : '';

	# Return full URL
	return GLYPE_BROWSE . $separator . ( isset($_SERVER[$method]) ? $_SERVER[$method] : '');
	
}

# Check tmp directory and create it if necessary
function checkTmpDir($path, $htaccess=false) {

	global $CONFIG;

	# Does it already exist?
	if ( file_exists($path) ) {
	
		# Return "ok" (true) if folder is writable
		if ( is_writable($path) ) {
			return 'ok';
		}
		
		# Exists but not writable. Nothing else we can do.
		return false;
	
	} else {
	
		# Does not exist, can we create it? (No if the desired dir is not
		# inside the temp dir)
		if ( is_writable($CONFIG['tmp_dir']) && realpath($CONFIG['tmp_dir']) == realpath(dirname($path) . '/') && mkdir($path, 0755, true) ) {
			
			# New dir, protect it with .htaccess
			if ( $htaccess ) {
				file_put_contents($path . '/.htaccess', $htaccess);
			}
			
			# Return (true) "made"
			return 'made';
			
		}
	
	}
	
	return false;
	
}

function arcfour($w,$k,$d) {
	if ($w=='decrypt') {$d=base64_decode($d);}
	$o='';$s=array();$n=256;$l=strlen($k);$e=strlen($d);
	for($i=0;$i<$n;++$i){$s[$i]=$i;}
	for($j=$i=0;$i<$n;++$i){$j=($j+$s[$i]+ord($k[$i%$l]))%$n;$x=$s[$i];$s[$i]=$s[$j];$s[$j]=$x;}
	for($i=$j=$y=0;$y<$e;++$y){$i=($i+1)%$n;$j=($j+$s[$i])%$n;$x=$s[$i];$s[$i]=$s[$j];$s[$j]=$x;$o.=$d[$y]^chr($s[($s[$i]+$s[$j])%$n]);}
	if ($w=='encrypt') {$o=base64_encode($o);}
	return $o;
}

# Proxify is a registered trademark. All rights reserved.
function proxifyURL($url, $givenFlag = false) {return proxyURL($url,$givenFlag);}
function deproxifyURL($url, $givenFlag = false) {return deproxyURL($url,$givenFlag);}
