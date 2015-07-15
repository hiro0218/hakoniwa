<?php
/*******************************************************************

	箱庭諸島 S.E

	- プレゼント定義用ファイル -

	hako-present.php by SERA - 2012/04/03

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-init.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-file.php';
require_once ABSOLUTE_PATH.'hako-html.php';

$init = new Init();
$THIS_FILE = $init->baseDir . "/hako-present.php";
$MAIN_FILE = $init->baseDir . "/hako-main.php";

//--------------------------------------------------------------------
class HtmlPresent extends HTML {
	function enter() {
		global $init;

		echo <<<END
<h1 class="title">プレゼントツール</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="メンテナンス">
</form>
END;
	}

	function main($data, $hako) {
		global $init;

		$width = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;
		$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;

		echo <<<END
<script type="text/javascript">
<!--
var w;
var p = 0;

function settarget(part){
	p = part.options[part.selectedIndex].value;
}

function targetopen() {
	w = window.open("{$GLOBALS['MAIN_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}
//-->
</script>

<h1 class="title">プレゼントツール</h1>

<h2>管理人からのプレゼント</h2>
<p>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<select name="ISLANDID">
$hako->islandList
</select>に、
資金：<input type="text" size="10" name="MONEY" value="0">{$init->unitMoney}、
食料：<input type="text" size="10" name="FOOD" value="0">{$init->unitFood}を
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="PRESENT">
<input type="submit" value="プレゼントする">
</form>
</p>
<h2>管理人からの災害プレゼント&hearts;</h2>
<p>
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<select name="ISLANDID" onchange="settarget(this);">
$hako->islandList
</select>の、(
<select name="POINTX">
END;
		echo "<option value=\"0\" selected>0</option>\n";
		for($i = 1; $i < $init->islandSize; $i++) {
			echo "<option value=\"{$i}\">{$i}</option>\n";
		}
		echo "</select>, <select name=\"POINTY\">";
		echo "<option value=\"0\" selected>0</option>\n";
		for($i = 1; $i < $init->islandSize; $i++) {
			echo "<option value=\"{$i}\">{$i}</option>\n";
		}
		echo <<<END
</select> )に、
<select name="PUNISH">
<option VALUE="0">キャンセル</option>
<option VALUE="1">地震</option>
<option VALUE="2">津波</option>
<option VALUE="3">怪獣</option>
<option VALUE="4">地盤沈下</option>
<option VALUE="5">台風</option>
<option VALUE="6">巨大隕石○</option>
<option VALUE="7">隕石○</option>
<option VALUE="8">噴火○</option>
</select>を
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="PUNISH">
<input type="submit" value="プレゼントしちゃう"><br>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
</form>
</p>
<h2>現在のプレゼントリスト</h2>
END;
		for ($i=0; $i < $hako->islandNumber; $i++) {
			$present =&$hako->islands[$i]['present'];
			$name =&$hako->islands[$i]['name'];
			if ( $present['item'] == 0 ) {
				if ( $present['px'] != 0 ) {
					$money = $present['px'] . $init->unitMoney;
					echo "{$init->tagName_}{$name}島{$init->_tagName}に<strong>{$money}</strong>の資金<br>\n";
				}
				if ( $present['py'] != 0 ) {
					$food = $present['py'] . $init->unitFood;
					echo "{$init->tagName_}{$name}島{$init->_tagName}に<strong>{$food}</strong>の食料<br>\n";
				}
			} elseif ( $present['item'] > 0 ) {
				$items = array ('地震','津波','怪獣','地盤沈下','台風','巨大隕石','隕石','噴火');
				$item = $items[$present['item'] - 1];
				if ( $present['item'] < 9 ) {
					$point = ($present['item'] < 6) ? '' : '(' . $present['px'] . ',' . $present['py'] . ')';
					echo "{$init->tagName_}{$name}島{$point}{$init->_tagName}に{$init->tagDisaster_}{$item}{$init->_tagDisaster}<br>\n";
				}
			}
		}
	}
}

class HakoPresent extends File {
	var $islandList;  // 島リスト

	function init($cgi) {
		$this->readIslandsFile($cgi);
		$this->readPresentFile();

		$this->islandList = "<option value=\"0\"></option>\n";
		for($i = 0; $i < ( $this->islandNumber ); $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$this->islandList .= "<option value=\"$id\">${name}島</option>\n";
		}
	}
}

class Main {
	public $mode;
	public $dataSet = array();

	function execute() {
		$html = new HtmlPresent();
		$hako =& new HakoPresent();
		$cgi = new Cgi();
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header($cgi->dataSet);

		switch($this->mode) {
			case "PRESENT":
				if($this->passCheck()) {
					$this->present($this->dataSet, $hako);
				}
				$html->main($this->dataSet, $hako);
				break;

			case "PUNISH":
				if($this->passCheck()) {
					$this->punish($this->dataSet, $hako);
				}
				$html->main($this->dataSet, $hako);
				break;

			case "enter":
				if($this->passCheck()) {
					$html->main($this->dataSet, $hako);
				}
				break;

			default:
				$html->enter();
				break;
		}
		$html->footer();
	}

	function parseInputData() {
		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				// 半角カナがあれば全角に変換して返す
				// JcodeConvert($value, 0, 2);
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}

	function present($data, &$hako) {
		global $init;

		if ($data['ISLANDID']) {
			$num = $hako->idToNumber[$data['ISLANDID']];
			$hako->islands[$num]['present']['item'] = 0;
			$hako->islands[$num]['present']['px'] = $data['MONEY'];
			$hako->islands[$num]['present']['py'] = $data['FOOD'];
			$hako->writePresentFile();
		}
	}

	function punish($data, &$hako) {
		global $init;

		if ($data['ISLANDID']) {
			$punish =& $data['PUNISH'];
			if (( $punish >= 0) && ( $punish <= 8 )) {
				$num = $hako->idToNumber[$data['ISLANDID']];
				$hako->islands[$num]['present']['item'] = $punish;
				$hako->islands[$num]['present']['px'] = ( $punish < 6 ) ? 0 : $data['POINTX'];
				$hako->islands[$num]['present']['py'] = ( $punish < 6 ) ? 0 : $data['POINTY'];
				$hako->writePresentFile();
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
		if(strcmp(crypt($this->dataSet['PASSWORD'], 'ma'), $masterPassword) == 0) {
			return 1;
		} else {
			echo "<h2>パスワードが違います。</h2>\n";
			return 0;
		}
	}
}

$start = new Main();
$start->execute();
