<?php

/*******************************************************************

	箱庭諸島 S.E
	
	- アクセス解析用ファイル -
	
	hako-axes.php by SERA - 2012/06/29

*******************************************************************/

require 'config.php';
require 'hako-cgi.php';
require 'hako-html.php';

define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-axes.php";

//--------------------------------------------------------------------
class HtmlMente extends HTML {
	function enter() {
		global $init;
		
		print <<<END
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
		print "<CENTER><a href=\"{$init->baseDir}/hako-main.php\"><span class=\"big\">トップへ戻る</span></a></CENTER>\n";
		print "<h1 class=\"title\">{$init->title}<br>アクセスログ閲覧室</h1>\n";
		$this->dataPrint($data);
	}
	
	// 表示モード
	function dataPrint($data, $suf = "") {
		global $init;
		
		print "<HR>";
		print <<<END
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
				print "<TR>\n";
				print "<TD scope=\"col\">{$num}</TD>\n";
				print "</TR>\n";
			}
		}
		fclose($fp);
		print "</tbody>\n</table>\n</form>";
	}
}

class Main {
	var $mode;
	var $dataSet = array();
	
	function execute() {
		$html = new HtmlMente;
		$cgi = new Cgi;
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
				// $value = Util::sjis_convert($value);
				// 半角カナがあれば全角に変換して返す
				// $value = i18n_ja_jp_hantozen($value,"KHV");
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
			print "<h2>パスワードが違います。</h2>\n";
			return 0;
		}
	}
}

$start = new Main();
$start->execute();

?>
