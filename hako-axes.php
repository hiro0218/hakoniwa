<?php
/**
 * 箱庭諸島 S.E - アクセス解析用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/hako-cgi.php';
require_once VIEWPATH.'/hako-html.php';

$init = new Init();

class Axes {
	public $init;
	public $mode;
	public $dataSet = array();

	function __construct() {
		global $init;
		$this->init = $init;
	}

	function execute() {
		$html = new HtmlAxes();
		$cgi  = new Cgi();
		$this->parseInputData();
		$cgi->getCookies();
		$html->header($cgi->dataSet);

		switch($this->mode) {
			case "enter":
				if($this->passCheck()) {
					$html->main($this->dataSet);
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
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}

	function passCheck() {

		if(file_exists("{$this->init->passwordFile}")) {
			$fp = fopen("{$this->init->passwordFile}", "r");
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

$start = new Axes();
$start->execute();
