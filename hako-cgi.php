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
		
		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				// $value = Util::sjis_convert($value);
				// 半角カナがあれば全角に変換して返す
				// $value = i18n_ja_jp_hantozen($value,"KHV");
				$value = str_replace(",", "", $value);
				$value = JcodeConvert($value, 0, 2);
				$value = HANtoZEN_SJIS($value);
				if($init->stripslashes == true) {
					$this->dataSet["{$name}"] = stripslashes($value);
				} else {
					$this->dataSet["{$name}"] = $value;
				}
			}
		}
		if(!empty($this->dataSet['IMGLINE'])) {
			$neo = $this->dataSet['IMGLINE'];
			if(strcmp($neo, 'delete') == 0) {
				$neo = $init->imgDir;
			} else {
				$neo = str_replace("\\", "/", $neo);
				$neo = preg_replace("/\/[\w\.]+\.gif/", "", $neo);
				$neo = 'file:///' . $neo;
			}
			$this->dataSet['IMG'] = $neo;
		}
		if(!empty($_GET['Sight'])) {
			$this->mode = "print";
			$this->dataSet['ISLANDID'] = $_GET['Sight'];
		}
		if(!empty($_GET['target'])) {
			$this->mode = "targetView";
			$this->dataSet['ISLANDID'] = $_GET['target'];
		}
		if($_GET['mode'] == "conf") {
			$this->mode = "conf";
		}
		if($_GET['mode'] == "log") {
			$this->mode = "log";
		}
		$init->adminMode = 0;
		if(empty($_GET['AdminButton'])) {
			if(Util::checkPassword("", $this->dataSet['PASSWORD'])) { $init->adminMode = 1; }
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
		$this->dataSet["ISLANDNAME"] = jsubstr($this->dataSet["ISLANDNAME"], 0, 16);
		$this->dataSet["MESSAGE"] = jsubstr($this->dataSet["MESSAGE"], 0, 60);
		$this->dataSet["LBBSMESSAGE"] = jsubstr($this->dataSet["LBBSMESSAGE"], 0, 60);
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
		header ("Last-Modified: $time GMT");
		$this->modifiedSinces($time_stamp);
	}
	
	function modifiedSinces($time) {
		$modsince = $_SERVER{'HTTP_IF_MODIFIED_SINCE'};
		
		$ms = gmdate("D, d M Y G:i:s", $time) . " GMT";
		if($modsince == $ms) {
			// RFC 822
			header ("HTTP/1.1 304 Not Modified\n");
		}
		$ms = gmdate("l, d-M-y G:i:s", $time) . " GMT";
		if($modsince == $ms) {
			// RFC 850
			header ("HTTP/1.1 304 Not Modified\n");
		}
		$ms = gmdate("D M j G:i:s Y", $time);
		if($modsince == $ms) {
			// ANSI C's asctime() format
			header ("HTTP/1.1 304 Not Modified\n");
		}
	}
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
						
					case "LBBSNAME":
						$this->dataSet['defaultName'] = $value;
						break;
						
					case "LBBSCOLOR":
						$this->dataSet['defaultColor'] = $value;
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
						
					case "SKIN":
						$this->dataSet['defaultSkin'] = $value;
						break;
						
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
		$time = time() + 30 * 86400; // 現在 + 30日有効
		
		// Cookieの設定 & POSTで入力されたデータで、Cookieから取得したデータを更新
		if($this->dataSet['ISLANDID'] && $this->mode == "owner") {
			setcookie("OWNISLANDID",$this->dataSet['ISLANDID'], $time);
			$this->dataSet['defaultID'] = $this->dataSet['ISLANDID'];
		}
		if($this->dataSet['PASSWORD']) {
			setcookie("OWNISLANDPASSWORD",$this->dataSet['PASSWORD'], $time);
			$this->dataSet['defaultPassword'] = $this->dataSet['PASSWORD'];
		}
		if($this->dataSet['TARGETID']) {
			setcookie("TARGETISLANDID",$this->dataSet['TARGETID'], $time);
			$this->dataSet['defaultTarget'] = $this->dataSet['TARGETID'];
		}
		if($this->dataSet['LBBSNAME']) {
			setcookie("LBBSNAME",$this->dataSet['LBBSNAME'], $time);
			$this->dataSet['defaultName'] = $this->dataSet['LBBSNAME'];
		}
		if($this->dataSet['LBBSCOLOR']) {
			setcookie("LBBSCOLOR",$this->dataSet['LBBSCOLOR'], $time);
			$this->dataSet['defaultColor'] = $this->dataSet['LBBSCOLOR'];
		}
		if($this->dataSet['POINTX']) {
			setcookie("POINTX",$this->dataSet['POINTX'], $time);
			$this->dataSet['defaultX'] = $this->dataSet['POINTX'];
		}
		if($this->dataSet['POINTY']) {
			setcookie("POINTY",$this->dataSet['POINTY'], $time);
			$this->dataSet['defaultY'] = $this->dataSet['POINTY'];
		}
		if($this->dataSet['COMMAND']) {
			setcookie("COMMAND",$this->dataSet['COMMAND'], $time);
			$this->dataSet['defaultKind'] = $this->dataSet['COMMAND'];
		}
		if($this->dataSet['DEVELOPEMODE']) {
			setcookie("DEVELOPEMODE",$this->dataSet['DEVELOPEMODE'], $time);
			$this->dataSet['defaultDevelopeMode'] = $this->dataSet['DEVELOPEMODE'];
		}
		if($this->dataSet['SKIN']) {
			setcookie("SKIN",$this->dataSet['SKIN'], $time);
			$this->dataSet['defaultSkin'] = $this->dataSet['SKIN'];
		}
		if($this->dataSet['IMG']) {
			setcookie("IMG",$this->dataSet['IMG'], $time);
			$this->dataSet['defaultImg'] = $this->dataSet['IMG'];
		}
	}
}

?>
