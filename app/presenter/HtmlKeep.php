<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlKeep extends HTML {
	function main($data, $hako) {
		global $init;
		$this_file = $init->baseDir . "/hako-keep.php";
		require_once(VIEWS_PATH.'/admin/keep.php');
	}
}
