<?php

/*******************************************************************

	箱庭諸島 S.E

	- BattleField管理用ファイル -

	hako-bfctrl.php by SERA - 2012/04/03

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-init.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-file.php';
require_once ABSOLUTE_PATH.'hako-html.php';

define("READ_LINE", 1024);
$init = new Init();
// $THIS_FILE = $init->baseDir . "/hako-bfctrl.php";
// $MAIN_FILE = $init->baseDir . "/hako-main.php";

class HtmlBF extends HTML {
	function main($data, $hako) {
		global $init;

		echo <<<END
<h1 class="title">BattleFields管理ツール</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
	<h2>通常の島からBattleFieldに変更</h2>
	<select name="ISLANDID">$hako->islandListNoBF</select>
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="TOBF">
	<input type="submit" value="BattleFieldに変更">
</form>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
	<h2>BattleFieldから通常の島に変更</h2>
	<select name="ISLANDID">$hako->islandListBF</select>
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="FROMBF">
	<input type="submit" value="通常の島に変更">
</form>
END;
	}
}

class Hako extends File {
	public $islandListNoBF;	// 普通の島リスト
	public $islandListBF;		// BFな島リスト

	function init($cgi) {
		$this->readIslandsFile($cgi);
		$this->islandListNoBF = "<option value=\"0\"></option>\n";
		for($i = 0; $i < ( $this->islandNumberNoBF ); $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$this->islandListNoBF .= "<option value=\"$id\">${name}島</option>\n";
		}
		$this->islandListBF = "<option value=\"0\"></option>\n";
		for($i = $this->islandNumberNoBF; $i < $this->islandNumber; $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$this->islandListBF .= "<option value=\"$id\">${name}島</option>\n";
		}
	}
}

class Main {
	public $mode;
	public $dataSet = array();

	function execute() {
		$html = new HtmlBF();
		$hako =& new Hako();
		$cgi = new Cgi();
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header($cgi->dataSet);

		switch($this->mode) {
			case "TOBF":
				if($this->passCheck()) {
					$this->toMode($this->dataSet['ISLANDID'], $hako);
					$hako->init($this);
				}
				$html->main($this->dataSet, $hako);
				break;

			case "FROMBF":
				if($this->passCheck()) {
					$this->fromMode($this->dataSet['ISLANDID'], $hako);
					$hako->init($this);
				}
				$html->main($this->dataSet, $hako);
				break;

			case "enter":
			default:
				if($this->passCheck()) {
					$html->main($this->dataSet, $hako);
				}
				break;
		}
		$html->footer();
	}

	function parseInputData() {
		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				// 半角カナがあれば全角に変換して返す
				// JcodeConvert($value, 0, 2);
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}

	function toMode($id, &$hako) {
		global $init;

		if ($id) {
			$num = $hako->idToNumber[$id];
			if (!$hako->islands[$num]['isBF']) {
				$hako->islands[$num]['isBF'] = 1;
				$hako->islandNumberBF++;
				require 'hako-turn.php';
				Turn::islandSort($hako);
				$hako->writeIslandsFile();
			}
		}
	}

	function fromMode($id, &$hako) {
		global $init;

		if ($id) {
			$num = $hako->idToNumber[$id];
			if ($hako->islands[$num]['isBF']) {
				$hako->islands[$num]['isBF'] = 0;
				$hako->islandNumberBF--;
				require 'hako-turn.php';
				Turn::islandSort($hako);
				$hako->writeIslandsFile();
			}
		}
	}

	function passCheck() {
		global $init;

		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		if(strcmp(crypt($this->dataSet['PASSWORD'], 'ma'), $masterPassword) == 0) {
			return 1;
		} else {
			echo "<h2>パスワードが違います。</h2>\n";
			return 0;
		}
	}
}

$start = new Main();
$start->execute();
