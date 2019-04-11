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
DB::$password = "";
DB::$dbName = "database";
DB::$encoding = "utf8";

function doesCardExist($id) {
	$res = DB::queryFirstRow("SELECT * FROM ws_cards WHERE card_id=%s", $id);

	// If exists, return true
	if(DB::count() > 0) { return true; }
	else { return false; }
}

function replaceColor($val) {
	$color = $val;
	$color = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/yellow.gif',
		'Yellow', $color);
	$color = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/green.gif',
		'Green', $color);
	$color = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/red.gif',
		'Red', $color);
	$color = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/blue.gif',
		'Blue', $color);
	
	return $color;
}

function replaceSide($val) {
	$side = $val;
	$side = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/w.gif',
		'Weiss', $side);
	$side = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/s.gif',
		'Schwarz', $side);

	return $side;
}

function replaceImage($val) {
	$str = $val;
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/soul.gif',
		'Soul', $str);
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/shot.gif',
		'Shot', $str);
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/gate.gif',
		'Gate', $str);
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/bounce.gif',
		'Bounce', $str);
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/treasure.gif',
		'Treasure', $str);
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/salvage.gif',
		'Salvage', $str);
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/standby.gif',
		'Standby', $str);
	$str = str_replace('https://s3-ap-northeast-1.amazonaws.com/static.ws-tcg.com/wordpress/wp-content/cardimages/_partimages/draw.gif',
		'Draw', $str);

	return $str;
}

// Get array of IDs
$ids = explode(PHP_EOL, file_get_contents('ids.txt'));

// Iterate through each set
foreach($ids as $id) {
	$time_pre = microtime(true);

	// Check if card exists
	if(doesCardExist($id)) { continue; }

	// Connect to webpage
	$url = 'https://ws-tcg.com/cardlist?cardno=' . $id;
	$html = file_get_html($url);
	
	// Check if card information table is present
	$table = $html->find('table.card-detail-table');
	if(!empty($table)) {
		$table = $html->find('table.card-detail-table')[0];
		// Get unnecessary info to remove
		$kana = $table->find('span.kana')[0]->plaintext;

		// Get Card Information
		$name = str_replace($kana, '', $table->find('td', 1)->plaintext);
		$card_id = $table->find('td', 2)->plaintext;
		$rarity = $table->find('td', 3)->plaintext;
		$set = $table->find('td', 4)->plaintext;
		$side = replaceSide($table->find('td', 5)->find('img')[0]->src);
		$code = $table->find('td', 7)->plaintext;

		$type = $table->find('td', 8)->plaintext;
			$type = str_replace('キャラ', 'Character', $type);
			$type = str_replace('イベント', 'Event', $type);
			$type = str_replace('クライマックス', 'Climax', $type);

		$color = replaceColor($table->find('td', 9)->find('img')[0]->src);
		$level = $table->find('td', 10)->plaintext;
		if($level == '-') $level = 0;

		$cost = $table->find('td', 11)->plaintext;
		if($cost == '-') $cost = 0;
		
		$power = $table->find('td', 12)->plaintext;
		if($power == '-') $power = 0;

		$soul = $table->find('td', 13)->find('img');
		if(!empty($soul)) {
			$soul_one = replaceImage($table->find('td', 13)->find('img')[0]->src);
			if(!empty($table->find('td', 13)->find('img')[1])) {
				$soul_two = replaceImage($table->find('td', 13)->find('img')[1]->src);
			} else {
				$soul_two = '';
			}

			$soul = $soul_one . $soul_two;
			$soul = substr_count($soul, 'Soul');
		} else {
			$soul = '0';
		}
		
		$trigger = $table->find('td', 14)->find('img');
		if(!empty($trigger)) {
			$trigger_one = replaceImage($table->find('td', 14)->find('img')[0]->src);
			if(!empty($table->find('td', 14)->find('img')[1])) {
				$trigger_two = replaceImage($table->find('td', 14)->find('img')[1]->src);
			} else {
				$trigger_two = '';
			}

			$trigger = $trigger_one . $trigger_two;
		} else {
			$trigger = "None";
		}
		
		$traits = $table->find('td', 15)->plaintext;
			$traits = explode('・', $traits);
			$trait_one = $traits[0];
			if($trait_one == '-') $trait_one = "None";

			$trait_two = $traits[1];
			if($trait_two == '-') $trait_two = "None";

		$text = str_replace('：', '', $table->find('td', 16)->plaintext);
		$flavor = $table->find('td', 17)->plaintext;

		// Card information
		// Add to final array
		$card = array(
			'card_id'=> $card_id,
			'name' => $name,
			'rarity' => $rarity,
			'set_name' => $set,
			'side' => $side,
			'set_code' => $code,
			'type' => $type,
			'color' => $color,
			'level' => $level,
			'cost' => $cost,
			'power' => $power,
			'soul' => $soul,
			'trigger_icon' => $trigger,
			'trait_one' => $trait_one,
			'trait_two' => $trait_two,
			'text' => $text,
			'flavor' => $flavor,  
		);

		// Insert into array
		DB::insert('ws_cards', $card);

		echo "Inserted $id into DB - ";
	}

	$time_post = microtime(true);
	$exec_time = $time_post - $time_pre;
	echo $exec_time . "<br />";

	flush();
}