<?php
/*
Openbox Game Server
-------------------
By williamtdr // William Teder
*/

function read_settings() {
	if(file_exists("server.properties")) {
		$unformatted = file("server.properties");
	} else {
		$default_config = array(
			"server-ip=0.0.0.0\n",
			"server-port=6511\n",
			"server-name=Openbox Game Server v".VERSION."\n",
			"max-players=4\n"
		);
		if(file_put_contents("server.properties",$default_config) == false) {
			echo("Couldn't write to server.properties, exiting.\n");
			stop();
		}
		$unformatted = file_get_contents("server.properties");
	}
	foreach($unformatted as $line) {
		if(stristr($line, "server-ip=") && strlen($line != 9)) {
			$str = trim($line, "\n\tserver-ip=");
			$ip = $str;
		} elseif(stristr($line, "server-port=") && strlen($line != 11)) {
			$str = trim($line, "\n\tserver-port=");
			$int = intval($str);
			if($int > -1 && $int <= 65535) {
				$port = $int;
			} else {
				echo("[ERROR] Invalid port to bind to in the server.properties file.\n");
			}
		} elseif(stristr($line, "server-name=") && strlen($line != 11)) {
			$str = trim($line, "\n\tserver-name=");
			$name = $str;
		} elseif(stristr($line, "max-players=") && strlen($line != 12)) {
			$str = trim($line, "\n\tmax-players=");
			$int = intval($str);
			if($int > -1) {
				$max_players = $int;
			} else {
				echo("[ERROR] Invalid maximum players to in the server.properties file.\n");
			}
		} else {
			echo("[WARNING] Unneccessary line or outdated server in server.properties file!\n");
		}
	}
	if(isset($ip) && isset($port) && isset($max_players) && isset($name)) {
		echo("Settings read successfully.\n");
		return array($ip,$port,$max_players,$name);
	} else {
		echo "Settings failed to load, exiting.\n";
		return false;
	}
}
?>
