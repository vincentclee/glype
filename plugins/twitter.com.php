<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2016 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

$options['stripJS'] = true;

#function preRequest() {
#	global $toSet,$URL;
#	header('Content-Type: text/plain');
#	if ($URL['host'] != 'mobile.twitter.com') {
#		$URL['host'] = 'mobile.twitter.com';
#		$URL['href'] = preg_replace('#^[a-z]+://[^/]+#i', 'https://mobile.twitter.com', $URL['href']);
#	}
#}
