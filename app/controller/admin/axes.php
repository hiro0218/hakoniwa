<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once MODEL_PATH.'/Admin.php';
require_once PRESENTER_PATH.'/HtmlAxes.php';

 class Axes extends Admin {

 	function __construct() {
        parent::__construct();
 	}

 	function execute() {
 		$html = new HtmlAxes();
 		$html->header();

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

 }
