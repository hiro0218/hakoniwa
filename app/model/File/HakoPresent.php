<?php
require_once MODEL_PATH. '/File/Core.php';

class HakoPresent extends File {
	public $islandList;  // 島リスト

	function init($cgi) {
		global $init;
		$this->readIslandsFile($cgi);
		$this->readPresentFile();

		$this->islandList = "<option value=\"0\"></option>\n";
		for($i = 0; $i < ( $this->islandNumber ); $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$this->islandList .= "<option value=\"$id\">${name}{$init->nameSuffix}</option>\n";
		}
	}
}
