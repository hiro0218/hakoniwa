<?php

/*******************************************************************

	箱庭諸島 S.E

	- 画面出力用ファイル -

	hako-html.php by SERA - 2013/05/12

*******************************************************************/

require_once ABSOLUTE_PATH.'hako-error.php';

//--------------------------------------------------------------------
class HTML {

	//---------------------------------------------------
	// HTML ヘッダ出力
	//---------------------------------------------------
	static function header($data = "") {
		global $init;

		// $css  = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
		$css  = $init->cssList[0];
		$bimg = (empty($data['defaultImg']))  ? $init->imgDir : $data['defaultImg'];

		echo <<<END
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="{$init->cssDir}/{$css}">
	<!-- <link rel="shortcut icon" href="{$init->baseDir}/favicon.ico"> -->
	<title>{$init->title}</title>
	<script type="text/javascript" src="{$init->jsDir}/{$init->js}"></script>
</head>
<body>

<div class="container-fluid">
	<header>
		<ul class="list-inline">
			<li><a href="http://www.bekkoame.ne.jp/~tokuoka/hakoniwa.html">箱庭諸島スクリプト配布元</a> <a href="http://scrlab.g-7.ne.jp">[PHP]</a></li>
			<li><a href="http://hakoniwa.symphonic-net.com">箱庭諸島S.E配布元</a></li>
			<li><a href="http://snufkin.jp.land.to">沙布巾の箱庭</a></li>
			<li><a href="http://www.s90259900.onlinehome.us/">箱庭の箱庭</a></li>
			<li><a href="http://no-one.s53.xrea.com">The Return of Neptune</a></li>
			<li><a href="http://minnano.min-ai.net/ocn/">みんなのあいらんど</a></li>
		</ul>

		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<a href="{$init->baseDir}/hako-main.php" class="navbar-brand">{$init->title}</a>
				</div>
				<ul class="nav navbar-nav">
					<li><a href="{$init->baseDir}/hako-main.php?mode=conf">島の登録・設定変更</a></li>
					<li><a href="{$init->baseDir}/hako-ally.php">同盟管理</a></li>
					<li><a href="{$init->baseDir}/hako-main.php?mode=log">最近の出来事</a></li>
				</ul>
			</div>
		</nav>
	</header>

END;

	}

	//---------------------------------------------------
	// HTML フッタ出力
	//---------------------------------------------------
	static function footer() {
		global $init;

		echo <<<END
<hr>
<script>
	// JavaScriptモード関連
    if (document.addEventListener) {
        if (typeof(init) == "function") {
            document.addEventListener("DOMContentLoaded", init(), false);
        }
    } else {
        if (typeof(init) == "function") {
            window.onload = init;
        }
    }
</script>

<div class="row">
	<div class="col-xs-12">
		<footer>
			<p>Produced by <a href="https://twitter.com/{$init->twitterID}">{$init->adminName}</a> (<a href="{$init->urlTopPage}">{$init->urlTopPage}</a>)
END;
		if($init->performance) {
			echo '<small class="pull-right">';
			list($tmp1, $tmp2) = array_pad( explode(" ", $init->CPU_start), 2, 0);
			list($tmp3, $tmp4) = array_pad( explode(" ", microtime()), 2, 0);
			printf("(CPU : %.6f秒)", $tmp4-$tmp2+$tmp3-$tmp1);
			echo '</small>';
		}
		echo <<<END
		</p>
</footer>
	</div>
</div>

</div><!-- container -->
</body>
</html>
END;
	}

	//---------------------------------------------------
	// 最終更新時刻 ＋ 次ターン更新時刻出力
	//---------------------------------------------------
	function lastModified($hako) {
		global $init;

		$timeString = date("Y年m月d日 H時", $hako->islandLastTime);
		echo <<<END
<div class="lastModified">
<p>最終更新時間: $timeString<br>
(次のターンまで、あと
<script type="text/javascript">
	remainTime($hako->islandLastTime + $init->unitTime);
</script>
</p>
</div>
END;
	}
}
//--------------------------------------------------------------------
class HtmlTop extends HTML {
	//---------------------------------------------------
	// ＴＯＰページ
	//---------------------------------------------------
	function main($hako, $data) {
		global $init;

		echo "<h1>{$init->title} トップ</h1>\n";

		if(DEBUG === true) {
			echo <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
	<input type="hidden" name="mode" value="debugTurn">
	<input type="submit" class="btn btn-default" value="ターンを進める">
</form>
END;
		}

		echo "<div class='Turn'>ターン".$hako->islandTurn."</div>";

		// 最終更新時刻 ＋ 次ターン更新時刻出力
		$this->lastModified($hako);

		$allyfile = $init->baseDir . "/hako-ally.php";
		if(empty($data['defaultDevelopeMode']) || $data['defaultDevelopeMode'] == "cgi") {
			$radio = "checked";
			$radio2 = "";
		} else {
			$radio = "";
			$radio2 = "checked";
		}

		$_defaultPassword = isset($data['defaultPassword']) ? $data['defaultPassword'] : "";
		echo <<<END

<hr>

<div class="row">
<div class="col-xs-6">
END;

if ( count($hako->islandList) != 0 ) {
	echo <<<END
<h2>自分の島へ</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
	<div class="form-group">
		<label>あなたの島の名前は？</label>
		<select name="ISLANDID" class="form-control">
			$hako->islandList
		</select>
	</div>
	<div class="form-group">
		<label>パスワード</label>
		<input type="password" name="PASSWORD" class="form-control" value="{$_defaultPassword}" size="32" maxlength="32" required>
	</div>

	<input type="hidden" name="mode" value="owner">

	<div class="form-group">
		<label class="radio-inline">
		  <input type="radio" name="DEVELOPEMODE" value="cgi" id="cgi" $radio>
			<label for="cgi">通常モード</label>
		</label>
		<label class="radio-inline">
		  <input type="radio" name="DEVELOPEMODE" value="javascript" id="javascript" $radio2>
			<label for="javascript">JavaScript高機能モード</label>
		</label>
	</div>

	<div class="form-group">
	<input type="submit" class="btn btn-primary" value="開発しに行く">
	</div>
</form>
END;
}

echo <<<END
</div>
<div class="col-xs-6">
END;
		$this->infoPrint(); // 「お知らせ」を表示
echo <<<END
</div>

</div>



<hr>
<h2>各部門ランキング</h2>
<div class="table-responsive">
<table class="table table-condensed">
END;
		$element   = array('point', 'money', 'food', 'pop', 'area', 'fire', 'pots', 'gold', 'rice', 'peop', 'monster', 'taiji', 'farm', 'factory', 'commerce', 'hatuden', 'mountain', 'team');
		$bumonName = array("総合ポイント", $init->nameFunds, $init->nameFood, $init->namePopulation, $init->nameArea, "軍事力", "成長", "収入", "収穫", "人口増加", "怪獣出現数", "怪獣退治数", "農場", "工場", "商業", "発電所", "採掘場", "サッカー");
		$bumonUnit = array('pts', $init->unitMoney, $init->unitFood, $init->unitPop, $init->unitArea, "機密事項", "pts↑", $init->unitMoney, $init->unitFood, $init->unitPop, $init->unitMonster, $init->unitMonster, "0{$init->unitPop}", "0{$init->unitPop}", "0{$init->unitPop}", "000kw", "0{$init->unitPop}", 'pts');

		for($r = 0; $r < sizeof($element); $r++) {
			$max = 0;
			for($i = 0; $i < $hako->islandNumber; $i++) {
				$island = $hako->islands[$i];
				if(($island[$element[$r]] > $max) && ($island['isBF'] != 1)) {
					$max = $island[$element[$r]];
					$rankid[$r] = $i;
				}
			}
			if($max == 0) {
				if(($r % 6) == 0) {
					echo "<tr>\n";
				}
				echo "<td width=\"15%\" class=\"M\">";
				echo "<table class=\"table table-bordered\" style=\"border:0\">\n";
				echo "<thead><tr><th {$init->bgTitleCell}>{$init->tagTH_}{$bumonName[$r]}{$init->_tagTH}</th></tr></thead>\n";
				echo "<tr><td class=\"TenkiCell\">{$init->tagName_}-{$init->_tagName}</a></td></tr>\n";
				echo "<tr><td class=\"TenkiCell\">-</td></tr>\n";
				echo "</table></td>\n";

				if(($r % 6) == 5) {
					echo "</tr>\n";
				}

			} else {
				if($r == 5) {
					$max = "";
				}
				if(($r % 6) == 0) {
					echo "<tr>\n";
				}
				$island = $hako->islands[$rankid[$r]];
				$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
				echo "<td width=\"15%\" class=\"M\">";
				echo "<table class=\"table table-bordered\">\n";
				echo "<thead><tr><th {$init->bgTitleCell}>{$init->tagTH_}{$bumonName[$r]}{$init->_tagTH}</th></tr></thead>\n";
				echo "<tr><td class=\"TenkiCell\"><a href=\"{$GLOBALS['THIS_FILE']}?Sight={$island['id']}\">{$init->tagName_}{$name}{$init->_tagName}</a></td></tr>\n";
				echo "<tr><td class=\"TenkiCell\">{$max}{$bumonUnit[$r]}</td></tr>\n";
				echo "</table></td>\n";
				if(($r % 6) == 5) {
					echo "</tr>\n";
				}
			}
		}
		echo "</table>\n";
		echo "</div>\n";
		echo "<BR>\n";

		if($hako->allyNumber) {
			echo <<<END
<hr>

<div id="IslandView">
<h2>同盟の状況</h2>
<table class="table table-bordered">
<tr>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}同盟{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}マーク{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}島の数{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}総人口{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}占有率{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameCommercialScale}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerPlantScale}{$init->_tagTH}</th>
</tr>
END;
			for($i=0; $i<$hako->allyNumber; $i++) {
//				if($num && ($i != $hako->idToAllyNumber[$num])) {
//					continue;
//				}
				$ally = $hako->ally[$i];
				$j = $i + 1;

				$pop = $farm = $factory = $commerce = $mountain = $hatuden = $missiles = 0;
				for($k=0; $k<$ally['number']; $k++) {
					$id = $ally['memberId'][$k];
					$island = $hako->islands[$hako->idToNumber[$id]];
					$pop += $island['pop'];
					$farm += $island['farm'];
					$factory += $island['factory'];
					$commerce += $island['commerce'];
					$mountain += $island['mountain'];
					$hatuden += $island['hatuden'];
				}
				$name = /*($num) ? "{$init->tagName_}{$ally['name']}{$init->_tagName}" : */"<a href=\"{$allyfile}?AmiOfAlly={$ally['id']}\">{$ally['name']}</a>";
				$pop = $pop . $init->unitPop;
				$farm = ($farm <= 0) ? $init->notHave : $farm * 10 . $init->unitPop;
				$factory = ($factory <= 0) ? $init->notHave : $factory * 10 . $init->unitPop;
				$commerce = ($commerce <= 0) ? $init->notHave : $commerce * 10 . $init->unitPop;
				$mountain = ($mountain <= 0) ? $init->notHave : $mountain * 10 . $init->unitPop;
				$hatuden = ($hatuden <= 0) ? "0kw" : $hatuden * 1000 . kw;

				echo <<<END
<tr>
	<th {$init->bgNumberCell} rowspan="2">{$init->tagNumber_}$j{$init->_tagNumber}</th>
	<td {$init->bgNameCell} rowspan="2">{$name}</td>
	<td class="TenkiCell"><b><font color="{$ally['color']}">{$ally['mark']}</font></b></td>
	<td {$init->bgInfoCell}>{$ally['number']}島</td>
	<td {$init->bgInfoCell}>{$pop}</td>
	<td {$init->bgInfoCell}>{$ally['occupation']}%</td>
	<td {$init->bgInfoCell}>{$farm}</td>
	<td {$init->bgInfoCell}>{$factory}</td>
	<td {$init->bgInfoCell}>{$commerce}</td>
	<td {$init->bgInfoCell}>{$mountain}</td>
	<td {$init->bgInfoCell}>{$hatuden}</td>
</tr>
<tr>
	<td {$init->bgCommentCell} colspan=9>{$init->tagTH_}<a href="{$allyfile}?Allypact={$ally['id']}">{$ally['oName']}</a>：{$init->_tagTH}{$ally['comment']}</td>
</tr>
END;
			}
			echo "</table>\n";
		}
		echo "<hr>\n";
		echo "<div ID=\"IslandView\">\n";

		echo "<h2>諸島の状況</h2>";

		$islandListStart = 0;
		$islandListSentinel = 0;

		if ($hako->islandNumber != 0) {
			$islandListStart = $data['islandListStart'];
			if ($init->islandListRange == 0) {
				$islandListSentinel = $hako->islandNumberNoBF;
			} else {
				$islandListSentinel = $islandListStart + $init->islandListRange - 1;
				if ( $islandListSentinel > $hako->islandNumberNoBF ) {
					$islandListSentinel = $hako->islandNumberNoBF;
				}
			}
		}
		echo "<p>\n";
		echo "島の名前をクリックすると、<strong>観光</strong>することができます。\n";
		echo "</p>\n";


		if (($islandListStart  != 1) || ($islandListSentinel != $hako->islandNumberNoBF)) {
			for ($i = 1; $i <= $hako->islandNumberNoBF ; $i += $init->islandListRange) {
				$j = $i + $init->islandListRange - 1;
				if ($j > $hako->islandNumberNoBF) {
					$j = $hako->islandNumberNoBF;
				}
				echo " ";
				if ( $i != $islandListStart ) {
					echo "<a href=\"" . $GLOBALS['THIS_FILE'] . "?islandListStart=" . $i ."\">";
				}
				echo " [ ". $i . " - " . $j . " ]";

				if ($i != $islandListStart) {
					echo "</a>";
				}
			}
		}
		$islandListStart--;
		$this->islandTable($hako, $islandListStart, $islandListSentinel);

		echo "<hr>\n\n";

		echo "<div ID=\"IslandView\">\n";
		echo "<h2>Battle Fieldの状況</h2>\n";

		$this->islandTable($hako, $hako->islandNumberNoBF, $hako->islandNumber);

		echo "<hr>\n";

		$this->historyPrint();

		if($init->registMode) {
			echo <<<END
<FORM action="{$GLOBALS['THIS_FILE']}?mode=conf" method="POST">
	<div class="text-right">
		<INPUT TYPE="password" NAME="PASSWORD" SIZE=8 MAXLENGTH=32 required>
		<INPUT TYPE="submit" VALUE="管理用" NAME="AdminButton">
	</div>
</FORM>
END;
		}
	}

	//---------------------------------------------------
	// 島の一覧表を表示
	//---------------------------------------------------
	function islandTable(&$hako, $start, $sentinel) {
		global $init;

		if ($sentinel == 0) {
			return;
		}
		echo '<div class="table-responsive">';
		echo '<table class="table table-bordered table-condensed">';

		for($i = $start; $i < $sentinel ; $i++) {
			$island        = $hako->islands[$i];
			$island['pop'] = ($island['pop'] <= 0) ? 1 : $island['pop'];

			$j            = isset($island['isBF']) ? '★' : $i + 1;
			$id           = $island['id'];
			$pop          = $island['pop'] . $init->unitPop;
			$area         = $island['area'] . $init->unitArea;
			$point        = $island['point'];
			$eisei        = $island['eisei'];
			$zin          = $island['zin'];
			$item         = $island['item'];
			$money        = Util::aboutMoney($island['money']);
			$lot          = $island['lot'];
			$food         = $island['food'] . $init->unitFood;
			$unemployed   = ($island['pop'] - ($island['farm'] + $island['factory'] + $island['commerce'] + $island['mountain'] + $island['hatuden']) * 10) / $island['pop'] * 100;
			$unemployed   = '<font color="' . ($unemployed < 0 ? 'black' : '#C7243A') . '">' . sprintf("%-3d%%", $unemployed) . '</font>';
			$farm         = ($island['farm'] <= 0) ? $init->notHave : $island['farm'] * 10 . $init->unitPop;
			$factory      = ($island['factory'] <= 0) ? $init->notHave : $island['factory'] * 10 . $init->unitPop;
			$commerce     = ($island['commerce'] <= 0) ? $init->notHave : $island['commerce'] * 10 . $init->unitPop;
			$mountain     = ($island['mountain'] <= 0) ? $init->notHave : $island['mountain'] * 10 . $init->unitPop;
			$hatuden      = ($island['hatuden'] <= 0) ? $init->notHave : $island['hatuden'] * 10 . $init->unitPop;
			$taiji        = ($island['taiji'] <= 0) ? "0匹" : $island['taiji'] * 1 . $init->unitMonster;
			$peop         = ($island['peop'] < 0) ? "{$island['peop']}{$init->unitPop}" : "+{$island['peop']}{$init->unitPop}";
			$okane        = ($island['gold'] < 0) ? "{$island['gold']}{$init->unitMoney}" : "+{$island['gold']}{$init->unitMoney}";
			$gohan        = ($island['rice'] < 0) ? "{$island['rice']}{$init->unitFood}" : "+{$island['rice']}{$init->unitFood}";
			$poin         = ($island['pots'] < 0) ? "{$island['pots']}pts" : "+{$island['pots']}pts";
			$tenki        = $island['tenki'];
			$team         = $island['team'];
			$shiai        = $island['shiai'];
			$kachi        = $island['kachi'];
			$make         = $island['make'];
			$hikiwake     = $island['hikiwake'];
			$kougeki      = $island['kougeki'];
			$bougyo       = $island['bougyo'];
			$tokuten      = $island['tokuten'];
			$shitten      = $island['shitten'];
			$comment      = $island['comment'];
			$comment_turn = $island['comment_turn'];
			$starturn     = $island['starturn'];
			$monster      = '';

			if($island['monster'] > 0) {
				$monster = "<strong class=\"monster\">[怪獣{$island['monster']}体]</strong>";
			}

			if($island['keep'] == 1) {
				$comment = "<span class=\"attention\">この島は管理人預かり中です。</span>";
			}

			$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
			if($island['absent'] == 0) {
				$name = "{$init->tagName_}{$name}{$init->_tagName}";
			} else {
				$name = "{$init->tagName2_}{$name}({$island['absent']}){$init->_tagName2}";
			}

			if(!empty($island['owner'])) {
				$owner = $island['owner'];
			} else {
				$owner = "コメント";
			}

			$prize = $island['prize'];
			$prize = $hako->getPrizeList($prize);

			$point = $island['point'];

			// /*if($init->commentNew > 0 && ($comment_turn + $init->commentNew) > $hako->islandTurn) { */
			if( $comment_turn > $hako->islandTurn) {
				$comment .= " <span class=\"new\">New</span>";
			}

			$sora = "";
			switch ($tenki) {
				case 1:
					$sora = "<IMG SRC=\"{$init->imgDir}/tenki1.gif\" ALT=\"晴れ\" title=\"晴れ\">";
					break;
				case 2:
					$sora = "<IMG SRC=\"{$init->imgDir}/tenki2.gif\" ALT=\"曇り\" title=\"曇り\">";
					break;
				case 3:
					$sora = "<IMG SRC=\"{$init->imgDir}/tenki3.gif\" ALT=\"雨\" title=\"雨\">";
					break;
				case 4:
					$sora = "<IMG SRC=\"{$init->imgDir}/tenki4.gif\" ALT=\"雷\" title=\"雷\">";
					break;
				default :
					$sora = "<IMG SRC=\"{$init->imgDir}/tenki5.gif\" ALT=\"雪\" title=\"雪\">";
			}



			$eiseis = "";
			for($e = 0; $e < $init->EiseiNumber; $e++) {
				if (isset($eisei[$e])) {
					if($eisei[$e] > 0) {
						$eiseis .= "<img src=\"{$init->imgDir}/eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eisei[$e]}%\" title=\"{$init->EiseiName[$e]} {$eisei[$e]}%\"> ";
					} else {
						$eiseis .= "　";
					}
				}
			}

			$zins = "";
			for($z = 0; $z < $init->ZinNumber; $z++) {
				if (isset($zin[$z])) {
					if($zin[$z] > 0) {
						$zins .= "<img src=\"{$init->imgDir}/zin{$z}.gif\" alt=\"{$init->ZinName[$z]}\" title=\"{$init->ZinName[$z]}\"> ";
					} else {
						$zins .= "";
					}
				}
			}

			$items = "";
			for($t = 0; $t < $init->ItemNumber; $t++) {
				if (isset($item[$t])) {
					if($item[$t] > 0) {
						if($t == 20) {
							$items .= "<img src=\"{$init->imgDir}/item{$t}.gif\" alt=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\"  title=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\"> ";
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

			$viking = "";
			for($v = 10; $v < 15; $v++) {
				if($island['ship'][$v] > 0) {
					$viking .= " <IMG SRC=\"{$init->imgDir}/ship{$v}.gif\" width=\"16\" height=\"16\" ALT=\"{$init->shipName[$v]}出現中\" title=\"{$init->shipName[$v]}出現中\">";
				}
			}

			$start = "";
			if(($hako->islandTurn - $island['starturn']) < $init->noAssist) {
				$start .= " <IMG SRC=\"{$init->imgDir}/start.gif\" width=\"16\" height=\"16\" ALT=\"初心者マーク\" title=\"初心者マーク\">";
			}

			$soccer = "";
			if($island['soccer'] > 0) {
				$soccer .= " <IMG SRC=\"{$init->imgDir}/soccer.gif\" width=\"16\" height=\"16\" ALT=\"総合ポイント：{$team}　{$shiai}戦{$kachi}勝{$make}敗{$hikiwake}分　攻撃力：{$kougeki}　守備力：{$bougyo}　得点：{$tokuten}　失点：{$shitten}\" title=\"総合ポイント：{$team}　{$shiai}戦{$kachi}勝{$make}敗{$hikiwake}分　攻撃力：{$kougeki}　守備力：{$bougyo}　得点：{$tokuten}　失点：{$shitten}\">";
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
			echo <<<END
<thead>
	<tr>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}島{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}得点{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePopulation}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameArea}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameWeather}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFunds}{$init->_tagTH}{$lots}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFood}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameUnemploymentRate}{$init->_tagTH}</th>
	</tr>
</thead>
END;
			$keep = isset($keep) ? $keep : "";

			echo <<<END
	<tr>
		<th {$init->bgNumberCell} rowspan="5">{$init->tagNumber_}$j{$init->_tagNumber}</th>
		<td {$init->bgNameCell} rowspan="5" valign="top">
			<h3><a href="{$GLOBALS['THIS_FILE']}?Sight={$id}">{$name}</a> <small>{$start}{$monster}{$soccer}</small></h3>
			{$prize}{$viking}<br>
			{$zins}<br>
			<small>({$peop} {$okane} {$gohan} {$poin})</small>
		</td>
		<td {$init->bgInfoCell}>$point</td>
		<td {$init->bgInfoCell}>$pop</td>
		<td {$init->bgInfoCell}>$area</td>
		<td class="TenkiCell">$sora</td>
		<td {$init->bgInfoCell}>$money</td>
		<td {$init->bgInfoCell}>$food</td>
		<td {$init->bgInfoCell}>$unemployed</td>
	</tr>
	<tr>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameCommercialScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerPlantScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerSupplyRate}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameSatellite}{$init->_tagTH}</th>
	</tr>
	<tr>
		<td {$init->bgInfoCell}>$farm</td>
		<td {$init->bgInfoCell}>$factory</td>
		<td {$init->bgInfoCell}>$commerce</td>
		<td {$init->bgInfoCell}>$mountain</td>
		<td {$init->bgInfoCell}>{$hatuden}</td>
		<td {$init->bgInfoCell}>$ene</td>
		<td class="ItemCell">$eiseis</td>
	</tr>
	<tr>
		<th {$init->bgTitleCell}>{$init->tagTH_}取得アイテム{$init->_tagTH}</th>
		<td class="ItemCell" colspan="6">　$items</td>
	</tr>
	<tr>
		<td {$init->bgCommentCell} colspan="7">{$init->tagTH_}{$owner}：{$init->_tagTH}$comment</td>
	</tr>
END;

		}
		echo "</table>";
		echo "</div>";
		echo "</div>";
	}

	//---------------------------------------------------
	// 島の登録と設定
	//---------------------------------------------------
	function register(&$hako, $data = "") {
		global $init;

		echo <<<END
<div class="row">
	<div class="col-lg-4 col-md-6 col-sm-8 col-xs-12">
		<h1>島の登録・設定変更</h1>
END;
		$this->newDiscovery($hako->islandNumber);
		$this->changeIslandInfo($hako->islandList);
		$this->changeOwnerName($hako->islandList);

		echo <<<END
	</div>
</div>

END;

	}

	//---------------------------------------------------
	// 新しい島を探す
	//---------------------------------------------------
	function newDiscovery($number) {
		global $init;

		echo "<div id=\"NewIsland\">\n";
		echo "<h2>新しい島を探す</h2>\n";

		if($number < $init->maxIsland) {
			if($init->registMode == 1 && $init->adminMode == 0) {
				echo "当箱庭では不適当な島名などの事前チェックを行っています。<BR>\n";
				echo "参加希望の方は、管理者に「島名」と「パスワード」を送信してください。<BR>\n";
			} else {
				echo <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
	<div class="form-group">
		<label>どんな名前をつける予定？</label>
		<div class="input-group">
			<input type="text" class="form-control" name="ISLANDNAME" size="32" maxlength="32" required>
			<span class="input-group-addon">島</span>
		</div>
	</div>

	<div class="form-group">
		<label>あなたのお名前は？(省略可)</label>
		<input type="text" class="form-control" name="OWNERNAME" size="32" maxlength="32">
	</div>
	<div class="form-group">
		<label>パスワードは？</label>
		<input type="password" class="form-control" name="PASSWORD" size="32" maxlength="32" required>
	</div>
	<div class="form-group">
		<label>念のためパスワードをもう一回</label>
		<input type="password" class="form-control" name="PASSWORD2" size="32" maxlength="32" required>
	</div>
	<div class="form-group">
		<input type="submit" class="btn btn-primary" value="探しに行く">
	</div>
	<input type="hidden" name="mode" value="new">
</form>
END;
			}
		} else {
			echo "島の数が最大数です。現在登録できません。\n";
		}
		echo "</div>\n";
	}

	//---------------------------------------------------
	// 島の名前とパスワードの変更
	//---------------------------------------------------
	function changeIslandInfo($islandList = "") {
		global $init;

		echo <<<END
<div id="ChangeInfo">
	<h2>島の名前とパスワードの変更</h2>

	<p class="alert alert-info" role="alert">(注意) 名前の変更には{$init->costChangeName}{$init->unitMoney}かかります。</p>

	<form action="{$GLOBALS['THIS_FILE']}" method="post">
		<div class="form-group">
			<label>どの島ですか？</label>
			<select NAME="ISLANDID" class="form-control">$islandList</select>
		</div>

		<div class="form-group">
			<label>どんな名前に変えますか？(変更する場合のみ)</label>
			<div class="input-group">
				<input type="text" class="form-control" name="ISLANDNAME" size="32" maxlength="32">
				<span class="input-group-addon">島</span>
			</div>
		</div>

		<div class="form-group">
			<label>パスワードは？(必須)</label>
			<input type="password" class="form-control" name="OLDPASS" size="32" maxlength="32" required>
		</div>
		<div class="form-group">
			<label>新しいパスワードは？(変更する時のみ)</label>
			<input type="password" class="form-control" name="PASSWORD" size="32" maxlength="32">
		</div>
		<div class="form-group">
			<label>念のためパスワードをもう一回(変更する時のみ)</label>
			<input type="password" class="form-control" name="PASSWORD2" size="32" maxlength="32">
		</div>
		<div class="form-group">
			<input type="submit" class="btn btn-primary" value="変更する">
		</div>
		<input type="hidden" name="mode" value="change">
	</form>
</div>
END;
	}

	//---------------------------------------------------
	// オーナー名の変更
	//---------------------------------------------------
	function changeOwnerName($islandList = "") {
		global $init;

		echo <<<END
<div id="ChangeOwnerName">
	<h2>オーナー名の変更</h2>
	<form action="{$GLOBALS['THIS_FILE']}" method="post">
		<div class="form-group">
			<label>どの島ですか？</label>
			<select name="ISLANDID" class="form-control">{$islandList}</select>
		</div>
		<div class="form-group">
			<label>新しいオーナー名は？</label>
			<input type="text" name="OWNERNAME" class="form-control" size="32" maxlength="32">
		</div>
		<div class="form-group">
			<label>パスワードは？</label>
			<input type="password" name="OLDPASS" class="form-control" size="32" maxlength="32" required>
		</div>
		<div class="form-group">
			<input type="submit" class="btn btn-primary" value="変更する">
		</div>
		<input type="hidden" name="mode" value="ChangeOwnerName">
	</form>
</div>
END;
	}


	//---------------------------------------------------
	// 最近の出来事
	//---------------------------------------------------
	function log() {
		global $init;

		echo <<<END
<div class="row">
	<div class="col-xs-12">
END;
		echo "<div id=\"RecentlyLog\">\n";
		echo "<h1>最近の出来事</h1>\n";
		for($i = 0; $i < $init->logTopTurn; $i++) {
			LogIO::logFilePrint($i, 0, 0);
		}
		echo "</div>\n";

		echo <<<END
	</div>
</div>
END;
	}

	//---------------------------------------------------
	// 発見の記録
	//---------------------------------------------------
	function historyPrint() {
		echo "<div id=\"HistoryLog\">\n";
		echo "<h2>歴史</h2>";
		LogIO::historyPrint();
		echo "</div>\n";
	}

	//---------------------------------------------------
	// お知らせ
	//---------------------------------------------------
	function infoPrint() {
		global $init;

		echo "<div id=\"HistoryLog\">\n";
		echo "<h2>お知らせ</h2>\n";
		echo "<DIV style=\"overflow:auto; height:{$init->divHeight}px;\">\n";
		LogIO::infoPrint();
		echo "</div></div>\n";
	}

}
//------------------------------------------------------------------
class HtmlMap extends HTML {
	//---------------------------------------------------
	// 開発画面
	//---------------------------------------------------
	function owner($hako, $data) {
		global $init;

		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		$island = $hako->islands[$number];

		// パスワードチェック
		if(!Util::checkPassword($island['password'], $data['PASSWORD'])){
			Error::wrongPassword();
			return;
		}
		// if(((empty($data['defaultImg'])) || ($data['defaultImg'] == $init->imgDir)) /*&& ($init->setImg)*/ ) {
		// 	Error::emptyImg();
		// 	return;
		// }
		$this->tempOwer($hako, $data, $number);

		// IP情報取得
		$logfile = "{$init->dirName}/{$init->logname}";
		$ax = $init->axesmax - 1;
		$log = file($logfile);
		$fp = fopen($logfile,"w");
		$timedata = date("Y年m月d日(D) H時i分s秒");
		$islandID = "{$data['ISLANDID']}";
		$name = "{$island['name']}島";
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

	//---------------------------------------------------
	// 観光画面
	//---------------------------------------------------
	function visitor($hako, $data) {
		global $init;
		// idから島番号を取得
		$id = $data['ISLANDID'];
		$number = isset($hako->idToNumber[$id]) ? $hako->idToNumber[$id] : -1;
		// なぜかその島がない場合
		if($number < 0 || $number > $hako->islandNumber) {
			Error::problem();
			return;
		}
		$island = $hako->islands[$number];
		$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);

		echo <<<END
<div class="text-center">
	<h2>{$init->tagName_}「{$name}」{$init->_tagName}へようこそ！！</h2>
</div>
END;

		$this->islandInfo($island, $number, 0);
		$this->islandMap($hako, $island, 0);

		// 他の島へ
		echo <<<END
<div class="text-center">
	<form action="{$GLOBALS['THIS_FILE']}" method="get">
	<select name="Sight">$hako->islandList</select>
	<input type="submit" value="観光">
</form>
</div>
END;

		$this->islandRecent($island, 0);
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
		if($tenki == 1) {
			$sora .= "<IMG SRC=\"{$init->imgDir}/tenki1.gif\" ALT=\"晴れ\" title=\"晴れ\">";
		} elseif($tenki == 2) {
			$sora .= "<IMG SRC=\"{$init->imgDir}/tenki2.gif\" ALT=\"曇り\" title=\"曇り\">";
		} elseif($tenki == 3) {
			$sora .= "<IMG SRC=\"{$init->imgDir}/tenki3.gif\" ALT=\"雨\" title=\"雨\">";
		} elseif($tenki == 4) {
			$sora .= "<IMG SRC=\"{$init->imgDir}/tenki4.gif\" ALT=\"雷\" title=\"雷\">";
		} else {
			$sora .= "<IMG SRC=\"{$init->imgDir}/tenki5.gif\" ALT=\"雪\" title=\"雪\">";
		}

		$eiseis = "";
		for($e = 0; $e < $init->EiseiNumber; $e++) {
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
		for($z = 0; $z < $init->ZinNumber; $z++) {
			if ( isset($zin[$z]) ) {
				if($zin[$z] > 0) {
					$zins .= "<img src=\"{$init->imgDir}/zin{$z}.gif\" alt=\"{$init->ZinName[$z]}\" title=\"{$init->ZinName[$z]}\"> ";
				} else {
					$zins .= "";
				}
			}
		}

		$items = "";
		for($t = 0; $t < $init->ItemNumber; $t++) {
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
		echo <<<END
<div id="islandInfo" class="table-responsive">
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePopulation}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameArea}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFunds}{$init->_tagTH}{$lots}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFood}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameUnemploymentRate}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameCommercialScale}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerPlantScale}{$init->_tagTH}</th>
				<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerSupplyRate}{$init->_tagTH}</th>
			</tr>
		</thead>
		<tr>
			<th {$init->bgNumberCell} rowspan="4">{$init->tagNumber_}$rank{$init->_tagNumber}</th>
			<td {$init->bgInfoCell}>$pop</td>
			<td {$init->bgInfoCell}>$area</td>
			<td {$init->bgInfoCell}>$money</td>
			<td {$init->bgInfoCell}>$food</td>
			<td {$init->bgInfoCell}>$unemployed</td>
			<td {$init->bgInfoCell}>$farm</td>
			<td {$init->bgInfoCell}>$factory</td>
			<td {$init->bgInfoCell}>$commerce</td>
			<td {$init->bgInfoCell}>$mountain</td>
			<td {$init->bgInfoCell}>$hatuden</td>
			<td {$init->bgInfoCell}>$ene</td>
		</tr>
		<tr>
			<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameWeather}{$init->_tagTH}</th>
			<td class="TenkiCell">$sora</td>
			<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMilitaryTechnology}{$init->_tagTH}</th>
			<td {$init->bgInfoCell}>{$arm}</td>
			<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMonsterExterminationNumber}{$init->_tagTH}</th>
			<td {$init->bgInfoCell}>$taiji</td>
			<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameSatellite}{$init->_tagTH}</th>
			<td class="ItemCell" colspan="4">　$eiseis</td>
		</tr>
		<tr>
			<th {$init->bgTitleCell}>{$init->tagTH_}ジン{$init->_tagTH}</th>
			<td class="ItemCell" colspan="5">　$zins</td>
			<th {$init->bgTitleCell}>{$init->tagTH_}アイテム{$init->_tagTH}</th>
			<td class="ItemCell" colspan="4">　$items</td>
		</tr>
		<tr>
			<td colspan="11" {$init->bgCommentCell}>$comment</td>
		</tr>
	</table>
</div>
END;
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

		echo "<div id=\"islandMap\" class=\"text-center\">";
		echo '<div class="table-responsive">';
		echo "<table border=\"1\"><tr><td>\n";

		for($y = 0; $y < $init->islandSize; $y++) {
			if($y % 2 == 0) {
				echo "<img src=\"{$init->imgDir}/land0.gif\" width=\"16\" height=\"32\" alt=\"{$y}\" title=\"{$y}\">";
			}
			for($x = 0; $x < $init->islandSize; $x++) {
				//$hako->landString($land[$x][$y], $landValue[$x][$y], $x, $y, $mode, $comStr[$x][$y]);
				$hako->landString($land[$x][$y], $landValue[$x][$y], $x, $y, $mode, $comStr);
			}
			if($y % 2 == 1) {
				echo "<img src=\"{$init->imgDir}/land0.gif\" width=\"16\" height=\"32\" alt=\"{$y}\" title=\"{$y}\">";
			}
			echo "<br>";
		}

		echo "<div id=\"NaviView\"></div>";

		echo "</div>";
		echo "</td></tr></table></div>\n";

		echo "<center>\n";
		//echo "<p>開始ターン：{$island['starturn']}</p>\n";

if (isset($island['soccer'])){
		if($island['soccer'] > 0) {
			echo <<<END
<table class="table table-bordered">
	<tr>
		<th {$init->bgTitleCell}>{$init->tagTH_}総合得点{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}成績{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}攻撃力{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}守備力{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}得点{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}失点{$init->_tagTH}</th>
	</tr>
	<tr>
		<td {$init->bgInfoCell}>{$island['team']}</td>
		<td {$init->bgInfoCell}>{$island['shiai']}戦{$island['kachi']}勝{$island['make']}敗{$island['hikiwake']}分</td>
		<td {$init->bgInfoCell}>{$island['kougeki']}</td>
		<td {$init->bgInfoCell}>{$island['bougyo']}</td>
		<td {$init->bgInfoCell}>{$island['tokuten']}</td>
		<td {$init->bgInfoCell}>{$island['shitten']}</td>
	</tr>
</table>
<br>
END;
		}
	}
		echo "</center>\n";
	}


	//---------------------------------------------------
	// 島の近況
	//---------------------------------------------------
	function islandRecent($island, $mode = 0) {
		global $init;

		echo "<hr>\n";

		echo "<div id=\"RecentlyLog\">\n";
		echo "<h2>{$island['name']}島の近況</h2>\n";
		for($i = 0; $i < $init->logMax; $i++) {
			LogIO::logFilePrint($i, $island['id'], $mode);
		}
		echo "</div>\n";
	}

	//---------------------------------------------------
	// 開発画面
	//---------------------------------------------------
	function tempOwer($hako, $data, $number = 0) {
		global $init;
		$island = $hako->islands[$number];
		$name   = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
		$width  = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;
		$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
		echo <<<END
<script type="text/javascript">
var w;
var p = $defaultTarget;

function ps(x, y) {
	document.InputPlan.POINTX.options[x].selected = true;
	document.InputPlan.POINTY.options[y].selected = true;
	return true;
}

function ns(x) {
	document.InputPlan.NUMBER.options[x].selected = true;
	return true;
}

function settarget(part){
	p = part.options[part.selectedIndex].value;
}
function targetopen() {
	w = window.open("{$GLOBALS['THIS_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}
</script>

<div class="text-center">
	<h2>{$init->tagName_}{$name}{$init->_tagName}開発計画</h2>
</div>

END;
		$this->islandInfo($island, $number, 1);
		echo <<<END
<div class="text-center">
<table class="table table-bordered">
<tr>
<td {$init->bgInputCell}>
<div class="text-center">
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="submit" class="btn btn-primary" value="計画送信">

<hr>

<b>計画番号</b>
<select name="NUMBER">
END;
		// 計画番号
		for($i = 0; $i < $init->commandMax; $i++) {
			$j = $i + 1;
			echo "<option value=\"{$i}\">{$j}</option>";
		}

		echo <<<END
</select><br>
<hr>
<strong>開発計画</strong><br>
<select name="COMMAND">
END;
		// コマンド
		$comCnt = count($init->comList);
		for($i = 0;  $i < $comCnt; $i++) {
			$kind = $init->comList[$i];
			$cost = $init->comCost[$kind];
			$s = '';

			if($cost == 0) {
				$cost = '無料';
			} elseif($cost < 0) {
				$cost = - $cost;
				if($kind == $init->comSellTree) {
					$cost .= $init->unitTree;
				} else {
					$cost .= $init->unitFood;
				}
			} else {
				$cost .= $init->unitMoney;
			}
			if ( isset($data['defaultKind']) ) {
				if($kind == $data['defaultKind']) {
					$s = 'selected';
				}
			}

			echo "<option value=\"{$kind}\" {$s}>{$init->comName[$kind]} ({$cost})</option>\n";
		}
		echo <<<END
</select>
<hr>
<strong>座標(</strong>
<select name="POINTX">
END;
		for($i = 0; $i < $init->islandSize; $i++) {

			if ( isset($data['defaultX']) ) {
				if($i == $data['defaultX']) {
					echo "<option value=\"{$i}\" selected>{$i}</option>\n";
				} else {
					echo "<option value=\"{$i}\">{$i}</option>\n";
				}
			} else {
				echo "<option value=\"{$i}\">{$i}</option>\n";
			}

		}
		echo "</select>, <select name=\"POINTY\">";
		for($i = 0; $i < $init->islandSize; $i++) {
			if ( isset($data['defaultY']) ) {
				if($i == $data['defaultY']) {
					echo "<option value=\"{$i}\" selected>{$i}</option>\n";
				} else {
					echo "<option value=\"{$i}\">{$i}</option>\n";
				}
			} else {
				echo "<option value=\"{$i}\">{$i}</option>\n";
			}

		}
		echo <<<END
</select><strong>)</strong>
<hr>
<strong>数量</strong>
<select name="AMOUNT">
END;
		 for($i = 0; $i < 100; $i++) {
			 echo "<option value=\"{$i}\">{$i}</option>\n";
		}
		 echo <<<END
</select>
<hr>
<strong>目標の島</strong><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
<hr>

<div>
	<p><strong>動作</strong></p>
	<label class="radio-inline">
		<input type="radio" name="COMMANDMODE" id="insert" value="insert" checked><label for="insert">挿入</label>
	</label>
	<label class="radio-inline">
		<input type="radio" name="COMMANDMODE" id="write" value="write"><label for="write">上書き</label><BR>
	</label>
	<label class="radio-inline">
		<input type="radio" name="COMMANDMODE" id="delete" value="delete"><label for="delete">削除</label>
	</label>
</div>

<hr>

<input type="hidden" name="DEVELOPEMODE" value="cgi">
<input type="submit" class="btn btn-primary" value="計画送信">
</form>

ミサイル発射上限数[<b> {$island['fire']} </b>]発
</div>
</td>
<td {$init->bgMapCell}>
END;
		$this->islandMap($hako, $island, 1); // 島の地図、所有者モード
		echo <<<END
</td>
<td {$init->bgCommandCell}>
END;
		$command = $island['command'];
		$commandMax = $init->commandMax;
		for($i = 0; $i < $commandMax; $i++) {
			$this->tempCommand($i, $command[$i], $hako);
		}
		echo <<<END
</td>
</tr>
</table>
</div>

 <hr>

<div id='CommentBox'>
	<h2>コメント更新</h2>
	<form action="{$GLOBALS['THIS_FILE']}" method="post">
		<div class="row">
		  <div class="col-xs-12">
			<div class="input-group">
				<input type="text" name="MESSAGE" class="form-control" size="80" value="{$island['comment']}" placeholder="コメントする">
				<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
				<input type="hidden" name="mode" value="comment">
				<input type="hidden" name="ISLANDID" value="{$island['id']}">
				<input type="hidden" name="DEVELOPEMODE" value="cgi">
			  <span class="input-group-btn">
				<input type="submit" class="btn btn-primary" value="コメント更新">
			  </span>
			</div>
		  </div>
		</div>

	</form>
</div>
END;
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
		$target = "{$init->tagName_}{$target}島{$init->_tagName}";
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
				if($arg >= $init->EiseiNumber) {
					$arg = 0;
				}
				$str = "{$init->tagComName_}{$init->EiseiName[$arg]}打ち上げ{$init->_tagComName}";
				break;

			case $init->comEiseimente:
				// 人工衛星修復
				if($arg >= $init->EiseiNumber) {
					$arg = 0;
				}
				$str = "{$init->tagComName_}{$init->EiseiName[$arg]}修復{$init->_tagComName}";
				break;

			case $init->comEiseiAtt:
				// 人工衛星破壊砲
				if($arg >= $init->EiseiNumber) {
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
<div class="text-center">
	{$init->tagBig_}島を発見しました！！{$init->_tagBig}<br>
	{$init->tagBig_}{$init->tagName_}「{$name}島」{$init->_tagName}と命名します。{$init->_tagBig}
</div>
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
			Error::problem();
			return;
		}
		$island = $hako->islands[$number];
		echo <<<END
<script type="text/javascript">
<!--
function ps(x, y) {
	window.opener.document.InputPlan.POINTX.options[x].selected = true;
	window.opener.document.InputPlan.POINTY.options[y].selected = true;
	return true;
}
//-->
</script>

<div align="center">
{$init->tagBig_}{$init->tagName_}{$island['name']}島{$init->_tagName}{$init->_tagBig}<br>
</div>
END;
		//島の地図
		$this->islandMap($hako, $island, 2);
	}
}

//------------------------------------------------------------------
class HtmlJS extends HtmlMap {

	//---------------------------------------------------
	// 開発画面
	//---------------------------------------------------
	function tempOwer($hako, $data, $number = 0) {
		global $init;

		$island = $hako->islands[$number];
		$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
		$width = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;

		// コマンドセット
		$set_com = "";
		$com_max = "";
		$commandMax = $init->commandMax;
		for($i = 0; $i < $commandMax; $i++) {
			// 各要素の取り出し
			$command = $island['command'][$i];
			$s_kind = $command['kind'];
			$s_target = $command['target'];
			$s_x = $command['x'];
			$s_y = $command['y'];
			$s_arg = $command['arg'];

			// コマンド登録
			if($i == $commandMax - 1){
				$set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target]\n";
				$com_max .= "0";
			} else {
				$set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target],\n";
				$com_max .= "0, ";
			}
		}
		//コマンドリストセット
		$l_kind;
		$set_listcom = "";
		$click_com = array("", "", "", "", "", "", "", "");
		$All_listCom = 0;
		$com_count = count($init->commandDivido);
		for($m = 0; $m < $com_count; $m++) {
			list($aa,$dd,$ff) = explode(",", $init->commandDivido[$m]);
			$set_listcom .= "[ ";
			for($i = 0; $i < $init->commandTotal; $i++) {
				$l_kind = $init->comList[$i];
				$l_cost = $init->comCost[$l_kind];
				if($l_cost == 0) {
					$l_cost = '無料';
				} elseif($l_cost < 0) {
					$l_cost = - $l_cost;
					if($l_kind == 83) {
						$l_cost .= $init->unitTree;
					} else {
						$l_cost .= $init->unitFood;
					}
				} else {
					$l_cost .= $init->unitMoney;
				}
				if($l_kind > $dd-1 && $l_kind < $ff+1) {
					$set_listcom .= "[$l_kind, '{$init->comName[$l_kind]}', '{$l_cost}'],\n";
					if($m >= 0 && $m <= 7){
						$click_com[$m] .= "<a href='javascript:void(0);' onclick='cominput(InputPlan, 6, {$l_kind})' onkeypress='cominput(InputPlan, 6, {$l_kind})' style='text-decoration:none'>{$init->comName[$l_kind]}({$l_cost})</a><br>\n";
					}
					$All_listCom++;
				}
				//if($l_kind < $ff+1) {
				//	next;
				//}
			}
			$bai = strlen($set_listcom);
			$set_listcom = substr($set_listcom, 0, $bai - 2);
			$set_listcom .= " ],\n";
		}
		$bai = strlen($set_listcom);
		$set_listcom = substr($set_listcom, 0, $bai - 2);
		if(empty($data['defaultKind'])) {
			$default_Kind = 1;
		} else {
			$default_Kind = $data['defaultKind'];
		}
		// 船リストセット
		$set_ships = "";
		for($i = 0; $i < $init->shipKind; $i++) {
			$set_ships .= "'".$init->shipName[$i]."',";
		}
		// 衛星リストセット
		//$set_eisei = implode("," , $init->EiseiName);
		$set_eisei = "";
		for($i = 0; $i < count($init->EiseiName); $i++) {
			$set_eisei .= "'".$init->EiseiName[$i]."',";
		}
		$set_eisei = substr($set_eisei, 0, -1);  // ケツカンマを削除

		// 島リストセット
		$set_island = "";
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$l_name = $hako->islands[$i]['name'];
			$l_name = preg_replace("/'/", "\'", $l_name);
			$l_id = $hako->islands[$i]['id'];
			if($i == $hako->islandNumber - 1){
				$set_island .= "[$l_id, '$l_name']\n";
			}else{
				$set_island .= "[$l_id, '$l_name'],\n";
			}
		}
		$set_island = substr($set_island, 0, -1);  // ケツカンマを削除


		$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
		echo <<<END
<div class="text-center">
	<h2>{$init->tagName_}{$name}{$init->_tagName}開発計画</h2>
</div>

<script type="text/javascript">
var w;
var p = $defaultTarget;

// ＪＡＶＡスクリプト開発画面配布元
// あっぽー庵箱庭諸島（ http://appoh.execweb.cx/hakoniwa/ ）
// Programmed by Jynichi Sakai(あっぽー)
// ↑ 削除しないで下さい。
var str;
var g  = [$com_max];
var k1 = [$com_max];
var k2 = [$com_max];
var tmpcom1 = [ [0, 0, 0, 0, 0] ];
var tmpcom2 = [ [0, 0, 0, 0, 0] ];
var command = [$set_com];
var comlist = [$set_listcom];

var islname   = [$set_island];
var shiplist  = [$set_ships];
var eiseilist = [$set_eisei];

var mx, my;

function init() {

	for(var i = 0; i < command.length; i++) {
		for(var s = 0; s < $com_count; s++) {
			var comlist2 = comlist[s];
			for(var j = 0; j < comlist2.length; j++) {
				if(command[i][0] == comlist2[j][0]) {
					g[i] = comlist2[j][1];
				}
			}
		}
	}
	SelectList('');
	outp();
	str = plchg();
	str = '<font color="blue">■ 送信済み ■<\\/font><br>' + str;
	disp(str, "");
	document.onmousemove = Mmove;
	// if(document.layers) {
	// 	//document.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
	// 	document.addEventListener("DOMContentLoaded", Event.MOUSEMOVE | Event.MOUSEUP, false);
	// }
	document.onmouseup = Mup;
	document.onmousemove = Mmove;
	document.onkeydown = Kdown;
	document.ch_numForm.AMOUNT.options.length = 100;
	for(i=0;i<document.ch_numForm.AMOUNT.options.length;i++){
		document.ch_numForm.AMOUNT.options[i].value = i;
		document.ch_numForm.AMOUNT.options[i].text = i;
	}
	document.InputPlan.SENDPROJECT.disabled = true;
	ns(0);
}

function cominput(theForm, x, k, z) {
	var a = theForm.NUMBER.options[theForm.NUMBER.selectedIndex].value;
	var b = theForm.COMMAND.options[theForm.COMMAND.selectedIndex].value;
	var c = theForm.POINTX.options[theForm.POINTX.selectedIndex].value;
	var d = theForm.POINTY.options[theForm.POINTY.selectedIndex].value;
	var e = theForm.AMOUNT.options[theForm.AMOUNT.selectedIndex].value;
	var f = theForm.TARGETID.options[theForm.TARGETID.selectedIndex].value;

	if(x == 6){
		b = k; menuclose();
	}

	var newNs = a;
	if (x == 1 || x == 2 || x == 6){
		if(x == 6) {
			b = k;
		}
		if(x != 2) {
			for(var i = $init->commandMax - 1; i > a; i--) {
				command[i] = command[i-1];
				g[i] = g[i-1];
			}
		}
		for(s = 0; s < $com_count ;s++) {
			var comlist2 = comlist[s];
			for(i = 0; i < comlist2.length; i++){
				if(comlist2[i][0] == b){
					g[a] = comlist2[i][1];
					break;
				}
			}
		}
		command[a] = [b,c,d,e,f];
		newNs++;
//		menuclose();

	} else if(x == 3) {
		var num = (k) ? k-1 : a;
		for(i = Math.floor(num); i < ($init->commandMax - 1); i++) {
			command[i] = command[i + 1];
			g[i] = g[i+1];
		}
		command[$init->commandMax - 1] = [81, 0, 0, 0, 0];
		g[$init->commandMax - 1] = '資金繰り';

	} else if(x == 4) {
		i = Math.floor(a);
		if (i == 0){ return true; }
		i = Math.floor(a);
		tmpcom1[i] = command[i];tmpcom2[i] = command[i - 1];
		command[i] = tmpcom2[i];command[i-1] = tmpcom1[i];
		k1[i] = g[i];k2[i] = g[i - 1];
		g[i] = k2[i];g[i-1] = k1[i];
		ns(--i);
		str = plchg();
		str = '<font color="#C7243A"><strong>■ 未送信 ■<\\/strong><\\/font><br>' + str;
		disp(str,"white");
		outp();
		newNs = i+1;
	} else if(x == 5) {
		i = Math.floor(a);
		if (i == $init->commandMax - 1){ return true; }
		tmpcom1[i] = command[i];tmpcom2[i] = command[i + 1];
		command[i] = tmpcom2[i];command[i + 1] = tmpcom1[i];
		k1[i] = g[i];k2[i] = g[i + 1];
		g[i] = k2[i];g[i + 1] = k1[i];
		newNs = i+1;
	}else if(x == 7){
		// 移動
		var ctmp = command[k];
		var gtmp = g[k];
		if(z > k) {
			// 上から下へ
			for(i = k; i < z-1; i++) {
				command[i] = command[i+1];
				g[i] = g[i+1];
			}
		} else {
			// 下から上へ
			for(i = k; i > z; i--) {
				command[i] = command[i-1];
				g[i] = g[i-1];
			}
		}
		command[i] = ctmp;
		g[i] = gtmp;
		newNs = i+1;
	}else if(x == 8){
		command[a][3] = k;
	}
	str = plchg();
	str = '<font color="#C7243A"><b>■ 未送信 ■<\\/b><\\/font><br>' + str;
	disp(str, "");
	outp();
	theForm.SENDPROJECT.disabled = false;
	ns(newNs);

	return true;
}

function plchg() {
	var strn1 = "";
	var strn2 = "";
	var arg = "";
	for(var i = 0; i < $init->commandMax; i++) {
		var c = command[i];
		var kind = '{$init->tagComName_}' + g[i] + '{$init->_tagComName}';
		var x = c[1];
		var y = c[2];
		var tgt = c[4];
		var point = '{$init->tagName_}' + "(" + x + "," + y + ")" + '{$init->_tagName}';

		for(var j = 0; j < islname.length ; j++) {
			if(tgt == islname[j][0]){
				tgt = '{$init->tagName_}' + islname[j][1] + "島" + '{$init->_tagName}';
			}
		}

		if(c[0] == $init->comMissileSM || c[0] == $init->comDoNothing || c[0] == $init->comGiveup){
			// ミサイル撃ち止め、資金繰り、島の放棄
			strn2 = kind;
		}else if(c[0] == $init->comMissileNM || // ミサイル関連
			c[0] == $init->comMissilePP ||
			c[0] == $init->comMissileST ||
			c[0] == $init->comMissileBT ||
			c[0] == $init->comMissileSP ||
			c[0] == $init->comMissileLD ||
			c[0] == $init->comMissileLU){
			if(c[3] == 0) {
				arg = "（無制限）";
			} else {
				arg = "（" + c[3] + "発）";
			}
			strn2 = tgt + point + "へ" + kind + arg;
		} else if((c[0] == $init->comSendMonster) || (c[0] == $init->comSendSleeper)) { // 怪獣派遣
			strn2 = tgt + "へ" + kind;
		} else if(c[0] == $init->comSell) { // 食料輸出
			if(c[3] == 0){ c[3] = 1; }
			arg = c[3] * 100;
			arg = "（" + arg + "{$init->unitFood}）";
			strn2 = kind + arg;
		} else if(c[0] == $init->comSellTree) { // 木材輸出
			if(c[3] == 0){ c[3] = 1; }
			arg = c[3] * 10;
			arg = "（" + arg + "{$init->unitTree}）";
			strn2 = kind + arg;
		} else if(c[0] == $init->comMoney) { // 資金援助
			if(c[3] == 0){ c[3] = 1; }
			arg = c[3] * {$init->comCost[$init->comMoney]};
			arg = "（" + arg + "{$init->unitMoney}）";
			strn2 = tgt + "へ" + kind + arg;
		} else if(c[0] == $init->comFood) { // 食料援助
			if(c[3] == 0){ c[3] = 1; }
			arg = c[3] * 100;
			arg = "（" + arg + "{$init->unitFood}）";
			strn2 = tgt + "へ" + kind + arg;
		} else if(c[0] == $init->comDestroy) { // 掘削
			if(c[3] == 0){
				strn2 = point + "で" + kind;
			} else {
				arg = c[3] * {$init->comCost[$init->comDestroy]};
				arg = "（予\算" + arg + "{$init->unitMoney}）";
				strn2 = point + "で" + kind + arg;
			}
		} else if(c[0] == $init->comLot) { // 宝くじ購入
			if(c[3] == 0) c[3] = 1;
			if(c[3] > 30) c[3] = 30;
				arg = c[3] * {$init->comCost[$init->comLot]};
				arg = "（予\算" + arg + "{$init->unitMoney}）";
				strn2 = kind + arg;
		} else if(c[0] == $init->comDbase) { // 防衛施設
			if(c[3] == 0) c[3] = 1;
			if(c[3] > $init->dBaseHP) c[3] = $init->dBaseHP;
				arg = c[3];
				arg = "(耐久力" + arg + "）";
				strn2 = point + "で" + kind + arg;
		} else if(c[0] == $init->comSdbase) { // 海底防衛施設
			if(c[3] == 0) c[3] = 1;
			if(c[3] > $init->sdBaseHP) c[3] = $init->sdBaseHP;
				arg = c[3];
				arg = "(耐久力" + arg + "）";
				strn2 = point + "で" + kind + arg;
		} else if(c[0] == $init->comShipBack){ // 船の破棄
				strn2 = point + "で" + kind;
		} else if(c[0] == $init->comSoukoM){ // 倉庫建設(貯金)
			if(c[3] == 0) {
				arg = "(セキュリティ強化)";
				strn2 = point + "で" + kind + arg;
			} else {
				arg = c[3] * 1000;
				arg = "(" + arg + "{$init->unitMoney})";
				strn2 = point + "で" + kind + arg;
			}
		} else if(c[0] == $init->comSoukoF){ // 倉庫建設(貯食)
			if(c[3] == 0) {
				arg = "(セキュリティ強化)";
				strn2 = point + "で" + kind + arg;
			} else {
				arg = c[3] * 1000;
				arg = "(" + arg + "{$init->unitFood})";
				strn2 = point + "で" + kind + arg;
			}
		} else if(c[0] == $init->comHikidasi) { // 倉庫引き出し
			if(c[3] == 0) c[3] = 1;
			arg = c[3] * 1000;
			arg = "（" + arg + "{$init->unitMoney} or " + arg + "{$init->unitFood}）";
			strn2 = point + "で" + kind + arg;
		} else if(c[0] == $init->comFarm || // 農場、海底農場、工場、商業ビル、採掘場整備、発電所、僕の引越し
			c[0] == $init->comSfarm ||
			c[0] == $init->comFactory ||
			c[0] == $init->comCommerce ||
			c[0] == $init->comMountain ||
			c[0] == $init->comHatuden ||
			c[0] == $init->comBoku) {
			if(c[3] != 0){
				arg = "（" + c[3] + "回）";
				strn2 = point + "で" + kind + arg;
			}else{
				strn2 = point + "で" + kind;
			}
		} else if(c[0] == $init->comPropaganda || // 誘致活動
			c[0] == $init->comOffense || // 強化
			c[0] == $init->comDefense ||
			c[0] == $init->comPractice) {
			if(c[3] != 0){
				arg = "（" + c[3] + "回）";
				strn2 = kind + arg;
			}else{
				strn2 = kind;
			}
		} else if(c[0] == $init->comPlaygame) { // 試合
			strn2 = tgt + "と" + kind;
		} else if(c[0] == $init->comMakeShip){ // 造船
			if(c[3] >= $init->shipKind) {
				c[3] = $init->shipKind - 1;
			}
			arg = c[3];
			strn2 = point + "で" + kind + " (" + shiplist[arg] + ")";
		} else if(c[0] == $init->comSendShip){ // 船派遣
			strn2 = tgt + "へ" + point + "の" + kind;
		} else if(c[0] == $init->comReturnShip){ // 船帰還
			strn2 = tgt + point + "の" + kind;
		} else if(c[0] == $init->comEisei){ // 人工衛星打ち上げ
			if(c[3] >= $init->EiseiNumber) {
				c[3] = 0;
			}
			arg = c[3];
			strn2 = '{$init->tagComName_}' + eiseilist[arg] + "打ち上げ" + '{$init->_tagComName}';
		} else if(c[0] == $init->comEiseimente){ // 人工衛星修復
			if(c[3] >= $init->EiseiNumber) {
				c[3] = 0;
			}
			arg = c[3];
			strn2 = '{$init->tagComName_}' + eiseilist[arg] + "修復" + '{$init->_tagComName}';
		} else if(c[0] == $init->comEiseiAtt){ // 人工衛星破壊
			if(c[3] >= $init->EiseiNumber) {
				c[3] = 0;
			}
			arg = c[3];
			strn2 = tgt + "へ" + '{$init->tagComName_}' + eiseilist[arg] + "破壊砲発射" + '{$init->_tagComName}';
		} else if(c[0] == $init->comEiseiLzr) { // 衛星レーザー
			strn2 = tgt + point + "へ" + kind;
		}else{
			strn2 = point + "で" + kind;
		}
		tmpnum = '';
		if(i < 9){ tmpnum = '0'; }
		strn1 +=
			'<div id="com_'+i+'" '+
				'onmouseover="mc_over('+i+');return false;" '+
				'><a HREF="javascript:void(0);" onclick="ns('+i+')" onkeypress="ns('+i+')" '+
				'onmousedown="return comListMove('+i+');" '+'ondblclick="chNum('+c[3]+');return false;" '+
				'><nobr>'+
				tmpnum+(i+1)+':'+
				strn2+'<\\/nobr><\\/a><\\/div>\\n';
	}

	return strn1;
}

function disp(str,bgclr) {
	if(str==null) {
		str = "";
	}
	LayWrite('LINKMSG1', str);
	SetBG('plan', bgclr);
}

function outp() {
	comary = "";

	for(k = 0; k < command.length; k++){
		comary = comary + command[k][0]
			+ " " + command[k][1]
			+ " " + command[k][2]
			+ " " + command[k][3]
			+ " " + command[k][4]
			+ " " ;
	}
	document.InputPlan.COMARY.value = comary;
}

function ps(x, y) {
	document.InputPlan.POINTX.options[x].selected = true;
	document.InputPlan.POINTY.options[y].selected = true;
	if(!(document.InputPlan.MENUOPEN.checked)) {
		moveLAYER("menu", mx+10, my-50);
	}
	NaviClose();
	return true;
}

function ns(x) {
	if (x == $init->commandMax){
		return true;
	}
	document.InputPlan.NUMBER.options[x].selected = true;
	return true;
}

function set_com(x, y, land) {
	com_str = land + " ";
	for(i = 0; i < $init->commandMax; i++) {
		c = command[i];
		x2 = c[1];
		y2 = c[2];
		if(x == x2 && y == y2 && c[0] < 30){
			com_str += "[" + (i + 1) +"]" ;
			kind = g[i];
			if(c[0] == $init->comDestroy){
				if(c[3] == 0){
					com_str += kind;
				} else {
					arg = c[3] * 200;
					arg = "（予\算" + arg + "{$init->unitMoney}）";
					com_str += kind + arg;
				}
			} else if(c[0] == $init->comLot){
				if(c[3] == 0) c[3] = 1;
				if(c[3] > 30) c[3] = 30;
					arg = c[3] * 300;
					arg = "（予\算" + arg + "{$init->unitMoney}）";
					com_str += kind + arg;
			} else if(c[0] == $init->comFarm ||
				c[0] == $init->comSfarm ||
				c[0] == $init->comFactory ||
				c[0] == $init->comCommerce ||
				c[0] == $init->comMountain ||
				c[0] == $init->comHatuden ||
				c[0] == $init->comBoku ||
				c[0] == $init->comPropaganda ||
				c[0] == $init->comOffense ||
				c[0] == $init->comDefense ||
				c[0] == $init->comPractice) {
				if(c[3] != 0){
					arg = "（" + c[3] + "回）";
					com_str += kind + arg;
				} else {
					com_str += kind;
				}
			} else {
				com_str += kind;
			}
			com_str += " ";
		}
	}
	document.InputPlan.COMSTATUS.value= com_str;
}

function SelectList(theForm) {
	var u, selected_ok;
	if(!theForm) { s = '' }
	else { s = theForm.menu.options[theForm.menu.selectedIndex].value; }
	if(s == ''){
		u = 0; selected_ok = 0;
		document.InputPlan.COMMAND.options.length = $All_listCom;
		for (i=0; i<comlist.length; i++) {
			var command = comlist[i];
			for (a=0; a<command.length; a++) {
				comName = command[a][1] + "(" + command[a][2] + ")";
				document.InputPlan.COMMAND.options[u].value = command[a][0];
				document.InputPlan.COMMAND.options[u].text = comName;
				if(command[a][0] == $default_Kind){
					document.InputPlan.COMMAND.options[u].selected = true;
					selected_ok = 1;
				}
				u++;
			}
		}
		if(selected_ok == 0)
			document.InputPlan.COMMAND.selectedIndex = 0;
	} else {
		var command = comlist[s];
		document.InputPlan.COMMAND.options.length = command.length;
		for (i=0; i<command.length; i++) {
			comName = command[i][1] + "(" + command[i][2] + ")";
			document.InputPlan.COMMAND.options[i].value = command[i][0];
			document.InputPlan.COMMAND.options[i].text = comName;
			if(command[i][0] == $default_Kind){
				document.InputPlan.COMMAND.options[i].selected = true;
				selected_ok = 1;
			}
		}
		if(selected_ok == 0) {
			document.InputPlan.COMMAND.selectedIndex = 0;
		}
	}
}

function moveLAYER(layName,x,y){
	var el = document.getElementById(layName);
	el.style.left = x + "px";
	el.style.top  = y + "px";
}

function menuclose() {
	moveLAYER("menu", -500, -500);
}

function Mmove(e){
	mx = e.pageX;
	my = e.pageY;

	return moveLay.move();
}

function LayWrite(layName, str) {
	document.getElementById(layName).innerHTML = str;
}

function SetBG(layName, bgclr) {
	document.getElementById(layName).style.backgroundColor = bgclr;
}

var oldNum=0;
function selCommand(num) {
	document.getElementById('com_'+oldNum).style.backgroundColor = '';
	document.getElementById('com_'+num).style.backgroundColor = '#FFFFAA';
	oldNum = num;
}

/* コマンド ドラッグ＆ドロップ用追加スクリプト */
var moveLay = new MoveFalse();
var newLnum = -2;
var Mcommand = false;

function Mup() {
	moveLay.up();
	moveLay = new MoveFalse();
}

function setBorder(num, color) {
	if(color.length == 4) {
		document.getElementById('com_'+num).style.borderTop = ' 1px solid '+color;
	} else {
		document.getElementById('com_'+num).style.border = '0px';
	}
}

function mc_out() {
	if(Mcommand && newLnum >= 0) {
		setBorder(newLnum, '');
		newLnum = -1;
	}
}

function mc_over(num) {
	if(Mcommand) {
		if(newLnum >= 0) setBorder(newLnum, '');
		newLnum = num;
		setBorder(newLnum, '#116'); // blue
	}
}

function comListMove(num) {
	moveLay = new MoveComList(num);
	return (document.layers) ? true : false;
}

function MoveFalse() {
	this.move = function() { }
	this.up = function() { }
}

function MoveComList(num) {
	var setLnum = num;
	Mcommand = true;
	LayWrite('mc_div', '<NOBR><strong>'+(num+1)+': '+g[num]+'</strong></NOBR>');
	this.move = function() {
		moveLAYER('mc_div', mx+10, my-30);
		return false;
	}
	this.up = function() {
		if(newLnum >= 0) {
			var com = command[setLnum];
			cominput(document.InputPlan,7,setLnum,newLnum);
		} else if(newLnum == -1) {
			cominput(document.InputPlan,3,setLnum+1);
		}
		mc_out();
		newLnum = -2;
		Mcommand = false;
		moveLAYER("mc_div",-50,-50);
	}
}

function showElement(layName) {
	var element = document.getElementById(layName).style;
	element.display = "block";
	element.visibility ='visible';
}

function hideElement(layName) {
	var element = document.getElementById(layName).style;
	element.display = "none";
}

function chNum(num) {
	document.ch_numForm.AMOUNT.options.length = 100;
	for(var i=0; i<document.ch_numForm.AMOUNT.options.length; i++){
		if(document.ch_numForm.AMOUNT.options[i].value == num){
			document.ch_numForm.AMOUNT.selectedIndex = i;
			document.ch_numForm.AMOUNT.options[i].selected = true;
			moveLAYER('ch_num', mx-10, my-60);
			showElement('ch_num');
			break;
		}
	}
}

function chNumDo() {
	var num = document.ch_numForm.AMOUNT.options[document.ch_numForm.AMOUNT.selectedIndex].value;
	cominput(document.InputPlan,8,num);
	hideElement('ch_num');
}

function Kdown(e){
	var c, el;
	var m = document.InputPlan.AMOUNT.selectedIndex;
	if(m > 9) {
		m = 0;
	}

	if (e.altKey || e.ctrlKey || e.shiftKey) {
		return;
	}
	c = e.which;
	el = new String(e.target.tagName);
	el = el.toUpperCase();
	if (el == "INPUT") {
		return;
	}

	c = String.fromCharCode(c);

	// 押されたキーに応じて計画番号を設定する
	switch (c) {
		case 'A': c = $init->comPrepare; break; // 整地
		case 'J': c = $init->comPrepare2; break; // 地ならし
		case 'U': c = $init->comReclaim; break; // 埋め立て
		case 'K': c = $init->comDestroy; break; // 掘削
		case 'B': c = $init->comSellTree; break; // 伐採
		case 'P': c = $init->comPlant; break; // 植林
		case 'N': c = $init->comFarm; break; // 農場整備
		case 'I': c = $init->comFactory; break; // 工場建設
		case 'S': c = $init->comMountain; break; // 採掘場整備
		case 'D': c = $init->comDbase; break; // 防衛施設建設
		case 'M': c = $init->comBase; break; // ミサイル基地建設
		case 'F': c = $init->comSbase; break; // 海底基地建設
		case '-': c = $init->comDoNothing; break; //INS 資金繰り
		case '.': cominput(InputPlan,3); return; //DEL 削除
		case'\b': //BS 一つ前削除
		var no = document.InputPlan.COMMAND.selectedIndex;
		if(no > 0) {
			document.InputPlan.COMMAND.selectedIndex = no - 1;
		}
		cominput(InputPlan,3);
		return;
		case '0':case'`': document.InputPlan.AMOUNT.selectedIndex = m*10+0; return;
		case '1':case'a': document.InputPlan.AMOUNT.selectedIndex = m*10+1; return;
		case '2':case'b': document.InputPlan.AMOUNT.selectedIndex = m*10+2; return;
		case '3':case'c': document.InputPlan.AMOUNT.selectedIndex = m*10+3; return;
		case '4':case'd': document.InputPlan.AMOUNT.selectedIndex = m*10+4; return;
		case '5':case'e': document.InputPlan.AMOUNT.selectedIndex = m*10+5; return;
		case '6':case'f': document.InputPlan.AMOUNT.selectedIndex = m*10+6; return;
		case '7':case'g': document.InputPlan.AMOUNT.selectedIndex = m*10+7; return;
		case '8':case'h': document.InputPlan.AMOUNT.selectedIndex = m*10+8; return;
		case '9':case'i': document.InputPlan.AMOUNT.selectedIndex = m*10+9; return;
		case 'Z':case'j': document.InputPlan.AMOUNT.selectedIndex = 0; return;
		default:
		// IE ではリロードのための F5 まで拾うので、ここに処理をいれてはいけない
		return;
	}
	cominput(document.InputPlan, 6, c);
}

function settarget(part){
	p = part.options[part.selectedIndex].value;
}

function targetopen() {
	w = window.open("{$GLOBALS['THIS_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}

</script>
END;
		$this->islandInfo($island, $number, 1);
		echo <<<END
<div id="menu" style="position:absolute; top:-500px;left:-500px; overflow:auto;width:360px;height:350px;">
	<table border=0 class="PopupCell" onClick="menuclose()">
		<tr valign=top>
			<td>
				$click_com[0]
				<hr>
				$click_com[1]
			</td>
			<td>
				$click_com[2]
				<hr>
				$click_com[3]
			</td>
		</tr>
		<tr valign=top>
			<td>
				$click_com[4]
				<hr>
				$click_com[5]
			</td>
			<td>
			$click_com[6]
			</td>
		</tr>
	</table>
</div>

<div ID="mc_div" style="position:absolute;top:-50;left:-50;height:22px;">
&nbsp;
</div>

<div ID="ch_num" style="position:absolute;visibility:hidden;display:none">
	<form name="ch_numForm">
		<table class="table table-bordered" bgcolor="#e0ffff" cellspacing=1>
		<tr>
			<td valign=top nowrap>
				<a href="JavaScript:void(0)" onClick="hideElement('ch_num');" style="text-decoration:none"><B>×</B></a><br>
				<select name="AMOUNT" size=13 onchange="chNumDo()"></select>
			</td>
		</tr>
		</table>
	</form>
</div>

<div align="center">
<table class="table table-bordered">
<tr valign="top">
<td $init->bgInputCell>

<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="COMARY" value="comary">
<input type="hidden" name="DEVELOPEMODE" value="javascript">

<div class="text-center">

	<input type="submit" class="btn btn-primary" value="計画送信" name="SENDPROJECT">

	<hr>

	<h3>コマンド入力</h3>
	<ul class="list-inline">
		<li><b><a href="javascript:void(0);" onclick="cominput(InputPlan,1)">挿入</a></b>
		<li><b><a href="javascript:void(0);" onclick="cominput(InputPlan,2)">上書き</a></b>
		<li><b><a href="javascript:void(0);" onclick="cominput(InputPlan,3)">削除</a></b>
	</ul>

	<hr>

	<b>計画番号</b>
	<select name="NUMBER">
END;
		// 計画番号
		for($i = 0; $i < $init->commandMax; $i++) {
			$j = $i + 1;
			echo "<option value=\"$i\">$j</option>\n";
		}

		$open = "";
		if ( isset($data['MENUOPEN']) ) {
			if ($data['MENUOPEN'] == 'on') {
				$open = "CHECKED";
			} else {
				$open = "";
			}
		}

		echo <<<END
	</select>

	<hr>

	<h3>開発計画</h3>
	<label class="checkbox-inline">
	  <input type="checkbox" name="NAVIOFF" $open>NaviOff
	</label>
	<label class="checkbox-inline">
	  <input type="checkbox" name="MENUOPEN" $open>PopupOff<br>
	</label>

	<br>

	<select name="menu" onchange="SelectList(InputPlan)">
	<option value="">全種類</option>
END;
		for($i = 0; $i < $com_count; $i++) {
			list($aa, $tmp) = explode(",", $init->commandDivido[$i], 2);
			echo "<option value=\"$i\">{$aa}</option>\n";
		}
		echo <<<END
	</select>
	<br>
	<select name="COMMAND">
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
		<option>　　　　　　　　　　</option>
	</select>

	<hr>

	<b>座標(</b>
	<select name="POINTX">
END;
		for($i = 0; $i < $init->islandSize; $i++) {
			if (isset($data['defaultX'])){
				if($i == $data['defaultX']) {
					echo "<option value=\"$i\" selected>$i</option>\n";
				} else {
					echo "<option value=\"$i\">$i</option>\n";
				}
			} else {
				echo "<option value=\"$i\">$i</option>\n";
			}
		}
		echo "</select>, <select name=\"POINTY\">\n";
		for($i = 0; $i < $init->islandSize; $i++) {
			if (isset($data['defaultY'])){
				if($i == $data['defaultY']) {
					echo "<option value=\"$i\" selected>$i</option>\n";
				} else {
					echo "<option value=\"$i\">$i</option>\n";
				}
			} else {
				echo "<option value=\"$i\">$i</option>\n";
			}
		}

		echo <<<END
	</select><b> )</b>

	<hr>

	<h3>数量</h3>
	<select name="AMOUNT">
END;
		// 数量
		for($i = 0; $i < 100; $i++) {
			echo "<option value=\"$i\">$i</option>\n";
		}

		// 船舶数
		$ownship = 0;
		for($i = 0; $i < $init->shipKind; $i++) {
			$ownship += $island['ship'][$i];
		}
		echo <<<END
	</select>

	<hr>

	<h3>目標の島</h3>
	<select name="TARGETID" onchange="settarget(this);">$hako->targetList</select>
	<input type="button" value="目標捕捉" onClick="javascript: targetopen();">

	<hr>

	<h3>コマンド移動</h3>
	<ul class="list-inline">
		<li><a href="javascript:void(0);" onclick="cominput(InputPlan,4)" style="text-decoration:none"> ▲ </a></li>
		<li><a href="javascript:void(0);" onclick="cominput(InputPlan,5)" style="text-decoration:none"> ▼ </a></li>
	</ul>

	<hr>

	<input type="hidden" name="ISLANDID" value="{$island['id']}">
	<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
	<input type="submit" class="btn btn-primary" value="計画送信" name="SENDPROJECT">

	<p>最後に<font color="#C7243A">計画送信ボタン</font>を押すのを忘れないように。</p>

</div>
</form>

<ul>
	<li>ミサイル発射上限数[<b> {$island['fire']} </b>]発</li>
	<li>所有船舶数[<b> {$ownship} </b>]隻</li>
</ul>

<p>
<a href="javascript:void(0)" title='数字=数量　BS=一つ前削除
DEL=削除　INS=資金繰り
A=整地　J=地ならし
K=掘削　U=埋め立て
B=伐採　P=植林
N=農場整備　I=工場建設
S=採掘場整備
D=防衛施設建設
M=ミサイル基地建設
F=海底基地建設'>ショートカットキー入力簡易説明</a>
</p>

</td>
<td $init->bgMapCell id="plan" onmouseout="mc_out();return false;">
END;
		$this->islandMap($hako, $island, 1); // 島の地図、所有者モード
		$comment = $hako->islands[$number]['comment'];
		echo <<<END
</td>
<td $init->bgCommandCell id="plan">
<ilayer name="PARENT_LINKMSG" width="100%" height="100%">
<layer name="LINKMSG1" width="200"></layer>
<span id="LINKMSG1"></span>
</ilayer>
<br>
</td>
</tr>
</table>
<hr>
<div id='CommentBox'>
	<h2>コメント更新</h2>
	<form action="{$GLOBALS['THIS_FILE']}" method="post">
		<div class="row">
		  <div class="col-xs-12">
			<div class="input-group">
				<input type="text" name="MESSAGE" class="form-control" size="80" value="{$island['comment']}" placeholder="コメントする">
				<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
				<input type="hidden" name="mode" value="comment">
				<input type="hidden" name="ISLANDID" value="{$island['id']}">
				<input type="hidden" name="DEVELOPEMODE" value="cgi">
			  <span class="input-group-btn">
				<input type="submit" class="btn btn-primary" value="コメント更新">
			  </span>
			</div>
		  </div>
		</div>

	</form>
</div>
END;
	}

	// 関数ダミー【追加】
	static function funcJavaDM() {
		echo <<<END
<script>
function init(){}
function SelectList(theForm){}
</script>
END;
	}
}

class HtmlSetted extends HTML {

	static function comment() {
		global $init;
		Util::makeTagMessage("コメントを更新しました", "success");
	}

	static function change() {
		global $init;
	}

	// コマンド削除
	static function commandDelete() {
		global $init;
		Util::makeTagMessage("コマンドを削除しました", "success");
	}

	// コマンド登録
	static function commandAdd() {
		global $init;
		Util::makeTagMessage("コマンドを登録しました", "success");
	}

	// 島の強制削除
	static function deleteIsland($name) {
		global $init;
		Util::makeTagMessage("{$name}島を強制削除しました", "success");
	}
}
