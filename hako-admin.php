<?php
/**
 * 箱庭諸島 S.E - 管理者モード用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once APPPATH.'/model/hako-cgi.php';
require_once APPPATH.'/view/hako-html.php';

$init = new Init();

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
		$html->header($cgi->dataSet);
		$html->enter($this->urllist, $this->menulist);
		$html->footer();
	}
}

$start = new Admin();
$start->execute();
