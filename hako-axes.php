<?php
/**
 * 箱庭諸島 S.E - アクセス解析用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/admin.php';
require_once MODELPATH.'/hako-cgi.php';
require_once VIEWPATH.'/hako-html.php';

$init = new Init();

class Axes extends Admin {
	public $init;

	function __construct() {
		global $init;
		$this->init = $init;
	}

	function execute() {
		$html = new HtmlAxes();
		$cgi  = new Cgi();
		$this->parseInputData();
		$cgi->getCookies();
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

$start = new Axes();
$start->execute();
