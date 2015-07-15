<?php

/*******************************************************************

	箱庭諸島 S.E

	- アクセス解析用ファイル -

	hako-axes.php by SERA - 2012/06/29

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-init.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-html.php';

define("READ_LINE", 1024);
$init = new Init();
$THIS_FILE = $init->baseDir . "/hako-axes.php";

//--------------------------------------------------------------------
class HtmlMente extends HTML {
	function enter() {
		global $init;

		echo <<<END
<h1 class="title">{$init->title}<br>アクセスログ閲覧室</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="入室する">
</form>
END;
	}

	function main($data) {
		global $init;
		echo "<h1 class=\"title\">アクセスログ閲覧室</h1>\n";
		$this->dataPrint($data);
	}

	// 表示モード
	function dataPrint($data, $suf = "") {
		global $init;

		echo "<HR>";
		echo <<<END
<br>
<h2>アクセスログ</h2>
<form action="#">
<input type="button" value="オートフィルタ表示" onclick="Button_DispFilter(this, 'DATA-TABLE')" onkeypress="Button_DispFilter(this, 'DATA-TABLE')">
<table id="DATA-TABLE">
	<thead>
		<tr class="NumberCell">
			<td scope="row"><input type="button" tabindex="1" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [0])" value="ログインした時間"></td>
			<td scope="row"><input type="button" tabindex="2" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [1, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [1, 0])" value="島ＩＤ"></td>
			<td scope="row"><input type="button" tabindex="3" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [2, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [2, 0])" value="島の名前"></td>
			<td scope="row"><input type="button" tabindex="4" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [3, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [3, 0])" value="ＩＰ情報"></td>
			<td scope="row"><input type="button" tabindex="5" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [4, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [4, 0])" value="ホスト情報"></td>
		</tr>
	</thead>
	<tbody>
END;
		// ファイルを読み込み専用でオープンする
		$fp = fopen("{$init->dirName}/{$init->logname}", 'r');

		// 終端に達するまでループ
		while (!feof($fp)) {
			// ファイルから一行読み込む
			$line = fgets($fp);
			if($line !== FALSE) {
				$line = substr_replace($line, ",<center>", 32, 1);
				$wpos = strpos($line, ',', 33);
				$line = substr_replace($line, "</center>,", $wpos, 1);
				$num  = preg_replace( "/,/", "</TD><TD>", $line);
				echo "<TR>\n";
				echo "<TD scope=\"col\">{$num}</TD>\n";
				echo "</TR>\n";
			}
		}
		fclose($fp);
		echo "</tbody>\n</table>\n</form>";
	}
}

class Main {
	public $mode;
	public $dataSet = array();

	function execute() {
		$html = new HtmlMente();
		$cgi = new Cgi();
		$this->parseInputData();
		$cgi->getCookies();
		$html->header($cgi->dataSet);

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

	function parseInputData() {
		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}

	function passCheck() {
		global $init;

		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		if(strcmp(crypt($this->dataSet['PASSWORD'], 'ma'), $masterPassword) == 0) {
			return 1;
		} else {
			echo "<h2>パスワードが違います。</h2>\n";
			return 0;
		}
	}
}

$start = new Main();
$start->execute();
