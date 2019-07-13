<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2016 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
*
* BY USING THIS DISCLAIMER, YOU ACKNOWLEDGE AND AGREE THAT ALL INFORMATION
* CONTAINED HEREIN DOES NOT CONSTITUTE LEGAL ADVICE OF ANY KIND OR NATURE.
* PLEASE CONSULT WITH LEGAL COUNSEL BEFORE USING THIS DISCLAIMER.
*
/*****************************************************************
* Initialize glype
******************************************************************/

require 'includes/init.php';


/*****************************************************************
* Create content
******************************************************************/

$content = <<<OUT
	<h2 class="first">Disclaimer</h2>
	<p>This service is provided as is, without warranty of any kind. Use of this service is entirely at your own risk. We cannot take responsibility for any direct or indirect damages resulting from the use of this service.</p>
	<p>The service allows indirect browsing of external, third-party websites. We are not responsible for the content on any external websites that may be accessible through our service. A website viewed through our service is in no way owned by or associated with this website.</p>
	<p>The term "indirect browsing" refers to the server which you connect to. During "direct" browsing, you connect to the server which provides the resource you are requesting. During "indirect" browsing, you connect to our server. Our script downloads the requested resource and forwards it to you.</p>
	<p>Any resource (such as web pages, images, files) downloaded through our service may be modified. This may include, but is not limited to, editing URLs so that any resources referenced by the target resource are also downloaded indirectly. The accuracy and reliablity of this process is not guaranteed. The resource which you receive may not be an accurate representation of the resource requested.</p>
	<p>A side-effect of indirect browsing may be anonymity. By connecting to our server instead of the target server, the target server does not see your IP address. However, we do not guarantee our service will be truly anonymous. The downloaded resource may reference other resources which your browser may automatically download. The service attempts to reroute all such requests through our server but may not be entirely successful. A single direct request will compromise your anonymity.</p>
	<p>This service may download a resource over a secure connection but this may be sent back to you over an unsecure connection. Do not enter confidential information unless you are on a secure connection to our server.</p>
OUT;


/*****************************************************************
* Send content wrapped in our theme
******************************************************************/

echo replaceContent($content);
