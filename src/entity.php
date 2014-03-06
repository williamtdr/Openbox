<?php
/*
Openbox Game Server
-------------------
By williamtdr // William Teder
*/

class entity {
	public $x = 0;
	public $y = 0;
	public $dir = 0;	

	public function __construct($x, $y, $dir) {
		$this->x = $x;
		$this->y = $y;
		$this->dir = $dir;
	}
}
?>