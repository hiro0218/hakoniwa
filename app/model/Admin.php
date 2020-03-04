<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once MODEL_PATH.'/Cgi.php';
require_once HELPER_PATH.'/message/error.php';

class Admin {
 	public $init;

    public $mode;
    public $dataSet = [];

    function __construct() {
        $this->initAdmin();
    }

    function initAdmin() {
 		global $init;

 		$this->init = $init;

 		$cgi = new Cgi();
 		$cgi->getCookies();

 		$this->parseInputData();
    }

    function parseInputData() {
        // mode
        $this->mode = $_POST['mode'] ?? "";

        // POST内容をdataSetへ
        $this->dataSet = Util::getParsePostData();
    }

    function passCheck() {
		global $init;

		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
        if ( !isset($this->dataSet['PASSWORD']) ) {
            ErrorHandler::wrongPassword();
            return 0;
        }
		if(strcmp(crypt($this->dataSet['PASSWORD'], 'ma'), $masterPassword) == 0) {
			return 1;
		} else {
			ErrorHandler::wrongPassword();
			return 0;
		}

	}
}
