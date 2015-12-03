<?php
/**
 * 箱庭諸島 S.E - 各種ユーティリティ定義用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

class Util {
	//---------------------------------------------------
	// 資金の表示
	//---------------------------------------------------
	static function aboutMoney($money = 0) {
		global $init;

		if($init->moneyMode) {
			if($money < 500) {
				return "推定 500{$init->unitMoney}未満";
			} else {
				return "推定 " . round($money / 1000) . "000" . $init->unitMoney;
			}
		} else {
			return $money . $init->unitMoney;
		}
	}

	//---------------------------------------------------
	// 経験地からミサイル基地レベルを算出
	//---------------------------------------------------
	static function expToLevel($kind, $exp) {
		global $init;

		if($kind == $init->landBase) {
			// ミサイル基地
			for($i = $init->maxBaseLevel; $i > 1; $i--) {
				if($exp >= $init->baseLevelUp[$i - 2]) {
					return $i;
				}
			}
			return 1;
		} else {
			// 海底基地
			for($i = $init->maxSBaseLevel; $i > 1; $i--) {
				if($exp >= $init->sBaseLevelUp[$i - 2]) {
					return $i;
				}
			}
			return 1;
		}
	}

	//---------------------------------------------------
	// 怪獣の種類・名前・体力を算出
	//---------------------------------------------------
	static function monsterSpec($lv) {
		global $init;

		// 種類
		$kind = (int)($lv / 100);
		// 名前
		$name = $init->monsterName[$kind];
		// 体力
		$hp = $lv - ($kind * 100);
		return array( 'kind' => $kind, 'name' => $name, 'hp' => $hp );
	}
	//---------------------------------------------------
	// 島の名前から番号を算出
	//---------------------------------------------------
	static function  nameToNumber($hako, $name) {
		// 全島から探す
		for($i = 0; $i < $hako->islandNumber; $i++) {
			if(strcmp($name, "{$hako->islands[$i]['name']}") == 0) {
				return $i;
			}
		}
		// 見つからなかった場合
		return -1;
	}

	/**
	 * 島名を返す
	 * @param  [type] $island         [description]
	 * @param  [type] $ally           [description]
	 * @param  [type] $idToAllyNumber [description]
	 * @return [type]                 [description]
	 */
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

	/**
	 * パスワードチェック
	 * @param  string $p1 [description]
	 * @param  string $p2 [description]
	 * @return [type]     [description]
	 */
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
		if(strcmp($p1, Util::encode($p2)) == 0) {
			return true;
		}

		return false;
	}

	/**
	 * [checkSpecialPassword description]
	 * @param  string $p [description]
	 * @return [type]    [description]
	 */
	static function checkSpecialPassword($p = "") {
		global $init;

		// nullチェック
		if(empty($p)) {
			return false;
		}
		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			$specialPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		// 特殊パスワードチェック
		if(strcmp($specialPassword, crypt($p, 'sp')) == 0) {
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
	// 0 ～ num -1 の乱数生成
	//---------------------------------------------------
	static function random($num = 0) {
		if($num <= 1) {
			return 0;
		}
		return mt_rand(0, $num - 1);
	}


	//---------------------------------------------------
	// ランダムな座標を生成
	//---------------------------------------------------
	static function makeRandomPointArray() {
		global $init;

		$rx = $ry = array();
		for($i = 0; $i < $init->islandSize; $i++)
		for($j = 0; $j < $init->islandSize; $j++)
		$rx[$i * $init->islandSize + $j] = $j;

		for($i = 0; $i < $init->islandSize; $i++)
		for($j = 0; $j < $init->islandSize; $j++)
		$ry[$j * $init->islandSize + $i] = $j;

		for($i = $init->pointNumber; --$i;) {
			$j = Util::random($i + 1);
			if($i != $j) {
				$tmp = $rx[$i];
				$rx[$i] = $rx[$j];
				$rx[$j] = $tmp;
				$tmp = $ry[$i];
				$ry[$i] = $ry[$j];
				$ry[$j] = $tmp;
			}
		}
		return array($rx, $ry);
	}

	//---------------------------------------------------
	// ランダムな島の順序を生成
	//---------------------------------------------------
	static function randomArray($n = 1) {
		// 初期値
		for($i = 0; $i < $n; $i++) {
			$list[$i] = $i;
		}
		// シャッフル
		for($i = 0; $i < $n; $i++) {
			$j = Util::random($n - 1);
			if($i != $j) {
				$tmp = $list[$i];
				$list[$i] = $list[$j];
				$list[$j] = $tmp;
			}
		}
		return $list;
	}

	//---------------------------------------------------
	// コマンドを前にずらす
	//---------------------------------------------------
	static function slideFront(&$command, $number = 0) {
		global $init;

		// それぞれずらす
		array_splice($command, $number, 1);

		// 最後に資金繰り
		$command[$init->commandMax - 1] = array (
			'kind'   => $init->comDoNothing,
			'target' => 0,
			'x'      => 0,
			'y'      => 0,
			'arg'    => 0
		);
	}

	//---------------------------------------------------
	// コマンドを後にずらす
	//---------------------------------------------------
	static function slideBack(&$command, $number = 0) {
		global $init;

		// それぞれずらす
		if($number == count($command) - 1) {
			return;
		}
		for($i = $init->commandMax - 1; $i >= $number; $i--) {
			if ( preg_match('/[^0-9]/', $i) ) {
				$command[$i] = $command[$i - 1];
			}
		}
	}

	//---------------------------------------------------
	// 船情報のUnpack
	//---------------------------------------------------
	static function navyUnpack($lv) {
		global $init;

		// bit 意味
		//-----------
		//  7  島ID
		//  4  種類
		//  4  耐久力
		//  5  経験値
		//  4  フラグ
		// 24  合計

		$flag = $lv & 0x0f; $lv >>= 4;
		$exp  = $lv & 0x1f; $lv >>= 5;
		$hp   = $lv & 0x0f; $lv >>= 4;
		$kind = $lv & 0x0f; $lv >>= 4;
		$id   = $lv;

		return array($id, $kind, $hp, $exp, $flag);
	}

	//---------------------------------------------------
	// 船情報のPack
	//---------------------------------------------------
	static function navyPack($id, $kind, $hp, $exp, $flag) {
		global $init;

		// bit 意味
		//-----------
		//  7  島ID
		//  4  種類
		//  4  耐久力
		//  5  経験値
		//  4  フラグ
		// 24  合計

		$lv = 0;
		$lv |= $id   & 0x7f; $lv <<= 4;
		$lv |= $kind & 0x0f; $lv <<= 4;
		$lv |= $hp   & 0x0f; $lv <<= 5;
		$lv |= $exp  & 0x1f; $lv <<= 4;
		$lv |= $flag & 0x0f;

		return $lv;
	}

	//---------------------------------------------------
	// ファイルをロックする
	//---------------------------------------------------
	static function lock() {
		global $init;

		$fp = fopen("{$init->dirName}/lock.dat", "w");

		for($count = 0; $count < LOCK_RETRY_COUNT; $count++) {
			if(flock($fp, LOCK_EX)) {
				// ロック成功
				return $fp;
			}
			// 一定時間sleepし、ロックが解除されるのを待つ
			// 乱数時間sleepすることで、ロックが何度も衝突しないようにする
			usleep((LOCK_RETRY_INTERVAL - mt_rand(0, 300)) * 1000);
		}
		// ロック失敗
		fclose($fp);
		HakoError::lockFail();
		return FALSE;
	}

	//---------------------------------------------------
	// ファイルをアンロックする
	//---------------------------------------------------
	static function unlock($fp) {
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/**
	 * アラートタグを出力する
	 * @param  [type] $message [description]
	 * @param  string $status  [description]
	 * @return [type]          [description]
	 */
	static function makeTagMessage($message, $status = "success"){
		echo '<div class="alert alert-'. $status .' role="alert">';
		echo nl2br($message);
		echo '</div>';
	}

	/**
	 * ランダムな文字列を返す
	 * @param  integer $max [description]
	 * @return [type]       [description]
	 */
	static function rand_string($max = 32) {
		return substr(md5(uniqid(rand_number(), true)), 0, $max);
	}

	/**
	 * 文字列内のURLをリンクにする
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	static function string_autolink($string) {
		$string = preg_replace('/(^|[^\"\w\.\~\-\/\?\&\#\+\=\:\;\@\%\!])(https?\:\/\/[\w\.\~\-\/\?\&\#\+\=\:\;\@\%\!]+)/', '$1<a href="$2" target="_blank">$2</a>', $string);

		return $string;
	}
}
