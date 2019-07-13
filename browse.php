<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
* This file is the main component of the glype proxy application.
* It decodes values contained within the current URI to determine a
* resource to download and pass onto the user.
******************************************************************/

/*****************************************************************
* Initialise
******************************************************************/

require 'includes/init.php';

if (count($adminDetails)===0) {
	header("HTTP/1.1 302 Found"); header("Location: admin.php"); exit;
}

# Debug mode - stores extra information in the cURL wrapper object and prints it
# out. It produces an ugly mess but still a quick tool for debugging.
define('DEBUG_MODE', 0);
define('CURL_LOG', 0);

# Log cURLs activity to file
# Change filename below if desired. Ensure file exists and is writable.
if ( CURL_LOG	&& ( $fh = @fopen('curl.txt', 'w')) ) {
	$toSet[CURLOPT_STDERR] = $fh;
	$toSet[CURLOPT_VERBOSE] = true;
}


/*****************************************************************
* PHP sends some headers by default. Stop them.
******************************************************************/

# Clear the default mime-type
header('Content-Type:');

# And remove the caching headers
header('Cache-Control:');
header('Last-Modified:');


/*****************************************************************
* Find URI of resource to load
* NB: flag and bitfield already extracted in /includes/init.php
******************************************************************/

switch ( true ) {

	# Try query string for URL
	case ! empty($_GET['u']) && ( $toLoad = deproxyURL($_GET['u'], true) ):
		break;
		
	# Try path info
	case ! empty($_SERVER['PATH_INFO'])	 && ( $toLoad = deproxyURL($_SERVER['PATH_INFO'], true) ):
		break;
		
	# Found no valid URL, return to index
	default:
		redirect();
}

# Validate the URL
if ( ! preg_match('#^((https?)://(?:([a-z0-9-.]+:[a-z0-9-.]+)@)?([a-z0-9-.]+)(?::([0-9]+))?)(?:/|$)((?:[^?/]*/)*)([^?]*)(?:\?([^\#]*))?(?:\#.*)?$#i', $toLoad, $tmp) ) {

	# Invalid, show error
	error('invalid_url', htmlentities($toLoad));

}

# Rename parts to more useful names
$URL = array(
	'scheme_host'	=> $tmp[1],
	'scheme'		=> $tmp[2],
	'auth'			=> $tmp[3],
	'host'			=> strtolower($tmp[4]),
	'domain'		=> strtolower(preg_match('#(?:^|\.)([a-z0-9-]+\.(?:[a-z.]{5,6}|[a-z]{2,}))$#', $tmp[4], $domain) ? $domain[1] : $tmp[4]), # Attempt to split off the subdomain (if any)
	'port'			=> $tmp[5],
	'path'			=> '/' . $tmp[6],
	'filename'		=> $tmp[7],
	'extension'		=> pathinfo($tmp[7], PATHINFO_EXTENSION),
	'query'			=> isset($tmp[8]) ? $tmp[8] : ''
);

# Apply encoding on full URL. In theory all parts of the URL need various special
# characters encoding but this needs to be done by the author of the webpage.
# We can make a guess at what needs encoding but some servers will complain when
# receiving the encoded character instead of unencoded and vice versa. We want
# to edit the URL as little as possible so we're only encoding spaces, as this
# seems to 'fix' the majority of cases.
$URL['href'] = str_replace(' ', '%20', $toLoad);

# Protect LAN from access through proxy (protected addresses copied from PHProxy)
if ( preg_match('#^(?:127\.|192\.168\.|10\.|172\.(?:1[6-9]|2[0-9]|3[01])\.|localhost)#i', $URL['host']) ) {
	error('banned_site', $URL['host']);
}

# Add any supplied authentication information to our auth array
if ( $URL['auth'] ) {
	$_SESSION['authenticate'][$URL['scheme_host']] = $URL['auth'];
}


/*****************************************************************
* Protect us from hotlinking
******************************************************************/

# Protect only if option is enabled and we don't have a verified session
if ( $CONFIG['stop_hotlinking'] && empty($_SESSION['no_hotlink']) ) {

	# Assume hotlinking to start with, then check against allowed domains
	$tmp = true;

	# Ensure we have valid referrer information to check
	if ( ! empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'http') === 0 ) {
		
		# Examine all the allowed domains (including our current domain)
		foreach ( array_merge( (array) GLYPE_URL, $CONFIG['hotlink_domains'] ) as $domain ) {

			# Do a case-insensitive comparison
			if ( stripos($_SERVER['HTTP_REFERER'], $domain) !== false ) {
				
				# This referrer is OK
				$tmp = false;
				break;
				
			}
		
		}

	}
	
	# Redirect to index if this is still identified as hotlinking
	if ( $tmp ) {
		error('no_hotlink');
	}

}

# If we're still here, the referrer must be OK so set the session for next time
$_SESSION['no_hotlink'] = true;


/*****************************************************************
* Are we allowed to visit this site? Check whitelist/blacklist
******************************************************************/

# Whitelist - deny IF NOT on list
if ( ! empty($CONFIG['whitelist']) ) {

	$tmp = false;

	# Loop through
	foreach ( $CONFIG['whitelist'] as $domain ) {

		# Check for match
		if ( strpos($URL['host'], $domain) !== false ) {

			# Must be a permitted site
			$tmp = true;

		}

	}

	# Unless $tmp is flagged true, this is an illegal site
	if ( ! $tmp ) {
		error('banned_site', $URL['host']);
	}

}

# Blacklist
if ( ! empty($CONFIG['blacklist']) ) {

	# Loop through
	foreach ( $CONFIG['blacklist'] as $domain ) {

		# Check for match
		if ( strpos($URL['host'], $domain) !== false ) {

			# If matched, site is banned
			error('banned_site', $URL['host']);

		}

	}

}


/*****************************************************************
* Show SSL warning
* This warns users if they access a secure site when the proxy is NOT
* on a secure connection and the $CONFIG['ssl_warning'] option is on.
******************************************************************/

if ( $URL['scheme'] == 'https' && $CONFIG['ssl_warning'] && empty($_SESSION['ssl_warned']) && ! HTTPS ) {

	# Remember this page so we can return after agreeing to the warning
	$_SESSION['return'] = currentURL();

	# Don't cache the warning page
	sendNoCache();

	# Show the page
	echo loadTemplate('sslwarning.page');

	# All done!
	exit;

}


/*****************************************************************
* Plugins
* Load any site-specific plugin.
******************************************************************/
global $foundPlugin;
$plugins = explode(',', $CONFIG['plugins']);
if ($foundPlugin = in_array($URL['domain'], $plugins)) {
	include(GLYPE_ROOT.'/plugins/'.$URL['domain'].'.php');
}



/*****************************************************************
* Close session to allow simultaneous transfers
* PHP automatically prevents multiple instances of the script running
* simultaneously to avoid concurrency issues with the session.
* This may be beneficial on high traffic servers but we have the option
* to close the session and thus allow simultaneous transfers.
******************************************************************/

if ( ! $CONFIG['queue_transfers'] ) {

	session_write_close();

}


/*****************************************************************
* Check load limit. This is done now rather than earlier so we
* don't stop serving the (relatively) cheap cached files.
******************************************************************/

if (
	# Option enabled (and possible? safe_mode prevents shell_exec)
	! SAFE_MODE && $CONFIG['load_limit']

	# Ignore inline elements - when borderline on the server load, if the HTML
	# page downloads fine but the inline images, css and js are blocked, the user
	# may get very frustrated very quickly without knowing about the load issues.
	&& ! in_array($URL['extension'], array('jpg','jpeg','png','gif','css','js','ico'))
	) {

	# Do we need to find the load and regenerate the temp cache file?
	# Try to fetch the load from the temp file (~30 times faster than
	# shell_exec()) and ensure the value is accurate and not outdated,
	if( ! file_exists($file = $CONFIG['tmp_dir'] . 'load.php') || ! (include $file) || ! isset($load, $lastChecked) || $lastChecked < $_SERVER['REQUEST_TIME']-60 ) {

		$load = (float) 0;

		# Attempt to fetch the load
		if ( ($uptime = @shell_exec('uptime')) && preg_match('#load average: ([0-9.]+),#', $uptime, $tmp) ) {
			$load = (float) $tmp[1];

			# And regenerate the file
			file_put_contents($file, '<?php $load = ' . $load . '; $lastChecked = ' . $_SERVER['REQUEST_TIME'] . ';');
		}

	}

	# Load found, (or at least, should be), check against max permitted
	if ( $load > $CONFIG['load_limit'] ) {
		error('server_busy'); # Show error
	}
}


/*****************************************************************
* * * * * * * * * * Prepare the REQUEST * * * * * * * * * * * *
******************************************************************/

/*****************************************************************
* Set cURL transfer options
* These options are merely passed to cURL and our script has no further
* impact or dependence of them. See the libcurl documentation and
* http://php.net/curl_setopt for more details.
*
* The following options are required for the proxy to function or
* inherit values from our config. In short: they shouldn't need changing.
******************************************************************/

# Time to wait for connection
$toSet[CURLOPT_CONNECTTIMEOUT] = $CONFIG['connection_timeout'];

# Time to allow for entire transfer
$toSet[CURLOPT_TIMEOUT] = $CONFIG['transfer_timeout'];

# Show SSL without verifying - we almost definitely don't have an up to date CA cert
# bundle so we can't verify the certificate. See http://curl.haxx.se/docs/sslcerts.html
$toSet[CURLOPT_SSL_VERIFYPEER] = false;
$toSet[CURLOPT_SSL_VERIFYHOST] = false;

# Send an empty Expect header (avoids 100 responses)
$toSet[CURLOPT_HTTPHEADER][] = 'Expect:';

# Can we use "If-Modified-Since" to save a transfer? Server can return 304 Not Modified
if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ) {

	# How to treat the time condition : if un/modified since
	$toSet[CURLOPT_TIMECONDITION] = CURL_TIMECOND_IFMODSINCE;

	# The time value. Requires a timestamp so we can't just forward it raw
	$toSet[CURLOPT_TIMEVALUE] = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);

}

# Resume a transfer?
if ( $CONFIG['resume_transfers'] && isset($_SERVER['HTTP_RANGE']) ) {

	# And give cURL the right part
	$toSet[CURLOPT_RANGE] = substr($_SERVER['HTTP_RANGE'], 6);

}

# cURL has a max filesize option but it's not listed in the PHP manual so check it's available
if ( $CONFIG['max_filesize'] && defined('CURLOPT_MAXFILESIZE') ) {

	# Use the cURL option - should be faster than our implementation
	$toSet[CURLOPT_MAXFILESIZE] = $CONFIG['max_filesize'];

}


/*****************************************************************
* Performance options
* The values below are NOT the result of benchmarking tests. For
* optimum performance, you may want to try adjusting these values.
******************************************************************/

# DNS cache expiry time (seconds)
$toSet[CURLOPT_DNS_CACHE_TIMEOUT] = 600;

# Speed limits - aborts transfer if we're going too slowly
#$toSet[CURLOPT_LOW_SPEED_LIMIT] = 5; # speed limit in bytes per second
#$toSet[CURLOPT_LOW_SPEED_TIME] = 20; # seconds spent under the speed limit before aborting

# Number of max connections (no idea what this should be)
# $toSet[CURLOPT_MAXCONNECTS] = 100;

# Accept encoding in any format (allows compressed pages to be downloaded)
# Any bandwidth savings are likely to be minimal so better to save on load by
# downloading pages uncompressed. Use blank string for any compression or
# 'identity' to explicitly ask for uncompressed.
# $toSet[CURLOPT_ENCODING] = '';

# Undocumented in PHP manual (added 5.2.1) but allows uploads to some sites
# (e.g. imageshack) when without this option, an error occurs. Less efficient
# so probably best not to set this unless you need it.
#	 $toSet[CURLOPT_TCP_NODELAY] = true;


/*****************************************************************
* "Accept" headers
* No point sending back a file that the browser won't understand.
* Forward all the "Accept" headers. For each, check if it exists
* and if yes, add to the custom headers array.
* NB: These may cause problems if the target server provides different
* content for the same URI based on these headers and we cache the response.
******************************************************************/

# Language (geotargeting will find the location of the server -
# forwarding this header can help avoid incorrect localisation)
if ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
	$toSet[CURLOPT_HTTPHEADER][] = 'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
}

# Accepted filetypes
if ( isset($_SERVER['HTTP_ACCEPT']) ) {
	$toSet[CURLOPT_HTTPHEADER][] = 'Accept: ' . $_SERVER['HTTP_ACCEPT'];
}

# Accepted charsets
if ( isset($_SERVER['HTTP_ACCEPT_CHARSET']) ) {
	$toSet[CURLOPT_HTTPHEADER][] = 'Accept-Charset: ' . $_SERVER['HTTP_ACCEPT_CHARSET'];
}


/*****************************************************************
* Browser options
* Allows customization of a "virtual" browser via /extras/edit-browser.php
******************************************************************/

# Send user agent
if ( $_SESSION['custom_browser']['user_agent'] ) {
	$toSet[CURLOPT_USERAGENT] = $_SESSION['custom_browser']['user_agent'];
}

# Set referrer
if ( $_SESSION['custom_browser']['referrer'] == 'real' ) {

	# Automatically determine referrer
	if ( isset($_SERVER['HTTP_REFERER']) && $flag != 'norefer' && strpos($tmp = deproxyURL($_SERVER['HTTP_REFERER']), GLYPE_URL) === false ) {
		$toSet[CURLOPT_REFERER] = $tmp;
	}

} else if ( $_SESSION['custom_browser']['referrer'] ) {

	# Send custom referrer
	$toSet[CURLOPT_REFERER] = $_SESSION['custom_browser']['referrer'];

}

# Clear the norefer flag
if ( $flag == 'norefer' ) {
	$flag = '';
}


/*****************************************************************
* Authentication
******************************************************************/

# Check for stored credentials for this site
if ( isset($_SESSION['authenticate'][$URL['scheme_host']]) ) {

	# Found credentials so use them!
	$toSet[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
	$toSet[CURLOPT_USERPWD] = $_SESSION['authenticate'][$URL['scheme_host']];

}


/*****************************************************************
* Cookies
* Find the relevant cookies for this request. All cookies get sent
* to the proxy, but we only want to forward the ones that were set
* for the current domain.
*
* Cookie storage methods:
* (1) Server-side - cookies stored server-side and handled
*		(mostly) internally by cURL
* (2) Encoded	- cookies forwarded to client but encoded
* (3) Normal - cookies forwarded without encoding
******************************************************************/

# Are cookies allowed?
if ( $options['allowCookies'] ) {

	# Option (1): cookies stored server-side
	if ( $CONFIG['cookies_on_server'] ) {

		# Check cookie folder exists or try to create it
		if ( $s = checkTmpDir($CONFIG['cookies_folder'], 'Deny from all') ) {

			# Set cURL to use this as the cookie jar
			$toSet[CURLOPT_COOKIEFILE] = $toSet[CURLOPT_COOKIEJAR] = $CONFIG['cookies_folder'] . session_id();

		}

	} else if ( isset($_COOKIE[COOKIE_PREFIX]) ) {

		# Encoded or unencoded?
		if ( $CONFIG['encode_cookies'] ) {

			# Option (2): encoded cookies stored client-side
			foreach ( $_COOKIE[COOKIE_PREFIX] as $attributes => $value ) {

				# Decode cookie to [domain,path,name]
				$attributes = explode(' ', base64_decode($attributes));

				# Check successful decoding and skip if failed
				if ( ! isset($attributes[2]) ) {
					continue;
				}

				# Extract parts
				list($domain, $path, $name) = $attributes;

				# Check for a domain match and skip if no match
				if ( stripos($URL['host'], $domain) === false ) {
					continue;
				}

				# Check for match and skip to next path if fail
				if ( stripos($URL['path'], $path) !== 0 ) {
					continue;
				}

				# Multiple cookies of the same name are permitted if different paths
				# so use path AND name as the key in the temp array
				$key = $path . $name;

				# Check for existing cookie with same domain, same path and same name
				if ( isset($toSend[$key]) && $toSend[$key]['path'] == $path && $toSend[$key]['domain'] > strlen($domain) ) {

					# Conflicting cookies so ignore the one with the less complete tail match
					# (i.e. the current one)
					continue;

				}

				# Domain and path OK, decode cookie value
				$value = base64_decode($value);

				# Only send secure cookies on https connection - secure cookies marked by !SEC suffix
				# so remove the suffix
				$value = str_replace('!SEC', '', $value, $tmp);

				# And if secure cookie but not https site, do not send
				if ( $tmp && $URL['scheme'] != 'https' ) {
					continue;
				}


				# Everything checked and verified, add to $toSend for further processing later
				$toSend[$key] = array('path_size' => strlen($path), 'path' => $path, 'domain' => strlen($domain), 'send' => $name . '=' . $value);

			}

		} else {

			# Option (3): unencoded cookies stored client-side
			foreach ( $_COOKIE[COOKIE_PREFIX] as $domain => $paths ) {

				# $domain holds the domain (surprisingly) and $path is an array
				# of keys (paths) and more arrays (each child array of $path = one cookie)
				# e.g. Array('domain.com' => Array('/' => Array('cookie_name' => 'value')))

				# First check for domain match and skip to next domain if no match
				if ( stripos($URL['host'], $domain) === false ) {
					continue;
				}

				# If conflicting cookies with same name and same path,
				# send the one with the more complete tail match. To do this we
				# need to know how long each match is/was so record domain length.
				$domainSize = strlen($domain);

				# Now look at all the available paths
				foreach ( $paths as $path => $cookies ) {

					# Check for match and skip to next path if fail
					if ( stripos($URL['path'], $path) !== 0 ) {
						continue;
					}

					# In final header, cookies are ordered with most specific path
					# matches first so include the length of match in temp array
					$pathSize = strlen($path);

					# All cookies in $cookies array should be sent
					foreach ( $cookies as $name => $value ) {

						# Multiple cookies of the same name are permitted if different paths
						# so use path AND name as the key in the temp array
						$key = $path . $name;

						# Check for existing cookie with same domain, same path and same name
						if ( isset($toSend[$key]) && $toSend[$key]['path'] == $path && $toSend[$key]['domain'] > $domainSize ) {

							# Conflicting cookies so ignore the one with the less complete tail match
							# (i.e. the current one)
							continue;

						}

						# Only send secure cookies on https connection - secure cookies marked by !SEC suffix
						# so remove the suffix
						$value = str_replace('!SEC', '', $value, $tmp);

						# And if secure cookie but not https site, do not send
						if ( $tmp && $URL['scheme'] != 'https' ) {
							continue;
						}

						# Add to $toSend for further processing later
						$toSend[$key] = array('path_size' => $pathSize, 'path' => $path, 'domain' => $domainSize, 'send' => $name . '=' . $value);

					}

				}

			}

		}

		# Ensure we have found cookies
		if ( ! empty($toSend) ) {

			# Order by path specificity (as per Netscape spec)
			function compareArrays($a, $b) {
				return ( $a['path_size'] > $b['path_size'] ) ? -1 : 1;
			}

			# Apply the sort to order by path_size descending
			uasort($toSend, 'compareArrays');

			# Go through the ordered array and generate the Cookie: header
			$tmp = '';

			foreach ( $toSend as $cookie ) {
				$tmp .= $cookie['send'] . '; ';
			}

			# Give the string to cURL
			$toSet[CURLOPT_COOKIE] = $tmp;

		}

		# And clear the toSend array
		unset($toSend);

	}

}


/*****************************************************************
* Post
* Forward the post data. Usually very simple but complicated by
* multipart forms because in those cases, the raw post is not available.
******************************************************************/

if ( ! empty($_POST) ) {

	# Attempt to get raw POST from the input wrapper
	if ( ! ($tmp = file_get_contents('php://input')) ) {

		# Raw data not available (probably multipart/form-data).
		# cURL will do a multipart post if we pass an array as the
		# POSTFIELDS value but this array can only be one deep.

		# Recursively flatten array to one level deep and rename keys
		# as firstLayer[second][etc]. Also apply the input decode to all
		# array keys.
		function flattenArray($array, $prefix='') {

			# Start with empty array
			$stack = array();

			# Loop through the array to flatten
			foreach ( $array as $key => $value ) {

				# Decode the input name
				$key = inputDecode($key);

				# Determine what the new key should be - add the current key to
				# the prefix and surround in []
				$newKey = $prefix ? $prefix . '[' . $key . ']' : $key;

				if ( is_array($value) ) {

					# If it's an array, recurse and merge the returned array
					$stack = array_merge($stack, flattenArray($value, $newKey));

				} else {

					# Otherwise just add it to the current stack
					$stack[$newKey] = clean($value);

				}

			}

			# Return flattened
			return $stack;

		}

		$tmp = flattenArray($_POST);

		# Add any file uploads?
		if ( ! empty($_FILES) ) {

			# Loop through and add the files
			foreach ( $_FILES as $name => $file ) {

				# Is this an array?
				if ( is_array($file['tmp_name']) ) {

					# Flatten it - file arrays are in the slightly odd format of
					# $_FILES['layer1']['tmp_name']['layer2']['layer3,etc.'] so add
					# layer1 onto the start.
					$flattened = flattenArray(array($name => $file['tmp_name']));

					# And add all files to the post
					foreach ( $flattened as $key => $value ) {
						$tmp[$key] = '@' . $value;
					}

				} else {

					# Not another array. Check if the file uploaded successfully?
					if ( ! empty($file['error']) || empty($file['tmp_name']) ) {
						continue;
					}

					# Add to array with @ - tells cURL to upload this file
					$tmp[$name] = '@' . $file['tmp_name'];

				}

				# To do: rename the temp file to it's real name before
				# uploading it to the target? Otherwise, the target receives
				# the temp name instead of the original desired name
				# but doing this may be a security risk.

			}

		}

		}

	# Convert back to GET if required
	if ( isset($_POST['convertGET']) ) {

		# Remove convertGET from POST array and update our location
		$URL['href'] .= ( empty($URL['query']) ? '?' : '&' ) . str_replace('convertGET=1', '', $tmp);

	} else {

		# Genuine POST so set the cURL post value
		$toSet[CURLOPT_POST] = 1;
		$toSet[CURLOPT_POSTFIELDS] = $tmp;

	}

}


/*****************************************************************
* Apply pre-request code from plugins
******************************************************************/

if ( $foundPlugin && function_exists('preRequest') ) {
	preRequest();
}


/*****************************************************************
* Make the request
* This request object uses custom header/body reading functions
* so we can start processing responses on the fly - e.g. we don't
* need to wait till the whole file has downloaded before deciding
* if it needs parsing or can be sent out unchanged.
******************************************************************/

class Request {

	# Response status code
	public $status = 0;

	# Headers received and read by our callback
	public $headers = array();

	# Returned data (if saved)
	public $return;

	# Reason for aborting transfer (or empty to continue downloading)
	public $abort;

	# The error (if any) returned by curl_error()
	public $error;

	# Type of resource downloaded [html, js, css] or empty if no parsing needed
	public $parseType;

	# Automatically detect(ed) content type?
	public $sniff = false;

	# Forward cookies or not
	private $forwardCookies = false;

	# Limit filesize?
	private $limitFilesize = 0;

	# Speed limit (bytes per second)
	private $speedLimit = 0;
	
	# URL array split into pieces
	private $URL;

	# = $options from the global scope
	private $browsingOptions;

	# Options to pass to cURL
	private $curlOptions;


	# Constructor - takes the parameters and saves them
	public function __construct($curlOptions) {

		global $options, $CONFIG;

		# Set our reading callbacks
		$curlOptions[CURLOPT_HEADERFUNCTION] = array(&$this, 'readHeader');
		$curlOptions[CURLOPT_WRITEFUNCTION] = array(&$this, 'readBody');

		# Determine whether or not to forward cookies
		if ( $options['allowCookies'] && ! $CONFIG['cookies_on_server'] ) {
			$this->forwardCookies = $CONFIG['encode_cookies'] ? 'encode' : 'normal';
		}

		# Determine a filesize limit
		if ( $CONFIG['max_filesize'] ) {
			$this->limitFilesize = $CONFIG['max_filesize'];
		}
		
		# Determine speed limit
		if ( $CONFIG['download_speed_limit'] ) {
			$this->speedLimit = $CONFIG['download_speed_limit'];
		}

		# Set options
		$this->browsingOptions = $options;
		$this->curlOptions = $curlOptions;

		# Extend the PHP timeout
		if ( ! SAFE_MODE ) {
			set_time_limit($CONFIG['transfer_timeout']);
		}

		# Record debug information
		if ( DEBUG_MODE ) {
			$this->cookiesSent = isset($curlOptions[CURLOPT_COOKIE]) ? $curlOptions[CURLOPT_COOKIE] : ( isset($curlOptions[CURLOPT_COOKIEFILE]) ? 'using cookie jar' : 'none');
			$this->postSent = isset($curlOptions[CURLOPT_POSTFIELDS]) ? $curlOptions[CURLOPT_POSTFIELDS] : '';
		}

	}

	# Make the request and return the downloaded file if parsing is needed
	public function go($URL) {

		# Save options
		$this->URL = $URL;

		# Get a cURL handle
		$ch = curl_init($this->URL['href']);

		# Set the options
		curl_setopt_array($ch, $this->curlOptions);

		# Make the request
		curl_exec($ch);

		# Save any errors (but not if we caused the error by aborting!)
		if ( ! $this->abort ) {
			$this->error = curl_error($ch);
		}

		# And close the curl handle
		curl_close($ch);

		# And return the document (will be empty if no parsing needed,
		# because everything else is outputted immediately)
		return $this->return;

	}


	/*****************************************************************
	* * * * * * * * * * Manage the RESPONSE * * * * * * * * * * * *
	******************************************************************/


	/*****************************************************************
	* Read headers - receives headers line by line (cURL callback)
	******************************************************************/

	public function readHeader($handle, $header) {

		# Extract the status code (can occur more than once if 100 continue)
		if ( $this->status == 0 || ( $this->status == 100 && ! strpos($header, ':') ) ) {
			$this->status = substr($header, 9, 3);
		}

		# Attempt to extract header name and value
		$parts = explode(':', $header, 2);

		# Did it split successfully? (i.e. was there a ":" in the header?)
		if ( isset($parts[1]) ) {

			# Header names are case insensitive
			$headerType = strtolower($parts[0]);

			# And header values will have trailing newlines and prevailing spaces
			$headerValue = trim($parts[1]);

			# Set any cookies
			if ( $headerType == 'set-cookie' && $this->forwardCookies ) {

				$this->setCookie($headerValue);

			}

			# Everything else, store as associative array
			$this->headers[$headerType] = $headerValue;

			# Do we want to forward this header? First list the headers we want:
			$toForward = array('last-modified',
									 'content-disposition',
									 'content-type',
									 'content-range',
									 'content-language',
									 'expires',
									 'cache-control',
									 'pragma');

			# And check for a match before forwarding the header.
			if ( in_array($headerType, $toForward) ) {
				header($header);
			}

		} else {

			# Either first header or last 'header' (more precisely, the 2 newlines
			# that indicate end of headers)

			# No ":", so save whole header. Also check for end of headers.
			if ( ( $this->headers[] = trim($header) ) == false ) {

				# Must be end of headers so process them before reading body
				$this->processHeaders();

				# And has that processing given us any reason to abort?
				if ( $this->abort ) {
					return -1;
				}

			}

		}

		# cURL needs us to return length of data read
		return strlen($header);

	}


	/*****************************************************************
	* Process headers after all received and before body is read
	******************************************************************/

	private function processHeaders() {

		# Ensure we only run this function once
		static $runOnce;

		# Check for flag and if found, stop running function
		if ( isset($runOnce) ) {
			return;
		}

		# Set flag for next time
		$runOnce = true;

		# Send the appropriate status code
		header(' ', true, $this->status);

		# Find out if we want to abort the transfer
		switch ( true ) {

			# Redirection
			case isset($this->headers['location']):

				$this->abort = 'redirect';

				return;

			# 304 Not Modified
			case $this->status == 304:

				$this->abort = 'not_modified';

				return;

			# 401 Auth required
			case $this->status == 401:

				$this->abort = 'auth_required';

				return;

			# Error code (>=400)
			case $this->status >= 400:

				$this->abort = 'http_status_error';

				return;

			# Check for a content-length above the filesize limit
			case isset($this->headers['content-length']) && $this->limitFilesize && $this->headers['content-length'] > $this->limitFilesize:

				$this->abort = 'filesize_limit';

				return;

		}

		# Still here? No need to abort so next we determine parsing mechanism to use (if any)
		if ( isset($this->headers['content-type']) ) {

			# Define content-type to parser type relations
			$types = array(
				'text/javascript'			=> 'javascript',
				'text/ecmascript'			=> 'javascript',
				'application/javascript'	=> 'javascript',
				'application/x-javascript'	=> 'javascript',
				'application/ecmascript'	=> 'javascript',
				'application/x-ecmascript'	=> 'javascript',
				'text/livescript'			=> 'javascript',
				'text/jscript'				=> 'javascript',
				'application/xhtml+xml'		=> 'html',
				'text/html'					=> 'html',
				'text/css'					=> 'css',
			#	'text/xml'					=> 'rss',
			#	'application/rss+xml'		=> 'rss',
			#	'application/rdf+xml'		=> 'rss',
			#	'application/atom+xml'		=> 'rss',
			#	'application/xml'			=> 'rss',
			);

			# Extract mimetype from charset (if exists)
			global $charset;
			$content_type = explode(';', $this->headers['content-type'], 2);
			$mime = isset($content_type[0]) ? trim($content_type[0]) : '';
			if (isset($content_type[1])) {
				$charset = preg_match('#charset\s*=\s*([^"\'\s]*)#is', $content_type[1], $tmp, PREG_OFFSET_CAPTURE) ? $tmp[1][0] : null;
			}

			# Look for that mimetype in our array to find the parsing mechanism needed
			if ( isset($types[$mime]) ) {
				$this->parseType = $types[$mime];
			}

		} else {

			# Tell our read body function to 'sniff' the data to determine type
			$this->sniff = true;

		}

		# If no content-disposition sent, send one with the correct filename
		if ( ! isset($this->headers['content-disposition']) && $this->URL['filename'] ) {
			header('Content-Disposition: filename="' . $this->URL['filename'] . '"');
		}

		# If filesize limit exists, content-length received and we're still here, the
		# content-length is OK. If we assume the content-length is accurate (and since
		# clients [and possibly libcurl too] stop downloading after reaching the limit,
		# it's probably safe to assume that),we can save on load by not checking the
		# limit with each chunk received.
		if ( $this->limitFilesize && isset($this->headers['content-length']) ) {
			$this->limitFilesize = 0;
		}

	}


	/*****************************************************************
	* Read body - takes chunks of data (cURL callback)
	******************************************************************/

	public function readBody($handle, $data) {

		# Static var to tell us if this function has been run before
		static $first;

		# Check for set variable
		if ( ! isset($first) ) {

			# Run the pre-body code
			$this->firstBody($data);

			# Set the variable so we don't run this code again
			$first = false;

		}

		# Find length of data
		$length = strlen($data);
		
		# Limit speed to X bytes/second
		if ( $this->speedLimit ) {
			
			# Limit download speed
			# Speed		 = Amount of data / Time
			# [bytes/s] = [bytes]			/ [s]
			# We know the desired speed (defined earlier in bytes per second)
			# and we know the number of bytes we've received. Now we need to find
			# the time that it should take to receive those bytes.
			$time = $length / $this->speedLimit; # [s]

			# Convert time to microseconds and sleep for that value
			usleep(round($time * 1000000));
			
		}
		
		# Monitor length if desired
		if ( $this->limitFilesize ) {

			# Set up a static downloaded-bytes value
			static $downloadedBytes;

			if ( ! isset($downloadedBytes) ) {
				$downloadedBytes = 0;
			}

			# Add length to downloadedBytes
			$downloadedBytes += $length;

			# Is downloadedBytes over the limit?
			if ( $downloadedBytes > $this->limitFilesize ) {

				# Set the abort variable and return -1 (so cURL aborts)
				$this->abort = 'filesize_limit';
				return -1;

			}

		}

		# If parsing is required, save as $return
		if ( $this->parseType ) {

			$this->return .= $data;

		} else {
			echo $data; # No parsing so print immediately
		}

		# cURL needs us to return length of data read
		return $length;

	}




	/*****************************************************************
	* Process first chunk of data in body
	* Sniff the content if no content-type was sent and create the file
	* handle if caching this.
	******************************************************************/

	private function firstBody($data) {

		# Do we want to sniff the data? Determines if ascii or binary.
		if ( $this->sniff ) {

			# Take a sample of 100 chars chosen at random
			$length = strlen($data);
			$sample = $length < 150 ? $data : substr($data, rand(0, $length-100), 100);

			# Assume ASCII if more than 95% of bytes are "normal" text characters
			if ( strlen(preg_replace('#[^A-Z0-9\!"$%\^&*\(\)=\+\\\\|\[\]\{\};:\\\'\@\#~,\.<>/\?\-]#i', '', $sample)) > 95 ) {

				# To do: expand this to detect if html/js/css
				$this->parseType = 'html';

			}

		}

		# Now we know if parsing is required, we can forward content-length
		if ( ! $this->parseType && isset($this->headers['content-length']) ) {
			header('Content-Length: ' . $this->headers['content-length']);
		}

	}


	/*****************************************************************
	* Accept cookies - takes the value from Set-Cookie: [COOKIE STRING]
	* and forwards cookies to the client
	******************************************************************/

	private function setCookie($cookieString) {

		# The script can handle cookies following the Netscape specification
		# (or close enough!) and supports "Max-Age" from RFC2109

		# Split parts by ;
		$cookieParts = explode(';', $cookieString);

		# Process each line
		foreach ( $cookieParts as $part ) {

			# Split attribute/value pairs by =
			$pair = explode('=', $part, 2);

			# Ensure we have a second part
			$pair[1] = isset($pair[1]) ? $pair[1] : '';

			# First pair must be name/cookie value
			if ( ! isset($cookieName) ) {

				# Name is first pair item, value is second
				$cookieName = $pair[0];
				$cookieValue = $pair[1];

				# Skip rest of loop and start processing attributes
				continue;

			}

			# If still here, must be an attribute (case-insensitive so lower it)
			$pair[0] = strtolower($pair[0]);

			# And save in array
			if ( $pair[1] ) {

				# We have a attribute/value pair so save as associative
				$attr[ltrim($pair[0])] = $pair[1];

			} else {

				# Not a pair, just a value
				$attr[] = $pair[0];

			}

		}

		# All cookies need to be sent to this script (and then we choose
		# the correct cookies to forward to the client) so the extra attributes
		# (path, domain, etc.) must be stored in the cookie itself

		# Cookies stored as c[domain.com][path][cookie_name] with values of
		# cookie_value;secure;
		# If encoded, cookie name becomes c[base64_encode(domain.com path cookie_name)]

		# Find the EXPIRES date
		if ( isset($attr['expires']) ) {

			# From the "Expires" attribute (original Netscape spec)
			$expires = strtotime($attr['expires']);

		} else if ( isset($attr['max-age']) ) {

			# From the "Max-Age" attribute (RFC2109)
			$expires = $_SERVER['REQUEST_TIME']+$attr['max-age'];

		} else {

			# Default to temp cookies
			$expires = 0;

		}

		# If temp cookies, override expiry date to end of session unless time
		# is in the past since that means the cookie should be deleted
		if ( $this->browsingOptions['tempCookies'] && $expires > $_SERVER['REQUEST_TIME'] ) {
			$expires = 0;
		}

		# Find the PATH. The spec says if none found, default to the current path.
		# Certain browsers default to the the root path so we'll do the same.
		if ( ! isset($attr['path']) ) {
			$attr['path'] = '/';
		}

		# Were we sent a DOMAIN?
		if ( isset($attr['domain']) ) {

			# Ensure it's valid and we can accept this cookie
			if ( stripos($attr['domain'], $this->URL['domain']) === false ) {

				# Our current domain does not match the specified domain
				# so we reject the cookie
				return;

			}

			# Some cookies will be sent with the domain starting with . as per RFC2109
			# The . then has to be stripped off by us when doing the tail match to determine
			# which cookies to send since ".glype.com" should match "glype.com". It's more
			# efficient to do any manipulations while forwarding cookies than on every request
			if ( $attr['domain'][0] == '.' ) {
				$attr['domain'] = substr($attr['domain'], 1);
			}

		} else {

			# No domain sent so use current domain
			$attr['domain'] = $this->URL['domain'];

		}

		# Check for SECURE cookie
		$sentSecure = in_array('secure', $attr);

		# Append "[SEC]" to cookie value if we should only forward to secure connections
		if ( $sentSecure ) {
			$cookieValue .= '!SEC';
		}

		# If we're on HTTPS, we can also send this cookie back as secure
		$secure = HTTPS && $sentSecure;

		# If the PHP version is recent enough, we can also forward the httponly flag
		$httponly = in_array('httponly', $attr) && version_compare(PHP_VERSION,'5.2.0','>=') ? true : false;

		# Prepare cookie name/value to save as
		$name = COOKIE_PREFIX . '[' . $attr['domain'] . '][' . $attr['path'] . '][' . inputEncode($cookieName) . ']';
		$value = $cookieValue;

		# Add encodings
		if ( $this->forwardCookies == 'encode' ) {

			$name = COOKIE_PREFIX . '[' . urlencode(base64_encode($attr['domain'] . ' ' . $attr['path'] . ' ' . urlencode($cookieName))) . ']';
			$value = base64_encode($value);

		}

		# Send cookie ...
		if ( $httponly ) {

			# ... with httponly flag
			setcookie($name, $value, $expires, '/', '', $secure, true);

		} else {

			# ... without httponly flag
			setcookie($name, $value, $expires, '/', '', $secure);

		}

		# And log if in debug mode
		if ( DEBUG_MODE ) {

			$this->cookiesReceived[] = array('name'			=> $cookieName,
														'value'			=> $cookieValue,
														'attributes'	=> $attr);

		}

	}

}


/*****************************************************************
* Execute the request
******************************************************************/

# Initiate cURL wrapper request object with our cURL options
$fetch = new Request($toSet);

# And make the request
$document = $fetch->go($URL);


/*****************************************************************
* Handle aborted transfers
******************************************************************/

if ( $fetch->abort ) {

	switch ( $fetch->abort ) {

		# Do a redirection
		case 'redirect':

			# Proxy the location
			$location = proxyURL($fetch->headers['location'], $flag);

			# Do not redirect in debug mode
			if ( DEBUG_MODE ) {
				$fetch->redirected = '<a href="' . $location . '">' . $fetch->headers['location'] . '</a>';
				break;
			}

			# Go there
			header('Location: ' . $location, true, $fetch->status);
			exit;


		# Send back a 304 Not modified and stop running the script
		case 'not_modified':
			header("HTTP/1.1 304 Not Modified", true, 304);
			exit;


		# 401 Authentication (HTTP authentication hooks not available in all PHP versions
		# so we have to use our method)
		case 'auth_required':

			# Ensure we have some means of authenticating and extract details about the type of authentication
			if ( ! isset($fetch->headers['www-authenticate']) ) {
				break;
			}

			# Realm to display to the user
			$realm = preg_match('#\brealm="([^"]*)"#i', $fetch->headers['www-authenticate'], $tmp) ? $tmp[1] : '';

			# Prevent caching
			sendNoCache();

			# Prepare template variables (session may be closed at this point so send via form)
			$tmp = array('site'	 => $URL['scheme_host'],
							 'realm'	 => $realm,
							 'return' => currentURL());

			# Show our form and quit
			echo loadTemplate('authenticate.page', $tmp);
			exit;


		# File request above filesize limit
		case 'filesize_limit':

			# If already sent some of the file, we can't display an error
			# so just stop running
			if ( ! $fetch->parseType ) {
				exit;
			}
		
			# Send to error page with filesize limit expressed in MB
			error('file_too_large', round($CONFIG['max_filesize']/1024/1024, 3));
			exit;


		# >=400 response code (some sort of HTTP error)
		case 'http_status_error':

			# Provide a friendly message
			$explain = isset($httpErrors[$fetch->status]) ? $httpErrors[$fetch->status] : '';

			# Simply forward the error with details
			error('http_error', $fetch->status, trim(substr($fetch->headers[0], 12)), $explain);
			exit;


		# Unknown (shouldn't happen)
		default:
			error('cURL::$abort (' . $fetch->abort .')');
	}

}

# Any cURL errors?
if ( $fetch->error ) {

	error('curl_error', $fetch->error);

}


/*****************************************************************
* Transfer finished and errors handle. Process the file.
******************************************************************/

# Is this AJAX? If so, don't cache, log or parse.
# Also, assume ajax if return is VERY short.
if ( $flag == 'ajax' || ( $fetch->parseType && strlen($document) < 10 ) ) {

	# Print if not already printed
	if ( $fetch->parseType ) {
		echo $document;
	}

	# And exit
	exit;
}

# Do we want to parse the file?
if ( $fetch->parseType ) {

	/*****************************************************************
	* Apply the relevant parsing methods to the document
	******************************************************************/

	# Decode gzip compressed content
	if (isset($fetch->headers['content-encoding']) && $fetch->headers['content-encoding']=='gzip') {
		if (function_exists('gzinflate')) {
			unset($fetch->headers['content-encoding']);
			$document=gzinflate(substr($document,10,-8));
		}
	}

	# Apply preparsing from plugins
	if ( $foundPlugin && function_exists('preParse') ) {
		$document = preParse($document, $fetch->parseType);
	}

	# Load the main parser
	require GLYPE_ROOT . '/includes/parser.php';
	
	# Create new instance, passing in the options that affect parsing
	$parser = new parser($options, $jsFlags);

	# Method of parsing depends on $parseType
	switch ( $fetch->parseType ) {

		# HTML document
		case 'html':

			# Do we want to insert our own code into the document?
			$inject = 
			$footer = 
			$insert = false;

			# Mini-form only if NOT frame or sniffed
			if ( $flag != 'frame' && $fetch->sniff == false ) {

				# Showing the mini-form?
				if ( $options['showForm'] ) {
				
					$toShow = array();

					# Prepare the options
					foreach ( $CONFIG['options'] as $name => $details ) {

						# Ignore if forced
						if ( ! empty($details['force']) )  {
							continue;
						}

						# Add to array
						$toShow[] = array(
							'name'		=> $name,
							'title'		=> $details['title'],
							'checked'	=> $options[$name] ? ' checked="checked" ' : ''
						);

					}

					# Prepare variables to pass to template
					if ($options['encodePage']) {
						$vars['url'] = ''; # Currently visited URL
					} else {
						$vars['url'] = $URL['href']; # Currently visited URL
					}
					$vars['toShow']	= $toShow; # Options
					$vars['return']	= rawurlencode(currentURL()); # Return URL (for clearcookies) (i.e. current URL proxied)
					$vars['proxy']	= GLYPE_URL; # Base URL for proxy directory

					# Load the template
					$insert = loadTemplate('framedForm.inc', $vars);
					
					# Wrap in enable/disble override to prevent the overriden functions
					# affecting anything in the mini-form (like ad codes)
					if ( $CONFIG['override_javascript'] ) {
						$insert = '<script type="text/javascript">disableOverride();</script>'
								  . $insert
								  . '<script type="text/javascript">enableOverride();</script>';
					}
					
				}

				# And load the footer
				$footer = $CONFIG['footer_include'];

			}

			# Inject javascript unless sniffed
			if ( $fetch->sniff == false ) {
				$inject = true;
			}

			# Run through HTML parser
			$document = $parser->HTMLDocument($document, $insert, $inject, $footer);

			break;


		# CSS file
		case 'css':

			# Run through CSS parser
			$document = $parser->CSS($document);

			break;


		# Javascript file
		case 'javascript':

			# Run through javascript parser
			$document = $parser->JS($document);

			break;

	}

	# Apply postparsing from plugins
	if ( $foundPlugin && function_exists('postParse') ) {
		$document = postParse($document, $fetch->parseType);
	}

	# Send output
	if ( ! DEBUG_MODE ) {

		# Do we want to gzip this? Yes, if all of the following are true:
		#	  - gzip option enabled
		#	  - client supports gzip
		#	  - zlib extension loaded
		#	  - output compression not automated
		if ( $CONFIG['gzip_return'] && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false && extension_loaded('zlib') && ! ini_get('zlib.output_compression') ) {

			# Send compressed (using level 3 compression - can be adjusted
			# to give smaller/larger files but will take longer/shorter time!)
			header('Content-Encoding: gzip');
			echo gzencode($document, 3);

		} else {

			# Send uncompressed
			echo $document;

		}

	}

}

if ( DEBUG_MODE ) {
	# Just dump the $fetch object in DEBUG_MODE
	$fetch->return = $document;
	echo '<pre>', print_r($fetch, 1), '</pre>';
}


/*****************************************************************
* Log the request
******************************************************************/

# Do we want to log? Check we want to log this type of request.
if ( $CONFIG['enable_logging'] && ( $CONFIG['log_all'] || $fetch->parseType == 'html' ) ) {

	# Is the log directory writable?
	if ( checkTmpDir($CONFIG['logging_destination'], 'Deny from all') ) {

		# Filename to save as
		$file = $CONFIG['logging_destination'] . '/' . date('Y-m-d') . '.log';

		# Line to write
		$write = str_pad($_SERVER['REMOTE_ADDR'] . ', ' , 17) . date('d/M/Y:H:i:s O') . ', ' . $URL['href'] . "\r\n";

		# Do it
		file_put_contents($file, $write, FILE_APPEND);

	}

}
