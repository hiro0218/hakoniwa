<?php
/**
 * 箱庭諸島 S.E - 島預かり管理用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/admin.php';
require_once MODELPATH.'/hako-cgi.php';
require_once MODELPATH.'/hako-file.php';
require_once PRESENTER.'/hako-html.php';

$init = new Init();
$MAIN_FILE = $init->baseDir . "/hako-main.php";

class KP extends Admin {

	function execute() {
		$html = new HTMLKP();
		$cgi = new Cgi();
		$hako =& new HakoKP();
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header();

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

}

$start = new KP();
$start->execute();
