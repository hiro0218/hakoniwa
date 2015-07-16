<?php
/**
 * 箱庭諸島 S.E - プレゼント定義用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/hako-cgi.php';
require_once MODELPATH.'/hako-file.php';
require_once VIEWPATH.'/hako-html.php';

$init = new Init();

class Present {
	public $mode;
	public $dataSet = array();

	function execute() {
		$html = new HtmlPresent();
		$hako =& new HakoPresent();
		$cgi = new Cgi();
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header($cgi->dataSet);

		switch($this->mode) {
			case "PRESENT":
				if($this->passCheck()) {
					$this->presents($this->dataSet, $hako);
				}
				$html->main($this->dataSet, $hako);
				break;

			case "PUNISH":
				if($this->passCheck()) {
					$this->punish($this->dataSet, $hako);
				}
				$html->main($this->dataSet, $hako);
				break;

			case "enter":
				if($this->passCheck()) {
					$html->main($this->dataSet, $hako);
				}
				break;

			default:
				$html->enter();
				break;
		}
		$html->footer();
	}

	function parseInputData() {
		$this->mode = isset($_POST['mode']) ? $_POST['mode'] : "";
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				// 半角カナがあれば全角に変換して返す
				// JcodeConvert($value, 0, 2);
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}

	function presents($data, &$hako) {
		global $init;

		if ($data['ISLANDID']) {
			$num = $hako->idToNumber[$data['ISLANDID']];
			$hako->islands[$num]['present']['item'] = 0;
			$hako->islands[$num]['present']['px'] = $data['MONEY'];
			$hako->islands[$num]['present']['py'] = $data['FOOD'];
			$hako->writePresentFile();
		}
	}

	function punish($data, &$hako) {
		global $init;

		if ($data['ISLANDID']) {
			$punish =& $data['PUNISH'];
			if (( $punish >= 0) && ( $punish <= 8 )) {
				$num = $hako->idToNumber[$data['ISLANDID']];
				$hako->islands[$num]['present']['item'] = $punish;
				$hako->islands[$num]['present']['px'] = ( $punish < 6 ) ? 0 : $data['POINTX'];
				$hako->islands[$num]['present']['py'] = ( $punish < 6 ) ? 0 : $data['POINTY'];
				$hako->writePresentFile();
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
			Util::makeTagMessage("パスワードが違います。", "danger");
			return 0;
		}
	}
}

$start = new Present();
$start->execute();
