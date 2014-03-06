<?php
/*
Openbox Game Server
-------------------
By williamtdr // William Teder
*/

class player {
	public $username;
	public $clientID;
	public $version;
	public $entity;
	public $lastUpdated;	
	
	public function __construct($username, $clientID, $version, $x, $y, $tick) {
		$this->entity = new entity($x, $y, 0);
		$this->username = $username;
		$this->clientID = $clientID;
		$this->version = $version;
		$this->lastUpdate = $tick;
	}

	public function __destruct() {
	}
	
	public function destroy() {
		$this->__destruct();
	}
}
?>
