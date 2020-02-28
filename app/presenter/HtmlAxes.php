<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlAxes extends HTML {
	function enter() {
		global $init;
		$this_file = $init->baseDir . "/hako-axes.php";

		echo <<<END
<h1 class="title">{$init->title}<br>アクセスログ閲覧室</h1>
<form action="{$this_file}" method="post">
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
