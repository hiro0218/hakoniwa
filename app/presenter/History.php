<?php

namespace Presenter;

require_once PRESENTER_PATH.'/HTML.php';

class History extends \HTML {

	private $data = [];

	public function setData($key, $value) {
		$this->data[$key] = $value;
    }

	public function getData($key) {
		return $this->data[$key] ?? '';
	}

    /**
     * 最近の出来事
     * @return void
     */
    function render_recent() {
        // ログデータを取得
        $logData = $this->getLog();
        $this->setData('log', $logData);

        echo $this->render(VIEWS_PATH.'/log/recent.php');
    }

    /**
     * getLog
     * @return String
     */
    function getLog() {
        global $init;

        $logData = '';
        $log = new \Log();

        for($i = 0; $i < $init->logTopTurn; $i++) {
            $logData .= $log->logFilePrint($i, 0, 0);
        }

        return $logData;
    }
}
