<?php
/**
 * 箱庭諸島 S.E - 同盟管理用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/hako-cgi.php';
require_once PRESENTER.'/hako-html.php';

$init = new Init();

class MakeAlly {
	/**
	 * 結成・変更メイン
	 * @param  [type] $hako [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	function makeAllyMain($hako, $data) {
		global $init;

		$currentID = $data['ISLANDID'];
		$allyID = isset($data['ALLYID']) ? $data['ALLYID'] : "";
		$currentAnumber = isset($data['ALLYNUMBER']) ? $data['ALLYNUMBER'] : "";
		$allyName = htmlspecialchars($data['ALLYNAME']);
		$allyMark = $data['MARK'];
		$allyColor = "{$data['COLOR1']}{$data['COLOR2']}{$data['COLOR3']}{$data['COLOR4']}{$data['COLOR5']}{$data['COLOR6']}";
		$adminMode = 0;

		// パスワードチェック
		$data['OLDPASS'] = isset($data['OLDPASS']) ? $data['OLDPASS'] : "";
		if(AllyUtil::checkPassword("", $data['OLDPASS'])) {
			$adminMode = 1;
			if($allyID > 200) {
				$max = $allyID;
				if($hako->allyNumber) {
					for($i=0; $i < count($hako->ally); $i++) {
						if($max <= $hako->ally[$i]['id']) {
							$max = $hako->ally[$i]['id'] + 1;
						}
					}
				}
				$currentID = $max;
			} else {
				$currentID = $hako->ally[$currentAnumber]['id'];
			}
		}
		if(!$init->allyUse && !$adminMode) {
			HakoError::newAllyForbbiden();
			return;
		}
		// 同盟名があるかチェック
		if($allyName == '') {
			HakoError::newAllyNoName();
			return;
		}
		// 同盟名が正当かチェック
		if(preg_match("/[,\?\(\)\<\>\$]|^無人|^沈没$/", $allyName)) {
			// 使えない名前
			HakoError::newIslandBadName();
			return;
		}
		// 名前の重複チェック
		$currentNumber = $hako->idToNumber[$currentID];
		if(!($adminMode && ($allyID == '') && ($allyID < 200)) &&
			((AllyUtil::nameToNumber($hako, $allyName) != -1) ||
			((AllyUtil::aNameToId($hako, $allyName) != -1) && (AllyUtil::aNameToId($hako, $allyName) != $currentID)))) {
			// すでに結成ずみ
			HakoError::newAllyAlready();
			return;
		}
		// マークの重複チェック
		if(!($adminMode && ($allyID == '') && ($allyID < 200)) &&
			((AllyUtil::aMarkToId($hako, $allyMark) != -1) && (AllyUtil::aMarkToId($hako, $allyMark) != $currentID))) {
			// すでに使用ずみ
			HakoError::markAllyAlready();
			return;
		}
		// passwordの判定
		$island = $hako->islands[$currentNumber];
		if(!$adminMode && !AllyUtil::checkPassword($island['password'], $data['PASSWORD'])) {
			// password間違い
			HakoError::wrongPassword();
			return;
		}
		if(!$adminMode && $island['money'] < $init->costMakeAlly) {
			HakoError::noMoney();
			return;
		}
		$n = $hako->idToAllyNumber[$currentID];
		if($n != '') {
			if($adminMode && ($allyID != '') && ($allyID < 200)) {
				$allyMember = $hako->ally[$n]['memberId'];
				$aIsland = $hako->islands[$hako->idToNumber[$allyID]];
				$flag = 0;
				foreach ($allyMember as $id) {
					if($id == $allyID) {
						$flag = 1;
						break;
					}
				}
				if(!$flag) {
					if($aIsland['allyId'][0] == '') {
						$flag = 2;
					}
				}
				if(!$flag) {
					echo "変更できません。\n";
					return;
				}
				$hako->ally[$n]['id']       = $allyID;
				$hako->ally[$n]['oName']    = $aIsland['name'];
				if($flag == 2) {
					$hako->ally[$n]['password'] = $aIsland['password'];
					$hako->ally[$n]['score']    = $aIsland['pop'];
					$hako->ally[$n]['number'] ++;
					array_push($hako->ally[$n]['memberId'], $aIsland['id']);
					array_push($aIsland['allyId'], $aIsland['id']);
				}
			} else {
				// すでに結成ずみなら変更
			}
		} else {
			// 他の島の同盟に入っている場合は、結成できない
			$flag = 0;
			for($i = 0; $i < $hako->allyNumber; $i++) {
				$allyMember = $hako->ally[$i]['memberId'];
				foreach ($allyMember as $id) {
					if($id == $currentID) {
						$flag = 1;
						break;
					}
				}
				if($flag) {
					break;
				}
			}
			if($flag) {
				HakoError::otherAlready();
				return;
			}
			if(($init->allyUse == 2) && !$adminMode && !AllyUtil::checkPassword("", $data['PASSWORD'])) {
				HakoError::newAllyForbbiden();
				return;
			}
			// 新規
			$n = $hako->allyNumber;
			$hako->ally[$n]['id'] = $currentID;
			$memberId = array();
			if($allyID < 200) {
				$hako->ally[$n]['oName']    = $island['name'] . "島";
				$hako->ally[$n]['password'] = $island['password'];
				$hako->ally[$n]['number']   = 1;
				$memberId[0]                = $currentID;
				$hako->ally[$n]['score']    = $island['pop'];
			} else {
				$hako->ally[$n]['oName']    = '';
				$hako->ally[$n]['password'] = AllyUtil::encode($data['PASSWORD']);
				$hako->ally[$n]['number']   = 0;
				$hako->ally[$n]['score']    = 0;
			}
			$hako->ally[$n]['occupation']   = 0;
			$hako->ally[$n]['memberId']     = $memberId;
			$island['allyId']               = $memberId;
			$ext = array(0,);
			$hako->ally[$n]['ext']          = $ext;
			$hako->idToAllyNumber[$currentID] = $n;
			$hako->allyNumber++;
		}

		// 同盟の各種の値を設定
		$hako->ally[$n]['name']     = $allyName;
		$hako->ally[$n]['mark']     = $allyMark;
		$hako->ally[$n]['color']    = "$allyColor";

		// 費用をいただく
		if(!$adminMode) {
			$island['money'] -= $init->costMakeAlly;
		}
		// データ格納先へ
		$hako->islands[$currentNumber] = $island;

		// データ書き出し
		AllyUtil::allyOccupy($hako);
		AllyUtil::allySort($hako);
		$hako->writeAllyFile();

		// トップへ
		$html = new HtmlAlly();
		$html->allyTop($hako, $data);
	}

	//--------------------------------------------------
	// 解散
	//--------------------------------------------------
	function deleteAllyMain($hako, $data) {
		global $init;

		$currentID = $data['ISLANDID'];
		$currentAnumber = $data['ALLYNUMBER'];
		$currentNumber = $hako->idToNumber[$currentID];
		$island = $hako->islands[$currentNumber];
		$n = $hako->idToAllyNumber[$currentID];
		$adminMode = 0;

		// パスワードチェック
		$passCheck = isset($data['OLDPASS']) ? AllyUtil::checkPassword("", $data['OLDPASS']) : false;
		if ($passCheck) {
			$n = $currentAnumber;
			$currentID = $hako->ally[$n]['id'];
			$adminMode = 1;
		} else {
			// passwordの判定
			if(!(AllyUtil::checkPassword($island['password'], $data['PASSWORD']))) {
				// 島 Password 間違い
				HakoError::wrongPassword();
				return;
			}
			if(!(AllyUtil::checkPassword($hako->ally[$n]['password'], $data['PASSWORD']))) {
				// 同盟 Password 間違い
				HakoError::wrongPassword();
				return;
			}
			// 念のためIDもチェック
			if($hako->ally[$n]['id'] != $currentID) {
				HakoError::wrongAlly();
				return;
			}
		}
		$allyMember = $hako->ally[$n]['memberId'];

		if($adminMode && (($allyMember[0] != '') || ($n == ''))){
			echo "削除できません。\n";
			return;
		}
		foreach ($allyMember as $id) {
			$island = $hako->islands[$hako->idToNumber[$id]];
			$newId = array();
			foreach ($island['allyId'] as $aId) {
				if($aId != $currentID) {
					array_push($newId, $aId);
				}
			}
			$island['allyId'] = $newId;
		}
		$hako->ally[$n]['dead'] = 1;
		$hako->idToAllyNumber[$currentID] = '';
		$hako->allyNumber --;

		// データ格納先へ
		$hako->islands[$currentNumber] = $island;

		// データ書き出し
		AllyUtil::allyOccupy($hako);
		AllyUtil::allySort($hako);
		$hako->writeAllyFile();

		// トップへ
		$html = new HtmlAlly();
		$html->allyTop($hako, $data);
	}

	//--------------------------------------------------
	// 加盟・脱退
	//--------------------------------------------------
	function joinAllyMain($hako, $data) {
		global $init;

		$currentID = $data['ISLANDID'];
		$currentAnumber = $data['ALLYNUMBER'];
		$currentNumber = $hako->idToNumber[$currentID];
		$island = $hako->islands[$currentNumber];

		// パスワードチェック
		if(!(AllyUtil::checkPassword($island['password'], $data['PASSWORD']))) {
			// password間違い
			HakoError::wrongPassword();
			return;
		}

		// 盟主チェック
		if($hako->idToAllyNumber[$currentID]) {
			HakoError::leaderAlready();
			return;
		}
		// 複数加盟チェック
		$ally = $hako->ally[$currentAnumber];
		if($init->allyJoinOne && ($island['allyId'][0] != '') && ($island['allyId'][0] != $ally['id'])) {
			HakoError::otherAlready();
			return;
		}

		$allyMember = $ally['memberId'];
		$newAllyMember = array();
		$flag = 0;

		foreach ($allyMember as $id) {
			if(!($hako->idToNumber[$id] > -1)) {
			} elseif($id == $currentID) {
				$flag = 1;
			} else {
				array_push($newAllyMember, $id);
			}
		}

		if($flag) {
			// 脱退
			$newAlly = array();
			foreach ($island['allyId'] as $id) {
				if($id != $ally['id']) {
					array_push($newAlly, $id);
				}
			}
			$island['allyId'] = $newAlly;
			$ally['score'] -= $island['pop'];
			$ally['number'] --;
		} else {
			// 加盟
			array_push($newAllyMember, $currentID);
			array_push($island['allyId'], $ally['id']);
			$ally['score'] += $island['pop'];
			$ally['number'] ++;
		}
		$island['money'] -= $init->comCost[$init->comAlly];
		$ally['memberId'] = $newAllyMember;

		// データ格納先へ
		$hako->islands[$currentNumber] = $island;
		$hako->ally[$currentAnumber] = $ally;

		// データ書き出し
		AllyUtil::allyOccupy($hako);
		AllyUtil::allySort($hako);
		$hako->writeAllyFile();

		// トップへ
		$html = new HtmlAlly();
		$html->allyTop($hako, $data);
	}

	//--------------------------------------------------
	// 盟主コメントモード
	//--------------------------------------------------
	function allyPactMain($hako, $data) {
		$ally = $hako->ally[$hako->idToAllyNumber[$data['ALLYID']]];

		if(AllyUtil::checkPassword($ally['password'], $data['Allypact'])) {
			$ally['comment'] = AllyUtil::htmlEscape($data['ALLYCOMMENT']);
			$ally['title'] = AllyUtil::htmlEscape($data['ALLYTITLE']);
			$ally['message'] = AllyUtil::htmlEscape($data['ALLYMESSAGE'], 1);

			$hako->ally[$hako->idToAllyNumber[$data['ALLYID']]] = $ally;
			// データ書き出し
			$hako->writeAllyFile();

			// 変更成功
			Success::allyPactOK($ally['name']);
		} else {
			// password間違い
			HakoError::wrongPassword();
			return;
		}
	}

	//--------------------------------------------------
	// 箱庭データとのデータ統合処理
	//--------------------------------------------------
	function allyReComp(&$hako) {
		$rt1 = $this->allyDelete($hako);    // 盟主不在により同盟データから削除
		$rt2 = $this->allyMemberDel($hako);    // 放棄、無人島を同盟データから削除
		$rt3 = $this->allyPopComp($hako);    // 人口の再集計（ターン処理に組み込んでいないため）

		if($rt1 || $rt2 || $rt3) {
			// データ書き出し
			AllyUtil::allyOccupy($hako);
			AllyUtil::allySort($hako);
			$hako->writeAllyFile();

			// メッセージ出力
			Success::allyDataUp();
			return 1;
		}
		return 0;
	}

	//--------------------------------------------------
	// 盟主不在により同盟データから削除
	//--------------------------------------------------
	function allyDelete(&$hako) {
		$count = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$id = $hako->ally[$i]['id'];
			if(!($hako->idToNumber[$id] > -1)) {
				// 配列から削除
				$hako->ally[$i]['dead'] = 1;
				$hako->idToAllyNumber[$id] = '';
				$count ++;
			}
		}

		if($count) {
			$hako->allyNumber -= $count;
			if($hako->allyNumber < 0) {
				$hako->allyNumber = 0;
			}
			// データ格納先へ
			$hako->islands[$currentNumber] = $island;
			return 1;
		}
		return 0;
	}

	//--------------------------------------------------
	// 放棄、無人島を同盟データから削除
	//--------------------------------------------------
	function allyMemberDel(&$hako) {
		$flg = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$count = 0;
			$allyMember = $hako->ally[$i]['memberId'];
			$newAllyMember = array();
			foreach ($allyMember as $id) {
				if($hako->idToNumber[$id] > -1) {
					array_push($newAllyMember, $id);
					$count ++;
				}
			}
			if($count != $hako->ally[$i]['number']) {
				$hako->ally[$i]['memberId'] = $newAllyMember;
				$hako->ally[$i]['number'] = $count;
				$flg = 1;
			}
		}
		if($flg) {
			return 1;
		}
		return 0;
    }

	//--------------------------------------------------
	// 人口の再集計（ターンに組み込めば処理不要）
	//--------------------------------------------------
	function allyPopComp(&$hako) {
		$flg = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$score = 0;
			$allyMember = $hako->ally[$i]['memberId'];
			foreach ($allyMember as $id) {
				$island = $hako->islands[$hako->idToNumber[$id]];
				$score += $island['pop'];
			}
			if($score != $hako->ally[$i]['score']) {
				$hako->ally[$i]['score'] = $score;
				$flg = 1;
			}
		}
		if($flg) {
			return 1;
		}
		return 0;
	}
}

//------------------------------------------------------------
// Ally
//------------------------------------------------------------
class Ally extends AllyIO {
	var $islandList;    // 島リスト
	var $targetList;    // ターゲットの島リスト
	var $defaultTarget;    // 目標補足用ターゲット

	/**
	 * [readIslands description]
	 * @param  [type] $cgi [description]
	 * @return [type]      [description]
	 */
	function readIslands(&$cgi) {
		global $init;

		$m = $this->readIslandsFile();
		$this->islandList = $this->getIslandList($cgi->dataSet['defaultID']);

		if($init->targetIsland == 1) {
			// 目標の島 所有の島が選択されたリスト
			$this->targetList = $this->islandList;
		} else {
			// 順位がTOPの島が選択された状態のリスト
			$this->targetList = $this->getIslandList($cgi->dataSet['defaultTarget']);
		}
		return $m;
	}

	//--------------------------------------------------
	// 島リスト生成
	//--------------------------------------------------
	function getIslandList($select = 0) {
		global $init;

		$list = "";
		for($i = 0; $i < $this->islandNumber; $i++) {
			if($init->allyUse) {
				$name = AllyUtil::islandName($this->islands[$i], $this->ally, $this->idToAllyNumber); // 同盟マークを追加
			} else {
				$name = $this->islands[$i]['name'];
			}
			$id   = $this->islands[$i]['id'];

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
				$list .= "<option value=\"$id\" $s>{$name}{$init->nameSuffix}</option>\n";
			}
		}
		return $list;
	}
}

//------------------------------------------------------------
// AllyIO
//------------------------------------------------------------
class AllyIO {
	var $islandTurn;     // ターン数
	var $islandLastTime; // 最終更新時刻
	var $islandNumber;   // 島の総数
	var $islandNextID;   // 次に割り当てる島ID
	var $islands;        // 全島の情報を格納
	var $idToNumber;
	var $allyNumber;     // 同盟の総数
	var $ally;           // 各同盟の情報を格納
	var $idToAllyNumber; // 同盟

	//--------------------------------------------------
	// 同盟データ読みこみ
	//--------------------------------------------------
	function readAllyFile() {
		global $init;

		$fileName = "{$init->dirName}/{$init->allyData}";
		if(!is_file($fileName)) {
			return false;
		}
		$fp = fopen($fileName, "r");
		AllyUtil::lockr($fp);
		$this->allyNumber   = chop(fgets($fp, READ_LINE));
		if($this->allyNumber == '') {
			$this->allyNumber = 0;
		}
		for($i = 0; $i < $this->allyNumber; $i++) {
			$this->ally[$i] = $this->readAlly($fp);
			$this->idToAllyNumber[$this->ally[$i]['id']] = $i;
		}
		// 加盟している同盟のIDを格納
		for($i = 0; $i < $this->allyNumber; $i++) {
			$member = $this->ally[$i]['memberId'];
			$j = 0;
			foreach ($member as $id) {
				$n = $this->idToNumber[$id];
				if(!($n > -1)) {
					continue;
				}
				array_push($this->islands[$n]['allyId'], $this->ally[$i]['id']);
			}
		}
		AllyUtil::unlock($fp);
		fclose($fp);
		return true;
	}
	//--------------------------------------------------
	// 同盟ひとつ読みこみ
	//--------------------------------------------------
	function readAlly($fp) {
		$name       = chop(fgets($fp, READ_LINE));
		$mark       = chop(fgets($fp, READ_LINE));
		$color      = chop(fgets($fp, READ_LINE));
		$id         = chop(fgets($fp, READ_LINE));
		$ownerName  = chop(fgets($fp, READ_LINE));
		$password   = chop(fgets($fp, READ_LINE));
		$score      = chop(fgets($fp, READ_LINE));
		$number     = chop(fgets($fp, READ_LINE));
		$occupation = chop(fgets($fp, READ_LINE));
		$tmp        = chop(fgets($fp, READ_LINE));
		$allymember = explode(",", $tmp);
		$tmp        = chop(fgets($fp, READ_LINE));
		$ext        = explode(",", $tmp);                // 拡張領域
		$comment    = chop(fgets($fp, READ_LINE));
		$title      = chop(fgets($fp, READ_LINE));
		list($title, $message) = array_pad(explode("<>", $title), 2, NULL);

		return array(
			'name'       => $name,
			'mark'       => $mark,
			'color'      => $color,
			'id'         => $id,
			'oName'      => $ownerName,
			'password'   => $password,
			'score'      => $score,
			'number'     => $number,
			'occupation' => $occupation,
			'memberId'   => $allymember,
			'ext'        => $ext,
			'comment'    => $comment,
			'title'      => $title,
			'message'    => $message,
		);
	}
	//--------------------------------------------------
	// 同盟データ書き込み
	//--------------------------------------------------
	function writeAllyFile() {
		global $init;

		$fileName = "{$init->dirName}/{$init->allyData}";
		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "w");
		AllyUtil::lockw($fp);
		fputs($fp, $this->allyNumber . "\n");

		for($i = 0; $i < $this->allyNumber; $i++) {
			$this->writeAlly($fp, $this->ally[$i]);
		}
		AllyUtil::unlock($fp);
		fclose($fp);
		return true;
	}

	//--------------------------------------------------
	// 同盟ひとつ書き込み
	//--------------------------------------------------
	function writeAlly($fp, $ally) {
		fputs($fp, $ally['name'] . "\n");
		fputs($fp, $ally['mark'] . "\n");
		fputs($fp, $ally['color'] . "\n");
		fputs($fp, $ally['id'] . "\n");
		fputs($fp, $ally['oName'] . "\n");
		fputs($fp, $ally['password'] . "\n");
		fputs($fp, $ally['score'] . "\n");
		fputs($fp, $ally['number'] . "\n");
		fputs($fp, $ally['occupation'] . "\n");
		$allymember = join(",", $ally['memberId']);
		fputs($fp, $allymember . "\n");
		$ext = join(",", $ally['ext']);
		fputs($fp, $ext . "\n");
		if (isset($ally['comment'])) {
			fputs($fp, $ally['comment'] . "\n");
		}
		if ( isset($ally['title']) && isset($ally['message']) ) {
			fputs($fp, $ally['title'] . '<>' . $ally['message'] . "\n");
		}
	}

	//---------------------------------------------------
	// 全島データを読み込む
	//---------------------------------------------------
	function readIslandsFile() {
		global $init;

		$fileName = "{$init->dirName}/hakojima.dat";
		if(!is_file($fileName)) {
			return false;
		}
		$fp = fopen($fileName, "r");
		AllyUtil::lockr($fp);
		$this->islandTurn     = chop(fgets($fp, READ_LINE));
		$this->islandLastTime = chop(fgets($fp, READ_LINE));
		$this->islandNumber   = chop(fgets($fp, READ_LINE));
		$this->islandNextID   = chop(fgets($fp, READ_LINE));

		for($i = 0; $i < $this->islandNumber; $i++) {
			$this->islands[$i] = $this->readIsland($fp);
			$this->idToNumber[$this->islands[$i]['id']] = $i;
			$this->islands[$i]['allyId'] = array();
		}
		AllyUtil::unlock($fp);
		fclose($fp);

		if($init->allyUse) {
			$this->readAllyFile();
		}
		return true;
	}

	//---------------------------------------------------
	// 島ひとつ読み込む
	//---------------------------------------------------
	function readIsland($fp) {
		$name     = chop(fgets($fp, READ_LINE));

		list($name, $owner, $monster, $port, $passenger, $fishingboat, $tansaku, $senkan, $viking) = array_pad(explode(",", $name), 10, NULL);
		$id       = chop(fgets($fp, READ_LINE));
		list($id, $starturn) = explode(",", $id);
		$prize    = chop(fgets($fp, READ_LINE));
		$absent   = chop(fgets($fp, READ_LINE));
		$comment  = chop(fgets($fp, READ_LINE));
		list($comment, $comment_turn) = explode(",", $comment);
		$password = chop(fgets($fp, READ_LINE));
		$point    = chop(fgets($fp, READ_LINE));
		list($point, $pots) = explode(",", $point);
		$eisei    = chop(fgets($fp, READ_LINE));
		list($eisei0, $eisei1, $eisei2, $eisei3, $eisei4, $eisei5) = array_pad(explode(",", $eisei), 6, NULL);
		$zin      = chop(fgets($fp, READ_LINE));
		list($zin0, $zin1, $zin2, $zin3, $zin4, $zin5, $zin6) = array_pad(explode(",", $zin), 7, NULL);
		$item     = chop(fgets($fp, READ_LINE));
		list($item0, $item1, $item2, $item3, $item4, $item5, $item6, $item7, $item8, $item9, $item10, $item11, $item12, $item13, $item14, $item15, $item16, $item17, $item18, $item19) = array_pad(explode(",", $item), 20, NULL);
		$money    = chop(fgets($fp, READ_LINE));
		list($money, $lot, $gold) = array_pad(explode(",", $money), 3, NULL);
		$food     = chop(fgets($fp, READ_LINE));
		list($food, $rice) = explode(",", $food);
		$pop      = chop(fgets($fp, READ_LINE));
		list($pop, $peop) = explode(",", $pop);
		$area     = chop(fgets($fp, READ_LINE));
		$job      = chop(fgets($fp, READ_LINE));
		list($farm, $factory, $commerce, $mountain, $hatuden) = explode(",", $job);
		$power    = chop(fgets($fp, READ_LINE));
		list($taiji, $rena, $fire) = explode(",", $power);
		$tenki    = chop(fgets($fp, READ_LINE));
		$soccer   = chop(fgets($fp, READ_LINE));
		list($soccer, $team, $shiai, $kachi, $make, $hikiwake, $kougeki, $bougyo, $tokuten, $shitten) = array_pad(explode(",", $soccer), 10, NULL);

		return array(
			'name'         => $name,
			'owner'        => $owner,
			'id'           => $id,
			'starturn'     => $starturn,
			'prize'        => $prize,
			'absent'       => $absent,
			'comment'      => $comment,
			'comment_turn' => $comment_turn,
			'password'     => $password,
			'point'        => $point,
			'pots'         => $pots,
			'money'        => $money,
			'lot'          => $lot,
			'gold'         => $gold,
			'food'         => $food,
			'rice'         => $rice,
			'pop'          => $pop,
			'peop'         => $peop,
			'area'         => $area,
			'farm'         => $farm,
			'factory'      => $factory,
			'commerce'     => $commerce,
			'mountain'     => $mountain,
			'hatuden'      => $hatuden,
			'monster'      => $monster,
			'taiji'        => $taiji,
			'rena'         => $rena,
			'fire'         => $fire,
			'tenki'        => $tenki,
			'soccer'       => $soccer,
			'team'         => $team,
			'shiai'        => $shiai,
			'kachi'        => $kachi,
			'make'         => $make,
			'hikiwake'     => $hikiwake,
			'kougeki'      => $kougeki,
			'bougyo'       => $bougyo,
			'tokuten'      => $tokuten,
			'shitten'      => $shitten,
			'land'         => (isset($land)) ? $land : "",
			'landValue'    => (isset($landValue)) ? $landValue : "",
			'command'      => (isset($command)) ? $command : "",
			'port'         => (isset($port)) ? $port : "",
			'ship'         => array('passenger' => $passenger, 'fishingboat' => $fishingboat, 'tansaku' => $tansaku, 'senkan' => $senkan, 'viking' => $viking),
			'eisei'        => array(0 => $eisei0, 1 => $eisei1, 2 => $eisei2, 3 => $eisei3, 4 => $eisei4, 5 => $eisei5),
			'zin'          => array(0 => $zin0, 1 => $zin1, 2 => $zin2, 3 => $zin3, 4 => $zin4, 5 => $zin5, 6 => $zin6),
			'item'         => array(0 => $item0, 1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4, 5 => $item5, 6 => $item6, 7 => $item7, 8 => $item8, 9 => $item9, 10 => $item10, 11 => $item11, 12 => $item12, 13 => $item13, 14 => $item14, 15 => $item15, 16 => $item16, 17 => $item17, 18 => $item18, 19 => $item19),
		);
	}
}

class AllyUtil {
	//---------------------------------------------------
	// 資金の表示
	//---------------------------------------------------
	static function aboutMoney($money = 0) {
		global $init;

		if($init->moneyMode) {
			if($money < 500) {
				return "推定500{$init->unitMoney}未満";
			} else {
				return "推定" . round($money / 1000) . "000" . $init->unitMoney;
			}
		} else {
			return $money . $init->unitMoney;
		}
	}

	//---------------------------------------------------
	// 同盟の占有率の計算
	//---------------------------------------------------
	static function allyOccupy(&$hako) {
		$totalScore = 0;

		for($i=0; $i<$hako->allyNumber; $i++) {
			$totalScore += $hako->ally[$i]['score'];
		}
		for($i=0; $i<$hako->allyNumber; $i++) {
			if($totalScore != 0) {
				$hako->ally[$i]['occupation'] = (int)($hako->ally[$i]['score'] / $totalScore * 100);
			} else {
				$hako->ally[$i]['occupation'] = (int)(100 / $hako->allyNumber);
			}
		}
		return;
	}

	//---------------------------------------------------
	// 人口順にソート(同盟バージョン)
	//---------------------------------------------------
	static function allySort(&$hako) {
		usort($hako->ally, 'scoreComp');
	}

	//---------------------------------------------------
	// 島の名前から番号を算出
	//---------------------------------------------------
	static function nameToNumber($hako, $name) {
		// 全島から探す
		for($i = 0; $i < $hako->islandNumber; $i++) {
			if(strcmp($name, "{$hako->islands[$i]['name']}") == 0) {
				return $i;
			}
		}
		// 見つからなかった場合
		return -1;
    }

	//---------------------------------------------------
	// 同盟の名前からIDを得る
	//---------------------------------------------------
	static function aNameToId($hako, $name) {
		// 全島から探す
		for($i = 0; $i < $hako->allyNumber; $i++) {
			if($hako->ally[$i]['name'] == $name) {
				return $hako->ally[$i]['id'];
			}
		}
		// 見つからなかった場合
		return -1;
	}

	//---------------------------------------------------
	// 同盟のマークからIDを得る
	//---------------------------------------------------
	static function aMarkToId($hako, $mark) {
		// 全島から探す
		for($i = 0; $i < $hako->allyNumber; $i++) {
			if($hako->ally[$i]['mark'] == $mark) {
				return $hako->ally[$i]['id'];
			}
		}
		// 見つからなかった場合
		return -1;
	}

	//---------------------------------------------------
	// エスケープ文字の処理
	//---------------------------------------------------
	static function htmlEscape($s, $mode = 0) {
		$s = htmlspecialchars($s);
		$s = str_replace('"','&quot;', $s);
		$s = str_replace("'","&#039;", $s);

		if ($mode) {
			$s = str_replace("\r\n", "<br>", $s);
			$s = str_replace("\r", "<br>", $s);
			$s = str_replace("\n", "<br>", $s);
			$s = preg_replace("/(<br>){5,}/", "<br>", $s); // 大量改行対策
		}
		return $s;
	}

	//---------------------------------------------------
	// 島名を返す
	//---------------------------------------------------
	static function islandName($island, $ally, $idToAllyNumber) {
		$name = '';
		foreach ($island['allyId'] as $id) {
			$i = $idToAllyNumber[$id];
			$mark  = $ally[$i]['mark'];
			$color = $ally[$i]['color'];
			$name .= '<FONT COLOR="' . $color . '"><B>' . $mark . '</B></FONT> ';
		}
		$name .= $island['name'] . "島";
		return ($name);
	}

	//---------------------------------------------------
	// パスワードチェック
	//---------------------------------------------------
	static function checkPassword($p1 = "", $p2 = "") {
		global $init;

		// nullチェック
		if(empty($p2)) {
			return false;
		}
		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		// マスターパスワードチェック
		if(strcmp($masterPassword, crypt($p2, 'ma')) == 0) {
			return true;
		}
		if(strcmp($p1, AllyUtil::encode($p2)) == 0) {
			return true;
		}
		return false;
	}

	//---------------------------------------------------
	// パスワードのエンコード
	//---------------------------------------------------
	static function encode($s) {
		global $init;

		if($init->cryptOn) {
			return crypt($s, 'h2');
		} else {
			return $s;
		}
	}

	//---------------------------------------------------
	// ファイルをロックする(書き込み時)
	//---------------------------------------------------
	static function lockw($fp) {
		set_file_buffer($fp, 0);
		if(!flock($fp, LOCK_EX)) {
			HakoError::lockFail();
		}
		rewind($fp);
	}

	//---------------------------------------------------
	// ファイルをロックする(読み込み時)
	//---------------------------------------------------
	static function lockr($fp) {
		set_file_buffer($fp, 0);
		if(!flock($fp, LOCK_SH)) {
			HakoError::lockFail();
		}
		rewind($fp);
	}

	//---------------------------------------------------
	// ファイルをアンロックする
	//---------------------------------------------------
	static function unlock($fp) {
		flock($fp, LOCK_UN);
	}
}


//------------------------------------------------------------
// メイン処理
//------------------------------------------------------------
class Main {
	public $mode;
	public $dataSet = array();
	//--------------------------------------------------
	// モード分岐
	//--------------------------------------------------
	function execute() {
		global $init;

		$ally = new Ally();
		$cgi = new Cgi();

		$this->parseInputData();
		$cgi->getCookies();

		if(!$ally->readIslands($cgi)) {
			HTML::header();
			HakoError::noDataFile();
			HTML::footer();
			exit();
		}
		$cgi->setCookies();

		$html = new HtmlAlly();
		$com = new MakeAlly();
		$html->header();
		switch($this->mode) {
			case "JoinA":
				// 同盟の結成・変更・解散・加盟・脱退
				$html->newAllyTop($ally, $this->dataSet);
				break;

			case "newally":
				// 同盟の結成・変更
				$com->makeAllyMain($ally, $this->dataSet);
				break;

			case "delally":
				// 同盟の解散
				$com->deleteAllyMain($ally, $this->dataSet);
				break;

			case "inoutally":
				// 同盟の加盟・脱退
				$com->joinAllyMain($ally, $this->dataSet);
				break;

			case "Allypact":
				// コメントの変更
				$html->tempAllyPactPage($ally, $this->dataSet);
				break;

			case "AllypactUp":
				// コメントの更新
				$com->allyPactMain($ally, $this->dataSet);
				break;

			case "AmiOfAlly":
				// 同盟の情報
				$html->amityOfAlly($ally, $this->dataSet);
				break;

			default:
				// 箱庭データとのデータ統合処理（ターン処理に組み込んでいないため）
				if($com->allyReComp($ally)) {
					break;
				}
				$html->allyTop($ally, $this->dataSet);
			break;
		}
		$html->footer();
	}
	//---------------------------------------------------
	// POST、GETのデータを取得
	//---------------------------------------------------
	function parseInputData() {
		global $init;

		if ( isset($_POST['mode']) ) {
			$this->mode = $_POST['mode'];
		}
		if(!empty($_POST)) {
			foreach ($_POST as $name => $value) { 
				$value = str_replace(",", "", $value);
				// JcodeConvert($value, 0, 2);
				// $value = HANtoZEN_UTF8($value);

				if($init->stripslashes == true) {
					$this->dataSet["{$name}"] = stripslashes($value);
				} else {
					$this->dataSet["{$name}"] = $value;
				}
			}
			if( isset($this->dataSet['Allypact']) ) {
				$this->mode = "AllypactUp";
			}
			if(array_key_exists('NewAllyButton', $_POST)) {
				$this->mode = "newally";
			}
			if(array_key_exists('DeleteAllyButton', $_POST)) {
				$this->mode = "delally";
			}
			if(array_key_exists('JoinAllyButton', $_POST)) {
				$this->mode = "inoutally";
			}
		}
		if(!empty($_GET['AmiOfAlly'])) {
			$this->mode = "AmiOfAlly";
			$this->dataSet['ALLYID'] = $_GET['AmiOfAlly'];
		}
		if(!empty($_GET['Allypact'])) {
			$this->mode = "Allypact";
			$this->dataSet['ALLYID'] = $_GET['Allypact'];
		}
		if(!empty($_GET['JoinA'])) {
			$this->mode = "JoinA";
			$this->dataSet['ALLYID'] = $_GET['JoinA'];
		}
	}
}

$start = new Main();
$start->execute();

// 人口を比較、同盟一覧用
function scoreComp($x, $y) {
	if($x['dead'] == 1) {
		// 死滅フラグが立っていれば後ろへ
		return +1;
	}
	if($y['dead'] == 1) {
		return -1;
	}
	if($x['score'] == $y['score']) {
		return 0;
	}
	return ($x['score'] > $y['score']) ? -1 : +1;
}
