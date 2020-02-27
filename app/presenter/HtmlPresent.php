<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlPresent extends HTML {

	function enter() {
		global $init;
		$this_file = $init->baseDir . "/hako-present.php";
		$main_file = $init->baseDir . "/hako-main.php";

		require_once(VIEWS_PATH.'/admin/present/top.php');
	}

	function main($data, $hako) {
		global $init;
		$this_file = $init->baseDir . "/hako-present.php";
		$main_file = $init->baseDir . "/hako-main.php";

		$width  = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;
		//$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
		$defaultTarget = "";

		require_once(VIEWS_PATH.'/admin/present/main.php');
	}
}
