<?
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

define('mobilemail',proxyURL('http://m.yahoo.com/mail'));
if(stripos($toLoad,'mail.yahoo.com')){header('Location: '.mobilemail);exit;}
function preParse($html,$type){
	if($type=='html') {
		$html = preg_replace('#r/(m6|lk|l6|m7|m2|l4)#', mobilemail, $html);
	}
	return $html;
}
