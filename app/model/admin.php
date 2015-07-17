<?php
/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

class Admin {
    public $mode;
    public $dataSet = array();

    function parseInputData() {
        $this->mode = isset($_POST['mode']) ? $_POST['mode'] : "";

        if(!empty($_POST)) {
            while(list($name, $value) = each($_POST)) {
                $value = str_replace(",", "", $value);
                $this->dataSet["{$name}"] = $value;
            }
        }
    }

    function passCheck() {
		global $init;

		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
        if ( !isset($this->dataSet['PASSWORD']) ) {
            Error::wrongPassword();
            return 0;
        }
		if(strcmp(crypt($this->dataSet['PASSWORD'], 'ma'), $masterPassword) == 0) {
			return 1;
		} else {
			Error::wrongPassword();
			return 0;
		}

	}
}
