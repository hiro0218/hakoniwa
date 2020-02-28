<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlAdmin extends HTML {

	function enter() {
		global $init;

		$urllist  = array( '/hako-mente.php', '/hako-axes.php', '/hako-keep.php', '/hako-present.php', '/hako-edit.php', '/hako-bf.php');
		$menulist = array('データ管理','アクセスログ閲覧','島預かり管理','プレゼント','マップエディタ','BattleField管理');

		require_once(VIEWS_PATH.'/admin/top.php');
	}
}
