<?php

session_start();

function __autoload($className) {
	if (file_exists("rest_$className.php")) {
		require_once "rest_$className.php";
	} else if (file_exists("$className.php")) {
		require_once "$className.php";
	} else {
		user_error("There is no $className", 'E_ERROR');
	}
}

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
	$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

require_once('class.dbEngineBase.php');

try {
$db_connector=new dbEngineBase('localhost','geo_rest_test','geo_rest_test','2H9d2P7l');
} catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}



try {

	$API = new restServer($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	/* register objects */
	$API->register('events');
	$API->register('users');
	

	/* process API */
	echo $API->processAPI();
} catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}

echo "\n";
