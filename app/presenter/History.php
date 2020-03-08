<?php

namespace Presenter;

require_once PRESENTER_PATH.'/HTML.php';

class History extends \HTML {

    private $data = [];

    function __construct() {
        $this->log = new \Log();
    }

	public function setData($key, $value) {
		$this->data[$key] = $value;
    }

	public function getData($key) {
		return $this->data[$key] ?? '';
    }

    /**
     * renderPage
     */
    function renderPage() {
        $this->setRecentData();
        $this->setHistoryData();

        echo $this->render(VIEWS_PATH.'/history/index.php');
    }

    /**
     * 最近の出来事
     * @return void
     */
    function setRecentData() {
        $logData = $this->getRecentLog();
        $this->setData('recent', $logData);
    }

    /**
     * 歴史
     * @return void
     */
    function setHistoryData() {
        $historyData = $this->log->historyPrint();
        $this->setData('history', $historyData);
    }

    /**
     * getRecentLog
     * @return String
     */
    function getRecentLog() {
        global $init;

        $logData = '';

        for($i = 0; $i < $init->logTopTurn; $i++) {
            $logData .= $this->log->logFilePrint($i, 0, 0);
        }

        return $logData;
    }
}
