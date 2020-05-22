#!/bin/env php
<?php
namespace Github\Laruence;

class OSC52 {
	private $term;

	public function __construct() {
		if (isset($_SERVER["STY"])) {
			$this->term = "screen";
		} else if (isset($_SERVER["TMUX"])) {
			$this->term = "tmux";
		}
	}

	public function escape($data) {
		$data = trim($data);
		switch ($this->term) {
		case "screen":
			echo "\033P\033]52;c;";
			echo base64_encode($data);
			echo "\007\033\\";
			break;
		case "tmux":
			echo "\033Ptmux;\033\033]52;c;";
			echo base64_encode($data);
			echo "\007\033\\";
			break;
		default:
			echo "\033]52;c;";
			echo base64_encode($data);
			echo "\007";
			break;
		}
	}
}

$osc52 = new OSC52();
$osc52->escape(file_get_contents("php://stdin"));
