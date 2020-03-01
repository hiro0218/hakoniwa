<?php
/**
 * 箱庭諸島 S.E - 島編集用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODEL_PATH.'/File/HakoEdit.php';
require_once PRESENTER_PATH.'/HtmlTop.php';
require_once PRESENTER_PATH.'/HtmlMap.php';
ini_set('display_errors', 0);

global $init;
$THIS_FILE = $init->baseDir . "/hako-edit.php";

class CgiImitation {
	public $mode = "";
	public $dataSet = array();
	//---------------------------------------------------
	// POST、GETのデータを取得
	//---------------------------------------------------
	function parseInputData() {
		global $init;

		if(empty($_POST)) {
            return;
        }

		$this->mode = isset($_POST['mode']) ? $_POST['mode'] : "";

        $this->dataSet = Util::getParsePostData();

        if(!empty($_POST['Sight'])) {
            $this->dataSet['ISLANDID'] = $_POST['Sight'];
        }
	}

	//---------------------------------------------------
	// COOKIEを取得
	//---------------------------------------------------
	function getCookies() {
		if(empty($_COOKIE)) {
            return;
        }

        foreach ($_COOKIE as $name => $value) {
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
                case "IMG":
                    $this->dataSet['defaultImg'] = $value;
                    break;
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
			HakoError::wrongPassword();
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
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameSuffix}{$init->_tagTH}</th>
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
			$farm = ($island['farm'] <= 0) ? $init->notHave : $island['farm'] * 10 . $init->unitPop;
			$factory = ($island['factory'] <= 0) ? $init->notHave : $island['factory'] * 10 . $init->unitPop;
			$mountain = ($island['mountain'] <= 0) ? $init->notHave : $island['mountain'] * 10 . $init->unitPop;
			$comment = $island['comment'];
			$comment_turn = $island['comment_turn'];
			$monster = '';
			if($island['monster'] > 0) {
				$monster = "<strong class=\"monster\">[怪獣{$island['monster']}体]</strong>";
			}
			$name = "";
			if($island['absent'] == 0) {
				$name = "{$init->tagName_}{$island['name']}{$init->nameSuffix}{$init->_tagName}";
			} else {
				$name = "{$init->tagName2_}{$island['name']}{$init->nameSuffix}({$island['absent']}){$init->_tagName2}";
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
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameSuffix}{$init->_tagTH}</th>
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
			HakoError::wrongPassword();
			return;
		}
		$html = new HtmlMap();
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
</script>

<div class="text-center">
	{$init->tagBig_}{$init->tagName_}{$island['name']}{$init->nameSuffix}{$init->_tagName} マップ・エディタ{$init->_tagBig}<br>
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
<div class="text-center">
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
<div class="text-center">
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
	ターン更新で他のデータへ反映されます。
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
			HakoError::wrongPassword();
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

 		Util::makeTagMessage("地形を変更しました", "success");

		// マップエディタの表示へ
		$this->editMap($hako, $data);
	}
}

class EditMain {

	function execute() {
		$hako = new HakoEdit();
		$cgi = new CgiImitation();
		$cgi->parseInputData();
		$cgi->getCookies();
		if(!$hako->readIslands($cgi)) {
			HTML::header();
			HakoError::noDataFile();
			HTML::footer();
			exit();
		}
		$cgi->setCookies();
		$edit = new Edit;

		switch($cgi->mode) {
			case "enter":
				$html = new HtmlTop();
				$html->header();
				$edit->main($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "list":
				$html = new HtmlTop();
				$html->header();
				$edit->main($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "map":
				$html = new HtmlTop();
				$html->header();
				$edit->editMap($hako, $cgi->dataSet);
				$html->footer();
				break;

			case "regist":
				$html = new HtmlTop();
				$html->header();
				$edit->register($hako, $cgi->dataSet);
				$html->footer();
				break;

			default:
				$html = new HtmlTop();
				$html->header();
				$edit->enter();
				$html->footer();
		}
		exit();
	}
}

$start = new EditMain();
$start->execute();
