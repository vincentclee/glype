<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2016 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

define('high_quality', true);

$CONFIG['transfer_timeout'] = 3600;
$options['stripJS'] = true;
$options['stripObjects'] = true;
$options['allowCookies'] = false;
$CONFIG['max_filesize'] = 209715200;
$CONFIG['resume_transfers'] = false;
$CONFIG['queue_transfers'] = false;

function preParse($input, $type) {
	switch($type) {
		case 'html':
			if (preg_match('#url_encoded_fmt_stream_map["\']:\s*["\']([^"\'\s]*)#', $input, $url_encoded_fmt_stream_map)) {
				$fmt_maps = explode(',', $url_encoded_fmt_stream_map[1]);
				if (!high_quality) {$fmt_maps = array_reverse($fmt_maps);}
				foreach ($fmt_maps as $fmt_map) {
					if (strpos($fmt_map,'x-flv')===false) {continue;}
					preg_match("/url=([^\\\\]*)/", $fmt_map, $yt_url);
					$yt_url[1] = urldecode($yt_url[1]);
					if (!$yt_url[1]) {continue;}
					define('videourl', $yt_url[1]);
					break;
				}
			}

			# Remove homepage advertisements
			$input = preg_replace('#<div id="ad_creative_.*?<\/div>#s','',$input, 4);

		break;
	}

	# fix thumbnail images
	$input = preg_replace('#<img[^>]*data-thumb=#s','<img alt="Thumbnail" src=',$input);

	# fix malformed links
	$input = preg_replace('#"href="#s','" href="',$input);

	return $input;
}

function postParse($input, $type) {
	switch($type) {
		case 'html':

			if(defined('videourl')) {

				# Create URL to JW Player
				$player_url = GLYPE_URL . '/player.swf';
				# Generate URL to flv file through proxy script

				$flvUrl = rawurlencode(proxyURL(sprintf('%s',videourl)));	 
				# Generate HTML for the flash object with our new FLV URL
				$html = "<embed src=\"{$player_url}\" width=\"640\" height=\"360\" bgcolor=\"000000\" allowscriptaccess=\"always\" allowfullscreen=\"true\" type=\"application/x-shockwave-flash\" flashvars=\"width=640&height=360&type=video&fullscreen=true&volume=100&autostart=true&file=$flvUrl\" />";

				# Replace video player
				$input = preg_replace('#<div id="player-api"([^>]*)>.*<div class="clear"#s', '<div id="player-api"$1>'.$html.'</div></div><div class="clear"', $input, 1);
			}

		break;
	}

	return $input;
}
?>