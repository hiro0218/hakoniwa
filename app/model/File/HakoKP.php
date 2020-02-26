<?php
require_once MODEL_PATH. '/File/Core.php';

class HakoKP extends File {
	public $islandListNoKP;	// 普通の島リスト
	public $islandListKP;	// 管理人預かり島リスト

	function init($cgi) {
		global $init;
		$this->readIslandsFile($cgi);
		$this->islandListNoKP = "<option value=\"0\"></option>\n";
		$this->islandListKP = "<option value=\"0\"></option>\n";

		for($i = 0; $i < $this->islandNumber; $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$keep = $this->islands[$i]['keep'];
			if($keep == 1) {
				$this->islandListKP .= "<option value=\"$id\">${name}{$init->nameSuffix}</option>\n";
			} else {
				$this->islandListNoKP .= "<option value=\"$id\">${name}{$init->nameSuffix}</option>\n";
			}
		}
	}
}
