<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once MODEL_PATH.'/Admin.php';
require_once MODEL_PATH.'/File/HakoBF.php';
require_once PRESENTER_PATH.'/HtmlBF.php';

 class BF extends Admin {

    function __construct() {
        parent::__construct();
    }

 	function execute() {
 		$html = new HtmlBF();
 		$hako = new HakoBF();
 		$hako->init($this);

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
 				require_once MODEL_PATH.'/Turn.php';
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
 				require_once MODEL_PATH.'/Turn.php';
 				Turn::islandSort($hako);
 				$hako->writeIslandsFile();
 			}
 		}
 	}

 }
