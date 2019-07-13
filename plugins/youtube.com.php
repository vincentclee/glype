<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2013 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

define('high_quality', true);

$toSet[CURLOPT_TIMEOUT] = 3600;
$options['stripJS'] = true;
$options['stripObjects'] = true;
$options['allowCookies'] = false;
$CONFIG['max_filesize'] = 209715200;
$CONFIG['resume_transfers'] = false;
$CONFIG['queue_transfers'] = false;

function preParse($input, $type) {
	switch($type) {
		case 'html':
			if (preg_match('#url_encoded_fmt_stream_map["\']:\s*["\']([^"\'\s]*)#', $input, $stream_map)) {
				define('stream_map', $stream_map[1]);
				preg_match("/^([a-z0-9_]*=)/i", $stream_map[1], $yt_sep);
				$urls = preg_split('/'.$yt_sep[1].'/', $stream_map[1]);
				if (!high_quality) {$urls = array_reverse($urls);}
				foreach ($urls as $url) {
					$url = urldecode($url);
					$url = str_replace('\u0026', '&', $url);

					if (strpos($url,'video/x-flv')===false) {continue;}
				
					$url = preg_replace('#;.*$#', '', $url);
					$url = preg_replace('#,.*$#', '', $url);

					if ($yt_sep[1]=='sig=') {
						preg_match("/^([^&]*)/", $url, $yt_sig);
						$url.='&signature='.$yt_sig[1];
					} else {
						preg_match("/sig=([^&]*)/", $url, $yt_sig);
						$url.='&signature='.$yt_sig[1];
					}

					if ($yt_sep[1]=='itag=') {
						preg_match("/^([^&]*)/", $url, $yt_itag);
						$url = preg_replace('#itag=[^&]*&#', '', $url);
						$url = preg_replace('#&itag=[^&]*#', '', $url);
						$url.='&itag='.$yt_itag[1];
					} else {
						preg_match("/itag=([^&]*)/", $url, $yt_itag);
						$url = preg_replace('#&itag=[^&]*#', '', $url);
						$url.='&itag='.$yt_itag[1];
					}

				#	$url = preg_replace('#&fallback_host=[^&]*#', '', $url);
					$url = preg_replace('#^.*url=#', '', $url);

					define('videourl', $url);
					break;
				}
			}

			# Remove noscript message
			$input = preg_replace('/'.preg_quote('<noscript>Hello, you either have JavaScript turned off or an old version of Adobe\'s Flash Player. <a href="http://www.adobe.com/go/getflashplayer/">Get the latest Flash player</a>.</noscript>','/').'/s','',$input);
			$input = preg_replace('/'.preg_quote('yt.www.watch.player.write("watch-player-div", false, null, null, "100%", "100%");').'/s','',$input);
			$input = preg_replace('/'.preg_quote('document.write(\'Hello, you either have JavaScript turned off or an old version of Adobe\\\'s Flash Player. <a href=\\"http://www.adobe.com/go/getflashplayer/\\">Get the latest Flash player</a>.\');','/').'/s','',$input);

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
			if(!defined('videourl')) {return $input;}

			# Create URL to JW Player
			$player_url = GLYPE_URL . '/player.swf';
			# Generate URL to flv file through proxy script

			$flvUrl = rawurlencode(proxyURL(sprintf('%s',videourl)));	 
			# Generate HTML for the flash object with our new FLV URL
			$html = "<embed src=\"{$player_url}\" width=\"640\" height=\"360\" bgcolor=\"000000\" allowscriptaccess=\"always\" allowfullscreen=\"true\" type=\"application/x-shockwave-flash\" flashvars=\"width=640&height=360&type=video&fullscreen=true&volume=100&autostart=true&file=$flvUrl\" />";

			# Add our own player into the player div
		#	$input = preg_replace('#<div id="player".*?</div>.*?</div>#s', '<div id="player"><div id="player-api" class="player-width player-height">' . $html .'</div></div>', $input, 1);
			$input = preg_replace('#<div id="player".*?<div id="watch7-main-container">#s', '<div id="player"><div id="player-api" class="player-width player-height off-screen-target" style="overflow: hidden;">' . $html .'</div></div><div id="watch7-main-container">', $input, 1);

			$input = preg_replace('#http:\\\/\\\/s.ytimg.com\\\/yt\\\/swf\\\/watch-vfl157150.swf\\\#s','' . $player_url . '\\',$input, 1);
			$input = preg_replace('#http:\\\/\\\/s.ytimg.com\\\/yt\\\/swf\\\/watch-vfl157150.swf\\\#s','' . $player_url . '\\',$input, 1);
			$input = preg_replace('#http://s.ytimg.com/yt/swf/watch-vfl157150.swf#s','' . $player_url . '',$input, 1);
			$input = preg_replace('#http://s.ytimg.com/yt/swf/watch-vfl127661.swf#s','' . $player_url . '',$input, 1);
			$input = preg_replace('#http://s.ytimg.com/yt/swf/watch_as3-vfl128003.swf#s','' . $player_url . '',$input, 1);
			$input = preg_replace('#http://s.ytimg.com/yt/swf/watch_v8-vfl127661.swf#s','' . $player_url . '',$input, 1);
			$input = preg_replace('#http://s.ytimg.com/yt/swf/watch-vfl142129.swf#s','' . $player_url . '',$input, 1);
			$input = preg_replace('#http://s.ytimg.com/yt/swf/watch_v8-vfl142129.swf#s','' . $player_url . '',$input, 1);
		break;
	}
	return $input;
}
?>