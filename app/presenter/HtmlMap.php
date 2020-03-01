<?php
require_once MODEL_PATH. '/Log/Core.php';
require_once HELPER_PATH.'/message/error.php';
require_once PRESENTER_PATH.'/HTML.php';

class HtmlMap extends HTML {
	//---------------------------------------------------
	// 開発画面
	//---------------------------------------------------
	function owner($hako, $data) {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		$island = $hako->islands[$number];

		// パスワードチェック
		if(!Util::checkPassword($island['password'], $data['PASSWORD'])){
			ErrorHandler::wrongPassword();
			return;
		}

		// 開発画面
		$this->tempOwer($hako, $data, $number);

		// IP情報取得
		$logfile = "{$init->dirName}/{$init->logname}";
		$ax = $init->axesmax - 1;
		$log = file($logfile);
		$fp = fopen($logfile,"w");
		$timedata = date("Y年m月d日(D) H時i分s秒");
		$islandID = "{$data['ISLANDID']}";
		$name = "{$island['name']}{$init->nameSuffix}";
		$ip = getenv("REMOTE_ADDR");
		$host = gethostbyaddr(getenv("REMOTE_ADDR"));
		fputs($fp,$timedata.",".$islandID.",".$name.",".$ip.",".$host."\n");
		for($i=0; $i<$ax; $i++) {
			if ( isset($log[$i]) ) {
				fputs($fp,$log[$i]);
			}
		}
		fclose($fp);

		$this->islandRecent($island, 1);
	}

	/**
	 * 観光画面
	 * @param  [type] $hako [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	function visitor($hako, $data) {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

		// idから島番号を取得
		$id = $data['ISLANDID'];
		$number = isset($hako->idToNumber[$id]) ? $hako->idToNumber[$id] : -1;

		// なぜかその島がない場合
		if($number < 0 || $number > $hako->islandNumber) {
			ErrorHandler::problem();
			return;
		}
		// 島の名前を取得
		$island = $hako->islands[$number];
		$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);

		// 読み込み
		require_once(VIEWS_PATH.'/map/main.php');
	}

	//---------------------------------------------------
	// 島の情報
	//---------------------------------------------------
	function islandInfo($island, $number = 0, $mode = 0) {
		global $init;
		$island['pop'] = ($island['pop'] <= 0) ? 1 : $island['pop'];

		$rank       = ($island['isBF']) ? '★' : $number + 1;
		$pop        = $island['pop'] . $init->unitPop;
		$area       = $island['area'] . $init->unitArea;
		$eisei      = isset($island['eisei']) ? $island['eisei'] : "";
		$zin        = isset($island['zin'])   ? $island['zin']   : "";
		$item       = isset($island['item'])  ? $island['item']  : "";
		$money      = ($mode == 0) ? Util::aboutMoney($island['money']) : "{$island['money']}{$init->unitMoney}";
		$lot        = isset($island['lot'])  ? $island['lot']  : "";
		$food       = $island['food'] . $init->unitFood;
		$unemployed = ($island['pop'] - ($island['farm'] + $island['factory'] + $island['commerce'] + $island['mountain'] + $island['hatuden']) * 10) / $island['pop'] * 100;
		$unemployed = '<font color="' . ($unemployed < 0 ? 'black' : '#C7243A') . '">' . sprintf("%-3d%%", $unemployed) . '</font>';
		$farm       = ($island['farm'] <= 0) ? $init->notHave : $island['farm'] * 10 . $init->unitPop;
		$factory    = ($island['factory'] <= 0) ? $init->notHave : $island['factory'] * 10 . $init->unitPop;
		$commerce   = ($island['commerce'] <= 0) ? $init->notHave : $island['commerce'] * 10 . $init->unitPop;
		$mountain   = ($island['mountain'] <= 0) ? $init->notHave : $island['mountain'] * 10 . $init->unitPop;
		$hatuden    = ($island['hatuden'] <= 0) ? $init->notHave : $island['hatuden'] * 10 . $init->unitPop;
		$taiji      = ($island['taiji'] <= 0) ? "0匹" : $island['taiji'] * 1 . $init->unitMonster;
		$tenki      = $island['tenki'];
		$team       = $island['team'];
		$shiai      = $island['shiai'];
		$kachi      = $island['kachi'];
		$make       = $island['make'];
		$hikiwake   = $island['hikiwake'];
		$kougeki    = $island['kougeki'];
		$bougyo     = $island['bougyo'];
		$tokuten    = $island['tokuten'];
		$shitten    = $island['shitten'];
		$comment    = $island['comment'];

		if($island['keep'] == 1) {
			$comment = "<span class=\"attention\">この島は管理人預かり中です。</span>";
		}

		$sora = "";
		switch ($tenki) {
			case 1:
				$sora = "☀";//"<IMG SRC=\"{$init->imgDir}/tenki1.gif\" ALT=\"晴れ\" title=\"晴れ\">";
				break;
			case 2:
				$sora = "☁";//"<IMG SRC=\"{$init->imgDir}/tenki2.gif\" ALT=\"曇り\" title=\"曇り\">";
				break;
			case 3:
				$sora = "☂";//"<IMG SRC=\"{$init->imgDir}/tenki3.gif\" ALT=\"雨\" title=\"雨\">";
				break;
			case 4:
				$sora = "⛈";//"<IMG SRC=\"{$init->imgDir}/tenki4.gif\" ALT=\"雷\" title=\"雷\">";
				break;
			default :
				$sora = "☃";//"<IMG SRC=\"{$init->imgDir}/tenki5.gif\" ALT=\"雪\" title=\"雪\">";
		}

		$eiseis = "";
		for($e = 0; $e < count($init->EiseiName); $e++) {
			$eiseip = "";
			if ( isset($eisei[$e]) ) {
				if($eisei[$e] > 0) {
					$eiseip .= $eisei[$e];
					$eiseis .= "<img src=\"{$init->imgDir}/eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eiseip}%\" title=\"{$init->EiseiName[$e]} {$eiseip}%\"> ({$eiseip}%)";
				} else {
					$eiseis .= "";
				}
			}
		}

		$zins = "";
		for($z = 0; $z < count($init->ZinName); $z++) {
			if ( isset($zin[$z]) ) {
				if($zin[$z] > 0) {
					$zins .= "<img src=\"{$init->imgDir}/zin{$z}.gif\" alt=\"{$init->ZinName[$z]}\" title=\"{$init->ZinName[$z]}\"> ";
				} else {
					$zins .= "";
				}
			}
		}

		$items = "";
		for($t = 0; $t < count($init->ItemName); $t++) {
			if ( isset($item[$t]) ) {
				if($item[$t] > 0) {
					if($t == 20) {
						$items .= "<img src=\"{$init->imgDir}/item{$t}.gif\" alt=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\" title=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\"> ";
					} else {
						$items .= "<img src=\"{$init->imgDir}/item{$t}.gif\" alt=\"{$init->ItemName[$t]}\" title=\"{$init->ItemName[$t]}\"> ";
					}
				} else {
					$items .= "";
				}
			}
		}
		$lots = "";
		if($lot > 0) {
			$lots .= " <IMG SRC=\"{$init->imgDir}/lot.gif\" ALT=\"{$lot}枚\" title=\"{$lot}枚\">";
		}

		if($mode == 1) {
			$arm = "Lv.{$island['rena']}";
		} else {
			$arm = "機密事項";
		}

		// 電力消費量
		$enesyouhi = round($island['pop'] / 100 + $island['factory'] * 2/3 + $island['commerce'] * 1/3 + $island['mountain'] * 1/4);
		if($enesyouhi == 0) {
			$ene = "電力消費なし";
		} elseif($island['hatuden'] == 0) {
			$ene =  "<font color=\"#C7243A\">0%</font>";
		} else {
			// 電力供給率
			$ene = round($island['hatuden'] / $enesyouhi * 100);
			if($ene < 100) {
				// 供給電力不足
				$ene = "<font color=\"#C7243A\">{$ene}%</font>";
			} else {
				// 供給電力充分
				$ene = "{$ene}%";
			}
		}

		// 島の情報
		require_once(VIEWS_PATH.'/map/island-info.php');
	}

	//---------------------------------------------------
	// 地形出力
	// $mode = 1 -- ミサイル基地なども表示
	//---------------------------------------------------
	function islandMap($hako, $island, $mode = 0) {
		global $init;

		$land = $island['land'];
		$landValue = $island['landValue'];
		$command = $island['command'];
		$comStr = array();

		// 増減情報
		$peop  = "";
		$okane = "";
		$gohan = "";
		$poin  = "";

		if (isset($island['peop'])){
			$peop = ($island['peop'] < 0) ? "{$island['peop']}{$init->unitPop}" : "+{$island['peop']}{$init->unitPop}";
		}
		if (isset($island['gold'])){
			$okane = ($island['gold'] < 0) ? "{$island['gold']}{$init->unitMoney}" : "+{$island['gold']}{$init->unitMoney}";
		}
		if (isset($island['rice'])){
			$gohan = ($island['rice'] < 0) ? "{$island['rice']}{$init->unitFood}" : "+{$island['rice']}{$init->unitFood}";
		}
		if (isset($island['pots'])){
			$poin = ($island['pots'] < 0) ? "{$island['pots']}pts" : "+{$island['pots']}pts";
		}

		if($mode == 1) {
			for($i = 0; $i < $init->commandMax; $i++) {
				$j = $i + 1;
				$com = $command[$i];
				if($com['kind'] < 51) {
					if ( isset($comStr[$com['x']][$com['y']]) ) {
					$comStr[$com['x']][$com['y']] .= "[{$j}]{$init->comName[$com['kind']]} ";
					}
				}
			}
		}

		require_once(VIEWS_PATH.'/map/development/map.php');

		echo "<p class='text-center'>開始ターン：{$island['starturn']}</p>\n";

		if (isset($island['soccer'])){
			if($island['soccer'] > 0) {
				echo <<<END
<table class="table table-bordered">
	<thead>
		<tr>
			<th {$init->bgTitleCell}>{$init->tagTH_}総合得点{$init->_tagTH}</th>
			<th {$init->bgTitleCell}>{$init->tagTH_}成績{$init->_tagTH}</th>
			<th {$init->bgTitleCell}>{$init->tagTH_}攻撃力{$init->_tagTH}</th>
			<th {$init->bgTitleCell}>{$init->tagTH_}守備力{$init->_tagTH}</th>
			<th {$init->bgTitleCell}>{$init->tagTH_}得点{$init->_tagTH}</th>
			<th {$init->bgTitleCell}>{$init->tagTH_}失点{$init->_tagTH}</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td {$init->bgInfoCell}>{$island['team']}</td>
			<td {$init->bgInfoCell}>{$island['shiai']}戦{$island['kachi']}勝{$island['make']}敗{$island['hikiwake']}分</td>
			<td {$init->bgInfoCell}>{$island['kougeki']}</td>
			<td {$init->bgInfoCell}>{$island['bougyo']}</td>
			<td {$init->bgInfoCell}>{$island['tokuten']}</td>
			<td {$init->bgInfoCell}>{$island['shitten']}</td>
		</tr>
	</tbody>
</table>
END;
			}
		}

	}


	/**
	 * 島の近況
	 * @param  [type]  $island [description]
	 * @param  integer $mode   [description]
	 * @return [type]          [description]
	 */
	function islandRecent($island, $mode = 0) {
		global $init;

		echo "<hr>\n";

		echo "<div id=\"RecentlyLog\">\n";
		echo "<h2>{$island['name']}{$init->nameSuffix}の近況</h2>\n";
		$log = new Log();
		for($i = 0; $i < $init->logMax; $i++) {
			$log->logFilePrint($i, $island['id'], $mode);
		}
		echo "</div>\n";
	}

	//---------------------------------------------------
	// 開発画面
	//---------------------------------------------------
	function tempOwer($hako, $data, $number = 0) {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

		$island = $hako->islands[$number];
		$name   = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
		$width  = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;
		$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;

		require_once(VIEWS_PATH.'/map/development/basic.php');
	}

	//---------------------------------------------------
	// 入力済みコマンド表示
	//---------------------------------------------------
	function tempCommand($number, $command, $hako) {
		global $init;

		$kind = $command['kind'];
		$target = $command['target'];
		$x = $command['x'];
		$y = $command['y'];
		$arg = $command['arg'];
		$comName = "{$init->tagComName_}{$init->comName[$kind]}{$init->_tagComName}";
		$point = "{$init->tagName_}({$x},{$y}){$init->_tagName}";

		if ( isset($hako->idToName[$target]) ) {
			$target = $hako->idToName[$target];
		}

		if(empty($target)) {
			$target = "無人";
		}
		$target = "{$init->tagName_}{$target}{$init->nameSuffix}{$init->_tagName}";
		$value = $arg * $init->comCost[$kind];
		if($value == 0) {
			$value = $init->comCost[$kind];
		}
		if($value < 0) {
			$value = -$value;
			if($kind == $init->comSellTree) {
				$value = "{$value}{$init->unitTree}";
			} else {
				$value = "{$value}{$init->unitFood}";
			}
		} elseif($kind == $init->comHikidasi) {
			$value = "{$value}0{$init->unitMoney} or {$value}0{$init->unitFood}";
		} else {
			$value = "{$value}{$init->unitMoney}";
		}
		$value = "{$init->tagName_}{$value}{$init->_tagName}";
		$j = sprintf("%02d：", $number + 1);
		echo "<a href=\"javascript:void(0);\" onclick=\"ns({$number})\">{$init->tagNumber_}{$j}{$init->_tagNumber}";

		switch($kind) {
			case $init->comMissileSM:
			case $init->comDoNothing:
			case $init->comGiveup:
				$str = "{$comName}";
				break;

			case $init->comMissileNM:
			case $init->comMissilePP:
			case $init->comMissileST:
			case $init->comMissileBT:
			case $init->comMissileSP:
			case $init->comMissileLD:
			case $init->comMissileLU:
				// ミサイル系
				$n = ($arg == 0) ? '無制限' : "{$arg}発";
				$str = "{$target}{$point}へ{$comName}({$init->tagName_}{$n}{$init->_tagName})";
				break;

			case $init->comEisei:
				// 人工衛星発射
				if($arg >= count($init->EiseiName)) {
					$arg = 0;
				}
				$str = "{$init->tagComName_}{$init->EiseiName[$arg]}打ち上げ{$init->_tagComName}";
				break;

			case $init->comEiseimente:
				// 人工衛星修復
				if($arg >= count($init->EiseiName)) {
					$arg = 0;
				}
				$str = "{$init->tagComName_}{$init->EiseiName[$arg]}修復{$init->_tagComName}";
				break;

			case $init->comEiseiAtt:
				// 人工衛星破壊砲
				if($arg >= count($init->EiseiName)) {
					$arg = 0;
				}
				$str = "{$target}へ{$init->tagComName_}{$init->EiseiName[$arg]}破壊砲発射{$init->_tagComName}";
				break;

			case $init->comEiseiLzr:
				// 衛星レーザー
				$str = "{$target}{$point}へ{$comName}";
				break;

			case $init->comSendMonster:
			case $init->comSendSleeper:
				// 怪獣派遣
				$str = "{$target}へ{$comName}";
				break;

			case $init->comSell:
			case $init->comSellTree:
				// 食料・木材輸出
				$str ="{$comName}{$value}";
				break;

			case $init->comMoney:
			case $init->comFood:
				// 援助
				$str = "{$target}へ{$comName}{$value}";
				break;

			case $init->comDestroy:
				// 掘削
				if($arg != 0) {
					$str = "{$point}で{$comName}(予算{$value})";
				} else {
					$str = "{$point}で{$comName}";
				}
				break;

			case $init->comLot:
				// 宝くじ購入
				if ($arg == 0) {
					$arg = 1;
				} elseif ($arg > 30) {
					$arg = 30;
				}
				$str = "{$comName}(予算{$value})";
				break;

			case $init->comDbase:
				// 防衛施設
				if ($arg == 0) {
					$arg = 1;
				} elseif ($arg > $init->dBaseHP) {
					$arg = $init->dBaseHP;
				}
				$str = "{$point}で{$comName}(耐久力{$arg})";
				break;

			case $init->comSdbase:
				// 海底防衛施設
				if ($arg == 0) {
					$arg = 1;
				} elseif ($arg > $init->sdBaseHP) {
					$arg = $init->sdBaseHP;
				}
				$str = "{$point}で{$comName}(耐久力{$arg})";
				break;

			case $init->comSoukoM:
				$flagm = 1;
			case $init->comSoukoF:
				// 倉庫建設
				if($arg == 0) {
					$str = "{$point}で{$comName}(セキュリティ強化)";
				} else {
					if($flagm == 1) {
						$str = "{$point}で{$comName}({$value})";
					} else {
						$str = "{$point}で{$comName}({$value})";
					}
				}
				break;

			case $init->comHikidasi:
				// 倉庫引き出し
				if ($arg == 0) {
					$arg = 1;
				}
				$str = "{$comName}({$value})";
				break;

			case $init->comMakeShip:
				// 造船
				if ($arg >= $init->shipKind) {
					$arg = $init->shipKind - 1;
				}
				$str = "{$point}で{$comName}({$init->shipName[$arg]})";
				break;

			case $init->comShipBack:
				// 船の破棄
				$str = "{$point}で{$comName}";
				break;

			case $init->comFarm:
			case $init->comSfarm:
			case $init->comNursery:
			case $init->comFactory:
			case $init->comCommerce:
			case $init->comMountain:
			case $init->comHatuden:
			case $init->comBoku:
				// 回数付き
				if($arg == 0) {
					$str = "{$point}で{$comName}";
				} else {
					$str = "{$point}で{$comName}({$arg}回)";
				}
				break;

			case $init->comPropaganda:
			case $init->comOffense:
			case $init->comDefense:
			case $init->comPractice:
				// 強化
				$str = "{$comName}({$arg}回)";
				break;

			case $init->comPlaygame:
				// 試合
				$str = "{$target}と{$comName}";
				break;

			case $init->comSendShip:
				// 船派遣
				$str = "{$target}へ{$point}の{$comName}";
				break;

			case $init->comReturnShip:
				// 船帰還
				$str = "{$target}{$point}の{$comName}";
				break;

			default:
				// 座標付き
				$str = "{$point}で{$comName}";
		}
		echo "{$str}</a><br>";
	}
	//---------------------------------------------------
	// 新しく発見した島
	//---------------------------------------------------
	function newIslandHead($name) {
		global $init;

		echo <<<END
	<h1 class="text-center">{$init->tagBig_}{$init->nameSuffix}を発見しました！！{$init->_tagBig}
		<small>{$init->tagBig_}{$init->tagName_}「{$name}{$init->nameSuffix}」{$init->_tagName}と命名します。{$init->_tagBig}</small>
	</h1>
END;
	}

	//---------------------------------------------------
	// 目標捕捉モード
	//---------------------------------------------------
	function printTarget($hako, $data) {
		global $init;

		// idから島番号を取得
		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		// なぜかその島がない場合
		if($number < 0 || $number > $hako->islandNumber) {
			ErrorHandler::problem();
			return;
		}
		$island = $hako->islands[$number];
		echo <<<END
<script>
function ps(x, y) {
	window.opener.document.InputPlan.POINTX.options[x].selected = true;
	window.opener.document.InputPlan.POINTY.options[y].selected = true;
	return true;
}
</script>

<div class="text-center">
{$init->tagBig_}{$init->tagName_}{$island['name']}{$init->nameSuffix}{$init->_tagName}{$init->_tagBig}<br>
</div>
END;
		//島の地図
		$this->islandMap($hako, $island, 2);
	}
}
