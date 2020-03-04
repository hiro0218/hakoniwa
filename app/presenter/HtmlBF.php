<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlBF extends HTML {
	function main($data, $hako) {
		global $init;
		$this_file = $init->baseDir . "/hako-bf.php";
		require_once(VIEWS_PATH.'/admin/bf.php');
	}
}
