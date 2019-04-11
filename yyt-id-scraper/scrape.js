var syncRequest = require('sync-request');
var cheerio = require('cheerio');

var fs = require("fs");
var text = fs.readFileSync("sets.txt").toString('utf-8');
var sets = text.split("\r\n");
var stream = fs.createWriteStream("ids.txt", {flags:'w'});

console.time("Total Execution Time");
// Iterate through each set
sets.forEach(function(ele) {
	console.time(ele);

	var res = syncRequest('GET', 'http://yuyu-tei.jp/game_ws/sell/sell_price.php?ver=' + ele);
	var $ = cheerio.load(res.getBody('utf-8'), { decodeEntities: false });
	
	$('li.card_unit').each(function(i, element) {		
		// Get Card Information
		var cardid = $(this).find('p.id').text().trim();

		stream.write(cardid + "\n");
	});

	console.timeEnd(ele);
});

stream.end();
console.timeEnd("Total Execution Time");