<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

function preRequest() {
	global $toSet;
	$toSet[CURLOPT_USERAGENT] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 6.1)';
}
