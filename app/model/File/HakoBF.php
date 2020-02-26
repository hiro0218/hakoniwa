<?php
require_once MODEL_PATH. '/File/Core.php';

class HakoBF extends File {
	public $islandListNoBF;	// 普通の島リスト
	public $islandListBF;	// BFな島リスト

	function init($cgi) {
		global $init;

		$this->readIslandsFile($cgi);
		$this->islandListNoBF = "<option value=\"0\"></option>\n";
		for($i = 0; $i < ( $this->islandNumberNoBF ); $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$this->islandListNoBF .= "<option value=\"$id\">${name}{$init->nameSuffix}</option>\n";
		}
		$this->islandListBF = "<option value=\"0\"></option>\n";
		for($i = $this->islandNumberNoBF; $i < $this->islandNumber; $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$this->islandListBF .= "<option value=\"$id\">${name}{$init->nameSuffix}</option>\n";
		}
	}
}
