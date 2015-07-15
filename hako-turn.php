<?php

/*******************************************************************

	箱庭諸島 S.E

	- ターン更新用ファイル -

	hako-turn.php by SERA - 2013/06/01

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-init.php';
require_once ABSOLUTE_PATH.'hako-log.php';
require_once ABSOLUTE_PATH.'hako-make.php';

//--------------------------------------------------------------------
class Turn {
	public $log;
	public $rpx;
	public $rpy;

	//---------------------------------------------------
	// ターン進行モード
	//---------------------------------------------------
	function turnMain(&$hako, $data) {
		global $init;

		$this->log = new Log();

		// 最終更新時間を更新
		if($init->contUpdate == 1) {
			$uptime = 1;
		} else {
			$uptime = (int)(($_SERVER['REQUEST_TIME'] - $hako->islandLastTime) / $init->unitTime);
		}
		$hako->islandLastTime += $init->unitTime * $uptime;

		// ログファイルを後ろにずらす
		$this->log->slideBackLogFile();

		// ターン番号
		$hako->islandTurn++;
		$GLOBALS['ISLAND_TURN'] = $hako->islandTurn;

		if($hako->islandNumber == 0) {
			// 島がなければターン数を保存して以降の処理は省く
			// ファイルに書き出し
			$hako->writeIslandsFile();
			return;
		}
		// プレゼントファイルを読み込む(終れば消去)
		$hako->readPresentFile(true);

		// 座標配列を作る
		$randomPoint = Util::makeRandomPointArray();
		$this->rpx = $randomPoint[0];
		$this->rpy = $randomPoint[1];

		// 順番決め
		$order = Util::randomArray($hako->islandNumber);

		// 船舶初期化
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$this->shipcounter($hako, $hako->islands[$order[$i]]);
		}

		// 更新前の情報メモ
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// 管理人預かり中の場合スキップ
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			// 人口、資金、食料、ポイントをメモする
			$hako->islands[$order[$i]]['oldMoney'] = $hako->islands[$order[$i]]['money'];
			$hako->islands[$order[$i]]['oldFood']  = $hako->islands[$order[$i]]['food'];
			$hako->islands[$order[$i]]['oldPop']   = $hako->islands[$order[$i]]['pop'];
			$hako->islands[$order[$i]]['oldPoint'] = $hako->islands[$order[$i]]['point'];
			$this->estimate($hako, $hako->islands[$order[$i]]);
		}

		// 収入・消費
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// 管理人預かり中の場合スキップ
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			$this->income($hako->islands[$order[$i]]);
		}

		// コマンド処理
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// 管理人預かり中の場合スキップ
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}

			// 戻り値1になるまで繰り返し
			while($this->doCommand($hako, $hako->islands[$order[$i]]) == 0);

			// 整地ログ (まとめてログ出力)
			if($init->logOmit) {
				$this->logMatome($hako->islands[$order[$i]]);
			}
		}

		// 成長および単ヘックス災害
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// 管理人預かり中の場合スキップ
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			$this->doEachHex($hako, $hako->islands[$order[$i]]);
		}
		// 島全体処理
		$remainNumber = $hako->islandNumber;

		for($i = 0; $i < $hako->islandNumber; $i++) {
			// 管理人預かり中の場合スキップ
			if( isset($hako->islands[$order[$i]]['keep']) ) {
				if($hako->islands[$order[$i]]['keep']) {
					continue;
				}
			}
			$island = $hako->islands[$order[$i]];
			$this->doIslandProcess($hako, $island);

			// 死滅判定
			if ( isset($island['dead']) ) {
				if($island['dead'] == 1) {
					$island['pop']   = 0;
					$island['point'] = 0;
					$remainNumber--;
				} elseif((($island['pop'] == 0) || ($island['point'] == 0)) && ($island['isBF'] != 1)) {
					$island['dead'] = 1;
					$remainNumber--;
					// 死滅メッセージ
					$tmpid = $island['id'];
					$this->log->dead($tmpid, $island['name']);
					if(is_file("{$init->dirName}/island.{$tmpid}")) {
						unlink("{$init->dirName}/island.{$tmpid}");
					}
				}
			}
			$hako->islands[$order[$i]] = $island;
		}

		// 人口順にソート
		$this->islandSort($hako);

		// ターン杯対象ターンだったら、その処理
		if(($hako->islandTurn % $init->turnPrizeUnit) == 0) {
			$island = $hako->islands[0];
			$this->log->prize($island['id'], $island['name'], "{$hako->islandTurn}{$init->prizeName[0]}");
			$hako->islands[0]['prize'] .= "{$hako->islandTurn},";
		}
		// 島数カット
		$hako->islandNumber = $remainNumber;

		// 船舶初期化
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$this->shipcounter($hako, $hako->islands[$order[$i]]);
		}

		for($i = 0; $i < $hako->islandNumber; $i++) {
			$this->estimate($hako, $hako->islands[$order[$i]]);
		}

		// バックアップターンであれば、書く前にrename
		if(!($init->safemode) && (($hako->islandTurn % $init->backupTurn) == 0)) {
			$hako->backUp();
		}
		// バックアップターンであれば、セーフモードバックアップ取得
		if(($init->safemode) && (($hako->islandTurn % $init->backupTurn) == 0)) {
			$hako->safemode_backup();
		}
		// ファイルに書き出し
		$hako->writeIslandsFile(-1);

		// ログ書き出し
		$this->log->flush();

		// 記録ログ調整
		$this->log->historyTrim();
	}

	//---------------------------------------------------
	// ログをまとめる
	//---------------------------------------------------
	function logMatome($island) {
		global $init;

		$sno = $island['seichi'];
		$point = "";
		$i = 0;
		if($sno > 0) {
			if($init->logOmit == 1) {
				$sArray = $island['seichipnt'];
				for($i; $i < $sno; $i++) {
					$spnt = $sArray[$i];
					if($spnt == "") {
						break;
					}
					$x = $spnt['x'];
					$y = $spnt['y'];
					$point .= "($x, $y) ";
					if(!(($i+1)%20)) {
						// 全角空白３つ
						$point .= "<br>　　　";
					}
				}
			}
			if($i > 1 || ($init->logOmit != 1)) {
				$point .= "の<strong>{$sno}ケ所</strong>";
			}
		}
		if($point != "") {
			if(($init->logOmit == 1) && ($sno > 1)) {
				$this->log->landSucMatome($island['id'], $island['name'], '整地', $point);
			} else {
				$this->log->landSuc($island['id'], $island['name'], '整地', $point);
			}
		}
	}

	//---------------------------------------------------
	// コマンドフェイズ
	//---------------------------------------------------
	function doCommand(&$hako, &$island) {
		global $init;

		$comArray  = &$island['command'];
		$command   = $comArray[0];
		Util::slideFront($comArray, 0);
		$island['command'] = $comArray;
		$kind      = $command['kind'];
		$target    = $command['target'];
		$x         = $command['x'];
		$y         = $command['y'];
		$arg       = $command['arg'];
		$name      = $island['name'];
		$id        = $island['id'];
		$land      = $island['land'];
		$landValue = &$island['landValue'];
		$landKind  = &$land[$x][$y];
		$lv        = $landValue[$x][$y];
		$cost      = $init->comCost[$kind];
		$comName   = $init->comName[$kind];
		$point     = "({$x},{$y})";
		$landName  = $this->landName($landKind, $lv);
		$prize     = &$island['prize'];

		if($kind == $init->comDoNothing) {
			//$this->log->doNothing($id, $name, $comName);
			if($island['isBF'] == 1) {
				$island['money'] = $init->maxMoney;
				$island['food'] = $init->maxFood;
			} else {
				$island['money'] += 10;
				$island['absent']++;

				// 自動放棄
				if($island['absent'] >= $init->giveupTurn) {
					$comArray[0] = array (
						'kind'   => $init->comGiveup,
						'target' => 0,
						'x'      => 0,
						'y'      => 0,
						'arg'    => 0
					);
					$island['command'] = $comArray;
				}
				return 1;
			}
		}
		$island['command'] = $comArray;
		$island['absent']  = 0;

		// コストチェック
		if($cost > 0) {
			// 金の場合
			if($island['money'] < $cost) {
				$this->log->noMoney($id, $name, $comName);
				return 0;
			}
		} elseif($cost < 0) {
			// 食料・木材の場合
			if(($kind == $init->comSell || $kind == $init->comSoukoF) && ($island['food'] < (-$cost))) {
				$this->log->noFood($id, $name, $comName);
				return 0;
			} elseif(($kind == $init->comSellTree) && ($island['item'][20] < (-$cost))) {
				$this->log->noWood($id, $name, $comName);
				return 0;
			}
		}
		$returnMode = 1;

		switch($kind) {
			case $init->comPrepare:
			case $init->comPrepare2:
				// 整地、地ならし
				if (($landKind == $init->landSea) ||
					($landKind == $init->landPoll) ||
					($landKind == $init->landSbase) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landOil) ||
					($landKind == $init->landPort) ||
					($landKind == $init->landMountain) ||
					($landKind == $init->landMonster) ||
					($landKind == $init->landSleeper) ||
					($landKind == $init->landZorasu)) {
					// 海、砂浜、汚染土壌、海底基地、海底防衛施設、海底都市
					// 海上都市、海底農場、養殖場、油田、港、山、怪獣は整地できない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// 石は整地・地ならしで金に、卵は食料になる
				if($landKind == $init->landMonument) {
					if((33 < $lv) && ($lv < 40)) {
						// 金になる
						$island['money'] += 9999;
					} elseif((39 < $lv) && ($lv < 44)) {
						// 食料になる
						$island['food'] += 5000;
					}
				}
				// 目的の場所を平地にする
				$land[$x][$y] = $init->landPlains;
				$landValue[$x][$y] = 0;

				// 整地ログのまとめ
				if($init->logOmit) {
					$sno = $island['seichi'];
					$island['seichi']++;

					// 座標ありのまとめ
					if($init->logOmit == 1) {
						$seichipnt['x'] = $x;
						$seichipnt['y'] = $y;
						$island['seichipnt'][$sno] = $seichipnt;
					}
				} else {
					$this->log->landSuc($id, $name, '整地', $point);
				}
				// 何かの卵発見
				if(Util::random(100) < 3) {
					$this->log->EggFound($id, $name, $comName, $point);
					$land[$x][$y] = $init->landMonument;
					$landValue[$x][$y] = 40 + Util::random(3);
				}
				// アイテム発見判定
				if(Util::random(100) < 7) {
					// 地図１発見
					if(($island['tenki'] == 1) && ($island['item'][0] != 1)) {
						$island['item'][0] = 1;
						$this->log->ItemFound($id, $name, $comName, '何かの地図');
					} elseif($island['tenki'] == 4) {
						// 天気が雷のとき
						if(($island['item'][3] == 1) && ($island['item'][4] != 1)) {
							// ポチョムキン発見
							$island['item'][4] = 1;
							$this->log->ItemFound($id, $name, $comName, '謎の人形');
						} elseif(($island['item'][6] == 1) && ($island['item'][7] == 1) && ($island['item'][8] != 1)) {
							// 第三の脳発見
							$island['item'][8] = 1;
							$this->log->ItemFound($id, $name, $comName, '脳の形をした何か');
						} elseif(($island['item'][9] == 1) && ($island['taiji'] >= 7) && ($island['zin'][2] != 1)) {
							// シェイド発見
							$itemName = "シェイド";
							$island['zin'][2] = 1;
							$this->log->Zin3Found($id, $name, $comName, 'シェイド');
						}
					} elseif($island['tenki'] == 5) {
						// 天気が雪のとき
						if(($island['item'][4] == 1) && ($island['item'][5] != 1)) {
							// 地図２発見
							$island['item'][5] = 1;
							$this->log->ItemFound($id, $name, $comName, '何かの地図');
						} elseif(($island['item'][17] == 1) && ($island['item'][18] != 1)) {
							// リング発見
							$island['item'][18] = 1;
							$this->log->ItemFound($id, $name, $comName, 'リング');
						}
					} elseif(($island['item'][0] == 1) && ($island['zin'][0] != 1)) {
						// ノーム発見
						$island['zin'][0] = 1;
						$this->log->ZinFound($id, $name, $comName, 'ノーム');
					}
				}
				// 金を差し引く
				$island['money'] -= $cost;

				if($kind == $init->comPrepare2) {
					// 地ならし
					$island['prepare2']++;
					// ターン消費せず
					$returnMode = 0;
				} else {
					// 整地なら、埋蔵金の可能性あり
					if($island['zin'][0] == 1) {
						// ノーム所持時埋蔵金確率
						$r = Util::random(500);
					} else {
						$r = Util::random(1000);
					}
					if($r < $init->disMaizo) {
						$v = 100 + Util::random(901);
						$island['money'] += $v;
						$this->log->maizo($id, $name, $comName, $v);
					}
					$returnMode = 1;
				}
				break;

			case $init->comReclaim:
				// 埋め立て
				if(!(($landKind == $init->landSea) && ($lv < 2)) &&
					($landKind != $init->landOil) &&
					($landKind != $init->landPort) &&
					($landKind != $init->landNursery) &&
					($landKind != $init->landSfarm) &&
					($landKind != $init->landSeaSide) &&
					($landKind != $init->landSsyoubou) &&
					($landKind != $init->landSeaCity) &&
					($landKind != $init->landFroCity) &&
					($landKind != $init->landSdefence) &&
					($landKind != $init->landSbase)) {
					// 海、砂浜、海底基地、油田、港、海底消防署、海底防衛施設
					// 海底都市、海上都市、海底農場、養殖場しか埋め立てできない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// 周りに陸があるかチェック
				$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea, $init->landSeaSide, $init->landSeaCity, $init->landFroCity,$init->landOil, $init->landNursery, $init->landSfarm, $init->landPort, $init->landSdefence, $init->landSbase));

				if($seaCount == 7) {
					// 全部海だから埋め立て不能
					$this->log->noLandAround($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				if((($landKind == $init->landSea) && ($lv == 1)) || ($landKind == $init->landSeaSide)) {
					// 浅瀬か砂浜の場合
					// 目的の場所を荒地にする
					$land[$x][$y] = $init->landWaste;
					$landValue[$x][$y] = 0;
					$this->log->landSuc($id, $name, $comName, $point);
					if ($landKind != $init->landSeaSide) {
						$island['area']++;
					}
					if($seaCount <= 4) {
						// 周りの海が3ヘックス以内なので、浅瀬にする
						for($i = 1; $i < 7; $i++) {
							$sx = $x + $init->ax[$i];
							$sy = $y + $init->ay[$i];
							// 行による位置調整
							if((($sy % 2) == 0) && (($y % 2) == 1)) {
								$sx--;
							}
							if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
							} else {
								// 範囲内の場合
								if($land[$sx][$sy] == $init->landSea) {
									$landValue[$sx][$sy] = 1;
								}
							}
						}
					}
				} else {
					// 海なら、目的の場所を浅瀬にする
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 1;
					$this->log->landSuc($id, $name, $comName, $point);

					// 禁断の書発見
					if((Util::random(100) < 7) && ($island['tenki'] == 2) && ($island['item'][2] != 1)) {
						$island['item'][2] = 1;
						$this->log->ItemFound($id, $name, $comName, '古ぼけた書物');
					}
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comDestroy:
				// 掘削
				if((($landKind == $init->landSea) && ($lv > 1)) ||
					($landKind == $init->landPoll) ||
					($landKind == $init->landOil) ||
					($landKind == $init->landPort) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landSbase) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landMonster) ||
					($landKind == $init->landSleeper) ||
					($landKind == $init->landZorasu)) {
					// 船舶、汚染土壌、油田、港、海底都市、海上都市、海底農場、養殖場
					// 海底基地、海底防衛施設、怪獣、ぞらすは掘削できない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				if(($landKind == $init->landSea) && ($lv == 0)) {
					// 海なら、油田探し
					// 投資額決定
					if($arg == 0) {
						$arg = 1;
					}
					$value = min($arg * ($cost), $island['money']);
					$str = "{$value}{$init->unitMoney}";
					$p = round($value / $cost);
					$island['money'] -= $value;

					// 油田見つかるか判定
					if($p > Util::random(100)) {
						// 油田見つかる
						$this->log->oilFound($id, $name, $point, $comName, $str);
						$island['oil']++;
						$land[$x][$y] = $init->landOil;
						$landValue[$x][$y] = 0;
					} else {
						// 無駄撃ちに終わる
						$this->log->oilFail($id, $name, $point, $comName, $str);
					}
					$returnMode = 1;
					break;
				}
				// 目的の場所を海にする。山なら荒地に。浅瀬なら海に。
				if($landKind == $init->landMountain) {
					$land[$x][$y] = $init->landWaste;
					$landValue[$x][$y] = 0;
				} elseif($landKind == $init->landSeaCity) {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				} elseif($landKind == $init->landSea) {
					$landValue[$x][$y] = 0;
				} else {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 1;
					$island['area']--;
				}
				$this->log->landSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				if((Util::random(100) < 7) && ($island['tenki'] == 2) && ($island['item'][15] != 1)) {
					// マナ・クリスタル発見
					$island['item'][15] = 1;
					$this->log->ItemFound($id, $name, $comName, 'きらめく宝石');
				}
				$returnMode = 1;
				break;

			case $init->comDeForest:
				// 伐採
				if($landKind != $init->landForest) {
					// 森以外は伐採できない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// 目的の場所を平地にする
				$land[$x][$y] = $init->landPlains;
				$landValue[$x][$y] = 0;
				$this->log->landSuc($id, $name, $comName, $point);

				if((Util::random(100) < 7) && ($island['tenki'] == 1)) {
					// 天気が晴れのとき
					if($island['item'][1] != 1) {
						// ノコギリ発見
						$island['item'][1] = 1;
						$this->log->ItemFound($id, $name, $comName, 'ノコギリ');
					} elseif(($island['item'][5] == 1) && ($island['zin'][1] != 1)) {
						// ウィスプ発見
						$island['zin'][1] = 1;
						$this->log->ZinFound($id, $name, $comName, 'ウィスプ');
					}
				}
				if($island['item'][20] >= $init->maxWood) {
					// 木材最大値を超えた場合、売却金を得る
					$island['money'] += $init->treeValue * $lv;
				} else {
					// 木材を得る
					$island['item'][20] += $lv;
				}
				if($island['item'][1] == 1) {
					$returnMode = 0;
					break;
				}
				$returnMode = 1;
				break;

			case $init->comSeaSide:
				// 砂浜整備
				if(($landKind == $init->landSea) && ($lv != 1)) {
					// 浅瀬以外は整備できない
					$this->log->landFail($id, $name, $comName, $landName, $point);

					$returnMode = 0;
					break;
				}
				if((($landKind == $init->landSea) && ($lv == 1)) || ($landKind == $init->landSeaSide)) {
					// 周りに陸があるかチェック
					$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea, $init->landSeaSide, $init->landPort,
					$init->landOil, $init->landNursery, $init->landSbase));
					if($seaCount == 7) {
						$this->log->noLandAround($id, $name, $comName, $point);
						$returnMode = 0;
						break;
					}
					$land[$x][$y] = $init->landSeaSide;
					$landValue[$x][$y] = 0;
				}
				$this->log->LandSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comPort:
				// 港
				if(!($landKind == $init->landSea && $lv == 1)){
					// 浅瀬以外には建設不可
					$this->log->LandFail($id, $name, $comName, $landName, $point);

					$returnMode = 0;
					break;
				}
				$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea));

				if($seaCount <= 1){
					// 周囲に最低1Hexの海も無い場合も建設不可
					$this->log->NoSeaAround($id, $name, $comName, $point);

					$returnMode = 0;
					break;
				}
				if($seaCount == 7){
					// 周りが全部海なので港は建設できない
					$this->log->NoLandAround($id, $name, $comName, $point);

					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landPort;
				$landValue[$x][$y] = 0;
				$this->log->LandSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comMakeShip:
				// 造船
				$countPort = Turn::countAround($land, $x, $y, 7, array($init->landPort));
				if($countPort < 1){
					// 周囲1ヘックスに港がないと失敗
					$this->log->NoPort($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				if(!($landKind == $init->landSea && $lv == 0)){
					// 船を設置する場所が海で無い場合は失敗
					$this->log->NoSea($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				$ownShip = 0;
				for($i = 0; $i < 10; $i++) {
					$ownShip += $island['ship'][$i];
				}
				if($init->shipMax <= $ownShip){
					// 船が最大所有量を超えていた場合、却下
					$this->log->maxShip($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landShip;
				$landValue[$x][$y] = Util::navyPack($island['id'], $arg, $init->shipHP[$arg], 0, 0);
				$this->log->LandSuc($id, $name, $init->shipName[$arg]."の".$comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comSendShip:
				// 船派遣
				// ターゲット取得
				$tn = $hako->idToNumber[$target];
				if($tn != 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->ssNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// 事前準備
				$tIsland    = $hako->islands[$tn];
				$tId        = $tIsland['id'];
				$tName      = $tIsland['name'];
				$tLand      = $tIsland['land'];
				$tLandValue = $tIsland['landValue'];

				$ship = Util::navyUnpack($landValue[$x][$y]);

				// 実行判定
				if($land[$x][$y] != $init->landShip) {
					// 対象が船舶以外の場合
					$this->log->landFail($id, $name, $comName, "船舶以外の地形", $point);
					$returnMode = 0;
					break;
				} elseif($ship[1] >= 10) {
					// 対象が海賊船の場合
					$this->log->landFail($id, $name, $comName, $init->shipName[$ship[1]], $point);
					$returnMode = 0;
					break;
				} elseif($ship[0] != $island['id']) {
					// 対象が他島の船舶の場合
					$this->log->landFail($id, $name, $comName, "他島所属の船舶", $point);
					$returnMode = 0;
					break;
				} elseif($tId == $island['id']) {
					// 派遣先が自島のため中止
					$this->log->shipFail($id, $name, $comName, "派遣先が自島");
					$returnMode = 0;
					break;
				}

				// 派遣地点を決める
				for ($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if(($tLand[$bx][$by] == $init->landSea) && ($tLandValue[$bx][$by] == 0)){
						break;
					}
				}
				// 派遣先
				$tLand[$bx][$by]      = $init->landShip;
				$tLandValue[$bx][$by] = $lv;
				// 派遣元
				$land[$x][$y]      = $init->landSea;
				$landValue[$x][$y] = 0;

				// 派遣ログ
				$this->log->shipSend($id, $tId, $name, $init->shipName[$ship[1]], "({$x}, {$y})", $tName);

				$tIsland['land']      = $tLand;
				$tIsland['landValue'] = $tLandValue;
				$hako->islands[$tn]   = $tIsland;

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comReturnShip:
				// 船帰還
				// ターゲット取得
				$tn = $hako->idToNumber[$target];
				if($tn != 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->ssNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// 事前準備
				$tIsland    = $hako->islands[$tn];
				$tId        = $tIsland['id'];
				$tName      = $tIsland['name'];
				$tLand      = $tIsland['land'];
				$tLandValue = $tIsland['landValue'];
				$ship = Util::navyUnpack($tLandValue[$x][$y]);

				// 実行判定
				if($tLand[$x][$y] != $init->landShip) {
					// 対象が船舶以外の場合
					$this->log->landFail($id, $name, $comName, "船舶以外の地形", $point);
					$returnMode = 0;
					break;
				} elseif($ship[1] >= 10) {
					// 対象が海賊船の場合
					$this->log->landFail($id, $name, $comName, $init->shipName[$ship[1]], $point);
					$returnMode = 0;
					break;
				} elseif($ship[0] != $island['id']) {
					// 対象が他島の船舶の場合
					$this->log->landFail($id, $name, $comName, "他島所属の船舶", $point);
					$returnMode = 0;
					break;
				} elseif($tId == $island['id']) {
					// すでに自島に帰還済みの場合
					$this->log->shipFail($id, $name, $comName, "対象の船舶がすでに帰還済み");
					$returnMode = 0;
					break;
				}

				if($ship[1] == 2 && ($ship[1] > 0 || $ship[4] > 0)) {
					// 帰還時に海底探索船の財宝を回収
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
					$tLandValue[$x][$y] = Util::navyPack($ship[0], $ship[1], $ship[2], 0, 0);
					$island['money'] += $treasure;
					$this->log->RecoveryTreasure($id, $name, $init->shipName[$ship[1]], $treasure);
				}

				// 派遣地点を決める
				for ($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if(($land[$bx][$by] == $init->landSea) && ($landValue[$bx][$by] == 0)){
						break;
					}
				}
				// 帰還先（自島）
				$land[$bx][$by]      = $init->landShip;
				$landValue[$bx][$by] = $tLandValue[$x][$y];
				// 派遣先（他島）
				$tLand[$x][$y]      = $init->landSea;
				$tLandValue[$x][$y] = 0;

				// 帰還ログ
				$this->log->shipReturn($id, $tId, $name, $init->shipName[$ship[1]], "({$x}, {$y})", $tName);

				$tIsland['land']      = $tLand;
				$tIsland['landValue'] = $tLandValue;
				$hako->islands[$tn]   = $tIsland;

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comShipBack:
				// 船破棄
				$ship = Util::navyUnpack($landValue[$x][$y]);

				// 実行判定
				if($land[$x][$y] != $init->landShip) {
					// 対象が船舶以外の場合
					$this->log->landFail($id, $name, $comName, "船舶以外の地形", $point);
					$returnMode = 0;
					break;
				} elseif($landKind == $init->landShip && $ship[1] >= 10) {
					// 対象が海賊船の場合
					$this->log->landFail($id, $name, $comName, $init->shipName[$ship[1]], $point);
					$returnMode = 0;
					break;
				} elseif($landKind == $init->landShip && $ship[0] != $island['id']) {
					// 対象が他島の船舶の場合
					$this->log->landFail($id, $name, $comName, "他島所属の船舶", $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landSea;
				$landValue[$x][$y] = 0;
				$this->log->ComeBack($id, $name, $comName, $init->shipName[$ship[1]], $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comPlant:
			case $init->comFarm:
			case $init->comNursery:
			case $init->comFactory:
			case $init->comHatuden:
			case $init->comCommerce:
			case $init->comBase:
			case $init->comMyhome:
			case $init->comSoukoM:
			case $init->comSoukoF:
			case $init->comHikidasi:
			case $init->comMonument:
			case $init->comNewtown:
			case $init->comHaribote:
			case $init->comDbase:
			case $init->comRail:
			case $init->comStat:
			case $init->comPark:
			case $init->comSeaResort:
			case $init->comFusya:
			case $init->comSyoubou:
			case $init->comSoccer:
				// 地上建設系
				if(!(($landKind == $init->landPlains) ||
					($landKind == $init->landTown) ||
					(($landKind == $init->landMyhome) && ($kind == $init->comMyhome)) ||
					(($landKind == $init->landSoukoM) && ($kind == $init->comSoukoM)) ||
					(($landKind == $init->landSoukoF) && ($kind == $init->comSoukoF)) ||
					(($landKind == $init->landSoukoM || $landKind == $init->landSoukoF) && ($kind == $init->comHikidasi)) ||
					(($landKind == $init->landMonument) && ($kind == $init->comMonument)) ||
					(($landKind == $init->landFarm) && ($kind == $init->comFarm)) ||
					(($landKind == $init->landlandSea) && ($lv == 1) && ($kind == $init->comNursery)) ||
					(($landKind == $init->landNursery) && ($kind == $init->comNursery)) ||
					(($landKind == $init->landFactory) && ($kind == $init->comFactory)) ||
					(($landKind == $init->landHatuden) && ($kind == $init->comHatuden)) ||
					(($landKind == $init->landCommerce) && ($kind == $init->comCommerce)) ||
					(($landKind == $init->landSoccer) && ($kind == $init->comSoccer)) ||
					(($landKind == $init->landRail) && ($kind == $init->comRail)) ||
					(($landKind == $init->landStat) && ($kind == $init->comStat)) ||
					(($landKind == $init->landPark) && ($kind == $init->comPark)) ||
					(($landKind == $init->landSeaResort) && ($kind == $init->comSeaResort)) ||
					(($landKind == $init->landFusya) && ($kind == $init->comFusya)) ||
					(($landKind == $init->landSyoubou) && ($kind == $init->comSyoubou)) ||
					(($landKind == $init->landDefence) && ($kind == $init->comDbase)))) {
					// 不適当な地形
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}

				// 種類で分岐
				switch($kind) {
					case $init->comPlant:
						// 目的の場所を森にする。
						$land[$x][$y] = $init->landForest;
						// 木は最低単位
						$landValue[$x][$y] = 1;
						$this->log->PBSuc($id, $name, $comName, $point);

						if(Util::random(100) < 7) {
							if($island['item'][10] != 1) {
								// 植物図鑑発見
								$island['item'][10] = 1;
								$this->log->ItemFound($id, $name, $comName, '植物図鑑');
							} elseif(($island['item'][10] == 1) && ($island['item'][11] != 1)) {
								// ルーぺ発見
								$island['item'][11] = 1;
								$this->log->ItemFound($id, $name, $comName, 'ルーぺ');
							} elseif(($island['item'][11] == 1) && ($island['item'][12] != 1)) {
								// 苗木発見
								$island['item'][12] = 1;
								$this->log->ItemFound($id, $name, $comName, '苗木');
							} elseif(($island['item'][12] == 1) && ($island['tenki'] == 3) && ($island['zin'][3] != 1)) {
								// ドリアード発見
								$island['zin'][3] = 1;
								$this->log->ZinFound($id, $name, $comName, 'ドリアード');
							}
						}
						break;

					case $init->comBase:
						// 目的の場所をミサイル基地にする。
						$land[$x][$y] = $init->landBase;
						// 経験値0
						$landValue[$x][$y] = 0;
						$this->log->PBSuc($id, $name, $comName, $point);

						if((Util::random(100) < 7) && ($island['item'][6] != 1)) {
							// 科学書発見
							$island['item'][6] = 1;
							$this->log->ItemFound($id, $name, $comName, '難しそうな書物');
						}
						break;

					case $init->comHaribote:
						// 目的の場所をハリボテにする
						$land[$x][$y] = $init->landHaribote;
						$landValue[$x][$y] = 0;
						$this->log->hariSuc($id, $name, $comName, $init->comName[$init->comDbase], $point);
						break;

					case $init->comNewtown:
						// 目的の場所をニュータウンにする
						$land[$x][$y] = $init->landNewtown;
						$landValue[$x][$y] = 1;
						$this->log->landSuc($id, $name, $comName, $point);
						break;

					case $init->comSoccer:
						// 目的の場所をスタジアムにする
						if($island['soccer'] > 0){
							// スタジアムは島に１つだけしか作れない
							$this->log->IsFail($id, $name, $comName, 'スタジアム');

							$returnMode = 0;
							break;
						}
						$land[$x][$y] = $init->landSoccer;
						$landValue[$x][$y] = 0;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;

					case $init->comRail:
						// 目的の場所を線路にする
						if($arg > 8) {
							$arg = 8;
						}
						$land[$x][$y] = $init->landRail;
						$landValue[$x][$y] = $arg;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;

					case $init->comStat:
						// 目的の場所を駅にする
						$land[$x][$y] = $init->landStat;
						$landValue[$x][$y] = 0;
						$island['stat']++;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;

					case $init->comPark:
						// 目的の場所を遊園地にする
						$land[$x][$y] = $init->landPark;
						if($arg > 4) {
							$arg = 4;
						}
						$landValue[$x][$y] = $arg;
						$island['park']++;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;

					case $init->comFusya:
						// 目的の場所を風車にする
						$land[$x][$y] = $init->landFusya;
						$landValue[$x][$y] = 0;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;

					case $init->comSyoubou:
						// 目的の場所を消防署にする
						$land[$x][$y] = $init->landSyoubou;
						$landValue[$x][$y] = 0;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;

					case $init->comFarm:
						// 農場
						if($landKind == $init->landFarm) {
							// すでに農場の場合
							$landValue[$x][$y] += 2; // 規模 + 2000人
							if($landValue[$x][$y] > 50) {
								$landValue[$x][$y] = 50; // 最大 50000人
							}
						} else {
							// 目的の場所を農場に
							$land[$x][$y] = $init->landFarm;
							$landValue[$x][$y] = 10; // 規模 = 10000人
						}
						$this->log->landSuc($id, $name, $comName, $point);

						if((Util::random(100) < 7) && ($island['tenki'] == 1) && ($island['zin'][3] == 1)) {
							if($island['item'][16] != 1) {
								// 農作物図鑑発見
								$island['item'][16] = 1;
								$this->log->ItemFound($id, $name, $comName, '農作物図鑑');
							} elseif(($island['item'][16] == 1) && ($island['zin'][5] != 1)) {
								// ジン発見
								$island['zin'][5] = 1;
								$this->log->Zin6Found($id, $name, $comName, 'ジン');
							}
						}
						break;

					case $init->comNursery:
						// 養殖場
						if($landKind == $init->landNursery) {
							// すでに養殖場の場合
							$landValue[$x][$y] += 2; // 規模 + 2000人
							if($landValue[$x][$y] > 50) {
								$landValue[$x][$y] = 50; // 最大 50000人
							}
						} elseif(($landKind == $init->landSea) && ($lv == 1)) {
							// 目的の場所を養殖場に
							$land[$x][$y] = $init->landNursery;
							$landValue[$x][$y] = 10; // 規模 = 10000人
						} else {
							// 不適当な地形
							$this->log->landFail($id, $name, $comName, $landName, $point);
							return 0;
						}
						$this->log->landSuc($id, $name, $comName, $point);
						break;

					case $init->comFactory:
						// 工場
						if($landKind == $init->landFactory) {
							// すでに工場の場合
							$landValue[$x][$y] += 10; // 規模 + 10000人
							if($landValue[$x][$y] > 200) {
								$landValue[$x][$y] = 200; // 最大 200000人
							}
						} else {
							// 目的の場所を工場に
							$land[$x][$y] = $init->landFactory;
							$landValue[$x][$y] = 30; // 規模 = 30000人
						}
						$this->log->landSuc($id, $name, $comName, $point);
						break;

					case $init->comHatuden:
						// 発電所
						if($landKind == $init->landHatuden) {
							// すでに発電所の場合
							$landValue[$x][$y] += 40; // 規模 + 40000kw
							if($landValue[$x][$y] > 250) {
								$landValue[$x][$y] = 250; // 最大 250000kw
							}
						} else {
							// 目的の場所を発電所に
							$land[$x][$y] = $init->landHatuden;
							$landValue[$x][$y] = 40; // 規模 = 40000kw
						}
						$this->log->landSuc($id, $name, $comName, $point);

						if(Util::random(100) < 7) {
							if(($island['tenki'] == 1) && ($island['item'][13] != 1)) {
								// 数学書発見
								$this->log->ItemFound($id, $name, $comName, '難しそうな書物');
								$island['item'][13] = 1;
							} elseif(($island['tenki'] == 3) && ($island['item'][14] != 1)) {
								// 技術書発見
								$this->log->ItemFound($id, $name, $comName, '難しそうな書物');
								$island['item'][14] = 1;
							} elseif(($island['tenki'] == 4) && ($island['item'][15] == 1) && ($island['zin'][4] != 1)) {
								// ルナ発見
								$this->log->Zin5Found($id, $name, $comName, 'ルナ');
								$island['zin'][4] = 1;
							}
						}
						break;

					case $init->comCommerce:
						// 商業ビル
						if($landKind == $init->landCommerce) {
							// すでに商業ビルの場合
							$landValue[$x][$y] += 20; // 規模 + 20000人
							if($landValue[$x][$y] > 250) {
								$landValue[$x][$y] = 250; // 最大 250000人
							}
						} else {
							// 目的の場所を商業ビルに
							$land[$x][$y] = $init->landCommerce;
							$landValue[$x][$y] = 30; // 規模 = 30000人
						}
						$this->log->landSuc($id, $name, $comName, $point);

						if(Util::random(100) < 7) {
							if($island['item'][17] != 1) {
								// 経済書発見
								$island['item'][17] = 1;
								$this->log->ItemFound($id, $name, $comName, '難しそうな書物');
							} elseif((($landKind == $init->landCommerce) > 0) && ($island['item'][19] == 1) && ($island['zin'][6] != 1)) {
								// サラマンダー発見
								$island['zin'][6] = 1;
								$this->log->ZinFound($id, $name, $comName, 'サラマンダー');
							}
						}
						break;

					case $init->comSeaResort:
						// 海の家
						if (Turn::countAround($land, $x, $y, 19, array($init->landSeaResort))) {
							// 周囲２ヘックスに海の家がある
							$this->log->LandFail($id, $name, $comName, '海の家の近く', $point);

							$returnMode = 0;
							break 2;
						} else {
							// 周囲２ヘックスに海の家がない
							$land[$x][$y] = $init->landSeaResort;
							$landValue[$x][$y] = 0;

							$this->log->LandSuc($id, $name, $comName, $point);
						}
						break;

					case $init->comMyhome:
						// 自宅建設
						if(!($island['home'])) {
							$landValue[$x][$y] = 0;
						}
						$cost = ($landValue[$x][$y] + 1) * $cost;
						if($island['item'][20] < ($landValue[$x][$y] + 1) * 200) {
							// 木材が足らない
							$this->log->noWood($id, $name, $comName);
							$returnMode = 0;
							break 2;
						}

						if($island['money'] < $cost) {
							// 資金チェック
							$island['money'] += $cost; // 返金

							$this->log->noMoney($id, $name, $comName);

							$returnMode = 0;
							break 2;
						}
						if($landKind == $init->landMyhome) {
							// すでに自宅の場合
							$landValue[$x][$y] += 1; // 規模 + 1人
							if($landValue[$x][$y] >= 11) {
								$returnMode = 0;
								break;
							}
							$this->log->landSuc($id, $name, 'リフォーム', $point);
						} else {
							// 目的の場所をマイホームに
							if($island['home'] > 0) {
								// すでにマイホームがある
								$this->log->IsFail($id, $name, $comName, 'マイホーム');

								$returnMode = 0;
								break 2;
							}
							$land[$x][$y] = $init->landMyhome;
							$landValue[$x][$y] = 1; // 規模 = 1人

							$this->log->landSuc($id, $name, $comName, $point);
						}
						// 木材を差し引く
						$island['item'][20] -= $landValue[$x][$y] * 100;
						break;

					case $init->comSoukoM:
						$flagm = 1;
					case $init->comSoukoF:
						// 倉庫建設
						if($arg == 0) {
							$flags = 1;
							$arg = 1;
						}
						// セキュリティと貯蓄を算出
						$sec = (int)($landValue[$x][$y] / 100);
						$tyo = $landValue[$x][$y] % 100;
						if($tyo == 99 && $flags != 1) {
							$str = "倉庫が一杯だった";
							$cost = 0;
							$this->log->SoukoMax($id, $name, $comName, $point, $str);
							return 0;
							break;
						} elseif($sec == 10 && $flags == 1) {
							$str = "倉庫のセキュリティレベルが最大値に達していた";
							$cost = 0;
							$this->log->SoukoMax($id, $name, $comName, $point, $str);
							return 0;
							break;
						}
						if($flagm == 1) {
							$arg = min($arg, (int)($island['money'] / $cost));
							$ryo = $cost * $arg;
							$cost = $ryo;
							$str = "({$ryo}{$init->unitMoney})";
						} else {
							$arg = min($arg, (int)($island['food'] / -$cost));
							$ryo = -$cost * $arg;
							$island['food'] -= $ryo;
							$cost = 0;
							$str = "({$ryo}{$init->unitFood})";
						}
						if($landKind == $init->landSoukoM || $landKind == $init->landSoukoF) {
							// すでに倉庫の場合
							if($flags == 1) {
								$arg = 0;
								$sec += 1;
								if($sec > 10) {
									$sec = 10;
								}
								$str ="(セキュリティ強化)";
							} else {
								$tyo += $arg;
								if($tyo > 99) {
									$tyo = 99;
								}
							}
						} else {
							// 目的の場所を倉庫に
							if($flagm == 1) {
								$land[$x][$y] = $init->landSoukoM;
							} else {
								$land[$x][$y] = $init->landSoukoF;
							}

							if($flags == 1) {
								$arg = 0;
								$sec = 1;
								$str ="(セキュリティ強化)";
							}
							$tyo = $arg;
						}
						$landValue[$x][$y] = $sec * 100 + $tyo;
						$this->log->Souko($id, $name, $comName, $point, $str);
						break;

					case $init->comHikidasi:
						// 倉庫引き出し
						if($arg == 0) {
							$arg = 1;
						}
						if($landKind == $init->landSoukoM) {
							$flagm = 1;
						} else {
							$flagm = 0;
						}
						// セキュリティと貯蓄を算出
						$sec = (int)($landValue[$x][$y] / 100);
						$tyo = $landValue[$x][$y] % 100;
						if($arg > $tyo) {
							$arg = $tyo;
						}
						if($flagm == 1) {
							$arg = min($arg, (int)($island['money'] / $cost));
							$cost *= $arg;
							$ryo = 1000 * $arg;
							$island['money'] += $ryo;
							$str = "({$ryo}{$init->unitMoney})";
						} else {
							$arg = min($arg, (int)($island['food'] / $cost));
							$ryo = 1000 * $arg;
							$island['food'] += $ryo - $cost * $arg;
							$cost = 0;
							$str = "({$ryo}{$init->unitFood})";
						}
						$tyo -= $arg;
						if($tyo < 0) {
							$tyo = 0;
						}
						$landValue[$x][$y] = $sec * 100 + $tyo;
						$this->log->Souko($id, $name, $comName, $point, $str);
						$returnMode = 0;
						break;

					case $init->comDbase:
						// 防衛施設
						if($landKind == $init->landDefence) {
							// すでに防衛施設の場合
							$landValue[$x][$y] = 0; // 自爆装置セット
							$this->log->bombSet($id, $name, $landName, $point);
						} else {
							// 目的の場所を防衛施設に
							$land[$x][$y] = $init->landDefence;
							if ($arg == 0) {
								$arg = 1;
							} elseif ($arg > $init->dBaseHP) {
								$arg = $init->dBaseHP;
							}
							$value = min($arg * ($cost), $island['money']);
							$p = floor($value / $cost);
							$cost = $value;
							$landValue[$x][$y] = $p;
							$this->log->landSuc($id, $name, $comName, $point);
						}
						if((Util::random(100) < 7) && ($island['item'][7] != 1)) {
							// 技術書発見
							$island['item'][7] = 1;
							$this->log->ItemFound($id, $name, $comName, '難しそうな書物');
						}
						$returnMode = 1;
						break;

					case $init->comMonument:
						// 記念碑
						if($landKind == $init->landMonument) {
							// すでに記念碑の場合
							// ターゲット取得
							$tn = $hako->idToNumber[$target];
							if($tn !== 0 && empty($tn)) {
								// ターゲットがすでにない
								// 何も言わずに中止
								$returnMode = 0;
								break 2;
							}
							if($hako->islands[$tn]['keep']) {
								// 目標の島が管理人預かり中のため実行が許可されない
								$this->log->CheckKP($id, $name, $comName);
								$returnMode = 0;
								break 2;
							}
							if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $hako->islands[$tn]['starturn']) < $init->noMissile)) {
								// 実行許可ターンを経過したか？
								$this->log->Forbidden($id, $name, $comName);
								$returnMode = 0;
								break 2;
							}
							$hako->islands[$tn]['bigmissile']++;

							// その場所は荒地に
							$land[$x][$y] = $init->landWaste;
							$landValue[$x][$y] = 0;
							$this->log->monFly($id, $name, $landName, $point);
						} else {
							// 目的の場所を記念碑に
							$land[$x][$y] = $init->landMonument;
							if($arg >= $init->monumentNumber) {
								$arg = 0;
							}
							$landValue[$x][$y] = $arg;
							$this->log->landSuc($id, $name, $comName, $point);
						}
						break;
				}
				// 金を差し引く
				$island['money'] -= $cost;

				// 回数付きなら、コマンドを戻す
				if (($kind == $init->comFarm) ||
					($kind == $init->comSfarm) ||
					($kind == $init->comNursery) ||
					($kind == $init->comFactory) ||
					($kind == $init->comHatuden) ||
					($kind == $init->comCommerce)) {
					if($arg > 1) {
						$arg--;
						Util::slideBack($comArray, 0);
						$comArray[0] = array (
							'kind'   => $kind,
							'target' => $target,
							'x'      => $x,
							'y'      => $y,
							'arg'    => $arg
						);
					}
				}
				$returnMode = 1;
				break;

				// ここまで地上建設系

			case $init->comMountain:
				// 採掘場
				if($landKind != $init->landMountain) {
					// 山以外には作れない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$landValue[$x][$y] += 5; // 規模 + 5000人
				if($landValue[$x][$y] > 200) {
					$landValue[$x][$y] = 200; // 最大 200000人
				}
				$this->log->landSuc($id, $name, $comName, $point);
				if((Util::random(100) < 7) && ($island['tenki'] == 3) &&
					($island['item'][2] == 1) && ($island['item'][3] != 1)) {
					// マスク発見
					$island['item'][3] = 1;
					$this->log->ItemFound($id, $name, $comName, '不気味なマスク');
				}
				// 金を差し引く
				$island['money'] -= $cost;
				if($arg > 1) {
					$arg--;
					Util::slideBack($comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;

			case $init->comSfarm:
				// 海底農場
				if($landKind == $init->landSfarm) {
					// すでに農場の場合
					$landValue[$x][$y] += 2; // 規模 + 2000人
					if($landValue[$x][$y] > 30) {
						$landValue[$x][$y] = 30; // 最大 30000人
					}
				} elseif(($landKind != $init->landSea) || ($lv != 0)) {
					// 海以外には作れない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				} else {
					// 目的の場所を農場に
					$land[$x][$y] = $init->landSfarm;
					$landValue[$x][$y] = 10; // 規模 = 10000人
				}
				$this->log->landSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;
				if($arg > 1) {
					$arg--;
					Util::slideBack($comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;

			case $init->comSeaCity:
				//海底都市
				if(($landKind != $init->landSea) || ($lv != 0)) {
					// 海以外には作れない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$cntL = Turn::countAround($land, $x, $y, 7, array($init->landSea));
				$cntS = Turn::countAroundValue($island, $x, $y, $init->landSea, 1, 7);

				if($cntL == 0 && $cntS == 0) {
					// 陸地、浅瀬のどちらも周囲にない
					if($cntL == 0) {
						// 陸地がないので中止
						$this->log->NoLandAround($id, $name, $comName, $point);
					} else {
						// 浅瀬がないので中止
						$this->log->NoShoalAround($id, $name, $comName, $point);
					}
					$returnMode = 0;
					break;
				}
				if ($arg == 77) {
					// 海上都市にする
					$land[$x][$y] = $init->landFroCity;
					$landValue[$x][$y] = 1; // 初期人口
				} else {
					$land[$x][$y] = $init->landSeaCity;
					$landValue[$x][$y] = 5; // 初期人口
				}
				$this->log->landSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comSdbase:
				// 海底防衛施設
				if(($landKind != $init->landSea) || ($lv != 0)){
					// 海以外には作れない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// 目的の場所を防衛施設に
				$land[$x][$y] = $init->landSdefence;

				if ($arg == 0) {
					$arg = 1;
				} elseif ($arg > $init->sdBaseHP) {
					$arg = $init->sdBaseHP;
				}
				$value = min($arg * ($cost), $island['money']);
				$p = round($value / $cost);
				$landValue[$x][$y] = $p;
				$this->log->landSuc($id, $name, $comName, $point);

				if((Util::random(100) < 7) && ($island['item'][7] != 1)) {
					// 技術書発見
					$island['item'][7] = 1;
					$this->log->ItemFound($id, $name, $comName, '難しそうな書物');
				}
				// 金を差し引く
				$island['money'] -= $value;

				$returnMode = 1;
				break;

			case $init->comSbase:
				// 海底基地
				if(($landKind != $init->landSea) || ($lv != 0)){
					// 海以外には作れない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landSbase;
				$landValue[$x][$y] = 0; // 経験値0
				$this->log->landSuc($id, $name, $comName, '(?, ?)');

				if((Util::random(100) < 7) && ($island['item'][6] != 1)) {
					// 科学書発見
					$island['item'][6] = 1;
					$this->log->ItemFound($id, $name, $comName, '難しそうな書物');
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comSsyoubou:
				// 目的の場所を海底消防署にする
				if(($landKind != $init->landSea) || ($lv != 0)){
					// 海以外には作れない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landSsyoubou;
				$landValue[$x][$y] = 0;
				$this->log->LandSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comProcity:
				// 防災都市
				if(($landKind != $init->landTown) || ($lv != 100)){
					// 町以外には作れない
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landProcity;
				$landValue[$x][$y] = 100; // 経験値0
				$this->log->landSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comBoku;
				// 僕の引越し
				if($landKind != $init->landProcity) {
					$this->log->BokuFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$townCount = Turn::countAround($land, $x, $y, 19, array($init->landTown));

				if($townCount == 0) {
					$this->log->noTownAround($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				$landValue[$x][$y] += 10; // 規模 + 1000人
				if($landValue[$x][$y] > 250) {
					$landValue[$x][$y] = 250; // 最大 25000人
				}
				for($i = 1; $i < 19; $i++) {
					$sx = $x + $init->ax[$i];
					$sy = $y + $init->ay[$i];
					if($land[$sx][$sy] == $init->landTown){
						$landValue[$sx][$sy] -= round(20/$townCount);
						if($landValue[$sx][$sy] <= 0) {
							// 平地に戻す
							$land[$sx][$sy] = $init->landPlains;
							$landValue[$sx][$sy] = 0;

							continue;
						}
					}
				}
				$this->log->landSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;
				if($arg > 1) {
					$arg--;
					Util::slideBack($comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;

			case $init->comBigtown:
				// 現代化
				if(!(($landKind == $init->landNewtown) && ($lv >= 150))){
					// ニュータウン以外には作れない
					$this->log->JoFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$townCount = Turn::countAround($land, $x, $y, 19, array($init->landTown, $init->landNewtown, $init->landBigtown));

				if($townCount < 16) {
					// 全部海だから埋め立て不能
					$this->log->JoFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landBigtown;
				$this->log->landSuc($id, $name, $comName, $point);

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comEisei;
				// 人工衛星打ち上げ
				if($arg > 5) {
					$arg = 0;
				}
				$value = ($arg + 1) * $cost;
				// 気象, 観測, 迎撃, 軍事, 防衛, イレ
				$rocket = array(1, 1, 2, 2, 3, 4);
				$tech   = array(10, 40, 100, 250, 300, 500);
				$failp  = array(700, 500, 600, 400, 200, 3000);
				$failq  = array(100, 100, 10, 10, 10, 1);

				if($island['m23'] < $rocket[$arg]) {
					// ロケットが$rocket以上ないとダメ
					$this->log->NoAny($id, $name, $comName, "ロケットが足りない");
					$returnMode = 0;
					break;
				} elseif($island['rena'] < $tech[$arg]) {
					// 軍事技術Lv$tech以上ないとダメ
					$this->log->NoAny($id, $name, $comName, "軍事技術が足りない");
					$returnMode = 0;
					break;
				} elseif($island['money'] < $value) {
					$this->log->NoAny($id, $name, $comName, "資金不足の");
					$returnMode = 0;
					break;
				} elseif(Util::random(10000) > $failp[$arg] + $failq[$arg] * $island['rena']) {
					$this->log->Eiseifail($id, $name, $comName, $point);

					// 金を差し引く
					$island['money'] -= $value;

					$returnMode = 1;
					break;
				}
				$island['eisei'][$arg] = ($arg == 5) ? 250 : 100;
				$this->log->Eiseisuc($id, $name, $init->EiseiName[$arg], "の打ち上げ");

				// 金を差し引く
				$island['money'] -= $value;

				$returnMode = 1;
				break;

			case $init->comEiseimente;
				// 人工衛星打修復
				if($arg > 5) {
					$arg = 0;
				}
				if($island['eisei'][$arg] > 0) {
					$island['eisei'][$arg] = 150;
					$this->log->Eiseisuc($id, $name, $init->EiseiName[$arg], "の修復");
				} else {
					$this->log->NoAny($id, $name, $comName, "指定の人工衛星がない");
					$returnMode = 0;
					break;
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comEiseiAtt;
				// 衛星破壊砲
				if($island['enehusoku'] < 0) {
					// 電力不足
					$this->log->Enehusoku($id, $name, $comName);
					$returnMode = 0;
					break;
				}

				// ターゲット取得
				$tn = $hako->idToNumber[$target];
				if($tn !== 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($hako->islands[$tn]['keep']) {
					// 目標の島が管理人預かり中のため実行が許可されない
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// 事前準備
				if($arg > 5) {
					$arg = 0;
				}
				$tIsland = &$hako->islands[$tn];
				$tName = &$tIsland['name'];

				if($island['eisei'][5] > 0 || $island['eisei'][3] > 0) {
					// イレギュラーか軍事衛星がある場合
					$eName = $init->EiseiName[$arg];
					$p = ($island['eisei'][5] >= 1) ? 110 : 70;
					if($tIsland['eisei'][$arg] > 0) {
						if(Util::random(100) < $p - 10 * $arg) {
							$tIsland['eisei'][$arg] = 0;
							$this->log->EiseiAtts($id, $tId, $name, $tName, $comName, $eName);
						} else {
							$this->log->EiseiAttf($id, $tId, $name, $tName, $comName, $eName);
						}
					} else {
						$this->log->NoAny($id, $name, $comName, "指定の人工衛星がない");
						$returnMode = 0;
						break;
					}
					$nkind = ($island['eisei'][5] >= 1) ? '5' : '3';
					$island['eisei'][$nkind] -= 30;

					if($island['eisei'][$nkind] < 1) {
						$island['eisei'][$nkind] = 0;
						$this->log->EiseiEnd($id, $name, ($island['eisei'][5] >= 1) ? $init->EiseiName[5] : $init->EiseiName[3]);
					}
				} else {
					// イレギュラーも軍事衛星もない場合
					$this->log->NoAny($id, $name, $comName, "必要な人工衛星がない");
					$returnMode = 0;
					break;
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comEiseiLzr:
				// 衛星レーザー
				if($island['enehusoku'] < 0) {
					// 電力不足
					$this->log->Enehusoku($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ターゲット取得
				$tn = $hako->idToNumber[$target];
				if($tn != 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($hako->islands[$tn]['keep']) {
					// 目標の島が管理人預かり中のため実行が許可されない
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// 事前準備
				$tIsland    = &$hako->islands[$tn];
				$tName      = &$tIsland['name'];
				$tLand      = &$tIsland['land'];
				$tLandValue = &$tIsland['landValue'];

				if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
					// 実行許可ターンを経過したか？
					$this->log->Forbidden($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// 着弾点の地形等算出
				$tL     = $tLand[$x][$y];
				$tLv    = $tLandValue[$x][$y];
				$tLname = $this->landName($tL, $tLv);
				$tPoint = "({$x}, {$y})";

				if($island['id'] == $tIsland['id']) {
					$tLand[$x][$y] = &$land[$x][$y];
				}
				if($island['eisei'][5] > 0 || $island['eisei'][3] > 0) {
					// イレギュラーか軍事衛星がある場合
					if((($tL == $init->landSea) && ($tLv < 2)) || ($tL == $init->landSeaCity) ||
						($tL == $init->landSbase) || ($tL == $init->landSdefence) || ($tL == $init->landMountain)) {
						// 効果のない地形
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "暖かくなりました。");
					} elseif((($tL == $init->landSea) && ($tLv >= 2)) || ($tL == $init->landOil) || ($tL == $init->landZorasu) || ($tL == $init->landFroCity)) {
						// 船と油田とぞらすと海上都市は海になる
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "焼き払われました。");
						$tLand[$x][$y] = $init->landSea;
						$tLandValue[$x][$y] = 0;
					} elseif(($tL == $init->landNursery) || ($tL == $init->landSeaSide) || ($tL == $init->landPort)) {
						// 養殖場と砂浜と港は浅瀬になる
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "焼き払われました。");
						$tLand[$x][$y] = $init->landSea;
						$tLandValue[$x][$y] = 1;
					} else {
						// その他は荒地に
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "焼き払われました。");
						$tLand[$x][$y] = $init->landWaste;
						$tLandValue[$x][$y] = 1;
					}
					$eName = $init->EiseiName[$arg];
					$p = ($island['eisei'][5] >= 1) ? 110 : 70;
					$nkind = ($island['eisei'][5] >= 1) ? '5' : '3';
					$island['eisei'][$nkind] -= (($island['eisei'][5] >= 1) ? 5 : 10);
				} else {
					// イレギュラーも軍事衛星もない場合
					$this->log->NoAny($id, $name, $comName, "必要な人工衛星がない");
					$returnMode = 0;
					break;
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comMissileNM:
			case $init->comMissilePP:
			case $init->comMissileST:
			case $init->comMissileBT:
			case $init->comMissileSP:
			case $init->comMissileLD:
			case $init->comMissileLU:
				// ミサイル系
				if((($island['tenki'] == 4) || ($island['tenki'] == 5)) && ($island['zin'][1] != 1)){
					// 雷・雪の日は打てない
					$this->log->msNoTenki($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($island['enehusoku'] < 0) {
					// 電力不足
					$this->log->Enehusoku($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				$flag = 0;
				do {
					if(($arg == 0) || ($arg > $island['fire'])) {
						// 0の場合は撃てるだけ
						$arg = $island['fire'];
					}
					$comp = $arg;
					// ターゲット取得
					$tn = $hako->idToNumber[$target];
					if($tn !== 0 && empty($tn)) {
						// ターゲットがすでにない
						$this->log->msNoTarget($id, $name, $comName);
						$returnMode = 0;
						break 2;
					}
					if($hako->islands[$tn]['keep']) {
						// 目標の島が管理人預かり中のため実行が許可されない
						$this->log->CheckKP($id, $name, $comName);
						$returnMode = 0;
						break 2;
					}
					// 事前準備
					$tIsland    = &$hako->islands[$tn];
					$tName      = &$tIsland['name'];
					$tLand      = &$tIsland['land'];
					$tLandValue = &$tIsland['landValue'];

					if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
						// 実行許可ターンを経過したか？
						$this->log->Forbidden($id, $name, $comName);
						$returnMode = 0;
						break 2;
					}
					// 難民の数
					$boat = 0;

					// ミサイルの内訳
					$missiles = 0; // 発射数
					$missileA = 0; // 範囲外、効果なし、荒地
					$missileB = 0; // 空中爆破
					$missileC = 0; // 硬化中、迎撃
					$missileD = 0; // 怪獣命中
					$missileE = 0; // 戦艦迎撃

					// 誤差
					if(($kind == $init->comMissilePP) || ($kind == $init->comMissileBT) || ($kind == $init->comMissileSP)) {
						$err = 7;
					} else {
						$err = 19;
					}
					$bx = $by = 0;
					// 金が尽きるか指定数に足りるか基地全部が撃つまでループ
					$count = 0;
					while(($arg > 0) && ($island['money'] >= $cost)) {
						// 基地を見つけるまでループ
						while($count < $init->pointNumber) {
							$bx = isset($this->rpx[$count]) ? $this->rpx[$count] : 0;
							$by = isset($this->rpx[$count]) ? $this->rpy[$count] : 0;
							if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
								break;
							}
							$count++;
						}
						if($count >= $init->pointNumber) {
							// 見つからなかったらそこまで
							break;
						}
						// 最低一つ基地があったので、flagを立てる
						$flag = 1;

						// 基地のレベルを算出
						$level = Util::expToLevel($land[$bx][$by], $landValue[$bx][$by]);

						// 基地内でループ
						while(($level > 0) && ($arg > 0) && ($island['money'] > $cost)) {
							// 撃ったのが確定なので、各値を消耗させる
							$level--;
							$arg--;
							$island['money'] -= $cost;
							$missiles++;

							// 着弾点算出
							$r = Util::random($err);
							$tx = $x + $init->ax[$r];
							$ty = $y + $init->ay[$r];
							if((($ty % 2) == 0) && (($y % 2) == 1)) {
								$tx--;
							}
							// 着弾点範囲内外チェック
							if(($tx < 0) || ($tx >= $init->islandSize) || ($ty < 0) || ($ty >= $init->islandSize)) {
								// 範囲外
								$missileA++;
								continue;
							}
							// 着弾点の地形等算出
							$tL     = $tLand[$tx][$ty];
							$tLv    = $tLandValue[$tx][$ty];
							$tLname = $this->landName($tL, $tLv);
							$tPoint = "({$tx}, {$ty})";

							// 防衛施設判定
							$defence = 0;
							if ( isset($defenceHex[$id][$tx][$ty]) ) {
								if($defenceHex[$id][$tx][$ty] == 1) {
									$defence = 1;
								} elseif($defenceHex[$id][$tx][$ty] == -1) {
									$defence = 0;
								}
							} else {
								if(($tL == $init->landDefence) || ($tL == $init->landSdefence) || ($tL == $init->landProcity)) {
									// 防衛施設に命中
									if(($tLv > 1) &&
										(($kind == $init->comMissileNM) ||
										($kind == $init->comMissilePP) ||
										($kind == $init->comMissileST))) {
										// 防衛施設の耐久力を下げる
										$tLv --;
									} elseif($kind == $init->comMissileSP) {
										break;
									} else {
										// 耐久力が１か、他のミサイル直撃なら、防衛施設破壊
										$tLv = 0;
										// フラグをクリア
										for($i = 0; $i < 19; $i++) {
											$sx = $tx + $init->ax[$i];
											$sy = $ty + $init->ay[$i];
											// 行による位置調整
											if((($sy % 2) == 0) && (($ty % 2) == 1)) {
												$sx--;
											}
											if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
												// 範囲外の場合何もしない
											} else {
												// 範囲内の場合
												$defenceHex[$id][$sx][$sy] = 0;
											}
										}
									}
								} elseif(Turn::countAround($tLand, $tx, $ty, 19, array($init->landDefence, $init->landSdefence)) +
									Turn::countAround($tLand, $tx, $ty, 7, array($init->landProcity))) {
									$defenceHex[$id][$tx][$ty] = 1;
									$defence = 1;
								} else {
									$defenceHex[$id][$tx][$ty] = -1;
									$defence = 0;
								}
							}
							/*
							if($defenceHex[$id][$tx][$ty] == 1) {
								$defence = 1;
							} elseif($defenceHex[$id][$tx][$ty] == -1) {
								$defence = 0;
							} else {
								if(($tL == $init->landDefence) || ($tL == $init->landSdefence) || ($tL == $init->landProcity)) {
									// 防衛施設に命中
									if(($tLv > 1) &&
										(($kind == $init->comMissileNM) ||
										($kind == $init->comMissilePP) ||
										($kind == $init->comMissileST))) {
										// 防衛施設の耐久力を下げる
										$tLv --;
									} elseif($kind == $init->comMissileSP) {
										break;
									} else {
										// 耐久力が１か、他のミサイル直撃なら、防衛施設破壊
										$tLv = 0;
										// フラグをクリア
										for($i = 0; $i < 19; $i++) {
											$sx = $tx + $init->ax[$i];
											$sy = $ty + $init->ay[$i];
											// 行による位置調整
											if((($sy % 2) == 0) && (($ty % 2) == 1)) {
												$sx--;
											}
											if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
												// 範囲外の場合何もしない
											} else {
												// 範囲内の場合
												$defenceHex[$id][$sx][$sy] = 0;
											}
										}
									}
								} elseif(Turn::countAround($tLand, $tx, $ty, 19, array($init->landDefence, $init->landSdefence)) +
									Turn::countAround($tLand, $tx, $ty, 7, array($init->landProcity))) {
									$defenceHex[$id][$tx][$ty] = 1;
									$defence = 1;
								} else {
									$defenceHex[$id][$tx][$ty] = -1;
									$defence = 0;
								}
							}
							*/
							if($defence == 1) {
								// 空中爆破
								$missileB++;
								continue;
							}
							if($island['id'] != $tIsland['id']) {
								// 防衛衛星がある場合
								if($tIsland['eisei'][4] && (Util::random(5000) < $tIsland['rena'])) {
									$tIsland['eisei'][4] -= 2;
									if($tIsland['eisei'][4] < 1) {
										$tIsland['eisei'][4] = 0;
										$this->log->EiseiEnd($target, $tName, $init->EiseiName[4]);
									}
									$missileB++;
									continue;
								}
							}
							// 「効果なし」hexを最初に判定
							if (($kind != $init->comMissileLU) && // 地形隆起弾でなく
								((($tL == $init->landSea) && ($tLv == 0))|| // 深い海
								(((($tL == $init->landSea) && ($tLv < 2)) || // 海または・・・
								(($tL == $init->landPoll) && ($kind != $init->comMissileBT)) || // 汚染土壌または・・・
								($tL == $init->landSbase) || // 海底基地または・・・
								(($tL == $init->landProcity) &&
								(Turn::countAround($tLand, $tx, $ty, 19, array($init->landDefence, $init->landSdefence)))) || // 防災都市または・・・
								($tL == $init->landSeaCity) || // 海底都市または・・・
								($tL == $init->landMountain)) // 山で・・・
								&& ($kind != $init->comMissileLD)))) { // 陸破弾以外
								// 海底基地の場合、海のフリ
								if($tL == $init->landSbase) {
									$tL = $init->landSea;
								}
								$tLname = $this->landName($tL, $tLv);
								$missileA++;
								continue;
							}
							// 弾の種類で分岐
							if($kind == $init->comMissileLD) {
								// 陸地破壊弾
								switch($tL) {
									case $init->landMountain:
										// 山(荒地になる)
										$this->log->msLDMountain($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);

										// 荒地になる
										$tLand[$tx][$ty] = $init->landWaste;
										$tLandValue[$tx][$ty] = 0;
										continue 2;

									case $init->landSbase:
									case $init->landSdefence:
									case $init->landSeaCity:
									case $init->landFroCity:
									case $init->landSsyoubou:
									case $init->landSfarm:
										// 海底基地、海底都市、海上都市、海底消防署、海底防衛施設、海底農場
										$this->log->msLDSbase($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										break;

									case $init->landMonster:
									case $init->landSleeper:
									case $init->landZorasu:
										// 怪獣
										$this->log->msLDMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										break;

									case $init->landSea:
										// 浅瀬
										$this->log->msLDSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										break;

									case $init->landSeaSide:
										// 砂浜なら水没
										$this->log->msLDSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										$tLand[$tx][$ty] = $init->landSea;
										$tIsland['area']--;
										$tLandValue[$tx][$ty] = 1;
										break;

									default:
										// その他
										$this->log->msLDLand($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
								}
								// 経験値
								if(($tL == $init->landTown) || ($tL == $init->landSeaCity) || ($tL == $init->landFroCity) ||
									($tL == $init->landNewtown) || ($tL == $init->landBigtown)) {
									if(($land[$bx][$by] == $init->landBase) ||
										($land[$bx][$by] == $init->landSbase)) {
										// まだ基地の場合のみ
										$landValue[$bx][$by] += round($tLv / 20);
										if($landValue[$bx][$by] > $init->maxExpPoint) {
											$landValue[$bx][$by] = $init->maxExpPoint;
										}
									}
								}
								// 浅瀬になる
								$tLand[$tx][$ty] = $init->landSea;
								$tIsland['area']--;
								$tLandValue[$tx][$ty] = 1;

								// でも油田、浅瀬、海底基地、海底都市、海底消防署、海底農場、海底防衛施設だったら海
								if(($tL == $init->landOil) ||
									($tL == $init->landSea) ||
									($tL == $init->landSeaCity) ||
									($tL == $init->landSsyoubou) ||
									($tL == $init->landSfarm) ||
									($tL == $init->landSdefence) ||
									($tL == $init->landSbase) ||
									($tL == $init->landZorasu)) {
									$tLandValue[$tx][$ty] = 0;
								}
								// でも養殖場だったら浅瀬
								if($tL == $init->landNursery) {
									$tLandValue[$tx][$ty] = 1;
								}
							} elseif($kind == $init->comMissileLU) {
								// 地形隆起弾
								switch($tL) {
									case $init->landMountain:
										// 山
										continue;

									case $init->landSbase:
									case $init->landSdefence:
									case $init->landSeaCity:
									case $init->landFroCity:
									case $init->landSsyoubou:
									case $init->landSfarm:
									case $init->landZorasu:
										// 海底基地、海底都市、海上都市、海底消防署、海底防衛施設、海底農場、ぞらす
										$this->log->msLUSbase($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										$tLand[$tx][$ty] = $init->landSea;
										$tLandValue[$tx][$ty] = 1;
										continue;

									case $init->landSea:
										// 海の場合
										if ($tLv == 1) {
											// 荒地になる
											$this->log->msLUSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											$tLand[$tx][$ty] = $init->landWaste;
											$tLandValue[$tx][$ty] = 1;
											$tIsland['area']++;
											if($seaCount <= 4) {
												// 周りの海が3ヘックス以内なので、浅瀬にする
												for($i = 1; $i < 7; $i++) {
													$sx = $x + $init->ax[$i];
													$sy = $y + $init->ax[$i];
													// 行による位置調整
													if((($sy % 2) == 0) && (($y % 2) == 1)) {
														$sx--;
													}
													if(!(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize))) {
														// 範囲内の場合
														if($tLand[$sx][$sy] == $init->landSea) {
															$tLandValue[$sx][$sy] = 1;
														}
													}
												}
											}
										} else {
											// 浅瀬になる
											$this->log->msLUSea0($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											$tLandValue[$tx][$ty] = 1;
										}
										continue;

									case $init->landMonster:
									case $init->landSleeper:
										// 怪獣
										$missileD++;
										// 山になる
										$tLand[$tx][$ty] = $init->landMountain;
										$tLandValue[$tx][$ty] = 0;
										$this->log->msLUMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										continue;

									default:
										// その他
										$this->log->msLULand($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										// 経験値
										if($tL == $init->landTown) {
											if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
												// まだ基地の場合のみ
												$landValue[$bx][$by] += round($tLv / 20);
												if($landValue[$bx][$by] > $init->maxExpPoint) {
													$landValue[$bx][$by] = $init->maxExpPoint;
												}
											}
										}
										// 山になる
										$tLand[$tx][$ty] = $init->landMountain;
										$tLandValue[$tx][$ty] = 0;
								}
								continue;
							} elseif($kind != $init->comMissileSP) {
								// その他ミサイル
								if($tL == $init->landWaste) {
									// 荒地
									if($kind == $init->comMissileBT) {
										$this->log->msPollution($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}else{
										$missileA++;
									}
								} elseif(($tL == $init->landMonster) || ($tL == $init->landSleeper)) {
									// 怪獣
									$monsSpec = Util::monsterSpec($tLv);
									$special = $init->monsterSpecial[$monsSpec['kind']];

									// 硬化中?
									if((($special & 0x4) && (($hako->islandTurn % 2) == 1)) || (($special & 0x10) && (($hako->islandTurn % 2) == 0))) {
										// 硬化中
										if($kind == $init->comMissileST) {
											// ステルス
											$this->log->msMonNoDamageS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										} else {
											// 通常弾
											$this->log->msMonNoDamage($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										}
										$missileC++;
										continue;
									} else {
										// 硬化中じゃない
										if(($special & 0x100) && (Util::random(100) < 30)) {
											// ミサイル叩き落とす
											if($kind == $init->comMissileST) {
												$this->log->msMonsCaughtS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											} else {
												$this->log->msMonsCaught($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											}
											$missileC++;
											continue;
										}
										if(($kind == $init->comMissileBT) && (Util::random(100) < 10)) {
											// バイオミサイルで突然変異
											$kind = Util::random($init->monsterNumber);
											$lv = $kind * 100 + $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
											$tLand[$tx][$ty] = $init->landMonster;
											$tLandValue[$tx][$ty] = $lv;
											$this->log->msMutation($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										}
										if($monsSpec['hp'] == 1) {
											// 怪獣しとめた
											if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
												// 経験値
												$landValue[$bx][$by] += $init->monsterExp[$monsSpec['kind']];
												if($landValue[$bx][$by] > $init->maxExpPoint) {
													$landValue[$bx][$by] = $init->maxExpPoint;
												}
											}
											$missileD++;

											if((Util::random(100) < 7) && ($island['item'][8] == 1) && ($island['item'][9] != 1)) {
												// マスターソード発見
												$island['item'][9] = 1;
												$this->log->SwordFound($id, $name, $tLname);
											}
											// 収入
											$value = $init->monsterValue[$monsSpec['kind']];
											if($value > 0) {
												if(($id != $target) && ($target != 1)) {
													$tIsland['money'] += (int)($value / 2);
													$island['money'] += (int)($value / 2);
												} else {
													$tIsland['money'] += $value;
												}
											}
											if($kind == $init->comMissileST) {
												// ステルス
												$this->log->msMonMoneyS($id, $target, $tLname, $value);
												$this->log->msMonsKillS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											} else {
												// 通常
												$this->log->msMonMoney($id, $target, $tLname, $value);
												$this->log->msMonsKill($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											}
											// 怪獣退治数
											$island['taiji']++;

											// 賞関係
											// $prize = $island['prize'];
											list($flags, $monsters, $turns) = explode(",", $prize, 3);
											$v = 1 << $monsSpec['kind'];
											$monsters |= $v;

											if ((!($flags & 512)) && $island['taiji'] == 100) {
												// 100匹退治で素人討伐賞
												$flags |= 512;
												$this->log->prize($id, $name, $init->prizeName[10]);
											} elseif ((!($flags & 1024)) && $island['taiji'] == 300) {
												// 300匹退治で討伐賞
												$flags |= 1024;
												$this->log->prize($id, $name, $init->prizeName[11]);
											} elseif ((!($flags & 2048)) && $island['taiji'] == 500) {
												// 500匹退治で超討伐賞
												$flags |= 2048;
												$this->log->prize($id, $name, $init->prizeName[12]);
											} elseif ((!($flags & 4096)) && $island['taiji'] == 700) {
												// 700匹退治で究極討伐賞
												$flags |= 4096;
												$this->log->prize($id, $name, $init->prizeName[13]);
											} elseif ((!($flags & 8192)) && $island['taiji'] == 1000) {
												// 1000匹退治で討伐王
												$flags |= 8192;
												$this->log->prize($id, $name, $init->prizeName[14]);
											}
											$prize = "{$flags},{$monsters},{$turns}";
											// $island['prize'] = "{$flags},{$monsters},{$turns}";

											// 荒れ地になる
											$tLand[$tx][$ty] = $init->landWaste;
											$tLandValue[$tx][$ty] = 1; // 着弾点
											continue;
										} else {
											// 怪獣生きてる
											if($kind == $init->comMissileST) {
												// ステルス
												$this->log->msMonsterS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											} else {
												// 通常
												$this->log->msMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											}
											// HPが1減る
											$tLandValue[$tx][$ty]--;
											$missileD++;
											continue;
										}
									}
								} elseif($tL == $init->landShip) {
									// 船舶
									$ship = Util::navyUnpack($tLv);
									if(($ship[1] == 3) && (Util::random(1000) < $init->shipIntercept)) {
										// 戦艦ミサイル迎撃
										$missileE++;
										continue;
									}
									if(($ship[1] == 2 || $ship[1] == 3) && ($ship[2] > 20)) {
										// 海底探索船または戦艦の場合
										$tLname = $init->shipName[$ship[1]];
										$tLname .= "（{$this->islands[$ship[0]]['name']}島所属）";
										if($kind == $init->comMissileST) {
											// ステルス
											$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										} else {
											// 通常
											$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										}
										$ship[2] -= 2;
										$tLandValue[$tx][$ty] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
									} else {
										$tLand[$tx][$ty] = $init->landSea;
										$tLandValue[$tx][$ty] = 0;
									}
								} elseif(($tL == $init->landDefence || $tL == $init->landSdefence) && ($tLv > 1)) {
									// 防衛施設（規模減少）
									if($kind == $init->comMissileST) {
										// ステルス
										$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// 通常
										$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLandValue[$tx][$ty] = $tLv;
								} elseif((($tL == $init->landFarm) && ($tLv > 25)) || (($tL == $init->landSfarm) && ($tLv > 20))) {
									// 農場、海底農場（規模減少）
									if($kind == $init->comMissileST) {
										// ステルス
										$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// 通常
										$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLandValue[$tx][$ty] -= 5;
								} elseif((($tL == $init->landFactory) && ($tLv > 100)) ||
									(($tL == $init->landHatuden) && ($tLv > 500)) ||
									(($tL == $init->landCommerce) && ($tLv > 150)) ||
									(($tL == $init->landProcity) && ($tLv >= 160))) {
									// 工場、発電所、商業ビル、防災都市（規模減少）
									if($kind == $init->comMissileST) {
										// ステルス
										$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// 通常
										$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLandValue[$tx][$ty] -= 20;
								} elseif(($tL == $init->landNursery) || ($tL == $init->landSeaSide)) {
									// 養殖場、砂浜だったら浅瀬
									if($kind == $init->comMissileST) {
										// ステルス
										$this->log->msNormalS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// 通常
										$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLand[$tx][$ty] = $init->landSea;
									$tLandValue[$tx][$ty] = 1;
								} elseif(($tL == $init->landShip) || ($tL == $init->landFroCity) ||
									($tL == $init->landOil) || ($tL == $init->landSdefence) ||
									($tL == $init->landSsyoubou) || ($tL == $init->landSfarm) || ($tL == $init->landZorasu)) {
									// 船、海上都市、油田、海底防衛施設、海底消防署、海底農場だったら海
									if($kind == $init->comMissileST) {
										// ステルス
										$this->log->msNormalS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// 通常
										$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLand[$tx][$ty] = $init->landSea;
									$tLandValue[$tx][$ty] = 0;
								} else {
									// 通常地形
									if($kind == $init->comMissileST) {
										// ステルス
										$this->log->msNormalS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										// 荒地になる
										$tLand[$tx][$ty] = $init->landWaste;
										$tLandValue[$tx][$ty] = 1; // 着弾点
									} elseif($kind == $init->comMissileBT) {
										// バイオミサイルの時は汚染
										if(($tL == $init->landPoll) && ($tLandValue[$tx][$ty] < 3)) {
											$tLandValue[$tx][$ty]++;
										} elseif($tL != $init->landPoll) {
											// 汚染土壌になる
											$tLand[$tx][$ty] = $init->landPoll;
											$tLandValue[$tx][$ty] = Util::random(2) + 1;
										}
										$this->log->msPollution($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// 通常
										$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										// 荒地になる
										$tLand[$tx][$ty] = $init->landWaste;
										$tLandValue[$tx][$ty] = 1; // 着弾点
									}
								}
								// 経験値
								if(($tL == $init->landTown) || ($tL == $init->landSeaCity) || ($tL == $init->landFroCity) ||
									($tL == $init->landNewtown) || ($tL == $init->landBigtown)) {
									if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
										$landValue[$bx][$by] += round($tLv / 20);
										$boat += $tLv; // 通常ミサイルなので難民にプラス
										if($landValue[$bx][$by] > $init->maxExpPoint) {
											$landValue[$bx][$by] = $init->maxExpPoint;
										}
									}
								}
							} else {
								if(($tL == $init->landMonster) && (Util::random(100) < 20)) {
									// 捕獲に成功
									$tLand[$tx][$ty] = $init->landSleeper;

									$this->log->MsSleeper($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
								} else {
									$missileA++;
								}
							}
						}
						// カウント増やしとく
						$count++;
					}
					// ミサイルログ
					if($missiles > 0){
						if($kind == $init->comMissileST) {
							// ステルス
							$this->log->mslogS($id, $target, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE);
						} else {
							// 通常
							$this->log->mslog($id, $target, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE);
						}
					}
					if($flag == 0) {
						// 基地が一つも無かった場合
						$this->log->msNoBase($id, $name, $comName);
						$returnMode = 0;
						break;
					}
					$tIsland['land'] = $tLand;
					$tIsland['landValue'] = $tLandValue;
					unset($hako->islands[$tn]);
					$hako->islands[$tn] = $tIsland;

					// 難民判定
					$boat = round($boat / 2);
					if(($boat > 0) && ($id != $target) && ($kind != $init->comMissileST)) {
						// 難民漂着
						$achive = 0; // 到達難民
						for($i = 0; ($i < $init->pointNumber && $boat > 0); $i++) {
							$bx = $this->rpx[$i];
							$by = $this->rpy[$i];
							if(($land[$bx][$by] == $init->landTown) || ($land[$bx][$by] == $init->landSeaCity) ||
								($land[$bx][$by] == $init->landFroCity)) {
								// 町の場合
								$lv = $landValue[$bx][$by];
								if($boat > 50) {
									$lv += 50;
									$boat -= 50;
									$achive += 50;
								} else {
									$lv += $boat;
									$achive += $boat;
									$boat = 0;
								}
								if($lv > 200) {
									$boat += ($lv - 200);
									$achive -= ($lv - 200);
									$lv = 200;
								}
								$landValue[$bx][$by] = $lv;
							} elseif($land[$bx][$by] == $init->landPlains) {
								// 平地の場合
								$land[$bx][$by] = $init->landTown;;
								if($boat > 10) {
									$landValue[$bx][$by] = 5;
									$boat -= 10;
									$achive += 10;
								} elseif($boat > 5) {
									$landValue[$bx][$by] = $boat - 5;
									$achive += $boat;
									$boat = 0;
								}
							}
							if($boat <= 0) {
								break;
							}
						}
						if($achive > 0) {
							// 少しでも到着した場合、ログを吐く
							$this->log->msBoatPeople($id, $name, $achive);

							// 難民の数が一定数以上なら、平和賞の可能性あり
							if($achive >= 200) {
								$prize = $island['prize'];
								list($flags, $monsters, $turns) = explode(",", $prize, 3);
								if((!($flags & 8)) && $achive >= 200){
									$flags |= 8;
									$this->log->prize($id, $name, $init->prizeName[4]);
								} elseif((!($flags & 16)) && $achive > 500){
									$flags |= 16;
									$this->log->prize($id, $name, $init->prizeName[5]);
								} elseif((!($flags & 32)) && $achive > 800){
									$flags |= 32;
									$this->log->prize($id, $name, $init->prizeName[6]);
								}
								$island['prize'] = "{$flags},{$monsters},{$turns}";
							}
						}
					}
					$command = $comArray[0];
					$kind    = $command['kind'];
					if((($kind == $init->comMissileNM) || // 次もミサイル系なら...
						($kind == $init->comMissilePP) ||
						($kind == $init->comMissileST) ||
						($kind == $init->comMissileBT) ||
						($kind == $init->comMissileSP) ||
						($kind == $init->comMissileLD) ||
						($kind == $init->comMissileLU)) &&
						($init->multiMissiles)) {
						$island['fire'] -= $comp;
						$cost = $init->comCost[$kind];

						if($island['fire'] < 1) {
							// 最大発射数を超えた場合
							$this->log->msMaxOver($id, $name, $comName);
							$returnMode = 0;
							break;
						}
						if (($island['fire'] > 0) && ($island['money'] >= $cost)) { // 少なくとも1発は撃てる
							Util::slideFront($comArray, 0);
							$island['command'] = $comArray;
							$kind = $command['kind'];
							$target   = $command['target'];
							$x        = $command['x'];
							$y        = $command['y'];
							$arg      = $command['arg'];
							$comName  = $init->comName[$kind];
							$point    = "({$x},{$y})";
							$landName = $this->landName($landKind, $lv);
						} else {
							break;
						}
					} else if($kind == $init->comMissileSM) {
						Util::slideFront($comArray, 0);
						break;
					} else {
						break;
					}
				} while ($island['fire'] > 0);
				$returnMode = 1;
				break;

			case $init->comSendMonster:
				// 怪獣派遣
				// ターゲット取得
				$tn = $hako->idToNumber[$target];
				$tIsland = $hako->islands[$tn];
				$tName = $tIsland['name'];

				if($tn !== 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// 目標の島が管理人預かり中のため実行が許可されない
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile) || ($island['zin'][2] != 1)) {
					// 実行許可ターンを経過したか？
					$this->log->Forbidden($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// メッセージ
				$this->log->monsSend($id, $target, $name, $tName);
				$tIsland['monstersend']++;
				$hako->islands[$tn] = $tIsland;

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comSendSleeper:
				// 怪獣輸送
				// ターゲット取得
				$tn = &$hako->idToNumber[$target];
				$tIsland = &$hako->islands[$tn];
				$tName = &$tIsland['name'];

				if($tn != 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// 目標の島が管理人預かり中のため実行が許可されない
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
					// 実行許可ターンを経過したか？
					$this->log->Forbidden($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// 睡眠中の怪獣がいるか調べる
				$tLand = &$tIsland['land'];
				$tLandValue = &$tIsland['landValue'];

				if($landKind != $init->landSleeper) {
					// 睡眠中の怪獣がいない
					$this->log->MonsNoSleeper($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// メッセージ
				$this->log->monsSendSleeper($id, $target, $name, $tName, $landName);

				// どこに現れるか決める
				for($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if($tLand[$bx][$by] == $init->landTown) {
						// 地形名
						$lName = &$this->landName($init->landTown, $tLandValue[$bx][$by]);

						// そのヘックスを怪獣に
						$tLand[$bx][$by] = $init->landMonster;
						$tLandValue[$bx][$by] = $lv;

						// 睡眠中の怪獣を荒れ地に
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;

						// 怪獣情報
						$monsSpec = Util::monsterSpec($lv);
						$mName    = $monsSpec['name'];

						// メッセージ
						$this->log->monsCome($target, $tName, $mName, "({$bx}, {$by})", $lName);
						break;
					}
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comOffense:
				// 攻撃力強化
				if($island['soccer'] <= 0){
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(Util::random(100) < 60) {
					$island['kougeki'] += Util::random(3) + 1;
				}
				$this->log->SoccerSuc($id, $name, $comName);

				if($arg > 1) {
					$arg--;
					Util::slideBack($comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comDefense:
				// 守備力強化
				if($island['soccer'] <= 0){
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(Util::random(100) < 60) {
					$island['bougyo'] += Util::random(3) + 1;
				}
				$this->log->SoccerSuc($id, $name, $comName);

				if($arg > 1) {
					$arg--;
					Util::slideBack($comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comPractice:
				// 総合練習
				if($island['soccer'] <= 0){
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(Util::random(100) < 60) {
					$island['bougyo'] += Util::random(3) + 1;
					$island['kougeki'] += Util::random(3) + 1;
				}
				$this->log->SoccerSuc($id, $name, $comName);

				if($arg > 1) {
					$arg--;
					Util::slideBack($comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comPlaygame:
				// 交流試合
				if($island['soccer'] <= 0) {
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($id == $target) {
					$this->log->SoccerFail2($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ターゲット取得
				$tn = $hako->idToNumber[$target];
				$tIsland = &$hako->islands[$tn];
				$tName   = $tIsland['name'];
				$tLand   = $tIsland['land'];
				$tLandValue = $tIsland['landValue'];

				if($tn !== 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['soccer'] <= 0) {
					$this->log->GameFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// 目標の島が管理人預かり中のため実行が許可されない
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(($island['kougeki'] > $tIsland['kougeki']) && ($island['bougyo'] > $tIsland['bougyo'])) {
					// 攻撃力、守備力ともに上
					$da = Util::random(7) + 3;
					$db = Util::random(5) + 3;
					$ba = Util::random(7);
					$bb = Util::random(5);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// 勝ち
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// 負け
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// 引き分け
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] > $tIsland['kougeki']) && ($island['bougyo'] < $tIsland['bougyo'])) {
					// 攻撃力は上、守備力は下
					$da = Util::random(7) + 3;
					$db = Util::random(5) + 3;
					$ba = Util::random(5);
					$bb = Util::random(7);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// 勝ち
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// 負け
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// 引き分け
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] < $tIsland['kougeki']) && ($island['bougyo'] > $tIsland['bougyo'])) {
					// 攻撃力は下、守備力は上
					$da = Util::random(5) + 3;
					$db = Util::random(7) + 3;
					$ba = Util::random(7);
					$bb = Util::random(5);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// 勝ち
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// 負け
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// 引き分け
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] < $tIsland['kougeki']) && ($island['bougyo'] < $tIsland['bougyo'])) {
					// 攻撃力、守備力ともに下
					$da = Util::random(5) + 3;
					$db = Util::random(7) + 3;
					$ba = Util::random(5);
					$bb = Util::random(7);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// 勝ち
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// 負け
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// 引き分け
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] == $tIsland['kougeki']) && ($island['bougyo'] == $tIsland['bougyo'])) {
					// 攻撃力、守備力ともにいっしょ
					$da = Util::random(5) + 3;
					$db = Util::random(5) + 3;
					$ba = Util::random(5);
					$bb = Util::random(5);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// 勝ち
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// 負け
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// 引き分け
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				}
				$island['shiai'] ++;
				$tIsland['shiai'] ++;

				// 金を差し引く
				$island['money'] -= $cost;

				$returnMode = 1;
				break;

			case $init->comSell:
				// 輸出量決定
				if($arg == 0) { $arg = 1; }
				$value = min($arg * (-$cost), $island['food']);
				$unit = $init->unitFood;
				// 輸出ログ
				$this->log->sell($id, $name, $comName, $value, $unit);
				$island['food'] -=  $value;
				$island['money'] += ($value / 10);

				$returnMode = 0;
				break;

			case $init->comSellTree:
				// 輸出量決定
				if($arg == 0) { $arg = 1; }
				$value = min($arg * (-$cost), $island['item'][20]);
				$unit = $init->unitTree;
				// 輸出ログ
				$this->log->sell($id, $name, $comName, $value, $unit);
				$island['item'][20] -=  $value;
				$island['money'] += $value * $init->treeValue;

				$returnMode = 0;
				break;

			case $init->comFood:
			case $init->comMoney:
				// 援助系
				// ターゲット取得
				$tn = $hako->idToNumber[$target];
				$tIsland = &$hako->islands[$tn];
				$tName = $tIsland['name'];

				if($tn !== 0 && empty($tn)) {
					// ターゲットがすでにない
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// 目標の島が管理人預かり中のため実行が許可されない
					$this->log->CheckKP($id, $name, $comName);

					$returnMode = 0;
					break;
				}
				if((($hako->islandTurn - $island['starturn']) < $init->noAssist) || (($hako->islandTurn - $tIsland['starturn']) < $init->noAssist)) {
					// 実行許可ターンを経過したか？
					$this->log->Forbidden($id, $name, $comName);

					$returnMode = 0;
					break;
				}
				// 援助量決定
				if($arg == 0) { $arg = 1; }

				if($cost < 0) {
					$value = min($arg * (-$cost), $island['food']);
					$str = "{$value}{$init->unitFood}";
				} else {
					$value = min($arg * ($cost), $island['money']);
					$str = "{$value}{$init->unitMoney}";
				}
				// 援助ログ
				$this->log->aid($id, $target, $name, $tName, $comName, $str);

				if($cost < 0) {
					$island['food'] -= $value;
					$tIsland['food'] += $value;
				} else {
					$island['money'] -= $value;
					$tIsland['money'] += $value;
				}
				$hako->islands[$tn] = $tIsland;
				$returnMode = 0;
				break;

			case $init->comLot:
				// 宝くじ購入
				if($island['lot'] > 30){
					// 宝くじ完売
					$this->log->noLot($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($arg == 0) { $arg = 1; }
				if($arg > 30) { $arg = 30; }

				$value = min($arg * ($cost), $island['money']);
				$str = "{$value}{$init->unitMoney}";
				$p = round($value / $cost);
				$island['lot'] += $p;

				// 購入ログ
				$this->log->buyLot($id, $name, $comName, $str);

				// 金を差し引く
				$island['money'] -= $value;

				$returnMode =  1;
				break;

			case $init->comPropaganda:
				// 誘致活動
				$island['propaganda'] = 1;
				$island['money'] -= $cost;
				$this->log->propaganda($id, $name, $comName);

				if($arg > 1) {
					$arg--;
					Util::slideBack($comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;

			case $init->comGiveup:
				// 放棄
				$this->log->giveup($id, $name);
				$island['dead'] = 1;
				unlink("{$init->dirName}/island.{$id}");
				$returnMode = 1;
				break;
		}
		// 変更された可能性のある変数を書き戻す
		// $hako->islands[$hako->idToNumber[$id]] = $island;
		// 事後処理
		unset($island['prize']);
		unset($island['land']);
		unset($island['landValue']);
		unset($island['command']);
		$island['prize'] = $prize;
		$island['land'] = $land;
		$island['landValue'] = $landValue;
		$island['command'] = $comArray;

		return $returnMode;
	}

	//---------------------------------------------------
	// 成長および単ヘックス災害
	//---------------------------------------------------
	function doEachHex(&$hako, &$island) {
		global $init;

		// 導出値
		$name = $island['name'];
		$id = $island['id'];
		$land = $island['land'];
		$landValue = $island['landValue'];
		$oilFlag = $island['oil'];

		// 増える人口のタネ値
		$addpop  = 10; // 村、町
		$addpop2 = 0;  // 都市

		// ???
		$pop = $island['pop'];

		//

		if ( isset($island['food']) ) {}
		if ( !isset($island['propaganda']) ) {
			$island['propaganda'] = "";
		}

		if($island['food'] <= 0) {
			// 食料不足
			$addpop = -30;
		} elseif(($island['ship'][10] + $island['ship'][11] + $island['ship'][12] + $island['ship'][13] + $island['ship'][14]) > 0) {
			// 海賊船が出没中は成長しない
			$addpop = 0;
		} elseif($island['park'] > 0) {
			// 遊園地があると人が集まる
			$addpop  += 10;
			$addpop2 += 1;
		} elseif($island['propaganda'] == 1) {
			// 誘致活動中
			$addpop = 30;
			$addpop2 = 3;
		} else {

		}
		$monsterMove = array();
		$bx = 0;
		$by = 0;

		// ループ
		for($i = 0; $i < $init->pointNumber; $i++) {
			$x = $this->rpx[$i];
			$y = $this->rpy[$i];
			$landKind = $land[$x][$y];
			$lv = $landValue[$x][$y];

			switch($landKind) {
				case $init->landWaste:
					//荒地
					if ($island['isBF'] == 1) {
						$land[$x][$y] = $init->landPlains;
						$landValue[$x][$y] = 0;
					}
					break;

				case $init->landTown:
				case $init->landSeaCity:
					// 町系
					if($addpop < 0) {
						// 不足
						$lv -= (Util::random(-$addpop) + 1);
						if(($lv <= 0) && ($landKind == $init->landSeaCity)) {
							// 海に戻す
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
							continue;
						} elseif(($lv <= 0) && ($landKind == $init->landTown)) {
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// 成長
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// 都市になると成長遅い
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 250) {
						$lv = 250;
					}
					$landValue[$x][$y] = $lv;
					break;

				case $init->landNewtown:
					// ニュータウン系
					$townCount = Turn::countAround($land, $x, $y, 19, array($init->landTown, $init->landNewtown, $init->landBigtown));
					if($townCount > 17) {
						if(Util::random(1000) < 3) {
							if($lv > 200) {
								$land[$x][$y] = $init->landBigtown;
							}
						}
					}
					if($addpop < 0) {
						// 不足
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// 平地に戻す
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// 成長
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// 都市になると成長遅い
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 300) {
						$lv = 300;
					}
					$landValue[$x][$y] = $lv;
					break;

				case $init->landBigtown:
					// 現代都市系
					if($addpop < 0) {
						// 不足
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// 平地に戻す
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// 成長
						if($lv < 200) {
							$lv += Util::random($addpop) + 1;
							if($lv > 200) {
								$lv = 200;
							}
						} else {
							// 都市になると成長遅い
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 500) {
						$lv = 500;
					}
					$landValue[$x][$y] = $lv;
					break;

				case $init->landPlains:
					// 平地
					if ($island['isBF'] == 1) { // BF勝手に村生成
						$land[$x][$y] = $init->landTown;
						$landValue[$x][$y] = 10;
					} elseif(Util::random(5) == 0) {
						// 周りに農場、町があれば、ここも町になる
						if($this->countGrow($land, $landValue, $x, $y)){
							$land[$x][$y] = $init->landTown;
							$landValue[$x][$y] = 1;
							if(Util::random(1000) < 75) {
								$land[$x][$y] = $init->landNewtown;
								$landValue[$x][$y] = 1;
							}
						}
					}
					break;

				case $init->landPoll:
					// 汚染土壌
					if(Util::random(3) == 0) {
						// 汚染浄化
						$land[$x][$y] = $init->landPoll;
						$landValue[$x][$y]--;
						if(($landKind == $init->landPoll) && ($landValue[$x][$y] == 0)) {
							// 汚染浄化され平地になる
							$land[$x][$y] = $init->landPlains;
						}
					}
					break;

				case $init->landProcity:
					// 防災都市
					if($addpop < 0) {
						// 不足
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// 平地に戻す
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// 成長
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// 都市になると成長遅い
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 200) {
						$lv = 200;
					}
					$landValue[$x][$y] = $lv;
					break;

				case $init->landFroCity:
					// 海上都市
					if($addpop < 0) {
						// 不足
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// 海に戻す
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						}
					} else {
						// 成長
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// 都市になると成長遅い
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 250) {
						$lv = 250;
					}
					// 動く方向を決定
					for($fro = 0; $fro < 3; $fro++) {
						$d = Util::random(6) + 1;
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// 行による位置調整
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// 範囲外判定
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// 海しか動かない
						if(($land[$sx][$sy] == $init->landSea) && ($landValue[$sx][$sy] == 0)) {
							break;
						}
					}
					if($fro == 3) {
						// 動かなかった
						break;
					}
					// 移動
					$land[$sx][$sy] = $land[$x][$y];
					$landValue[$sx][$sy] = $lv;

					// もと居た位置を海に
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
					break;

				case $init->landForest:
					// 森
					if($lv < 200) {
						// 木を増やす
						if($island['zin'][3] == 1) {
							$landValue[$x][$y] += 2;
						} else {
							$landValue[$x][$y]++;
						}
					}
					break;

				case $init->landCommerce:
					// 商業ビル
					if(Util::random(1000) < $init->disSto) {
						// ストライキ
						$landValue[$x][$y] -= 5;
						if($landValue[$x][$y] <= 0){
							$land[$x][$y] = $init->landCommerce;
							$landValue[$x][$y] = 0;
						}
						$this->log->Sto($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
					}
					break;

				case $init->landMonument:
					// 記念碑
					$lv = $landValue[$x][$y];
					$lName = $this->landName($landKind, $lv);

					if(($lv == 5) || ($lv == 6) || ($lv == 21) || ($lv == 24) || ($lv == 32)) {
						if(util::random(100) < 5) {
							// お土産
							$value = 1+ Util::random(49);
							if ($value > 0) {
								$island['money'] += $value;
								$str = "{$value}{$init->unitMoney}";
								$this->log->Miyage($id, $name, $lName, "($x,$y)", $str);
								break;
							}
						}
					} elseif(($lv == 1) || ($lv == 7) || ($lv == 33)) {
						if(util::random(100) < 5) {
							// 収穫
							$value = round($island['pop'] / 100) * 10 + Util::random(11);
							// 人口１万人ごとに1000トンの収穫
							if ($value > 0) {
								$island['food'] += $value;
								$str = "{$value}{$init->unitFood}";
								$this->log->Syukaku($id, $name, $lName, "($x,$y)", $str);
								break;
							}
						}
					} elseif($lv == 15) {
						if(util::random(100) < 5) {
							// 銀行化
							$land[$x][$y] = $init->landBank;
							$landValue[$x][$y] = 1;
							// メッセージ
							$this->log->Bank($id, $name, $lName, "($x,$y)");
							break;
						}
					} elseif(($lv == 40) || ($lv == 41) || ($lv == 42) || ($lv == 43)) {
						if(util::random(100) < 1) {
							// 卵孵化
							$kind = Util::random($init->monsterLevel1) + 1;
							$lv = $kind * 100
								+ $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
							// そのヘックスを怪獣に
							$land[$x][$y] = $init->landMonster;
							$landValue[$x][$y] = $lv;
							// 怪獣情報
							$monsSpec = Util::monsterSpec($lv);
							// メッセージ
							$this->log->EggBomb($id, $name, $mName, "($x,$y)", $lName);
							break;
						}
					}
					break;

				case $init->landSeaResort:
					// 海の家
					$nt = Turn::countAround($land, $x, $y, 19, array($init->landTown)); // 周囲2ヘックスの人口
					$ns = Turn::countAround($land, $x, $y, 19, array($init->landSeaSide)); // 周囲2ヘックスの砂浜収容人数
					// 収益の計算
					if ($nt > 0) {
						$value = $ns / $nt;
					}
					$value = (int)($lv * $value * $nt);
					if ($value > 0) {
						$island['money'] += $value;
						// 収入ログ
						$str = "{$value}{$init->unitMoney}";
						$this->log->oilMoney($id, $name, $this->landName($landKind, $lv), "($x,$y)", $str);
					}
					if($lv < 30) {
						// 海の家
						$n = 1;
					} elseif($lv < 100) {
						// 民宿
						$n = 2;
					} else {
						// リゾートホテル
						$n = 4;
					}
					$lv += (int)(Util::random($nt / $n) * (($nt < $ns) ? -1 : 1));
					if ($lv < 1) {
						$lv = 1;
					} elseif ($lv > 200) {
						$lv = 200;
					}
					$landValue[$x][$y] = $lv;
					break;

				case $init->landDefence:
					if($lv == 0) {
						// 防衛施設自爆
						$lName = $this->landName($landKind, $lv);
						$this->log->bombFire($id, $name, $lName, "($x, $y)");
						// 広域被害ルーチン
						$this->wideDamage($id, $name, $land, $landValue, $x, $y);
					}
					break;

				case $init->landHatuden:
					// 発電所
					$lName = $this->landName($landKind, $lv);
					if(Util::random(100000) < $landValue[$x][$y]) {
						// メルトダウン
						$land[$x][$y] = $init->landSea;
						$landValue[$x][$y] = 0;
						$this->log->CrushElector($id, $name, $lName, "($x, $y)");
					}
					break;

				case $init->landSoukoM:
				case $init->landSoukoF:
					// 倉庫
					$lName = $this->landName($landKind, $lv);

					// セキュリティと貯蓄を算出
					$sec = (int)($landValue[$x][$y] / 100);
					$tyo = $landValue[$x][$y] % 100;

					if(Util::random(100) < (10 - $sec)) {
						// 強盗
						$tyo = (int)($tyo / 100 * Util::random(100));
						$sec--;
						if($sec < 0) {
							$sec = 0;
						}
						$landValue[$x][$y] = $sec * 100 + $tyo;
						$this->log->SoukoLupin($id, $name, $lName, "($x, $y)");
					}
					break;

				case $init->landOil:
					// 海底油田
					$lName = $this->landName($landKind, $lv);
					$value = $init->oilMoney;
					$island['money'] += $value;
					$island['oilincome'] += $value;

					// 枯渇判定
					if(Util::random(1000) < $init->oilRatio) {
						// 枯渇
						$land[$x][$y] = $init->landSea;
						$landValue[$x][$y] = 0;
						$this->log->oilEnd($id, $name, $lName, "($x, $y)");
					}
					break;

				case $init->landBank:
					// 銀行
					$island['bank']++;
					break;

				case $init->landSoccer:
					// スタジアム
					$lName = $this->landName($landKind, $lv);
					$value = $island['team'];

					if($value > 200) {
						$value = 200;
					}
					$island['money'] += $value;
					$str = "{$value}{$init->unitMoney}";
					// 収入ログ
					if ($value > 0) {
						$this->log->oilMoney($id, $name, $lName, "($x, $y)", $str);
					}
					break;

				case $init->landPark:
					// 遊園地
					$lName = $this->landName($landKind, $lv);
					//$value = floor($island['pop'] / 50); // 人口５千人ごとに１億円の収入
					//収益は人口増加とともに横ばい傾向
					//人口の平方根の1～2倍 ex 1万=10～20億円 100万=100～200億円
					$value = floor(sqrt($island['pop'])*((Util::random(100)/100)+1));
					$island['money'] += $value;
					$str = "{$value}{$init->unitMoney}";

					//収入ログ
					if ($value > 0) {
						$this->log->ParkMoney($id, $name, $lName, "($x,$y)", $str);
					}
					//イベント判定
					if(Util::random(100) < 30) {
						// 毎ターン 30% の確率でイベントが発生する
						//遊園地のイベント
						$value2=$value;

						//食料消費
						$value = floor($island['pop'] * $init->eatenFood / 2); // 規定食料消費の半分消費
						$island['food'] -= $value;
						$str = "{$value}{$init->unitFood}";

						if ($value > 0) {
							$this->log->ParkEvent($id, $name, $lName, "($x,$y)", $str);
						}
						//イベントの収支
						$value = floor((Util::random(200) - 100)/100 * $value2);//マイナス100%～プラス100%
						$island['money'] += $value;
						if ($value > 0) {
							$str = "{$value}{$init->unitMoney}";
							$this->log->ParkEventLuck($id, $name, $lName, "($x,$y)", $str);
						}
						if ($value < 0) {
							$value = -$value;
							$str = "{$value}{$init->unitMoney}";
							$this->log->ParkEventLoss($id, $name, $lName, "($x,$y)", $str);
						}
					}
					// 老築化判定
					if(Util::random(100) < 5) {
						// 施設が老築化したため閉園
						$land[$x][$y] = $init->landPlains;
						$landValue[$x][$y] = 0;
						$this->log->ParkEnd($id, $name, $lName, "($x,$y)");
					}
					break;

				case $init->landPort:
					// 港
					$lName = $this->landName($landKind, $lv);
					$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea));
					if($seaCount == 0 || $seaCount == 6){
						// 周囲に最低1Hexの海も無い場合、閉鎖
						// 周囲に最低1Hexの陸地が無い場合、閉鎖
						$land[$x][$y] = $init->landSea;
						$landValue[$x][$y] = 1;
						$this->log->ClosedPort($id, $name, $lName, "($x,$y)");
					}
					break;

				case $init->landTrain:
					// 電車
					if($TrainMove[$x][$y] == 1) {
						// すでに動いた後
						break;
					}
					// 動く方向を決定
					for($t = 0; $t < 3; $t++) {
						$d = Util::random(6) + 1;
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// 行による位置調整
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// 範囲外判定
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// 線路しか動かない
						if($land[$sx][$sy] == $init->landRail) {
							break;
						}
					}
					if($t == 3) {
						// 動かなかった
						break;
					}
					$l = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$lName = $this->landName($l, $lv);
					$point = "({$sx}, {$sy})";

					// 移動
					$land[$sx][$sy] = $land[$x][$y];

					// もと居た位置を線路に
					$land[$x][$y] = $init->landRail;

					// 移動ずみフラグ、セット
					$TrainMove[$sx][$sy] = 1;
					break;

				case $init->landZorasu:
					// 海怪獣
					if($ZorasuMove[$x][$y] == 1) {
						// すでに動いた後
						break;
					}
					// 動く方向を決定
					for($j = 0; $j < 3; $j++) {
						$d = Util::random(6) + 1;
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// 行による位置調整
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// 範囲外判定
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// 海、船舶、海基、海防、海底都市、海上都市、海底消防署、海底農場、油田
						if(($land[$sx][$sy] == $init->landSea) ||
							($land[$sx][$sy] == $init->landShip) ||
							($land[$sx][$sy] == $init->landSbase) ||
							($land[$sx][$sy] == $init->landSdefence) ||
							($land[$sx][$sy] == $init->landSeaCity) ||
							($land[$sx][$sy] == $init->landFroCity) ||
							($land[$sx][$sy] == $init->landSsyoubou) ||
							($land[$sx][$sy] == $init->landSfarm) ||
							($land[$sx][$sy] == $init->landOil)) {
							break;
						}
					}
					if($j == 3) {
						// 動かなかった
						break;
					}
					// 動いた先の地形によりメッセージ
					$l = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$lName = $this->landName($l, $lv);
					$point = "({$sx}, {$sy})";
					if($land[$sx][$sy] != $init->landSea) {
						$this->log->ZorasuMove($id, $name, $lName, $point);
					}
					// 移動
					$land[$sx][$sy] = $land[$x][$y];
					$landValue[$sx][$sy] = $landValue[$x][$y];

					// もと居た位置を海に
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;

					// 移動ずみフラグ、セット
					$ZorasuMove[$sx][$sy] = 1;
					break;

				case $init->landMonster:
					// 怪獣
					if ( isset($monsterMove[$x][$y])) {
						if($monsterMove[$x][$y] == 2) {
							// すでに動いた後
							break;
						}
					}
					// 各要素の取り出し
					$monsSpec = Util::monsterSpec($landValue[$x][$y]);
					$special  = $init->monsterSpecial[$monsSpec['kind']];
					$mName = $monsSpec['name'];

					// 怪獣の体力回復
					if(($monsSpec['hp'] < $init->monsterBHP[$monsSpec['kind']]) && (Util::random(100) < 20)) {
						$landValue[$x][$y]++;
					}

					if((Turn::countAroundValue($island, $x, $y, $init->landProcity, 200, 7)) && ($monsSpec['kind'] != 26)) {
						// 周囲1Hexに別の怪獣がいる場合、その怪獣を攻撃する
						// 対象の怪獣が倒れて荒地になる
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						$this->log->BariaAttack($id, $name, $lName, "($x,$y)", $mName, $tPoint);

						// 収入
						$value = $init->monsterValue[$monsSpec['kind']];
						if($value > 0) {
							$island['money'] += $value;
							$this->log->msMonMoney($id, $target, $mName, $value);
						}
						break;
					}
					// 硬化中?
					if((($special & 0x4) && (($hako->islandTurn % 2) == 1)) ||
						(($special & 0x10) && (($hako->islandTurn % 2) == 0))) {
						// 硬化中
						break;
					}
					if($special & 0x20) {
						// 仲間を呼ぶ怪獣
						if ((Util::random(100) < 5) && ($pop >= $init->disMonsBorder1)) {
							// 怪獣出現
							$pop = $island['pop'];
							$this->log->monsCall($id, $name, $mName, "({$x}, {$y})");
							if ($pop >= $init->disMonsBorder5) {
								// level5まで
								$kind = Util::random($init->monsterLevel5) + 1;
							} elseif($pop >= $init->disMonsBorder4) {
								// level4のみ
								$kind = Util::random($init->monsterLevel4) + 1;
							} elseif($pop >= $init->disMonsBorder3) {
								// level3のみ
								$kind = Util::random($init->monsterLevel3) + 1;
							} elseif($pop >= $init->disMonsBorder2) {
								// level2のみ
								$kind = Util::random($init->monsterLevel2) + 1;
							} else {
								// level1のみ
								$kind = Util::random($init->monsterLevel1) + 1;
							}
							// lvの値を決める
							$lv = $kind * 100
								+ $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
							// どこに現れるか決める
							for($i = 0; $i < $init->pointNumber; $i++) {
								$bx = $this->rpx[$i];
								$by = $this->rpy[$i];
								if($land[$bx][$by] == $init->landTown) {
									// 地形名
									$lName = $this->landName($init->landTown, $landValue[$bx][$by]);
									// そのヘックスを怪獣に
									$land[$bx][$by] = $init->landMonster;
									$landValue[$bx][$by] = $lv;
									// 怪獣情報
									$monsSpec = Util::monsterSpec($lv);
									// メッセージ
									$this->log->monsCome($id, $name, $mName, "({$bx}, {$by})", $lName);
									break;
								}
							}
						}
					}
					// ワープする怪獣
					if ($special & 0x40) {
						$r = mt_rand(0,100);
						if ($r < 20) { // 20%
							// ワープする
							$tg;
							$tIsland = $island;
							$r = mt_rand(0,100);
							if ($r < 50) { // 50%
								// ワープする島を決める
								$tg = Util::random($hako->islandNumber);
								$tIsland = $hako->islands[$tg];
								if((($hako->islandTurn - $tIsland['starturn']) < $init->noAssist) && ($tIsland['isBF'] != 1)) {
									// 初心者期間中の島にはワープしない（自島へワープ）
									$tIsland = $island;
								}
							}
							$tId   = $tIsland['id'];
							$tName = $tIsland['name'];

							// ワープ地点を決める
							$tLand      = $tIsland['land'];
							$tLandValue = $tIsland['landValue'];
							for ($w = 0; $w < $init->pointNumber; $w++) {
								$bx = $this->rpx[$w];
								$by = $this->rpy[$w];
								// 海、船舶、海基、海防、海底都市、海上都市、海底消防署、養殖場、油田、港、怪獣、山、ぞらす、記念碑以外
								if(($tLand[$bx][$by] != $init->landSea) &&
									($tLand[$bx][$by] != $init->landShip) &&
									($tLand[$bx][$by] != $init->landSbase) &&
									($tLand[$bx][$by] != $init->landSdefence) &&
									($tLand[$bx][$by] != $init->landSeaCity) &&
									($tLand[$bx][$by] != $init->landFroCity) &&
									($tLand[$bx][$by] != $init->landSsyoubou) &&
									($tLand[$bx][$by] != $init->landSfarm) &&
									($tLand[$bx][$by] != $init->landNursery) &&
									($tLand[$bx][$by] != $init->landOil) &&
									($tLand[$bx][$by] != $init->landPort) &&
									($tLand[$bx][$by] != $init->landMountain) &&
									($tLand[$bx][$by] != $init->landMonument) &&
									($tLand[$bx][$by] != $init->landZorasu) &&
									($tLand[$bx][$by] != $init->landSleeper) &&
									($tLand[$bx][$by] != $init->landMonster)) {
									break;
								}
							}
							// ワープ！
							$this->log->monsWarp($id, $tId, $name, $mName, "({$x}, {$y})", $tName);
							$this->log->monsCome($tId, $tName, $mName, "($bx, $by)", $this->landName($tLand[$bx][$by], $tLandValue[$bx][$by]));

							if($id == $tId) {
								$land[$bx][$by]       = $init->landMonster;
								$landValue[$bx][$by]  = $lv;
							} else {
								$tLand[$bx][$by]      = $init->landMonster;
								$tLandValue[$bx][$by] = $lv;
							}
							$monsterMove[$bx][$bx] = 2;
							$land[$x][$y]      = $init->landWaste;
							$landValue[$x][$y] = 0;

							if($id != $tId) {
								// ターゲットが異なる場合は、値を戻す
								$tIsland['land']      = $tLand;
								$tIsland['landValue'] = $tLandValue;
								$hako->islands[$tg]   = $tIsland;
							}
							break;
						} else {
							// ワープしない
						}
					}
					if ($special & 0x400) {
						// 瀕死になると大爆発
						if ($monsSpec['hp'] <= 1) { // 残り体力１なら
							$point = "({$x}, {$y})";
							// 瀕死になったので爆発する
							$this->log->MonsExplosion($id, $name, $point, $mName);
							// 広域被害ルーチン
							$this->wideDamage($id, $name, $land, $landValue, $x, $y);
							break;
						}
					}
					if ($special & 0x1000) {
						// 出現中はお金を増やしてくれる
						$point = "({$x}, {$y})";
						$money = (Util::random(100) + 1); // 1億円～100億円
						$island['money'] += $money;
						$str = "{$money}{$init->unitMoney}";
						$this->log->MonsMoney($id, $name, $mName, $point, "$str");
					}
					if ($special & 0x2000) {
						// 出現中は食料を増やしてくれる
						$point = "({$x}, {$y})";
						$food  = (Util::random(10) + 1); // 1000トン～10000トン
						$island['food'] += $food;
						$str = "{$food}{$init->unitFood}";

						$this->log->MonsFood($id, $name, $mName, $point, "$str");
					}
					if ($special & 0x4000) {
						// 出現中はお金を減らしてしまう
						$point = "({$x}, {$y})";
						$money = (Util::random(100) + 1); // 1億円～100億円
						$island['money'] -= $money;
						$str = "{$money}{$init->unitMoney}";
						$this->log->MonsMoney2($id, $name, $mName, $point, "$str");
					}
					if ($special & 0x10000) {
						// 出現中は食料を腐らせてしまう
						$point = "({$x}, {$y})";
						$food  = (Util::random(10) + 1); // 1000トン～10000トン
						$island['food'] -= $food;
						$str = "{$food}{$init->unitFood}";
						$this->log->MonsFood2($id, $name, $mName, $point, "$str");
					}
					// 動く方向を決定
					for($j = 0; $j < 3; $j++) {
						$d = Util::random(6) + 1;
						if($special & 0x200){
							// 飛行移動能力
							$d = Util::random(12) + 7;
						}
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// 行による位置調整
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// 範囲外判定
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// 海、船舶、海基、海防、海底都市、海上都市、海底消防署、養殖場、油田、港、怪獣、山、ぞらす、記念碑以外
						if(($land[$sx][$sy] != $init->landSea) &&
							($land[$sx][$sy] != $init->landShip) &&
							($land[$sx][$sy] != $init->landSbase) &&
							($land[$sx][$sy] != $init->landSdefence) &&
							($land[$sx][$sy] != $init->landSeaCity) &&
							($land[$sx][$sy] != $init->landFroCity) &&
							($land[$sx][$sy] != $init->landSsyoubou) &&
							($land[$sx][$sy] != $init->landSfarm) &&
							($land[$sx][$sy] != $init->landNursery) &&
							($land[$sx][$sy] != $init->landOil) &&
							($land[$sx][$sy] != $init->landPort) &&
							($land[$sx][$sy] != $init->landMountain) &&
							($land[$sx][$sy] != $init->landMonument) &&
							($land[$sx][$sy] != $init->landZorasu) &&
							($land[$sx][$sy] != $init->landSleeper) &&
							($land[$sx][$sy] != $init->landMonster)) {
							break;
						}
					}
					if($j == 3) {
						// 動かなかった
						break;
					}
					// 動いた先の地形によりメッセージ
					$l = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$lName = $this->landName($l, $lv);
					$point = "({$sx}, {$sy})";

					// 移動
					$land[$sx][$sy] = $land[$x][$y];
					$landValue[$sx][$sy] = $landValue[$x][$y];

					if (($special & 0x20000) && (Util::random(100) < 30)) { // 分裂確率30%
						// 分裂する怪獣
						// もと居た位置を怪獣に
						$land[$bx][$by] = $init->landMonster;
						$landValue[$bx][$by] = $lv;
						// 怪獣情報
						$monsSpec = Util::monsterSpec($lv);
						// メッセージ
						$this->log->monsBunretu($id, $name, $lName, $point, $mName);
					} else {
						// もと居た位置を荒地に
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
					}
					// 移動済みフラグ
					if($init->monsterSpecial[$monsSpec['kind']] & 0x2) {
						// 移動済みフラグは立てない
					} elseif($init->monsterSpecial[$monsSpec['kind']] & 0x1) {
						// 速い怪獣
						if ( isset($monsterMove[$sx][$sy]) ) {
							$monsterMove[$sx][$sy] = $monsterMove[$x][$y] + 1;
						} else {
							$monsterMove[$sx][$sy] = 1;
						}
					} else {
						// 普通の怪獣
						$monsterMove[$sx][$sy] = 2;
					}
					if(($l == $init->landDefence) && ($init->dBaseAuto == 1)) {
						// 防衛施設を踏んだ
						$this->log->monsMoveDefence($id, $name, $lName, $point, $mName);

						// 広域被害ルーチン
						$this->wideDamage($id, $name, $land, $landValue, $sx, $sy);
					} else {
						// 行き先が荒地になる
						if($island['isBF'] != 1)
						$this->log->monsMove($id, $name, $lName, $point, $mName);
					}
					break;

				case $init->landSleeper:
					// 捕獲怪獣
					// 各要素の取り出し
					$monsSpec = Util::monsterSpec($landValue[$x][$y]);
					$special  = $init->monsterSpecial[$monsSpec['kind']];
					$mName    = $monsSpec['name'];
					if(Util::random(1000) < $monsSpec['hp'] * 10) {
						// (怪獣の体力 * 10)% の確率で捕獲解除
						$point = "({$x}, {$y})";
						$land[$x][$y] = $init->landMonster; // 捕獲解除
						$this->log->MonsWakeup($id, $name, $lName, $point, $mName);
					}
					break;

				case $init->landShip:
					// 船舶
					if($shipMove[$x][$y] != 1){
						//船がまだ動いていない時
						$ship = Util::navyUnpack($landValue[$x][$y]);
						$lName = $init->shipName[$ship[1]];
						$tLname .= "（{$this->islands[$ship[0]]['name']}島所属）";

						$tn = $hako->idToNumber[$ship[0]];
						$tIsland = &$hako->islands[$tn];
						$tName = $hako->idToName[$ship[0]];

						if($init->shipCost[$ship[1]] > $tIsland['money'] && $ship[0] != 0) {
							// 維持費を払えなくなり海の藻屑となる
							$this->log->ShipRelease($id, $ship[0], $name, $tName, "($x,$y)", $init->shipName[$ship[1]]);
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
							break;
						}

						if($ship[1] == 2) {
							// 海底探索船
							$cntTreasure = Turn::countAroundValue($island, $x, $y, $init->landSea, 100, 7);
							if($cntTreasure) {
								// 周囲1ヘックス以内に財宝あり
								for($s1 = 0; $s1 < 7; $s1++) {
									$sx = $x + $init->ax[$s1];
									$sy = $y + $init->ay[$s1];
									// 行による位置調整
									if((($sy % 2) == 0) && (($y % 2) == 1)) {
										$sx--;
									}
									if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
										// 範囲外の場合何もしない
										continue;
									} else {
										// 範囲内の場合
										if($land[$sx][$sy] == $init->landSea && $landValue[$sx][$sy] >= 100) {
											// 財宝発見
											if($ship[0] == $island['id']) {
												// 自島所属であればすぐに財宝回収
												$island['money'] += $landValue[$sx][$sy];
											} else {
												// 他島所属であれば積載して帰還するまで回収しない
												$ship[3] = round($landValue[$sx][$sy] / 1000);
												$ship[4] = round(($landValue[$sx][$sy] - $ship[3] * 1000) /100);
												$landValue[$x][$y] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
											}
											$tName = $hako->idToName[$ship[0]];
											$this->log->FindTreasure($id, $ship[0], $name, $tName, "($x,$y)", $init->shipName[$ship[1]], $landValue[$sx][$sy]);

											// 財宝があった地形は海になる
											$land[$sx][$sy] = $init->landSea;
											$landValue[$sx][$sy] = 0;
											break;
										}
									}
								}
							}
						} elseif($ship[1] == 3) {
							// 戦艦
							if($island['monster'] > 0 && $ship[4] != intval($hako->islandTurn) % 10) {
								// 怪獣が出現しており、現在のターンで未攻撃の場合
								for($s2 = 0; $s2 < $init->pointNumber; $s2++) {
									$sx = $this->rpx[$s2];
									$sy = $this->rpy[$s2];
									if($land[$sx][$sy] == $init->landMonster || $land[$sx][$sy] == $init->landSleeper) {
										// 対象となる怪獣の各要素取り出し
										$monsSpec = Util::monsterSpec($landValue[$sx][$sy]);
										$tLv = $landValue[$sx][$sy];
										$tspecial  = $init->monsterSpecial[$monsSpec['tkind']];
										$tmonsName = $monsSpec['name'];
										// 硬化中?
										if((($special & 0x4) && (($hako->islandTurn % 2) == 1)) ||
											(($special & 0x10) && (($hako->islandTurn % 2) == 0))) {
											// 硬化中なら効果なし
											break;
										}
										if($monsSpec['hp'] > 1){
											// 対象の体力を減らす
											$landValue[$sx][$sy]--;
										} else {
											// 対象の怪獣が倒れて荒地になる
											$land[$sx][$sy] = $init->landWaste;
											$landValue[$sx][$sy] = 0;

											// 収入
											$value = $init->monsterValue[$monsSpec['kind']];
											if($value > 0) {
												$island['money'] += $value;
												$this->log->msMonMoney($id, $target, $tmonsName, $value);
											}
										}
										$tName = $hako->idToName[$ship[0]];
										$this->log->SenkanMissile($id, $ship[0], $name, $tName, $lName, "($x,$y)", "($sx,$sy)", $tmonsName);
										break;
									}
								}
								// 1ターンに1度しか攻撃できない
								$ship[4] = intval($hako->islandTurn) % 10;
							} else {
							}
							// 海賊船が出現していた場合攻撃する
							$cntViking = Turn::countAround($land, $x, $y, 19, array($init->landShip));
							if($cntViking && $ship[4] != intval($hako->islandTurn) % 10) {
								//周囲2ヘックス以内に船舶あり
								for($s3 = 0; $s3 < 19; $s3++) {
									$sx = $x + $init->ax[$s3];
									$sy = $y + $init->ay[$s3];
									// 行による位置調整
									if((($sy % 2) == 0) && (($y % 2) == 1)) {
										$sx--;
									}
									if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
										// 範囲外の場合何もしない
										continue;
									} else {
										// 範囲内の場合
										if($land[$sx][$sy] == $init->landShip) {
											$tShip = Util::navyUnpack($landValue[$sx][$sy]);
											$tName = $hako->idToName[$ship[0]];
											$tshipName = $init->shipName[$tShip[1]];
											if($tShip[1] >= 10) {
												// 海賊船だった場合攻撃する
												$tShip[2] -= 2;
												if($tShip[2] <= 0) {
													// 海賊船を沈没させた
													$land[$sx][$sy] = $init->landSea;
													$this->log->SenkanAttack($id, $ship[0], $name, $tName, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $tshipName);
													$this->log->BattleSinking($id, $tShip[0], $name, $tshipName, "($sx,$sy)");
													// 30%の確率で財宝になる
													$treasure = $tShip[3] * 1000 + $tShip[4] * 100;
													if(Util::random(100) < 30 && $treasure > 0) {
														$landValue[$sx][$sy] = $treasure;
														$this->log->VikingTreasure($id, $name, "($sx,$sy)");
													} else {
														$landValue[$sx][$sy] = 0;
													}
												} else {
													// 海賊船にダメージ与えた
													$landValue[$sx][$sy] = Util::navyPack($tShip[0], $tShip[1], $tShip[2], $tShip[3], $tShip[4]);
													$this->log->SenkanAttack($id, $ship[0], $name, $tName, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $tshipName);
												}
												break;
											}
										}
									}
								}
								// 1ターンに1度しか攻撃できない
								$ship[4] = intval($hako->islandTurn) % 10;
							}
						} elseif($ship[1] >= 10) {
							// 海賊船
							if(Util::random(1000) < $init->disVikingRob) {
								// 強奪
								$vMoney = round(Util::random($island['money'])/10);
								$vFood  = round(Util::random($island['food'])/10);
								$island['money'] -= $vMoney;
								$island['food'] -= $vFood;
								$this->log->RobViking($island['id'], $island['name'], "($x,$y)", $init->shipName[$ship[1]], $vMoney, $vFood);

								// 所持金
								$treasure = $ship[3] * 1000 + $ship[4] * 100;
								$treasure += $vMoney;
								$ship[3] = $treasure / 1000;
								$ship[4] = ($treasure - $ship[1] * 1000) / 100;
								if($ship[3] > 32) $ship[3] = 32;
								// 海賊船ステータス更新
								$landValue[$x][$y] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
							}
							// 攻撃
							$cntShip = Turn::countAround($land, $x, $y, 19, array($init->landPort, $init->landShip, $init->landFroCity));
							if($cntShip) {
								//周囲2ヘックス以内に港または船舶または海上都市あり
								if(Util::random(1000) < $init->disVikingAttack) {
									// 海賊船の襲撃
									for($s4 = 0; $s4 < 19; $s4++) {
										$sx = $x + $init->ax[$s4];
										$sy = $y + $init->ay[$s4];
										// 行による位置調整
										if((($sy % 2) == 0) && (($y % 2) == 1)) {
											$sx--;
										}
										if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
											// 範囲外の場合何もしない
											continue;
										} else {
											// 範囲内の場合
											if($land[$sx][$sy] == $init->landPort) {
												// 港の場合浅瀬になる
												$land[$sx][$sy] = $init->landSea;
												$landValue[$sx][$sy] = 1;
												$this->log->BattleSinking($id, 0, $name, $this->landName($init->landPort, 1), "($sx,$sy)");
												$this->log->VikingAttack($id, $id, $name, $name, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $this->landName($init->landPort, 1));
												break;
											} elseif($land[$sx][$sy] == $init->landShip) {
												// 船舶の場合
												$tShip = Util::navyUnpack($landValue[$sx][$sy]);
												$tName = $hako->idToName[$tShip[0]];
												$tshipName = $init->shipName[$tShip[1]];
												if($tShip[1] < 10) {
													// 海賊船の攻撃
													$tShip[2] -= ($init->disVikingMinAtc + Util::random($init->disVikingMaxAtc - $init->disVikingMinAtc));
													if($tShip[2] <= 0) {
														// 船舶沈没
														$land[$sx][$sy] = $init->landSea;
														$landValue[$sx][$sy] = 0;
														$this->log->ShipSinking($id, $tShip[0], $name, $tName, $tshipName, "($sx,$sy)");
														break;
													} else {
														// 船舶ダメージ
														$landValue[$sx][$sy] = Util::navyPack($tShip[0], $tShip[1], $tShip[2], $tShip[3], $tShip[4]);
														$this->log->VikingAttack($id, $tShip[0], $name, $tName, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $tshipName);
														break;
													}
												}
											} elseif($land[$sx][$sy] == $init->landFroCity) {
												// 海上都市の場合海になる
												$land[$sx][$sy] = $init->landSea;
												$landValue[$sx][$sy] = 0;
												$this->log->BattleSinking($id, 0, $name, $this->landName($init->landFroCity, 0), "($sx,$sy)");
												$this->log->VikingAttack($id, $id, $name, $name, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $this->landName($init->landFroCity, 0));
												break;
											}
										}
									}
								}
							}
							if(Util::random(1000) < $init->disVikingAway) {
								// 海賊船 去る
								$land[$x][$y] = $init->landSea;
								$landValue[$x][$y] = 0;
								$this->log->VikingAway($id, $name, "($x,$y)");
								break;
							}
						}

						if ($landValue[$x][$y] != 0){
							// 船がまだ存在していたら
							// 動く方向を決定
							for($s5 = 0; $s5 < 3; $s5++) {
								$d = Util::random(6) + 1;
								$sx = $x + $init->ax[$d];
								$sy = $y + $init->ay[$d];
								// 行による位置調整
								if((($sy % 2) == 0) && (($y % 2) == 1)) {
									$sx--;
								}
								// 範囲外判定
								if(($sx < 0) || ($sx >= $init->islandSize) ||
									($sy < 0) || ($sy >= $init->islandSize)) {
									continue;
								}
								// 海であれば、動く方向を決定
								if(($land[$sx][$sy] == $init->landSea) && ($landValue[$sx][$sy] < 1)){
									break;
								}
							}
							if($s5 == 3) {
								// 動かなかった
							} else {
								// 移動
								$land[$sx][$sy] = $land[$x][$y];
								$landValue[$sx][$sy] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
								if ($ship[1] == 2) {
									if((Util::random(100) < 7) && ($island['tenki'] == 1) &&
										($island['item'][18] == 1) && ($island['item'][19] != 1)) {
										// レッドダイヤ発見
										$island['item'][19] = 1;
										$this->log->RedFound($id, $name, '赤い宝石');
									}
									// 油田見っけ
									if (Util::random(100) < 3) {
										$lName = $init->shipName[$ship[1]];
										$island['oil']++;
										$land[$x][$y] = $init->landOil;
										$landValue[$x][$y] = 0;
										$this->log->tansakuoil($id, $name, $lName, $point);
									} else {
										// もと居た位置を海に
										$land[$x][$y] = $init->landSea;
										$landValue[$x][$y] = 0;
									}
								} else {
									// もと居た位置を海に
									$land[$x][$y] = $init->landSea;
									$landValue[$x][$y] = 0;
								}
								// 移動済みフラグ
								if(Util::random(2)){
									$shipMove[$sx][$sy] = 1;
								}
							}
						}
					}
					break;
			}
			// すでに$init->landTownがcase文で使われているのでswitchを別に用意
			switch($landKind) {
				case $init->landTown:
				case $init->landHaribote:
				case $init->landFactory:
				case $init->landHatuden:
				case $init->landPark:
				case $init->landSeaResort:
				case $init->landSyoubou:
				case $init->landSsyoubou:
				case $init->landSeaCity:
				case $init->landFroCity:
				case $init->landNewtown:
				case $init->landBigtown:
					// 火災判定
					if (Turn::countAround($land, $x, $y, 19, array($init->landSyoubou, $init->landSsyoubou)) > 0) {
						break;
					}
					if ((($landKind == $init->landSeaResort) && ($lv <= 30)) ||
						($landKind == $init->landFactory && ($lv >= 100)) ||
						($landKind == $init->landHatuden && ($lv >= 100)) ||
						($landKind == $init->landTown && ($lv <= 30))) {
						break;
					}
					if(Util::random(1000) < $init->disFire - (int)($island['eisei'][0] / 20)) {
						// 周囲の森と記念碑を数える
						if(Turn::countAround($land, $x, $y, 7, array($init->landForest, $init->landProcity, $init->landFusya, $init->landMonument)) == 0) {
							// 無かった場合、火災で壊滅
							$l = $land[$x][$y];
							$lv = $landValue[$x][$y];
							$point = "({$x}, {$y})";
							$lName = $this->landName($l, $lv);
							if(($landKind == $init->landNewtown) || ($landKind == $init->landBigtown)) {
								// ニュータウン、現代都市の場合
								$landValue[$x][$y] -= Util::random(100) + 50;
								$this->log->firenot($id, $name, $lName, $point);
								if($landValue[$x][$y] <= 0) {
									$land[$x][$y] = $init->landWaste;
									$landValue[$x][$y] = 0;
									$this->log->fire($id, $name, $lName, $point);
								}
							} elseif(($landKind == $init->landSeaCity) || ($landKind == $init->landFroCity)) {
								$land[$x][$y] = $init->landSea;
								$landValue[$x][$y] = 0;
								$this->log->fire($id, $name, $lName, $point);
							} else {
								$land[$x][$y] = $init->landWaste;
								$landValue[$x][$y] = 0;
								$this->log->fire($id, $name, $lName, $point);
							}
						}
					}
					break;
			}
		}
		// 変更された可能性のある変数を書き戻す
		$island['land'] = $land;
		$island['landValue'] = $landValue;
	}

	//---------------------------------------------------
	// 島全体
	//---------------------------------------------------
	function doIslandProcess($hako, &$island) {
		global $init;

		// 導出値
		$name = $island['name'];
		$id   = $island['id'];
		$land = $island['land'];
		$landValue = $island['landValue'];
		$presentItem = isset($island['present']['item']) ? $island['present']['item'] : 0;

		// 収入ログ
		if( isset($island['oilincome']) ) {
		  if($island['oilincome'] > 0) {
				$this->log->oilMoney($id, $name, "海底油田", "", "総額{$island['oilincome']}{$init->unitMoney}");
			}
		}
		// 収入ログ
		if( isset($island['bank']) ) {
			if($island['bank'] > 0) {
				$value = (int)($island['money'] * 0.005);
				$island['money'] += $value;
				$this->log->oilMoney($id, $name, "銀行", "", "総額{$value}{$init->unitMoney}");
			}
		}
		// 天気判定
		if( isset($island['tenki']) ) {
			if($island['tenki'] > 0) {
				$rnd = Util::random(100);
				if($rnd < 5) {
					$island['tenki'] = 5;
				} elseif($rnd < 10) {
					$island['tenki'] = 4;
				} elseif($rnd < 15) {
					$island['tenki'] = 3;
				} elseif($rnd < 20) {
					$island['tenki'] = 2;
				} else {
					$island['tenki'] = 1;
				}
			} else {
				$island['tenki'] = 1;
			}
		}

		// 日照り判定
		if( isset($island['tenki']) ) {
			if((Util::random(1000) < $init->disTenki) && ($island['tenki'] == 1)) {
				// 日照り発生
				$this->log->Hideri($id, $name);
				for($i = 0; $i < $init->pointNumber; $i++) {
					$x = $this->rpx[$i];
					$y = $this->rpy[$i];
					$landKind = $land[$x][$y];
					$lv = $landValue[$x][$y];
					if(($landKind == $init->landTown) && ($landValue[$x][$y] > 100)) {
						// 人口が減る
						$people = (Util::random(2) + 1);
						$landValue[$x][$y] -= $people;
					}
				}
			}
		}

		// にわか雨判定
		if( isset($island['tenki']) ) {
			if((Util::random(1000) < $init->disTenki) && ($island['tenki'] == 3)) {
				// にわか雨発生
				$this->log->Niwakaame($id, $name);
				for($i = 0; $i < $init->pointNumber; $i++) {
					$x = $this->rpx[$i];
					$y = $this->rpy[$i];
					$landKind = $land[$x][$y];
					$lv = $landValue[$x][$y];
					if($landKind == $init->landForest) {
						// 木が増える
						$tree = (Util::random(5) + 1);
						$landValue[$x][$y] += $tree;
						if($landValue[$x][$y] > 200) {
							$landValue[$x][$y] = 200;
						}
					}
				}
			}
		}

		// 宝くじ判定
		if(($hako->islandTurn % $init->lottery) == 0) {
			if((Util::random(500) < $island['lot']) && ($island['lot'] > 0)) {
				// 何等賞に当選するか？
				$syo   = Util::random(2) + 1;
				$value = $init->lotmoney / $syo;
				$island['money'] += $value;
				$str = "{$value}{$init->unitMoney}";
				// 収入ログ
				$this->log->LotteryMoney($id, $name, $str, $syo);
			}
			// 宝くじの枚数リセット
			$island['lot'] = 0;
		}

		// 暴動判定
		$island['pop'] = ($island['pop'] <= 0) ? 1 : $island['pop'];
		$unemployed = ($island['pop'] - ($island['farm'] + $island['factory'] + $island['commerce'] + $island['mountain'] + $island['hatuden']) * 10) / $island['pop'] * 100;
		if (($island['isBF'] != 1) && (Util::random(1000) < $unemployed) && ($unemployed > $init->disPoo) && ($island['pop'] >= $init->disPooPop)) {
			// 暴動発生
			$this->log->pooriot($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landTown) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landNewtown) ||
					($landKind == $init->landBigtown) ||
					($landKind == $init->landProcity) ||
					($landKind == $init->landFroCity)) {
					// 1/4で人口が減る
					if(Util::random(4) == 0) {
						$landValue[$x][$y] -= Util::random($unemployed);
						if ($landValue[$x][$y]  > 0) {
							// 人口減
							$this->log->riotDamage1($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						} else {
							// 壊滅
							if ($landKind == $init->landSeaCity || $landKind == $init->landFroCity) {
								$land[$x][$y] = $init->landSea;
							} else {
								$land[$x][$y] = $init->landWaste;
							}
							$landValue[$x][$y] = 0;
							$this->log->riotDamage2($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						}
					}
				}
			}
		}

		// 地震判定
		$prepare2     = isset($island['prepare2']) ? (int)$island['prepare2'] : 0;
		if ((Util::random(1000) < (($prepare2 + 1) * $init->disEarthquake) - (int)($island['eisei'][1] / 15))
			|| ($presentItem == 1) )
		{
			// 地震発生
			$this->log->earthquake($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if((($landKind == $init->landTown) && ($lv >= 100)) ||
					(($landKind == $init->landProcity) && ($lv < 130)) ||
					(($landKind == $init->landSfarm) && ($lv < 20)) ||
					(($landKind == $init->landFactory) && ($lv < 100)) ||
					(($landKind == $init->landHatuden) && ($lv < 100)) ||
					($landKind == $init->landHaribote) ||
					($landKind == $init->landSeaResort) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity)) {
					// 1/4で壊滅
					if(Util::random(4) == 0) {
						$this->log->eQDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						if(($landKind == $init->landSeaCity) || ($landKind == $init->landFroCity) ||
							($landKind == $init->landSfarm)) {
							$land[$x][$y] = $init->landSea;
						} else {
							$land[$x][$y] = $init->landWaste;
						}
						$landValue[$x][$y] = 0;
					}
				}
				if((($landKind == $init->landBigtown) && ($lv >= 100)) ||
				(($landKind == $init->landNewtown) && ($lv >= 100))) {
					// 1/3で壊滅
					if(Util::random(3) == 0) {
						$this->log->eQDamagenot($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						$landValue[$x][$y] -= Util::random(100) + 50;
					}
					if($landValue[$x][$y] <= 0) {
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						$this->log->eQDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						continue;
					}
				}
			}
		}

		// 食料不足
		if($island['food'] <= 0) {
			// 不足メッセージ
			$this->log->starve($id, $name);
			$island['food'] = 0;
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landFarm) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landFactory) ||
					($landKind == $init->landCommerce) ||
					($landKind == $init->landHatuden) ||
					($landKind == $init->landBase) ||
					($landKind == $init->landDefence)) {
					// 1/4で壊滅
					if(Util::random(4) == 0) {
						$this->log->svDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						// でも養殖場なら浅瀬
						if($landKind == $init->landNursery) {
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 1;
						} elseif($landKind == $init->landSfarm) {
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						}
					}
				}
			}
		}

		// 座礁判定
		if(Util::random(1000) < $init->disRunAground1){
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landShip) && (Util::random(1000) < $init->disRunAground2)){
					$this->log->RunAground($id, $name, $this->landName($landKind, $lv), "($x,$y)");
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				}
			}
		}

		// 海賊船判定
		$ownShip = 0;
		for($i = 0; $i < 10; $i++) {
			$ownShip += $island['ship'][$i];
		}
		if(Util::random(1000) < $init->disViking * $ownShip){
			// どこに現れるか決める
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landSea) && ($lv == 0)) {
					// 海賊船登場
					$land[$x][$y] = $init->landShip;
					$landValue[$x][$y] = Util::navyPack(0, 10, $init->shipHP[10], 0, 0);
					$this->log->VikingCome($id, $name, "($x,$y)");
					break;
				}
			}
		}

		// 電車判定
		if((Util::random(1000) < $init->disTrain) && ($island['stat'] >= 2) &&
			($island['train'] < $island['stat']) && ($island['pop'] >= 2000)) {
			// どこに現れるか決める
			for($i = 0; $i < $init->pointNumber; $i++) {
				$bx = $this->rpx[$i];
				$by = $this->rpy[$i];
				$landKind = $land[$bx][$by];
				$lv = $landValue[$bx][$by];
				if($landKind == $init->landRail) {
					// 電車登場
					$land[$bx][$by] = $init->landTrain;
					break;
				}
			}
		}

		// ぞらす判定
		if($island['money'] >= 10000) {
			$smo = Util::random(800);
		} else {
			$smo = Util::random(1000);
		}
		if(($smo < $init->disZorasu) && ($island['taiji'] >= 50) && ($island['pop'] >= 2000)) {
			// どこに現れるか決める
			for($i = 0; $i < $init->pointNumber; $i++) {
				$bx = $this->rpx[$i];
				$by = $this->rpy[$i];
				$landKind = $land[$bx][$by];
				$lv = $landValue[$bx][$by];
				if(($landKind == $init->landSea) && ($lv == 0)) {
					// ぞらす登場
					$land[$bx][$by] = $init->landZorasu;
					$landValue[$bx][$by] = Util::random(200);

					$this->log->ZorasuCome($id, $name, "($x,$y)");
					break;
				}
			}
		}

		// 津波判定
		if ((Util::random(1000) < $init->disTsunami - (int)($island['eisei'][1] / 15))
			|| ($presentItem == 2)) {
			// 津波発生
			$this->log->tsunami($id, $name);

			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landTown) ||
					(($landKind == $init->landProcity) && ($lv < 110)) ||
					($landKind == $init->landNewtown) ||
					($landKind == $init->landBigtown) ||
					(($landKind == $init->landFarm) && ($lv < 25)) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landFactory) ||
					($landKind == $init->landHatuden) ||
					($landKind == $init->landBase) ||
					($landKind == $init->landDefence) ||
					($landKind == $init->landSeaSide)  ||
					($landKind == $init->landSeaResort)||
					($landKind == $init->landPort)     ||
					($landKind == $init->landShip)   ||
					($landKind == $init->landHaribote)) {
					// 1d12 <= (周囲の海 - 1) で崩壊
					if(Util::random(12) <
						(Turn::countAround($land, $x, $y, 7, array($init->landOil, $init->landSbase, $init->landSea)) - 1)) {
						$this->log->tsunamiDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						if (($landKind == $init->landSeaSide)||
							($landKind == $init->landNursery)||
							($landKind == $init->landPort)){
							//砂浜か養殖場か港なら浅瀬に
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 1;
						} elseif($landKind == $init->landShip){
							//船なら水没、海に
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						} else {
							$land[$x][$y] = $init->landWaste;
							$landValue[$x][$y] = 0;
						}
					}
				}
			}
		}

		// 怪獣判定
		if($island['isBF'] == 1) {
			$r = Util::random(500);
			$pop = $island['pop'];
		} else {
			$r = Util::random(10000);
			$pop = $island['pop'];
		}
		$isMons = (($presentItem == 3) && ($pop >= $init->disMonsBorder1));

		if ( !isset($island['monstersend']) ) {
			$island['monstersend'] = 0;
		}
		do {
			if((($r < ($init->disMonster * $island['area'])) &&
				($pop >= $init->disMonsBorder1)) || ($isMons) || ($island['monstersend'] > 0)) {
				// 怪獣出現
				// 種類を決める
				if($island['monstersend'] > 0) {
					// 人造
					$kind = 0;
					$island['monstersend']--;
				} elseif($pop >= $init->disMonsBorder5) {
					// level5まで
					$kind = Util::random($init->monsterLevel5) + 1;
				} elseif($pop >= $init->disMonsBorder4) {
					// level4まで
					$kind = Util::random($init->monsterLevel4) + 1;
				} elseif($pop >= $init->disMonsBorder3) {
					// level3まで
					$kind = Util::random($init->monsterLevel3) + 1;
				} elseif($pop >= $init->disMonsBorder2) {
					// level2まで
					$kind = Util::random($init->monsterLevel2) + 1;
				} else {
					// level1のみ
					$kind = Util::random($init->monsterLevel1) + 1;
				}
				// lvの値を決める
				$lv = $kind * 100
					+ $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);

				// どこに現れるか決める
				for($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if(($land[$bx][$by] == $init->landTown) ||
						($land[$bx][$by] == $init->landBigtown) ||
						($land[$bx][$by] == $init->landNewtown)) {
						// 地形名
						$lName = $this->landName($init->landTown, $landValue[$bx][$by]);
						// そのヘックスを怪獣に
						$land[$bx][$by] = $init->landMonster;
						$landValue[$bx][$by] = $lv;
						// 怪獣情報
						$monsSpec = Util::monsterSpec($lv);
						$mName = $monsSpec['name'];
						// メッセージ
						$this->log->monsCome($id, $name, $mName, "({$bx}, {$by})", $lName);
						break;
					}
				}
			}
		} while($island['monstersend'] > 0);

		// 地盤沈下判定
		if(($island['area'] > $init->disFallBorder) &&
			((Util::random(1000) < $init->disFalldown) && ($island['isBF'] != 1) ||
			($presentItem == 4))) {
			$this->log->falldown($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind != $init->landSea) &&
					($landKind != $init->landSbase) &&
					($landKind != $init->landSdefence) &&
					($landKind != $init->landSfarm) &&
					($landKind != $init->landOil) &&
					($landKind != $init->landMountain)) {
					// 周囲に海があれば、値を-1に
					if(Turn::countAround($land, $x, $y, 7, array($init->landSea, $init->landSbase))) {
						$land[$x][$y] = -1;
						$landValue[$x][$y] = 0;
						$this->log->falldownLand($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
					}
				}
			}

			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				if($landKind == -1) {
					// -1になっている所を浅瀬に
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 1;
				} elseif ($landKind == $init->landSea) {
					// 浅瀬は海に
					$landValue[$x][$y] = 0;
				}
			}
		}

		// 台風判定
		if ((Util::random(1000) < ($init->disTyphoon - (int)($island['eisei'][0] / 10)))
				&& (($island['tenki'] == 2) || ($island['tenki'] == 3))
				|| ($presentItem == 5))
		{
			// 台風発生
			$this->log->typhoon($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if((($landKind == $init->landFarm) && ($lv < 25)) ||
					(($landKind == $init->landSfarm) && ($lv < 20)) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landHaribote)) {
					// 1d12 <= (6 - 周囲の森) で崩壊
					if(Util::random(12) <
						(6 - Turn::countAround($land, $x, $y, 7, array($init->landForest, $init->landFusy, $init->landMonument)))) {
						$this->log->typhoonDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						if (($landKind == $init->landSeaSide)||($landKind == $init->landNursery)){
							//砂浜か養殖場ならは浅瀬
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 1;
						} elseif ($landKind == $init->landSfarm) {
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						} else {
							//その他は平地に
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
						}
					}
				}
			}
		}

		// 巨大隕石判定
		if (((Util::random(1000) < ($init->disHugeMeteo - (int)($island['eisei'][2] / 50))) && ($island['id'] != 1))
			|| ($presentItem == 6)) {
			// 落下
			if ( $presentItem == 6 ) {
				$x = $island['present']['px'];
				$y = $island['present']['py'];
			} else {
				$x = Util::random($init->islandSize);
				$y = Util::random($init->islandSize);
			}
			$landKind = $land[$x][$y];
			$lv = $landValue[$x][$y];
			$point = "({$x}, {$y})";
			// メッセージ
			$this->log->hugeMeteo($id, $name, $point);
			// 広域被害ルーチン
			$this->wideDamage($id, $name, $land, $landValue, $x, $y);
		}

		// 巨大ミサイル判定
		if ( isset($island['bigmissile']) ) {
			while($island['bigmissile'] > 0) {
				$island['bigmissile']--;
				// 家族の力
				for($i = 0; $i < $init->pointNumber; $i++) {
					$x = $this->rpx[$i];
					$y = $this->rpy[$i];
					$landKind = $land[$x][$y];
					$lv = $landValue[$x][$y];
					if(($landKind == $init->landMyhome) && (Util::random(100) < ($lv * 10))) {
						// 自宅があったとき
						$power = 1;
						if($lv > 1) {
							// 家族が１人死ぬ
							$landValue[$x][$y]--;
							$this->log->kazokuPower($id, $name, "家族の力");
							break;
						} else {
							// 全滅…
							$land[$x][$y] = $init->landWaste;
							$landValue[$x][$y] = 0;

							$this->log->kazokuPower($id, $name, "独身の底力");
							break;
						}
					}
				}

				if($power != 1) {
					// 落下
					$x = Util::random($init->islandSize);
					$y = Util::random($init->islandSize);
					$landKind = $land[$x][$y];
					$lv = $landValue[$x][$y];
					$point = "({$x}, {$y})";
					// メッセージ
					$this->log->monDamage($id, $name, $point);
					// 広域被害ルーチン
					$this->wideDamage($id, $name, $land, $landValue, $x, $y);
				}
			}
		}

		// 隕石判定
		if ((Util::random(1000) < ($init->disMeteo - (int)($island['eisei'][2] / 40)))
			|| ($presentItem == 7)) {
			$first = 1;
			while((Util::random(2) == 0) || ($first == 1)) {
				$first = 0;
				// 落下
				if (($presentItem == 7) && ($first == 1)) {
					$x = $island['present']['px'];
					$y = $island['present']['py'];
				} else {
					$x = Util::random($init->islandSize);
					$y = Util::random($init->islandSize);
				}
				$first = 0;
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				$point = "({$x}, {$y})";

				if(($landKind == $init->landSea) && ($lv == 0)){
					// 海ポチャ
					$this->log->meteoSea($id, $name, $this->landName($landKind, $lv), $point);
				} elseif($landKind == $init->landMountain) {
					// 山破壊
					$this->log->meteoMountain($id, $name, $this->landName($landKind, $lv), $point);
					$land[$x][$y] = $init->landWaste;

					$landValue[$x][$y] = 0;
					continue;
				} elseif(($landKind == $init->landSbase) || ($landKind == $init->landSfarm) ||
					($landKind == $init->landSeaCity) || ($landKind == $init->landFroCity) ||
					($landKind == $init->landSdefence)) {
					$this->log->meteoSbase($id, $name, $this->landName($landKind, $lv), $point);
				} elseif(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
					$this->log->meteoMonster($id, $name, $this->landName($landKind, $lv), $point);
				} elseif($landKind == $init->landSea) {
					// 浅瀬
					$this->log->meteoSea1($id, $name, $this->landName($landKind, $lv), $point);
				} else {
					$this->log->meteoNormal($id, $name, $this->landName($landKind, $lv), $point);
				}
				$land[$x][$y] = $init->landSea;
				$landValue[$x][$y] = 0;
			}
		}

		// 噴火判定
		if ((Util::random(1000) < ($init->disEruption - (int)($island['eisei'][1] / 40)))
			|| ($presentItem == 8)) {
			if ( $presentItem == 8 ) {
				$x = $island['present']['px'];
				$y = $island['present']['py'];
			} else {
				$x = Util::random($init->islandSize);
				$y = Util::random($init->islandSize);
			}
			$landKind = $land[$x][$y];
			$lv = $landValue[$x][$y];
			$point = "({$x}, {$y})";
			$this->log->eruption($id, $name, $this->landName($landKind, $lv), $point);
			$land[$x][$y] = $init->landMountain;
			$landValue[$x][$y] = 0;

			for($i = 1; $i < 7; $i++) {
				$sx = $x + $init->ax[$i];
				$sy = $y + $init->ay[$i];
				// 行による位置調整
				if((($sy % 2) == 0) && (($y % 2) == 1)) {
					$sx--;
				}
				$landKind = $land[$sx][$sy];
				$lv = $landValue[$sx][$sy];
				$point = "({$sx}, {$sy})";

				if(($sx < 0) || ($sx >= $init->islandSize) ||
					($sy < 0) || ($sy >= $init->islandSize)) {
				} else {
					// 範囲内の場合
					$landKind = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$point = "({$sx}, {$sy})";
						if(($landKind == $init->landSea) ||
						($landKind == $init->landOil) ||
						($landKind == $init->landSeaCity) ||
						($landKind == $init->landFroCity) ||
						($landKind == $init->landSsyoubou) ||
						($landKind == $init->landSfarm) ||
						($landKind == $init->landSdefence) ||
						($landKind == $init->landSbase)) {
						// 海の場合
						if($lv == 1) {
							// 浅瀬
							$this->log->eruptionSea1($id, $name, $this->landName($landKind, $lv), $point);
						} else {
							$land[$sx][$sy] = $init->landSea;
							$landValue[$sx][$sy] = 1;

							$this->log->eruptionSea($id, $name, $this->landName($landKind, $lv), $point);
							continue;
						}
					} elseif(($landKind == $init->landMountain) ||
						($landKind == $init->landMonster) ||
						($landKind == $init->landSleeper) ||
						($landKind == $init->landWaste)) {
						continue;
					} else {
						// それ以外の場合
						$this->log->eruptionNormal($id, $name, $this->landName($landKind, $lv), $point);
					}
					$land[$sx][$sy] = $init->landWaste;
					$landValue[$sx][$sy] = 0;
				}
			}
		}

		// 人工衛星エネルギー減少
		for($i = 0; $i < 6; $i++) {
			if($island['eisei'][$i]) {
				$island['eisei'][$i] -= Util::random(2);
				if($island['eisei'][$i] < 1) {
					$island['eisei'][$i] = 0;
					$this->log->EiseiEnd($id, $name, $init->EiseiName[$i]);
				}
			}
		}

		// 変更された可能性のある変数を書き戻す
		$island['land']      = $land;
		$island['landValue'] = $landValue;

		$island['gold'] = $island['money'] - $island['oldMoney'];
		$island['rice'] = $island['food']  - $island['oldFood'];

		// 食料があふれてたら換金
		if($island['food'] > $init->maxFood) {
			$island['money'] += round(($island['food'] - $init->maxFood) / 10);
			$island['food'] = $init->maxFood;
		}
		// 金があふれてたら切り捨て
		if($island['money'] > $init->maxMoney) {
			$island['money'] = $init->maxMoney;
		}
		// 各種の値を計算
		Turn::estimate($hako, $island);

		// 繁栄、災難賞
		$pop = $island['pop'];
		$damage = $island['oldPop'] - $pop;
		$prize = $island['prize'];
		list($flags, $monsters, $turns) = explode(",", $prize, 3);
		$island['peop'] = $island['pop'] - $island['oldPop'];
		$island['pots'] = $island['point'] - $island['oldPoint'];

		// 繁栄賞
		if((!($flags & 1)) &&  $pop >= 3000*100){
			$flags |= 1;
			$this->log->prize($id, $name, $init->prizeName[1]);
		} elseif((!($flags & 2)) &&  $pop >= 5000*100){
			$flags |= 2;
			$this->log->prize($id, $name, $init->prizeName[2]);
		} elseif((!($flags & 4)) &&  $pop >= 10000*100){
			$flags |= 4;
			$this->log->prize($id, $name, $init->prizeName[3]);
		}
		// 災難賞
		if((!($flags & 64)) &&  $damage >= 500*100){
			$flags |= 64;
			$this->log->prize($id, $name, $init->prizeName[7]);
		} elseif((!($flags & 128)) &&  $damage >= 1000*100){
			$flags |= 128;
			$this->log->prize($id, $name, $init->prizeName[8]);
		} elseif((!($flags & 256)) &&  $damage >= 2000*100){
			$flags |= 256;
			$this->log->prize($id, $name, $init->prizeName[9]);
		}
		$island['prize'] = "{$flags},{$monsters},{$turns}";
	}

	//---------------------------------------------------
	// 周囲の町、農場があるか判定
	//---------------------------------------------------
	function countGrow($land, $landValue, $x, $y) {
		global $init;

		for($i = 1; $i < 7; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// 行による位置調整
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
			} else {
				// 範囲内の場合
				if(($land[$sx][$sy] == $init->landTown) ||
					($land[$sx][$sy] == $init->landProcity) ||
					($land[$sx][$sy] == $init->landNewtown) ||
					($land[$sx][$sy] == $init->landBigtown) ||
					($land[$sx][$sy] == $init->landFarm)) {
					if($landValue[$sx][$sy] != 1) {
						return true;
					}
				}
			}
		}
		return false;
	}

	//---------------------------------------------------
	// 広域被害ルーチン
	//---------------------------------------------------
	function wideDamage($id, $name, $land, $landValue, $x, $y) {
		global $init;

		for($i = 0; $i < 19; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// 行による位置調整
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			$landKind = $land[$sx][$sy];
			$lv = $landValue[$sx][$sy];
			$landName = $this->landName($landKind, $lv);
			$point = "({$sx}, {$sy})";
			// 範囲外判定
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
				continue;
			}
			// 範囲による分岐
			if($i < 7) {
				// 中心、および1ヘックス
				if($landKind == $init->landSea) {
					$landValue[$sx][$sy] = 0;
					continue;
				} elseif(($landKind == $init->landSbase) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSsyoubou) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landZorasu) ||
					($landKind == $init->landOil)) {
					$land[$sx][$sy] = $init->landSea;
					$landValue[$sx][$sy] = 0;
					$this->log->wideDamageSea2($id, $name, $landName, $point);
				} else {
					if(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
						$this->log->wideDamageMonsterSea($id, $name, $landName, $point);
					} else {
						$this->log->wideDamageSea($id, $name, $landName, $point);
					}
					$land[$sx][$sy] = $init->landSea;
					if($i == 0) {
						// 海
						$landValue[$sx][$sy] = 0;
					} else {
						// 浅瀬
						$landValue[$sx][$sy] = 1;
					}
				}
			} else {
				// 2ヘックス
				if(($landKind == $init->landSea) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSsyoubou) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landZorasu) ||
					($landKind == $init->landOil) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landWaste) ||
					($landKind == $init->landMountain) ||
					($landKind == $init->landSbase)) {
					continue;
				} elseif(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
					$land[$sx][$sy] = $init->landWaste;
					$landValue[$sx][$sy] = 0;
					$this->log->wideDamageMonster($id, $name, $landName, $point);
				} else {
					$land[$sx][$sy] = $init->landWaste;
					$landValue[$sx][$sy] = 0;
					$this->log->wideDamageWaste($id, $name, $landName, $point);
				}
			}
		}
	}

	//---------------------------------------------------
	// 人口順でソート
	//---------------------------------------------------
	static function islandSort(&$hako) {
		global $init;
		usort($hako->islands, 'popComp');
	}

	//---------------------------------------------------
	// 収入、消費フェイズ
	//---------------------------------------------------
	function income(&$island) {
		global $init;

		$pop = $island['pop'];
		$farm = $island['farm'] * 10;
		$factory = $island['factory'];
		$commerce = $island['commerce'];
		$mountain = $island['mountain'];
		$hatuden = $island['hatuden'];

		// 工場、採掘場、商業は発電量が関係
		$enesyouhi = round($pop / 100 + $factory * 2/3 + $commerce * 1/3 + $mountain * 1/4);
		$work = min(round($enesyouhi), ($factory + $commerce + $mountain));

		// 収入
		if($pop > $farm) {
			// 農業だけじゃ手が余る場合
			if((Util::random(1000) < $init->disTenki) && ($island['tenki'] == 4)) {
				// 停電発生
				if($island['zin'][5] == 1) {
					// ジン所持時
					$island['food'] += $farm * 2; // 全員野良仕事
				} else {
					$island['food'] += $farm; // 全員野良仕事
				}
				$this->log->Teiden($island['id'], $island['name']);
			} else {
				if($island['zin'][5] == 1) {
					// ジン所持時
					$island['food'] += $farm * 2; // 農場フル稼働
		        } else {
					$island['food'] += $farm; // 農場フル稼働
				}
				if($island['zin'][6] == 1) {
					// サラマンダー所持時
					$island['money'] += (min(round(($pop - $farm) / 10), $work)) * 2;
				} else {
					$island['money'] += min(round(($pop - $farm) / 10), $work);
				}
			}
		} else {
			// 農業だけで手一杯の場合
			$island['food'] += $pop; // 全員野良仕事
		}
		if ( isset($island['present']) ) {
			if ( $island['present'] == 0 ) {
				if ( $island['present']['px'] != 0 ) {
					$island['money'] += $island['present']['px'];
					$this->log->presentMoney($island['id'], $island['name'], $island['present']['px']);
				}
				if ( $island['present']['py'] != 0 ) {
					$island['food'] += $island['present']['py'];
					$this->log->presentFood($island['id'], $island['name'], $island['present']['py']);
				}
			}
		}
		// 食料消費
		$island['food'] = round($island['food'] - $pop * $init->eatenFood);

		// 船
		$shipCost = 0;
		for($i = 0; $i < 10; $i++) {
			$shipCost += $init->shipCost[$i] * $island['ship'][$i];
		}
		$island['money'] -= $shipCost;
		if($island['port'] > 0){
			$island['money'] += $init->shipIncom * $island['ship'][0];
			$island['food']  += $init->shipFood  * $island['ship'][1];
		}
		if($island['money'] < 0) $island['money'] = 0;
		if($island['food'] < 0) $island['food']  = 0 ;
	}

	//---------------------------------------------------
	// 船舶数初期化
	//---------------------------------------------------
	function shipcounter($hako, &$island) {
		global $init;

		// 船舶数初期化
		for($i = 0; $i < 15; $i++) {
			$island['ship'][$i] = 0;
		}
	}
	//---------------------------------------------------
	// 人口その他の値を算出
	//---------------------------------------------------
	static function estimate($hako, &$island) {
		global $init;

		$land = $island['land'];
		$landValue = $island['landValue'];

		$area      = 0;
		$pop       = 0;
		$farm      = 0;
		$factory   = 0;
		$commerce  = 0;
		$mountain  = 0;
		$hatuden   = 0;
		$home      = 0;
		$monster   = 0;
		$port      = 0;
		$oil       = 0;
		$soccer    = 0;
		$park      = 0;
		$stat      = 0;
		$train     = 0;
		$bank      = 0;
		$m23       = 0;
		$fire = $rena = $base = 0;

		// 数える
		for($y = 0; $y < $init->islandSize; $y++) {
			for($x = 0; $x < $init->islandSize; $x++) {
				$kind = $land[$x][$y];
				$value = $landValue[$x][$y];
				if($kind == $init->landShip){
					$ship = Util::navyUnpack($value);
					if($ship[0] != 0) {
						$tn = $hako->idToNumber[$ship[0]];
						$tIsland = &$hako->islands[$tn];
						$tIsland['ship'][$ship[1]]++;
					} else {
						$island['ship'][$ship[1]]++;
					}
				}
				if($kind == $init->landOil) {
					$oil++;
				}
				if($kind == $init->landSbase) {
					$base += 3;
					$fire += Util::expToLevel($kind, $value);
				}
				if($kind == $init->landSdefence) {
					$base += $value;
				}
				if(($kind != $init->landSea) &&
					($kind != $init->landShip) &&
					($kind != $init->landSbase) &&
					($kind != $init->landSdefence) &&
					($kind != $init->landSsyoubou) &&
					($kind != $init->landOil)) {
					if(($kind != $init->landNursery) && ($kind != $init->landSeaCity) && ($kind != $init->landFroCity) &&
						($kind != $init->landSfarm) && ($kind != $init->landZorasu)) {
						$area++;
					}
					switch($kind) {
						case $init->landTown:
						case $init->landSeaCity:
						case $init->landFroCity:
						case $init->landProcity:
							// 町
							$base++;
							$pop += $value;
							break;

						case $init->landNewtown:
							// ニュータウン
							$pop += $value;
							$nwork =  (int)($value/15);
							$commerce += $nwork;
							break;

						case $init->landBigtown:
							// 現代都市
							$pop += $value;
							$mwork =  (int)($value/20);
							$lwork =  (int)($value/30);
							$farm += $mwork;
							$commerce += $lwork;
							break;

						case $init->landFarm:
							// 農場
							if(Turn::countAround($land, $x, $y, 19, array($init->landFusya))){
								// 周囲2へクスに風車があれば2倍の規模に
								$farm += $value * 2;
							}else{
								$farm += $value;
							}
							break;

						case $init->landSfarm:
							// 海底農場
							$farm += $value;
							break;

						case $init->landNursery:
							// 養殖場
							$farm += $value;
							break;

						case $init->landFactory:
							// 工場
							$factory += $value;
							break;

						case $init->landCommerce:
							// 商業
							$commerce += $value;
							break;

						case $init->landMountain:
							// 山
							$mountain += $value;
							break;

						case $init->landHatuden:
							// 発電所
							if($island['zin'][4] == 1) {
								// ルナ所持
								$hatuden += $value * 2;
							} else {
								$hatuden += $value;
							}
							break;

						case $init->landBase:
							// ミサイル
							$base += 2;
							$fire += Util::expToLevel($kind, $value);
							break;

						case $init->landMonster:
						case $init->landSleeper:
							// 怪獣
							$monster++;
							break;

						case $init->landZorasu:
							// ぞらす
							$hatuden += $value;
							break;

						case $init->landPort:
							// 港
							$port++;
							break;

						case $init->landStat:
							// 駅
							$stat++;
							break;

						case $init->landTrain:
							// 電車
							$train++;
							break;

						case $init->landSoccer:
							// スタジアム
							$soccer++;
							break;

						case $init->landPark:
							// 遊園地
							$park++;
							break;

						case $init->landBank:
							// 銀行
							$bank++;
							break;

						case $init->landMonument:
							// 記念碑
							if($value == 23) {
								$m23++;
							}
							break;

						case $init->landMyhome:
							// マイホーム
							$home++;
							break;
					}
				}
			}
		}
		// 代入
		$island['pop']      = $pop;
		$island['area']     = $area;
		$island['farm']     = $farm;
		$island['factory']  = $factory;
		$island['commerce'] = $commerce;
		$island['mountain'] = $mountain;
		$island['hatuden']  = $hatuden;
		$island['home']     = $home;
		$island['oil']      = $oil;
		$island['monster']  = $monster;
		$island['port']     = $port;
		$island['stat']     = $stat;
		$island['train']    = $train;
		$island['soccer']   = $soccer;
		$island['park']     = $park;
		$island['bank']     = $bank;
		$island['m23']      = $m23;
		$island['fire']     = $fire;
		$island['rena']     = $fire + $base;

		// 電力消費量
		if(($island['pop'] - $island['farm']) <= 0 || ($island['factory'] + $island['commerce'] + $island['mountain']) <= 0) {
			$island['enesyouhi'] = 0;
		} elseif($island['factory'] + $island['commerce'] + $island['mountain'] > 0) {
			$island['enesyouhi'] = min(round($island['pop'] - $island['farm']), ($island['factory'] * 2/3 + $island['commerce'] * 1/3 + $island['mountain'] * 1/4));
		}
		// 電力過不足量
		$island['enehusoku'] = $island['hatuden'] - $island['enesyouhi'];

		if($island['soccer'] == 0) {
			$island['kachi'] = $island['make'] = $island['hikiwake'] = $island['kougeki'] = $island['bougyo'] = $island['tokuten'] = $island['shitten'] = 0;
		}
		$island['team'] = $island['kachi']*2 - $island['make']*2 + $island['hikiwake'] + $island['kougeki'] + $island['bougyo'] + $island['tokuten'] - $island['shitten'];

		if($island['pop'] == 0) {
			$island['point'] = 0;
		} else {
			if($island['isBF'] == 1) {
				$island['point'] = 100;
			} else {
				$island['point'] = ($island['pop']*15 + $island['money'] + $island['food'] + $island['farm']*2 + $island['factory'] + $island['commerce']*1.2 + $island['mountain']*2 + $island['hatuden']*3 + $island['team'] + $island['area']*5 + $island['taiji']*5 + $island['fire']*10 + $island['monster']*5)*10;
			}
		}
		$island['seichi'] = 0;
	}

	//---------------------------------------------------
	// 範囲内の地形を数える
	//---------------------------------------------------
	static function countAround($land, $x, $y, $range, $kind) {
		global $init;

		// 範囲内の地形を数える
		$count = 0;
		$sea = 0;
		$list = array();
		$sx = 0;
		$sy = 0;
		reset($kind);

		while (list(, $value) = each($kind)) {
			$list[$value] = 1;
		}
		for($i = 0; $i < $range; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// 行による位置調整
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
				// 範囲外の場合
				// 海に加算
				$sea++;
			} elseif( isset($list[$land[$sx][$sy]]) ) {
				// 範囲内の場合
				$count++;
			}
		}

		if ( isset($init->landSea) ) {
			if( isset($list[$init->landSea]) ) {
				$count += $sea;
			}
		}
		return $count;
	}
	//---------------------------------------------------
	// 範囲内の地形＋値でカウント
	//---------------------------------------------------
	function countAroundValue($island, $x, $y, $kind, $lv, $range) {
		global $init;

		$land = $island['land'];
		$landValue = $island['landValue'];
		$count = 0;

		for($i = 0; $i < $range; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// 行による位置調整
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
				// 範囲外の場合
			} else {
				// 範囲内の場合
				if($land[$sx][$sy] == $kind && $landValue[$sx][$sy] >= $lv) {
					$count++;
				}
			}
		}
		return $count;
	}

	//---------------------------------------------------
	// 地形の呼び方
	//---------------------------------------------------
	function landName($land, $lv) {
		global $init;

		switch($land) {
			case $init->landSea:
				if($lv == 1) {
					return '浅瀬';
				} else {
					return '海';
				}

			case $init->landShip:
				// 船舶
				$ship = Util::navyUnpack($lv);
				return $init->shipName[$ship[1]];

			case $init->landPort:
				return '港';

			case $init->landRail:
				return '線路';

			case $init->landStat:
				return '駅';

			case $init->landTrain:
				return '電車';

			case $init->landZorasu:
				return 'ぞらす';

			case $init->landSeaSide:
				return '砂浜';

			case $init->landSeaResort:
				// 海の家
				$n;
				if($lv < 30) {
					$n = '海の家';
				} elseif($lv < 100) {
					$n = '民宿';
				} else {
					$n = 'リゾートホテル';
				}
				return $n;

			case $init->landWaste:
				return '荒地';

			case $init->landPoll:
				return '汚染土壌';

			case $init->landPlains:
				return '平地';

			case $init->landTown:
				if($lv < 30) {
					return '村';
				} elseif($lv < 100) {
					return '町';
				} elseif($lv < 200) {
					return '都市';
				} else {
					return '大都市';
				}

			case $init->landProcity:
				return '防災都市';

			case $init->landNewtown:
				return 'ニュータウン';

			case $init->landBigtown:
				return '現代都市';

			case $init->landForest:
				return '森';

			case $init->landFarm:
				return '農場';

			case $init->landSfarm:
				return '海底農場';

			case $init->landNursery:
				return '養殖場';

			case $init->landFactory:
				return '工場';

			case $init->landCommerce:
				return '商業ビル';

			case $init->landHatuden:
				return '発電所';

			case $init->landBank:
				return '銀行';

			case $init->landBase:
				return 'ミサイル基地';

			case $init->landDefence:
				return '防衛施設';

			case $init->landSdefence:
				return '海底防衛施設';

			case $init->landMountain:
				return '山';

			case $init->landMonster:
			case $init->landSleeper:
				$monsSpec = Util::monsterSpec($lv);
				return $monsSpec['name'];

			case $init->landSbase:
				return '海底基地';

			case $init->landSeaCity:
				return '海底都市';

			case $init->landFroCity:
				return '海上都市';

			case $init->landOil:
				return '海底油田';

			case $init->landMyhome:
				return 'マイホーム';

			case $init->landSoukoM:
				return '金庫';

			case $init->landSoukoF:
				return '食料庫';

			case $init->landMonument:
				return $init->monumentName[$lv];

			case $init->landHaribote:
				return 'ハリボテ';

			case $init->landSoccer:
				return 'スタジアム';

			case $init->landPark:
				return '遊園地';

			case $init->landFusya:
				return '風車';

			case $init->landSyoubou:
				return '消防署';

			case $init->landSsyoubou:
				return '海底消防署';

		}
	}
}

//---------------------------------------------------
// ポイントを比較
//---------------------------------------------------
function popComp($x, $y) {
	if ($x['point'] == 0) {
		return 1;
	}
	if ($y['point'] == 0) {
		return -1;
	}
	if ($x['isBF']) {
		if (!($y['isBF'])) {
			return 1;
		}
	}
	if ($y['isBF']) {
		if (!($x['isBF'])) {
			return -1;
		}
	}
	if($x['point'] == $y['point']) {
		return 0;
	}
	return ($x['point'] > $y['point']) ? -1 : 1;
}
