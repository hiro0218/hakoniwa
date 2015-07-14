<?php

/*******************************************************************

	箱庭諸島 S.E

	- メインファイル -

	hako-main.php by SERA - 2013/05/19

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-file.php';
require_once ABSOLUTE_PATH.'hako-html.php';
require_once ABSOLUTE_PATH.'hako-turn.php';
require_once ABSOLUTE_PATH.'hako-util.php';

$init = new Init();

define("READ_LINE", 1024);
$THIS_FILE = $init->baseDir . "/hako-main.php";
$ISLAND_TURN; // ターン数

//--------------------------------------------------------------------
class Hako extends File {
	var $islandList;    // 島リスト
	var $targetList;    // ターゲットの島リスト
	var $defaultTarget; // 目標補足用ターゲット

	function readIslands(&$cgi) {
		global $init;

		$m = $this->readIslandsFile($cgi);
		$this->islandList = $this->getIslandList( (isset( $cgi->dataSet['defaultID'] )) ? $cgi->dataSet['defaultID'] : "");
		if($init->targetIsland == 1) {
			// 目標の島 所有の島が選択されたリスト
			$this->targetList = $this->islandList;
		} else {
			// 順位がTOPの島が選択された状態のリスト
			$this->targetList = $this->getIslandList($cgi->dataSet['defaultTarget']);
		}
		return $m;
	}
	//---------------------------------------------------
	// 島リスト生成
	//---------------------------------------------------
	function getIslandList($select = 0) {
		global $init;

		$list = "";
		for($i = 0; $i < $this->islandNumber; $i++) {
			if($init->allyUse) {
				$name = Util::islandName($this->islands[$i], $this->ally, $this->idToAllyNumber); // 同盟マークを追加
			} else {
				$name = $this->islands[$i]['name'];
			}
			$id = $this->islands[$i]['id'];

			// 攻撃目標をあらかじめ自分の島にする
			if(empty($this->defaultTarget)) {
				$this->defaultTarget = $id;
			}

			if($id == $select) {
				$s = "selected";
			} else {
				$s = "";
			}
			if($init->allyUse) {
				$list .= "<option value=\"$id\" $s>{$name}</option>\n"; // 同盟マークを追加
			} else {
				$list .= "<option value=\"$id\" $s>{$name}島</option>\n";
			}
		}
		return $list;
	}
	//---------------------------------------------------
	// 賞に関するリストを生成
	//---------------------------------------------------
	function getPrizeList($prize) {
		global $init;
		list($flags, $monsters, $turns) = explode(",", $prize, 3);

		$turns = explode(",", $turns);
		$prizeList = "";
		// ターン杯
		$max = -1;
		$nameList = "";
		if($turns[0] != "") {
			for($k = 0; $k < count($turns) - 1; $k++) {
				$nameList .= "[{$turns[$k]}] ";
				$max = $k;
			}
		}
		if($max != -1) {
			$prizeList .= "<img src=\"{$init->imgDir}/prize0.gif\" alt=\"$nameList\" title=\"$nameList\" width=\"16\" height=\"16\"> ";
		}
		// 賞
		$f = 1;
		for($k = 1; $k < count($init->prizeName); $k++) {
			if($flags & $f) {
				$prizeList .= "<img src=\"{$init->imgDir}/prize{$k}.gif\" alt=\"{$init->prizeName[$k]}\" title=\"{$init->prizeName[$k]}\" width=\"16\" height=\"16\"> ";
			}
			$f = $f << 1;
		}
		// 倒した怪獣リスト
		$f = 1;
		$max = -1;
		$nameList = "";
		for($k = 0; $k < $init->monsterNumber; $k++) {
			if($monsters & $f) {
				$nameList .= "[{$init->monsterName[$k]}] ";
				$max = $k;
			}
			$f = $f << 1;
		}
		if($max != -1) {
			$prizeList .= "<img src=\"{$init->imgDir}/monster{$max}.gif\" alt=\"{$nameList}\" title=\"{$nameList}\" width=\"16\" height=\"16\"> ";
		}
		return $prizeList;
	}
	//---------------------------------------------------
	// 地形に関するデータ生成
	//---------------------------------------------------
	function landString($l, $lv, $x, $y, $mode, $comStr) {
		global $init;

		$point = "({$x},{$y})";
		$naviExp = "''";
		$image = '';
		$naviTitle = '';
		$naviText = "";

		if ( empty($comStr) ) {
			$comStr = "";
		}

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
					// 財宝
					$image = 'land17.gif';
					$naviTitle = '海';
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
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
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
				$naviText = "{$lv}{$init->unitPop}/職場{$lwork}0{$init->unitPop}/農場{$mwork}0{$init->unitPop}";
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
				if((($special & 0x4)  && (($this->islandTurn % 2) == 1)) ||
					 (($special & 0x10) && (($this->islandTurn % 2) == 0))) {
					// 硬化中
					$image = $init->monsterImage[$monsSpec['kind']];
				}
				$naviText = "怪獣{$monsSpec['name']}(体力{$monsSpec['hp']})";
		}

		// 座標設定
		if($mode == 1 || $mode == 2) {
			echo "<a href=\"javascript:void(0);\" onclick=\"ps($x,$y)\">";
		}

		echo "<img src=\"{$init->imgDir}/{$image}\" width=\"32\" height=\"32\" alt=\"{$point} {$naviTitle}\" onMouseOver=\"Navi({$naviPos},'{$init->imgDir}/{$image}', '{$naviTitle}', '{$point}', '{$naviText}', {$naviExp});\" onMouseOut=\"NaviClose(); return false\">";

		// 座標設定 閉じ
		if($mode == 1 || $mode == 2) {
			echo "</a>";
		}

	}
}

//--------------------------------------------------------------------
class Main {
	function execute() {
		$hako = new Hako();
		$cgi  = new Cgi();

		$cgi->parseInputData();
		$cgi->getCookies();
		$fp = "";

		if(!$hako->readIslands($cgi)) {
			HTML::header($cgi->dataSet);
			Error::noDataFile();
			HTML::footer();
			Util::unlock($lock);
			exit();
		}
		$lock = Util::lock($fp);
		if(FALSE == $lock) {
			exit;
		}
		$cgi->setCookies();

		$_developmode = (isset( $cgi->dataSet['DEVELOPEMODE'] )) ? $cgi->dataSet['DEVELOPEMODE'] : "";
		if( mb_strtolower($_developmode) == "javascript") {
			$html = new HtmlJS;
			$com  = new MakeJS;
		} else {
			$html = new HtmlMap;
			$com  = new Make;
		}
		switch($cgi->mode) {
			case "turn":
				$turn = new Turn();
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$turn->turnMain($hako, $cgi->dataSet);
				$html->main($hako, $cgi->dataSet); // ターン処理後、TOPページopen
				$html->footer();
				break;

			case "owner":
				$html->header($cgi->dataSet);
				$html->owner($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "command":
				$html->header($cgi->dataSet);
				$com->commandMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "new":
				$html->header($cgi->dataSet);
				$com->newIsland($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "comment":
				$html->header($cgi->dataSet);
				$com->commentMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "print":
				$html->header($cgi->dataSet);
				$html->visitor($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "targetView":
				$html->header($cgi->dataSet);
				$html->printTarget($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "change":
				$html->header($cgi->dataSet);
				$com->changeMain($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "ChangeOwnerName":
				$html->header($cgi->dataSet);
				$com->changeOwnerName($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "conf":
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$html->register($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "log":
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$html->log();
				$html->footer();
				break;

			default:
				$html = new HtmlTop();
				$html->header($cgi->dataSet);
				$html->main($hako, $cgi->dataSet);
				$html->footer();
		}
		Util::unlock($lock);
		exit();
	}
}

$start = new Main();
$start->execute();
