<?php
/**
 * 箱庭諸島 S.E - メインファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once APPPATH.'/model/hako-cgi.php';
require_once APPPATH.'/model/hako-file.php';
require_once APPPATH.'/view/hako-html.php';
require_once APPPATH.'/model/hako-turn.php';

$init = new Init();

class Main {

	function execute() {
		$hako = new Hako();
		$cgi  = new Cgi();

		$cgi->parseInputData();
		$cgi->getCookies();
		$fp = "";

		if(!$hako->readIslands($cgi)) {
			HTML::header();
			Error::noDataFile();
			HTML::footer();
			Util::unlock($lock);
			exit();
		}
		$lock = Util::lock($fp);
		if(FALSE == $lock) {
			exit();
		}
		$cgi->setCookies();

		$_developmode = (isset( $cgi->dataSet['DEVELOPEMODE'] )) ? $cgi->dataSet['DEVELOPEMODE'] : "";
		if( mb_strtolower($_developmode) == "javascript") {
			$html = new HtmlMapJS();
			$com  = new MakeJS();
		} else {
			$html = new HtmlMap();
			$com  = new Make();
		}
		switch($cgi->mode) {
			case "turn":
				$turn = new Turn();
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$turn->turnMain($hako, $cgi->dataSet);
				$html->main($hako, $cgi->dataSet); // ターン処理後、TOPページopen
				$html->footer();
				break;

			case "owner":
				$html->header($cgi->dataSet);
				$html->owner($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "command":
				$html->header($cgi->dataSet);
				$com->commandMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "new":
				$html->header($cgi->dataSet);
				$com->newIsland($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "comment":
				$html->header($cgi->dataSet);
				$com->commentMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "print":
				$html->header($cgi->dataSet);
				$html->visitor($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "targetView":
				$html->header($cgi->dataSet);
				$html->printTarget($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "change":
				$html->header($cgi->dataSet);
				$com->changeMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "ChangeOwnerName":
				$html->header($cgi->dataSet);
				$com->changeOwnerName($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "conf":
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$html->register($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "log":
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$html->log();
				$html->footer();
				break;

			default:
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$html->main($hako, $cgi->dataSet);
				$html->footer();
		}
		Util::unlock($lock);
		exit();
	}
}

$start = new Main();
$start->execute();
