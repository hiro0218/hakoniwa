<?php
/**
 * 箱庭諸島 S.E - 新規作成用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */
// turn.php

class Make {
	//---------------------------------------------------
	// 島の新規作成モード
	//---------------------------------------------------
	function newIsland($hako, $data) {
		global $init;

		$log = new Log();
		if($hako->islandNumber >= $init->maxIsland) {
			Error::newIslandFull();
			return;
		}
		if(empty($data['ISLANDNAME'])) {
			Error::newIslandNoName();
			return;
		}
		// 名前が正当化チェック
		if(preg_match("/[,?()<>$]/", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "無人") == 0) {
			Error::newIslandBadName();
			return;
		}
		// 名前の重複チェック
		if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
			Error::newIslandAlready();
			return;
		}
		// パスワードの存在判定
		if(empty($data['PASSWORD'])) {
			Error::newIslandNoPassword();
			return;
		}
		if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
			Error::wrongPassword();
			return;
		}
		// 新しい島の番号を決める
		$newNumber = $hako->islandNumber;
		$hako->islandNumber++;
		$hako->islandNumberNoBF++;
		$hako->islandNumberNoKP++;
		$island = $this->makeNewIsland();

		// 島の番号の使いまわし
		$safety = 0;
		while(isset($hako->idToNumber[$hako->islandNextID])) {
			$hako->islandNextID++;
			if($hako->islandNextID > 250) $hako->islandNextID = 1;
			$safety++;
			if($safety == 250) break;
		}

		// 各種の値を設定
		$island['name'] = htmlspecialchars($data['ISLANDNAME']);
		$island['owner'] = htmlspecialchars($data['OWNERNAME']);
		$island['id'] = $hako->islandNextID;
		$hako->islandNextID++;
		$island['starturn'] = $hako->islandTurn;
		$island['isBF'] = $island['keep'] = 0;
		$island['absent'] = $init->giveupTurn - 3;
		$island['comment'] = '(未登録)';
		$island['comment_turn'] = $hako->islandTurn;
		$island['password'] = Util::encode($data['PASSWORD']);
		$island['tenki'] = 1;
		$island['team'] = $island['shiai'] = $island['kachi'] = $island['make'] = $island['hikiwake'] = $island['kougeki'] = $island['bougyo'] = $island['tokuten'] = $island['shitten'] = 0;

		Turn::estimate($hako, $island);
		if ( $hako->islandNumberBF > 0 ) {
			for ( $i = 0; $i < $hako->islandNumberBF; $i++ ) {
				$hako->islands[$newNumber - $i] = $hako->islands[$newNumber - $i - 1];
			}
			$hako->islands[$newNumber - $hako->islandNumberBF] = $island;
		} else {
			$hako->islands[$newNumber] = $island;
		}
		$hako->writeIslandsFile($island['id']);
		$log->discover($island['id'], $island['name']);
		$htmlMap = new HtmlMap();
		$htmlMap->newIslandHead($island['name']);
		$htmlMap->islandInfo($island, $newNumber);
		$htmlMap->islandMap($hako, $island, 1, $data);

	}
	//---------------------------------------------------
	// 新しい島を作成する
	//---------------------------------------------------
	function makeNewIsland() {
		global $init;

		$command = array();
		// 初期コマンド生成
		for($i = 0; $i < $init->commandMax; $i++) {
			$command[$i] = array (
				'kind'   => $init->comDoNothing,
				'target' => 0,
				'x'      => 0,
				'y'      => 0,
				'arg'    => 0,
			);
		}

		$land = array();
		$landValue = array();

		if ($init->initialLand) {
			// 初期島データファイル使用モード
			// 基本形を作成
			$fp_i = fopen($init->initialLand, "r");
			$offset = 7; // 一対のデータが何文字か
			for($y = 0; $y < $init->islandSize; $y++) {
				$line = chop(fgets($fp_i, READ_LINE));
				for($x = 0; $x < $init->islandSize; $x++) {
					$l = substr($line, $x * $offset, 2);
					$v = substr($line, $x * $offset + 2, 5);
					$land[$x][$y] = hexdec($l);
					$landValue[$x][$y] = hexdec($v);
				}
			}
			fclose($fp_i);
		} else if ($init->initialSize) {
			// 初期面積統一モード
			// 基本形を作成
			for($y = 0; $y < $init->islandSize; $y++) {
				for($x = 0; $x < $init->islandSize; $x++) {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				}
			}

			// 4*4に荒地を配置
			$center = $init->islandSize / 2 - 1;
			for($y = $center -1; $y < $center + 3; $y++) {
				for($x = $center - 1; $x < $center + 3; $x++) {
					$land[$x][$y] = $init->landWaste;
				}
			}
			// 島発見時の面積固定
			$size = 16;

			// 8*8範囲内に陸地を増殖
			while($size < $init->initialSize) {
				$x = Util::random(8) + $center - 3;
				$y = Util::random(8) + $center - 3;
				if(Turn::countAround($land, $x, $y, 7, array($init->landSea)) != 7) {
					// 周りに陸地がある場合、浅瀬にする
					// 浅瀬は荒地にする
					// 荒地は平地にする
					if($land[$x][$y] == $init->landSea) {
						if($landValue[$x][$y] == 1) {
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							$size++;
						} else {
							if($land[$x][$y] == $init->landWaste) {
								$land[$x][$y] = $init->landPlains;
								$landValue[$x][$y] = 0;
							} else {
								if($landValue[$x][$y] == 1) {
									$land[$x][$y] = $init->landWaste;
									$landValue[$x][$y] = 0;
								} else {
									$landValue[$x][$y] = 1;
								}
							}
						}
					}
				}
			}
			// 森を作る
			$count = 0;
			while($count < 4) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこがすでに森でなければ、森を作る
				if($land[$x][$y] != $init->landForest) {
					$land[$x][$y] = $init->landForest;
					$landValue[$x][$y] = 5; // 最初は500本
					$count++;
				}
			}
			$count = 0;
			while($count < 2) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこが森か町でなければ、町を作る
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landTown;
					$landValue[$x][$y] = 5; // 最初は500人
					$count++;
				}
			}
			// 山を作る
			$count = 0;
			while($count < 1) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこが森か町でなければ、町を作る
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landMountain;
					$landValue[$x][$y] = 0; // 最初は採掘場なし
					$count++;
				}
			}
			// 基地を作る
			$count = 0;
			while($count < 1) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこが森か町か山でなければ、基地
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest) &&
					 ($land[$x][$y] != $init->landMountain)) {
					$land[$x][$y] = $init->landBase;
					$landValue[$x][$y] = 0;
					$count++;
				}
			}
		} else {
			// 通常モード
			// 基本形を作成
			for($y = 0; $y < $init->islandSize; $y++) {
				for($x = 0; $x < $init->islandSize; $x++) {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				}
			}
			// 4*4に荒地を配置
			$center = $init->islandSize / 2 - 1;
			for($y = $center -1; $y < $center + 3; $y++) {
				for($x = $center - 1; $x < $center + 3; $x++) {
					$land[$x][$y] = $init->landWaste;
				}
			}
			// 8*8範囲内に陸地を増殖
			for($i = 0; $i < 120; $i++) {
				$x = Util::random(8) + $center - 3;
				$y = Util::random(8) + $center - 3;
				if(Turn::countAround($land, $x, $y, 7, array($init->landSea)) != 7) {
					// 周りに陸地がある場合、浅瀬にする
					// 浅瀬は荒地にする
					// 荒地は平地にする
					if($land[$x][$y] == $init->landWaste) {
						$land[$x][$y] = $init->landPlains;
						$landValue[$x][$y] = 0;
					} else {
						if($landValue[$x][$y] == 1) {
							$land[$x][$y] = $init->landWaste;
							$landValue[$x][$y] = 0;
						} else {
							$landValue[$x][$y] = 1;
						}
					}
				}
			}
			// 森を作る
			$count = 0;
			while($count < 4) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこがすでに森でなければ、森を作る
				if($land[$x][$y] != $init->landForest) {
					$land[$x][$y] = $init->landForest;
					$landValue[$x][$y] = 5; // 最初は500本
					$count++;
				}
			}
			$count = 0;
			while($count < 2) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこが森か町でなければ、町を作る
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landTown;
					$landValue[$x][$y] = 5; // 最初は500人
					$count++;
				}
			}
			// 山を作る
			$count = 0;
			while($count < 1) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこが森か町でなければ、町を作る
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landMountain;
					$landValue[$x][$y] = 0; // 最初は採掘場なし
					$count++;
				}
			}
			// 基地を作る
			$count = 0;
			while($count < 1) {
				// ランダム座標
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;

				// そこが森か町か山でなければ、基地
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest) &&
					 ($land[$x][$y] != $init->landMountain)) {
					$land[$x][$y] = $init->landBase;
					$landValue[$x][$y] = 0;
					$count++;
				}
			}
		}
		return array (
			'money'     => $init->initialMoney,
			'food'      => $init->initialFood,
			'land'      => $land,
			'landValue' => $landValue,
			'command'   => $command,
			'prize'     => '0,0,',
			'taiji'     => 0,
		);
	}

	//---------------------------------------------------
	// コメント更新
	//---------------------------------------------------
	function commentMain($hako, $data) {
		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];

		// パスワード
		if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		// メッセージを更新
		$island['comment'] = htmlspecialchars($data['MESSAGE']);
		$island['comment_turn'] = $hako->islandTurn;
		$hako->islands[$num] = $island;

		// データの書き出し
		$hako->writeIslandsFile();

		// コメント更新メッセージ
		Success::Comment();

		// owner modeへ
		if($data['DEVELOPEMODE'] == "cgi") {
			$html = new HtmlMap();
		} else {
			$html = new HtmlMapJS();
		}
		$html->owner($hako, $data);
	}


	//---------------------------------------------------
	// 情報変更モード
	//---------------------------------------------------
	function changeMain($hako, $data) {
		global $init;
		$log = new Log();

		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];

		// パスワードチェック
		if(Util::checkSpecialPassword($data['OLDPASS'])) {
			// 特殊パスワード
			if(preg_match("/^無人$/", $data['ISLANDNAME'])) {
				// 島の強制削除
				$this->deleteIsland($hako, $data);
				Success::deleteIsland($name);
				return;
			} else {
				$island['money'] = $init->maxMoney;
				$island['food'] = $init->maxFood;
			}
		} elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		// 確認用パスワード
		if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		if(!empty($data['ISLANDNAME'])) {
			// 名前変更の場合
			// 名前が正当かチェック
			if(preg_match("/[,?()<>$]/", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "無人") == 0) {
				Error::newIslandBadName();
				return;
			}
			// 名前の重複チェック
			if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
				Error::newIslandAlready();
				return;
			}
			if($island['money'] < $init->costChangeName) {
				// 金が足りない
				Error::changeNoMoney();
				return;
			}
			// 代金
			if(!Util::checkSpecialPassword($data['OLDPASS'])) {
				$island['money'] -= $init->costChangeName;
			}
			// 名前を変更
			$log->changeName($island['name'], $data['ISLANDNAME']);
			$island['name'] = $data['ISLANDNAME'];
			$flag = 1;
		}
		// password変更の場合
		if(!empty($data['PASSWORD'])) {
			// パスワードを変更
			$island['password'] = Util::encode($data['PASSWORD']);
			$flag = 1;
		}
		if(($flag == 0) && (strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0)) {
			// どちらも変更されていない
			Error::changeNothing();
			return;
		}
		$hako->islands[$num] = $island;
		// データ書き出し
		$hako->writeIslandsFile($id);

		// 変更成功
		Success::change();
	}

	//---------------------------------------------------
	// オーナ名変更モード
	//---------------------------------------------------
	function changeOwnerName($hako, $data) {
		global $init;

		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];

		// パスワードチェック
		if(Util::checkSpecialPassword($data['OLDPASS'])) {
			// 特殊パスワード
			$island['money'] = $init->maxMoney;
			$island['food'] = $init->maxFood;
		} elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		$island['owner'] = htmlspecialchars($data['OWNERNAME']);
		$hako->islands[$num] = $island;
		// データ書き出し
		$hako->writeIslandsFile($id);

		// 変更成功
		Success::change();
	}

	//---------------------------------------------------
	// コマンドモード
	//---------------------------------------------------
	function commandMain($hako, $data) {
		global $init;

		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];

		// パスワード
		if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		// モードで分岐
		$command = $island['command'];


		if(strcmp($data['COMMANDMODE'], 'delete') == 0) {
			Util::slideFront($command, $data['NUMBER']);
			Success::commandDelete();

		} else if(($data['COMMAND'] == $init->comAutoPrepare) ||
				  ($data['COMMAND'] == $init->comAutoPrepare2)) {
			// フル整地、フル地ならし
			// 座標配列を作る
			$r = Util::makeRandomPointArray();
			$rpx = $r[0];
			$rpy = $r[1];
			$land = $island['land'];
			// コマンドの種類決定
			switch ($data['COMMAND']){
				case $init->comAutoPrepare:
					$kind = $init->comPrepare;
					break;
				case $init->comAutoPrepare2:
					$kind = $init->comPrepare2;
					break;
			}
//			$kind = $init->comPrepare;
//			if($data['COMMAND'] == $init->comAutoPrepare2) {
//				$kind = $init->comPrepare2;
//			}
			$i = $data['NUMBER'];
			$j = 0;
			while(($j < $init->pointNumber) && ($i < $init->commandMax)) {
				$x = $rpx[$j];
				$y = $rpy[$j];
				if($land[$x][$y] == $init->landWaste) {
					Util::slideBack($command, $i);
					$command[$i] = array (
						'kind'   => $kind,
						'target' => 0,
						'x'      => $x,
						'y'      => $y,
						'arg'    => 0,
					);
					$i++;
				}
				$j++;
			}
			Success::commandAdd();

		} else if ($data['COMMAND'] == $init->comAutoReclaim) {
			$r = Util::makeRandomPointArray();
			$rpx = $r[0];
			$rpy = $r[1];
			$land = $island['land'];
			$landValue = $island['landValue'];

			$i = $data['NUMBER'];
			$j = 0;
			while(($j < $init->pointNumber) && ($i < $init->commandMax)) {
				$x = $rpx[$j];
				$y = $rpy[$j];
				$kind = $land[$x][$y];
				$lv = $landValue[$x][$y];

				if (($kind == $init->landSea) && ($lv == 1)) {
					Util::slideBack($command, $i);
					$command[$i] = array (
						'kind'   => $init->comReclaim,
						'target' => 0,
						'x'      => $x,
						'y'      => $y,
						'arg'    => 0,
					);
					$i++;
				}
				$j++;
			}
/*
    } elsif($HcommandKind == $HcomAutoDestroy) {
        # 浅瀬掘削
        makeRandomPointArray();
        my($land) = $island->{'land'};
        my($landValue) = $island->{'landValue'};

        my($x, $y, $kind, $lv, $i, $n);
        $n = 0;
        for ($i = 0; ($i < $HpointNumber) && ($n < $HcommandMax); $i++) {
            $x = $Hrpx[$i];
            $y = $Hrpy[$i];
            $kind = $land->[$x][$y];
            $lv = $landValue->[$x][$y];

            if (($kind == $HlandSea) && ($lv == 1)) {
                # 浅瀬
                slideBack($command, $HcommandPlanNumber);
                $command->[$HcommandPlanNumber] = {
                    'kind' => $HcomDestroy, # 掘削
                    'target' => 0,
                    'x' => $x,
                    'y' => $y,
                    'arg' => 0
                    };
                $n++;
            }
        }
        tempCommandAdd();
    } elsif($HcommandKind == $HcomAutoSellTree) {
        # 伐採
        # （数量×２００本より多い森だけが対象）
        makeRandomPointArray();
        my($land) = $island->{'land'};
        my($landValue) = $island->{'landValue'};

        my($x, $y, $kind, $lv, $i, $n);
        $n = 0;
        for ($i = 0; ($i < $HpointNumber) && ($n < $HcommandMax - 1); $i++) {
            $x = $Hrpx[$i];
            $y = $Hrpy[$i];
            $kind = $land->[$x][$y];
            $lv = $landValue->[$x][$y];

            if (($kind == $HlandForest) && ($lv > $HcommandArg * 2)) {
                # 森
                slideBack($command, $HcommandPlanNumber);
                $command->[$HcommandPlanNumber] = {
                    'kind' => $HcomSellTree, # 伐採
                    'target' => 0,
                    'x' => $x,
                    'y' => $y,
                    'arg' => 0
                    };
                $n += 2;
            }
        }
        tempCommandAdd();
    } elsif($HcommandKind == $HcomAutoForestry) {
        # 伐採と植林
        # （数量×２００本より多い森だけが対象）
        makeRandomPointArray();
        my($land) = $island->{'land'};
        my($landValue) = $island->{'landValue'};

        my($x, $y, $kind, $lv, $i, $n);
        $n = 0;
        for ($i = 0; ($i < $HpointNumber) && ($n < $HcommandMax - 1); $i++) {
            $x = $Hrpx[$i];
            $y = $Hrpy[$i];
            $kind = $land->[$x][$y];
            $lv = $landValue->[$x][$y];

            if (($kind == $HlandForest) && ($lv > $HcommandArg * 2)) {
                # 森
                slideBack($command, $HcommandPlanNumber);
                $command->[$HcommandPlanNumber] = {
                    'kind' => $HcomPlant, # 植林
                    'target' => 0,
                    'x' => $x,
                    'y' => $y,
                    'arg' => 0
                    };
                slideBack($command, $HcommandPlanNumber);
                $command->[$HcommandPlanNumber] = {
                    'kind' => $HcomSellTree, # 伐採
                    'target' => 0,
                    'x' => $x,
                    'y' => $y,
                    'arg' => 0
                    };
                $n += 2;
            }
        }
        tempCommandAdd();
 */
		} else if($data['COMMAND'] == $init->comAutoDelete) {
			// 全消し
			for($i = 0; $i < $init->commandMax; $i++) {
				Util::slideFront($command, 0);
			}
			Success::commandDelete();
		} else {
			if(strcmp($data['COMMANDMODE'], 'insert') == 0) {
				Util::slideBack($command, $data['NUMBER']);
			}
			Success::commandAdd();
			// コマンドを登録
			$command[$data['NUMBER']] = array (
				'kind'   => $data['COMMAND'],
				'target' => $data['TARGETID'],
				'x'      => $data['POINTX'],
				'y'      => $data['POINTY'],
				'arg'    => $data['AMOUNT'],
			);
		}

		// データの書き出し
		$island['command'] = $command;
		$hako->islands[$num] = $island;
		$hako->writeIslandsFile($island['id']);

		// owner modeへ
		$html = new HtmlMap();
		$html->owner($hako, $data);
	}

	//---------------------------------------------------
	// 島の強制削除
	//---------------------------------------------------
	function deleteIsland($hako, $data) {
		global $init;

		$log = new Log();
		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];

		// 島テーブルの操作
		$island['point'] = 0;
		$island['pop'] = 0;
		$island['dead'] = 1;
		$tmpid = $island['id'];
		$log->deleteIsland($tmpid, $island['name']);
		if(is_file("{$init->dirName}/island.{$tmpid}")) {
			unlink("{$init->dirName}/island.{$tmpid}");
		}
		// メインデータの操作
		$hako->islands[$num] = $island;
		Turn::islandSort($hako); // 削除する島を最下位に移動
		$hako->islandNumber -= 1; // 最下位削除

		// データ書き出し
		$hako->writeIslandsFile($id);
	}
}

//--------------------------------------------------------------------
class MakeJS extends Make {

	//---------------------------------------------------
	// コマンドモード
	//---------------------------------------------------
	function commandMain($hako, $data) {
		global $init;

		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];

		// パスワード
		if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		// モードで分岐
		$command = $island['command'];
		$comary = explode(" " , $data['COMARY']);

		for($i = 0; $i < $init->commandMax; $i++) {
			$pos = $i * 5;
			$kind   = $comary[$pos];
			$x      = $comary[$pos + 1];
			$y      = $comary[$pos + 2];
			$arg    = $comary[$pos + 3];
			$target = $comary[$pos + 4];

			// コマンド登録
			if($kind == 0) {
				$kind = $init->comDoNothing;
			}
			$command[$i] = array (
				'kind'   => $kind,
				'x'      => $x,
				'y'      => $y,
				'arg'    => $arg,
				'target' => $target
			);
		}
		Success::commandAdd();

		// データの書き出し
		$island['command'] = $command;
		$hako->islands[$num] = $island;
		$hako->writeIslandsFile($island['id']);

		// owner modeへ
		$html = new HtmlMapJS();
		$html->owner($hako, $data);
	}
}
