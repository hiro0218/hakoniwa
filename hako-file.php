<?php

/*******************************************************************

	箱庭諸島 S.E

	- データフォーマット定義用ファイル -

	hako-file.php by SERA - 2012/07/08

*******************************************************************/

//--------------------------------------------------------------------
class File {
	public $islandTurn;      // ターン数
	public $islandLastTime;  // 最終更新時刻
	public $islandNumber;    // 島の総数
	public $islandNextID;    // 次に割り当てる島ID
	public $islands;         // 全島の情報を格納
	public $idToNumber;
	public $idToName;
	public $islandNumberBF;   // BFに設定されている島の数
	public $islandNumberNoBF; // 普通の島の数
	public $islandNumberKP;   // 管理人預かりに設定されている島の数
	public $islandNumberNoKP; // 普通の島の数
	public $allyNumber;       // 同盟の総数
	public $ally;             // 各同盟の情報を格納
	public $idToAllyNumber;

	//---------------------------------------------------
	// 全島データを読み込む
	// 'mode'が変わる可能性があるので$cgiを参照で受け取る
	//---------------------------------------------------
	function readIslandsFile(&$cgi) {
		global $init;

		$num = (isset( $cgi->dataSet['ISLANDID'] )) ? $cgi->dataSet['ISLANDID'] : "";
		$fileName = "{$init->dirName}/hakojima.dat";
		if(!is_file($fileName)) {
			return false;
		}
		$fp = fopen($fileName, "r");
		$this->islandTurn = chop(fgets($fp, READ_LINE));
		$this->islandLastTime = chop(fgets($fp, READ_LINE));
		$str = chop(fgets($fp, READ_LINE));
		list ($this->islandNumber, $this->islandNumberBF, $this->islandNumberKP) = array_pad(explode(",", $str), 3, 0);

		$this->islandNextID = chop(fgets($fp, READ_LINE));

		$GLOBALS['ISLAND_TURN'] = $this->islandTurn;

		// ターン処理判定
		$now = $_SERVER['REQUEST_TIME'];
		$_mode = isset($cgi->dataSet['mode']) ? $cgi->dataSet['mode'] : "";
		if((DEBUG && (strcmp($_mode, 'debugTurn') == 0)) ||
			 (($now - $this->islandLastTime) >= $init->unitTime)) {
			$cgi->mode = $data['mode'] = 'turn';
			$num = -1;
		}
		$islandNumberBF = $islandNumberKP = 0;
		for($i = 0; $i < $this->islandNumber; $i++) {
			$this->islands[$i] = $this->readIsland($fp, $num);
			if ( $this->islands[$i]['isBF'] ) { $islandNumberBF++; }
			if ( $this->islands[$i]['keep'] ) { $islandNumberKP++; }
			$this->idToNumber[$this->islands[$i]['id']] = $i;
			$this->islands[$i]['allyId'] = array();
		}
		$this->islandNumberBF = $islandNumberBF;
		$this->islandNumberKP = $islandNumberKP;
		$this->islandNumberNoBF = $this->islandNumber - $islandNumberBF;
		$this->islandNumberNoKP = $this->islandNumber - $islandNumberKP;
		fclose($fp);

		if($init->allyUse) {
			$this->readAllyFile();
		}
		return true;
	}
	//---------------------------------------------------
	// 島ひとつ読み込む
	//---------------------------------------------------
	function readIsland($fp, $num) {
		global $init;

		$name = chop(fgets($fp, READ_LINE));
		list($name, $owner, $monster, $port, $ship0, $ship1, $ship2, $ship3, $ship4, $ship5, $ship6, $ship7, $ship8, $ship9, $ship10, $ship11, $ship12, $ship13, $ship14) = array_pad(explode(",", $name), 20, 0);
		$id = chop(fgets($fp, READ_LINE));
		list($id, $starturn, $isBF, $keep) = array_pad(explode(",", $id), 4, 0);
		if ($isBF) {
			$isBF = 1;
		}
		if ($keep) {
			$keep = 1;
		}
		$prize = chop(fgets($fp, READ_LINE));
		$absent = chop(fgets($fp, READ_LINE));
		$comment = chop(fgets($fp, READ_LINE));
		list($comment, $comment_turn) = array_pad(explode(",", $comment), 2, 0);
		$password = chop(fgets($fp, READ_LINE));
		$point = chop(fgets($fp, READ_LINE));
		list($point, $pots) = array_pad(explode(",", $point), 2, NULL);
		$eisei = chop(fgets($fp, READ_LINE));
		list($eisei0, $eisei1, $eisei2, $eisei3, $eisei4, $eisei5) = array_pad(explode(",", $eisei), 6, 0);
		$zin = chop(fgets($fp, READ_LINE));
		list($zin0, $zin1, $zin2, $zin3, $zin4, $zin5, $zin6) = array_pad(explode(",", $zin), 7, 0);
		$item = chop(fgets($fp, READ_LINE));
		list($item0, $item1, $item2, $item3, $item4, $item5, $item6, $item7, $item8, $item9, $item10, $item11, $item12, $item13, $item14, $item15, $item16, $item17, $item18, $item19, $item20) = array_pad(explode(",", $item), 21, NULL);
		$money = chop(fgets($fp, READ_LINE));
		list($money, $lot, $gold) = array_pad(explode(",", $money), 3, 0);
		$food = chop(fgets($fp, READ_LINE));
		list($food, $rice) = array_pad(explode(",", $food), 2, 0);
		$pop = chop(fgets($fp, READ_LINE));
		list($pop, $peop) = array_pad(explode(",", $pop), 2, 0);
		$area = chop(fgets($fp, READ_LINE));
		$job = chop(fgets($fp, READ_LINE));
		list($farm, $factory, $commerce, $mountain, $hatuden) = array_pad(explode(",", $job), 5, 0);
		$power = chop(fgets($fp, READ_LINE));
		list($taiji, $rena, $fire) = array_pad(explode(",", $power), 3, 0);
		$tenki = chop(fgets($fp, READ_LINE));
		$soccer = chop(fgets($fp, READ_LINE));
		list($soccer, $team, $shiai, $kachi, $make, $hikiwake, $kougeki, $bougyo, $tokuten, $shitten) = array_pad(explode(",", $soccer), 10, 0);

		$this->idToName[$id] = $name;

		if(($num == -1) || ($num == $id)) {
			$fp_i;

			// データファイルの存在チェック
			if ( file_exists("{$init->dirName}/island.{$id}") ) {
				$fp_i = fopen("{$init->dirName}/island.{$id}", "r");
			} else {
				$fp_i = false;
			}

			if ($fp_i === false) {
				Html::header();
				Error::problem();
			}


			// 地形
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

			// コマンド
			for($i = 0; $i < $init->commandMax; $i++) {
				$line = chop(fgets($fp_i, READ_LINE));
				list($kind, $target, $x, $y, $arg) = explode(",", $line);
				$command[$i] = array (
					'kind' => $kind,
					'target' => $target,
					'x' => $x,
					'y' => $y,
					'arg' => $arg,
				);
			}

			fclose($fp_i);
		}

		return array(
			'name'         => $name,
			'owner'        => $owner,
			'id'           => $id,
			'starturn'     => $starturn,
			'isBF'         => $isBF,
			'keep'         => $keep,
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
			'land'         => isset($land)      ? $land      : "",
			'landValue'    => isset($landValue) ? $landValue : "",
			'command'      => isset($command)   ? $command   : "",
			'lbbs'         => isset($lbbs)      ? $lbbs      : "",
			'port'         => $port,
			'ship'         => array(0 => $ship0, 1 => $ship1, 2 => $ship2, 3 => $ship3, 4 => $ship4, 5 => $ship5, 6 => $ship6, 7 => $ship7, 8 => $ship8, 9 => $ship9, 10 => $ship10, 11 => $ship11, 12 => $ship12, 13 => $ship13, 14 => $ship14),
			'eisei'        => array(0 => $eisei0, 1 => $eisei1, 2 => $eisei2, 3 => $eisei3, 4 => $eisei4, 5 => $eisei5),
			'zin'          => array(0 => $zin0, 1 => $zin1, 2 => $zin2, 3 => $zin3, 4 => $zin4, 5 => $zin5, 6 => $zin6),
			'item'         => array(0 => $item0, 1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4, 5 => $item5, 6 => $item6, 7 => $item7, 8 => $item8, 9 => $item9, 10 => $item10, 11 => $item11, 12 => $item12, 13 => $item13, 14 => $item14, 15 => $item15, 16 => $item16, 17 => $item17, 18 => $item18, 19 => $item19, 20 => $item20),
		);
	}
	//---------------------------------------------------
	// 地形を書き込む
	//---------------------------------------------------
	function writeLand($num, $island) {
		global $init;
		// 地形
		if(($num <= -1) || ($num == $island['id'])) {
			$fileName = "{$init->dirName}/island.{$island['id']}";

			if(!is_file($fileName)) {
				touch($fileName);
			}
			$fp_i = fopen($fileName, "w");
			$land = $island['land'];
			$landValue = $island['landValue'];

			for($y = 0; $y < $init->islandSize; $y++) {
				for($x = 0; $x < $init->islandSize; $x++) {
					$l = sprintf("%02x%05x", $land[$x][$y], $landValue[$x][$y]);
					fputs($fp_i, $l);
				}
				fputs($fp_i, "\n");
			}
			// コマンド
			$command = $island['command'];
			for($i = 0; $i < $init->commandMax; $i++) {
					$com = sprintf("%d,%d,%d,%d,%d\n",
					$command[$i]['kind'],
					$command[$i]['target'],
					$command[$i]['x'],
					$command[$i]['y'],
					$command[$i]['arg']
				);
				fputs($fp_i, $com);
			}

			fclose($fp_i);
		}
	}
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
		$this->allyNumber = chop(fgets($fp, READ_LINE));
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
		fclose($fp);
		return true;
	}
	//--------------------------------------------------
	// 同盟ひとつ読みこみ
	//--------------------------------------------------
	function readAlly($fp) {
		$name = chop(fgets($fp, READ_LINE));
		$mark = chop(fgets($fp, READ_LINE));
		$color = chop(fgets($fp, READ_LINE));
		$id = chop(fgets($fp, READ_LINE));
		$ownerName = chop(fgets($fp, READ_LINE));
		$password = chop(fgets($fp, READ_LINE));
		$score = chop(fgets($fp, READ_LINE));
		$number = chop(fgets($fp, READ_LINE));
		$occupation = chop(fgets($fp, READ_LINE));
		$tmp = chop(fgets($fp, READ_LINE));
		$allymember = explode(",", $tmp);
		$tmp = chop(fgets($fp, READ_LINE));
		$ext = explode(",", $tmp); // 拡張領域
		$comment = chop(fgets($fp, READ_LINE));
		$title = chop(fgets($fp, READ_LINE));
		list($title, $message) = array_pad(explode("<>", $title), 2, 0);

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
	//---------------------------------------------------
	// 全島データを書き込む
	//---------------------------------------------------
	function writeIslandsFile($num = 0) {
		global $init;

		$fileName = "{$init->dirName}/hakojima.dat";

		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "w");
		fputs($fp, $this->islandTurn . "\n");
		fputs($fp, $this->islandLastTime . "\n");
		fputs($fp, $this->islandNumber . "," . $this->islandNumberBF . "," . $this->islandNumberKP . "\n");
		fputs($fp, $this->islandNextID . "\n");
		for($i = 0; $i < $this->islandNumber; $i++) {
			$this->writeIsland($fp, $num, $this->islands[$i]);
		}
		fclose($fp);
		// chmod($fileName, 0666);
	}
	//---------------------------------------------------
	// 島ひとつ書き込む
	//---------------------------------------------------
	function writeIsland($fp, $num, $island) {
		global $init;

		$ships  = "";
		$eiseis = "";
		$zins   = "";
		$items  = "";

		if ( !isset($island['ship']) ) {
			for ($i=0; $i<=14; $i++) {
				if ( !isset($island['ship'][$i]) ) {
					$island['ship'][$i] = "";
				}
			}
		}
		if ( !isset($island['eisei']) ) {
			for ($i=0; $i<=5; $i++) {
				if ( !isset($island['eisei'][$i]) ) {
					$island['eisei'][$i] = "";
				}
			}
		}
		if ( !isset($island['zin']) ) {
			for ($i=0; $i<=6; $i++) {
				if ( !isset($island['zin'][$i]) ) {
					$island['zin'][$i] = "";
				}
			}
		}
		if ( !isset($island['item']) ) {
			for ($i=0; $i<=20; $i++) {
				if ( !isset($island['item'][$i]) ) {
					$island['item'][$i] = "";
				}
			}
		}

		$ships  = $island['ship'][0].",".$island['ship'][1].",".$island['ship'][2].",".$island['ship'][3].",".$island['ship'][4].",".$island['ship'][5].",".$island['ship'][6].",".$island['ship'][7].",".$island['ship'][8].",".$island['ship'][9].",".$island['ship'][10].",".$island['ship'][11].",".$island['ship'][12].",".$island['ship'][13].",".$island['ship'][14];
		$eiseis = $island['eisei'][0].",".$island['eisei'][1].",".$island['eisei'][2].",".$island['eisei'][3].",".$island['eisei'][4].",".$island['eisei'][5];
		$zins   = $island['zin'][0].",".$island['zin'][1].",".$island['zin'][2].",".$island['zin'][3].",".$island['zin'][4].",".$island['zin'][5].",".$island['zin'][6];
		$items  = $island['item'][0].",".$island['item'][1].",".$island['item'][2].",".$island['item'][3].",".$island['item'][4].",".$island['item'][5].",".$island['item'][6].",".$island['item'][7].",".$island['item'][8].",".$island['item'][9].",".$island['item'][10].",".$island['item'][11].",".$island['item'][12].",".$island['item'][13].",".$island['item'][14].",".$island['item'][15].",".$island['item'][16].",".$island['item'][17].",".$island['item'][18].",".$island['item'][19].",".$island['item'][20];

		fputs($fp, $island['name'] . "," . $island['owner'] . "," . $island['monster'] . "," . $island['port'] . "," . $ships . "\n");
		fputs($fp, $island['id'] . "," . $island['starturn'] . "," . $island['isBF'] . "," . $island['keep'] . "\n");
		fputs($fp, $island['prize'] . "\n");
		fputs($fp, $island['absent'] . "\n");
		fputs($fp, $island['comment'] . "," . $island['comment_turn'] . "\n");
		fputs($fp, $island['password'] . "\n");

		if ( !isset($island['pots']) ) {
			$island['pots'] = "0";
		}
		if ( !isset($island['lot']) ){
			$island['lot'] = "0";
		}
		if ( !isset($island['gold']) ) {
			$island['gold'] = "0";
		}
		if ( !isset($island['rice']) ) {
			$island['rice'] = "0";
		}
		if ( !isset($island['peop']) ) {
			$island['peop'] = "0";
		}

		fputs($fp, $island['point'] . "," . $island['pots'] . "\n");
		fputs($fp, $eiseis . "\n");
		fputs($fp, $zins . "\n");
		fputs($fp, $items . "\n");
		fputs($fp, $island['money'] . "," . $island['lot'] . "," . $island['gold'] . "\n");
		fputs($fp, $island['food'] . "," . $island['rice'] . "\n");
		fputs($fp, $island['pop'] . "," . $island['peop'] . "\n");

		fputs($fp, $island['area'] . "\n");
		fputs($fp, $island['farm'] . "," . $island['factory'] . "," . $island['commerce'] . "," . $island['mountain'] ."," . $island['hatuden'] . "\n");
		fputs($fp, $island['taiji'] . "," . $island['rena'] . "," . $island['fire'] . "\n");
		fputs($fp, $island['tenki'] . "\n");
		fputs($fp, $island['soccer'].",".$island['team'].",".$island['shiai'].",".$island['kachi'].",".$island['make'].",".$island['hikiwake'].",".$island['kougeki'].",".$island['bougyo'].",".$island['tokuten'].",".$island['shitten']."\n");

		// 地形
		if(($num <= -1) || ($num == $island['id'])) {
			$fileName = "{$init->dirName}/island.{$island['id']}";

			if(!is_file($fileName)) {
				touch($fileName);
			}
			$fp_i = fopen($fileName, "w");
			$land = $island['land'];
			$landValue = $island['landValue'];

			for($y = 0; $y < $init->islandSize; $y++) {
				for($x = 0; $x < $init->islandSize; $x++) {
					$l = sprintf("%02x%05x", $land[$x][$y], $landValue[$x][$y]);
					fputs($fp_i, $l);
				}
				fputs($fp_i, "\n");
			}
			// コマンド
			$command = $island['command'];
			for($i = 0; $i < $init->commandMax; $i++) {
				$com = sprintf("%d,%d,%d,%d,%d\n",
					$command[$i]['kind'],
					$command[$i]['target'],
					$command[$i]['x'],
					$command[$i]['y'],
					$command[$i]['arg']
				);
				fputs($fp_i, $com);
			}

			fclose($fp_i);
			// chmod($fileName, 0666);
		}
	}
	//---------------------------------------------------
	// データのバックアップ
	//---------------------------------------------------
	function backUp() {
		global $init;

		if($init->backupTimes <= 0) {
			return;
		}
		$tmp = $init->backupTimes - 1;
		$this->rmTree("{$init->dirName}.bak{$tmp}");
		for($i = ($init->backupTimes - 1); $i > 0; $i--) {
			$j = $i - 1;
			if(is_dir("{$init->dirName}.bak{$j}")) {
				rename("{$init->dirName}.bak{$j}", "{$init->dirName}.bak{$i}");
			}
		}
		if(is_dir("{$init->dirName}")) {
			rename("{$init->dirName}", "{$init->dirName}.bak0");
		}
		mkdir("{$init->dirName}", $init->dirMode);

		// ログファイルだけ戻す
		for($i = 0; $i <= $init->logMax; $i++) {
			if(is_file("{$init->dirName}.bak0/hakojima.log{$i}")){
				rename("{$init->dirName}.bak0/hakojima.log{$i}", "{$init->dirName}/hakojima.log{$i}");
			}
		}
		if(is_file("{$init->dirName}.bak0/hakojima.his")) {
			rename("{$init->dirName}.bak0/hakojima.his", "{$init->dirName}/hakojima.his");
		}
	}
	//---------------------------------------------------
	// セーフモードバックアップ
	//---------------------------------------------------
	function safemode_backup() {
		global $init;

		try {
			if ($init->backupTimes <= 0) {
				return;
			}
			for ($i = ($init->backupTimes - 1); $i >= 0; $i--) {
				$from = $i - 1;
				$dir2;
				if ($from >= 0) {
					$dir2 = "./{$init->dirName}.bak{$from}";
				} else {
					$dir2 = "./{$init->dirName}";
				}
				// データディレクトリの中身を空にする
				$to_del = "{$init->dirName}.bak{$i}";
				if ( file_exists("{$to_del}/") ) {
					$dir = opendir("{$to_del}/");
				}

				while ($fileName = readdir($dir)) {
					if (!(strcmp($fileName, ".") == 0 || strcmp($fileName, "..") == 0))
						unlink("{$to_del}/{$fileName}");
				}
				closedir($dir);

				// データディレクトリを開く
				if ( file_exists($dir2) ) {
					$dir = opendir($dir2);
				} else {
					mkdir($dir2);
				}
				$dir = opendir($dir2);

				while ($copyFile = readdir($dir)) {
					if (is_file("{$dir2}/{$copyFile}")) {
						// コピー元
						$from_copy = "{$dir2}/{$copyFile}";
						// コピー先
						$to_copy = "{$init->dirName}.bak{$i}/{$copyFile}";
						// コピー実行
						copy($from_copy, $to_copy);
					}
				}
			}
		} catch (Exception $ex) {
			return;
		}
	}

	//---------------------------------------------------
	// 不要なディレクトリとファイルを削除
	//---------------------------------------------------
	function rmTree($dirName) {
		if(is_dir("{$dirName}")) {
			$dir = opendir("{$dirName}/");
			while($fileName = readdir($dir)) {
				if(!(strcmp($fileName, ".") == 0 || strcmp($fileName, "..") == 0)) {
					unlink("{$dirName}/{$fileName}");
				}
			}
			closedir($dir);
			rmdir($dirName);
		}
	}

	function readPresentFile( $erase = false ) {
		global $init;

		$fileName = "{$init->dirName}/present.dat";
		if(is_file($fileName)) {
			$presents = file($fileName);
			foreach ($presents as $present) {
				list($id, $item, $px, $py) = explode(",", chop($present));
				$num = $this->idToNumber[$id];
				$this->islands[$num]['present']['item'] = $item;
				$this->islands[$num]['present']['px'] = $px;
				$this->islands[$num]['present']['py'] = $py;
			}
			if ( $erase ) {
				unlink($fileName);
			}
		}
	}

	function writePresentFile() {
		global $init;

		$presents = array();
		$fileName = "{$init->dirName}/present.dat";
		for($i = 0; $i < $this->islandNumber; $i++) {
			$present =& $this->islands[$i]['present'];
			if ((( $present['item'] == 0 ) && (( $present['px'] != 0 ) || ( $present['py'] != 0 ))) ||
				(( $present['item'] > 0 ) && ( $present['item'] < 9 ))) {
				$presents[] = $this->islands[$i]['id'] . ',' . $present['item'] . ',' . $present['px'] . ',' . $present['py'] . "\n";
			}
		}
		$num = count($presents);
		$fp = fopen($fileName, "w");
		if ( $num > 0 ) {
			for ($i = 0; $i < $num ; $i++) {
				fputs( $fp, $presents[$i]);
			}
		}
		fclose($fp);
	}
}
