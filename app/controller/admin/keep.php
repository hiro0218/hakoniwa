<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once MODEL_PATH.'/Admin.php';
require_once MODEL_PATH.'/Cgi.php';
require_once MODEL_PATH.'/File/HakoKP.php';
require_once PRESENTER_PATH.'/HtmlKeep.php';

 class Keep extends Admin {

 	function execute() {
 		$html = new HtmlKeep();
 		$cgi = new Cgi();
 		$hako = new HakoKP();
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
