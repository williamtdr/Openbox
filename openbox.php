<?php
/*
Openbox Game Server
-------------------
By williamtdr // William Teder
*/

require_once("vendor/autoload.php"); // load reactPHP
require_once("src/entity.php");
require_once("src/player.php");
require_once("src/settings.php");

/*  DEFINES  */
define("VERSION","0.1");

class OpenboxGameServer {
	private $IP;
	private $port;
	public $max_players;
	public $name;
	public $chat = array();
	public $players = array();
	public $tick = 0;

	public function __construct($IP = "0.0.0.0", $port = 6511, $max_players = 10, $name = "Openbox Game Server") {
		$this->IP = $IP;
		$this->port = $port;
		$this->max_players = $max_players;
		$this->name = $name;
		$this->initialize();
	}

	public function stop() {
		echo("Stoppping the server...\n");
		die();
	}

	public function movePlayer($clientID, $x, $y) {
		foreach($this->players as $player) {
			if($player->clientID == $clientID) {
				$player->lastUpdated = $this->tick;
				$player->entity->x = $x;
				$player->entity->y = $y;
			}
		}
	}

	public function getPositions() {
		$posdata = array();
		foreach($this->players as $player) {
			$posdata[] = array('username' => $player->username, 'x' => $player->entity->x, 'y' => $player->entity->y);
		}
		$data = json_encode($posdata);
		return $data;
	}

	public function validateJoin($username, $clientID, $version) {
		if($version != '1.0') {
			return $responsearray = array('action-successful' => false, 'msg' => "Invalid client version.");
		}
		if(strlen($username) == 0) {
			return $responsearray = array('action-successful' => false, 'msg' => "Invalid username.");
		}
		foreach($this->players as $player) {
			if($player->username == $username) {
				return $responsearray = array('action-successful' => false, 'msg' => "Username in use.");
			}
		}
		if(count($this->players) == $this->max_players) {
			return $responsearray = array('action-successful' => false, 'msg' => "Server is full!");
		}
		$this->chat[] = $username . " joined the game\n";
		echo($username . " joined the game");
		$this->players[] = new player($username, $clientID, $version, 0, 0, $this->tick);
		$chat = json_encode($this->chat);
		$responsearray = array('action-successful' => true, 'players-online' => count($this->players), 'chat' => json_encode($chat));
		return $responsearray;
	}

	function initialize() {
		echo("Starting Openbox Game Server v".VERSION."...\n");
		$app = function($request, $response) {
		$response->writeHead(200, array('Content-Type' => 'text/plain'));
		$requestarray = $request->getQuery();
		if(isset($requestarray['a'])) {
			switch($requestarray['a']) {
				case "join":
					$clientID = $requestarray['clientID'];
					$username = $requestarray['user'];
					$version = $requestarray['version'];
					$responsearray = $this->validateJoin($username, $clientID, $version);						
				break;
				case "getPositions":
					return $this->getPositions();
				break;
				case "updatePosition":
					$clientID = $requestarray['clientID'];
					$x = $requestarray['x'];
					$y = $requestarray['y'];
					$this->movePlayer($clientID, $x, $y);
					$responsearray = array('action-successful' => true);
				break;
				default:
					$responsearray = array('error' => 'Invalid action or outdated server.');
				break;
			}
		} else {
			$responsearray = array('error' => 'No action specified.');
		}
		$response->write(json_encode($responsearray));
		$response->end(); 
	};

	$loop = React\EventLoop\Factory::create();
	$socket = new React\Socket\Server($loop);
	$http = new React\Http\Server($socket, $loop);
	$http->on('request', $app); 
	echo "Game server running on ".$this->IP.":".$this->port."\n";
	$socket->listen($this->port, $this->IP);
	$self = $this;
	$timer = $loop->addPeriodicTimer(0.1, function ($timer) use ($self) {
		$self->tick++;
		foreach($self->players as $player) {
			$destroy = false;
			if($player->lastUpdated < $self->tick - 100) {
				echo($player->username . " logged out due to timeout\n");
				$self->chat[] = $player->username . " left the game";
				$destroy = true;
			}
			if($destroy) { // remove player from players array
				$array = array();
				foreach($self->players as $player2) {	
					if($player2->clientID != $player->clientID) {
						$array[] = $player2;
					}
				}
				$self->players = $array;
			}
		}
	});
	$loop->run();
}
}

$settings = read_settings();
if($settings) {
	$ob_server = new OpenboxGameServer($settings[0],$settings[1],$settings[2],$settings[3]);
} else {
	die();
}
?>