#!/bin/env php
<?php
/* copy text to the host terminal clipboard */
/* Copyright (C) 2020 laruence */
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
			/* https://www.gnu.org/software/screen/manual/html_node/Control-Sequences.html */
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
			/* See https://invisible-island.net/xterm/ctlseqs/ctlseqs.html
			 * section "OSC Ps = 5 2" */
			echo "\033]52;c;";
			echo base64_encode($data);
			echo "\007";
			break;
		}
	}
}

$osc52 = new OSC52();
$osc52->escape(file_get_contents("php://stdin"));
