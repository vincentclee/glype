<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

$options['stripJS'] = false;
$options['allowCookies'] = true;

function preRequest() {
	global $URL;
	if ($URL['host'] != 'm.facebook.com') {
		$URL['host'] = preg_replace('/((www\.)?facebook\.com)/', 'm.facebook.com', $URL['host']);
		$URL['href'] = preg_replace('/\/\/((www\.)?facebook\.com)/', '//m.facebook.com', $URL['href']);
	}
}
