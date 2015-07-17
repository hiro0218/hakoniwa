<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

class Admin {
	private $urllist  = array();
	private $menulist = array();

	function __construct() {
		$this->urllist  = array( ini_get('safe_mode') ? '/hako-mente-safemode.php' : '/hako-mente.php', '/hako-axes.php', '/hako-keep.php', '/hako-present.php', '/hako-edit.php', '/hako-bf.php');
		$this->menulist = array('データ管理','アクセスログ閲覧','島預かり管理','プレゼント','マップエディタ','BattleField管理');
	}

	function execute() {
		$html = new HtmlAdmin();
		$cgi  = new Cgi();

		$cgi->getCookies();
		$html->header();
		$html->enter($this->urllist, $this->menulist);
		$html->footer();
	}
}
