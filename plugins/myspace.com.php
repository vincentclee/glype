<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2016 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************
* Plugin: MySpace
* Description:
*	  Fixes a minor JavaScript issue where code that 'looks' like
*	  it needs parsing actually doesn't.
******************************************************************/

/*****************************************************************
* Pre-parsing applied BEFORE main proxy parser.
******************************************************************/

function preParse($input, $type) {

	switch ( $type ) {
	
		// Apply changes to HTML documents
		case 'html':
		
			// Javascript fix - break up the string into 2 pieces so we don't
			// confuse the main proxy parser with a ".innerHTML = " string.
			$input = str_replace('"invalidLogin.innerHTML = \""', '"invalidLogin.in"+"nerHTML = \""', $input);
			
			// Reroute AJAX requests
			$insert = <<<OUT
				<script type="text/javascript">
				XMLHttpRequest.prototype.open = function(method,uri,async) {
					return this.base_open(method, parseURL(uri.replace('localhost', 'www.myspace.com'), 'ajax'), async);
				};
				</script>
OUT;
			$input = str_replace('</head>', $insert . '</head>', $input);
			
			break;
		
	}
	
	// Return changed
	return $input;

}
