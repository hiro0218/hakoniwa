<?php

/*******************************************************************

	箱庭諸島 S.E

	- 島預かり管理用ファイル -

	hako-keep.php by SERA - 2012/07/23

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-init.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-file.php';
require_once ABSOLUTE_PATH.'hako-html.php';

$init = new Init();
$THIS_FILE = $init->baseDir . "/hako-keep.php";
$MAIN_FILE = $init->baseDir . "/hako-main.php";

//--------------------------------------------------------------------
class HTMLKP extends HTML {
	function main($data, $hako) {
		global $init;

		echo <<<END
<h1 class="title">島預かり管理ツール</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
	<h2>管理人預かりに変更</h2>
	<select name="ISLANDID">$hako->islandListNoKP</select>
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="TOKP">
	<input type="submit" value="管理人預かりに変更">
</form>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
	<h2>管理人預かりを解除</h2>
	<select name="ISLANDID">$hako->islandListKP</select>
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="FROMKP">
	<input type="submit" value="管理人預かりを解除">
</form>
END;
	}
}

class HakoKP extends File {
	public $islandListNoKP;	// 普通の島リスト
	public $islandListKP;	// 管理人預かり島リスト

	function init($cgi) {
		$this->readIslandsFile($cgi);
		$this->islandListNoKP = "<option value=\"0\"></option>\n";
		$this->islandListKP = "<option value=\"0\"></option>\n";
		for($i = 0; $i < $this->islandNumber; $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$keep = $this->islands[$i]['keep'];
			if($keep == 1) {
				$this->islandListKP .= "<option value=\"$id\">${name}島</option>\n";
			} else {
				$this->islandListNoKP .= "<option value=\"$id\">${name}島</option>\n";
			}
		}
	}
}

class Main {
	public $mode;
	public $dataSet = array();

	function execute() {
		$html = new HTMLKP();
		$cgi = new Cgi();
		$hako =& new HakoKP();
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header($cgi->dataSet);

		switch($this->mode) {
			case "TOKP":
				if($this->passCheck()) {
					$this->toMode($this->dataSet['ISLANDID'], $hako);
					$hako->init($this);
				}
				$html->main($this->dataSet, $hako);
				break;

			case "FROMKP":
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
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}

	function toMode($id, &$hako) {
		global $init;

		if ($id) {
			$num = $hako->idToNumber[$id];
			if (!$hako->islands[$num]['keep']) {
				$hako->islands[$num]['keep'] = 1;
				$hako->islandNumberKP++;
				//require 'hako-turn.php';
				//Turn::islandSort($hako);
				$hako->writeIslandsFile();
			}
		}
	}

	function fromMode($id, &$hako) {
		global $init;

		if ($id) {
			$num = $hako->idToNumber[$id];
			if ($hako->islands[$num]['keep']) {
				$hako->islands[$num]['keep'] = 0;
				$hako->islandNumberKP--;
				//require 'hako-turn.php';
				//Turn::islandSort($hako);
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
