<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

class Main {

	function execute() {
		$hako = new Hako();
		$cgi  = new Cgi();

		$cgi->parseInputData();
		$cgi->getCookies();
		$fp = "";

		if(!$hako->readIslands($cgi)) {
			HTML::header();
			HakoError::noDataFile();
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
				$html->header();
				$turn->turnMain($hako, $cgi->dataSet);
				$html->main($hako, $cgi->dataSet); // ターン処理後、TOPページopen
				$html->footer();
				break;

			case "owner":
				$html->header();
				$html->owner($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "command":
				$html->header();
				$com->commandMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "new":
				$html->header();
				$com->newIsland($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "comment":
				$html->header();
				$com->commentMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "print":
				$html->header();
				$html->visitor($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "targetView":
				$html->head();
				$html->printTarget($hako, $cgi->dataSet);
				//$html->footer();
				break;

			case "change":
				$html->header();
				$com->changeMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "ChangeOwnerName":
				$html->header();
				$com->changeOwnerName($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "conf":
				$html = new HtmlTop();
				$html->header();
				$html->register($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "log":
				$html = new HtmlTop();
				$html->header();
				$html->log();
				$html->footer();
				break;

			default:
				$html = new HtmlTop();
				$html->header();
				$html->main($hako, $cgi->dataSet);
				$html->footer();
		}
		Util::unlock($lock);
		exit();
	}
}
