<?php
/*******************************************************************
* Glype is copyright and trademark 2007-2012 UpsideOut, Inc. d/b/a Glype
* and/or its licensors, successors and assigners. All rights reserved.
*
* Use of Glype is subject to the terms of the Software License Agreement.
* http://www.glype.com/license.php
******************************************************************/

$toSet[CURLOPT_TIMEOUT] = 3600;
$options['stripJS'] = true;
$options['stripObjects'] = true;
$CONFIG['max_filesize'] = 209715200;
$CONFIG['queue_transfers'] = false;

function preParse($input, $type) {
	switch($type) {
		case 'html':
			if (preg_match('#url_encoded_fmt_stream_map["\']:\s*["\']([^"\'\s]*)#', $input, $stream_map)) {
				$urls = preg_split('/url=/', $stream_map[1]);
				foreach ($urls as $url) {
					$url = urldecode($url);
				
					if (strpos($url,'video/x-flv')===false) {continue;}
					if (strpos($url,'quality=medium')===false) {continue;}
				#	if (strpos($url,'quality=small')===false) {continue;}
				
					$url = preg_replace('#;.*$#', '', $url);
					$url = preg_replace('#,.*$#', '', $url);
					$url = str_replace('sig','signature',$url);
					$url = str_replace('\u0026amp;', '&', $url);
					$url = str_replace('\u0026', '&', $url);
				
					define('videourl', $url);
					break;
				}
			}

			# Remove noscript message
			$input = preg_replace('/'.preg_quote('<noscript>Hello, you either have JavaScript turned off or an old version of Adobe\'s Flash Player. <a href="http://www.adobe.com/go/getflashplayer/">Get the latest Flash player</a>.</noscript>','/').'/s','',$input);
			$input = preg_replace('/'.preg_quote('yt.www.watch.player.write("watch-player-div", false, null, null, "100%", "100%");').'/s','',$input);
			$input = preg_replace('/'.preg_quote('document.write(\'Hello, you either have JavaScript turned off or an old version of Adobe\\\'s Flash Player. <a href=\\"http://www.adobe.com/go/getflashplayer/\\">Get the latest Flash player</a>.\');','/').'/s','',$input);
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
			$input = preg_replace('#<div id="watch-video-container">(.*?)</div>#s', '<div id="watch-video-container">', $input, 1);
			$input = preg_replace('#<div id="watch-video-container">(.*?)</div>#s', '<div id="watch-video-container">', $input, 1);
			$input = preg_replace('#<div id="watch-video-container">(.*?)</div>#s', '<div id="watch-video-container"><div id="watch-video" class=" "><script>if \(window.yt.timing\) \{yt.timing.tick\(\'bf\', \'\'\)\;\}</script><div id="watch-player" class="flash-player">' . $html .'</div></div></div><div id="watch-main"></div>', $input, 1);
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