<?php
/**
 * 箱庭諸島 S.E - プレゼント定義用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/admin.php';
require_once MODELPATH.'/hako-cgi.php';
require_once MODELPATH.'/hako-file.php';
require_once VIEWPATH.'/hako-html.php';

$init = new Init();

class Present extends Admin {

	function execute() {
		$html = new HtmlPresent();
		$hako =& new HakoPresent();
		$cgi = new Cgi();
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header();

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

	function presents($data, &$hako) {

		if ($data['ISLANDID']) {
			$num = $hako->idToNumber[$data['ISLANDID']];
			$hako->islands[$num]['present']['item'] = 0;
			$hako->islands[$num]['present']['px'] = $data['MONEY'];
			$hako->islands[$num]['present']['py'] = $data['FOOD'];
			$hako->writePresentFile();
		}
	}

	function punish($data, &$hako) {

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

}

$start = new Present();
$start->execute();
