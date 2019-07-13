<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
* This file is the webroot index.php and displays the proxy form.
* Nothing too complicated - decode any errors, render relevant
* options taking into account forced/defaults.
******************************************************************/

/*****************************************************************
* Initialise the application
******************************************************************/

# Load global file
require 'includes/init.php';

# Send our no-cache headers
sendNoCache();

# Start the output buffer
ob_start('render');

# Flag valid entry point for hotlink protection
if (!isset($_GET['e']) || $_GET['e']!='no_hotlink') {
	$_SESSION['no_hotlink'] = true;
}

/*****************************************************************
* Determine the options to display
******************************************************************/

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


/*****************************************************************
* Look for any error information in the URL.
******************************************************************/

# Check for error
if ( isset($_GET['e']) && isset($phrases[$_GET['e']]) ) {

	# Look for additional arguments (to be used as variables in the error message)
	$args = isset($_GET['p']) ? @unserialize(base64_decode($_GET['p'])) : array();
	
	# If we failed to decode the arguments, reset to a blank array
	if ( ! is_array($args) ) {
		$args = array();
	}
	
	# Did we find any args to pass?
	if ( $args ) {
	
		# Add phrase to start of array (to give to call_user_func_array())
		$args = array_merge( (array) $phrases[$_GET['e']], $args);
		$error = call_user_func_array('sprintf',$args);
	
	} else {
	
		# Just a simple print
		$error = $phrases[$_GET['e']];
		
	}
	
	# Finally add it to the $themeReplace array to get it in there
	$themeReplace['error'] = '<div id="error">' . $error . '</div>';
	
	# And a link to try again?
	if ( ! empty($_GET['return']) ) {
		$themeReplace['error'] .= '<p style="text-align:right">[<a href="' . htmlentities($_GET['return']) . '">Reload ' . htmlentities(deproxyURL($_GET['return'])) . '</a>]</p>';
	}
	
}

/*****************************************************************
* Check PHP version
******************************************************************/

if ( version_compare(PHP_VERSION, 5) < 0 ) {
	$themeReplace['error'] = '<div id="error">You need PHP 5 to run this script. You are currently running ' . PHP_VERSION . '</div>';
}

if (count($adminDetails)===0) {
	header("HTTP/1.1 302 Found"); header("Location: admin.php"); exit;
}


/*****************************************************************
* Maintenance - check if we want to do anything now
******************************************************************/

if ( $CONFIG['tmp_cleanup_interval'] ) {
	
	# Do we have a next run time?
	if ( file_exists($file = $CONFIG['tmp_dir'] . 'cron.php') ) {
	
		# Load the next runtime
		include $file;
		
		# Compare to current time
		$runCleanup = $nextRun <= $_SERVER['REQUEST_TIME'];
		
	} else {
	
		# No runtime stored, assume first request with the cleanup option
		# enabled so run now.
		$runCleanup = true;
				
	}
	
	# This might take a while so do it after user has received
	# page and cut connection.
	if ( ! empty($runCleanup) ) {
		header('Connection: Close');
	}

}


/*****************************************************************
* All done, show the page
******************************************************************/

# Throw all template variables into an array to pass to the template
$vars['toShow'] = $toShow;

echo loadTemplate('main', $vars);

# And flush buffer
ob_end_flush();


/*****************************************************************
* Now actually do the maintenance if desired
******************************************************************/

if ( ! empty($runCleanup) ) {

	# Don't stop
	ignore_user_abort(true);

	# Update the time file
	file_put_contents($file, '<?php $nextRun = ' . ( $_SERVER['REQUEST_TIME'] + round(3600 * $CONFIG['tmp_cleanup_interval']) ) . ';');
	
	# remove old cookie files
	if ( is_dir($CONFIG['cookies_folder']) && ( $handle = opendir($CONFIG['cookies_folder']) ) ) {
	
		# Cut off for "active" files (24 hours)
		$cutOff = $_SERVER['REQUEST_TIME']-86400;
	
		# Read every file in the cookies dir
		while ( ( $file = readdir($handle) ) !== false ) {
		
			# Skip dot files
			if ( $file[0] == '.' ) {
				continue;
			}
			
			$path = $CONFIG['cookies_folder'] . $file;
			
			# Check it's not being used
			if ( filemtime($path) > $cutOff ) {
				continue;
			}
			
			# Delete it
			unlink($path);
		
		}
		
		# And close handle
		closedir($handle);
	
	}
	
	# remove logs
	if ( $CONFIG['tmp_cleanup_logs'] && is_dir($CONFIG['logging_destination']) && ( $handle = opendir($CONFIG['logging_destination']) ) ) {
	
		# Cut off for deletion of old logs
		$cutOff = $_SERVER['REQUEST_TIME'] - ($CONFIG['tmp_cleanup_logs'] * 86400);
	
		# Read every file in the logs dir
		while ( ( $file = readdir($handle) ) !== false ) {
		
			# Skip dot files
			if ( $file[0] == '.' ) {
				continue;
			}
			
			$path = $CONFIG['logging_destination'] . $file;
			
			# Check it's not being used
			if ( filemtime($path) > $cutOff ) {
				continue;
			}
			
			# Delete it
			unlink($path);
		
		}
		
		# And close handle
		closedir($handle);
	
	}
	
	# Finished.

}