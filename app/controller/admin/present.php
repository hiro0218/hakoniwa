<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once MODEL_PATH.'/admin.php';
require_once MODEL_PATH.'/hako-cgi.php';
require_once MODEL_PATH.'/File/HakoPresent.php';

 class Present extends Admin {

 	function execute() {
 		$html = new HtmlPresent();
 		$hako = new HakoPresent();
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
