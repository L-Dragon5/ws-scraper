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
require_once "meekrodb.2.3.class.php";
DB::$user = "root";
DB::$password = "";
DB::$dbName = "cardsv2";
DB::$encoding = "utf8";

$txtfile = file_get_contents('http://www.heartofthecards.com/translations/hotcwsappdata174fv.txt');

foreach( explode("^^^^^^", $txtfile) as $line )
{
	$entry = explode("=====", $line);
	$cardNo = trim($entry[0]);
	$cardJpnName = trim($entry[1]);
	$cardType = trim($entry[2]);
	$cardEngName = trim($entry[3]);
	
	$entry[4] = trim($entry[4]);
	$entry[5] = trim($entry[5]);
	
	if(strcmp($entry[4], "No Keyword1") == 0)
		$cardTraits = "-- No Keywords --";
	else if(strcmp($entry[5], "No Keyword2") == 0)
		$cardTraits = "::" . $entry[4] . "::";
	else
		$cardTraits = "::" . $entry[4] . "::    ::" . $entry[5] . "::";
		
		
	$cardEngText = trim($entry[6]);
	$cardEngText = str_replace("[B]", "", $cardEngText);
	$cardEngText = str_replace("[/B]", "", $cardEngText);
	
	if(strcmp($cardEngText, "[No Card Text]") == 0)
		$cardEngText = "---No Card Text---";
  
  
  // Card array to update values
  $card = array(
    'card_eng_name' => $cardEngName,
    'card_jpn_name' => $cardJpnName,
    'card_eng_text' => $cardEngText,
    'card_type' => $cardType,
    'card_traits' => $cardTraits
  );
  
  DB::update('cards', $card, 'card_id LIKE %ss', $cardNo);
  
  echo "Updated " . $cardNo . "<br>";
  echo str_pad("",1024," ");
  echo "<br />";

  flush();
}
?>