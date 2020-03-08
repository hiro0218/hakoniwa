<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlTop extends HTML {

    function __construct() {
		global $init;

		$this->this_file = $init->baseDir . "/hako-main.php";
    }

	function main($hako, $data) {
		global $init;
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
		require_once(VIEWS_PATH.'/top/main.php');

		// 各部門ランキング
		require_once(VIEWS_PATH.'/top/category-rank.php');

		// 同盟の状況
		if($hako->allyNumber) {
			require_once(VIEWS_PATH.'/top/ally-list.php');
		}

		// 各諸島の状況
		require_once(VIEWS_PATH.'/top/island-list.php');

		// Battle Fieldの状況
		require_once(VIEWS_PATH.'/top/bf-list.php');

		// 管理者登録モード
		if($init->registerMode) {
			require_once(VIEWS_PATH.'/top/register-mode.php');
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

		if ($sentinel == 0) {
			return;
		}

		echo '<table class="table table-bordered table-condensed">';

		for($i = $start; $i < $sentinel ; $i++) {
			$island        = $hako->islands[$i];
			$island['pop'] = ($island['pop'] <= 0) ? 1 : $island['pop'];

			$j            = $island['isBF'] ? '★' : $i + 1;
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
			for($e = 0; $e < count($init->EiseiName); $e++) {
				if (isset($eisei[$e])) {
					if($eisei[$e] > 0) {
						$eiseis .= "<img src=\"{$init->imgDir}/eisei{$e}.gif\" alt=\"{$init->EiseiName[$e]} {$eisei[$e]}%\" title=\"{$init->EiseiName[$e]} {$eisei[$e]}%\"> ";
					} else {
						$eiseis .= "";
					}
				}
			}

			$zins = "";
			for($z = 0; $z < count($init->ZinName); $z++) {
				if (isset($zin[$z])) {
					if($zin[$z] > 0) {
						$zins .= "<img src=\"{$init->imgDir}/zin{$z}.gif\" alt=\"{$init->ZinName[$z]}\" title=\"{$init->ZinName[$z]}\"> ";
					} else {
						$zins .= "";
					}
				}
			}

			$items = "";
			for($t = 0; $t < count($init->ItemName); $t++) {
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
			<h3><a href="{$this->this_file}?Sight={$id}">{$name}</a> <small>{$start}{$monster}{$soccer}</small></h3>
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
	}

	/**
	 * 島の登録と設定
	 * @param type $hako
	 * @param type $data
	 */
	function register(&$hako, $data = "") {
		require_once(VIEWS_PATH.'/conf/register.php');
	}

	/**
	 * 新しい島を探す
	 * @param  [type] $number [description]
	 * @return [type]         [description]
	 */
	function discovery($number) {
		global $init;

		require_once(VIEWS_PATH.'/conf/discovery.php');
	}

	/**
	 * 島の名前とパスワードの変更
	 */
	function changeIslandInfo($islandList = "") {
		global $init;

		require_once(VIEWS_PATH.'/conf/change/island-info.php');
	}

	/**
	 * オーナー名の変更
	 */
	function changeOwnerName($islandList = "") {
		require_once(VIEWS_PATH.'/conf/change/owner-name.php');
	}

	/**
	 * お知らせ
	 */
	function infoPrint() {
        global $init;

		require_once(VIEWS_PATH.'/log/info.php');
	}

}
