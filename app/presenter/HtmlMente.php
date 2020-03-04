<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlMente extends HTML {
    public $this_file = '';

	function __construct() {
        global $init;

		$this->this_file = $init->baseDir. "/hako-mente.php";
        $this->exist_log = is_dir("{$init->dirName}");
    }

	function enter() {
		global $init;

		require_once(VIEWS_PATH.'/admin/mente/top.php');
	}

	function main($data) {
		global $init;

        $this->initData($data);

        require_once(VIEWS_PATH.'/admin/mente/main.php');
	}

	// 表示モード
	function initData($data, $suf = "") {
		global $init;

        $this->is_backup = strcmp($suf, "") !== 0;
        $file_dat = !$this->is_backup ? "{$init->dirName}/hakojima.dat" : "{$init->dirName}.bak{$suf}/hakojima.dat";
        $this->readDatFile($file_dat);
        $this->timeString = self::timeToString($this->lastTime);

        if(!$this->is_backup) {
            $this->time = localtime($this->lastTime, TRUE);
            $this->time['tm_year'] += 1900;
            $this->time['tm_mon']++;
        }
    }

    function readDatFile($file_path) {
        $fp = fopen($file_path, "r");

        $this->lastTurn = chop(fgets($fp, READ_LINE));
        $this->lastTime = chop(fgets($fp, READ_LINE));

        fclose($fp);
    }

}
