<?php

/*******************************************************************

	箱庭諸島 S.E

	- 管理者モード用ファイル -

	hako-admin.php by SERA - 2013/06/29

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-init.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-html.php';

$init = new Init();
$THIS_FILE = $init->baseDir . "/hako-main.php";

class HtmlEntrance extends HTML {
	function enter($urllist, $menulist) {
		global $init;
		echo <<<END
<script>
function go(obj) {
	if(obj.menulist.value) {
		obj.action = obj.menulist.value;
	}
}
</script>


<h1 class="title">管理室入り口</h1>
<hr>
<TABLE BORDER=0 width="100%">
<TR valign="top">
<TD class="M">
<div id="AdminEnter">
<h2>管理室へ</h2>
<form method="post" onSubmit="go(this)">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<select name="menulist">
END;
		$urllistCnt = (int)count($urllist);
		for ( $i = 0; $i < $urllistCnt; $i++ ) {
			if ($i === 0) {
				echo "<option value=\"{$init->baseDir}{$urllist[$i]}\" selected=\"selected\">{$menulist[$i]}</option>\n";
			} else {
				echo "<option value=\"{$init->baseDir}{$urllist[$i]}\">{$menulist[$i]}</option>\n";
			}
		}
		echo "</select>\n";
		echo "<input type=\"submit\" value=\"管理室へ\">\n";
		echo "</form>\n";
		echo <<<END
</TD>
</TR>
</TABLE>
<BR>
END;
	}
}

class Main {
	private $urllist  = array();
	private $menulist = array();

	function __construct() {
		$this->urllist  = array( ini_get('safe_mode') ? '/hako-mente-safemode.php' : '/hako-mente.php', '/hako-axes.php', '/hako-keep.php', '/hako-present.php', '/hako-edit.php', '/hako-bfctrl.php');
		$this->menulist = array('データ管理','アクセスログ閲覧','島預かり管理','プレゼント','マップエディタ','BattleField管理');
	}

	function execute() {
		$html = new HtmlEntrance();
		$cgi  = new Cgi();
		$cgi->getCookies();
		$html->header($cgi->dataSet);
		$html->enter($this->urllist, $this->menulist);
		$html->footer();
	}
}

$start = new Main();
$start->execute();
