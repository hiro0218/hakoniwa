<?php

/*******************************************************************

	箱庭諸島 S.E
	
	- 画面出力用ファイル -
	
	hako-html.php by SERA - 2013/05/12

*******************************************************************/

if(GZIP == true) {
	// gzip圧縮転送用
	require_once "HTTP/Compress.php";
	$http = new HTTP_Compress;
}

//--------------------------------------------------------------------
class HTML {
	//---------------------------------------------------
	// HTML ヘッダ出力
	//---------------------------------------------------
	function header($data = "") {
		global $init;
		global $PRODUCT_VERSION;
		
		// 圧縮転送
		if(GZIP == true) {
			global $http;
			$http->start();
		}
		header("X-Product-Version: {$PRODUCT_VERSION}");
		$css = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
		$bimg = (empty($data['defaultImg'])) ? $init->imgDir : $data['defaultImg'];
		
		print <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<base href="{$bimg}/">
<meta http-equiv="Content-type" content="text/html; charset=Shift_JIS">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="{$init->cssDir}/{$css}">
<link rel="shortcut icon" href="{$init->baseDir}/favicon.ico">
<title>{$init->title}</title>
<script type="text/javascript" src="{$init->baseDir}/hako.js"></script>
<script type="text/javascript" src="{$init->baseDir}/auto-filter.js"></script>
<script type="text/javascript" src="{$init->baseDir}/cpick.js"></script>
</head>
<body>

<div id="LinkHeader">
<a href="http://www.bekkoame.ne.jp/~tokuoka/hakoniwa.html">箱庭諸島スクリプト配布元</a>
<a href="http://scrlab.g-7.ne.jp">[PHP]</a>　
[<a href="http://hakoniwa.symphonic-net.com">箱庭諸島S.E配布元</a>]　
[<a href="http://snufkin.jp.land.to">沙布巾の箱庭</a>]　
[<a href="http://www.s90259900.onlinehome.us/">箱庭の箱庭</a>]　
[<a href="http://no-one.s53.xrea.com">The Return of Neptune</a>]　
[<a href="http://minnano.min-ai.net/ocn/">みんなのあいらんど</a>]
<BR>
[<a href="{$init->baseDir}/hako-main.php?mode=conf">島の登録・設定変更</a>]　
[<a href="{$init->baseDir}/hako-ally.php">同盟管理</a>]　
[<a href="{$init->baseDir}/hako-main.php?mode=log">最近の出来事</a>]　
[<a href="{$init->urlManu}" target="_blank">マニュアル</a>]　
[<a href="{$init->urlBbs}" target="_blank">掲示板</a>]　
[<a href="{$init->baseDir}/hako-admin.php">管理人室</a>]
</div>
<hr>
END;
	}
	
	//---------------------------------------------------
	// HTML フッタ出力
	//---------------------------------------------------
	function footer() {
		global $init;
		
		print <<<END
<hr>
<div id="LinkFoot">
管理者：{$init->adminName}(<a href="mailto:{$init->adminEmail}">{$init->adminEmail}</a>)<br>
掲示板：(<a href="{$init->urlBbs}">{$init->urlBbs}</a>)<br>
トップページ：(<a href="{$init->urlTopPage}">{$init->urlTopPage}</a>)
</div>
<br><div align="right">
END;
		if($init->performance) {
			list($tmp1, $tmp2) = split(" ", $init->CPU_start);
			list($tmp3, $tmp4) = split(" ", microtime());
			printf("　<SMALL>(CPU : %.6f秒)</SMALL>", $tmp4-$tmp2+$tmp3-$tmp1);
		}
		print <<<END
</div>
</body>
</html>
END;
		if(GZIP == true) {
			global $http;
			$http->output();
		}
	}
	
	//---------------------------------------------------
	// 最終更新時刻 ＋ 次ターン更新時刻出力
	//---------------------------------------------------
	function lastModified($hako) {
		global $init;
		
		$timeString = date("Y年m月d日　H時", $hako->islandLastTime);
		print <<<END
<div class="lastModified">最終更新時間 : $timeString
<span style="font-weight: normal;">
(次のターンまで、あと
<script type="text/javascript">
<!--
var nextTime = $hako->islandLastTime + $init->unitTime;
remainTime(nextTime);
//-->
</script>
</span>
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
		
		// 最終更新時刻 ＋ 次ターン更新時刻出力
		$this->lastModified($hako);
		$allyfile = $init->baseDir . "/hako-ally.php";
		if(empty($data['defaultDevelopeMode']) || $data['defaultDevelopeMode'] == "cgi") {
			$radio = "checked"; $radio2 = "";
		} else {
			$radio = ""; $radio2 = "checked";
		}
		print "<h1 class=\"title\">{$init->title}</h1>\n";
		
		if(DEBUG == true) {
			print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="mode" value="debugTurn">
<input type="submit" value="ターンを進める">
</form>
END;
		}
		print <<<END
<div class='Turn'>ターン$hako->islandTurn</div>
<hr>
<TABLE BORDER=0 width="100%">
<TR valign="top">
<TD width="50%" class="M">
<div id="MyIsland">
<h2>自分の島へ</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
あなたの島の名前は？<br>
<select name="ISLANDID">
$hako->islandList
</select>
<br>
パスワードをどうぞ！！<br>
<input type="password" name="PASSWORD" value="{$data['defaultPassword']}" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="owner">
<input type="radio" name="DEVELOPEMODE" value="cgi" id="cgi" $radio><label for="cgi">通常モード</label>
<input type="radio" name="DEVELOPEMODE" value="java" id="java" $radio2><label for="java">Javaスクリプトモード</label><BR>
<input type="submit" value="開発しに行く">
</form>
</div>

</TD>
<TD width="50%" class="M">
END;
		$this->infoPrint(); // 「お知らせ」を表示
		// $this->historyPrint(); // 「発見の記録」を表示
		
		print <<<END
</TD></TR></TABLE>
<hr>
<h2>各部門ランキング</h2>
<table width="90%">
END;
		$element = array('point', 'money', 'food', 'pop', 'area', 'fire', 'pots', 'gold', 'rice', 'peop', 'monster', 'taiji', 'farm', 'factory', 'commerce', 'hatuden', 'mountain', 'team');
		$bumonName = array("総合ポイント", "資金", "食料", "人口", "面積", "軍事力", "成長", "収入", "収穫", "人口増加", "怪獣出現数", "怪獣退治数", "農場", "工場", "商業", "発電所", "採掘場", "サッカー");
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
					print "<tr>\n";
				}
				print "<td width=\"15%\" class=\"M\"><table width=\"100%\">\n";
				print "<tr><th {$init->bgTitleCell}>{$init->tagTH_}{$bumonName[$r]}{$init->_tagTH}</th></tr>\n";
				print "<tr><td class=\"TenkiCell\">{$init->tagName_}-{$init->_tagName}</a></td></tr>\n";
				print "<tr><td class=\"TenkiCell\">-</td></tr>\n";
				print "</table></td>\n";
				if(($r % 6) == 5) {
					print "</tr>\n";
				}
			} else {
				if($r == 5) {
					$max = "";
				}
				if(($r % 6) == 0) {
					print "<tr>\n";
				}
				$island = $hako->islands[$rankid[$r]];
				$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
				print "<td width=\"15%\" class=\"M\"><table width=\"100%\">\n";
				print "<tr><th {$init->bgTitleCell}>{$init->tagTH_}{$bumonName[$r]}{$init->_tagTH}</th></tr>\n";
				print "<tr><td class=\"TenkiCell\"><a href=\"{$GLOBALS['THIS_FILE']}?Sight={$island['id']}\">{$init->tagName_}{$name}{$init->_tagName}</a></td></tr>\n";
				print "<tr><td class=\"TenkiCell\">{$max}{$bumonUnit[$r]}</td></tr>\n";
				print "</table></td>\n";
				if(($r % 6) == 5) {
					print "</tr>\n";
				}
			}
		}
		print "</table>\n";
		print "<BR>\n";
		
		if($hako->allyNumber) {
			print <<<END
<hr>
<div id="IslandView">
<h2>同盟の状況</h2>
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}順位{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}同盟{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}マーク{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}島の数{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}総人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}占有率{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}農場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}工場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}商業規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}採掘場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}発電所規模{$init->_tagTH}</th>
</tr>
END;
			for($i=0; $i<$hako->allyNumber; $i++) {
				if($num && ($i != $hako->idToAllyNumber[$num])) {
					continue;
				}
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
				$name = ($num) ? "{$init->tagName_}{$ally['name']}{$init->_tagName}" : "<a href=\"{$allyfile}?AmiOfAlly={$ally['id']}\">{$ally['name']}</a>";
				$pop = $pop . $init->unitPop;
				$farm = ($farm <= 0) ? "保有せず" : $farm * 10 . $init->unitPop;
				$factory = ($factory <= 0) ? "保有せず" : $factory * 10 . $init->unitPop;
				$commerce = ($commerce <= 0) ? "保有せず" : $commerce * 10 . $init->unitPop;
				$mountain = ($mountain <= 0) ? "保有せず" : $mountain * 10 . $init->unitPop;
				$hatuden = ($hatuden <= 0) ? "0kw" : $hatuden * 1000 . kw;
				
				print <<<END
<tr>
<th {$init->bgNumberCell} rowspan=2>{$init->tagNumber_}$j{$init->_tagNumber}</th>
<td {$init->bgNameCell} rowspan=2>{$name}</td>
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
			print "</table>\n";
		}
		print "<BR>\n";
		print "<hr>\n";
		print "<div ID=\"IslandView\">\n";
		print "<h2>諸島の状況</h2>\n";
		
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
		print "<p>\n";
		print "島の名前をクリックすると、<strong>観光</strong>することができます。\n";
		print "</p>\n";
		
		if (($islandListStart != 1) || ($islandListSentinel != $hako->islandNumberNoBF)) {
			for ($i = 1; $i <= $hako->islandNumberNoBF ; $i += $init->islandListRange) {
				$j = $i + $init->islandListRange - 1;
				if ($j > $hako->islandNumberNoBF) {
					$j = $hako->islandNumberNoBF;
				}
				print " ";
				if ( $i != $islandListStart ) {
					print "<a href=\"" . $GLOBALS['THIS_FILE'] . "?islandListStart=" . $i ."\">";
				}
				print " [ ". $i . " - " . $j . " ]";
				
				if ($i != $islandListStart) {
					print "</a>";
				}
			}
		}
		$islandListStart--;
		$this->islandTable($hako, $islandListStart, $islandListSentinel);
		
		print "<hr>\n\n";
		print "<div ID=\"IslandView\">\n";
		print "<h2>Battle Fieldの状況</h2>\n";
		
		$this->islandTable($hako, $hako->islandNumberNoBF, $hako->islandNumber);
		
		print "<hr>\n";
		
		$this->historyPrint();
		
		if($init->registMode) {
			print <<<END
<FORM action="{$GLOBALS['THIS_FILE']}?mode=conf" method="POST">
<DIV align="right">
<INPUT TYPE="password" NAME="PASSWORD" SIZE=8 MAXLENGTH=32>
<INPUT TYPE="submit" VALUE="管理用" NAME="AdminButton">
</DIV>
</FORM>
END;
		}
	}
	
	//---------------------------------------------------
	// 島の一覧表を表示
	//---------------------------------------------------
	function islandTable(&$hako, $start, $sentinel) {
		global $init;
		
		print "<table border=\"1\">\n";
		
		for($i = $start; $i < $sentinel ; $i++) {
			$island       = $hako->islands[$i];
			$j            = ($island['isBF']) ? '★' : $i + 1;
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
			$unemployed   = '<font color="' . ($unemployed < 0 ? 'black' : 'red') . '">' . sprintf("%-3d%%", $unemployed) . '</font>';
			$farm         = ($island['farm'] <= 0) ? "保有せず" : $island['farm'] * 10 . $init->unitPop;
			$factory      = ($island['factory'] <= 0) ? "保有せず" : $island['factory'] * 10 . $init->unitPop;
			$commerce     = ($island['commerce'] <= 0) ? "保有せず" : $island['commerce'] * 10 . $init->unitPop;
			$mountain     = ($island['mountain'] <= 0) ? "保有せず" : $island['mountain'] * 10 . $init->unitPop;
			$hatuden      = ($island['hatuden'] <= 0) ? "保有せず" : $island['hatuden'] * 10 . $init->unitPop;
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
			
			if($init->commentNew > 0 && ($comment_turn + $init->commentNew) > $hako->islandTurn) {
				$comment .= " <span class=\"new\">New</span>";
			}
			
			$sora = "";
			if($tenki == 1) {
				$sora .= "<IMG SRC=\"tenki1.gif\" ALT=\"晴れ\" title=\"晴れ\">";
			} elseif($tenki == 2) {
				$sora .= "<IMG SRC=\"tenki2.gif\" ALT=\"曇り\" title=\"曇り\">";
			} elseif($tenki == 3) {
				$sora .= "<IMG SRC=\"tenki3.gif\" ALT=\"雨\" title=\"雨\">";
			} elseif($tenki == 4) {
				$sora .= "<IMG SRC=\"tenki4.gif\" ALT=\"雷\" title=\"雷\">";
			} else {
				$sora .= "<IMG SRC=\"tenki5.gif\" ALT=\"雪\" title=\"雪\">";
			}
			
			$eiseis = "";
			for($e = 0; $e < $init->EiseiNumber; $e++) {
				if($eisei[$e] > 0) {
					$eiseis .= "<img src=\"eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eisei[$e]}%\" title=\"{$init->EiseiName[$e]} {$eisei[$e]}%\"> ";
				} else {
					$eiseis .= "　";
				}
			}
			
			$zins = "";
			for($z = 0; $z < $init->ZinNumber; $z++) {
				if($zin[$z] > 0) {
					$zins .= "<img src=\"zin{$z}.gif\" alt=\"{$init->ZinName[$z]}\" title=\"{$init->ZinName[$z]}\"> ";
				} else {
					$zins .= "";
				}
			}
			
			$items = "";
			for($t = 0; $t < $init->ItemNumber; $t++) {
				if($item[$t] > 0) {
					if($t == 20) {
						$items .= "<img src=\"item{$t}.png\" alt=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\"  title=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\"> ";
					} else {
						$items .= "<img src=\"item{$t}.png\" alt=\"{$init->ItemName[$t]}\" title=\"{$init->ItemName[$t]}\"> ";
					}
				} else {
					$items .= "";
				}
			}
			
			$lots = "";
			if($lot > 0) {
				$lots .= " <IMG SRC=\"lot.png\" ALT=\"{$lot}枚\" title=\"{$lot}枚\">";
			}
			
			$viking = "";
			for($v = 10; $v < 15; $v++) {
				if($island['ship'][$v] > 0) {
					$viking .= " <IMG SRC=\"ship{$v}.gif\" width=\"16\" height=\"16\" ALT=\"{$init->shipName[$v]}出現中\" title=\"{$init->shipName[$v]}出現中\">";
				}
			}
			
			$start = "";
			if(($hako->islandTurn - $island['starturn']) < $init->noAssist) {
				$start .= " <IMG SRC=\"start.gif\" width=\"16\" height=\"16\" ALT=\"初心者マーク\" title=\"初心者マーク\">";
			}
			
			$soccer = "";
			if($island['soccer'] > 0) {
				$soccer .= " <IMG SRC=\"soccer.gif\" width=\"16\" height=\"16\" ALT=\"総合ポイント：{$team}　{$shiai}戦{$kachi}勝{$make}敗{$hikiwake}分　攻撃力：{$kougeki}　守備力：{$bougyo}　得点：{$tokuten}　失点：{$shitten}\" title=\"総合ポイント：{$team}　{$shiai}戦{$kachi}勝{$make}敗{$hikiwake}分　攻撃力：{$kougeki}　守備力：{$bougyo}　得点：{$tokuten}　失点：{$shitten}\">";
			}
			
			// 電力消費量
			$enesyouhi = round($island['pop'] / 100 + $island['factory'] * 2/3 + $island['commerce'] * 1/3 + $island['mountain'] * 1/4);
			if($enesyouhi == 0) {
				$ene = "電力消費なし";
			} elseif($island['hatuden'] == 0) {
				$ene =  "<font color=\"#ff0000\">0%</font>";
			} else {
				// 電力供給率
				$ene = round($island['hatuden'] / $enesyouhi * 100);
				if($ene < 100) {
					// 供給電力不足
					$ene = "<font color=\"#ff0000\">{$ene}%</font>";
				} else {
					// 供給電力充分
					$ene = "{$ene}%";
				}
			}
			print <<<END
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}順位{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}島{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}得点{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}面積{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}天気{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}資金{$init->_tagTH}{$lots}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}食料{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}失業率{$init->_tagTH}</th>
</tr>
END;
			print "<tr>\n";
			print "<th {$init->bgNumberCell} rowspan=\"5\">{$init->tagNumber_}$j{$init->_tagNumber}</th>\n";
			print "<td {$init->bgNameCell} rowspan=\"5\">{$keep}<br>\n<a href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$name}</a>{$start}{$monster}{$soccer}<br>\n{$prize}{$viking}<br>\n{$zins}<br>\n<font size=\"-1\">({$peop} {$okane} {$gohan} {$poin})</font></td>\n";
			print "<td {$init->bgInfoCell}>$point</td>\n";
			print "<td {$init->bgInfoCell}>$pop</td>\n";
			print "<td {$init->bgInfoCell}>$area</td>\n";
			print "<td class=\"TenkiCell\">$sora</td>\n";
			print "<td {$init->bgInfoCell}>$money</td>\n";
			print "<td {$init->bgInfoCell}>$food</td>\n";
			print "<td {$init->bgInfoCell}>$unemployed</td>\n";
			print "</tr>\n";
			print "<tr>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}農場規模{$init->_tagTH}</th>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}工場規模{$init->_tagTH}</th>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}商業規模{$init->_tagTH}</th>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}採掘場規模{$init->_tagTH}</th>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}発電所規模{$init->_tagTH}</th>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}電力供給率{$init->_tagTH}</th>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}人工衛星{$init->_tagTH}</th>\n";
			print "</tr>\n";
			print "<tr>\n";
			print "<td {$init->bgInfoCell}>$farm</td>\n";
			print "<td {$init->bgInfoCell}>$factory</td>\n";
			print "<td {$init->bgInfoCell}>$commerce</td>\n";
			print "<td {$init->bgInfoCell}>$mountain</td>\n";
			print "<td {$init->bgInfoCell}>{$hatuden}</td>\n";
			print "<td {$init->bgInfoCell}>$ene</td>\n";
			print "<td class=\"ItemCell\">$eiseis</td>\n";
			print "</tr>\n";
			print "<tr>\n";
			print "<th {$init->bgTitleCell}>{$init->tagTH_}取得アイテム{$init->_tagTH}</th>\n";
			print "<td class=\"ItemCell\" colspan=\"6\">　$items</td>\n";
			print "</tr>\n";
			print "<tr>\n";
			print "<td {$init->bgCommentCell} colspan=\"7\">{$init->tagTH_}{$owner}：{$init->_tagTH}$comment</td>\n";
			print "</tr>\n";
		}
		print "</table>\n</div>\n";
	}
	
	//---------------------------------------------------
	// 島の登録と設定
	//---------------------------------------------------
	function regist(&$hako, $data = "") {
		global $init;
		
		print "<center>{$GLOBALS['BACK_TO_TOP']}</center>";
		$this->newDiscovery($hako->islandNumber);
		$this->changeIslandInfo($hako->islandList);
		$this->changeOwnerName($hako->islandList);
		$this->setStyleSheet();
		$this->setLocalImage($data);
	}
	
	//---------------------------------------------------
	// 新しい島を探す
	//---------------------------------------------------
	function newDiscovery($number) {
		global $init;
		
		print "<div id=\"NewIsland\">\n";
		print "<h2>新しい島を探す</h2>\n";
		if($number < $init->maxIsland) {
			if($init->registMode == 1 && $init->adminMode == 0) {
				print "当箱庭では不適当な島名などの事前チェックを行っています。<BR>\n";
				print "参加希望の方は、管理者に「島名」と「パスワード」を送信してください。<BR>\n";
			} else {
				print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
どんな名前をつける予定？<br>
<input type="text" name="ISLANDNAME" size="32" maxlength="32">島<br>
あなたのお名前は？(省略可)<br>
<input type="text" name="OWNERNAME" size="32" maxlength="32"><br>
パスワードは？<br>
<input type="password" name="PASSWORD" size="32" maxlength="32"><br>
念のためパスワードをもう一回<br>
<input type="password" name="PASSWORD2" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="new">
<input type="submit" value="探しに行く">
</form>
END;
			}
		} else {
			print "島の数が最大数です・・・現在登録できません。\n";
		}
		print "</div>\n";
		print "<hr>\n";
	}
	
	//---------------------------------------------------
	// 島の名前とパスワードの変更
	//---------------------------------------------------
	function changeIslandInfo($islandList = "") {
		global $init;
		
		print <<<END
<div id="ChangeInfo">
<h2>島の名前とパスワードの変更</h2>
<p>
(注意)名前の変更には500億円かかります。
</p>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
どの島ですか？<br>
<select NAME="ISLANDID">
$islandList
</select>
<br>
どんな名前に変えますか？(変更する場合のみ)<br>
<input type="text" name="ISLANDNAME" size="32" maxlength="32">島<br>
パスワードは？(必須)<br>
<input type="password" name="OLDPASS" size="32" maxlength="32"><br>
新しいパスワードは？(変更する時のみ)<br>
<input type="password" name="PASSWORD" size="32" maxlength="32"><br>
念のためパスワードをもう一回(変更する時のみ)<br>
<input type="password" name="PASSWORD2" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="change">
<input type="submit" value="変更する">
</form>
</div>
<hr>
END;
	}
	
	//---------------------------------------------------
	// オーナー名の変更
	//---------------------------------------------------
	function changeOwnerName($islandList = "") {
		global $init;
		
		print <<<END
<div id="ChangeOwnerName">
<h2>オーナー名の変更</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
どの島ですか？<br>
<select name="ISLANDID">
{$islandList}
</select>
<br>
新しいオーナー名は？<br>
<input type="text" name="OWNERNAME" size="32" maxlength="32"><br>
パスワードは？<br>
<input type="password" name="OLDPASS" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="ChangeOwnerName">
<input type="submit" value="変更する">
</form>
</div>
END;
	}
	
	//---------------------------------------------------
	// スタイルシートの設定
	//---------------------------------------------------
	function setStyleSheet() {
		global $init;
		
		$styleSheet;
		for($i = 0; $i < count($init->cssList); $i++) {
			$styleSheet .= "<option value=\"{$init->cssList[$i]}\">{$init->cssList[$i]}</option>\n";
		}
		print <<<END
<div id="HakoSkin">
<h2>スタイルシートの設定</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<select name="SKIN">
$styleSheet
</select>
<input type="hidden" name="mode" value="skin">
<input type="submit" value="設定">
</form>
</div>
<hr>

END;
	}
	
	//---------------------------------------------------
	// 画像のローカル設定
	//---------------------------------------------------
	function setLocalImage($data = "") {
		global $init;
		
		$Himgflag;
		if(empty($data['defaultImg']) || (strcmp($data['defaultImg'], $init->imgDir) == 0)){
			$Himgflag = '<span class=attention>未設定</span>';
		} else {
			$Himgflag = $data['defaultImg'];
		}
		print <<<END
<div id="localImage">
<h2>画像のローカル設定</h2>
<table border width=50%><tr><td class='N'>
　画像転送によるサーバーへの負荷を軽減するだけでなく、あなたのパソコンにある画像を呼び出すので、表示スピードが飛躍的にアップします。<br>
　画像は<B><a href="{$init->imgPack}">ここ</a></B>からダウンロードして、１つのフォルダに解凍し、下の設定で「land0.gif」を指定して下さい。<br>
　詳しくは<B><a href="{$init->imgExp}">説明のページ</a></B>をご覧下さい。
</td></tr></table>
<table border=0 width=50%><tr><td class="M">
現在の設定<B>[</B> ${Himgflag} <B>]</B>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type=file name="IMGLINE">
<input type="hidden" name="mode" value="imgset">
<input type="submit" value="設定">
</form>

<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type=hidden name="IMGLINE" value="delete">
<input type="hidden" name="mode" value="imgset">
<input type="submit" value="設定を解除する">
</form>
</td></tr></table>
</div>
<hr>

END;
	}
	
	//---------------------------------------------------
	// 最近の出来事
	//---------------------------------------------------
	function log() {
		global $init;
		
		print "<center>{$GLOBALS['BACK_TO_TOP']}</center>";
		print "<div id=\"RecentlyLog\">\n";
		print "<h2>最近の出来事</h2>\n";
		for($i = 0; $i < $init->logTopTurn; $i++) {
			LogIO::logFilePrint($i, 0, 0);
		}
		print "</div>\n";
	}
	
	//---------------------------------------------------
	// 発見の記録
	//---------------------------------------------------
	function historyPrint() {
		print "<div id=\"HistoryLog\">\n";
		print "<h2>発見の記録</h2>";
		LogIO::historyPrint();
		print "</div>\n";
	}
	
	//---------------------------------------------------
	// お知らせ
	//---------------------------------------------------
	function infoPrint() {
		global $init;
		
		print "<div id=\"HistoryLog\">\n";
		print "<h2>お知らせ</h2>\n";
		print "<DIV style=\"overflow:auto; height:{$init->divHeight}px;\">\n";
		LogIO::infoPrint();
		print "</div></div>\n";
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
		if(((empty($data['defaultImg'])) || ($data['defaultImg'] == $init->imgDir)) && ($init->setImg)) {
			Error::emptyImg();
			return;
		}
		$this->tempOwer($hako, $data, $number);
		
		// IP情報取得
		$logfile = "{$init->dirName}/{$init->logname}";
		$ax = $init->axesmax - 1;
		$log = file($logfile);
		$fp = fopen($logfile,"w"); 
		$timedata =date ("Y年m月d日(D) H時i分s秒");
		$islandID = "{$data['ISLANDID']}";
		$name = "{$island['name']}島";
		$ip = getenv("REMOTE_ADDR");
		$host = gethostbyaddr(getenv("REMOTE_ADDR"));
		fputs($fp,$timedata.",".$islandID.",".$name.",".$ip.",".$host."\n");
		for($i=0; $i<$ax; $i++) fputs($fp,$log[$i]);
		fclose($fp);
		
		if($init->useBbs) {
			print "<div id=\"localBBS\">\n";
			$this->lbbsHead($island);
			$this->lbbsInputOW($island, $data);
			$this->lbbsContents($hako, $island, 1);
			print "</div>\n";
		}
		$this->islandRecent($island, 1);
	}
	
	//---------------------------------------------------
	// 観光画面
	//---------------------------------------------------
	function visitor($hako, $data) {
		global $init;
		
		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		$island = $hako->islands[$number];
		$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
		
		print <<<END
<div align="center">
{$init->tagBig_}{$init->tagName_}「{$name}」{$init->_tagName}へようこそ！！{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>
END;
		$this->islandInfo($island, $number, 0);
		$this->islandMap($hako, $island, 0);
		
		// 他の島へ
		print <<<END
<div align="center"><form action="{$GLOBALS['THIS_FILE']}" method="get">
<select name="Sight">$hako->islandList</select><input type="submit" value="観光">
</form></div>
END;
		if($init->useBbs) {
			print "<div id=\"localBBS\">\n";
			$this->lbbsHead($island);
			$this->lbbsInput($hako, $island, $data);
			$this->lbbsContents($hako, $island, 0);
			print "</div>\n";
		}
		$this->islandRecent($island, 0);
	}
	
	//---------------------------------------------------
	// 島の情報
	//---------------------------------------------------
	function islandInfo($island, $number = 0, $mode = 0) {
		global $init;
		
		$rank       = ($island['isBF']) ? '★' : $number + 1;
		$pop        = $island['pop'] . $init->unitPop;
		$area       = $island['area'] . $init->unitArea;
		$eisei      = $island['eisei'];
		$zin        = $island['zin'];
		$item       = $island['item'];
		$money      = ($mode == 0) ? Util::aboutMoney($island['money']) : "{$island['money']}{$init->unitMoney}";
		$lot        = $island['lot'];
		$food       = $island['food'] . $init->unitFood;
		$unemployed = ($island['pop'] - ($island['farm'] + $island['factory'] + $island['commerce'] + $island['mountain'] + $island['hatuden']) * 10) / $island['pop'] * 100;
		$unemployed = '<font color="' . ($unemployed < 0 ? 'black' : 'red') . '">' . sprintf("%-3d%%", $unemployed) . '</font>';
		$farm       = ($island['farm'] <= 0) ? "保有せず" : $island['farm'] * 10 . $init->unitPop;
		$factory    = ($island['factory'] <= 0) ? "保有せず" : $island['factory'] * 10 . $init->unitPop;
		$commerce   = ($island['commerce'] <= 0) ? "保有せず" : $island['commerce'] * 10 . $init->unitPop;
		$mountain   = ($island['mountain'] <= 0) ? "保有せず" : $island['mountain'] * 10 . $init->unitPop;
		$hatuden    = ($island['hatuden'] <= 0) ? "保有せず" : $island['hatuden'] * 10 . $init->unitPop;
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
			$sora .= "<IMG SRC=\"tenki1.gif\" ALT=\"晴れ\" title=\"晴れ\">";
		} elseif($tenki == 2) {
			$sora .= "<IMG SRC=\"tenki2.gif\" ALT=\"曇り\" title=\"曇り\">";
		} elseif($tenki == 3) {
			$sora .= "<IMG SRC=\"tenki3.gif\" ALT=\"雨\" title=\"雨\">";
		} elseif($tenki == 4) {
			$sora .= "<IMG SRC=\"tenki4.gif\" ALT=\"雷\" title=\"雷\">";
		} else {
			$sora .= "<IMG SRC=\"tenki5.gif\" ALT=\"雪\" title=\"雪\">";
		}
		
		$eiseis = "";
		for($e = 0; $e < $init->EiseiNumber; $e++) {
		$eiseip = "";
			if($eisei[$e] > 0) {
				$eiseip .= $eisei[$e];
				$eiseis .= "<img src=\"eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eiseip}%\" title=\"{$init->EiseiName[$e]} {$eiseip}%\"> ({$eiseip}%)";
			} else {
				$eiseis .= "";
			}
		}
		
		$zins = "";
		for($z = 0; $z < $init->ZinNumber; $z++) {
			if($zin[$z] > 0) {
				$zins .= "<img src=\"zin{$z}.gif\" alt=\"{$init->ZinName[$z]}\" title=\"{$init->ZinName[$z]}\"> ";
			} else {
				$zins .= "";
			}
		}
		
		$items = "";
		for($t = 0; $t < $init->ItemNumber; $t++) {
			if($item[$t] > 0) {
				if($t == 20) {
					$items .= "<img src=\"item{$t}.png\" alt=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\" title=\"{$init->ItemName[$t]} {$item[$t]}{$init->unitTree}\"> ";
				} else {
					$items .= "<img src=\"item{$t}.png\" alt=\"{$init->ItemName[$t]}\" title=\"{$init->ItemName[$t]}\"> ";
				}
			} else {
				$items .= "";
			}
		}
		$lots = "";
		if($lot > 0) {
			$lots .= " <IMG SRC=\"lot.png\" ALT=\"{$lot}枚\" title=\"{$lot}枚\">";
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
			$ene =  "<font color=\"#ff0000\">0%</font>";
		} else {
			// 電力供給率
			$ene = round($island['hatuden'] / $enesyouhi * 100);
			if($ene < 100) {
				// 供給電力不足
				$ene = "<font color=\"#ff0000\">{$ene}%</font>";
			} else {
				// 供給電力充分
				$ene = "{$ene}%";
			}
		}
		print <<<END
<div id="islandInfo">
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}順位{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}面積{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}資金{$init->_tagTH}{$lots}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}食料{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}失業率{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}農場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}工場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}商業規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}採掘場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}発電所規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}電力供給率{$init->_tagTH}</th>
</tr>
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
<th {$init->bgTitleCell}>{$init->tagTH_}天気{$init->_tagTH}</th>
<td class="TenkiCell">$sora</td>
<th {$init->bgTitleCell}>{$init->tagTH_}軍事技術{$init->_tagTH}</th>
<td {$init->bgInfoCell}>{$arm}</td>
<th {$init->bgTitleCell}>{$init->tagTH_}怪獣退治数{$init->_tagTH}</th>
<td {$init->bgInfoCell}>$taiji</td>
<th {$init->bgTitleCell}>{$init->tagTH_}人工衛星{$init->_tagTH}</th>
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
		
		// 増減情報
		$peop       = ($island['peop'] < 0) ? "{$island['peop']}{$init->unitPop}" : "+{$island['peop']}{$init->unitPop}";
		$okane      = ($island['gold'] < 0) ? "{$island['gold']}{$init->unitMoney}" : "+{$island['gold']}{$init->unitMoney}";
		$gohan      = ($island['rice'] < 0) ? "{$island['rice']}{$init->unitFood}" : "+{$island['rice']}{$init->unitFood}";
		$poin       = ($island['pots'] < 0) ? "{$island['pots']}pts" : "+{$island['pots']}pts";
		
		if($mode == 1) {
			for($i = 0; $i < $init->commandMax; $i++) {
				$j = $i + 1;
				$com = $command[$i];
				if($com['kind'] < 51) {
					$comStr[$com['x']][$com['y']] .=
						"[{$j}]{$init->comName[$com['kind']]} ";
				}
			}
		}
		
		print "<div id=\"islandMap\" align=\"center\"><table border=\"1\"><tr><td>\n";
		for($y = 0; $y < $init->islandSize; $y++) {
			if($y % 2 == 0) { print "<img src=\"land0.gif\" width=\"16\" height=\"32\" alt=\"{$y}\" title=\"{$y}\">"; }
			for($x = 0; $x < $init->islandSize; $x++) {
				$hako->landString($land[$x][$y], $landValue[$x][$y], $x, $y, $mode, $comStr[$x][$y]);
			}
			if($y % 2 == 1) { print "<img src=\"land0.gif\" width=\"16\" height=\"32\" alt=\"{$y}\" title=\"{$y}\">"; }
			print "<br>";
		}
		print "<div id=\"NaviView\"></div>";
		print "</td></tr></table></div>\n";
		print "<center>\n";
		print "<p>開始ターン：{$island['starturn']} ({$peop} {$okane} {$gohan} {$poin})</p>\n";
		
		if($island['soccer'] > 0) {
			print <<<END
<table border="1">
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
		print "</center>\n";
	}
	
	//---------------------------------------------------
	// 観光者通信
	//---------------------------------------------------
	function lbbsHead($island) {
		global $init;
		
		print <<<END
<hr>
<h2>{$island['name']}島{$init->_tagName}観光者通信</h2>
END;
	}
	
	//---------------------------------------------------
	// 観光者通信 入力部分
	//---------------------------------------------------
	function lbbsInput($hako, $island, $data) {
		global $init;
		
		$lbbsAtention = '';
		if($init->lbbsMoneyPublic + $init->lbbsMoneySecret > 0) {
			// 発言は有料
			$lbbsAtention .= "<CENTER><B>※</B>";
			if($init->lbbsMoneyPublic > 0){
				$lbbsAtention .= "公開通信は<B>{$init->lbbsMoneyPublic}{$init->unitMoney}</B>です。";
			}
			if($init->lbbsMoneySecret > 0){
				$lbbsAtention .= "極秘通信は<B>{$init->lbbsMoneySecret}{$init->unitMoney}</B>です。";
			}
			$lbbsAtention .= "</CENTER>";
		}
		$lbbsAnonny = '';
		if ($init->lbbsAnon){
			$lbbsAnonny .= "<input type=\"radio\" name=\"LBBSTYPE\" value=\"ANON\">観光客";
		} else {
			$col2 = " colspan=2";
			$col3 = " colspan=3";
		}
		print <<<END
<div align="center">
<form action="{$GLOBALS['THIS_FILE']}" method="post">
{$lbbsAtention}<B>※</B>島を持っている方は島名がコメントのあとにつきます。
<table border="1">
<tr>
<th>名前</th>
<th{$col3}>内容</th>
<th>カラー</th>
</tr>
<tr class="lbbsCell">
<td><input type="text" size="32" maxlength="32" name="LBBSNAME" value="{$data['defaultName']}" style="width:100%"></td>
<td{$col3}><input type="text" size="80" name="LBBSMESSAGE" style="width:100%"></td>
<td><input type="text" name="LBBSCOLOR" value="{$data['defaultColor']}" class="html5jp-cpick [coloring:true]" onFocus="this.blur();"></td>
</tr>
<tr>
<th>島名</th>
<th>パスワード</th>
<th>通信方法</th>
<th{$col2}>動作</th>
</tr>
<tr>
<td>
<select name="ISLANDID2">{$hako->islandList}</select>
{$lbbsAnonny}
</td>
<td><input type=password size="16" maxlength="16" name=PASSWORD value="{$data['defaultPassword']}"></td>
<td>
<input type="radio" name="LBBSTYPE" value="PUBLIC" checked>公開
<input type="radio" name="LBBSTYPE" value="SECRET"><font color="red">極秘</font>
</td>
<td>
<input type="hidden" name="mode" value="lbbs">
<input type="hidden" name="lbbsMode" value="0">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="記帳する"></TD>
END;
		if ($init->lbbsAnon == 0){
			print <<<END
<td align="right">
番号
<select name="NUMBER">
END;
			// 発言番号
			for($i = 0; $i < $init->lbbsMax; $i++) {
				$j = $i + 1;
				print "<option value=\"{$i}\">{$j}</option>\n";
			}
			print <<<END
</select>
<input type="submit" name="DEL" value="削除する">
</td>
END;
		}
		print <<<END
</tr>
</table>
</form>
</div>
END;
	}
	
	//---------------------------------------------------
	// 観光者通信 入力部分 オーナ用
	//---------------------------------------------------
	function lbbsInputOW($island, $data) {
		global $init;
		
		print <<<END
<div align="center">
<table border="1">
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<tr>
<th>名前</th>
<th colspan="2">内容</th>
</tr>
<tr>
<td><input type="text" size="32" maxlength="32" name="LBBSNAME" VALUE="{$data['defaultName']}" style="width:100%"></TD>
<td colspan="2"><input type="text" size="80" name="LBBSMESSAGE" style="width:100%"></td>
</tr>
<tr>
<th>カラー</th>
<th colspan="2">動作</th>
</tr>
<tr class="lbbsCell">
<td><input type="text" name="LBBSCOLOR" value="{$data['defaultColor']}" class="html5jp-cpick [coloring:true]" onFocus="this.blur();"></td>
<td align="center">
<input type="hidden" name="mode" value="lbbs">
<input type="hidden" name="lbbsMode" value="1">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="記帳する">
</td>
<td align="right">
番号
<select name="NUMBER">
END;
		// 発言番号
		for($i = 0; $i < $init->lbbsMax; $i++) {
			$j = $i + 1;
			print "<option value=\"{$i}\">{$j}</option>\n";
		}
		print <<<END
</select>
<input type="submit" name="DEL" value="削除する">
</form>
</td>
</tr>
</table>
</div>
END;
	}
	
	//---------------------------------------------------
	// 観光者通信 書き込まれた内容を出力
	//---------------------------------------------------
	function lbbsContents($hako, $island, $owner = 0) {
		global $init;
		
		$lbbs = $island['lbbs'];
		print <<<END
<div align="center">
<table border="1">
<tr>
<th style="width:3em;">番号</th>
<th>記帳内容</th>
</tr>
END;
		for($i = 0; $i < $init->lbbsMax; $i++) {
			$j = $i + 1;
			$line = $lbbs[$i];
			list($secret, $sTemp, $mode, $turn, $message, $color) = split(">", $line);
			list($sName, $sId) = split(",", $sTemp);
			$sNo = $hako->idToNumber[$sId];
			print "<tr><th>{$init->tagNumber_}{$j}{$init->_tagNumber}</th>";
			$speaker = '';
			if($init->lbbsSpeaker && ($sName != '')) {
				if($sNo == '0' || !empty($sNo)) {
					$speaker = " <font color=gray><b><small>(<a style=\"text-decoration:none\" href=\"{$GLOBALS['THIS_FILE']}?Sight={$sId}\">{$sName}</a>)</small></b></font>";
				} else {
					$speaker = " <font color=gray><b><small>({$sName})</small></b></font>";
				}
			}
			if($mode == 0) {
				// 観光者
				if($secret == 0) {
					// 公開
					print "<td class=\"lbbsCell\"><font color=\"$color\">{$turn} &gt; {$message} {$speaker}</font></td></tr>\n";
				} else {
					// 極秘
					if($owner == 0) {
						// 観光客
						print "<td class=\"lbbsCell\"><center><font color=gray>- 極秘 -</font></center></td></tr>\n";
					} else {
						// オーナー
						print "<td class=\"lbbsCell\"><font color=\"$color\">{$turn} &gt;(秘) {$message} {$speaker}</font></td></tr>\n";
					}
				}
			} else {
				// 島主
				print "<td class=\"lbbsCell\"><font color=\"$color\">{$turn} &gt; {$message}</font></td></tr>\n";
			}
		}
		print "</table></div>\n";
	}
	
	//---------------------------------------------------
	// 島の近況
	//---------------------------------------------------
	function islandRecent($island, $mode = 0) {
		global $init;
		print "<hr>\n";
		print "<div id=\"RecentlyLog\">\n";
		print "<h2>{$island['name']}島{$init->_tagName}の近況</h2>\n";
		for($i = 0; $i < $init->logMax; $i++) {
			LogIO::logFilePrint($i, $island['id'], $mode);
		}
		print "</div>\n";
	}
	
	//---------------------------------------------------
	// 開発画面
	//---------------------------------------------------
	function tempOwer($hako, $data, $number = 0) {
		global $init;
		
		$island = $hako->islands[$number];
		$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
		$width = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;
		$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
		print <<<END
<script type="text/javascript">
<!--
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
//-->
</script>
<div align="center">
{$init->tagBig_}{$init->tagName_}{$name}{$init->_tagName}開発計画{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>
END;
		$this->islandInfo($island, $number, 1);
		print <<<END
<div align="center">
<table border="1">
<tr>
<td {$init->bgInputCell}>
<div align="center">
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="submit" value="計画送信">
<hr>
<strong>計画番号</strong>
<select name="NUMBER">
END;
		// 計画番号
		for($i = 0; $i < $init->commandMax; $i++) {
			$j = $i + 1;
			print "<option value=\"{$i}\">{$j}</option>";
		}
		print <<<END
</select><br>
<hr>
<strong>開発計画</strong><br>
<select name="COMMAND">
END;
		// コマンド
		for($i = 0; $i < $init->commandTotal; $i++) {
			$kind = $init->comList[$i];
			$cost = $init->comCost[$kind];
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
			if($kind == $data['defaultKind']) {
				$s = 'selected';
			} else {
				$s = '';
			}
			print "<option value=\"{$kind}\" {$s}>{$init->comName[$kind]}({$cost})</option>\n";
		}
		print <<<END
</select>
<hr>
<strong>座標(</strong>
<select name="POINTX">
END;
		for($i = 0; $i < $init->islandSize; $i++) {
			if($i == $data['defaultX']) {
				print "<option value=\"{$i}\" selected>{$i}</option>\n";
			} else {
				print "<option value=\"{$i}\">{$i}</option>\n";
			}
		}
		print "</select>, <select name=\"POINTY\">";
		for($i = 0; $i < $init->islandSize; $i++) {
			if($i == $data['defaultY']) {
				print "<option value=\"{$i}\" selected>{$i}</option>\n";
			} else {
				print "<option value=\"{$i}\">{$i}</option>\n";
			}
		}
		print <<<END
</select><strong>)</strong>
<hr>
<strong>数量</strong>
<select name="AMOUNT">
END;
		 for($i = 0; $i < 100; $i++) {
			 print "<option value=\"{$i}\">{$i}</option>\n";
		}
		 print <<<END
</select>
<hr>
<strong>目標の島</strong><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
<hr>
<strong>動作</strong><br>
<input type="radio" name="COMMANDMODE" id="insert" value="insert" checked><label for="insert">挿入</label>
<input type="radio" name="COMMANDMODE" id="write" value="write"><label for="write">上書き</label><BR>
<input type="radio" name="COMMANDMODE" id="delete" value="delete"><label for="delete">削除</label>
<hr>
<input type="hidden" name="DEVELOPEMODE" value="cgi">
<input type="submit" value="計画送信">
</form>
<center>ミサイル発射上限数[<b> {$island['fire']} </b>]発</center>
</div>
</td>
<td {$init->bgMapCell}>
END;
		$this->islandMap($hako, $island, 1); // 島の地図、所有者モード
		print <<<END
</td>
<td {$init->bgCommandCell}>
END;
		$command = $island['command'];
		for($i = 0; $i < $init->commandMax; $i++) {
			$this->tempCommand($i, $command[$i], $hako);
		}
		print <<<END
</td>
</tr>
</table>
</div>
<hr>
<div id='CommentBox'>
<h2>コメント更新</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
コメント<input type="text" name="MESSAGE" size="80" value="{$island['comment']}"><br>
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="hidden" name="mode" value="comment">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="cgi">
<input type="submit" value="コメント更新">
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
		$target = $hako->idToName[$target];
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
		print "<a href=\"javascript:void(0);\" onclick=\"ns({$number})\">{$init->tagNumber_}{$j}{$init->_tagNumber}";
		
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
		print "{$str}</a><br>";
	}
	//---------------------------------------------------
	// 新しく発見した島
	//---------------------------------------------------
	function newIslandHead($name) {
		global $init;
		
		print <<<END
<div align="center">
{$init->tagBig_}島を発見しました！！{$init_tagBig}<br>
{$init->tagBig_}{$init->tagName_}「{$name}島」{$init->_tagName}と命名します。{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
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
		print <<<END
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
	function header($data = "") {
		global $init;
		global $PRODUCT_VERSION;
		
		// 圧縮転送
		if(GZIP == true) {
			global $http;
			$http->start();
		}
		header("X-Product-Version: {$PRODUCT_VERSION}");
		$css = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
		$bimg = (empty($data['defaultImg'])) ? $init->imgDir : $data['defaultImg'];
		print <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<base href="{$bimg}/">
<meta http-equiv="Content-type" content="text/html; charset=Shift_JIS">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="{$init->cssDir}/{$css}">
<link rel="shortcut icon" href="{$init->baseDir}/favicon.ico">
<title>{$init->title}</title>
<script type="text/javascript" src="{$init->baseDir}/hako.js"></script>
<script type="text/javascript" src="{$init->baseDir}/cpick.js"></script>
</head>
<body onload="init()">
<div id="LinkHeader">
<a href="http://www.bekkoame.ne.jp/~tokuoka/hakoniwa.html">箱庭諸島スクリプト配布元</a>
<a href="http://scrlab.g-7.ne.jp">[PHP]</a>　
[<a href="http://hakoniwa.symphonic-net.com">箱庭諸島S.E配布元</a>]　
[<a href="http://snufkin.jp.land.to">沙布巾の箱庭</a>]　
[<a href="http://www.s90259900.onlinehome.us/">箱庭の箱庭</a>]　
[<a href="http://no-one.s53.xrea.com">The Return of Neptune</a>]　
[<a href="http://minnano.min-ai.net/ocn/">みんなのあいらんど</a>]
<BR>
[<a href="{$init->baseDir}/hako-main.php?mode=conf">島の登録・設定変更</a>]　
[<a href="{$init->baseDir}/hako-ally.php">同盟管理</a>]　
[<a href="{$init->baseDir}/hako-main.php?mode=log">最近の出来事</a>]　
[<a href="{$init->urlManu}" target="_blank">マニュアル</a>]　
[<a href="{$init->urlBbs}" target="_blank">掲示板</a>]　
[<a href="{$init->baseDir}/hako-admin.php">管理人室</a>]
</div>
<hr>
END;
	}
	
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
		for($i = 0; $i < $init->commandMax; $i++) {
			// 各要素の取り出し
			$command = $island['command'][$i];
			$s_kind = $command['kind'];
			$s_target = $command['target'];
			$s_x = $command['x'];
			$s_y = $command['y'];
			$s_arg = $command['arg'];
			
			// コマンド登録
			if($i == $init->commandMax - 1){
				$set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target]\n";
				$com_max .= "0";
			} else {
				$set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target],\n";
				$com_max .= "0,";
			}
		}
		//コマンドリストセット
		$l_kind;
		$set_listcom = "";
		$click_com = array("", "", "", "", "", "", "", "");
		$All_listCom = 0;
		$com_count = count($init->commandDivido);
		for($m = 0; $m < $com_count; $m++) {
			list($aa,$dd,$ff) = split(",", $init->commandDivido[$m]);
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
				if($l_kind < $ff+1) { next; }
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
		for($i = 0; $i < $init->shipKind; $i++) {
				$set_ships .= "'".$init->shipName[$i]."',";
		}
		// 衛星リストセット
		//$set_eisei = implode("," , $init->EiseiName);
		for($i = 0; $i < count($init->EiseiName); $i++) {
				$set_eisei .= "'".$init->EiseiName[$i]."',";
		}
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
		$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
		print <<<END
<center>
{$init->tagBig_}{$init->tagName_}{$name}{$init->_tagName}開発計画{$init->_tagBig}<BR>
{$GLOBALS['BACK_TO_TOP']}<br>
</center>
<script type="text/javascript">
<!--
var w;
var p = $defaultTarget;

// ＪＡＶＡスクリプト開発画面配布元
// あっぽー庵箱庭諸島（ http://appoh.execweb.cx/hakoniwa/ ）
// Programmed by Jynichi Sakai(あっぽー)
// ↑ 削除しないで下さい。
var str;
g = [$com_max];
k1 = [$com_max];
k2 = [$com_max];
tmpcom1 = [ [0,0,0,0,0] ];
tmpcom2 = [ [0,0,0,0,0] ];
command = [
$set_com];

comlist = [
$set_listcom
];

islname = [
$set_island];

shiplist = [$set_ships];
eiseilist = [$set_eisei];

function init() {
	for(i = 0; i < command.length ;i++) {
		for(s = 0; s < $com_count ;s++) {
			var comlist2 = comlist[s];
			for(j = 0; j < comlist2.length ; j++) {
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
	if(document.layers) {
		document.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
	}
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
	a = theForm.NUMBER.options[theForm.NUMBER.selectedIndex].value;
	b = theForm.COMMAND.options[theForm.COMMAND.selectedIndex].value;
	c = theForm.POINTX.options[theForm.POINTX.selectedIndex].value;
	d = theForm.POINTY.options[theForm.POINTY.selectedIndex].value;
	e = theForm.AMOUNT.options[theForm.AMOUNT.selectedIndex].value;
	f = theForm.TARGETID.options[theForm.TARGETID.selectedIndex].value;
	if(x == 6){ b = k; menuclose(); }
	var newNs = a;
	if (x == 1 || x == 2 || x == 6){
		if(x == 6) b = k;
		if(x != 2) {
			for(i = $init->commandMax - 1; i > a; i--) {
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
		str = '<font color="red"><strong>■ 未送信 ■<\\/strong><\\/font><br>' + str;
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
	str = '<font color="red"><b>■ 未送信 ■<\\/b><\\/font><br>' + str;
	disp(str, "");
	outp();
	theForm.SENDPROJECT.disabled = false;
	ns(newNs);
	return true;
}

function plchg() {
	strn1 = "";
	for(i = 0; i < $init->commandMax; i++) {
		c = command[i];
		kind = '{$init->tagComName_}' + g[i] + '{$init->_tagComName}';
		x = c[1];
		y = c[2];
		tgt = c[4];
		point = '{$init->tagName_}' + "(" + x + "," + y + ")" + '{$init->_tagName}';
		for(j = 0; j < islname.length ; j++) {
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
	if(str==null) str = "";

	if(document.getElementById || document.all){
		LayWrite('LINKMSG1', str);
		SetBG('plan', bgclr);
	} else if(document.layers) {
		lay = document.layers["PARENT_LINKMSG"].document.layers["LINKMSG1"];
		lay.document.open();
		lay.document.write("<font style='font-size:11pt'>"+str+"<\\/font>");
		lay.document.close();
		SetBG("PARENT_LINKMSG", bgclr);
	}
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
	if(!(document.InputPlan.MENUOPEN.checked))
		moveLAYER("menu",mx+10,my-50);
	NaviClose();
	return true;
}

function ns(x) {
	if (x == $init->commandMax){ return true; }
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
	if(document.getElementById){ //NN6,IE5
		el = document.getElementById(layName);
		el.style.left = x;
		el.style.top = y;
	} else if(document.layers){ //NN4
		msgLay = document.layers[layName];
		msgLay.moveTo(x,y);
	} else if(document.all){ //IE4
		msgLay = document.all(layName).style;
		msgLay.pixelLeft = x;
		msgLay.pixelTop = y;
	}
}

function menuclose() {
	moveLAYER("menu",-500,-500);
}

function Mmove(e){
	if(document.all){
		mx = event.x + document.body.scrollLeft;
		my = event.y + document.body.scrollTop;
	}else if(document.layers){
		mx = e.pageX;
		my = e.pageY;
	}else if(document.getElementById){
		mx = e.pageX;
		my = e.pageY;
	}
	return moveLay.move();
}

function LayWrite(layName, str) {
	if(document.getElementById){
		document.getElementById(layName).innerHTML = str;
	} else if(document.all){
		document.all(layName).innerHTML = str;
	} else if(document.layers){
		lay = document.layers[layName];
		lay.document.open();
		lay.document.write(str);
		lay.document.close();
	}
}

function SetBG(layName, bgclr) {
	 if(document.getElementById) document.getElementById(layName).style.backgroundColor = bgclr;
	 else if(document.all) document.all.layName.bgColor = bgclr;
	 //else if(document.layers) document.layers[layName].bgColor = bgclr;
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
	if(document.getElementById) {
		if(color.length == 4) document.getElementById('com_'+num).style.borderTop = ' 1px solid '+color;
		else document.getElementById('com_'+num).style.border = '0px';
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
	moveLay = new MoveComList(num); return (document.layers) ? true : false;
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
		moveLAYER('mc_div',mx+10,my-30);
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
	for(i=0;i<document.ch_numForm.AMOUNT.options.length;i++){
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
	if(document.all){
		if (event.altKey || event.ctrlKey || event.shiftKey) return;
		c = event.keyCode;
		el = new String(event.srcElement.tagName);
		el = el.toUpperCase();
		if (el == "INPUT") return;
//	}else if(document.layers){// NN4 KEYDOWNイベントはWin98系で文字化けするのでコメント化
//		if (e.modifiers != 0) return;
//		c = e.which;
//		if ((c >= 97) && (c <= 122)) c -= 32; // 英小文字を英大文字にする
//		el = new String(e.target);
//		el = el.toUpperCase();
//		if (el.indexOf("<INPUT") >= 0) return;
	}else if(document.getElementById){
		if (e.altKey || e.ctrlKey || e.shiftKey) return;
		c = e.which;
		el = new String(e.target.tagName);
		el = el.toUpperCase();
		if (el == "INPUT") return;
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
//-->
</script>
END;
		$this->islandInfo($island, $number, 1);
		print <<<END
<div id="menu" style="position:absolute; top:-500;left:-500; overflow:auto;width:360px;height:350px;">
<table border=0 class="PopupCell" onClick="menuclose()">
<tr valign=top>
<td>
$click_com[0]<hr>
$click_com[1]
</div>
</td>
<td>
$click_com[2]<hr>
$click_com[3]
</td>
</tr>
<tr valign=top>
<td>
$click_com[4]<hr>
$click_com[5]
</td>
<td>
$click_com[6]
</td>
</tr>
</table>
</div>
<div ID="mc_div" style="position:absolute;top:-50;left:-50;height:22px;">&nbsp;</div>
<div ID="ch_num" style="position:absolute;visibility:hidden;display:none">
<form name="ch_numForm">
<table border=1 bgcolor="#e0ffff" cellspacing=1>
<tr><td valign=top nowrap>
<a href="JavaScript:void(0)" onClick="hideElement('ch_num');" style="text-decoration:none"><B>×</B></a><br>
<select name="AMOUNT" size=13 onchange="chNumDo()">
</select>
</TD>
</TR>
</TABLE>
</form>
</div>
<div align="center">
<table border="1">
<tr valign="top">
<td $init->bgInputCell>
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="COMARY" value="comary">
<input type="hidden" name="DEVELOPEMODE" value="java">
<center>
<br>
<b>コマンド入力</b><br>
<b>
<a href="javascript:void(0);" onclick="cominput(InputPlan,1)">挿入</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,2)">上書き</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,3)">削除</a>
</b>
<hr>
<b>計画番号</b>
<select name="NUMBER">
END;
		// 計画番号
		for($i = 0; $i < $init->commandMax; $i++) {
			$j = $i + 1;
			print "<option value=\"$i\">$j</option>\n";
		}
		if ($data['MENUOPEN'] == 'on') {
			$open = "CHECKED";
		}else{
			$open = "";
		}
		print <<<END
</select>
<hr>
<b>開発計画</b><br>
<input type="checkbox" name="NAVIOFF" $open>NaviOff
<input type="checkbox" name="MENUOPEN" $open>PopupOff<br>
<br>
<select name="menu" onchange="SelectList(InputPlan)">
<option value="">全種類</option>
END;
		for($i = 0; $i < $com_count; $i++) {
			list($aa, $tmp) = split(",", $init->commandDivido[$i], 2);
			print "<option value=\"$i\">{$aa}</option>\n";
		}
		print <<<END
</select><br>
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
			if($i == $data['defaultX']) {
				print "<option value=\"$i\" selected>$i</option>\n";
			} else {
				print "<option value=\"$i\">$i</option>\n";
			}
		}
		print "</select>, <select name=\"POINTY\">\n";
		for($i = 0; $i < $init->islandSize; $i++) {
			if($i == $data['defaultY']) {
				print "<option value=\"$i\" selected>$i</option>\n";
			} else {
				print "<option value=\"$i\">$i</option>\n";
			}
		}
		print <<<END
</select><b> )</b>
<hr>
<b>数量</b><select name="AMOUNT">
END;
		// 数量
		for($i = 0; $i < 100; $i++) {
			print "<option value=\"$i\">$i</option>\n";
		}
		
		// 船舶数
		$ownship = 0;
		for($i = 0; $i < $init->shipKind; $i++) {
			$ownship += $island['ship'][$i];
		}
		print <<<END
</select>
<hr>
<b>目標の島</b><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList<br>
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
<hr>
<b>コマンド移動</b>：
<a href="javascript:void(0);" onclick="cominput(InputPlan,4)" style="text-decoration:none"> ▲ </a>・・
<a href="javascript:void(0);" onclick="cominput(InputPlan,5)" style="text-decoration:none"> ▼ </a>
<hr>
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="submit" value="計画送信" name="SENDPROJECT">
<br>最後に<font color="red">計画送信ボタン</font>を<br>押すのを忘れないように。</font>
</center>
</form>
<center>ミサイル発射上限数[<b> {$island['fire']} </b>]発
<br>所有船舶数[<b> {$ownship} </b>]隻
<br><br>
<a title='数字=数量　BS=一つ前削除
DEL=削除　INS=資金繰り
A=整地　J=地ならし
K=掘削　U=埋め立て
B=伐採　P=植林
N=農場整備　I=工場建設
S=採掘場整備
D=防衛施設建設
M=ミサイル基地建設
F=海底基地建設'>
キー入力簡易説明</a>(NN4不可)
</center>
</td>
<td $init->bgMapCell id="plan" onmouseout="mc_out();return false;">
END;
		$this->islandMap($hako, $island, 1); // 島の地図、所有者モード
		$comment = $hako->islands[$number]['comment'];
		print <<<END
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
コメント<input type="text" name="MESSAGE" size="80" value="{$island['comment']}"><br>
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="hidden" name="mode" value="comment">
<input type="hidden" name="DEVELOPEMODE" value="java">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="submit" value="コメント更新">
</FORM>
</DIV>
</DIV>
END;
	}
	
	// 関数ダミー【追加】
	function funcJavaDM() {
		print <<<END
<script  type="text/javascript">
<!--
function init(){
}
function SelectList(theForm){
}
//-->
</script>
END;
	}
}

class HtmlSetted extends HTML {
	function setSkin() {
		global $init;
		print "{$init->tagBig_}スタイルシートを設定しました。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
	}
	
	function setImg() {
		global $init;
		print "{$init->tagBig_}画像のローカル設定をしました。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
	}
	
	function comment() {
		global $init;
		print "{$init->tagBig_}コメントを更新しました{$init->_tagBig}<hr>";
	}
	
	function change() {
		global $init;
		print "{$init->tagBig_}変更完了しました{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
	}
	
	function lbbsDelete() {
		global $init;
		print "{$init->tagBig_}記帳内容を削除しました{$init->_tagBig}<hr>";
	}
	
	function lbbsAdd() {
		global $init;
		print "{$init->tagBig_}記帳を行いました{$init->_tagBig}<hr>";
	}
	
	// コマンド削除
	function commandDelete() {
		global $init;
		print "{$init->tagBig_}コマンドを削除しました{$init->_tagBig}<hr>\n";
	}
	
	// コマンド登録
	function commandAdd() {
		global $init;
		print "{$init->tagBig_}コマンドを登録しました{$init->_tagBig}<hr>\n";
	}
	
	// 島の強制削除
	function deleteIsland($name) {
		global $init;
		print "{$init->tagBig_}{$name}島を強制削除しました。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
	}
}

class Error {
	function wrongPassword() {
		global $init;
		print "{$init->tagBig_}パスワードが違います。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";

    // JavaScript error の回避【追加】
    HtmlJS::funcJavaDM();

    HTML::footer();
    exit;
	}
	
	function wrongID() {
		global $init;
		print "{$init->tagBig_}IDが違います。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function emptyImg() {
		global $init;
		print "{$init->tagBig_}設定変更で「画像のローカル設定」を行って下さい。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
	}
	
	// hakojima.datがない
	function noDataFile() {
		global $init;
		print "{$init->tagBig_}データファイルが開けません。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function newIslandFull() {
		global $init;
		print "{$init->tagBig_}申し訳ありません、島が一杯で登録できません！！{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function newIslandNoName() {
		global $init;
		print "{$init->tagBig_}島につける名前が必要です。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function newIslandBadName() {
		global $init;
		print "{$init->tagBig_},?()<>\$とか入ってたり、「無人島」とかいった変な名前はやめましょうよ～。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function newIslandAlready() {
		global $init;
		print "{$init->tagBig_}その島ならすでに発見されています。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function newIslandNoPassword() {
		global $init;
		print "{$init->tagBig_}パスワードが必要です。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function changeNoMoney() {
		global $init;
		print "{$init->tagBig_}資金不足のため変更できません{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function changeNothing() {
		global $init;
		print "{$init->tagBig_}名前、パスワードともに空欄です{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function problem() {
		global $init;
		print "{$init->tagBig_}問題発生、とりあえず戻ってください。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function lbbsNoMessage() {
		global $init;
		print "{$init->tagBig_}名前または内容の欄が空欄です。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function lockFail() {
		global $init;
		print "{$init->tagBig_}同時アクセスエラーです。<BR>ブラウザの「戻る」ボタンを押し、<BR>しばらく待ってから再度お試し下さい。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
	
	function lbbsNoMoney() {
		global $init;
		print "{$init->tagBig_}資金不足のため記帳できません。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}\n";
		HTML::footer();
		exit;
	}
}

?>
