<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2016 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

function preParse($html,$type) {
	if ( stripos($html,'JavaScript required to sign in') ) {
		header("Location: " . proxyURL('https://mid.live.com/si/login.aspx'));
		exit;
	}
    return $html;
}
