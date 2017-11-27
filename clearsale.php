<?php

$CLEARSALE_TOKEN = 'YOUR_ENTITY_CODE';
$CLEARSALE_URL = str_replace('__TOKEN__', $CLEARSALE_TOKEN, 'http://www.clearsale.com.br/integracaov2/service.asmx/CheckOrderStatus?entityCode=__TOKEN__&pedidoIDCliente=');

$DB_ORDER_TABLE = 'TABLE_NAME';
$DB_ORDER_ID = 'FIELD_ORDER_ID_NAME';
$CLEARSALE_DB_SCORE = 'FIELD_CLEARSALE_SCORE';
$CLEARSALE_DB_STATUS = 'FIELD_CLEARSALE_STATUS';

$DB_HOST = 'DB_HOST';
$DB_USER = 'DB_USER';
$DB_PASSWORD = 'DB_PASSWORD';
$DB_DATABASE = 'DB_DATABASE';

$ids = "1111,2222,3333,4444,5555";

$link = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE);

if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    return;
}

echo "database connected." . PHP_EOL;

$orders = explode(",", $ids);

foreach ($orders as $value) {

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $CLEARSALE_URL . $value,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_CUSTOMREQUEST => "GET",
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error :" . $err;
	  return;
	}

	$xml = simplexml_load_string($response);
	preg_match('/<ID>(\d+)<\/ID><Score>(.+)<\/Score>/', $xml[0], $group);

	if(empty($group[2])){
		echo 'ERRO: order: ' . $value ." dont have score" . PHP_EOL;
		continue;
	}

	echo 'updating order ' . $value . ' score: ' . number_format($group[2], 2, '.', '') . ' ...' . PHP_EOL;
	$sql = ' UPDATE '. $DB_ORDER_TABLE .' SET '. $CLEARSALE_DB_SCORE . '=' . number_format($group[2], 2, ".", "") . ', ' . $CLEARSALE_DB_STATUS . '="1" WHERE ' . $DB_ORDER_ID . ' = ' . $value . '';
	
	if (!mysqli_query($link, $sql)) {
	  echo "Error updating record: " . mysqli_error($link) . PHP_EOL;
      continue;
    }

    echo "Record updated successfully" . PHP_EOL;
	
}

mysqli_close($link);
echo "Update completed";

