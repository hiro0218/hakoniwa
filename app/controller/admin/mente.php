<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once MODEL_PATH.'/Admin.php';
require_once MODEL_PATH.'/Cgi.php';
require_once PRESENTER_PATH.'/HtmlMente.php';

class Mente extends Admin {

	function execute() {
		$html = new HtmlMente();
		$cgi = new Cgi();
		$this->parseInputData();
		$cgi->getCookies();
		$html->header();
		switch($this->mode) {
			case "NEW":
				if($this->passCheck()) {
					$this->newMode();
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

	function newMode() {
		global $init;

		mkdir($init->dirName, $init->dirMode, true);

		// 現在の時間を取得
		$now = $_SERVER['REQUEST_TIME'];
		$now = $now - ($now % ($init->unitTime));
		$fileName = "{$init->dirName}/hakojima.dat";
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
		// $fileName = "{$init->dirName}/.htaccess";
		// $fp = fopen($fileName, "w");
		// fputs($fp, "Options -Indexes");
		// fclose($fp);
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

	function rmTree($dirName) {
		if(is_dir("{$dirName}")) {
			$dir = opendir("{$dirName}/");
			while($fileName = readdir($dir)) {
				if(!(strcmp($fileName, ".") == 0 || strcmp($fileName, "..") == 0))
					unlink("{$dirName}/{$fileName}");
			}
			closedir($dir);
			rmdir($dirName);
		}
	}

	function setupMode() {
		global $init;

		if(empty($this->dataSet['MPASS1']) || empty($this->dataSet['MPASS2']) || strcmp($this->dataSet['MPASS1'], $this->dataSet['MPASS2'])) {
			HakoError::wrongMasterPassword();
			return 0;
		} else if(empty($this->dataSet['SPASS1']) || empty($this->dataSet['SPASS2']) || strcmp($this->dataSet['SPASS1'], $this->dataSet['SPASS2'])) {
			HakoError::wrongSpecialPassword();
			return 0;
		}
		$masterPassword  = crypt($this->dataSet['MPASS1'], 'ma');
		$specialPassword = crypt($this->dataSet['SPASS1'], 'sp');
		$fp = fopen("{$init->passwordFile}", "w");
		fputs($fp, "$masterPassword\n");
		fputs($fp, "$specialPassword\n");
		fclose($fp);
	}

}
