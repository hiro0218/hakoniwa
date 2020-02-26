<?php
/**
 * 箱庭諸島 S.E - ログ出力用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

class LogIO {
    public $this_file = '';
    public $init;

	private $logPool       = array();
	private $secretLogPool = array();
	private $lateLogPool   = array();


	public function __construct() {
		global $init;
        $this->init = $init;
		$this->this_file = $init->baseDir . "/hako-main.php";
	}

	/**
	 * ログファイルを後ろにずらす
	 * @return [type] [description]
	 */
	function slideBackLogFile() {
		for($i = $this->init->logMax - 1; $i >= 0; $i--) {
			$j = $i + 1;
			$s = "{$this->init->dirName}/hakojima.log{$i}";
			$d = "{$this->init->dirName}/hakojima.log{$j}";
			if(is_file($s)) {
				if(is_file($d)) {
					unlink($d);
				}
				rename($s, $d);
			}
		}
	}
	//---------------------------------------------------
	// 最近の出来事を出力
	//---------------------------------------------------
	function logFilePrint($num = 0, $id = 0, $mode = 0) {
		global $init;
		$fileName = $init->dirName . "/hakojima.log" . $num;
		if(!is_file($fileName)) {
			return;
		}
		$fp = fopen($fileName, "r");
		$row = 1;

		echo "<div>";
		while($line = chop(fgets($fp, READ_LINE))) {
			list($m, $turn, $id1, $id2, $message) = explode(",", $line, 5);
			if($m == 1) {
				if(($mode == 0) || ($id1 != $id)) {
					continue;
				}
				$m = "<strong>(機密)</strong>";
			} else {
				$m = "";
			}
			if($id != 0) {
				if(($id != $id1) && ($id != $id2)) {
					continue;
				}
			}
			if($row == 1) {
				echo "<h2>{$init->tagNumber_}ターン{$turn}の出来事{$init->_tagNumber}</h2>\n";
				$row++;
			}
			echo "<ul class='list-unstyled'>";
			echo "<li>{$message}</li>\n";
			echo "</ul>";
		}
		echo "</div>";

		fclose($fp);
	}
	//---------------------------------------------------
	// 発見の記録を出力
	//---------------------------------------------------
	function historyPrint() {
		$fileName = $this->init->dirName . "/hakojima.his";

		if(!is_file($fileName)) {
			return;
		}

		$fp = fopen($fileName, "r");
		$history = array();
		$k = 0;
		while($line = chop(fgets($fp, READ_LINE))) {
			array_push($history, $line);
			$k++;
		}

		for($i = 0; $i < $k; $i++) {
			list($turn, $his) = explode(",", array_pop($history), 2);
			echo "<li>{$this->init->tagNumber_}ターン{$turn}{$this->init->_tagNumber}：$his</li>\n";
		}

	}
	//---------------------------------------------------
	// 発見の記録を保存
	//---------------------------------------------------
	function history($str) {
		$fileName = "{$this->init->dirName}/hakojima.his";

		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "a");
		fputs($fp, "{$GLOBALS['ISLAND_TURN']},{$str}\n");
		fclose($fp);
		// chmod($fileName, 0666);
	}
	//---------------------------------------------------
	// 発見の記録ログ調整
	//---------------------------------------------------
	function historyTrim() {
		$count = 0;
		$fileName = "{$this->init->dirName}/hakojima.his";

		if(is_file($fileName)) {
			$fp = fopen($fileName, "r");

			$line = array();
			while($l = chop(fgets($fp, READ_LINE))) {
				array_push($line, $l);
				$count++;
			}
			fclose($fp);
			if($count > $this->init->historyMax) {
				if(!is_file($fileName)) {
					touch($fileName);
				}
				$fp = fopen($fileName, "w");
				for($i = ($count - $this->init->historyMax); $i < $count; $i++) {
					fputs($fp, "{$line[$i]}\n");
				}
				fclose($fp);
				// chmod($fileName, 0666);
			}
		}
	}
	//---------------------------------------------------
	// ログ
	//---------------------------------------------------
	function out($str, $id = "", $tid = "") {
		array_push($this->logPool, "0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// 機密ログ
	//---------------------------------------------------
	function secret($str, $id = "", $tid = "") {
		array_push($this->secretLogPool,"1,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// 遅延ログ
	//---------------------------------------------------
	function late($str, $id = "", $tid = "") {
		array_push($this->lateLogPool,"0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// ログ書き出し
	//---------------------------------------------------
	function flush() {
		$fileName = "{$this->init->dirName}/hakojima.log0";

		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "w");

		// 全部逆順にして書き出す
		if(!empty($this->secretLogPool)) {
			for($i = count($this->secretLogPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->secretLogPool[$i]}\n");
			}
		}
		if(!empty($this->lateLogPool)) {
			for($i = count($this->lateLogPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->lateLogPool[$i]}\n");
			}
		}
		if(!empty($this->logPool)) {
			for($i = count($this->logPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->logPool[$i]}\n");
			}
		}
		fclose($fp);
		// chmod($fileName, 0666);
	}

	/**
	 * お知らせを出力
	 * @return [type] [description]
	 */
	function infoPrint() {
		if($this->init->noticeFile == "") {
			return;
		}

		$fileName = "{$this->init->noticeFile}";
		if(!is_file($fileName)) {
			return;
		}

		$fp = fopen($fileName, "r");
		while($line = fgets($fp, READ_LINE)) {
			$line = chop($line);
			echo "{$line}<br>\n";
		}
		fclose($fp);

	}
}
