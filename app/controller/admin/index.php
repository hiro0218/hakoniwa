<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

class Admin {

	function execute() {
		$html = new HtmlAdmin();
		$cgi  = new Cgi();

		$cgi->getCookies();
		$html->header();
		$html->enter();
		$html->footer();
	}
}
