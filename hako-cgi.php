<?php

/*******************************************************************

	箱庭諸島 S.E

	- Cookie定義用ファイル -

	hako-cgi.php by SERA - 2012/04/03

*******************************************************************/

class Cgi {
	var $mode = "";
	var $dataSet = array();
	//---------------------------------------------------
	// POST、GETのデータを取得
	//---------------------------------------------------
	function parseInputData() {
		global $init;

		$this->mode = (array_key_exists('mode', $_POST)) ? $_POST['mode'] : "";

		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				// $value = Util::sjis_convert($value);
				// 半角カナがあれば全角に変換して返す
				// $value = i18n_ja_jp_hantozen($value,"KHV");
				$value = str_replace(",", "", $value);
				// $value = JcodeConvert($value, 0, 2);
				// $value = HANtoZEN_UTF8($value);
				if($init->stripslashes == true) {
					$this->dataSet["{$name}"] = stripslashes($value);
				} else {
					$this->dataSet["{$name}"] = $value;
				}
			}
		}

		if(!empty($_GET['Sight'])) {
			$this->mode = "print";
			$this->dataSet['ISLANDID'] = $_GET['Sight'];
		}
		if(!empty($_GET['target'])) {
			$this->mode = "targetView";
			$this->dataSet['ISLANDID'] = $_GET['target'];
		}

		$getMode = (array_key_exists('mode', $_GET)) ? $_GET['mode'] : "";
		if($getMode == "conf") {
			$this->mode = "conf";
		}
		if($getMode == "log") {
			$this->mode = "log";
		}
		$init->adminMode = 0;
		if(empty($_GET['AdminButton'])) {
			$_password = (isset( $this->dataSet['PASSWORD'] )) ? $this->dataSet['PASSWORD'] : "";

			if(Util::checkPassword("", $_password)) {
				$init->adminMode = 1;
				}
		}
		if($this->mode == "turn") {
			// この段階で mode に turn がセットされるのは不正アクセスがある場合のみなのでクリアする
			$this->mode = '';
		}
		if(!empty($_GET['islandListStart'])) {
			$this->dataSet['islandListStart'] = $_GET['islandListStart'];
		} else {
			$this->dataSet['islandListStart'] = 1;
		}

		$this->dataSet["ISLANDNAME"]  = (isset( $this->dataSet['ISLANDNAME'] ))  ? mb_substr($this->dataSet["ISLANDNAME"], 0, 16) : "";
		$this->dataSet["MESSAGE"]     = (isset( $this->dataSet['MESSAGE'] ))     ? mb_substr($this->dataSet["MESSAGE"], 0, 60) : "";
	}

	function lastModified() {
		global $init;

		// Last Modifiedヘッダを出力
		/*
		if($this->mode == "Sight") {
			$fileName = "{$init->dirName}/island.{$this->dataSet['ISLANDID']}";
		} else {
			$fileName = "{$init->dirName}/hakojima.dat";
		}
		*/
		$fileName = "{$init->dirName}/hakojima.dat";
		$time_stamp = filemtime($fileName);
		$time = gmdate("D, d M Y G:i:s", $time_stamp);
		//header ("Last-Modified: $time GMT");
		//$this->modifiedSinces($time_stamp);
	}

	// function modifiedSinces($time) {
	// 	$modsince = "";
	// 	if(isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])){
	// 		$modsince = $_SERVER["HTTP_IF_MODIFIED_SINCE"];
	// 	}
	// 	if ( isset($modsince) ) {
	// 		$ms = gmdate("D, d M Y G:i:s", $time) . " GMT";
	// 		if($modsince == $ms) {
	// 			// RFC 822
	// 			header ("HTTP/1.1 304 Not Modified\n");
	// 		}
	// 		$ms = gmdate("l, d-M-y G:i:s", $time) . " GMT";
	// 		if($modsince == $ms) {
	// 			// RFC 850
	// 			header ("HTTP/1.1 304 Not Modified\n");
	// 		}
	// 		$ms = gmdate("D M j G:i:s Y", $time);
	// 		if($modsince == $ms) {
	// 			// ANSI C's asctime() format
	// 			header ("HTTP/1.1 304 Not Modified\n");
	// 		}
	// 	}
	// }

	//---------------------------------------------------
	// COOKIEを取得
	//---------------------------------------------------
	function getCookies() {
		if(!empty($_COOKIE)) {
			while(list($name, $value) = each($_COOKIE)) {
				switch($name) {
					case "OWNISLANDID":
						$this->dataSet['defaultID'] = $value;
						break;

					case "OWNISLANDPASSWORD":
						$this->dataSet['defaultPassword'] = $value;
						break;

					case "TARGETISLANDID":
						$this->dataSet['defaultTarget'] = $value;
						break;

					case "POINTX":
						$this->dataSet['defaultX'] = $value;
						break;

					case "POINTY":
						$this->dataSet['defaultY'] = $value;
						break;

					case "COMMAND":
						$this->dataSet['defaultKind'] = $value;
						break;

					case "DEVELOPEMODE":
						$this->dataSet['defaultDevelopeMode'] = $value;
						break;

					// case "SKIN":
					// 	$this->dataSet['defaultSkin'] = $value;
					// 	break;

					case "IMG":
						$this->dataSet['defaultImg'] = $value;
						break;
				}
			}
		}
	}

	//---------------------------------------------------
	// COOKIEを生成
	//---------------------------------------------------
	function setCookies() {
		$time = $_SERVER['REQUEST_TIME'] + 30 * 86400; // 現在 + 30日有効

		// Cookieの設定 & POSTで入力されたデータで、Cookieから取得したデータを更新
		if( isset($this->dataSet['ISLANDID']) && $this->mode == "owner") {
			setcookie("OWNISLANDID",$this->dataSet['ISLANDID'], $time);
			$this->dataSet['defaultID'] = $this->dataSet['ISLANDID'];
		}
		if( isset($this->dataSet['PASSWORD']) ) {
			setcookie("OWNISLANDPASSWORD",$this->dataSet['PASSWORD'], $time);
			$this->dataSet['defaultPassword'] = $this->dataSet['PASSWORD'];
		}
		if( isset($this->dataSet['TARGETID']) ) {
			setcookie("TARGETISLANDID",$this->dataSet['TARGETID'], $time);
			$this->dataSet['defaultTarget'] = $this->dataSet['TARGETID'];
		}

		if( isset($this->dataSet['POINTX']) ) {
			setcookie("POINTX",$this->dataSet['POINTX'], $time);
			$this->dataSet['defaultX'] = $this->dataSet['POINTX'];
		}
		if( isset($this->dataSet['POINTY']) ) {
			setcookie("POINTY",$this->dataSet['POINTY'], $time);
			$this->dataSet['defaultY'] = $this->dataSet['POINTY'];
		}
		if( isset($this->dataSet['COMMAND']) ) {
			setcookie("COMMAND",$this->dataSet['COMMAND'], $time);
			$this->dataSet['defaultKind'] = $this->dataSet['COMMAND'];
		}
		if( isset($this->dataSet['DEVELOPEMODE']) ) {
			setcookie("DEVELOPEMODE",$this->dataSet['DEVELOPEMODE'], $time);
			$this->dataSet['defaultDevelopeMode'] = $this->dataSet['DEVELOPEMODE'];
		}
		// if( isset($this->dataSet['SKIN']) ) {
		// 	setcookie("SKIN",$this->dataSet['SKIN'], $time);
		// 	$this->dataSet['defaultSkin'] = $this->dataSet['SKIN'];
		// }
		if( isset($this->dataSet['IMG']) ) {
			setcookie("IMG",$this->dataSet['IMG'], $time);
			$this->dataSet['defaultImg'] = $this->dataSet['IMG'];
		}
	}
}
