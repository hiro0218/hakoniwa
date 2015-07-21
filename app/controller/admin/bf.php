<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

 class BF extends Admin {

 	function execute() {
 		$html = new HtmlBF();
 		$hako = new HakoBF();
 		$cgi = new Cgi();
 		$this->parseInputData();
 		$hako->init($this);
 		$cgi->getCookies();
 		$html->header();

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

 	function toMode($id, &$hako) {
 		global $init;

 		if ($id) {
 			$num = $hako->idToNumber[$id];
 			if (!$hako->islands[$num]['isBF']) {
 				$hako->islands[$num]['isBF'] = 1;
 				$hako->islandNumberBF++;
 				require_once APPPATH.'/model/hako-turn.php';
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
 				require_once APPPATH.'/model/hako-turn.php';
 				Turn::islandSort($hako);
 				$hako->writeIslandsFile();
 			}
 		}
 	}

 }
