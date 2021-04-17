<?php
	/*include_once('classes/db_classes.php');
	$mysql = new MySQL('localhost', 'jipatas_bmg', 'fduBB6KQ', 'jipatas_badminger');

	define('CLIENT_ID', 'Aklq5hpBCffkIqRvfDNKGp');
	define('CLIENT_SECRET', 'O3H3GWCszX8KvQiPETQRljDnSkdALANeHAt2XGlhBBH');
	define('LINE_API_URI', 'https://notify-bot.line.me/oauth/token');
	define('CALLBACK_URI', 'http://www.ibadclub.com/badminger/line_callback.php');

	parse_str($_SERVER['QUERY_STRING'], $queries);

	$fields = [
		'grant_type' => 'authorization_code',
		'code' => $queries['code'],
		'redirect_uri' => CALLBACK_URI,
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET
	];

	try {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, LINE_API_URI);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$res = curl_exec($ch);
		curl_close($ch);

		if ($res == false)
			throw new Exception(curl_error($ch), curl_errno($ch));

		$json = json_decode($res);

		var_dump($json);
	} catch(Exception $e) {
		var_dump($e);
	}*/
?>