<?php
/*
 * pagewatch.php is a simple script that sends a notification email whenever a website has changed.
 * pagewatch.json holds the configuration of what links to check and where to send mails etc.
 *
 */

// Load configuration
$cfgFile = 'pagewatch.json';
$watchlist = loadJson($cfgFile);

// Format of an entry in the configuration file
$defaultWatchlistEntry = array(
	'url' 			=> null,
	'subject' 		=> 'The website you are watching has changed',
	'message' 		=> "The contents on\n".'${url}'."\nhas changed.", // ${url} will be replaced by they value associated with the key 'url' in this array.
	'subscribers' 	=> null,
	'hash' 			=> '',
	'timestamp' 	=> time(),
);

// Loop over all entries in the watchlist
for($i =0; $i<sizeof($watchlist); $i++) {
	$watch = $watchlist[$i];
	$watch = array_merge($defaultWatchlistEntry, $watch); // add missing keys to this entry in the configuration

	$url 	  	 = $watch['url'];
	$subject  	 = $watch['subject'];
	$message  	 = $watch['message'];
	$subscribers = $watch['subscribers'];
	$hash_prev 	 = $watch['hash'];

	// load page contents and strip the unessential
	$contents = file_get_contents($url);
	$contents = stripHtmlComments($contents);
	$contents = stripJavaScript($contents);
	$contents = stripWhitespaces($contents);
	$contents = trim($contents);
	$hash_now = sha1($contents);

	// Update watchlist entry when something changed
	if ($hash_now != $hash_prev) {
		logmsg("Contents on '$url' has changed.");
		$watch['hash']      = $hash_now;
		$watch['timestamp'] = time();

		// prepare the email message
		$msg = fillTemplate($message, $watch);
		logmsg("Prepared message: $msg");

		// send mail to all subsribers
		foreach (explode(',', $subscribers) as $to) {
			logmsg("Sending mail '$subject' to $to.");
			sendMail($to, $subject, $msg);
		}
	}	

	$watchlist[$i] = $watch;
}

// save updated watchlist back to disk
saveJson($cfgFile, $watchlist);

exit(0);

// ************************
// *** Helper functions ***
// ************************

function loadJson($filename) {
	return json_decode(file_get_contents($filename), true);
}

function saveJson($filename, array &$data) {
	return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
}

function stripJavaScript($string) {
	return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $string);
}

function stripWhitespaces($string) {
	return preg_replace('/\s+/', '', $string);
}

function stripHtmlComments($string) {
	return preg_replace('/<!--(.|\s)*?-->/', '', $string);
}

function fillTemplate($template, array &$searchAndReplace) {
	return str_replace(
		array_map("convertToTemplateTag", array_keys($searchAndReplace)),
		$searchAndReplace,
		$template
	);
}

function convertToTemplateTag($key) {
	return '${'.$key.'}';
}

function sendMail($to, $subject, $message) {
	$headers = "From: ".$to;
	return @mail($to, $subject, $message, $headers);
}

function logmsg($msg) {
	echo "LOG: $msg<br>\r\n";
}

?>