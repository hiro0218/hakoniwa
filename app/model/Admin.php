<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

require_once HELPER_PATH.'/message/error.php';

class Admin {
    public $mode;
    public $dataSet = array();

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
