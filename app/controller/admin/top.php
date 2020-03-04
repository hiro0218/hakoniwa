<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once MODEL_PATH.'/Admin.php';
require_once PRESENTER_PATH.'/HtmlAdmin.php';

class AdminTop extends Admin {

    function __construct() {
        parent::__construct();
    }

	function execute() {
		$html = new HtmlAdmin();

		$html->header();
		$html->enter();
		$html->footer();
	}
}
