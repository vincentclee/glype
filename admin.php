<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
* This is a stand-alone admin control panel for the Glype software.
******************************************************************/

/*****************************************************************
* Configuration - edit this section (if you want!)
******************************************************************/

# Path to the /includes/settings.php file. Change if you want to move
# this admin script out of the glype directory. You can use a relative
# or absolute path to the file.
define('ADMIN_GLYPE_SETTINGS', 'includes/settings.php');

# How long to keep an inactive admin session open for? After this
# period of inactivity, an admin session is invalidated and you
# must log in again. [seconds]
define('ADMIN_TIMEOUT', 60*60);

# Log viewer limit for collated stats. Limits "most viewed" to
# the top X websites.
define('ADMIN_STATS_LIMIT', 50);

# End of configuration.


/*****************************************************************
* Initialize admin script
******************************************************************/

# Setup error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

# Define a path to us
define('ADMIN_URI', $_SERVER['PHP_SELF']);

# Define the current admin version
define('ADMIN_VERSION', '1.4.4');

# Start buffering
ob_start();

# Set up equivalents to glype's /includes/init.php constants
# that might be available in the settings file
define('GLYPE_URL',	pathToURL(dirname(ADMIN_GLYPE_SETTINGS) . '/..'));
define('GLYPE_ROOT', str_replace('\\', '/', dirname(dirname(realpath(ADMIN_GLYPE_SETTINGS)))));

# And backwards compatibility (will be removed at some point)
function findURL() { return GLYPE_URL; }
define('LCNSE_KEY', '');
define('proxyPATH', GLYPE_ROOT . '/');

# Load current settings
$settingsLoaded = file_exists(ADMIN_GLYPE_SETTINGS) && (@include ADMIN_GLYPE_SETTINGS);

# Extract the "action" from the query string
$action = isset($_SERVER['QUERY_STRING']) && preg_match('#^([a-z-]+)#', $_SERVER['QUERY_STRING'], $tmp) ? $tmp[1] : '';

$cache_bust=filemtime(__FILE__)+filemtime(ADMIN_GLYPE_SETTINGS);

# SHORTCUTS
# Make a newline
define('NL', "\r\n");

/*****************************************************************
* IMAGES (ugly but keeps it in a single file)
******************************************************************/

if ( isset($_GET['image']) ) {

	# Send image function
	function sendImage($str) {
		header('Content-Type: image/gif');
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s", filemtime(__FILE__)) . 'GMT');
		header('Expires: ' . gmdate("D, d M Y H:i:s", filemtime(__FILE__) + (60*60)) . 'GMT');
		echo base64_decode($str);
		exit;
	}

	switch ( $_GET['image'] ) {
		case 'bg.gif':
			sendImage('R0lGODlhkAMMAMQAAP////7+/v3+/fz9/Pr7+vn6+fj6+Pj5+PX39fL08u/x7+ru6ubq5uDm4OHm4dzi3Njf2NTc1NHZ0c7XzsnTycfRxwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAACQAwwAAAX/4CACZGmeaKoC4oEkysI0DxRJU1WsfO//wKBwSCwaj8ikcslsOp/QqHRKrVqv2KxWWKBIIpCHg7FQJBAGwwgrWldbr9isdsvttvi8fs/v+/+AgYKDhIWGSV1fYWNlZ2luVm0DV3AwMjQ2ODqHnJ2en6ChoqOkpaaAiWBiZGZoapNsbZQDLpZzmXanuru8vb6/wMHCo6mLrI6vWZKztXKYdZvD0tPU1dbX2NmCxauNrpBvspG0cZd0mnfa6uvs7e7v8KTcjK2PsLPgUpXO57nx/wADChxIsGCJece+3RuXL8o+c7iiGZxIsaLFixj/IPRmT5m4cM0gQkuXsaTJkyhTO6bcWC9ZrIZQHt4aqbKmzZs4c1ZjiQymw49UZD5Dp7Oo0aNIk/bhqdCjTydC+0lUSrWq1atYVTDtyCYEADs=');
			break;
		case 'bullet_green.gif':
			sendImage('R0lGODlhEAAQAMQAAFOYS////6LUoJW4kI/Bi+nv6F28V5TSlLTcs5TMkYTKgp3VmlelUJvKluv26r/hv4zOhJ/cnJnMmV7CWev461WcTaPUoZa6kZrWlr3etYrMiQAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAQABAAAAUzYCCOZGmeaKqu7ElF07Q4aoQ9TyKp03NoFobKgNAoJBXVIiGQEC4qR6MCGBRa2Kx2qwoBADs=');
			break;
		case 'bullet_grey.gif':
			sendImage('R0lGODlhEAAQALMAAHR0dLW1te/v76Wlpby8vI2NjcfHx62trXp6eszMzP///wAAAAAAAAAAAAAAAAAAACH5BAEHAAoALAAAAAAQABAAAAQtUMlJq704682vIEURCBoRJMkRaEUSDATCGscQAFpwmMOgCQcAYEDqGI/IZCYCADs=');
			break;
		case 'button.gif':
			sendImage('R0lGODlhCgAoAKIAAOno6Ovq6/f39////+vp6vPx8uzr6u/v7yH5BAAHAP8ALAAAAAAKACgAAAMtOLos/jDKSWssOOvNOz5gKI6jYZ5oqq4m4b4EIM90bd94ru987//AoHBILBoTADs=');
			break;
		case 'content_bg.gif':
			sendImage('R0lGODlhCAAyALMAAP////v9/fj7+/X6+vH4+O729ur09Ofy8uPw8N/v79zt7djr6wAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAIADIAAAQ8EMgpl7046827/5oijmJiniairurhvq4hz3Jh3zah7/rg/z6BcCgMGI9GinLJbDqf0Kh0Sq1ar9islhoBADs=');
			break;
		case 'footer.gif':
			sendImage('R0lGODlhkAMwAOYAAP////7+/v39/f3+/fz9/Pz8/Pv7+/v8+/r7+vn6+fj6+Pf59/j5+Pb49vf49/b39vX39fT29PL18vP18/L08vH08fHz8e/y7/Dy8O7x7u/x7+3w7e3x7ezv7Ovv6+vu6+ru6unt6ejs6Ofr5+br5ubq5uTp5OTo5OPo4+Hn4eLn4uDm4OHm4d/l397k3t3j3dvi29zi3Nvh29rh2tng2drg2tjf2Nnf2dfe19ff19bd1tbe1tXd1dXc1dTc1NPb09La0tDZ0NHZ0c/Yz87Xzs3WzczWzMzVzMrUysvUy8nTycrTysjSyMnSycfRxwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAACQAzAAAAf/gASCAISFhoeIiYoAggwQFBogJSsxNj5CRE4Ji5ydnp+goaKjpKWmp6ipqqusra6vsLGys7S1tre4ubqiCUpCPjYxLCUgGhQQCgqDuILLtY2PkZOVl5mbu9jZ2tvc3d7f4OHi4+Tl5qm9v8HDxcfJzrbNBLfQkJKUlpia5/z9/v8AAwocSLCgQXDpgAkjZgyZsnnMmtEj4MjetHzWDmrcyLGjx48gQ4ocmHAdQ3cPc8mbWFEavmr7RsqcSbOmzZs4c4orubCdQ3jPJMajGO0eNX3XdCpdyrSp06dQCfJk1/AdxIlAZdVzeTRj1K9gw4odS7Zsoaknf14dmjXWVqMY/2OanUu3rt27eL+h9WlVpdCgLeHCTJq3sOHDiBMn3ls1ZcS2sN5eHKy4suXLmDPXZIwSstu/tCS/RKq5tOnTqFN346zWr2dXorvKVU27tu3buBWx7vt47azYcQnnHk68uPG8ux1j9a2VqMXRXo9Ln069us7kr1+tHBp4Mmnr4MOLHy8QO/PfoNF3hz6bvPv38OPbMu/6fGTnXIPL38+/v/9Q9PXGUlHeRfffgQgm6F6Ayw34nGzCKSjhhBTexiBb9mmHn2DfVejhhyBedmFQ2bECHGUhpqjiinONGFp6za0HIYs01mjjUi6iV+IqJ3Z4449ABrlRjs3tqEqPBgqp5P+STJJD5GdGpoJke01WaeWVtzx5X5SoTBkhlmCGKaYpWmrI5SlejqnmmmxyUiZsMH4mo35t1mnnmG+2sh1gBLL35Z2ABvpjnibGed+cKAqq6KI0EsqjoRoi6iOjlFY6oaNHQgqnpEla6umn+2EqpaZ6blgglaCmqip4onZJaqGcorrqrLQO1yqarz4a65+19urrabeasueLu/5q7LGmBVvKsOr12RURSCDBxLROVGvttdhmq+223Hbr7bfghivuuOSWa+656Kar7rrstuvuu/DGK++89NZr77345quvt9P2e8QR6sSwQlq8NcgdBBJo8EEJLLxwAw9ADDFEEhRT3O//xRhnrPHGHHfs8ccghyzyyCSXbPLJKKes8sost+zyyzDHLPPMNNds880456xzyRQPAQQPN7zAAgkfXCABBAwwcKarSy9LQDISXODBCCq8QIMOPwQRhBFcG7HE12CHLfbYZJdt9tlop6322my37fbbcMct99x012333XjnrffefPft99+ABy744IS7TYQQQuhAwwsqjOCB0Q88YIABAdSHy+QNTIBBByKg4IIMNvQABBBEEPHv6ainrvrqrLfu+uuwxy777LTXbvvtuOeu++689+7778AHL/zwxBdv/PHIJ6/88rhr3YMNMriAgggdYDBBAw1MXrmAtxxwAPYWbBCC/wkrWE2DDz5oXfr67Lfv/vvwxy///PTXb//9+Oev//789+///wAMoAAHSMACGvCACEygAhfIwAY68IH8Gx0OcPCCFZggBBuwQAQikIAECEAAluveARwQgQpkQBIqcIELaLCDHfzgB4iLoQxnSMMa2vCGOMyhDnfIwx768IdADKIQh0jEIhrxiEhMohKXyMQmOvGJUIyiFKdIxSoKsQc9oEEMYqACYmSgAhFwgAO8tz2D2cJ7C1hA1DwgAhOwwAUwgIENbMADHqDvjnjMox73yMc++vGPgAykIAdJyEIa8pCITKQiF8nIRjrykZCMpCQnSclKWvKSmMykJjd5yDlukf8FJhDB446WNDKG0BYFKEDSJjAB8ZGABG/8nAxAN8cc2PKWuMylLnfJy1768pfADKYwh0nMYhrzmMhMpjKXycxmOvOZ0IymNKdJzWpa85rYzKY2t/nLOXozji5gwQlOgEELWCByCEDAB08ZlA4+4gIdCEEJSqACFbTgnlvMpz73yc9++vOfAA2oQAdK0IIa9KAITahCF8rQhjr0oRCNqEQnStGKWvSiGM2oRjfK0Y4q9J71LEEIOmA0CECgg6ksADtr4b2klbAC8ZRnCVBAU5qu4KY4zalOd8rTnvr0p0ANqlCHStSiGvWoSE2qUpfK1KY69alQjapUp0rVqlr1qli4zapWt8pVo9J0niPVgAY2WMoDBKCMGMpFKjv4AFZeQGoeYKMI5jrPutr1rnjNq173yte++vWvgA2sYAdL2MIa9rCITaxiF8vYxjr2sZCNrGQnS9nKWvaymM2sYUPAWQ5wwJyRQ2kqdcEslraUARs0ZwZWG1cPgOC1sI2tbGdL29ra9ra4za1ud8vb3vr2t8ANrnCHS9ziGve4yE2ucpfL3OY697nQja50p0td4G7guuYka1nNStpAAAA7');
			break;
		case 'footer_bg.gif':
			sendImage('R0lGODlhMgAyAMQAAIXDKYTCKYPAKIPBKIK/KIG+KIG9KIC8J4C7J3+6J3+5J364Jn23Jn22Jny1JXu0JXqzJXmyJHmxJHevJHiwJHauIwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAyADIAAAX/ICCOZGmeaKqubOu+cCzPdG3feK7vfO//wKCwFCgaj8ikcslsOp9Q5mBKrVqv2KzWKuh6v+CweEwum89oMmHNbrvf8Lh8Tq/b5YW8fs/v+/+AgYKDhIAGh4iJiouMjY6KB5GSk5SVlpeYmZqbnJgIn6ChoqOkpaanqKmqpgmtrq+wsbKztLAKt7i5uru8vb6/wMHCvgvFxsfIycrLzM3Oz9DMDNPU1dbX2Nna29zd3toN4eLj5OXm5+jkDuvs7e7v8PHy8/T19vIP+fr7/P3+/wADChxIsKDBgwUhKFzIsKHDhxAjSpxIsWLECBgzatzIsaPHjxsliBxJsqTJkyhTNKpcybJlSgowY8qcSbOmzZs4c+rceXOCz59AgwodSrSo0aNIkxatwLSp06dQo0qd+nTIixAAOw==');
			break;
		case 'nane.gif':
			sendImage('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');
			break;
		case 'tableheader-bg-grey.gif':
			sendImage('R0lGODlhqgsyAKIAAPHx8e/v7+3t7ezs7Orq6unp6ejo6Ofn5yH5BAAHAP8ALAAAAACqCzIAAAP/CLrc/jDKSau9OOvNu/9gKI5kaZ5oqq5s675wLM90bd94ru987//AoHBILBqPyKRyyWw6n9CodEolBq7YrHbL7Xq/4LB4TC6bz+i0es1uu9/wuHxOr9vv+Lx+z+/7/4CBgoOEhYaHiImKi4yNjo+QkZKTlJWWl5iZmpucnZ6foKGRAqSlpqeoqaqrrK2ur7CxsrO0tba3uLm6u7y9vr/AwcLDxMXGx8jJysvMzc7P0NHS09TV1tfY2drb3N3e3+Dh4uPk5ebn6Onq6+zt7t4D8fLz9PX29/j5+vv8/f7/AAMKHEiwoMGDCBMqXMiwocOHECNKnEixosWLGDNq3Mix/6PHjyBDihxJsqTJkyhTqlzJsqXLlzBjypxJs6bNmzhz6ty5koDPn0CDCh1KtKjRo0iTKl3KtKnTp1CjSp1KtarVq1izat3KtavXr2DDih1LtqzZs2jTql3Ltq3bt3Djyp1Lt67du3jz6t3Lt6/fv4ADCx5MuLDhw4jxFljMuLHjx5AjS55MubLly5gza97MubPnz6BDix5NurTp06hTq17NurXr17Bjy55Nu7bt27hz697Nu7fv38CDCx9OvLjx48iTK1/OvLnz59CjS59OvXpxA9iza9/Ovbv37+DDix9Pvrz58+jTq1/Pvr379/Djy59Pv779+/jz69/Pv7////8ABijggAQWaOCBCCao4IIMNujggxBGKOGEFFZo4YUYZqjhhhx26OGHIIYo4ogklmjiiSimqOKKLLbo4oswxijjjDTWaOONOOao44489ujjj0AGKSSLBxRp5JFIJqnkkkw26eSTUEYp5ZRUVmnllVhmqeWWXHbp5ZdghinmmGSWaeaZaKap5ppstunmm3DGKeecdNZp55145qnnnnz26eefgAYq6KCEFmrooYgmquiijDbq6KOQRirppJRWaumlmGaq6aacdurpp6CGKuqopJZq6qmopqrqqqy26uqrsMYq66y01mrrrbjmquuuvPbq66/ABivssMQWa+yxyCar7LL/zDbr7LPQRivttNRWa+212Gar7bbcduvtt+CGK+645JZr7rnopqvuuuy26+678MYr77z01mvvvfjmq+++/Pbr778AByzwwAQXbPDBCCes8MIMN+zwwxBHLPHEFFds8cUYZ6zxxhx37PHHIIcs8sgkl2zyySinrPLKLLfs8sswxyzzzDTXbPPNOOes88489+zzz0AHLfTQRBdt9NFIJ6300kw37fTTUEct9dRUV2311VhnrfXWXHft9ddghy322GSXbfbZaKet9tpst+3223DHLffcdNdt991456333nz37fffgAcu+OCEF2744YgnrvjijDfu+OOQRy755JRXbvnl0phnrvnmnHfu+eeghy766KSXbvrpqKeu+uqst+7667DHLvvstNdu++2456777rz37vvvwAcv/PDEF2/88cgnr/zyzDfv/PPQRy/99NRXb/312Gev/fbcd+/99+CHL/745Jdv/vnop6/++uy37/778Mcv//z012///fjnr//+/Pfv//8ADKAAB0jAAhrwgAhMoAIXyMAGOvCBEIygBCdIwQpa8IIYzKAGN8jBDnrwgyAMoQhHSMISmvCEKEyhClfIwha68IUwjKEMZ0jDGtrwhuVKAAA7');
			break;
		case 'tableheader-bg.gif':
			sendImage('R0lGODlhrgsyALMAAOPs8urw9O3y9eHr8t/q8t7q8uXt8+fu8+Xu8+ju8+fu9N7p8t3p8tzp8gAAAAAAACH5BAAAAAAALAAAAACuCzIAAAT/UMhJq7046827/2AojmRpnmiqrmzrvnAsz3Rt33iu73zv/8CgcEgsGo/IpHLJbDqf0Kh0Sq1ar9isdsuFBr7gsHhMLpvP6LR6zW673/C4fE6v2+/4vH7P7/v/gIGCg4SFhoeIiYqLjI2Oj5CRkpOUlZaXmJmam5ydnp+goaKjpKWmp6ipnQcHCaysCgqur7OwCQqts7WtsK+srrKuu7e+xbzFur+7r7i0xsfQxgm3zc6/vczT1tG+y7jCss+xyM7Vz8zHzbPqreHC69fa2gfh2/TE88PY5fLd5tIAj80rZm7ZtYPPpo3rZs1csG0GEfYCF3EhRHrnCKY7yO6WsFzY/96BvMcw27dkDck9HPaPnEtdFUu65NaN2kx42WrpPOeNl0dx/3a2FLexKEWfOEXOqydR1kmJOPl9pDXU3kGYRK0ijKjM4tZ993ZKlDmxXcWg5TL6+hay7U+RGJGOZBrN6Uh9caVGI5bRIMp+JO3ZzVcS7Up/av1ejakVGtdcXh2Dffg1ocajxiILVYsuqVt3c+PBpbtrMEqOKgH/qjpWoLKsrSXztCkzatiLltf6pMsM7TXWuj3HPTpa9Fy8tp7uBcvrMNXEAbFmJos7IW3cDgeKDdjZLNCbedWyHf7541LjS5HfU47XsOp20F++nt54u7TrlbNv59oTM0HfzXFWFv955L0VmlzpkWUaVMwB8x5fPEX32jrqLbhceM09CJxiAjEWm33I4CebftXNVNZPmQEIjIAYCdcRaOchSKGC+JxWlF4sxVeSdBrV11hXKk6m3Y/9eZcieMB1R+CL5nkmz4yC1cgghg5OtZqO1vCom4+xAQleeJTJxt2JvMESZJIDslMgjE6OVqGUF7pnJXx9SThSjx/+CFmQYA7ZZZEo/ocki+OpyaRcBbpJI3uFpTYnhLnt+JoBlFKKgKWYVmrApZp2Wimnm4Ka6aaZImAqqaKO6mmoo6aqqauncirqpa6Sumqnp75q66eyyvopq7jeamuqpvoq7K+51prrrckGe2z/q8T+Omysu0rLrLHVOvsqrapy62mt30ZLbK+tAqsrs6qGiu2xtK7LK7vugqvtsuVOyy2s8Na7KqzezpptutcGO66l2Lb7rbDxNvssqu4SnG+28p5rbr293nswwgUjHO6wuiq78MSo4krurgZre/G4De8LrLIRK2wtu+9KbGyxIZu8cczRLgwqvSSXSi2+GOt78bYcI6uyzuKK7HDPPP8rLcoAq+wy0UFD/PHOORdNs7cS75sxukqvy3XXVRtNtcslk30u1E4fPHXMAVutM9wUE2yxzUr3DDbRYjsdMd0145xx0/ImnLLbKTd9Nt5Hg7yzz3ervXjUAFNb7dgvl43p/8ArIwv00Wz/vXniLcd7NeCP2xt45nkvnTXMS1s7889Dey003pb76zHSAueNNshtF83r4QKT/vDrGsdet7qrU+4666zn3nHjD5steLmEJx/66W8rfzPye/OcesXNB9+58/wKX775qEM88uPZg7393N0DPzn0Q4v/tN3ll3672tKznuRuJsCVDe5zJxMZ8TpmvKqBr3H6KxX/MGe+XAHgghjMoAY3yMEOevCDIAyhCEdIwhKa8IQoTKEKV8jCFrrwhTCMoQxnSMMa2vCGOMyhDnfIwx768IdADKIQh0jEIhrxiEhMohKXyMQmOvGJUIyiFKdIxSpa8YpYzKIWt8jFLv968YtgDGMVB0DGMprxjGhMoxrXyMY2uvGNcIyjHOdIxzra8Y54zKMe98jHPvrxj4AMpCAHSchCGvKQiEykIhfJyEY68pGQjKQkJ0nJSlrykpjMpCY3yclOevKToAylKEdJylKa8pSoTKUqV8nKVrpSlASIpSxnScta2vKWuMylLnfJy1768pfADKYwZ1mAYRrzmMhMpjKXycxm8rKYzoymNKdJzWpak5jXzKY2t8nNbUKzm+AMpzjHSc5ufrOc6EynOqd5znW6853w9GU740nPetJznvbMpz73qU988vOfAKWmPwNK0IIic6AGTahCn7lQcRagAAt4qEQjKlGIVvT/ohjNKEUzytGOatSjIA2pSEdK0pJydKMbNelEU2pRlYaUpSw1aUxdelKa2vSmOBUpSnG6AJjm9KMVnSlJhfpTov70qEh96UR56tOkPrSpNDUqT51K1ap2dKc37elFpRrVrWbVqi29qldtylWygvWsVsUqWaGaVLaqtKxdRatckarWqLr1qHctKVxdute5+lWsT2XqWJ2a16GCta9/TWxQl5rVwk51sWZNK0gdq9PDKvayemXsWgfbVs6+1bKYDe1kNWtXz+LVtJmVrGhXC9SwlhayhEWtYVXL2toG9rabhW1ndftZ2l6UAcANrnCHS9ziGve4yE2ucpfL3OY697nQ/42udKdL3epa97rYza52t8vd7nr3u+ANr3jHS97ymve86E2vetfL3va6973wja9850vf+tr3vvjNr373y9/++ve/AA6wgAdM4AIb+MAITrCC/duABjv4wRCOsIQnTOEKW/jCGM6whjfM4Q57+MMgDrGIR0ziEpv4xChOsYpXzOIWu/jFMI6xjGdM4xrb+MY4zrGOd8zjHvv4x0AOspCHTOQiG/nISE6ykpfM5CY7+clQjrKUp0zlKlv5yktecHcbUFwui9fLWg6zdcEsXDJ/18xiTvNz0cwANnPXzWqOM3LZDGft1lnOeA4zncd75zznec9f9rOgjQvo8PZ50GouNP94D43oRjO4y3x2dKMVfWZJI5rS3mW0pROM6S1vWr9YDrWoR03qUpv61KhOtapXzepWu/rVsI61rGdN61rb+ta4zrWud83rXvv618AOtrCHTexiG/vYyE62spfN7GY7+9nQjra0p03talv72tjOtra3ze1ue/vb4A63uMdN7nKb+9zoTre6183udrv73fCOt7znTe962/ve+M63vvfN7377+98AD7jAB07wghv84AhPuMIXzvCGO/zhEI+4xCdO8Ypb/OIYz7jGN87xjnv84yAPuchHTvKSm/zkKE+5ylfO8pa7/OUwj7nMZ07zmtv85jjPuc53zvOe+/znQA+60If/TvSiG/3oSE+60pfO9KY7/elQj7rUp071qlv96ljPuta3zvWue/3rYA+72MdO9rKb/exoT7va1872trv97XCPu9znTve62/3ueM+73vfO9777/e+AD7zgB0/4whv+8IhPvOIXz/jGO/7xkI+85CdP+cpb/vKYz7zmN8/5znv+86APvehHT/rSm/70qE+96lfP+ta7/vWwj73sZ0/72tv+9rjPve53z/ve+/73wA++8IdP/OIb//jIT77yl8/85jv/+dCPvvSnT/3qW//62M++9rfP/e57//vgD7/4x0/+8pv//OhPv/rXz/72u//98I+//OdP//rb//74z7/+98//Yf77//8AGIACOIAEWIAGeIAImIAKuIAM2IAO+IAQGIESOIEUWIEWeIEYmIEauIEc2IEe+IEgGIIiOIIkWIImeIIomIIquIIs2IIu+IIwGIMyOIM0WIM2eIM4mIM6uIMvFwEAOw==');
			break;
		case 'top_left.gif':
			sendImage('R0lGODlhCgAyAMQAAKvWa5rNTYjFL4XDKYTCKYTBKIPBKIPAKIK/KIG+J4G+KIC9J4C8J3+7J366Jn66J365Jn24Jny2Jnu2Jny3Jnu1JXq0JQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAKADIAAAV/ICCOZFkGaKoKbOsScCwbdG0jeK4nfO8zwKCwQSwaH8ikMsJsOinQqHRCrVot2Kx2u614v+BwWEIum89nqXqtdrrf8DhkTq877vi8cc/fC/+AfwuDhIWGhgqJiouMjDqPkJGSB5SVlpeXBZqbnJ2dMqChoAOkpaanqKmqq6ynIQA7');
			break;
		case 'loading.gif':
			sendImage('R0lGODlhEAAQAPIAAP///2ZmZtra2o2NjWZmZqCgoLOzs729vSH+GkNyZWF0ZWQgd2l0aCBhamF4bG9hZC5pbmZvACH5BAAKAAAAIf8LTkVUU0NBUEUyLjADAQAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQACgABACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkEAAoAAgAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkEAAoAAwAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkEAAoABAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQACgAFACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQACgAGACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAAKAAcALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==');
			break;
	}

}


/*****************************************************************
* FUNCTIONS
******************************************************************/

# Quote string
function quote($str) {
	$str = str_replace('\\', '\\\\', $str);
	$str = str_replace("'",	 "\'",	$str);
	return "'$str'";
}

# Convert filesize from bytes to most suitable unit
function formatSize($bytes) {

	# Define suitable units in 1024x increments
	$types = array( 'B', 'KB', 'MB', 'GB', 'TB' );

	# Decrease until we run out of units or we're less than 1024 in the current unit
	for ( $i = 0, $l = count($types)-1; $bytes >= 1024 && $i < $l; $bytes /= 1024, $i++ );

	# Return a rounded figure with unit
	return ( round($bytes, 2) . ' ' . $types[$i] );

}

# Convert path to URL
function pathToURL($filePath) {

	# Run through realpath to normalise path
	$realPath = realpath($filePath);

	# Verify that the path passed is real and find the directory
	if ( is_file($realPath)) {

		$dir = dirname($realPath);

	} elseif ( is_dir($realPath) ) {

		$dir = $realPath;

	} else {
		# Path does not exist, fails
		return false;
	}
	
	# Expand the document root path
	$_SERVER['DOCUMENT_ROOT'] = realpath($_SERVER['DOCUMENT_ROOT']);

	# Make sure the path is not lower than the server root
	if ( strlen($dir) < strlen($_SERVER['DOCUMENT_ROOT']) ) {
		return false;
	}

	# Determine path from web root
	$rootPos = strlen($_SERVER['DOCUMENT_ROOT']);
	
	# Make sure $rootPos includes the first slash
	if ( ( $tmp = substr($_SERVER['DOCUMENT_ROOT'], -1) ) && ( $tmp == '/' || $tmp == '\\' ) ) {
		--$rootPos;
	}
	
	# Extract path below webroot and discard path above webroot
	$pathFromRoot = substr($realPath, $rootPos);

	# Build URL from parts
	$path = 'http' . ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off' ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . $pathFromRoot;

	# Convert to forward slash if on Windows
	if ( DIRECTORY_SEPARATOR == '\\' ) {
		$path = str_replace('\\', '/', $path);
	}

	return $path;
}

# Hide from non-js browsers by using document.write() to output
function jsWrite($str) {
	return '<script type="text/javascript">document.write(' . quote($str) . ');</script>';
}

# Convert a string of bool value to bool
function bool($str) {
	if ( $str == 'false' ) {
		return false;
	}
	if ( $str == 'true' ) {
		return true;
	}
	return NULL;
}


/*****************************************************************
* CLASSES
******************************************************************/

/*****************************************************************
* Location wrapper - allows us to have observers on the location
******************************************************************/

class Location {

	# Observers
	private $observers;

	# Redirect elsewhere
	public function redirect($to = '') {

		# Notify observers
		$this->notifyObservers('redirect');

		# Redirect and quit
		header('Location: ' . ADMIN_URI . '?' . $to);
		exit;
	}

	# Redirect elsewhere but without observer
	public function cleanRedirect($to = '') {

		# Redirect and quit
		header('Location: ' . ADMIN_URI . '?' . $to);
		exit;
	}

	# Register observers
	public function addObserver(&$obj) {
		$this->observers[] = $obj;
	}

	# Notify observers
	public function notifyObservers($action) {

		# Determine method to call
		$method = 'on' . ucfirst($action);

		# Prepare parameters
		$params = func_get_args();
		array_shift($params);

		# Loop through all observers
		foreach ( $this->observers as $obj ) {

			# If an observing method exists, call it
			if ( method_exists($obj, $method) ) {

				call_user_func_array(array(&$obj, $method), $params);

			}

		}

	}

}


/*****************************************************************
* Input wrapper for incoming data
******************************************************************/

class Input {

	# Set up inputs
	public function __construct() {

		$this->GET	  = $this->prepare($_GET);
		$this->POST	  = $this->prepare($_POST);
		$this->COOKIE = $this->prepare($_COOKIE);

	}

	# Return array with keys converted to lowercase and values cleaned
	private function prepare($array) {

		$return = array();

		foreach ( $array as $key => $value ) {
			$return[strtolower($key)] = self::clean($value);
		}

		return $return;

	}

	# Get an input - inputs can be requested in the form pVarName
	# where VarName is (case insensitive) name of variable (duh!)
	# and p denotes from _POST. G and C are also available.
	public function __get($name) {

		# Do we have a varname?
		if ( ! isset($name[1]) ) {
			return NULL;
		}

		# Split into GPC and VarName (case insensitive)
		$from = strtolower($name[0]);
		$var	= strtolower(substr($name, 1));

		# Define $from to target relationships
		$targets = array('g' => $this->GET,
							  'p' => $this->POST,
							  'c' => $this->COOKIE);

		# Look for the value and return it
		if ( isset($targets[$from][$var]) ) {
			return $targets[$from][$var];
		}

		# Not found, return false
		return NULL;

	}

	# Clean a value
	static public function clean($val) {

		static $magicQuotes;

		# What is our magic quotes setting?
		if ( ! isset($magicQuotes) ) {
			$magicQuotes = get_magic_quotes_gpc();
		}

		# What type is this?
		switch ( true ) {
			case is_string($val):

				# Strip slashes and trim
				if ( $magicQuotes ) {
					$val = stripslashes($val);
				}

				$val = trim($val);

				break;

			case is_array($val):

				$val = array_map(array('Input', 'clean'), $val);

				break;

			default:
				return $val;
		}

		return $val;

	}

}


/*****************************************************************
* Output wrappers
******************************************************************/

# A simple overloading object
class Overloader {

	# Store variables in this array
	protected $data;

	# Set value (case insensitive)
	public function __set($name, $value) {
		$this->data[strtolower($name)] = $value;
	}

	# Get value (case insensitive)
	public function __get($name) {
		$name = strtolower($name);
		return isset($this->data[$name]) ? $this->data[$name] : '';
	}

}

# Base wrapper object
abstract class Output extends Overloader {

	# Full page to output
	protected $output;

	# Content only
	protected $content;

	# Array of observers
	protected $observers = array();


	# Output the page
	final public function out() {

		# Notify our observers we're about to print
		$this->notifyObservers('print', $this);

		# Wrap content in our wrapper
		$this->wrap();

		# Send headers
		$this->sendHeaders();

		# Send body
		print $this->output;

		# Page completed, finish
		exit;

	}


	# Override this to send custom headers instead of default (html)
	protected function sendHeaders() {}


	# Wrapper for body content
	protected function wrap() {
		$this->output = $this->content;
	}


	# Add content
	public function addContent($content) {
		$this->content .= $content;
	}


	# Register observers
	public function addObserver(&$obj) {
		$this->observers[] = $obj;
	}


	# Notify observers
	public function notifyObservers($action) {

		# Determine method to call
		$method = 'on' . ucfirst($action);

		# Prepare parameters
		$params = func_get_args();
		array_shift($params);

		# Loop through all observers
		foreach ( $this->observers as $obj ) {

			# If an observing method exists, call it
			if ( method_exists($obj, $method) ) {

				call_user_func_array(array(&$obj, $method), $params);

			}

		}

	}


	# Send status code
	public function sendStatus($code) {
		header(' ', true, $code);
	}

	# More overloading. Set value with key.
	public function __call($func, $args) {
		if ( substr($func, 0, 3) == 'add' && strlen($func) > 3 && ! isset($args[2]) ) {

			# Saving with key or not?
			if ( isset($args[1]) ) {
				$this->data[strtolower(substr($func, 3))][$args[0]] = $args[1];
			} else {
				$this->data[strtolower(substr($func, 3))][] = $args[0];
			}

		}
	}

}

# Output with our HTML skin
class SkinOutput extends Output {

	# Print all
	private function printAll($name) {
		$name = strtolower($name);
		if ( isset($this->data[$name]) && is_array($this->data[$name]) ) {
			foreach ( $this->data[$name] as $item ) {
				echo $item;
			}
		}
	}

	# Wrap content in HTML skin
	protected function wrap() {

		# Prepare the "get image" path
		$imgs = ADMIN_URI . '?image=';

		# Self
		$self = ADMIN_URI;

		# Prepare date
		$date = date('H:i, d F Y');

		# Append "glype control panel" to title
		$title = $this->title . ( $this->title ? ' : ' : '' ) . 'Glype control panel';

		# Buffer so we can get this into a variable
		ob_start();

		# Print output
		echo <<<OUT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title>{$title}</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script type="text/javascript">var offsetx=12;var offsety=8;function newelement(a){if(document.createElement){var b=document.createElement('div');b.id=a;with(b.style){display='none';position='absolute'}b.innerHTML='&nbsp;';document.body.appendChild(b)}}var ie5=(document.getElementById&&document.all);var ns6=(document.getElementById&&!document.all);var ua=navigator.userAgent.toLowerCase();var isapple=(ua.indexOf('applewebkit')!=-1?1:0);function getmouseposition(e){if(document.getElementById){var a=(document.compatMode&&document.compatMode!='BackCompat')?document.documentElement:document.body;pagex=(isapple==1?0:(ie5)?a.scrollLeft:window.pageXOffset);pagey=(isapple==1?0:(ie5)?a.scrollTop:window.pageYOffset);mousex=(ie5)?event.x:(ns6)?clientX=e.clientX:false;mousey=(ie5)?event.y:(ns6)?clientY=e.clientY:false;var b=document.getElementById('tooltip');b.style.left=(mousex+pagex+offsetx)+'px';b.style.top=(mousey+pagey+offsety)+'px'}}function tooltip(a){if(!document.getElementById('tooltip'))newelement('tooltip');var b=document.getElementById('tooltip');b.innerHTML=a;b.style.display='block';document.onmousemove=getmouseposition}function exit(){document.getElementById('tooltip').style.display='none'}window.domReadyFuncs=new Array();window.addDomReadyFunc=function(a){window.domReadyFuncs.push(a)};function init(){if(arguments.callee.done)return;arguments.callee.done=true;if(_timer)clearInterval(_timer);for(var i=0;i<window.domReadyFuncs.length;++i){try{window.domReadyFuncs[i]()}catch(ignore){}}};if(document.addEventListener){document.addEventListener("DOMContentLoaded",init,false)}/*@cc_on@*//*@if(@_win32)document.write("<script id=__ie_onload defer src=javascript:void(0)><\\\/script>");var script=document.getElementById("__ie_onload");script.onreadystatechange=function(){if(this.readyState=="complete"){init()}};/*@end@*/if(/WebKit/i.test(navigator.userAgent)){var _timer=setInterval(function(){if(/loaded|complete/.test(document.readyState)){init()}},10)}window.onload=init;if(!window.XMLHttpRequest){window.XMLHttpRequest=function(){return new ActiveXObject('Microsoft.XMLHTTP')}}function runAjax(a,b,c,d){var e=new XMLHttpRequest();var f=b?'POST':'GET';e.open(f,a,true);e.setRequestHeader("Content-Type","application/x-javascript;");e.onreadystatechange=function(){if(e.readyState==4&&e.status==200){if(e.responseText){c.call(d,e.responseText)}}};e.send(b)}</script>
<script type="text/javascript">
OUT;

		# Add domReady javascript
		if ( $this->domReady ) {
			echo 'window.addDomReadyFunc(function(){', $this->printAll('domReady'), '});';
		}

		# Add other javascript
		if ( $this->javascript ) {
			echo $this->printAll('javascript');
		}

		echo <<<OUT
</script>
<style type="text/css">body{margin:0;font-size:62.5%;font-family:Verdana, Arial, Helvetica, sans-serif;padding:15px 0;background:#eee}#wrap{width:820px;margin:0 auto;background:url({$self}?image=bg.gif) top center repeat-y #FFF}#top_content{padding:0 10px}#topheader{padding:25px 15px 15px;margin:0 auto;background:url({$self}?image=top_left.gif) top left repeat-x #85C329}#rightheader{float:right;width:375px;height:40px;color:#FFF;text-align:right}#rightheader p{padding:35px 15px 0 0;margin:0;text-align:right}#title{padding:0;margin:0;font-size:2.5em;color:#FFF}#title span{font-size:0.5em;font-style:italic}#title a:link,#title a:visited{color:#FFF;text-decoration:none}#title a:hover{color:#E1F3C7}#navigation{background:#74A8F5;border-top:1px solid #fff;height:25px;clear:both}#navigation ul{padding:0;margin:0;list-style:none;font-size:1.1em;height:25px}#navigation ul li{display:inline}#navigation ul li a{color:#FFF;display:block;text-decoration:none;float:left;line-height:25px;padding:0 16px;border-right:1px solid #fff}#navigation ul li a:hover{background:#5494F3}#content{padding:15px;margin:0 auto;background:url({$self}?image=content_bg.gif) repeat-x left top #fff;color:#666}#content h1,#content h2,#content h3,#content h4,#content h5{color:#74A8F5}#content h1{font-family:"Trebuchet MS", Arial, Helvetica;padding:0;margin:0 0 15px;font-size:2em}#content h2{font-family:"Trebuchet MS", Arial, Helvetica;padding:0;margin:0 0 15px;font-size:1.5em}#top_body,#content_body{padding:0 25px}#footer{background:url({$self}?image=footer.gif) no-repeat center bottom;color:#FFF;padding:0 10px 13px}#footer p a:link,#footer p a:visited{color:#FFF;font-style:italic;text-decoration:none}#footer #footer_bg{background:url({$self}?image=footer_bg.gif) repeat-x left bottom #85C329;padding:15px 15px 25px;border-top:1px solid #7BB425}#footer #design{display:block;width:150px;height:30px;float:right;line-height:20px;padding:0 5px;text-align:right;color:#E1F3C7}#footer #design a,#rightheader a:link,#rightheader a:visited{color:#FFF;text-decoration:underline}.table{margin-bottom:15px;width:100%;border-collapse:collapse}.table_header td a:link,.table_header td a:visited{text-decoration:underline;color:#467aa7}.table_header td{background:url({$self}?image=tableheader-bg.gif) no-repeat left top;padding:5px 10px;color:#467aa7;border-top:1px solid #CBD6DE;border-bottom:1px solid #ADBECB;font-size:1.1em;font-weight:bold;border:1px solid #CBD6DE}.row1 td,.row2 td,.row3 td,.row_hover td,.paging_row td{padding:5px 10px;color:#666;border:1px solid #CBD6DE}.row1 td{background:#fff}.row2 td{background:#f6f6f6}.row3 td{background:#eee}.row1:hover td,.row2:hover td,.row3:hover td{background:#FBFACE;color:#000}.hidden{display:none}#content .little{font-size:9px}.clear{clear:both}.img_left{float:left;padding:1px;border:1px solid #ccc;margin:0 10px 10px 0}#content ul{font-size:1.1em;line-height:1.8em;margin:0 0 15px;padding:0;list-style-type:none}#content p{font-size:1.2em;margin:0;padding:0 0 15px;line-height:150%}#content p a:hover,.table a:hover,.form_table a:hover,.link a:hover{text-decoration:underline}#content ul.green li{padding:0 0 0 20px;margin:0;background:url({$self}?image=bullet_green.gif) no-repeat 1px 3px;font-size:1.1em}#content ul.black li{padding:0 0 0 20px;margin:0;background:url({$self}?image=bullet_grey.gif) no-repeat 1px 3px;font-size:1.1em}#content ul.black li a:link,#content ul.black li a:visited{color:#666;text-decoration:none}#content ol{padding:0 0 0 25px;margin:0 0 15px;line-height:1.8em}#content ol li{font-size:1.1em}#content ol li a:link,#content ol li a:visited,#content ul.green li a:link,#content ul.green li a:visited,#content p a,#content p a:visited,.table a,.table a:visited,.form_table a,.link a{color:#73A822;text-decoration:none}#content ol li a:hover,#content ul.green li a:hover,.table_header td a:hover{color:#73A822;text-decoration:underline}#content p.paging{padding:5px;border:1px solid #CBD6DE;text-align:center;margin-bottom:15px;background:#eee}.small_input{font-size:10px}.form_table{margin-bottom:15px;font-size:1.1em}.form_table td{padding:5px 10px}input.button{margin:0;padding:2px;border:3px double #999;border-left-color:#ccc;border-top-color:#ccc;background:url({$self}?image=button.gif) repeat-x left top;font-size:11px;font-family:Verdana, Arial, Helvetica, sans-serif}input.inputgri,select.inputgri,textarea.inputgri{background:#eee;font-size:14px;border:1px solid #ccc;padding:3px}input.inputgri:focus,select.inputgri:focus,textarea.inputgri:focus{background:#fff;border:1px solid #686868}textarea.inputgri{font-size:12px;font-family:Verdana, Arial, Helvetica, sans-serif;height:60px}.notice{background:#CAEA99;border:1px solid #70A522;padding:15px;margin-bottom:15px;font-size:1.2em;color:#333}.notice_error{background:#FEDCDA;border:1px solid #CE090E;padding:15px;margin-bottom:15px;font-size:1.2em;color:#333}.notice .close,.notice_error .close{float:right;cursor:pointer;color:#fff;background:#74A8F5;padding:2px;margin-right:2px;border:1px outset #ccc}#notice a{color:#333;text-decoration:underline}.other_links{background:#eee;border-top:1px solid #ccc;padding:5px;margin:0 0 15px}#content .other_links h2{color:#999;padding:0 0 0 3px;margin:0}#content .other_links ul li{padding:0 0 0 20px;background:url({$self}?image=bullet_grey.gif) no-repeat left center}#content .other_links a,#content .other_links a:visited,#content ul.black li a:hover{color:#999;text-decoration:underline}#content .other_links a:hover{color:#666}code{font-size:1.2em;color:#73A822}#tooltip{width:20em;color:#fff;background:#555;font-size:12px;font-weight:normal;padding:5px;border:3px solid #333;text-align:left}.hr{border-top:2px solid #ccc;margin:5px 0 15px}.bold,#rightheader p span{font-weight:bold}.center{text-align:center}.right{text-align:right}.error-color{color:#CE090E}.ok-color{color:#70A522}.wide-input{width:350px}.small-input{width:50px}.tooltip{padding-bottom:1px;border-bottom:1px dotted #70A522;cursor:help}.ajax-loading{background:url({$self}?image=loading.gif)}.bar{background:#73A822;height:10px;font-size:xx-small;padding:2px;color:#000}.comment{padding:5px;border:1px solid #CBD6DE;border-width:1px 0 1px 0;margin-bottom:15px;background:#f6f6f6}#content .comment p,#content .comment ul,#content .other_links ul,form,.checkbox_nomargins,.form_table p,#footer p{margin:0;padding:0}#preload{position:absolute;height:10px;top:-100px}</style>
</head>
<body>
	<div id="wrap">

		<div id="top_content">

			<div id="header">

				<div id="rightheader">
					<p>
						{$date}
						<br />
OUT;

		# Add the "welcome" and log out link
		if ( $this->admin ) {
			echo "welcome, <i>{$this->admin}</i> : <strong><a href=\"{$self}?logout\">log out</a></strong>\r\n";
		}

		$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		echo <<<OUT
					</p>
				</div>

				<div id="topheader">
					<h1 id="title">
						<a href="{$self}">Glype Admin Control Panel</a><br>
						<span>for {$http_host}</span>
					</h1>
				</div>

				<div id="navigation">
					<ul>
OUT;

		# Add navigation
		if ( is_array($this->navigation) ) {

			foreach ( $this->navigation as $text => $href ) {
				if (stripos($href,$self)!==false) {
					echo "<li><a href=\"{$href}\">{$text}</a></li>\r\n";
				} else {
					echo "<li><a href=\"{$href}\" target=\"_blank\">{$text}</a></li>\r\n";
				}
			}

		}

		echo <<<OUT
					</ul>
				</div>

			</div>

			<div id="content">

				<h1>{$this->bodyTitle}</h1>
OUT;

		# Do we have any error messages?
		if ( $this->error ) {

			# Print all
			foreach ( $this->error as $id => $message ) {
				echo <<<OUT
				<div class="notice_error" id="notice_error_{$id}">
					<a class="close" title="Dismiss" onclick="document.getElementById('notice_error_{$id}').style.display='none';">X</a>
					{$message}
				</div>
OUT;
			}

		}

		# Do we have any confirmation messages?
		if ( $this->confirm ) {

			# Print all
			foreach ( $this->confirm as $id => $message ) {
				echo <<<OUT
				<div class="notice" id="notice_{$id}">
					<a class="close" title="Dismiss" onclick="document.getElementById('notice_{$id}').style.display='none';">X</a>
					{$message}
				</div>
OUT;
			}

		}

		# Print content
		echo $this->content;

		# Print footer links
		if ( is_array($this->footerLinks) ) {

			echo '
				<br>
				<div class="other_links">
					<h2>See also</h2>
					<ul class="other">
					';

			foreach ( $this->footerLinks as $text => $href ) {
				echo "<li><a href=\"{$href}\">{$text}</a></li>\r\n";
			}

			echo '
					</ul>
				</div>
					';

		}


		# And finish off the page
		echo <<<OUT

			</div>

		</div>

		<div id="footer">

			<div id="footer_bg">
				<p><a href="http://www.glype.com/">Glype</a>&reg; &copy; 2007-2013 Glype. All rights reserved.</p>
			</div>

		</div>

	</div>

	<div id="preload">
		<span class="ajax-loading">&nbsp;</span>
	</div>

</body>
</html>
OUT;

		$this->output = ob_get_contents();

		# Discard buffer
		ob_end_clean();

	}

}


# Send output in "raw" form
class RawOutput extends Output {

	protected function sendHeaders() {
		header('Content-Type: text/plain; charset="utf-8"');
		header('Content-Disposition: inline; filename=""');
	}

}


/*****************************************************************
* User object
******************************************************************/

# Manage sessions and stores user data
class User {

	# Username we're logged in as
	public $name;

	# Our user agent
	public $userAgent;

	# Our IP address
	public $IP;

	# Reason for aborting a session
	public $aborted;


	# Constructor sets up session
	public function __construct() {

		# Don't try to start if autostarted
		if ( session_id() == '' ) {

			# Set up new session
			session_name('admin');
			session_start();

		}

		# Always use a fresh ID for security
		session_regenerate_id();

		# Prepare user data
		$this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$this->IP		  = isset($_SERVER['REMOTE_ADDR'])		? $_SERVER['REMOTE_ADDR']		: '';

		# Use user-agent and IP as identifying data since these shouldn't change mid-session
		$authKey = $this->userAgent . $this->IP;

		# Do we have a stored auth key?
		if ( isset($_SESSION['auth_key']) ) {

			# Compare our current auth_key to stored key
			if ( $_SESSION['auth_key'] != $authKey ) {

				# Mismatch. Session may be stolen.
				$this->clear();
				$this->aborted = 'Session data mismatch.';

			}

		} else {

			# No stored auth key, save it
			$_SESSION['auth_key'] = $authKey;

		}

		# Are we verified?
		if ( ! empty($_SESSION['verified']) ) {
			$this->name = $_SESSION['verified'];
		}

		# Have we expired? Only expire if we're logged in of course...
		if ( $this->isAdmin() && isset($_SESSION['last_click']) && $_SESSION['last_click'] < (time() - ADMIN_TIMEOUT) ) {
			$this->clear();
			$this->aborted = 'Your session timed out after ' . round(ADMIN_TIMEOUT/60) . ' minutes of inactivity.';
		}

		# Set last click time
		$_SESSION['last_click'] = time();

	}

	# Log out, destroy all session data
	public function clear() {

		# Clear existing
		session_destroy();

		# Unset existing variables
		$_SESSION = array();
		$this->name = false;

		# Restart session
		session_start();

	}

	# Log in, saving username session for future requests
	public function login($name) {
		$this->name = $name;
		$_SESSION['verified'] = $name;
	}

	# Are we verified or not?
	public function isAdmin() {
		return (bool) $this->name;
	}

}


/*****************************************************************
* Notice handler (errors or confirmations)
******************************************************************/

class Notice {

	# Storage of messages
	private $data = array();

	# Type of notice handler
	private $type;

	# Constructor fetches any stored from session and clears session
	public function __construct($type) {

		# Save type
		$this->type = $type;

		# Array key
		$key = 'notice_' . $type;

		# Any existing?
		if ( isset($_SESSION[$key]) ) {

			# Extract
			$this->data = $_SESSION[$key];

			# And clear
			unset($_SESSION[$key]);

		}

	}

	# Get messages
	public function get($id = false) {

		# Requesting an individual message?
		if ( $id !== false ) {
			return isset($this->data[$id]) ? $this->data[$id] : false;
		}

		# Requesting all
		return $this->data;

	}

	# Add message
	public function add($msg, $id = false) {

		# Add with or without an explicit key
		if ( $id ) {
			$this->data[$id] = $msg;
		} else {
			$this->data[]	  = $msg;
		}

	}

	# Do we have any messages?
	public function hasMsg() {
		return ! empty($this->data);
	}

	# Observer the print method of output
	public function onPrint($output) {

		$funcName = 'add' . $this->type;

		# Add our messages to the output object
		foreach ( $this->data as $msg ) {
			$output->{$funcName}($msg);
		}

	}

	# Observe redirects - store notices in session
	public function onRedirect() {
		$_SESSION['notice_' . $this->type] = $this->data;
	}

}


/*****************************************************************
* Initialize instances of defined classes. If we were structing
* this nicely we'd stick the above in separate files to keep it
* clean but we're sacrificing good structure for the convenience
* of running this admin script stand-alone.
******************************************************************/

# Create output object
$output	 = new SkinOutput;

# Create an overloader object to hold our template vars.
# This keeps them all together and avoids problems with undefined variable notices.
$tpl		 = new Overloader;

# Location wrapper for redirections
$location = new Location;

# Create user object
$user		 = new User();

# Create notice handlers
$confirm	 = new Notice('confirm');
$error	 = new Notice('error');

# Input wrapper
$input	 = new Input;


/*****************************************************************
* Nearly finished preparing, now just bind them together as appropriate
******************************************************************/

# Add notice handlers as observers of the output object
$output->addObserver($confirm);
$output->addObserver($error);

# Add notice handlers as observers on redirect();
$location->addObserver($confirm);
$location->addObserver($error);

# Pass user details to output object
$output->admin = $user->name;

/*****************************************************************
* AJAX INTERCEPTS
******************************************************************/

if ( $input->gFetch && $user->isAdmin() ) {

	# Stop caching of response
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT' );
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');

	switch ( $input->gFetch ) {

		# Get the latest news
		case 'news':

			# Style the news
			echo '<style type="text/css">body { margin:0; padding:5px; font:80% Tahoma,Verdana; } a { color: #73A822; }</style>';

			# Connect to glype
			if ($ch=curl_init('http://www.glype.com/feeds/news.php?vn='.urlencode($CONFIG['version']).'&lk='.urlencode($CONFIG['license_key']).'&cb='.$cache_bust)) {
				curl_setopt($ch, CURLOPT_TIMEOUT, 2);
				$success = curl_exec($ch);
				curl_close($ch);
			}

			# Ensure we have a return
			if ( empty($success) ) {
				echo 'Currently unable to connect to glype.com for a news update.';
			}

			break;


		# Verify a directory exists and is writable
		case 'test-dir':

			$fail = false;

			# Verify
			if ( ! ( $dir = $input->gDir ) ) {

				# Check we have a dir to test
				$fail = 'no directory given';

			} else if ( ! file_exists($dir) || ! is_dir($dir) ) {

				# Check it exists and is actually a directory
				$fail = 'directory does not exist';

				# Try to create it (in case it was inside the temporary directory)
				if ( ! bool($input->gTmp) && is_writable(dirname($dir)) && @mkdir($dir, 0755, true) ) {

					# Reset error messages and delete directory
					$fail = false;
					$ok	= 'directory does not exist but can be created';
					rmdir($dir);

				}

			} else if ( ! is_writable($dir) ) {

				# Make sure it's writable
				$fail = 'directory not writable - permission denied';

			} else {

				# OK
				$ok = 'directory exists and is writable';

			}

			# Print result
			if ( $fail ) {
				echo '<span class="error-color">Error:</span> ', $fail;
			} else {
				echo '<span class="ok-color">OK:</span> ', $ok;
			}

			break;

	}

	# Finish here
	exit;
}


/*****************************************************************
* Did our settings file load? If not, nothing else we can do.
******************************************************************/

if ( ! $settingsLoaded ) {

	# Show error and exit
	$error->add('The settings file for Glype could not be found.
					 Please upload this tool into your root glype directory.
					 If you wish to run this script from another location,
					 edit the configuration options at the top of the file.
					 <br><br>
					 Attempted to load: <b>' . ADMIN_GLYPE_SETTINGS . '</b>');
	$output->out();

}


/*****************************************************************
* Verify a valid action and force to something else if not.
******************************************************************/

# Are we an admin? If not, force login page.
if ( ! $user->isAdmin() ) {
	$action = 'login';
}

# Do we even have any user details? If not, force installer.
if ( ! isset($adminDetails) ) {
	$action = 'install';
}


/*****************************************************************
* Prepare template variables
******************************************************************/

# URI to self
$self = ADMIN_URI;

# Links to other sections of the control panel
if ( $user->isAdmin() ) {
	$output->addNavigation('Home', $self);
	$output->addNavigation('Edit Settings', $self.'?settings');
	$output->addNavigation('View Logs', $self.'?logs');
	$output->addNavigation('Glype&reg; Licenses', 'https://www.glype.com/purchase.php');
	$output->addNavigation('BlockScript&reg;', $self.'?blockscript');
	$output->addNavigation('Support Forum', 'http://proxy.org/forum/glype-proxy/');
	$output->addNavigation('Promote Your Proxy', 'https://proxy.org/advertise.shtml');
}


/*****************************************************************
* Process current request.
******************************************************************/

switch ( $action ) {


	/*****************************************************************
	* INSTALL - save an admin username/password in our settings file
	******************************************************************/

	case 'install':

		# Do we have any admin details already?
		if ( isset($adminDetails) ) {

			# Add error
			$error->add('An administrator account already exists. For security reasons, you must manually create additional administrator accounts.');

			# And redirect to index
			$location->redirect();

		}

		# Do we have any submitted details to process?
		if ( $input->pSubmit ) {

			# Verify inputs
			if ( ! ( $username = $input->pAdminUsername ) ) {
				$error->add('You must enter a username to protect access to your control panel!');
			}

			if ( ! ( $password = $input->pAdminPassword ) ) {
				$error->add('You must enter a password to protect access to your control panel!');
			}

			# In case things go wrong, add this into the template
			$tpl->username = $username;

			# Process the installation if no errors
			if ( ! $error->hasMsg() && is_writable(ADMIN_GLYPE_SETTINGS) ) {

				# Load up the file
				$file = file_get_contents(ADMIN_GLYPE_SETTINGS);

				# Clear any closing php tag ? > (unnecessary and gets in the way)
				if ( substr(trim($file), -2) == '?>' ) {
					$file	 = substr(trim($file), 0, -2);
				}

				# Look for a "Preserve Me" section
				if ( strpos($file, '//---PRESERVE ME---') === false ) {

					# If it doesn't exist, add it
					$file .= "\r\n//---PRESERVE ME---
# Anything below this line will be preserved when the admin control panel rewrites
# the settings. Useful for storing settings that don't/can't be changed from the control panel\r\n";

				}

				# Prepare the inputs
				$password = md5($password);

				# Add to file
				$file .= "\r\n\$adminDetails[" . quote($username) . "] = " . quote($password) . ";\r\n";

				# Save updated file
				if ( file_put_contents(ADMIN_GLYPE_SETTINGS, $file) ) {

					# Add confirmation
					$confirm->add('Installation successful. You have added <b>' . $username . '</b> as an administrator and are now logged in.');

					# Log in the installer
					$user->login($username);

				} else {

					# Add error message
					$error->add('Installation failed. The settings file appears writable but file_put_contents() failed.');

				}

				# Redirect
				$location->redirect();

			}

		}

		# Prepare skin variables
		$output->title		 = 'install';
		$output->bodyTitle = 'First time use installation';

		# Add javascript
		$output->addDomReady("document.getElementById('username').focus();");

		# Is the settings file writable?
		if ( ! ( $writable = is_writable(ADMIN_GLYPE_SETTINGS) ) ) {

			$error->add('The settings file was found at <b>' . ADMIN_GLYPE_SETTINGS . '</b> but is not writable. Please set the appropriate permissions to make the settings file writable.');

			# And disable the submit button
			$tpl->disabled = ' disabled="disabled"';

		} else {

			$confirm->add('Settings file was found and is writable. Installation can proceed. <b>Do not leave the script at this stage!</b>');

		}

		# Print form
		echo <<<OUT
		<p>No administrator details were found in the settings file. Enter a username and password below to continue. The details supplied will be required on all future attempts to use this control panel.</p>

		<form action="{$self}?install" method="post">
			<table class="form_table" border="0" cellpadding="0" cellspacing="0">
			<tr>
			<td align="right">Username:</td>
			<td align="left"><input class="inputgri" id="username" name="adminUsername" type="text" value="{$tpl->username}"></td>
			</tr>
			<tr>
			<td align="right">Password:</td>
			<td align="left"><input class="inputgri" name="adminPassword" type="password"></td>
			</tr>
			</table>
			<p><input class="button" value="Submit &raquo;" name="submit" type="submit"{$tpl->disabled}></p>
		</form>

OUT;
		break;


	/*****************************************************************
	* LOG IN
	******************************************************************/

	case 'login':

		# Do we have any login details to process?
		if ( $input->pLoginSubmit ) {

			# Verify inputs
			if ( ! ( $username = $input->pAdminUsername ) ) {
				$error->add('You did not enter your username. Please try again.');
			}

			if ( ! ( $password = $input->pAdminPassword ) ) {
				$error->add('You did not enter your password. Please try again.');
			}

			# Validate the submitted details
			if ( ! $error->hasMsg() ) {

				# Validate submitted password
				if ( isset($adminDetails[$username]) && $adminDetails[$username] == md5($password) ) {

					# Update user
					$user->login($username);

					# Redirect to index
					$location->cleanRedirect();

				} else {

					# Incorrect password
					$error->add('The login details you submitted were incorrect.');

				}

			}

		}

		# Have we been automatically logged out?
		if ( $user->aborted ) {
			$error->add($user->aborted);
		}

		# Set up page titles
		$output->title		 = 'log in';
		$output->bodyTitle = 'Log in';

		# Add javascript
		$output->addDomReady("document.getElementById('username').focus();");

		# Show form
		echo <<<OUT
			<p>This is a restricted area for authorised users only. Enter your log in details below.</p>
			<form action="{$self}?login" method="post">
				<table class="form_table" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td align="right">Username:</td>
						<td align="left"><input class="inputgri" id="username" name="adminUsername" type="text"></td>
					</tr>
					<tr>
						<td align="right">Password:</td>
						<td align="left"><input class="inputgri" name="adminPassword" type="password"></td>
					</tr>
				</table>
				<p><input class="button" value="Submit &raquo;" name="loginsubmit" type="submit"></p>
			</form>
OUT;

		break;


	/*****************************************************************
	* LOG OUT
	******************************************************************/

	case 'logout':

		# Clear all user data
		$user->clear();

		# Print confirmation
		$confirm->add('You are now logged out.');

		# Redirect back to login page
		$location->redirect('login');

		break;


	/*****************************************************************
	* INDEX - check status and print summary
	******************************************************************/

	case '':

		#
		# System requirements
		#

		$requirements = array();

		# PHP VERSION ----------------------
		# Find PHP version - may be bundled OS so strip that out
		$phpVersion			= ( $tmp = strpos(PHP_VERSION, '-') ) ? substr(PHP_VERSION, 0, $tmp) : PHP_VERSION;

		# Check above 5 and if not, add error text
		if ( ! ( $ok = version_compare($phpVersion, '5', '>=') ) ) {
			$error->add('Glype requires at least PHP 5 or greater.');
		}

		# Add to requirements
		$requirements[] = array(
			'name'  => 'PHP version',
			'value' => $phpVersion,
			'ok'	=> $ok
		);

		# CURL -------------------------------
		# Check for libcurl
		if ( ! ( $ok = function_exists('curl_version') ) ) {
			$error->add('Glype requires cURL/libcurl.');
		}

		# curl version
		$curlVersion	= $ok && ( $tmp = curl_version() ) ? $tmp['version'] : 'not available';

		# Add to requirements
		$requirements[] = array(
			'name'  => 'cURL version',
			'value' => $curlVersion,
			'ok'	=> $ok
		);

		# --------------------------------------

		# Print page header
		$output->bodyTitle = 'Welcome to your control panel';

		#
		# Glype news
		#

		echo <<<OUT
		<p>This script provides an easy to use interface for managing your Glype. Use the navigation above to get started.</p>
		<h2>Latest Glype news...</h2>
		<iframe scrolling="no" src="{$self}?fetch=news" style="width: 100%; height:150px; border: 1px solid #ccc;" onload="setTimeout('updateLatestVersion()',1000);"></iframe>
		<br><br>
		
		<h2>Checking environment...</h2>
		<ul class="green">
OUT;

		# Print requirements
		foreach ( $requirements as $li ) {
			echo "<li>{$li['name']}: <span class=\"bold" . ( ! $li['ok'] ? ' error-color' : '' ) . "\">{$li['value']}</span></li>\r\n";
		}

		# End requirements
		echo <<<OUT
		</ul>
OUT;

		# How are we doing - tell user if we're OK or not.
		if ( $error->hasMsg() ) {
			echo '<p><span class="bold error-color">Environment check failed</span>. You will not be able to run Glype until you fix the above issue(s).</p>';
		} else {
			echo '<p><span class="bold ok-color">Environment okay</span>. You can run Glype on this server.</p>';
		}


		#
		# Script versions
		#

		$acpVersion = ADMIN_VERSION;
		$proxyVersion = isset($CONFIG['version']) ? $CONFIG['version'] : 'unknown - pre 1.0';
	
		# Create javascript to update the latest stable version
		$javascript = <<<OUT
		function updateLatestVersion(response) {
			document.getElementById('current-version').innerHTML = '<img src="http://www.glype.com/feeds/proxy-version.php?cb={$cache_bust}" border="0" alt="version" />';
		}
OUT;
		$output->addJavascript($javascript);

		# Print version summary
		echo <<<OUT
		<br>
		<h2>Checking script versions...</h2>
		<ul class="green">
			<li>Control Panel version: <b>{$acpVersion}</b></li>
			<li>Glype version: <b>{$proxyVersion}</b></li>
			<li>Latest version: <span class="bold" id="current-version">unknown</span></li>
		</ul>
OUT;

		# Is the settings file up to date?
		function forCompare($val) { return str_replace(' ', '', $val); }

		if ( $proxyVersion != 'unknown - pre 1.0' && version_compare(forCompare($acpVersion), forCompare($proxyVersion), '>') ) {
			echo "<p><span class=\"bold error-color\">Note:</span> Your settings file needs updating. Use the <a href=\"{$self}?settings\">Edit Settings</a> page and click Update.</p>";
		}


		# Add footer links
		$output->addFooterLinks('Glype support forum at Proxy.org', 'http://proxy.org/forum/glype-proxy/');

		break;


	/*****************************************************************
	* SETTINGS
	******************************************************************/

	case 'settings':

		# Check the settings are writable
		if ( ! is_writable(ADMIN_GLYPE_SETTINGS) ) {
			$error->add('The settings file is not writable. You will not be able to save any changes. Please set permissions to allow PHP to write to <b>' . realpath(ADMIN_GLYPE_SETTINGS) . '</b>');
			$tpl->disabled = ' disabled="disabled"';
		}

		# Load options into object
		$options = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><options><section name="Special Options" type="settings"><option key="license_key" type="string" input="text" styles="wide-input"><title>Glype License key</title><default>\'\'</default><desc>If you have purchased a license, please enter your license key here. Leave blank if you don\'t have a license.</desc></option><option key="enable_blockscript" type="bool" input="radio"><title>Enable BlockScript</title><default>false</default><desc>BlockScript is security software which protects websites and empowers webmasters to stop unwanted traffic.</desc></option></section><section name="Installation Options" type="settings"><option key="theme" type="string" input="select"><title>Theme</title><default>\'default\'</default><desc>Theme/skin to use. This should be the name of the appropriate folder inside the /themes/ folder.</desc><generateOptions eval="true"><![CDATA[/* Check the dir exists */$themeDir = GLYPE_ROOT . \'/themes/\';if ( ! is_dir($themeDir) ) {return false;}/* Load folders from /themes/ */$dirs = scandir($themeDir);/* Loop through to create options string */$options = \'\';foreach ( $dirs as $dir ) {/* Ignore dotfiles */if ( $dir[0] == \'.\' ) {continue;}/* Add if this is valid theme */if ( file_exists($themeDir . $dir . \'/main.php\') ) {/* Make selected if this is our current theme */$selected =	( isset($currentValue) && $currentValue == $dir ) ? \' selected="selected"\' : \'\';/* Add option */$options .= "<option{$selected}>{$dir}</option>";}}return $options;]]></generateOptions></option><option key="plugins" type="string" input="text" styles="wide-input" readonly="readonly"><title>Register Plugins</title><default></default><desc>Run plugins on these websites</desc><toDisplay eval="true"><![CDATA[ if ($handle = opendir(GLYPE_ROOT."/plugins")) {while (($plugin=readdir($handle))!==false) {if (preg_match(\'#\.php$#\', $plugin)) {$plugin = preg_replace("#\.php$#", "", $plugin);$plugins[] = $plugin;}}closedir($handle);$plugin_list = implode(",", $plugins);} return $plugin_list; ]]></toDisplay><afterField>Auto-generated from plugins directory. Do not edit!</afterField></option><option key="tmp_dir" type="string" input="text" styles="wide-input"><title>Temporary directory</title><default>GLYPE_ROOT . \'/tmp/\'</default><desc>Temporary directory used by the script. Many features require write permission to the temporary directory. Ensure this directory exists and is writable for best performance.</desc><relative to="GLYPE_ROOT" desc="root proxy folder" /><isDir /></option><option key="gzip_return" type="bool" input="radio"><title>Use GZIP compression</title><default>false</default><desc>Use GZIP compression when sending pages back to the user. This reduces bandwidth usage but at the cost of increased CPU load.</desc></option><option key="ssl_warning" type="bool" input="radio"><title>SSL warning</title><default>true</default><desc>Warn users before browsing a secure site if on an insecure connection. This option has no effect if your proxy is on https.</desc></option><option key="override_javascript" type="bool" input="radio"><title>Override native javascript</title><default>false</default><desc>The fastest and most reliable method of ensuring javascript is properly proxied is to override the native javascript functions with our own. However, this may interfere with any other javascript added to the page, such as ad codes.</desc></option><option key="load_limit" type="float" input="text" styles="small-input"><title>Load limiter</title><default>0</default><desc>This option fetches the server load and stops the script serving pages whenever the server load goes over the limit specified. Set to 0 to disable this feature.</desc><afterField eval="true"><![CDATA[/* Attempt to find the load */$load = ( ($uptime = @shell_exec(\'uptime\')) && preg_match(\'#load average: ([0-9.]+),#\', $uptime, $tmp) ) ? (float) $tmp[1] : false;if ( $load === false ) {return \'<span class="error-color">Feature unavailable here</span>. Failed to find current server load.\';} else {return \'<span class="ok-color">Feature available here</span>. Current load: \' . $load;}]]></afterField></option><option key="footer_include" type="string" input="textarea" styles="wide-input"><title>Footer include</title><default>\'\'</default><desc>Anything specified here will be added to the bottom of all proxied pages just before the <![CDATA[</body>]]> tag.</desc><toDisplay eval="true"><![CDATA[ return htmlentities($currentValue); ]]></toDisplay></option></section><section name="URL Encoding Options" type="settings"><option key="path_info_urls" type="bool" input="radio"><title>Use path info</title><default>false</default><desc>Formats URLs as browse.php/aHR0... instead of browse.php?u=aHR0... Path info may not be available on all servers.</desc></option></section><section name="Hotlinking" type="settings"><option key="stop_hotlinking" type="bool" input="radio"><title>Prevent hotlinking</title><default>true</default><desc>This option prevents users "hotlinking" directly to a proxied page and forces all users to first visit the index page.</desc></option><option key="hotlink_domains" type="array" input="textarea" styles="wide-input"><title>Allow hotlinking from</title><default>array()</default><desc>If the above option is enabled, you can add individual referrers that are allowed to bypass the hotlinking protection.</desc><toDisplay eval="true"><![CDATA[ return implode("\r\n", $currentValue); ]]></toDisplay><toStore eval="true"><![CDATA[ $value = str_replace("\r", "\n", $value);$value=preg_replace("#\n+#", "\n", $value);return array_filter(explode("\n", $value));]]></toStore><afterField>Enter one domain per line</afterField></option></section><section name="Logging" type="settings"><comment><![CDATA[<p>You may be held responsible for requests from your proxy\'s IP address. You can use logs to record the decrypted URLs of pages visited by users in case of illegal activity undertaken through your proxy.</p>]]></comment><option key="enable_logging" type="bool" input="radio"><title>Enable logging</title><default>false</default><desc>Enable/disable the logging feature. If disabled, skip the rest of this section.</desc></option><option key="logging_destination" type="string" input="text" styles="wide-input"><title>Path to log folder</title><default>$CONFIG[\'tmp_dir\']	. \'logs/\'</default><desc>Enter a destination for log files. A new log file will be created each day in the directory specified. The directory must be writable. To protect against unauthorized access, place the log folder above your webroot.</desc><relative to="$CONFIG[\'tmp_dir\']" desc="temporary directory" /><isDir /></option><option key="log_all" type="bool" input="radio"><title>Log all requests</title><default>false</default><desc>You can avoid huge log files by only logging requests for .html pages, as per the default setting. If you want to log all requests (images, etc.) as well, enable this.</desc></option></section><section name="Website access control" type="settings"><comment><![CDATA[<p>You can restrict access to websites through your proxy with either a whitelist or a blacklist:</p><ul class="black"><li>Whitelist: any site that <strong>is not</strong> on the list will be blocked.</li><li>Blacklist: any site that <strong>is</strong> on the list will be blocked</li></ul>]]></comment><option key="whitelist" type="array" input="textarea" styles="wide-input"><title>Whitelist</title><default>array()</default><desc>Block everything except these websites</desc><toDisplay eval="true"><![CDATA[ return implode("\r\n", $currentValue); ]]></toDisplay><toStore eval="true"><![CDATA[ $value = str_replace("\r", "\n", $value);$value=preg_replace("#\n+#", "\n", $value);return array_filter(explode("\n", $value));]]></toStore><afterField>Enter one domain per line</afterField></option><option key="blacklist" type="array" input="textarea" styles="wide-input"><title>Blacklist</title><default>array()</default><desc>Block these websites</desc><toDisplay eval="true"><![CDATA[ return implode("\r\n", $currentValue); ]]></toDisplay><toStore eval="true"><![CDATA[ $value = str_replace("\r", "\n", $value);$value=preg_replace("#\n+#", "\n", $value);return array_filter(explode("\n", $value));]]></toStore><afterField>Enter one domain per line</afterField></option></section><section name="User access control" type="settings"><comment><![CDATA[<p>You can ban users from accessing your proxy by IP address. You can specify individual IP addresses or IP address ranges in the following formats:</p><ul class="black"><li>127.0.0.1</li><li>127.0.0.1-127.0.0.5</li><li>127.0.0.1/255.255.255.255</li><li>192.168.17.1/16</li><li>189.128/11</li></ul>]]></comment><option key="ip_bans" type="array" input="textarea" styles="wide-input"><title>IP bans</title><default>array()</default><toDisplay eval="true"><![CDATA[ return implode("\r\n", $currentValue); ]]></toDisplay><toStore eval="true"><![CDATA[ $value = str_replace("\r", "\n", $value);$value=preg_replace("#\n+#", "\n", $value);return array_filter(explode("\n", $value));]]></toStore><afterField>Enter one IP address or IP address range per line</afterField></option></section><section name="Transfer options" type="settings"><option key="connection_timeout" type="int" input="text" styles="small-input" unit="seconds"><title>Connection timeout</title><default>5</default><desc>Time to wait for while establishing a connection to the target server. If the connection takes longer, the transfer will be aborted.</desc><afterField>Use 0 for no limit</afterField></option><option key="transfer_timeout" type="int" input="text" styles="small-input" unit="seconds"><title>Transfer timeout</title><default>15</default><desc>Time to allow for the entire transfer. You will need a longer time limit to download larger files.</desc><afterField>Use 0 for no limit</afterField></option><option key="max_filesize" type="int" input="text" styles="small-input" unit="MB"><title>Filesize limit</title><default>0</default><desc>Preserve bandwidth by limiting the size of files that can be downloaded through your proxy.</desc><toDisplay>return $currentValue ? round($currentValue/(1024*1024), 2) : 0;</toDisplay><toStore>return $value*1024*1024;</toStore><afterField>Use 0 for no limit</afterField></option><option key="download_speed_limit" type="int" input="text" styles="small-input" unit="KB/s"><title>Download speed limit</title><default>0</default><desc>Preserve bandwidth by limiting the speed at which files are downloaded through your proxy. Note: if limiting download speed, you may need to increase the transfer timeout to compensate.</desc><toDisplay>return $currentValue ? round($currentValue/(1024), 2) : 0;</toDisplay><toStore>return $value*1024;</toStore><afterField>Use 0 for no limit</afterField></option><option key="resume_transfers" type="bool" input="radio"><title>Resume transfers</title><default>false</default><desc>This forwards any requested ranges from the client and this makes it possible to resume previous downloads. Depending on the "Queue transfers" option below, it may also allow users to download multiple segments of a file simultaneously.</desc></option><option key="queue_transfers" type="bool" input="radio"><title>Queue transfers</title><default>true</default><desc>You can limit use of your proxy to allow only one transfer at a time per user. Disable this for faster browsing.</desc></option></section><section name="Cookies" type="settings"><comment><![CDATA[<p>All cookies must be sent to the proxy script. The script can then choose the correct cookies to forward to the target server. However there are finite limits in both the client\'s storage space and the size of the request Cookie: header that the server will accept. For prolonged browsing, you may wish to store cookies server side to avoid this problem.</p><br><p>This has obvious privacy issues - if using this option, ensure your site clearly states how it handles cookies and protect the cookie data from unauthorized access.</p>]]></comment><option key="cookies_on_server" type="bool" input="radio"><title>Store cookies on server</title><default>false</default><desc>If enabled, cookies will be stored in the folder specified below.</desc></option><option key="cookies_folder" type="string" input="text" styles="wide-input"><title>Path to cookie folder</title><default>$CONFIG[\'tmp_dir\']	 . \'cookies/\'</default><desc>If storing cookies on the server, specify a folder to save the cookie data in. To protect against unauthorized access, place the cookie folder above your webroot.</desc><relative to="$CONFIG[\'tmp_dir\']" desc="temporary directory" /><isDir /></option><option key="encode_cookies" type="bool" input="radio"><title>Encode cookies</title><default>false</default><desc>You can encode cookie names, domains and values with this option for optimum privacy but at the cost of increased server load and larger cookie sizes. This option has no effect if storing cookies on server.</desc></option></section><section name="Maintenance" type="settings"><option key="tmp_cleanup_interval" type="float" input="text" styles="small-input" unit="hours"><title>Cleanup interval</title><default>48</default><desc>How often to clear the temporary files created by the script?</desc><afterField>Use 0 to disable</afterField></option><option key="tmp_cleanup_logs" type="float" input="text" styles="small-input" unit="days"><title>Keep logs for</title><default>30</default><desc>When should old log files be deleted? This option has no effect if the above option is disabled.</desc><afterField>Use 0 to never delete logs</afterField></option></section><section type="user" name="User Configurable Options"><option key="encodeURL" default="true" force="false"><title>Encrypt URL</title><desc>Encrypts the URL of the page you are viewing for increased privacy.</desc></option><option key="encodePage" default="false" force="false"><title>Encrypt Page</title><desc>Helps avoid filters by encrypting the page before sending it and decrypting it with javascript once received.</desc></option><option key="showForm" default="true" force="true"><title>Show Form</title><desc>This provides a mini-form at the top of each page that allows you to quickly jump to another site without returning to our homepage.</desc></option><option key="allowCookies" default="true" force="false"><title>Allow Cookies</title><desc>Cookies may be required on interactive websites (especially where you need to log in) but advertisers also use cookies to track your browsing habits.</desc></option><option key="tempCookies" default="true" force="true"><title>Force Temporary Cookies</title><desc>This option overrides the expiry date for all cookies and sets it to at the end of the session only - all cookies will be deleted when you shut your browser. (Recommended)</desc></option><option key="stripTitle" default="false" force="true"><title>Remove Page Titles</title><desc>Removes titles from proxied pages.</desc></option><option key="stripJS" default="true" force="false"><title>Remove Scripts</title><desc>Remove scripts to protect your anonymity and speed up page loads. However, not all sites will provide an HTML-only alternative. (Recommended)</desc></option><option key="stripObjects" default="false" force="false"><title>Remove Objects</title><desc>You can increase page load times by removing unnecessary Flash, Java and other objects. If not removed, these may also compromise your anonymity.</desc></option></section><section type="forced" hidden="true" name="Do not edit this section manually!"><option key="version" type="string"><default>\''.ADMIN_VERSION.'\'</default><desc>Settings file version for determining compatibility with admin tool.</desc></option></section></options>');
		
		#
		# SAVE CHANGES
		#
		if ( $input->pSubmit && ! $error->hasMsg() ) {

			# Filter inputs to create valid PHP code
			function filter($value, $type) {

				switch ( $type ) {

					# Quote strings
					case 'string':
					default:
						return quote($value);

					# Clean integers
					case 'int':
						return intval($value);

					# Float
					case 'float':
						if ( is_numeric($value) ) {
							return $value;
						}
						return quote($value);

					# Create arrays - make empty array if no value, not an array with a single empty value
					case 'array':
						$args = $value ? implode(', ', array_map('quote', (array) $value)) : '';
						return 'array(' . $args . ')';

					# Bool - check we have a real bool and resort to default if not
					case 'bool':
						if ( bool($value) === NULL ) {
							global $option;
							$value = $option->default;
						}
						return $value;

				}

			}

			# Create a comment line
			function comment($text, $multi=false) {

				# Comment marker
				$char = $multi ? '*' : '#';

				# Split and make newlines with the comment char
				$text = wordwrap($text, 65, "\r\n$char ");

				# Return a large comment
				if ( $multi ) {
					return '/*****************************************************************
* ' . $text . '
******************************************************************/';
				}

				# Return a small comment
				return "# $text";

			}

			# Prepare the file header
			$toWrite = '<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
*******************************************************************
* Our settings file. Self-explanatory - stores the config values.
*******************************************************************
* This file has been automatically generated by the glype admin tool.
* For a more complete and thorough explanation of options, consult
* the original settings.php file from the glype package.
******************************************************************/
';
			# Loop through all the sections
			foreach ( $options->section as $section ) {

				# Add section header to the file
				$toWrite .= NL . NL . comment($section['name'], true) . NL;

				# Now go through this section's options
				foreach ( $section->option as $option ) {

					$key = (string) $option['key'];

					# Grab the posted value
					$value = $input->{'p' . $key};

					# The user-configurable options need special treatment
					if ( $section['type'] == 'user' ) {

						# We need to save 4 values - title, desc, default and force
						$title	= filter( ( isset($value['title'])	 ? $value['title']	: $option->title	  ), 'string');
						$desc		= filter( ( isset($value['desc'])	 ? $value['desc']		: $option->desc	  ), 'string');
						$default = filter( ( isset($value['default']) ? $value['default'] : $option['default']), 'bool');
						$force	= isset($value['force']) ? 'true' : 'false';

						# Write them
						$toWrite .=
"\r\n\$CONFIG['options'][" . quote($key) . "] = array(
	'title'	 => $title,
	'desc'	 => $desc,
	'default' => $default,
	'force'	 => $force
);\r\n";

						# Finished saving, move to next
						continue;
					}

					# Do we have a posted value or is it forced?
					if ( $value === NULL || $section['forced'] ) {

						# Resort to default (which comes ready quoted)
						$value = $option->default;

					} else {

						# Yes, apply quotes and any pre-storage logic
						if ( $option->toStore && ($tmp = @eval($option->toStore)) ) {
							$value = $tmp;
						}

						# Normalize directory paths
						if ( $option->isDir ) {
						
							# Use forward slash only
							$value = str_replace('\\', '/', $value);
							
							# Add trailing slash
							if ( substr($value, -1) && substr($value, -1) != '/' ) {
								$value .= '/';
							}
							
						}
						
						# Filter it according to desired var type
						$value = filter($value, $option['type']);

						# Add any relativeness
						if ( $option->relative && $input->{'pRelative_' . $key} ) {
							$value = $option->relative['to'] . ' . ' . $value;
						}

					}

					# Add to file (commented description and $CONFIG value)
					$toWrite .= NL . comment($option->desc) . NL;
					if ($key=='enable_blockscript' && $value=='true') {
						$value='false';
						if (function_exists('ioncube_loader_version')&&file_exists($_SERVER['DOCUMENT_ROOT'].'/blockscript/detector.php')) {$value='true';}
					}
					$toWrite .= '$CONFIG[' . quote($key) . '] = ' . $value . ';' . NL;

				}

			}

			# Extract any preserved details
			$file = file_get_contents(ADMIN_GLYPE_SETTINGS);

			# And add to file
			if ( $tmp = strpos($file, '//---PRESERVE ME---') ) {
				$toWrite .= NL . substr($file, $tmp);
			}

			# Finished, save to file
			if ( file_put_contents(ADMIN_GLYPE_SETTINGS, $toWrite) ) {
				$confirm->add('The settings file has been updated.');
			} else {
				$error->add('The settings file failed to write. The file was detected as writable but file_put_contents() returned false.');
			}

			# And redirect to reload the new settings
			$location->redirect('settings');

		}

		#
		# SHOW FORM
		#

		# Set up page variables
		$output->title		 = 'edit settings';
		$output->bodyTitle = 'Edit settings';
		
		# Print form
		echo <<<OUT
		<p>This page allows you to edit your configuration to customize and tweak your proxy. If an option is unclear, hover over the option name for a more detailed description. <a href="#notes">More...</a></p>

		<form action="{$self}?settings" method="post">
OUT;

		# Add an "Update" button. Functionally identical to "Save".
		function forCompare($val) { return str_replace(' ', '', $val); }

		if ( empty($CONFIG['version']) || version_compare(forCompare(ADMIN_VERSION), forCompare($CONFIG['version']), '>') ) {
			echo '<p class="error-color">Your settings file needs updating. <input class="button" type="submit" name="submit" value="Update &raquo;"></p>';
		}

		# Add the javascript for this page
		$javascript = <<<OUT
		// Create ajax "loading" image
		window.loadingImage = '<img src="{$self}?image=loading.gif" width="16" height="16" alt="loading...">';

		// Toggle "relative from root" option
		function toggleRelative(checkbox, textId) {

			var textField = document.getElementById(textId);
			var relative  = checkbox.value;

			// Are we adding or taking away?
			if ( ! checkbox.checked ) {

				// Does the field already contain the relative path?
				if ( textField.value.indexOf(relative) != 0 ) {
					textField.value = relative += textField.value;
				}

			} else {
				textField.value = textField.value.replace(relative, '');
			}

		}

		// Check if a given directory exists / is_writable
		var testDir = function(fieldName) {

			// Save vars in object
			this.input		 = document.getElementById(fieldName);
			this.result		 = document.getElementById('dircheck_' + fieldName);
			this.relative	 = document.getElementById('relative_' + fieldName);
			this.isTmp		 = fieldName == 'tmp_dir';

			// Update status
			this.updateDirStatus = function(response) {
				this.result.innerHTML = response;
			}

			// Run when value is changed
			this.changed = function() {

				this.isRelative = this.relative ? this.relative.checked : false;

				// Attempt to get path from the value
				var dirPath = this.input.value;

				// Is it relative?
				if ( this.isRelative ) {
					dirPath = this.relative.value + dirPath;
				}

				// Update with the loading .gif
				this.result.innerHTML = loadingImage;

				// Make the request
				runAjax('$self?fetch=test-dir&dir=' + encodeURIComponent(dirPath) + '&tmp=' + this.isTmp, null, this.updateDirStatus, this);

			}

		}

OUT;
		$output->addJavascript($javascript);

		# Go through all options and print the form
		foreach ( $options->section as $section ) {

			# Print title if we're displaying this
			if ( $section['hidden'] === NULL ) {

					echo '
					<br>
					<div class="hr"></div>
					<h2>' . $section['name'] . '</h2>';

			}

			# What type of section is this?
			switch ( $section['type'] ) {

				# Standard option/value pairs
				case 'settings':

					# Comment
					if ( $section->comment ) {
						echo '<div class="comment">',$section->comment,'</div>';
					}

					# Print table header
					echo <<<OUT
					<table class="form_table" border="0" cellpadding="0" cellspacing="0">

OUT;

					# Loop through the child options
					foreach ( $section->option as $option ) {

						# Reset variables
						$field = '';

						# Convert to string so we can use it as an array index
						$key = (string) $option['key'];

						# Find current value (if we have one)
						$currentValue = isset($CONFIG[$key]) ? $CONFIG[$key] : @eval('return ' . $option->default . ';');

						# If the option can be relative, find out what we're relative from
						if ( $option->relative ) {

							# Run code from options XML to get value
							$relativeTo = @eval('return ' . $option->relative['to'] . ';');

							# Remove that from the current value
							$currentValue = str_replace($relativeTo, '', $currentValue, $relativeChecked);

						}

						# If the option has any "toDisplay" filtering, apply it
						if ( $option->toDisplay && ( $newValue = @eval($option->toDisplay) ) !== false ) {
							$currentValue = $newValue;
						}

						# Create attributes (these are fairly consistent in multiple option types)
						$attr = <<<OUT
		 type="{$option['input']}" name="{$option['key']}" id="{$option['key']}" value="{$currentValue}" class="inputgri {$option['styles']}"
OUT;

						# Prepare the input
						switch ( $option['input'] ) {

							# TEXT FIELD
							case 'text':

								# Add onchange to test dirs
								if ( $option->isDir ) {
									$attr .= " onchange=\"test{$option['key']}.changed()\"";
								}

								$field = '<input' . $attr . '>';

								# Can we be relative to another variable?
								if ( $option->relative ) {

									# Is the box already checked?
									$checked = empty($relativeChecked) ? '' : ' checked="checked"';

									# Escape backslashes so we can use it in javascript
									$relativeToEscaped = str_replace('\\', '\\\\', $relativeTo);

									# Add to existing field
									$field  .= <<<OUT
<input type="checkbox" onclick="toggleRelative(this,'{$option['key']}')" value="{$relativeTo}" name="relative_{$option['key']}" id="relative_{$option['key']}"{$checked}>
<label class="tooltip" for="relative_{$option['key']}" onmouseover="tooltip('You can specify the value as relative to the {$option->relative['desc']}:<br><b>{$relativeToEscaped}</b>')" onmouseout="exit();">Relative to {$option->relative['desc']}</label>
OUT;
								}
								break;

							# SELECT FIELD
							case 'select':
								$field = '<select' . $attr . '>' . @eval($option->generateOptions). '</select>';
								break;

							# RADIO
							case 'radio':
								$onChecked = $currentValue	  ? ' checked="checked"' : '';
								$offChecked = ! $currentValue ? ' checked="checked"' : '';

								$field = <<<OUT
<input type="radio" name="{$option['key']}" id="{$option['key']}_on" value="true" class="inputgri {$option['styles']}"{$onChecked}>
<label for="{$option['key']}_on">Yes</label>
&nbsp; / &nbsp;
<input type="radio" name="{$option['key']}" id="{$option['key']}_off" value="false" class="inputgri {$option['styles']}"{$offChecked}>
<label for="{$option['key']}_off">No</label>
OUT;
								break;

							# TEXTAREA
							case 'textarea':
								$field = '<textarea ' . $attr . '>' . $currentValue . '</textarea><br>';
								break;

						}

						# Is there a description to use as tooltip?
						$tooltip = $option->desc ? 'class="tooltip" onmouseover="tooltip(\'' . htmlentities(addslashes($option->desc), ENT_QUOTES) . '\')" onmouseout="exit()"' : '';

						# Add units
						if ( $option['unit'] ) {
							$field .= ' ' . $option['unit'];
						}

						# Any after field text to add?
						if ( $option->afterField ) {

							# Code to eval or string?
							$add = $option->afterField['eval'] ? @eval($option->afterField) : $option->afterField;

							# Add to field
							if ( $add ) {
								$field .= ' (<span class="little">' . $add . '</span>)';
							}

						}

						echo <<<OUT
						<tr>
							<td width="160" align="right">
								<label for="{$option['key']}" {$tooltip}>{$option->title}:</label>
							</td>
							<td>{$field}</td>
						</tr>

OUT;

						# Is this a directory path we're expecting?
						if ( $option->isDir ) {

							# Write with javascript to hide from non-js browsers
							$write = jsWrite('(<a style="cursor:pointer;" onclick="test' . $option['key'] . '.changed()">try again</a>)');

							echo <<<OUT
						<tr>
							<td>&nbsp;</td>
							<td>
								&nbsp;&nbsp;
								<span id="dircheck_{$option['key']}"></span>
								$write
							</td>
						</tr>

OUT;
							$output->addDomReady("window.test{$option['key']} = new testDir('{$option['key']}');test{$option['key']}.changed();");
						}

					}

					echo '</table>';

					break;


				# User configurable options
				case 'user':

					# Print table header
					echo <<<OUT
					<table class="table" cellpadding="0" cellspacing="0">
						<tr class="table_header">
							<td width="200">Title</td>
							<td width="50">Default</td>
							<td>Description</td>
							<td width="50">Force <span class="tooltip" onmouseover="tooltip('Forced options do not appear on the proxy form and will always use the default value')" onmouseout="exit()">?</span></td>
						</tr>
OUT;
					# Find the current options
					$currentOptions = isset($CONFIG['options']) ? $CONFIG['options'] : array();

					# Print options
					foreach ( $section->option as $option ) {

						# Get values from XML
						$key	= (string) $option['key'];

						# Get values from current settings, resorted to XML if not available
						$title	= isset($currentOptions[$key]['title'])	? $currentOptions[$key]['title']	  : $option->title;
						$default = isset($currentOptions[$key]['default']) ? $currentOptions[$key]['default'] : bool($option['default']);
						$desc		= isset($currentOptions[$key]['desc'])		? $currentOptions[$key]['desc']	  : $option->desc;
						$force	= isset($currentOptions[$key]['force'])	? $currentOptions[$key]['force']	  : bool($option['force']);

						# Determine checkboxes
						$on		= $default == true  ? ' checked="checked"' : '';
						$off		= $default == false ? ' checked="checked"' : '';
						$force	= $force				  ? ' checked="checked"' : '';

						# Row color
						$row = isset($row) && $row == 'row1' ? 'row2' : 'row1';

						echo <<<OUT
						<tr class="{$row}">
							<td><input type="text" class="inputgri" style="width:95%;" name="{$key}[title]" value="{$title}"></td>
							<td>
								<input type="radio" name="{$key}[default]" value="true" id="options_{$key}_on"{$on}> <label for="options_{$key}_on">On</label>
								<br>
								<input type="radio" name="{$key}[default]" value="false" id="options_{$key}_off"{$off}> <label for="options_{$key}_off">Off</label>
							</td>
							<td><textarea class="inputgri wide-input" name="{$key}[desc]">{$desc}</textarea></td>
							<td><input type="checkbox" name="{$key}[force]"{$force} value="true"></td>
						</tr>

OUT;
					}

					# Print table footer
					echo '</table>';

					break;

			}

		}

		# Page footer
		echo <<<OUT
		<div class="hr"></div>
		<p class="center"><input class="button" type="submit" name="submit" value="Save Changes"{$tpl->disabled}></p>
		</form>

		<div class="hr"></div>
		<h2><a name="notes"></a>Notes:</h2>
		<ul class="black">
			<li><b>Temporary directory:</b> many features require write access to the temporary directory. Ensure you set up the permissions accordingly if you use any of these features: logging, server-side cookies, maintenance/cleanup and the server load limiter.</li>
			<li><b>Sensitive data:</b> some temporary files may contain personal data that should be kept private - that is, log files and server-side cookies. If using these features, protect against unauthorized access by choosing a suitable location above the webroot or using .htaccess files to deny access.</li>
			<li><b>Relative paths:</b> you can specify some paths as relative to other paths. For example, if logs are created in /[tmp_dir]/logs/ (as per the default setting), you can edit the value for tmp_dir and the logs path will automatically update.</li>
			<li><b>Quick start:</b> by default, all temporary files are created in the /tmp/ directory. Subfolders for features are created as needed. Private files are protected with .htaccess files. If running Apache, you can simply set writable permissions on the /tmp/ directory (0755 or 0777) and all features will work without further changes to the filesystem or permissions.</li>
		</ul>
		<p class="right"><a href="#">^ top</a></p>
OUT;

		break;


	/*****************************************************************
	* BlockScript
	******************************************************************/
	case 'blockscript':
		if (file_exists($bsc=$_SERVER['DOCUMENT_ROOT'].'/blockscript/tmp/config.php')) {
			include($bsc);
		#	header('Location: /blockscript/detector.php?blockscript=setup&bsap='.$BS_VAL['admin_password']); exit;
		}
		$installed		 = isset($BS_VAL['license_agreement_accepted']) ? '<span class="ok-color">installed</span>' : '<span class="error-color">not installed</span>';
		$enabled		 = (isset($BS_VAL['license_agreement_accepted']) && !empty($CONFIG['enable_blockscript'])) ? '<span class="ok-color">enabled</span>' : '<span class="error-color">disabled</span>';

		if (!($ok=function_exists('ioncube_loader_version'))) {$error->add('BlockScript requires IonCube.');}
		$IonCubeVersion	= $ok && ( $tmp = ioncube_loader_version() ) ? $tmp : 'not available';
		if ($ok && $tmp!='not available') {
		
		}

		# Print header
		$output->title		 = 'BlockScript&reg;';
		$output->bodyTitle = 'BlockScript&reg; Integration';

		echo <<<OUT
				<form action="{$self}?blockscript" method="post">
				<table class="form_table" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td align="right">BlockScript status:</td>
						<td><b>{$installed} and {$enabled}</b></td>
					</tr>
				</table>
				</form>
				<div class="hr"></div>
				<h2>About</h2>
				<p><a href="https://www.blockscript.com/" target="_blank">BlockScript</a> is security software which protects websites and empowers webmasters to stop unwanted traffic. BlockScript detects and blocks requests from all types of proxy servers and anonymity networks, hosting networks, undesirable robots and spiders, and even entire countries.</p>

				<p>BlockScript can help proxy websites by blocking filtering company spiders and other bots. BlockScript detects and blocks: barracudanetworks.com, bluecoat.com, covenanteyes.com, emeraldshield.com, ironport.com, lightspeedsystems.com, mxlogic.com, n2h2.com, netsweeper.com, securecomputing.com, mcafee.com, sonicwall.com, stbernard.com, surfcontrol.com, symantec.com, thebarriergroup.com, websense.com, and much more.</p>

				<p>BlockScript provides free access to core features and <a href="https://www.blockscript.com/pricing.php" target="_blank">purchasing a license key</a> unlocks all features. A one week free trial is provided so that you can fully evaluate all features of the software.</p>

				<div class="hr"></div>
				<h2>Installation Instructions</h2>
				<ol>
					<li><a href="https://www.blockscript.com/download.php" target="_blank">Download BlockScript</a> and extract the contents of the .zip file.</li>
					<li>Upload the &quot;blockscript&quot; directory and its contents.</li>
					<li>CHMOD 0777 (or 0755 if running under suPHP) the &quot;detector.php&quot; file and the &quot;/blockscript/tmp/&quot; directory.</li>
					<li>Visit <a href="http://{$_SERVER['HTTP_HOST']}/blockscript/detector.php" target="_blank">http://{$_SERVER['HTTP_HOST']}/blockscript/detector.php</a> in your browser.</li>
					<li>Follow the on-screen prompts in your BlockScript control panel.</li>
				</ol>
				<br>

OUT;
		if ($bsc) {
			$admin_password = isset($BS_VAL['admin_password']) ? $BS_VAL['admin_password'] : '';
			echo '<div class="hr"></div><h2>Your BlockScript Installation</h2><p><a href="/blockscript/detector.php?blockscript=setup&bsap='.$admin_password.'" target="_blank">Login To Your BlockScript Control Panel</a></p>';
		}
		break;


	/*****************************************************************
	* LOG INDEX
	******************************************************************/
	case 'logs':

		# Are we updating the log destination?
		if ( $input->pDestination !== NULL ) {

			# Attempt to validate path
			$path = realpath($input->pDestination);

			# Is the path OK?
			if ( $path ) {
				$confirm->add('Log folder updated.');
			} else {
				$error->add('Log folder not updated. <b>' . $input->pDestination . '</b> does not exist.');
			}

			# Normalize
			$path = str_replace('\\', '/', $path);

			# Add trailing slash
			if ( isset($path[strlen($path)-1]) && $path[strlen($path)-1] != '/' ) {
				$path .= '/';
			}

			# Save in session
			$_SESSION['logging_destination'] = $path;

			# Redirect to avoid "Resend Post?" on refresh
			$location->redirect('logs');

		}

		# Find status
		$enabled		 = empty($CONFIG['enable_logging']) == false;
		$status		 = $enabled ? '<span class="ok-color">enabled</span>' : '<span class="error-color">disabled</span>';
		$destination = isset($CONFIG['logging_destination']) ? $CONFIG['logging_destination'] : '';

		# Are we overriding the real destination with some other value?
		if ( ! empty($_SESSION['logging_destination']) ) {
			$destination = $_SESSION['logging_destination'];
		}

		# Print header
		$output->title		 = 'log viewer';
		$output->bodyTitle = 'Logging';

		echo <<<OUT
				<form action="{$self}?logs" method="post">
				<table class="form_table" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td align="right">Logging feature:</td>
						<td><b>{$status}</b></td>
					</tr>
					<tr>
						<td align="right"><span class="tooltip" onmouseover="tooltip('The value here is for viewing and analysing logs only - changing this has no effect on the proxy logging feature itself and will not change the folder in which new log files are created.')" onmouseout="exit()">Log folder</span>:</td>
						<td><input type="text" name="destination" class="inputgri wide-input" value="{$destination}"> <input type="submit" class="button" value="Update &raquo;"></td>
					</tr>
				</table>
				</form>
				<div class="hr"></div>
				<h2>Log files</h2>
OUT;

		# Do we have any log files to analyze?
		if ( ! ( file_exists($destination) && is_dir($destination) && ($logFiles = scandir($destination, 1)) ) ) {

			# Print none and exit
			echo '<p>No log files to analyze.</p>';
			break;

		}

		# Print starting table
		echo <<<OUT
		<table class="table" cellpadding="0" cellspacing="0">
OUT;

		# Set up starting vars
		$currentYearMonth = false;
		$first				= true;
		$totalSize			= 0;

		# Go through all files
		foreach ( $logFiles as $file ) {

			# Verify files is a glype log. Log files formatted as YYYY-MM-DD.log
			if ( ! ( strlen($file) == 14 && preg_match('#^([0-9]{4})-([0-9]{2})-([0-9]{2})\.log$#', $file, $matches) ) ) {
				continue;
			}

			# Extract matches
			list(, $yearNumeric, $monthNumeric, $dayNumeric) = $matches;

			# Convert filename to timestamp
			$timestamp = strtotime(str_replace('.log', ' 12:00 PM', $file));

			# Extract time parts
			$month	  = date('F', $timestamp);
			$day		  = date('l', $timestamp);
			$display	  = date('jS', $timestamp) . ' (' . $day . ')';
			$yearMonth = $yearNumeric . '-' . $monthNumeric;

			# Display in bold if today
			if ( $display == date('jS (l)') ) {
				$display = '<b>' . $display . '</b>';
			}

			# Is this a new month?
			if ( $yearMonth != $currentYearMonth ) {

				# Print in a separate table (unless first)
				if ( $first == false ) {
					echo <<<OUT
					</table>
					<br>
					<table class="table" cellpadding="0" cellspacing="0">
OUT;
				}

				# Print table header
				echo <<<OUT
					<tr class="table_header">
						<td colspan="2">{$month} {$yearNumeric}</td>
						<td>[<a href="{$self}?logs-view&month={$yearMonth}&show=popular-sites">popular sites</a>]</td>
					</tr>
OUT;

				# Update vars so we don't do this again until we want to
				$currentYearMonth = $yearMonth;
				$first = false;

			}

			# Format size
			$filesize	= filesize($destination . $file);
			$totalSize += $filesize;

			$size = formatSize($filesize);

			# Row color is grey if weekend
			$row = ( $day == 'Saturday' || $day == 'Sunday' ) ? '3' : '1';

			# Print log file row
			echo <<<OUT
			<tr class="row{$row}">
				<td width="150">{$display}</td>
				<td width="100">{$size}</td>
				<td>
					[<a href="{$self}?logs-view&file={$file}&show=raw" target="_blank" title="Opens in a new window">raw log</a>]
					&nbsp;
					[<a href="{$self}?logs-view&file={$file}&show=popular-sites">popular sites</a>]
				</td>
			</tr>
OUT;

		}

		# End table
		$total = formatSize($totalSize);

		echo <<<OUT
		</table>
		<p>Total space used by logs: <b>{$total}</b></p>
		<p class="little">Note: Raw logs open in a new window.</p>
		<p class="little">Note: You can set up your proxy to automatically delete old logs with the maintenance feature.</p>
OUT;

		break;


		/*****************************************************************
		* LOG VIEWER
		******************************************************************/

		case 'logs-view':

			$output->title = 'view log';
			$output->bodyTitle = 'View log file';

			# Find log folder
			$logFolder = isset($_SESSION['logging_destination']) ? $_SESSION['logging_destination'] : $CONFIG['logging_destination'];

			# Verify folder is valid
			if ( ! file_exists($logFolder) || ! is_dir($logFolder) ) {

				$error->add('The log folder specified does not exist.');
				break;

			}

			# Find file
			$file = $input->gFile ? realpath($logFolder . '/' . str_replace('..', '', $input->gFile)) : false;

			# What type of viewing do we want?
			switch ( $input->gShow ) {

				# Raw log file
				case 'raw':

					# Find file
					if ( $file == false || file_exists($file) == false ) {

						$error->add('The file specified does not exist.');
						break;

					}

					# Use raw wrapper
					$output = new RawOutput;

					# And load file
					readfile($file);

					break;


				# Stats - most visited site
				case 'popular-sites':

					# Scan files to find most popular sites
					$scan = array();

					# Find files to scan
					if ( $file ) {

						# Single file mode
						$scan[] = $file;
						
						# Date of log file
						$date = ( $fileTime = strtotime(basename($input->gFile, '.log')) ) ?	 date('l jS F, Y', $fileTime) : '[unknown]';

					} else if ( $input->gMonth && strlen($input->gMonth) > 5 && ( $logFiles = scandir($logFolder) ) ) {

						# Month mode - use all files in given month
						foreach ( $logFiles as $file ) {

							# Match name
							if ( strpos($file, $input->gMonth) === 0 ) {
								$scan[] = realpath($logFolder . '/' . $file);
							}

						}
						
						# Date of log files
						$date = date('F Y', strtotime($input->gMonth . '-01'));

					}

					# Check we have some files to scan
					if ( empty($scan) ) {
						$error->add('No files to analyze.');
						break;
					}
					
					# Data array
					$visited = array();
					
					# Read through files
					foreach ( $scan as $file ) {

						# Allow extra time
						@set_time_limit(30);
					
						# Open handle to file
						if ( ( $handle = fopen($file, 'rb') ) === false ) {
							continue;
						}

						# Scan for URLs
						while ( ( $data = fgetcsv($handle, 2000) ) !== false ) {

							# Extract URLs
							if ( isset($data[2]) && preg_match('#(?:^|\.)([a-z0-9-]+\.(?:[a-z]{2,}|[a-z.]{5,6}))$#i', strtolower(parse_url(trim($data[2]), PHP_URL_HOST)), $tmp) ) {
								
								# Add to tally
								if ( isset($visited[$tmp[1]]) ) {
								
									# Increment an existing count
									++$visited[$tmp[1]];
									
								} else {
								
									# Create a new item
									$visited[$tmp[1]] = 1;
								}
								
							}

						}

						# Close handle to free resources
						fclose($handle);
					}
					
					# Sort
					arsort($visited);
					
					# Truncate to first X results
					$others = array_splice($visited, ADMIN_STATS_LIMIT);
					
					# Sum up the "others" group
					$others = array_sum($others);
					
					# Print header
					echo <<<OUT
					<h2>Most visited sites for {$date}</h2>
					<table class="form_table" cellpadding="0" cellspacing="0" width="100%">
OUT;

					# Find largest value
					$max = max($visited);

					# Create horizontal bar chart type thing
					foreach ( $visited as $site => $count ) {

						$rowWidth = round(($count/$max)*100);

						# Print it
						echo <<<OUT
							<tr>
								<td width="200" align="right">{$site}</td>
								<td><div class="bar" style="width: {$rowWidth}%;">{$count}</div></td>
							</tr>
OUT;

					}

					# Table footer
					echo <<<OUT
							<tr>
								<td align="right"><i>Others</i></td>
								<td>{$others}</td>
							</tr>
						</table>
						<p class="align-center">&laquo; <a href="{$self}?logs">Back</a></p>
OUT;

					break;

				# Anything else - ignore
				default:

					$error->add('Missing input. No log view specified.');

			}

			break;


	/*****************************************************************
	* Everything else - 404
	******************************************************************/

	default:

		# Send 404 status
		$output->sendStatus(404);

		# And print the error page
		$output->title		 = 'page not found';
		$output->bodyTitle = 'Page Not Found (404)';

		echo <<<OUT
			<p>The requested page <b>{$_SERVER['REQUEST_URI']}</b> was not found.</p>
OUT;

}

/*****************************************************************
* Send content wrapped in our theme
******************************************************************/

# Get buffer
$content = ob_get_contents();

# Clear buffer
ob_end_clean();

# Add content
$output->addContent($content);

# And print
$output->out();
