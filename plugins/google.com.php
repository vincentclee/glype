<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2014 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

function preParse($html,$type) {
	if (stripos($html,'loadingError')) {
		header("Location: ".proxyURL('http://mail.google.com/mail/?ui=html'));
		exit;
	}
	return $html;
}
function postParse(&$in,$type) {
	$in = preg_replace('# style="padding-top:\d+px"#', '', $in);
	return $in;
}
