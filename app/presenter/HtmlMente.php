<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlMente extends HTML {
    public $this_file = '';

	function __construct() {
        global $init;

		$this->this_file = $init->baseDir. "/hako-mente.php";
    }

	function enter() {
		global $init;

		require_once(VIEWS_PATH.'/admin/mente/top.php');
	}

	function main($data) {
		global $init;

		require_once(VIEWS_PATH.'/admin/mente/main.php');

        // バックアップデータ
        $this->backupPrint($data);
	}

	// 表示モード
	function dataPrint($data, $suf = "") {
		global $init;

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
		$timeString = self::timeToString($lastTime);
		echo <<<END
<strong>ターン$lastTurn</strong><br>
<strong>最終更新時間</strong>:$timeString<br>
<strong>最終更新時間(秒数表示)</strong>:1970年1月1日から$lastTime 秒<br>
<form action="{$this->this_file}" method="post">
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
<form action="{$this->this_file}" method="post">
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
<form action="{$this->this_file}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="STIME">
	<input type="hidden" name="NUMBER" value="{$suf}">
	1970年1月1日から<input type="text" size="32" name="SSEC" value="$lastTime">秒
	<input type="submit" value="秒指定で変更">
</form>
END;
		}
    }

    function backupPrint($data) {
		global $init;

        $dir = opendir("./");
		while($dn = readdir($dir)) {
            $_dirName = preg_quote($init->dirName, "/");

			if(preg_match("/{$_dirName}\.bak(.*)$/", $dn, $suf)) {
				if (is_file("{$init->dirName}.bak{$suf[1]}/hakojima.dat")) {
					$this->dataPrint($data, $suf[1]);
				}
			}
        }
		closedir($dir);
    }

}
