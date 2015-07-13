<?php

/*******************************************************************

	箱庭諸島 S.E

	- メンテナンス（セーフモード）用ファイル -

	hako-mente-safemode.php by SERA - 2012/05/07

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-html.php';

define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-mente-safemode.php";

class HtmlMente extends HTML {
	function enter() {
		global $init;

		echo "<h1 class=\"title\">メンテナンスツール</h1>";
		if(file_exists("{$init->passwordFile}")) {
			echo <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="メンテナンス">
END;
		} else {
			echo <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<H2>マスタパスワードと特殊パスワードを決めてください。</H2>
<P>※入力ミスを防ぐために、それぞれ２回ずつ入力してください。</P>
<B>マスタパスワード：</B><BR>
(1) <INPUT type="password" name="MPASS1" value="$mpass1">&nbsp;&nbsp;(2) <INPUT type="password" name="MPASS2" value="$mpass2"><BR>
<BR>
<B>特殊パスワード：</B><BR>
(1) <INPUT type="password" name="SPASS1" value="$spass1">&nbsp;&nbsp;(2) <INPUT type="password" name="SPASS2" value="$spass2"><BR>
<BR>
<input type="hidden" name="mode" value="setup">
<INPUT type="submit" value="パスワードを設定する">
END;
		}
		echo "</form>\n";
	}

	function main($data) {
		global $init;

		echo "<h1 class=\"title\">{$init->title}<br>メンテナンスツール</h1>\n";
		// データ保存用ディレクトリの存在チェック
		if(!is_dir("{$init->dirName}")) {
			echo "{$init->tagBig_}データ保存用のディレクトリが存在しません{$init->_tagBig}";
			HTML::footer();
			exit;
		}
		// データ保存用ディレクトリのパーミッションチェック
		if(!is_writeable("{$init->dirName}") || !is_readable("{$init->dirName}")) {
			echo "{$init->tagBig_}データ保存用のディレクトリのパーミッションが不正です。パーミッションを0777等の値に設定してください。{$init->_tagBig}";
			HTML::footer();
			exit;
		}
		if(is_file("{$init->dirName}/hakojima.dat")) {
			$this->dataPrint($data);
		} else {
			echo "<hr>\n";
			echo "<form action=\"{$GLOBALS['THIS_FILE']}\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"PASSWORD\" value=\"{$data['PASSWORD']}\">\n";
			echo "<input type=\"hidden\" name=\"mode\" value=\"NEW\">\n";
			echo "<input type=\"submit\" value=\"新しいデータを作る\">\n";
			echo "</form>\n";
		}
		// バックアップデータ
		$dir = opendir("./");
		while($dn = readdir($dir)) {
			if(preg_match("/{$init->dirName}\.bak(.*)$/", $dn, $suf)) {
				if (is_file("{$init->dirName}.bak{$suf[1]}/hakojima.dat")) {
					$this->dataPrint($data, $suf[1]);
				}
			}
		}
		closedir($dir);
	}

	// 表示モード
	function dataPrint($data, $suf = "") {
		global $init;

		echo "<HR>";
		if(strcmp($suf, "") == 0) {
			$fp = fopen("{$init->dirName}/hakojima.dat", "r");
			echo "<h2>現役データ</h2>\n";
		} else {
			$fp = fopen("{$init->dirName}.bak{$suf}/hakojima.dat", "r");
			echo "<h2>バックアップ{$suf}</h2>\n";
		}
		$lastTurn = chop(fgets($fp, READ_LINE));
		$lastTime = chop(fgets($fp, READ_LINE));
		fclose($fp);
		$timeString = timeToString($lastTime);

		echo <<<END
<strong>ターン$lastTurn</strong><br>
<strong>最終更新時間</strong>:$timeString<br>
<strong>最終更新時間(秒数表\示)</strong>:1970年1月1日から$lastTime 秒<br>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="DELETE">
<input type="hidden" name="NUMBER" value="{$suf}">
<input type="submit" value="このデータを削除">
</form>
END;
		if(strcmp($suf, "") == 0) {
			$time = localtime($lastTime, TRUE);
			$time['tm_year'] += 1900;
			$time['tm_mon']++;
			echo <<<END
<h2>最終更新時間の変更</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="NTIME">
<input type="hidden" name="NUMBER" value="{$suf}">
<input type="text" size="4" name="YEAR" value="{$time['tm_year']}">年
<input type="text" size="2" name="MON" value="{$time['tm_mon']}">月
<input type="text" size="2" name="DATE" value="{$time['tm_mday']}">日
<input type="text" size="2" name="HOUR" value="{$time['tm_hour']}">時
<input type="text" size="2" name="MIN" value="{$time['tm_min']}">分
<input type="text" size="2" name="NSEC" value="{$time['tm_sec']}">秒
<input type="submit" value="変更">
</form>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="STIME">
<input type="hidden" name="NUMBER" value="{$suf}">
1970年1月1日から<input type="text" size="32" name="SSEC" value="$lastTime">秒
<input type="submit" value="秒指定で変更">
</form>
END;
		} else {
			echo <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="NUMBER" value="{$suf}">
<input type="hidden" name="mode" value="CURRENT">
<input type="submit" value="このデータを現役に">
</form>
END;
		}
	}
}

function timeToString($t) {
	$time = localtime($t, TRUE);
	$time['tm_year'] += 1900;
	$time['tm_mon']++;
	return "{$time['tm_year']}年 {$time['tm_mon']}月 {$time['tm_mday']}日 {$time['tm_hour']}時 {$time['tm_min']}分 {$time['tm_sec']}秒";
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
			case "NEW":
				if($this->passCheck()) {
					$this->newMode();
				}
				$html->main($this->dataSet);
				break;

			case "CURRENT":
				if($this->passCheck()) {
					$this->currentMode($this->dataSet['NUMBER']);
				}
				$html->main($this->dataSet);
				break;

			case "DELETE":
				if($this->passCheck()) {
					$this->delMode($this->dataSet['NUMBER']);
				}
				$html->main($this->dataSet);
				break;

			case "NTIME":
				if($this->passCheck()) {
					$this->timeMode();
				}
				$html->main($this->dataSet);
				break;

			case "STIME":
				if($this->passCheck()) {
					$this->stimeMode($this->dataSet['SSEC']);
				}
				$html->main($this->dataSet);
				break;

			case "setup":
				$this->setupMode();
				$html->enter();
				break;

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
				// JcodeConvert($value, 0, 2);
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}

	function newMode() {
		global $init;

		// mkdir($init->dirName, $init->dirMode);
		// 現在の時間を取得
		$now = $_SERVER['REQUEST_TIME'];
		$now = $now - ($now % ($init->unitTime));
		$fileName = "{$init->dirName}/hakojima.dat";
		touch($fileName);
		$fp = fopen($fileName, "w");
		fputs($fp, "1\n");
		fputs($fp, "{$now}\n");
		fputs($fp, "0\n");
		fputs($fp, "1\n");
		fclose($fp);

		// 同盟ファイル生成
		$fileName = "{$init->dirName}/ally.dat";
		$fp = fopen($fileName, "w");
		fclose($fp);

		// アクセスログ生成
		$fileName = "{$init->dirName}/{$init->logname}";
		$fp = fopen($fileName, "w");
		fclose($fp);

		// .htaccess生成
		$fileName = "{$init->dirName}/.htaccess";
		$fp = fopen($fileName, "w");
		fputs($fp, "Options -Indexes");
		fclose($fp);
	}

	function delMode($id) {
		global $init;

		if(strcmp($id, "") == 0) {
			$dirName = "{$init->dirName}";
		} else {
			$dirName = "{$init->dirName}.bak{$id}";
		}
		$this->rmTree($dirName);
	}

	function timeMode() {
		$year = $this->dataSet['YEAR'];
		$day = $this->dataSet['DATE'];
		$mon = $this->dataSet['MON'];
		$hour = $this->dataSet['HOUR'];
		$min = $this->dataSet['MIN'];
		$sec = $this->dataSet['NSEC'];
		$ctSec = mktime($hour, $min, $sec, $mon, $day, $year);
		$this->stimeMode($ctSec);
	}

	function stimeMode($sec) {
		global $init;

		$fileName = "{$init->dirName}/hakojima.dat";
		$fp = fopen($fileName, "r+");
		$buffer = array();
		while($line = fgets($fp, READ_LINE)) {
			array_push($buffer, $line);
		}
		$buffer[1] = "{$sec}\n";
		fseek($fp, 0);
		while($line = array_shift($buffer)) {
			fputs($fp, $line);
		}
		fclose($fp);
	}

	function currentMode($id) {
		global $init;

		$this->rmTree("{$init->dirName}");
		// mkdir("{$init->dirName}", $init->dirMode);
		$dir = opendir("{$init->dirName}.bak{$id}/");
		while($fileName = readdir($dir)) {
			if(!(strcmp($fileName, ".") == 0 || strcmp($fileName, "..") == 0))
				copy("{$init->dirName}.bak{$id}/{$fileName}", "{$init->dirName}/{$fileName}");
		}
		closedir($dir);
	}

	function rmTree($dirName) {
		if(is_dir("{$dirName}")) {
			$dir = opendir("{$dirName}/");
			while($fileName = readdir($dir)) {
				if(!(strcmp($fileName, ".") == 0 || strcmp($fileName, "..") == 0))
					unlink("{$dirName}/{$fileName}");
			}
			closedir($dir);
			// rmdir($dirName);
		}
	}

	function setupMode() {
		global $init;

		if(empty($this->dataSet['MPASS1']) || empty($this->dataSet['MPASS2']) || strcmp($this->dataSet['MPASS1'], $this->dataSet['MPASS2'])) {
			echo "<h2>マスタパスワードが入力されていないか間違っています</h2>\n";
			return 0;
		} else if(empty($this->dataSet['SPASS1']) || empty($this->dataSet['SPASS2']) || strcmp($this->dataSet['SPASS1'], $this->dataSet['SPASS2'])) {
			echo "<h2>特殊パスワードが入力されていないか間違っています</h2>\n";
			return 0;
		}
		$masterPassword = crypt($this->dataSet['MPASS1'], 'ma');
		$specialPassword = crypt($this->dataSet['SPASS1'], 'sp');
		$fp = fopen("{$init->passwordFile}", "w");
		fputs($fp, "$masterPassword\n");
		fputs($fp, "$specialPassword\n");
		fclose($fp);
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
