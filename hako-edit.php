<?php

/*******************************************************************

	箱庭諸島 S.E

	- 島編集用ファイル -

	hako-edit.php by SERA - 2013/06/02

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-file.php';
require_once ABSOLUTE_PATH.'hako-html.php';
require_once ABSOLUTE_PATH.'hako-util.php';

$init = new Init();

define("READ_LINE", 1024);
$THIS_FILE = $init->baseDir . "/hako-edit.php";
$BACK_TO_TOP = "<A HREF=\"JavaScript:void(0);\" onClick=\"document.TOP.submit();return false;\">{$init->tagBig_}トップへ戻る{$init->_tagBig}</A>";

ini_set('display_errors', 0);

//----------------------------------------------------------------------
class Hako extends HakoIO {

	function readIslands(&$cgi) {
		global $init;

		$m = $this->readIslandsFile($cgi);
		return $m;
	}

	//---------------------------------------------------
	// 地形に関するデータ生成
	//---------------------------------------------------
	function landString($l, $lv, $x, $y, $mode, $comStr) {
		global $init;
		$point = "({$x},{$y})";
		$naviExp = "''";

		if($x < $init->islandSize / 2) {
			$naviPos = 0;
		} else {
			$naviPos = 1;
		}
		switch($l) {
			case $init->landSea:
				if($lv == 0) {
					// 海
					$image = 'land0.gif';
					$naviTitle = '海';
				} elseif($lv == 1) {
					// 浅瀬
					$image = 'land14.gif';
					$naviTitle = '浅瀬';
				} else {
					// 財宝？
					$image = 'land17.gif';
					$naviTitle = '海';
					$naviText = "{$lv}";
				}
				break;

			case $init->landSeaCity:
				// 海底都市
				$image = 'SeaCity.gif';
				$naviTitle = '海底都市';
				$naviText = "{$lv}{$init->unitPop}";
				break;

			case $init->landFroCity:
				// 海上都市
				$image = 'FroCity.gif';
				$naviTitle = '海上都市';
				$naviText = "{$lv}{$init->unitPop}";
				break;

			case $init->landPort:
				// 港
				$image = 'port.gif';
				$naviTitle = '港';
				break;

			case $init->landShip:
				// 船舶
				$ship = Util::navyUnpack($lv);
				$owner = $this->idToName[$ship[0]]; // 所属
				$naviTitle = "{$init->shipName[$ship[1]]}"; // 船舶の種類
				$hp = round(100 - $ship[2] / $init->shipHP[$ship[1]] * 100); // 破損率
				if($ship[1] <= 1) {
					// 輸送船、漁船
					$naviText = "{$owner}島所属";
				} elseif($ship[1] == 2) {
					// 海底探索船
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
					if($treasure > 0) {
						$naviText = "{$owner}島所属<br>破損率：{$hp}%<br>{$treasure}億円相当の財宝積載";
					} else {
						$naviText = "{$owner}島所属";
					}
				} elseif($ship[1] < 10) {
					$naviText = "{$owner}島所属<br>破損率：{$hp}%";
				} else {
					// 海賊船
					$naviText = "破損率：{$hp}%";
				}
				$image = "ship{$ship[1]}.gif"; // 船舶画像
				break;

			case $init->landRail:
				// 線路
				$image = "rail{$lv}.gif";
				$naviTitle = '線路';
				break;

			case $init->landStat:
				// 駅
				$image = 'stat.gif';
				$naviTitle = '駅';
				break;

			case $init->landTrain:
				// 電車
				$image = "train{$lv}.gif";
				$naviTitle = '電車';
				break;

			case $init->landZorasu:
				// 海怪獣
				$image = 'zorasu.gif';
				$naviTitle = 'ぞらす';
				break;

			case $init->landSeaSide:
				// 海岸
				$image = 'sunahama.gif';
				$naviTitle = '砂浜';
				break;

			case $init->landSeaResort:
				// 海の家
				if($lv < 30) {
					$image = 'umi1.gif';
					$naviTitle = '海の家';
				} else if($lv < 100) {
					$image = 'umi2.gif';
					$naviTitle = '民宿';
				} else {
					$image = 'umi3.gif';
					$naviTitle = 'リゾートホテル';
				}
				$naviText = "収入:{$lv}{$init->unitPop} <br>";
				break;

			case $init->landSoccer:
				// スタジアム
				$image = 'stadium.gif';
				$naviTitle = 'スタジアム';
				break;

			case $init->landPark:
				// 遊園地
				$image = "park{$lv}.gif";
				$naviTitle = '遊園地';
				break;

			case $init->landFusya:
				// 風車
				$image = 'fusya.gif';
				$naviTitle = '風車';
				break;

			case $init->landSyoubou:
				// 消防署
				$image = 'syoubou.gif';
				$naviTitle = '消防署';
				break;

			case $init->landSsyoubou:
				// 海底消防署
				$image = 'syoubou2.gif';
				$naviTitle = '海底消防署';
				break;

			case $init->landNursery:
				// 養殖場
				$image = 'Nursery.gif';
				$naviTitle = '養殖場';
				$naviText = "{$lv}0{$init->unitPop}規模";
				break;

			case $init->landWaste:
				// 荒地
				if($lv == 1) {
					$image = 'land13.gif'; // 着弾点
				} else {
					$image = 'land1.gif';
				}
				$naviTitle = '荒地';
				break;

			case $init->landPlains:
				// 平地
				$image = 'land2.gif';
				$naviTitle = '平地';
				break;

			case $init->landPoll:
				// 汚染土壌
				$image = 'poll.gif';
				$naviTitle = '汚染土壌';
				$naviText = "汚染レベル{$lv}";
				break;

			case $init->landForest:
				// 森
				if($mode == 1) {
					$image = 'land6.gif';
					$naviText= "${lv}{$init->unitTree}";
				} else {
					// 観光者の場合は木の本数隠す
					$image = 'land6.gif';
				}
				$naviTitle = '森';
				break;

			case $init->landTown:
				// 町
				$p; $n;
				if($lv < 30) {
					$p = 3;
					$naviTitle = '村';
				} else if($lv < 100) {
					$p = 4;
					$naviTitle = '町';
				} else if($lv < 200) {
					$p = 5;
					$naviTitle = '都市';
				} else {
					$p = 52;
					$naviTitle = '大都市';
				}
				$image = "land{$p}.gif";
				$naviText = "{$lv}{$init->unitPop}";
				break;

			case $init->landProcity:
				// 防災都市
				if($lv < 110) {
					$naviTitle = '防災都市ランクＥ';
				} else if($lv < 130) {
					$naviTitle = '防災都市ランクＤ';
				} else if($lv < 160) {
					$naviTitle = '防災都市ランクＣ';
				} else if($lv < 200) {
					$naviTitle = '防災都市ランクＢ';
				} else {
					$naviTitle = '防災都市ランクＡ';
				}
				$image = "bousai.gif";
				$naviText = "{$lv}{$init->unitPop}";
				break;

			case $init->landNewtown:
				// ニュータウン
				$level = Util::expToLevel($l, $lv);
				$nwork = (int)($lv/15);
				$image = 'new.gif';
				$naviTitle = 'ニュータウン';
				$naviText = "{$lv}{$init->unitPop}/職場{$nwork}0{$init->unitPop}";
				break;

			case $init->landBigtown:
				// 現代都市
				$level = Util::expToLevel($l, $lv);
				$mwork = (int)($lv/20);
				$lwork = (int)($lv/30);
				$image = 'big.gif';
				$naviTitle = '現代都市';
				$naviText = "{$lv}{$init->unitPop}/職場{$mwork}0{$init->unitPop}/農場{$lwork}0{$init->unitPop}";
				break;

			case $init->landFarm:
				// 農場
				$image = 'land7.gif';
				$naviTitle = '農場';
				$naviText = "{$lv}0{$init->unitPop}規模";
				if($lv > 25) {
					// ドーム型農場
					$image = 'land71.gif';
					$naviTitle = 'ドーム型農場';
				}
				break;

			case $init->landSfarm:
				// 海底農場
				$image = 'land72.gif';
				$naviTitle = '海底農場';
				$naviText = "{$lv}0{$init->unitPop}規模";
				break;

			case $init->landFactory:
				// 工場
				$image = 'land8.gif';
				$naviTitle = '工場';
				$naviText = "{$lv}0{$init->unitPop}規模";
				if($lv > 100) {
					// 大工場
					$image = 'land82.gif';
					$naviTitle = '大工場';
				}
				break;

			case $init->landCommerce:
				// 商業ビル
				$image = 'commerce.gif';
				$naviTitle = '商業ビル';
				$naviText = "{$lv}0{$init->unitPop}規模";
				if($lv > 150) {
					// 本社ビル
					$image = 'commerce2.gif';
					$naviTitle = '本社ビル';
				}
				break;

			case $init->landHatuden:
				// 発電所
				$image = 'hatuden.gif';
				$naviTitle = '発電所';
				$naviText = "{$lv}000kw";
				if($lv > 100) {
					// 大型発電所
					$image = 'hatuden2.gif';
					$naviTitle = '大型発電所';
				}
				break;

			case $init->landBank:
				// 銀行
				$image = 'bank.gif';
				$naviTitle = '銀行';
				break;

			case $init->landBase:
				if($mode == 0 || $mode == 2) {
					// 観光者の場合は森のふり
					$image = 'land6.gif';
					$naviTitle = '森';
				} else {
					// ミサイル基地
					$level = Util::expToLevel($l, $lv);
					$image = 'land9.gif';
					$naviTitle = 'ミサイル基地';
					$naviText = "レベル ${level} / 経験値 {$lv}";
				}
				break;
			case $init->landSbase:
				// 海底基地
				if($mode == 0 || $mode == 2) {
					// 観光者の場合は海のふり
					$image = 'land0.gif';
					$naviTitle = '海';
				} else {
					$level = Util::expToLevel($l, $lv);
					$image = 'land12.gif';
					$naviTitle = '海底基地';
					$naviText = "レベル ${level} / 経験値 {$lv}";
				}
				break;

			case $init->landDefence:
				// 防衛施設
				if($mode == 0 || $mode == 2) {
					$image = 'land10.gif';
					$naviTitle = '防衛施設';
				} else {
					$image = 'land10.gif';
					$naviTitle = '防衛施設';
					$naviText = "耐久力 {$lv}";
				}
				break;

			case $init->landHaribote:
				// ハリボテ
				$image = 'land10.gif';
				if($mode == 0 || $mode == 2) {
					// 観光者の場合は防衛施設のふり
					$naviTitle = '防衛施設';
				} else {
					$naviTitle = 'ハリボテ';
				}
				break;

			case $init->landSdefence:
				// 海底防衛施設
				if($mode == 0 || $mode == 2) {
					$image = 'land102.gif';
					$naviTitle = '海底防衛施設';
				} else {
					$image = 'land102.gif';
					$naviTitle = '海底防衛施設';
					$naviText = "耐久力 {$lv}";
				}
				break;

			case $init->landOil:
				// 海底油田
				$image = 'land16.gif';
				$naviTitle = '海底油田';
				break;

			case $init->landMountain:
				// 山
				if($lv > 0) {
					$image = 'land15.gif';
					$naviTitle = '採掘場';
					$naviText = "{$lv}0{$init->unitPop}規模";
				} else {
					$image = 'land11.gif';
					$naviTitle = '山';
				}
				break;

			case $init->landMyhome:
				// 自宅
				$image = "home{$lv}.gif";
				$naviTitle = 'マイホーム';
				$naviText = "{$lv}人家族";
				break;

			case $init->landSoukoM:
				$flagm = 1;
			case $init->landSoukoF:
				// 倉庫
				if($flagm == 1) {
					$naviTitle = '金庫';
				} else {
					$naviTitle = '食料庫';
				}
				$image = "souko.gif";
				$sec = (int)($lv / 100);
				$tyo = $lv % 100;
				if($l == $init->landSoukoM) {
					if($tyo == 0) {
						$naviText = "セキュリティ：{$sec}、貯金：なし";
					} else {
						$naviText = "セキュリティ：{$sec}、貯金：{$tyo}000{$init->unitMoney}";
					}
				} else {
					if($tyo == 0) {
						$naviText = "セキュリティ：{$sec}、貯食：なし";
					} else {
						$naviText = "セキュリティ：{$sec}、貯食：{$tyo}000{$init->unitFood}";
					}
				}
				break;

			case $init->landMonument:
				// 記念碑
				$image = "monument{$lv}.gif";
				$naviTitle = '記念碑';
				$naviText = $init->monumentName[$lv];
				break;

			case $init->landMonster:
			case $init->landSleeper:
				// 怪獣
				$monsSpec = Util::monsterSpec($lv);
				$spec = $monsSpec['kind'];
				$special = $init->monsterSpecial[$spec];
				$image = "monster{$spec}.gif";
				if($l == $init->landSleeper) {
					$naviTitle = '怪獣（睡眠中）';
				} else {
					$naviTitle = '怪獣';
				}

				// 硬化中?
				if((($special & 0x4) && (($this->islandTurn % 2) == 1)) ||
					 (($special & 0x10) && (($this->islandTurn % 2) == 0))) {
					// 硬化中
					$image = $init->monsterImage[$monsSpec['kind']];
				}
				$naviText = "怪獣{$monsSpec['name']}(体力{$monsSpec['hp']})";
		}

		if($mode == 1 || $mode == 2) {
			echo "<a href=\"javascript:void(0);\" onclick=\"ps($x,$y)\">";
			$naviText = "{$comStr}\\n{$naviText}";
		}

		echo "<img src=\"{$init->imgDir}/{$image}\" width=\"32\" height=\"32\" alt=\"{$point} {$naviTitle} {$comStr}\" onMouseOver=\"Navi({$naviPos}, '{$init->imgDir}/{$image}', '{$naviTitle}', '{$point}', '{$naviText}', {$naviExp});\" onMouseOut=\"NaviClose(); return false\">";

		// 座標設定閉じ
		if($mode == 1 || $mode == 2) {
			echo "</a>";
		}
	}
}

//----------------------------------------------------------------------
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
				$value = str_replace(",", "", $value);
				// $value = JcodeConvert($value, 0, 2);
				// $value = HANtoZEN_UTF8($value);
				if($init->stripslashes == true) {
					$this->dataSet["{$name}"] = stripslashes($value);
				} else {
					$this->dataSet["{$name}"] = $value;
				}
			}
			if(!empty($_POST['Sight'])) {
				$this->dataSet['ISLANDID'] = $_POST['Sight'];
			}
		}
	}

	//---------------------------------------------------
	// COOKIEを取得
	//---------------------------------------------------
	function getCookies() {
		if(!empty($_COOKIE)) {
			while(list($name, $value) = each($_COOKIE)) {
				switch($name) {
					case "POINTX":
						$this->dataSet['defaultX'] = $value;
						break;
					case "POINTY":
						$this->dataSet['defaultY'] = $value;
						break;
					case "LAND":
						$this->dataSet['defaultLAND'] = $value;
						break;
					case "MONSTER":
						$this->dataSet['defaultMONSTER'] = $value;
						break;
					case "SHIP":
						$this->dataSet['defaultSHIP'] = $value;
						break;
					case "LEVEL":
						$this->dataSet['defaultLEVEL'] = $value;
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
		$time = $_SERVER['REQUEST_TIME'] + 30; // 現在 + 30秒有効

		// Cookieの設定 & POSTで入力されたデータで、Cookieから取得したデータを更新
		if($this->dataSet['POINTX']) {
			setcookie("POINTX",$this->dataSet['POINTX'], $time);
			$this->dataSet['defaultX'] = $this->dataSet['POINTX'];
		}
		if($this->dataSet['POINTY']) {
			setcookie("POINTY",$this->dataSet['POINTY'], $time);
			$this->dataSet['defaultY'] = $this->dataSet['POINTY'];
		}
		if($this->dataSet['LAND']) {
			setcookie("LAND",$this->dataSet['LAND'], $time);
			$this->dataSet['defaultLAND'] = $this->dataSet['LAND'];
		}
		if($this->dataSet['MONSTER']) {
			setcookie("MONSTER",$this->dataSet['MONSTER'], $time);
			$this->dataSet['defaultMONSTER'] = $this->dataSet['MONSTER'];
		}
		if($this->dataSet['SHIP']) {
			setcookie("SHIP",$this->dataSet['SHIP'], $time);
			$this->dataSet['defaultSHIP'] = $this->dataSet['SHIP'];
		}
		if($this->dataSet['LEVEL']) {
			setcookie("LEVEL",$this->dataSet['LEVEL'], $time);
			$this->dataSet['defaultLEVEL'] = $this->dataSet['LEVEL'];
		}
		// if($this->dataSet['SKIN']) {
		// 	setcookie("SKIN",$this->dataSet['SKIN'], $time);
		// 	$this->dataSet['defaultSkin'] = $this->dataSet['SKIN'];
		// }
		if($this->dataSet['IMG']) {
			setcookie("IMG",$this->dataSet['IMG'], $time);
			$this->dataSet['defaultImg'] = $this->dataSet['IMG'];
		}
	}
}

//----------------------------------------------------------------------
class Edit {
	//---------------------------------------------------
	// TOP 表示（パスワード入力）
	//---------------------------------------------------
	function enter() {
		global $init;

		echo <<<END
<h1 class="title">{$init->title}<br>マップ・エディタ</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="list">
<input type="submit" value="一覧へ">
</form>
END;
	}

	//---------------------------------------------------
	// 島の一覧を表示
	//---------------------------------------------------
	function main($hako, $data) {
		global $init;

		// パスワード
		if(!Util::checkPassword("", $data['PASSWORD'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}

		echo "<h1 class=\"title\">マップ・エディタ</h1>\n";
		echo <<<END
<h2 class='Turn'>ターン$hako->islandTurn</h2>
<hr>
<div ID="IslandView">
<h2>諸島の状況</h2>
<p>島の名前をクリックすると、<strong>マップ</strong>が表示されます。</p>

<table class="table table-bordered table-condensed">
<tr>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}島{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePopulation}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameArea}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFunds}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFood}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
</tr>
END;
		// 表示内容は、管理者用の内容
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$island = $hako->islands[$i];
			$j = ($island['isBF']) ? '★' : $i + 1;
			$id = $island['id'];
			$pop = $island['pop'] . $init->unitPop;
			$area = $island['area'] . $init->unitArea;
			$money = $island['money'] . $init->unitMoney;
			$food = $island['food'] . $init->unitFood;
			$farm = ($island['farm'] <= 0) ? "保有せず" : $island['farm'] * 10 . $init->unitPop;
			$factory = ($island['factory'] <= 0) ? "保有せず" : $island['factory'] * 10 . $init->unitPop;
			$mountain = ($island['mountain'] <= 0) ? "保有せず" : $island['mountain'] * 10 . $init->unitPop;
			$comment = $island['comment'];
			$comment_turn = $island['comment_turn'];
			$monster = '';
			if($island['monster'] > 0) {
				$monster = "<strong class=\"monster\">[怪獣{$island['monster']}体]</strong>";
			}
			$name = "";
			if($island['absent'] == 0) {
				$name = "{$init->tagName_}{$island['name']}島{$init->_tagName}";
			} else {
				$name = "{$init->tagName2_}{$island['name']}島({$island['absent']}){$init->_tagName2}";
			}
			if(!empty($island['owner'])) {
				$owner = $island['owner'];
			} else {
				$owner = "コメント";
			}
			if($init->commentNew > 0 && ($comment_turn + $init->commentNew) > $hako->islandTurn) {
				$comment .= " <span class=\"new\">New</span>";
			}
			if($hako->islandNumber - $i == $hako->islandNumberBF) {
				echo "</table>\n</div>\n";
				echo "<BR>\n";
				echo "<hr>\n\n";
				echo "<div ID=\"IslandView\">\n";
				echo "<h2>Battle Fieldの状況</h2>\n";

				echo <<<END
<table class="table table-bordered">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}島{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePopulation}{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameArea}{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFunds}{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFood}{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
</tr>
END;
			}
			echo "<tr>\n";
			echo "<th {$init->bgNumberCell} rowspan=\"2\">{$init->tagNumber_}$j{$init->_tagNumber}</th>\n";
			echo "<td {$init->bgNameCell} rowspan=\"2\"><a href=\"JavaScript:void(0);\" onClick=\"document.MAP{$id}.submit();return false;\">{$name}</a> {$monster}<br>\n{$prize}</td>\n";
			echo <<<END
<form name="MAP{$id}" action="{$GLOBALS['THIS_FILE']}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="map">
	<input type="hidden" name="Sight" value="{$id}">
</form>
END;
			echo "<td {$init->bgInfoCell}>$pop</td>\n";
			echo "<td {$init->bgInfoCell}>$area</td>\n";
			echo "<td {$init->bgInfoCell}>$money</td>\n";
			echo "<td {$init->bgInfoCell}>$food</td>\n";
			echo "<td {$init->bgInfoCell}>$farm</td>\n";
			echo "<td {$init->bgInfoCell}>$factory</td>\n";
			echo "<td {$init->bgInfoCell}>$mountain</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td {$init->bgCommentCell} colspan=\"7\">{$init->tagTH_}{$owner}：{$init->_tagTH}$comment</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n</div>\n";
	}
	//---------------------------------------------------
	// マップエディタの表示
	//---------------------------------------------------
	function editMap($hako, $data) {
		global $init;

		// パスワード
		if(!Util::checkPassword("", $data['PASSWORD'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		$html = new HtmlMap;
		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		$island = $hako->islands[$number];

		// 地形リストを生成
		$landList = array (
			"$init->landSea",
			"$init->landSeaSide",
			"$init->landWaste",
			"$init->landPoll",
			"$init->landPlains",
			"$init->landForest",
			"$init->landTown",
			"$init->landProcity",
			"$init->landNewtown",
			"$init->landBigtown",
			"$init->landSeaCity",
			"$init->landFroCity",
			"$init->landPort",
			"$init->landShip",
			"$init->landRail",
			"$init->landStat",
			"$init->landTrain",
			"$init->landFusya",
			"$init->landSyoubou",
			"$init->landSsyoubou",
			"$init->landFarm",
			"$init->landSfarm",
			"$init->landNursery",
			"$init->landFactory",
			"$init->landCommerce",
			"$init->landMountain",
			"$init->landHatuden",
			"$init->landBase",
			"$init->landHaribote",
			"$init->landDefence",
			"$init->landSbase",
			"$init->landSdefence",
			"$init->landMyhome",
			"$init->landSoukoM",
			"$init->landSoukoF",
			"$init->landMonument",
			"$init->landSoccer",
			"$init->landPark",
			"$init->landSeaResort",
			"$init->landOil",
			"$init->landBank",
			"$init->landMonster",
			"$init->landSleeper",
			"$init->landZorasu"
		);

		// 地形リスト名称を生成
		$landName = array (
			"海、浅瀬",
			"砂浜",
			"荒地",
			"汚染土壌",
			"平地",
			"森",
			"村、町、都市",
			"防災都市",
			"ニュータウン",
			"現代都市",
			"海底都市",
			"海上都市",
			"港",
			"船舶",
			"線路",
			"駅",
			"電車",
			"風車",
			"消防署",
			"海底消防署",
			"農場",
			"海底農場",
			"養殖場",
			"工場",
			"商業ビル",
			"山、採掘場",
			"発電所",
			"ミサイル基地",
			"ハリボテ",
			"防衛施設",
			"海底基地",
			"海底防衛施設",
			"マイホーム",
			"金庫",
			"食料庫",
			"記念碑",
			"スタジアム",
			"遊園地",
			"海の家",
			"海底油田",
			"銀行",
			"怪獣",
			"怪獣（睡眠中）",
			"ぞらす"
		);
		echo <<<END
<script type="text/javascript">
<!--
function ps(x, y, ld, lv) {
	document.InputPlan.POINTX.options[x].selected = true;
	document.InputPlan.POINTY.options[y].selected = true;
	document.InputPlan.LAND.options[ld].selected = true;

	if(ld == {$init->landMonster} || ld == {$init->landSleeper}) {
		mn = Math.floor(lv / 10);
		lv = lv - mn * 10;
		document.InputPlan.MONSTER.options[mn].selected = true;
		document.InputPlan.LEVEL.options[lv].selected = true;
	} else {
		document.InputPlan.LEVEL.options[lv].selected = true;
	}
	return true;
}
//-->
</script>
<div align="center">
{$init->tagBig_}{$init->tagName_}{$island['name']}島{$init->_tagName}マップ・エディタ{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}
</div>

<form name="TOP" action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="list">
</form>
END;
		// 島の情報を表示
		$html->islandInfo($island, $number, 1);

		// 説明文を表示
		echo <<<END
<div align="center">
<table class="table table-bordered">
<tr valign="top">
<td {$init->bgCommandCell}>
<b>レベルについて</b>
<ul>
<li><b>海、浅瀬</b><br>レベル 0 のとき海<br>1 のとき浅瀬<br>それ以外 のとき財宝
<li><b>荒地</b><br>レベル 1 のとき着弾点
<li><b>村、町、都市</b><br>レベル 30 未満が村<br>レベル 100 未満が町<br>レベル 200 未満が都市
<li><b>ミサイル基地</b><br>経験値
<li><b>山、採掘場</b><br>レベル 1 以上のとき採掘場
<li><b>怪獣</b><br>各怪獣の最大レベルを超える<br>設定はできません
<li><b>海底基地</b><br>経験値
</ul>

</td>
<td {$init->bgMapCell}>
END;
		// 地形出力
		$html->islandMap($hako, $island, 1);

		// エディタ領域の表示
		echo <<<END
</td>
<td {$init->bgInputCell}>
<div align="center">
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="regist">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<strong>マップ・エディタ</strong><br>
<hr>
<strong>座標(</strong>
<select name="POINTX">
END;
		for($i = 0; $i < $init->islandSize; $i++) {
			if($i == $data['defaultX']) {
				echo "<option value=\"{$i}\" selected>{$i}</option>\n";
			} else {
				echo "<option value=\"{$i}\">{$i}</option>\n";
			}
		}
		echo "</select>, <select name=\"POINTY\">";
		for($i = 0; $i < $init->islandSize; $i++) {
			if($i == $data['defaultY']) {
				echo "<option value=\"{$i}\" selected>{$i}</option>\n";
			} else {
				echo "<option value=\"{$i}\">{$i}</option>\n";
			}
		}
		echo <<<END
</select><strong>)</strong>
<hr>
<strong>地形</strong>
<select name="LAND">
END;
		for($i = 0; $i < count($landList); $i++) {
			if($landList[$i] == $data['defaultLAND']) {
				echo "<option value=\"{$landList[$i]}\" selected>{$landName[$i]}</option>\n";
			} else {
				echo "<option value=\"{$landList[$i]}\">{$landName[$i]}</option>\n";
			}
		}
		echo <<<END
</select>
<hr>
<strong>怪獣の種類</strong>
<select name="MONSTER">
END;
		for($i = 0; $i < $init->monsterNumber; $i++) {
			if($i == $data['defaultMONSTER']) {
				echo "<option value=\"{$i}\" selected>{$init->monsterName[$i]}</option>\n";
			} else {
				echo "<option value=\"{$i}\">{$init->monsterName[$i]}</option>\n";
			}
		}
		echo <<<END
</select>
<hr>
<strong>船舶の種類</strong>
<select name="SHIP">
END;
		for($i = 0; $i < 15; $i++) {
			if($init->shipName[$i] != "") {
				if($i == $data['defaultSHIP']) {
					echo "<option value=\"{$i}\" selected>{$init->shipName[$i]}</option>\n";
				} else {
						echo "<option value=\"{$i}\">{$init->shipName[$i]}</option>\n";
				}
			}
		}
		echo <<<END
</select>
<hr>
<strong>レベル</strong>
<input type="number" min="0" max="1048575" size="8" maxlength="7" name="LEVEL" value="{$data['defaultLEVEL']}">
<hr>
<input type="submit" value="登録">
</form>
</div>

<ul>
<li>登録するときは十分注意願います。
<li>データを破壊する場合があります。
<li>バックアップを行ってから<br>行う様にしましょう。
<li>地形データを変更するのみで、<br>他のデータは変更されません。<br>
ターン更新で他のデータへ<br>反映されます。
</ul>

</td>
</tr>
</table>
</div>
END;
	}
	//---------------------------------------------------
	// 地形の登録
	//---------------------------------------------------
	function register($hako, $data) {
		global $init;

		// パスワード
		if(!Util::checkPassword("", $data['PASSWORD'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}

		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		$island = $hako->islands[$number];
		$land = &$island['land'];
		$landValue = &$island['landValue'];
		$x = $data['POINTX'];
		$y = $data['POINTY'];
		$ld = $data['LAND'];
		$mons = $data['MONSTER'];
		$ships = $data['SHIP'];
		$level = $data['LEVEL'];

		if($ld == $init->landMonster || $ld == $init->landSleeper) {
			// 怪獣のレベル設定
			$BHP = $init->monsterBHP[$mons];
			if($init->monsterDHP[$mons] > 0) {
				$DHP = Util::random($init->monsterDHP[$mons] - 1);
			} else {
				$DHP = Util::random($init->monsterDHP[$mons]);
			}
			$level = $BHP + $DHP;
			$level = $mons * 100 + $level;
		} elseif($ld == $init->landShip) {
			// 船舶のレベル設定
			$level = Util::navyPack($id, $ships, $init->shipHP[$ships], 0, 0);
		}

		// 更新データ設定
		$land[$x][$y] = $ld;
		$landValue[$x][$y] = $level;

		// マップデータ更新
		$hako->writeLand($id, $island);

		// 設定した値を戻す
		$hako->islands[$number] = $island;

		echo "{$init->tagBig_}地形を変更しました{$init->_tagBig}<hr>\n";

		// マップエディタの表示へ
		$this->editMap($hako, $data);
	}
}

//----------------------------------------------------------------------
class Main {

	function execute() {
		$hako = new Hako;
		$cgi = new Cgi;
		$cgi->parseInputData();
		$cgi->getCookies();
		if(!$hako->readIslands($cgi)) {
			HTML::header($cgi->dataSet);
			Error::noDataFile();
			HTML::footer();
			exit();
		}
		$cgi->setCookies();
		$edit = new Edit;

		switch($cgi->mode) {
			case "enter":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->main($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "list":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->main($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "map":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->editMap($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "regist":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->register($hako, $cgi->dataSet);
				$html->footer();
				break;

			// case "skin":
			// 	$html = new HtmlSetted;
			// 	$html->header($cgi->dataSet);
			// 	$html->setSkin();
			// 	$html->footer();
			// 	break;

			// case "imgset":
			// 	$html = new HtmlSetted;
			// 	$html->header($cgi->dataSet);
			// 	$html->setImg();
			// 	$html->footer();
			// 	break;

			default:
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->enter();
				$html->footer();
		}
		exit();
	}
}

$start = new Main;
$start->execute();
