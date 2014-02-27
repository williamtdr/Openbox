<?php
/*
Openbox Game Server
-------------------
Copyright 2013 williamtdr // William Teder. Licence is proprietary.
*/

require 'vendor/autoload.php'; // load reactPHP

/*  DEFINES  */
define(VERSION,"0.1");

/* Global Variables */
$stdin = fopen('php://stdin', 'r');
$settings = array();
$unformatted_settings = array();

function stop() {
	echo("Stoppping the server...");
	fclose($GLOBALS['stdin']);
	die();
}

function read_settings() {
	if(file_exists("server.properties")) {
		$GLOBALS['unformatted_settings'] = file_get_contents("server.properties");
	} else {
		$default_config = array(
			"server-ip=0.0.0.0\n",
			"server-port=6511\n",
			"server-name=Openbox Game Server v".VERSION."\n"
		);
		if(file_put_contents("server.properties",$default_config) == false) {
			echo("Couldn't write to server.properties, exiting.");
			stop();
		}
		$GLOBALS['unformatted_settings'] = file_get_contents("server.properties");
	}
	foreach($GLOBALS['unformatted_settings'] as $line) {
		if(stristr($line, "server-ip=") && strlen($line != 9)) {
			$str = trim($line, "\n\tserver-ip=");
			$GLOBALS['settings']['ip'] = $str;
		} elseif(stristr($line, "server-port=") && strlen($line != 11) {
			$str = trim($line, "\n\tserver-port=");
			$int = intval($str);
			if($int > -1 && $int <= 65535) {
				$GLOBALS['settings']['port'] = $int;
			} else {
				echo("[ERROR] Invalid port to bind to in the server.properties file.");
			}
		} elseif(stristr($line, "server-name=") && strlen($line != 11)) {
			$str = trim($line, "\n\tserver-name=");
			$GLOBALS['settings']['name'] = $str;
		} else {
			echo("[WARNING] Unneccessary line or outdated server in server.properties file!");
		}
	}
	if(isset($GLOBALS['settings']['ip']) && isset($GLOBALS['settings']['port']) && isset($GLOBALS['settings']['name'])) {
		echo("Settings read successfully.");
	} else {
		echo "Settings failed to load, exiting.";
		stop();
	}
}

function initialize() {
	echo("Starting Openbox Game Server v".VERSION."...");
	read_settings();
	$app = function($request, $response) {
	$response->writeHead(200, array('Content-Type' => 'text/plain'));
	$response->end("Hello World\n"); 
	};

	$loop = React\EventLoop\Factory::create();
	$socket = new React\Socket\Server($loop);
	$http = new React\Http\Server($socket, $loop);
	$http->on('request', $app); 
	echo "Game server running on $ip:$port\n";
	$socket->listen($GLOBALS['settings']['port'], $GLOBALS['settings']['ip']);
	$loop->run();
	while(true) {
		read_console();
	}
}

function process_cmd($cmd) {
	echo "[DEBUG] Command recieved: ".$cmd;
	$d = explode(" ",$cmd);
	$command = $d[0];
	$args = $d;
	switch($command) {
		case "stop":
			stop();
		break;
		default:
			echo("Unknown command ".'"'.$command.'"'.".");
		break;
	}
}

function read_console() {
	while($line = fgets($GLOBALS['stdin'])) {
	  echo $line;
	}
}
?>