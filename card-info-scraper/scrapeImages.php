<?php
set_time_limit(0);
header('content-type: text/html; charset=utf-8');

// Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
//Flush (send) the output buffer and turn off output buffering
while (@ob_end_flush());
// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);

// Connect to database
require_once "simple_html_dom.php";
require_once "meekrodb.2.3.class.php";
DB::$user = "root";
DB::$password = "Drag0nk!tty5";
DB::$dbName = "cards_db";
DB::$encoding = "utf8";


// Get IDs
$results = DB::query("SELECT card_id FROM ws_cards");

// Iterate through each set
foreach($results as $row) {
	$id = strtolower($row['card_id']);
	$letter = $id[0];

	$parts = explode('/', $id);
	$parts2 = explode('-', $parts[1]);

	$series_code = $parts[0];
	$set_code = $parts2[0];
	$card_id = $parts2[1];

	// Check if ID is empty
	if(empty($id)) { continue; }

	// Get initial timer
	$time_pre = microtime(true);

	// Split ID into URL
	$url = "https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/";
	$url .= $letter . '/';
	$url .= $series_code . '_' . $set_code . '/';
	$url .= $series_code . '_' . $set_code . '_' . $card_id . '.gif';

	// Image path
	$img = "images/" . $series_code . '_' . $set_code . '_' . $card_id . '.jpg';

	// Check if image exists
	if(!file_exists($img)) {
		$img_string = @file_get_contents($url);
		if($img_string === FALSE) { continue; }

		// Get image raw
		$img_raw = imagecreatefromstring($img_string);
		if($img_raw === FALSE) { continue; }

		// Save image
		imageinterlace($img_raw, true);
		$res = imagejpeg($img_raw, $img);

		// Check if successfully saved
		if($res) {
			echo "Downloaded $id -- ";

			$time_post = microtime(true);
			$exec_time = $time_post - $time_pre;
			echo $exec_time . "\r\n";
		}

		imagedestroy($img_raw);
	}

	flush();
}