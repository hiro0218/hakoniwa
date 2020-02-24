<?php
/**
 * 箱庭諸島 S.E - 画面出力用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once HELPERPATH.'/message/error.php';
require_once HELPERPATH.'/message/success.php';
require_once APPPATH.'/model/hako-log.php';

class HTML {
	/**
	 * HTML <head>
	 * @return [type] [description]
	 */
	static function header() {
		global $init;
		require_once(VIEWS.'/header.php');
		require_once(VIEWS.'/body.php');
	}

	static function head() {
		global $init;
		require_once(VIEWS.'/header.php');
	}
	
	
	/**
	 * HTML <footer>
	 * @return [type] [description]
	 */
	static function footer() {
		global $init;
		require_once(VIEWS.'/footer.php');
	}

	/**
	 * 最終更新時刻 ＋ 次ターン更新時刻出力
	 * @param  [type] $hako [description]
	 * @return [type]       [description]
	 */
	function lastModified($hako) {
		global $init;
		require_once(VIEWS.'/lastModified.php');
	}

	/**
	 * [timeToString description]
	 * @param  [type] $t [description]
	 * @return [type]    [description]
	 */
	function timeToString($t) {
		$time = localtime($t, TRUE);
		$time['tm_year'] += 1900;
		$time['tm_mon']++;
		return "{$time['tm_year']}年 {$time['tm_mon']}月 {$time['tm_mday']}日 {$time['tm_hour']}時 {$time['tm_min']}分 {$time['tm_sec']}秒";
	}
}


class HtmlTop extends HTML {

	function main($hako, $data) {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";
		$allyfile = $init->baseDir . "/hako-ally.php";

		$radio  = "checked";
		$radio2 = "";
		if( !empty($data['defaultDevelopeMode']) && $data['defaultDevelopeMode'] == "javascript") {
			$radio  = "";
			$radio2 = "checked";
		}

		// セットするパスワードのチェック
		$defaultPassword = isset($data['defaultPassword']) ? $data['defaultPassword'] : "";

		// 読み込み
		require_once(VIEWS.'/top/main.php');

		// 各部門ランキング
		require_once(VIEWS.'/top/category-rank.php');

		// 同盟の状況
		if($hako->allyNumber) {
			require_once(VIEWS.'/top/ally-list.php');
		}

		// 各諸島の状況
		require_once(VIEWS.'/top/island-list.php');

		// Battle Fieldの状況
		require_once(VIEWS.'/top/bf-list.php');

		// 歴史
		require_once(VIEWS.'/log/history.php');

		// 管理者登録モード
		if($init->registerMode) {
			require_once(VIEWS.'/top/register-mode.php');
		}
	}

	/**
	 * 島の一覧表を表示
	 * @param  [type] $hako     [description]
	 * @param  [type] $start    [description]
	 * @param  [type] $sentinel [description]
	 * @return [type]           [description]
	 */
	function islandTable(&$hako, $start, $sentinel) {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

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
			//$starturn     = $island['starturn'];
			$monster      = '';

			if($island['monster'] > 0) {
				$monster = "<strong class=\"monster\">[怪獣{$island['monster']}体 出現中]</strong>";
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
			for($e = 0; $e < $init->EiseiNumber; $e++) {
				if (isset($eisei[$e])) {
					if($eisei[$e] > 0) {
						$eiseis .= "<img src=\"{$init->imgDir}/eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eisei[$e]}%\" title=\"{$init->EiseiName[$e]} {$eisei[$e]}%\"> ";
					} else {
						$eiseis .= "";
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
				$start .= " 🔰";//" <IMG SRC=\"{$init->imgDir}/start.gif\" width=\"16\" height=\"16\" ALT=\"初心者マーク\" title=\"初心者マーク\">";
			}

			$soccer = "";
			if($island['soccer'] > 0) {
				//$soccer .= " <IMG SRC=\"{$init->imgDir}/soccer.gif\" width=\"16\" height=\"16\" ALT=\"総合ポイント：{$team}　{$shiai}戦{$kachi}勝{$make}敗{$hikiwake}分　攻撃力：{$kougeki}　守備力：{$bougyo}　得点：{$tokuten}　失点：{$shitten}\" title=\"総合ポイント：{$team}　{$shiai}戦{$kachi}勝{$make}敗{$hikiwake}分　攻撃力：{$kougeki}　守備力：{$bougyo}　得点：{$tokuten}　失点：{$shitten}\">";
				$soccer .= " <span title=\"総合ポイント：{$team}　{$shiai}戦{$kachi}勝{$make}敗{$hikiwake}分　攻撃力：{$kougeki}　守備力：{$bougyo}　得点：{$tokuten}　失点：{$shitten}\">⚽</span>";
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
			$keep = isset($keep) ? $keep : "";
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
	<tr>
		<th {$init->bgNumberCell} rowspan="5">{$init->tagNumber_}$j{$init->_tagNumber}</th>
		<td {$init->bgNameCell} rowspan="5" valign="top">
			<h3><a href="{$this_file}?Sight={$id}">{$name}</a> <small>{$start}{$monster}{$soccer}</small></h3>
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
		<td class="ItemCell" colspan="6">$items</td>
	</tr>
	<tr>
		<td {$init->bgCommentCell} colspan="7">{$init->tagTH_}{$owner}：{$init->_tagTH}$comment</td>
	</tr>
END;
		}
		echo "</table>";
		echo "</div>";
	}

	/**
	 * 島の登録と設定
	 * @param type $hako
	 * @param type $data
	 */
	function register(&$hako, $data = "") {
		require_once(VIEWS.'/conf/register.php');
	}

	/**
	 * 新しい島を探す
	 * @param  [type] $number [description]
	 * @return [type]         [description]
	 */
	function discovery($number) {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

		require_once(VIEWS.'/conf/discovery.php');
	}

	/**
	 * 島の名前とパスワードの変更
	 */
	function changeIslandInfo($islandList = "") {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

		require_once(VIEWS.'/conf/change/island-info.php');
	}

	/**
	 * オーナー名の変更
	 */
	function changeOwnerName($islandList = "") {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

		require_once(VIEWS.'/conf/change/owner-name.php');
	}

	/**
	 * 最近の出来事
	 */
	function log() {
		global $init;
		require_once(VIEWS.'/log/recent.php');
	}

	/**
	 * お知らせ
	 */
	function infoPrint() {
		global $init;
		require_once(VIEWS.'/log/info.php');
	}

}


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
			HakoError::wrongPassword();
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
			HakoError::problem();
			return;
		}
		// 島の名前を取得
		$island = $hako->islands[$number];
		$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);

		// 読み込み
		require_once(VIEWS.'/map/main.php');
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

		// 島の情報
		require_once(VIEWS.'/map/island-info.php');
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

		require_once(VIEWS.'/map/development/map.php');

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

		require_once(VIEWS.'/map/development/basic.php');
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
			HakoError::problem();
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


class HtmlMapJS extends HtmlMap {

	//---------------------------------------------------
	// 開発画面
	//---------------------------------------------------
	function tempOwer($hako, $data, $number = 0) {
		global $init;
		$this_file = $init->baseDir . "/hako-main.php";

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

		require_once(VIEWS.'/map/development/js.php');

		echo <<<END
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
	w = window.open("{$this_file}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
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

<div class="text-center">
<table class="table table-bordered">
<tr valign="top">
<td $init->bgInputCell>

<form action="{$this_file}" method="post" name="InputPlan">
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

	<b>数量</b>
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
	<form action="{$this_file}" method="post">
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

}


class HtmlAdmin extends HTML {

	function enter() {
		global $init;

		$urllist  = array( ini_get('safe_mode') ? '/hako-mente-safemode.php' : '/hako-mente.php', '/hako-axes.php', '/hako-keep.php', '/hako-present.php', '/hako-edit.php', '/hako-bf.php');
		$menulist = array('データ管理','アクセスログ閲覧','島預かり管理','プレゼント','マップエディタ','BattleField管理');

		require_once(VIEWS.'/admin/top.php');
	}
}

class HtmlPresent extends HTML {

	function enter() {
		global $init;
		$this_file = $init->baseDir . "/hako-present.php";
		$main_file = $init->baseDir . "/hako-main.php";

		require_once(VIEWS.'/admin/present/top.php');
	}

	function main($data, $hako) {
		global $init;
		$this_file = $init->baseDir . "/hako-present.php";
		$main_file = $init->baseDir . "/hako-main.php";

		$width  = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;
		//$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
		$defaultTarget = "";

		require_once(VIEWS.'/admin/present/main.php');
	}
}

class HtmlMente extends HTML {

	function enter() {
		global $init;
		$this_file = $init->baseDir . "/hako-mente.php";

		echo "<h1 class=\"title\">メンテナンスツール</h1>";
		if(file_exists("{$init->passwordFile}")) {
			echo <<<END
<form action="{$this_file}" method="post">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="メンテナンス">
END;
		} else {
			echo <<<END
<form action="{$this_file}" method="post">
<H2>マスタパスワードと特殊パスワードを決めてください。</H2>
<P>※入力ミスを防ぐために、それぞれ２回ずつ入力してください。</P>
<B>マスタパスワード：</B><BR>
(1) <INPUT type="password" name="MPASS1" value="$mpass1">&nbsp;&nbsp;(2) <INPUT type="password" name="MPASS2" value="$mpass2"><BR>
<BR>
<B>特殊パスワード：</B><BR>
(1) <INPUT type="password" name="SPASS1" value="$spass1">&nbsp;&nbsp;(2) <INPUT type="password" name="SPASS2" value="$spass2"><BR>
<BR>
<input type="hidden" name="mode" value="setup">
<INPUT type="submit" value="パスワードを設定する">
END;
		}
		echo "</form>\n";
	}

	function main($data) {
		global $init;
		$this_file = $init->baseDir . "/hako-mente.php";

		echo "<h1 class=\"title\">メンテナンスツール</h1>\n";
		if(is_dir("{$init->dirName}")) {
			$this->dataPrint($data);
		} else {
			echo "<hr>\n";
			echo "<form action=\"{$this_file}\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"PASSWORD\" value=\"{$data['PASSWORD']}\">\n";
			echo "<input type=\"hidden\" name=\"mode\" value=\"NEW\">\n";
			echo "<input type=\"submit\" value=\"新しいデータを作る\">\n";
			echo "</form>\n";
		}
		// バックアップデータ
		$dir = opendir("./");
		while($dn = readdir($dir)) {
			$_dirName = preg_quote($init->dirName, "/");
			if(preg_match("/{$_dirName}\.bak(.*)$/", $dn, $suf)) {
				if (is_file("{$init->dirName}.bak{$suf[1]}/hakojima.dat")) {
					$this->dataPrint($data, $suf[1]);
				}
			}
		}
		closedir($dir);
	}

	// 表示モード
	function dataPrint($data, $suf = "") {
		global $init;
		$this_file = $init->baseDir . "/hako-mente.php";

		echo "<HR>";
		if(strcmp($suf, "") == 0) {
			$fp = fopen("{$init->dirName}/hakojima.dat", "r");
			echo "<h2>現役データ</h2>\n";
		} else {
			$fp = fopen("{$init->dirName}.bak{$suf}/hakojima.dat", "r");
			echo "<h2>バックアップ{$suf}</h2>\n";
		}
		$lastTurn = chop(fgets($fp, READ_LINE));
		$lastTime = chop(fgets($fp, READ_LINE));
		fclose($fp);
		$timeString = self::timeToString($lastTime);
		echo <<<END
<strong>ターン$lastTurn</strong><br>
<strong>最終更新時間</strong>:$timeString<br>
<strong>最終更新時間(秒数表示)</strong>:1970年1月1日から$lastTime 秒<br>
<form action="{$this_file}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="DELETE">
<input type="hidden" name="NUMBER" value="{$suf}">
<input type="submit" value="このデータを削除">
</form>
END;
		if(strcmp($suf, "") == 0) {
			$time = localtime($lastTime, TRUE);
			$time['tm_year'] += 1900;
			$time['tm_mon']++;
			echo <<<END
<h2>最終更新時間の変更</h2>
<form action="{$this_file}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="NTIME">
	<input type="hidden" name="NUMBER" value="{$suf}">
	<input type="text" size="4" name="YEAR" value="{$time['tm_year']}">年
	<input type="text" size="2" name="MON" value="{$time['tm_mon']}">月
	<input type="text" size="2" name="DATE" value="{$time['tm_mday']}">日
	<input type="text" size="2" name="HOUR" value="{$time['tm_hour']}">時
	<input type="text" size="2" name="MIN" value="{$time['tm_min']}">分
	<input type="text" size="2" name="NSEC" value="{$time['tm_sec']}">秒
	<input type="submit" value="変更">
</form>
<form action="{$this_file}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="STIME">
	<input type="hidden" name="NUMBER" value="{$suf}">
	1970年1月1日から<input type="text" size="32" name="SSEC" value="$lastTime">秒
	<input type="submit" value="秒指定で変更">
</form>
END;
		}
	}

}

class HtmlMenteSafe extends HTML {
	function enter() {
		global $init;
		$this_file = $init->baseDir . "/hako-mente-safemode.php";

		echo "<h1 class=\"title\">メンテナンスツール</h1>";
		if(file_exists("{$init->passwordFile}")) {
			echo <<<END
<form action="{$this_file}" method="post">
<strong>パスワード：</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="メンテナンス">
END;
		} else {
			echo <<<END
<form action="{$this_file}" method="post">
<H2>マスタパスワードと特殊パスワードを決めてください。</H2>
<P>※入力ミスを防ぐために、それぞれ２回ずつ入力してください。</P>
<B>マスタパスワード：</B><BR>
(1) <INPUT type="password" name="MPASS1" value="$mpass1">&nbsp;&nbsp;(2) <INPUT type="password" name="MPASS2" value="$mpass2"><BR>
<BR>
<B>特殊パスワード：</B><BR>
(1) <INPUT type="password" name="SPASS1" value="$spass1">&nbsp;&nbsp;(2) <INPUT type="password" name="SPASS2" value="$spass2"><BR>
<BR>
<input type="hidden" name="mode" value="setup">
<INPUT type="submit" value="パスワードを設定する">
END;
		}
		echo "</form>\n";
	}

	function main($data) {
		global $init;
		$this_file = $init->baseDir . "/hako-mente-safemode.php";

		echo "<h1 class=\"title\">{$init->title}<br>メンテナンスツール</h1>\n";
		// データ保存用ディレクトリの存在チェック
		if(!is_dir("{$init->dirName}")) {
			echo "{$init->tagBig_}データ保存用のディレクトリが存在しません{$init->_tagBig}";
			HTML::footer();
			exit();
		}
		// データ保存用ディレクトリのパーミッションチェック
		if(!is_writeable("{$init->dirName}") || !is_readable("{$init->dirName}")) {
			echo "{$init->tagBig_}データ保存用のディレクトリのパーミッションが不正です。パーミッションを0777等の値に設定してください。{$init->_tagBig}";
			HTML::footer();
			exit();
		}
		if(is_file("{$init->dirName}/hakojima.dat")) {
			$this->dataPrint($data);
		} else {
			echo "<hr>\n";
			echo "<form action=\"{$this_file}\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"PASSWORD\" value=\"{$data['PASSWORD']}\">\n";
			echo "<input type=\"hidden\" name=\"mode\" value=\"NEW\">\n";
			echo "<input type=\"submit\" value=\"新しいデータを作る\">\n";
			echo "</form>\n";
		}
		// バックアップデータ
		$dir = opendir("./");
		while($dn = readdir($dir)) {
			$_dirName = preg_quote($init->dirName, "/");
			if(preg_match("/{$_dirName}\.bak(.*)$/", $dn, $suf)) {
				if (is_file("{$init->dirName}.bak{$suf[1]}/hakojima.dat")) {
					$this->dataPrint($data, $suf[1]);
				}
			}
		}
		closedir($dir);
	}

	// 表示モード
	function dataPrint($data, $suf = "") {
		global $init;
		$this_file = $init->baseDir . "/hako-mente-safemode.php";

		echo "<HR>";
		if(strcmp($suf, "") == 0) {
			$fp = fopen("{$init->dirName}/hakojima.dat", "r");
			echo "<h2>現役データ</h2>\n";
		} else {
			$fp = fopen("{$init->dirName}.bak{$suf}/hakojima.dat", "r");
			echo "<h2>バックアップ{$suf}</h2>\n";
		}
		$lastTurn = chop(fgets($fp, READ_LINE));
		$lastTime = chop(fgets($fp, READ_LINE));
		fclose($fp);
		$timeString = self::timeToString($lastTime);

		echo <<<END
<strong>ターン$lastTurn</strong><br>
<strong>最終更新時間</strong>:$timeString<br>
<strong>最終更新時間(秒数表示)</strong>:1970年1月1日から$lastTime 秒<br>
<form action="{$this_file}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="DELETE">
	<input type="hidden" name="NUMBER" value="{$suf}">
	<input type="submit" value="このデータを削除">
</form>
END;
		if(strcmp($suf, "") == 0) {
			$time = localtime($lastTime, TRUE);
			$time['tm_year'] += 1900;
			$time['tm_mon']++;
			echo <<<END
<h2>最終更新時間の変更</h2>
<form action="{$this_file}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="NTIME">
	<input type="hidden" name="NUMBER" value="{$suf}">
	<input type="text" size="4" name="YEAR" value="{$time['tm_year']}">年
	<input type="text" size="2" name="MON" value="{$time['tm_mon']}">月
	<input type="text" size="2" name="DATE" value="{$time['tm_mday']}">日
	<input type="text" size="2" name="HOUR" value="{$time['tm_hour']}">時
	<input type="text" size="2" name="MIN" value="{$time['tm_min']}">分
	<input type="text" size="2" name="NSEC" value="{$time['tm_sec']}">秒
	<input type="submit" value="変更">
</form>
<form action="{$this_file}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="STIME">
	<input type="hidden" name="NUMBER" value="{$suf}">
	1970年1月1日から<input type="text" size="32" name="SSEC" value="$lastTime">秒
	<input type="submit" value="秒指定で変更">
</form>
END;
		} else {
			echo <<<END
<form action="{$this_file}" method="post">
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="NUMBER" value="{$suf}">
	<input type="hidden" name="mode" value="CURRENT">
	<input type="submit" value="このデータを現役に">
</form>
END;
		}
	}


}

class HtmlAxes extends HTML {
	function enter() {
		global $init;
		$this_file = $init->baseDir . "/hako-axes.php";

		echo <<<END
<h1 class="title">{$init->title}<br>アクセスログ閲覧室</h1>
<form action="{$this_file}" method="post">
	<strong>パスワード：</strong>
	<input type="password" size="32" maxlength="32" name="PASSWORD">
	<input type="hidden" name="mode" value="enter">
	<input type="submit" value="入室する">
</form>
END;
	}

	function main($data) {
		global $init;
		echo "<h1 class=\"title\">アクセスログ閲覧室</h1>\n";
		$this->dataPrint($data);
	}

	// 表示モード
	function dataPrint($data, $suf = "") {
		global $init;

		echo "<HR>";
		echo <<<END
<br>
<h2>アクセスログ</h2>
<form action="#">
<input type="button" value="オートフィルタ表示" onclick="Button_DispFilter(this, 'DATA-TABLE')" onkeypress="Button_DispFilter(this, 'DATA-TABLE')">
<table id="DATA-TABLE">
	<thead>
		<tr class="NumberCell">
			<td scope="row"><input type="button" tabindex="1" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [0])" value="ログインした時間"></td>
			<td scope="row"><input type="button" tabindex="2" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [1, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [1, 0])" value="島ＩＤ"></td>
			<td scope="row"><input type="button" tabindex="3" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [2, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [2, 0])" value="島の名前"></td>
			<td scope="row"><input type="button" tabindex="4" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [3, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [3, 0])" value="ＩＰ情報"></td>
			<td scope="row"><input type="button" tabindex="5" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [4, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [4, 0])" value="ホスト情報"></td>
		</tr>
	</thead>
	<tbody>
END;
		// ファイルを読み込み専用でオープンする
		$fp = fopen("{$init->dirName}/{$init->logname}", 'r');

		// 終端に達するまでループ
		while (!feof($fp)) {
			// ファイルから一行読み込む
			$line = fgets($fp);
			if($line !== FALSE) {
				$line = substr_replace($line, ",<center>", 32, 1);
				$wpos = strpos($line, ',', 33);
				$line = substr_replace($line, "</center>,", $wpos, 1);
				$num  = preg_replace( "/,/", "</TD><TD>", $line);
				echo "<TR>\n";
				echo "<TD scope=\"col\">{$num}</TD>\n";
				echo "</TR>\n";
			}
		}
		fclose($fp);
		echo "</tbody>\n</table>\n</form>";
	}
}

class HtmlBF extends HTML {
	function main($data, $hako) {
		global $init;
		$this_file = $init->baseDir . "/hako-bf.php";
		require_once(VIEWS.'/admin/bf.php');
	}
}

class HTMLKeep extends HTML {
	function main($data, $hako) {
		global $init;
		$this_file = $init->baseDir . "/hako-keep.php";
		require_once(VIEWS.'/admin/keep.php');
	}
}

///


class HtmlAlly extends HTML {
	//--------------------------------------------------
	// 初期画面
	//--------------------------------------------------
	function allyTop($hako, $data) {
		global $init;
		$this_file  = $init->baseDir . "/hako-ally.php";

		echo "<div class='row'>";
		echo "<div class='col-xs-12'>";
		echo "<h1>同盟管理ツール</h1>\n";

		if($init->allyUse) {
			echo <<<END
<input type="button" class="btn btn-default" value="同盟の結成・変更・解散・加盟・脱退はこちらから" onClick="JavaScript:location.replace('{$this_file}?JoinA=1')">
<h2>各同盟の状況</h2>
END;
		}
		$this->allyInfo($hako);

		echo "</div>";
		echo "</div>";

	}

	//--------------------------------------------------
	// 同盟の状況
	//--------------------------------------------------
	function allyInfo($hako, $num = 0) {
		global $init;
		$this_file  = $init->baseDir . "/hako-ally.php";

		$tag = "";
		$allyNumber = (int)$hako->allyNumber;
		if ( $allyNumber <= 0 ) {
			echo "同盟がありません。";
			return;
		}

		echo <<<END
占有率は、同盟加盟の<b>総人口</b>により算出されたものです。
<div id="IslandView" class="table-responsive">
<table class="table table-bordered">
<thead>
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
</thead>
END;

		for($i=0; $i<$allyNumber; $i++) {
			if($num && ($i != $hako->idToAllyNumber[$num])) {
				continue;
			}
			$ally = $hako->ally[$i];
			$j = $i + 1;
			$pop = $farm = $factory = $commerce = $mountain = $hatuden = $missiles = 0;

			$num = (int)$ally['number'];
			for($k=0; $k<$num; $k++) {
				$id = $ally['memberId'][$k];
				$island = $hako->islands[$hako->idToNumber[$id]];
				$pop      += $island['pop'];
				$farm     += $island['farm'];
				$factory  += $island['factory'];
				$commerce += $island['commerce'];
				$mountain += $island['mountain'];
				$hatuden  += $island['hatuden'];
			}

			$name      = ($num) ? "{$init->tagName_}{$ally['name']}{$init->_tagName}" : "<a href=\"{$this_file}?AmiOfAlly={$ally['id']}\">{$ally['name']}</a>";
			$pop       = $pop . $init->unitPop;
			$farm      = ($farm <= 0)     ? $init->notHave : $farm * 10 . $init->unitPop;
			$factory   = ($factory <= 0)  ? $init->notHave : $factory * 10 . $init->unitPop;
			$commerce  = ($commerce <= 0) ? $init->notHave : $commerce * 10 . $init->unitPop;
			$mountain  = ($mountain <= 0) ? $init->notHave : $mountain * 10 . $init->unitPop;
			$hatuden   = ($hatuden <= 0)  ? "0kw" : $hatuden * 1000 . kw;

			$ally['comment'] = isset($ally['comment']) ? $ally['comment'] : "";


			echo <<<END
<tbody>
	<tr>
		<th {$init->bgNumberCell} rowspan=2>{$init->tagNumber_}$j{$init->_tagNumber}</th>
		<td {$init->bgNameCell} rowspan=2>{$name}</td>
		<td {$init->bgMarkCell}><b><font color="{$ally['color']}">{$ally['mark']}</font></b></td>
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
		<td {$init->bgCommentCell} colspan=9>{$init->tagTH_}<a href="{$this_file}?Allypact={$ally['id']}">{$ally['oName']}</a>：{$init->_tagTH}{$ally['comment']}</td>
	</tr>
<tbody>
END;
		}
		echo <<<END
</table>
</div>
<p>※ 同盟の名前をクリックすると「同盟の情報」欄へ、盟主島の名前だと「コメント変更」欄へ移動します。</p>
END;

	}

	//--------------------------------------------------
	// 同盟の情報
	//--------------------------------------------------
	function amityOfAlly($hako, $data) {
		global $init;
		$this_file  = $init->baseDir . "/hako-ally.php";

		$num = $data['ALLYID'];
		$ally = $hako->ally[$hako->idToAllyNumber[$num]];
		$allyName = "<FONT COLOR=\"{$ally['color']}\"><B>{$ally['mark']}</B></FONT>{$ally['name']}";

		echo <<<END
<div class='text-center'>
	{$init->tagBig_}{$init->tagName_}{$allyName}{$init->_tagName}の情報{$init->_tagBig}<br>
</div>

<div ID='campInfo'>
END;
		// 同盟状況の表示
		if($ally['number']) {
			$this->allyInfo($hako, $num);
		}
		// メッセージ・盟約の表示
		if($ally['message'] != '') {
			$allyTitle = $ally['title'];
			if($allyTitle == '') {
				$allyTitle = '盟主からのメッセージ';
			}
			$allyMessage = $ally['message'];
			if($init->autoLink) {
				//preg_replace("/(^|[^=\\\"'])(http:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/", "<a href='$2' target='_blank'>$2</a>", $allyMessage);
				$allyMessage = Util::string_autolink($allyMessage);
			}
			echo <<<END
<hr>

<table class="table table-bordered" width="80%">
	<TR><TH {$init->bgTitleCell}>{$init->tagTH_}$allyTitle{$init->_tagTH}</TH></TR>
	<TR><TD {$init->bgCommentCell}><blockquote>$allyMessage</blockquote></TD></TR>
</table>
END;
		}
        // メンバー一覧の表示
		echo <<<END
<HR>
<TABLE class="table table-bordered">
	<TR>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}島{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->namePopulation}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameArea}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFunds}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFood}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</TH>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameCommercialScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerPlantScale}{$init->_tagTH}</th>
	</TR>
END;
		if(!$ally['number']) {
			echo "<TR><TH colspan=12>所属している島がありません！</TH></TR>";
		}
		foreach ($ally['memberId'] as $id) {
			$number = $hako->idToNumber[$id];
			if(!($number > -1)) continue;
			$island = $hako->islands[$number];
			$money = AllyUtil::aboutMoney($island['money']);
			$farm = $island['farm'];
			$factory = $island['factory'];
			$commerce = $island['commerce'];
			$mountain = $island['mountain'];
			$hatuden = $island['hatuden'];
            $ranking = $number + 1;
			$name = AllyUtil::islandName($island, $hako->ally, $hako->idToAllyNumber);
			if($island['absent']  == 0) {
				$name = "{$init->tagName_}<a href=\"{$init->baseDir}/hako-main.php?Sight={$island['id']}\">{$name}{$init->_tagName}</a>";
			} else {
				$name = "{$init->tagName2_}<a href=\"{$init->baseDir}/hako-main.php?Sight={$island['id']}\">{$name}</a>({$island['absent']}){$init->_tagName2}";
			}
			$farm = ($farm == 0) ? $init->notHave : "{$farm}0$init->unitPop";
			$factory = ($factory == 0) ? $init->notHave : "{$factory}0$init->unitPop";
			$commerce  = ($commerce == 0) ? $init->notHave : "{$commerce}0$init->unitPop";
			$mountain = ($mountain == 0) ? $init->notHave : "{$mountain}0$init->unitPop";
			$hatuden  = ($hatuden == 0) ? "0kw" : "{$hatuden}000kw";

			echo <<<END
<TR>
	<TH {$init->bgNumberCell}>{$init->tagNumber_}$ranking{$init->_tagNumber}</TH>
	<TD {$init->bgNameCell}>$name</TD>
	<TD {$init->bgInfoCell}>{$island['pop']}$init->unitPop</TD>
	<TD {$init->bgInfoCell}>{$island['area']}$init->unitArea</TD>
	<TD {$init->bgInfoCell}>$money</TD>
	<TD {$init->bgInfoCell}>{$island['food']}$init->unitFood</TD>
	<TD {$init->bgInfoCell}>$farm</TD>
	<TD {$init->bgInfoCell}>$factory</TD>
	<TD {$init->bgInfoCell}>$commerce</TD>
	<TD {$init->bgInfoCell}>$mountain</TD>
	<TD {$init->bgInfoCell}>$hatuden</TD>
</TR>
END;
		}
		echo "</TABLE>\n";
    }

	//--------------------------------------------------
	// 同盟コメントの変更
	//--------------------------------------------------
	function tempAllyPactPage($hako, $data) {
		global $init;
		$this_file  = $init->baseDir . "/hako-ally.php";

		$num = $data['ALLYID'];
		$ally = $hako->ally[$hako->idToAllyNumber[$num]];
		$allyMessage = $ally['message'];

		$allyMessage = str_replace("<br>", "\n", $allyMessage);
		$allyMessage = str_replace("&amp;", "&", $allyMessage);
		$allyMessage = str_replace("&lt;", "<", $allyMessage);
		$allyMessage = str_replace("&gt;", ">", $allyMessage);
		$allyMessage = str_replace("&quot;", "\"", $allyMessage);
		$allyMessage = str_replace("&#039;", "'", $allyMessage);

		$data['defaultPassword'] = isset($data['defaultPassword']) ? $data['defaultPassword'] : "";
		echo <<<END
<div class='text-center'>
	{$init->tagBig_}コメント変更（{$init->tagName_}{$ally['name']}{$init->_tagName}）{$init->_tagBig}<br>
</div>

<DIV ID='changeInfo'>
<table border=0 width=50%>
<tr>
	<td class="M">
		<FORM action="{$this_file}" method="POST">
			<B>盟主パスワードは？</B><BR>
			<INPUT TYPE="password" NAME="Allypact" VALUE="{$data['defaultPassword']}" SIZE=32 MAXLENGTH=32 class="f form-control">
			<INPUT TYPE="hidden"  NAME="ALLYID" VALUE="{$ally['id']}">
			<INPUT TYPE="submit" VALUE="送信" NAME="AllypactButton"><BR>

			<B>コメント</B><small>(全角{$init->lengthAllyComment}字まで：トップページの「各同盟の状況」欄に表示されます)</small>
			<INPUT TYPE="text" NAME="ALLYCOMMENT" VALUE="{$ally['comment']}" MAXLENGTH="50" class="form-control">

			<B>メッセージ・盟約など</B><small>(「同盟の情報」欄の上に表示されます)</small><BR>
			タイトル<small>(全角{$init->lengthAllyTitle}字まで)</small>
			<INPUT TYPE="text" NAME="ALLYTITLE"  VALUE="{$ally['title']}" MAXLENGTH="50" class="form-control">

			メッセージ<small>(全角{$init->lengthAllyMessage}字まで)</small>
			<TEXTAREA COLS=50 ROWS=16 NAME="ALLYMESSAGE" WRAP="soft" class="form-control">{$allyMessage}</TEXTAREA>
			<BR>
			「タイトル」を空欄にすると『盟主からのメッセージ』というタイトルになります。<BR>
			「メッセージ」を空欄にすると「同盟の情報」欄には何も表示されなくなります。
		</FORM>
	</td>
	</tr>
</table>
</DIV>
END;
	}

	//--------------------------------------------------
	// 同盟の結成・変更・解散・加盟・脱退
	//--------------------------------------------------
	function newAllyTop($hako, $data) {
		global $init;
		$this_file  = $init->baseDir . "/hako-ally.php";

		$adminMode = 0;

		$jsAllyList      = "";
		$jsAllyIdList    = "";
		$jsAllyMarkList  = "";
		$jsAllyColorList = "";

		$data['defaultPassword'] = isset($data['defaultPassword']) ? $data['defaultPassword'] : "";
		if(AllyUtil::checkPassword("", $data['defaultPassword'])) {
			// 管理者の判定は、お菓子のパスワード、盟主の変更可
			$adminMode = 1;
		} elseif(!$init->allyUse) {
			$this->allyTop($hako, $data);
		}

		$jsIslandList    = "";
		$num = (int)$hako->islandNumber;
		for($i=0; $i<$num; $i++) {
			$name = $hako->islands[$i]['name'];
			$name = preg_replace("/'/", "\'", $name);
			$id = $hako->islands[$i]['id'];
			$jsIslandList .= "island[$id] = '$name';\n";
		}
		$data['defaultID'] = isset($data['defaultID']) ? $data['defaultID'] : "";
		$n = '';
		$n = isset($hako->idToAllyNumber[$data['defaultID']]) ? $hako->idToAllyNumber[$data['defaultID']] : "";

		if($n == '') {
			$allyname = '';
			$defaultMark = $hako->ally[0];
			$defaultAllyId = '';
		} else {
			$allyname = $hako->ally[$n]['name'];
			$allyname = preg_replace("/'/", "\'", $allyname);
			$defaultMark = $hako->ally[$n]['mark'];
			$defaultAllyId = $hako->ally[$n]['id'];
		}
		$defaultMark = '';
		$markList = "";
		foreach ($init->allyMark as $aMark) {
			$s = '';
			if($aMark == $defaultMark) {
				$s = ' selected';
			}
			$markList .= "<option value=\"$aMark\"$s>$aMark</option>\n";
		}

		$hx = array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F');
		$colorList = array('','','','','','','');
		for($i=1; $i<7; $i++) {
			if($n == '') {
				$allycolor[$i] = '0';
			} else {
				$allycolor[$i] = substr($hako->ally[$n]['color'], $i, 1);
			}
			for($j=0; $j<count($hx); $j++) {
				$s = '';
				if($hx[$j] == $allycolor[$i]) {
					$s = ' selected';
				}
				$colorList[$i] .= "<option value=\"{$hx[$j]}\"$s>{$hx[$j]}</option>\n";
			}
		}

		$max = 201;
		if($hako->allyNumber) {
			$jsAllyList      = "var ally = [";
			$jsAllyIdList    = "var allyID = [";
			$jsAllyMarkList  = "var allyMark = [";
			$jsAllyColorList = "var allyColor = [";
			for($i=0; $i<count($hako->ally); $i++) {
				$s = "";
				if($hako->ally[$i]['id'] == $defaultAllyId) $s = ' selected';
				$allyList = "";
				$allyList .= "<option value=\"$i\"$s>{$hako->ally[$i]['name']}</option>\n";
				$jsAllyList .= "'{$hako->ally[$i]['name']}'";
				$jsAllyIdList .= "{$hako->ally[$i]['id']}";
				$jsAllyMarkList .= "'{$hako->ally[$i]['mark']}'";
				$jsAllyColorList .= "[";
				for($j=0; $j<6; $j++) {
					$jsAllyColorList .= '\'' . substr($hako->ally[$i]['color'], $j, 1) . '\'';
					if($j < 5) $jsAllyColorList .= ',';
				}
				$jsAllyColorList .= "]";
				if($i < count($hako->ally)) {
					$jsAllyList .= ",\n";
					$jsAllyIdList .= ",\n";
					$jsAllyMarkList .= ",\n";
					$jsAllyColorList .= ",\n";
				}
				if($max <= $hako->ally[$i]['id']) $max = $hako->ally[$i]['id'] + 1;
			}
			$jsAllyList .= "];\n";
			$jsAllyIdList .= "];\n";
			$jsAllyMarkList .= "];\n";
			$jsAllyColorList .= "];\n";
		}
		$str1 = ($adminMode ? '(メンテナンス)' : $init->allyJoinComUse) ? '' : '・加盟・脱退';
		$str2 = $adminMode ? '' : 'onChange="colorPack()" onClick="colorPack()"';
		$makeCost = $init->costMakeAlly ? "{$init->costMakeAlly}{$init->unitMoney}" : '無料';
		$keepCost = $init->costKeepAlly ? "{$init->costKeepAlly}{$init->unitMoney}" : '無料';
		$joinCost = isset($init->comCost[$init->comAlly]) ? "{$init->comCost[$init->comAlly]}{$init->unitMoney}" : '無料';
		$joinStr = $init->allyJoinComUse ? '' : "加盟・脱退の際の費用は、{$init->tagMoney_}$joinCost{$init->_tagMoney}です。<BR>";
		$str3 = $adminMode ? "特殊パスワードは？（必須）<BR>
<INPUT TYPE=\"password\" NAME=\"OLDPASS\" VALUE=\"{$data['defaultPassword']}\" SIZE=32 MAXLENGTH=32 class=f><BR>同盟" : "<div class='alert alert-info'><span class='attention'>(注意)</span><BR>
同盟の結成・変更の費用は、{$init->tagMoney_}{$makeCost}{$init->_tagMoney}です。<BR>
また、毎ターン必要とされる維持費は{$init->tagMoney_}$keepCost{$init->_tagMoney}です。<BR>
（維持費は同盟に所属する島で均等に負担することになります）<BR>
{$joinStr}
</div>

あなたの島は？（必須）<BR>
<SELECT NAME=\"ISLANDID\" {$str2}>
{$hako->islandList}
</SELECT><BR>あなた";
		$str0 = ($adminMode || ($init->allyUse == 1)) ? '結成・' : '';
		echo <<<END
<div class='text-center'>
{$init->tagBig_}同盟の{$str0}変更・解散{$str1}{$init->_tagBig}<br>
</div>

<DIV ID='changeInfo'>
<table border=0 width=50%><tr><td class="M"><P>
<FORM name="AcForm" action="{$this_file}" method="POST">
{$str3}のパスワードは？（必須）<BR>
<INPUT TYPE="password" NAME="PASSWORD" SIZE="32" MAXLENGTH="32" class="f" class="form-control">
END;
		if($hako->allyNumber) {
			$str4 = ($adminMode ? '・結成・変更' : $init->allyJoinComUse) ? '' : '・加盟・脱退';
			$str5 = ($adminMode || $init->allyJoinComUse) ? '' : '<INPUT TYPE="submit" VALUE="加盟・脱退" NAME="JoinAllyButton" class="btn btn-default">';
			echo <<<END
<BR>
<BR><B>［解散{$str4}］</B>
<BR>どの同盟ですか？<BR>
<SELECT NAME="ALLYNUMBER" onChange="allyPack()" onClick="allyPack()">
{$allyList}
</SELECT>
<BR>
<INPUT TYPE="submit" VALUE="解散" NAME="DeleteAllyButton" class="btn btn-danger">
{$str5}
<BR>
END;
		}
		$str7 = $adminMode ? "盟主島の変更(上のメニューで同盟を選択)<BR> or 同盟の新規作成(上のメニューは無効)<BR><SELECT NAME=\"ALLYID\"><option value=\"$max\">新規作成\n{$hako->islandList}</option></SELECT><BR>" : "<BR><B>［{$str0}変更］</B><BR>";
		echo <<<END
<BR>
{$str7}
同盟の名前（変更）<small>(全角{$init->lengthAllyName}字まで)</small><BR>
<INPUT TYPE="text" NAME="ALLYNAME" VALUE="$allyname" SIZE=32 MAXLENGTH=32 class="form-control"><BR>
マーク（変更）<BR>
<SELECT NAME="MARK" onChange="colorPack()" onClick="colorPack()">{$markList}</SELECT>
<br>
<ilayer name="PARENT_CTBL" width="100%" height="100%">
   <layer name="CTBL" width="200"></layer>
   <span id="CTBL"></span>
</ilayer>
マークの色コード（変更）<BR>
<TABLE class="table table-bordered table-condensed">
<TR>
	<TD align='center'>RED</TD>
	<TD align='center'>GREEN</TD>
	<TD align='center'>BLUE</TD>
</TR>
<TR>
	<TD>
		<SELECT NAME="COLOR1" onChange="colorPack()" onClick="colorPack()">{$colorList[1]}</SELECT>
		<SELECT NAME="COLOR2" onChange="colorPack()" onClick="colorPack()">{$colorList[2]}</SELECT>
	</TD>
	<TD>
		<SELECT NAME="COLOR3" onChange="colorPack()" onClick="colorPack()">{$colorList[3]}</SELECT>
		<SELECT NAME="COLOR4" onChange="colorPack()" onClick="colorPack()">{$colorList[4]}</SELECT>
	</TD>
	<TD>
		<SELECT NAME="COLOR5" onChange="colorPack()" onClick="colorPack()">{$colorList[5]}</SELECT>
		<SELECT NAME="COLOR6" onChange="colorPack()" onClick="colorPack()">{$colorList[6]}</SELECT>
	</TD>
</TR>
</TABLE>

<INPUT TYPE="submit" VALUE="結成 (変更)" NAME="NewAllyButton" class="btn btn-primary">
END;
		if(!$adminMode) {
			echo <<<END
<script>
function colorPack() {
	var island = new Array(128);
	{$jsIslandList}
	var a = document.AcForm.COLOR1.value;
	var b = document.AcForm.COLOR2.value;
	var c = document.AcForm.COLOR3.value;
	var d = document.AcForm.COLOR4.value;
	var e = document.AcForm.COLOR5.value;
	var f = document.AcForm.COLOR6.value;
	var mark = document.AcForm.MARK.value;
	var number = document.AcForm.ISLANDID.value;

	str = "#" + a + b + c + d + e + f;

	str = '表示サンプル：『<B><span class="number"><FONT color="' + str +'">' + mark + '</FONT></B>'
		+ island[number] + '島</span>』';

	document.getElementById("CTBL").innerHTML = str;

	return true;
}
function allyPack() {
	{$jsAllyList}
	{$jsAllyMarkList}
	{$jsAllyColorList}
	document.AcForm.ALLYNAME.value = ally[document.AcForm.ALLYNUMBER.value];
	document.AcForm.MARK.value     = allyMark[document.AcForm.ALLYNUMBER.value];
	document.AcForm.COLOR1.value   = allyColor[document.AcForm.ALLYNUMBER.value][0];
	document.AcForm.COLOR2.value   = allyColor[document.AcForm.ALLYNUMBER.value][1];
	document.AcForm.COLOR3.value   = allyColor[document.AcForm.ALLYNUMBER.value][2];
	document.AcForm.COLOR4.value   = allyColor[document.AcForm.ALLYNUMBER.value][3];
	document.AcForm.COLOR5.value   = allyColor[document.AcForm.ALLYNUMBER.value][4];
	document.AcForm.COLOR6.value   = allyColor[document.AcForm.ALLYNUMBER.value][5];
	colorPack();
	return true;
}
END;
		} else {
			echo <<<END

function colorPack() {
	var island = new Array(128);
	{$jsIslandList}
	var a = document.AcForm.COLOR1.value;
	var b = document.AcForm.COLOR2.value;
	var c = document.AcForm.COLOR3.value;
	var d = document.AcForm.COLOR4.value;
	var e = document.AcForm.COLOR5.value;
	var f = document.AcForm.COLOR6.value;
	var mark = document.AcForm.MARK.value;

	var str = "#" + a + b + c + d + e + f;

	str = '表示サンプル：『<B><span class="number"><FONT color="' + str +'">' + mark + '</FONT></B>'
		+ 'さんぷる島</span>』';

	document.getElementById("CTBL").innerHTML = str;

	return true;
}

function allyPack() {
	{$jsAllyList}
	{$jsAllyIdList}
	{$jsAllyMarkList}
	{$jsAllyColorList}
	document.AcForm.ALLYID.value   = allyID[document.AcForm.ALLYNUMBER.value];
	document.AcForm.ALLYNAME.value = ally[document.AcForm.ALLYNUMBER.value];
	document.AcForm.MARK.value     = allyMark[document.AcForm.ALLYNUMBER.value];
	document.AcForm.COLOR1.value   = allyColor[document.AcForm.ALLYNUMBER.value][0];
	document.AcForm.COLOR2.value   = allyColor[document.AcForm.ALLYNUMBER.value][1];
	document.AcForm.COLOR3.value   = allyColor[document.AcForm.ALLYNUMBER.value][2];
	document.AcForm.COLOR4.value   = allyColor[document.AcForm.ALLYNUMBER.value][3];
	document.AcForm.COLOR5.value   = allyColor[document.AcForm.ALLYNUMBER.value][4];
	document.AcForm.COLOR6.value   = allyColor[document.AcForm.ALLYNUMBER.value][5];
	colorPack();
	return true;
}
END;
		}
		echo <<<END
colorPack();
</script>
</form>

</td>
</tr>
</table>
</div>
END;
	}
}
