<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
* This page displays a list of cookies that have been forwarded
* to the user and allows individual cookies to be deleted.
******************************************************************/


/*****************************************************************
* Initialize glype
******************************************************************/

require 'includes/init.php';

// Stop caching
sendNoCache();

// Start buffering
ob_start();


/*****************************************************************
* Create content
******************************************************************/

echo <<<OUT
	<h2 class="first">Manage Cookies</h2>
	<p>You can view and delete cookies set on your computer by sites accessed through our service. Your cookies are listed below:</p>
	<form action="includes/process.php?action=cookies" method="post">
		<table cellpadding="2" cellspacing="0" align="center">
			<tr>
				<th width="33%">Website</th>
				<th width="33%">Name</th>
				<th width="33%">Value</th>
				<th>&nbsp;</th>
			</tr>
		
OUT;


/*****************************************************************
* Find cookies
******************************************************************/

// Server side storage
if ( $CONFIG['cookies_on_server'] ) {

	// Check cookie file exists
	if ( file_exists($cookieFile = $CONFIG['cookies_folder'] . session_id()) ) {
	
		// Load into array
		if ( $cookieLine = file($cookieFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ) {
	
			// Process line by line
			foreach ( $cookieLine as $line ) {
				
				// Comment line?
				if ( ! isset($line[0]) || $line[0] == '#' ) {
					continue;
				}
				
				// Clear newlines
				$line = rtrim($line);
				
				// Split by tab
				$details = explode(' ', $line);
				
				// Check valid split, expecting 7 items
				if ( count($details) != 7 ) {
					continue;
				}
				
				// Save in array(domain, path, name value)
				$showCookies[] = array($details[0], $details[2], $details[5], $details[6]);
				
			}
			
		}
	
	}

} else if ( isset($_COOKIE[COOKIE_PREFIX]) ) {

	// Cookies on client

	// Encoded or unencoded?
	if ( $CONFIG['encode_cookies'] ) {

		// Encoded cookies stored client-side
		foreach ( $_COOKIE[COOKIE_PREFIX] as $attributes => $value ) {

			// Decode cookie to [domain,path,name]
			$attributes = explode(' ', base64_decode($attributes));
		
			// Check successful decoding and skip if failed
			if ( ! isset($attributes[2]) ) {
				continue;
			}
			
			// Extract parts
			list($domain, $path, $name) = $attributes;
			
			// Decode cookie value
			$value = base64_decode($value);
			
			// Secure cookies marked by !SEC suffix so remove the suffix
			$value = str_replace('!SEC', '', $value);
			
			// Add cookie
			$showCookies[] = array($domain, $path, $name, $value);
			
		}
			
	} else {
	
		// Unencoded cookies stored client-side
		foreach ( $_COOKIE[COOKIE_PREFIX] as $domain => $paths ) {

			// $domain holds the domain (surprisingly) and $path is an array
			// of keys (paths) and more arrays (each child array of $path = one cookie)
			// e.g. Array('domain.com' => Array('/' => Array('cookie_name' => 'value')))

			foreach ( $paths as $path => $cookies ) {

				foreach ( $cookies as $name => $value ) {

					// Secure cookies marked by !SEC suffix so remove the suffix
					$value = str_replace('!SEC', '', $value);
					
					// Add cookie
					$showCookies[] = array($domain, $path, $name, $value);

				}
				
			}
		
		}
		
	}
	
}


/*****************************************************************
* Print cookies
******************************************************************/

// Any to print?
if ( empty($showCookies) ) {

	echo <<<OUT
		<tr>
			<td colspan="4" align="center">No cookies found</td>
		</tr>
		
OUT;

} else {

	// Loop through and print them
	foreach ( $showCookies as $id => $cookie ) {
		
		// Join domain & path to create "website"
		$website = $cookie[0] . ( $cookie[1] == '/' ? '' : $cookie[1] );
		
		// Cookie name
		$name = htmlentities($cookie[2]);
		 
		// Get cookie value
		$value = $cookie[3];
		
		// Truncate value to avoid stretching page
		if ( strlen($value) > 35 ) {
		
			// Create a row ID
			$rowID = 'cookieRow' . $id;
			
			// Wrap the long value and escape ' so we can use it in javascript
			$wrapped = str_replace("'", "\'", wordwrap($cookie[3], 30, ' ', true));
		
			// Truncate the string
			$truncated = substr($value, 0, 30);
			
			// Replace the value with a shorten version that expands onclick
			$value  = <<<OUT
			<span id="{$rowID}">{$truncated}<a style="cursor:pointer;" onclick="document.getElementById('{$rowID}').innerHTML='{$wrapped}';">...</a></span>
OUT;
		}
	
		echo <<<OUT
			<tr>
				<td>{$website}</td>
				<td>{$name}</td>
				<td>{$value}</td>
				<td><input type="checkbox" name="delete[]" value="{$cookie[0]}|{$cookie[1]}|{$name}"></td>
			</tr>
			
OUT;
	}
	
}


/*****************************************************************
* Finish page
******************************************************************/

echo <<<OUT
			<tr>
				<th colspan="3" align="right"><input type="submit" value="Delete"></th>
				<th><input type="checkbox" name="checkall"  onclick="selectAll(this)"></th>
			</tr>
		</table>
	</form>
	<script type="text/javascript">
		function selectAll(checkbox) {
			var theForm = checkbox.form;
			for(var z=0; z<theForm.length;z++){
				if(theForm[z].type == 'checkbox' && theForm[z].name != 'checkall'){
					theForm[z].checked = checkbox.checked;
				}
			}
		}
	</script>
OUT;


/*****************************************************************
* Send content wrapped in our theme
******************************************************************/

// Get buffer
$content = ob_get_contents();

// Clear buffer
ob_end_clean();

// Print content wrapped in theme
echo replaceContent($content);
