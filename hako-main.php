<?php

/*******************************************************************

	” ’ë”“‡ S.E
	
	- ƒƒCƒ“ƒtƒ@ƒCƒ‹ -
	
	hako-main.php by SERA - 2013/05/19

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-cgi.php';
require 'hako-file.php';
require 'hako-html.php';
require 'hako-turn.php';
require 'hako-util.php';

$init = new Init;

define("READ_LINE", 1024);
$THIS_FILE = $init->baseDir . "/hako-main.php";
$BACK_TO_TOP = "<A HREF=\"{$THIS_FILE}?\">{$init->tagBig_}ƒgƒbƒv‚Ö–ß‚é{$init->_tagBig}</A>";
$ISLAND_TURN; // ƒ^[ƒ“”

$PRODUCT_VERSION = '20130519';

//--------------------------------------------------------------------
class Hako extends HakoIO {
	var $islandList;    // “‡ƒŠƒXƒg
	var $targetList;    // ƒ^[ƒQƒbƒg‚Ì“‡ƒŠƒXƒg
	var $defaultTarget; // –Ú•W•â‘«—pƒ^[ƒQƒbƒg
	
	function readIslands(&$cgi) {
		global $init;
		
		$m = $this->readIslandsFile($cgi);
		$this->islandList = $this->getIslandList($cgi->dataSet['defaultID']);
		if($init->targetIsland == 1) {
			// –Ú•W‚Ì“‡ Š—L‚Ì“‡‚ª‘I‘ğ‚³‚ê‚½ƒŠƒXƒg
			$this->targetList = $this->islandList;
		} else {
			// ‡ˆÊ‚ªTOP‚Ì“‡‚ª‘I‘ğ‚³‚ê‚½ó‘Ô‚ÌƒŠƒXƒg
			$this->targetList = $this->getIslandList($cgi->dataSet['defaultTarget']);
		}
		return $m;
	}
	//---------------------------------------------------
	// “‡ƒŠƒXƒg¶¬
	//---------------------------------------------------
	function getIslandList($select = 0) {
		global $init;
		
		$list = "";
		for($i = 0; $i < $this->islandNumber; $i++) {
			if($init->allyUse) {
				$name = Util::islandName($this->islands[$i], $this->ally, $this->idToAllyNumber); // “¯–¿ƒ}[ƒN‚ğ’Ç‰Á
			} else {
				$name = $this->islands[$i]['name'];
			}
			$id = $this->islands[$i]['id'];
			
			// UŒ‚–Ú•W‚ğ‚ ‚ç‚©‚¶‚ß©•ª‚Ì“‡‚É‚·‚é
			if(empty($this->defaultTarget)) {
				$this->defaultTarget = $id;
			}
			
			if($id == $select) {
				$s = "selected";
			} else {
				$s = "";
			}
			if($init->allyUse) {
				$list .= "<option value=\"$id\" $s>{$name}</option>\n"; // “¯–¿ƒ}[ƒN‚ğ’Ç‰Á
			} else {
				$list .= "<option value=\"$id\" $s>{$name}“‡</option>\n";
			}
		}
		return $list;
	}
	//---------------------------------------------------
	// Ü‚ÉŠÖ‚·‚éƒŠƒXƒg‚ğ¶¬
	//---------------------------------------------------
	function getPrizeList($prize) {
		global $init;
		list($flags, $monsters, $turns) = split(",", $prize, 3);
		
		$turns = split(",", $turns);
		$prizeList = "";
		// ƒ^[ƒ“”t
		$max = -1;
		$nameList = "";
		if($turns[0] != "") {
			for($k = 0; $k < count($turns) - 1; $k++) {
				$nameList .= "[{$turns[$k]}] ";
				$max = $k;
			}
		}
		if($max != -1) {
			$prizeList .= "<img src=\"prize0.gif\" alt=\"$nameList\" title=\"$nameList\" width=\"16\" height=\"16\"> ";
		}
		// Ü
		$f = 1;
		for($k = 1; $k < count($init->prizeName); $k++) {
			if($flags & $f) {
				$prizeList .= "<img src=\"prize{$k}.gif\" alt=\"{$init->prizeName[$k]}\" title=\"{$init->prizeName[$k]}\" width=\"16\" height=\"16\"> ";
			}
			$f = $f << 1;
		}
		// “|‚µ‚½‰öbƒŠƒXƒg
		$f = 1;
		$max = -1;
		$nameList = "";
		for($k = 0; $k < $init->monsterNumber; $k++) {
			if($monsters & $f) {
				$nameList .= "[{$init->monsterName[$k]}] ";
				$max = $k;
			}
			$f = $f << 1;
		}
		if($max != -1) {
			$prizeList .= "<img src=\"monster{$max}.gif\" alt=\"{$nameList}\" title=\"{$nameList}\" width=\"16\" height=\"16\"> ";
		}
		return $prizeList;
	}
	//---------------------------------------------------
	// ’nŒ`‚ÉŠÖ‚·‚éƒf[ƒ^¶¬
	//---------------------------------------------------
	function landString($l, $lv, $x, $y, $mode, $comStr) {
		global $init;
		
		$point = "({$x},{$y})";
		$naviExp = "''";
		
		if($x < $init->islandSize / 2) {
			$naviPos = 0;
		} else {
			$naviPos = 1;
		}
		switch($l) {
			case $init->landSea:
				if($lv == 0) {
					// ŠC
					$image = 'land0.gif';
					$naviTitle = 'ŠC';
				} elseif($lv == 1) {
					// ó£
					$image = 'land14.gif';
					$naviTitle = 'ó£';
				} else {
					// à•ó
					$image = 'land17.gif';
					$naviTitle = 'ŠC';
				}
				break;
				
			case $init->landSeaCity:
				// ŠC’ê“ss
				$image = 'SeaCity.gif';
				$naviTitle = 'ŠC’ê“ss';
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landFroCity:
				// ŠCã“ss
				$image = 'FroCity.gif';
				$naviTitle = 'ŠCã“ss';
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landPort:
				// `
				$image = 'port.gif';
				$naviTitle = '`';
				break;
				
			case $init->landShip:
				// ‘D”•
				$ship = Util::navyUnpack($lv);
				$owner = $this->idToName[$ship[0]]; // Š‘®
				$naviTitle = "{$init->shipName[$ship[1]]}"; // ‘D”•‚Ìí—Ş
				$hp = round(100 - $ship[2] / $init->shipHP[$ship[1]] * 100); // ”j‘¹—¦
				if($ship[1] <= 1) {
					// —A‘—‘DA‹™‘D
					$naviText = "{$owner}“‡Š‘®";
				} elseif($ship[1] == 2) {
					// ŠC’ê’Tõ‘D
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
					if($treasure > 0) {
						$naviText = "{$owner}“‡Š‘®<br>”j‘¹—¦F{$hp}%<br>{$treasure}‰­‰~‘Š“–‚Ìà•óÏÚ";
					} else {
						$naviText = "{$owner}“‡Š‘®";
					}
				} elseif($ship[1] < 10) {
					$naviText = "{$owner}“‡Š‘®<br>”j‘¹—¦F{$hp}%";
				} else {
					// ŠC‘¯‘D
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
					$naviText = "”j‘¹—¦F{$hp}%";
				}
				$image = "ship{$ship[1]}.gif"; // ‘D”•‰æ‘œ
				break;
				
			case $init->landRail:
				// ü˜H
				$image = "rail{$lv}.gif";
				$naviTitle = 'ü˜H';
				break;
				
			case $init->landStat:
				// ‰w
				$image = 'stat.gif';
				$naviTitle = '‰w';
				break;
				
			case $init->landTrain:
				// “dÔ
				$image = "train{$lv}.gif";
				$naviTitle = '“dÔ';
				break;
				
			case $init->landZorasu:
				// ŠC‰öb
				$image = 'zorasu.gif';
				$naviTitle = '‚¼‚ç‚·';
				break;
				
			case $init->landSeaSide:
				// ŠCŠİ
				$image = 'sunahama.gif';
				$naviTitle = '»•l';
				break;
				
			case $init->landSeaResort:
				// ŠC‚Ì‰Æ
				if($lv < 30) {
					$image = 'umi1.gif';
					$naviTitle = 'ŠC‚Ì‰Æ';
				} else if($lv < 100) {
					$image = 'umi2.gif';
					$naviTitle = '–¯h';
				} else {
					$image = 'umi3.gif';
					$naviTitle = 'ƒŠƒ][ƒgƒzƒeƒ‹';
				}
				$naviText = "û“ü:{$lv}{$init->unitPop} <br>";
				break;
				
			case $init->landSoccer:
				// ƒXƒ^ƒWƒAƒ€
				$image = 'stadium.gif';
				$naviTitle = 'ƒXƒ^ƒWƒAƒ€';
				break;
				
			case $init->landPark:
				// —V‰€’n
				$image = "park{$lv}.gif";
				$naviTitle = '—V‰€’n';
				break;
				
			case $init->landFusya:
				// •—Ô
				$image = 'fusya.gif';
				$naviTitle = '•—Ô';
				break;
				
			case $init->landSyoubou:
				// Á–h
				$image = 'syoubou.gif';
				$naviTitle = 'Á–h';
				break;
				
			case $init->landSsyoubou:
				// ŠC’êÁ–h
				$image = 'syoubou2.gif';
				$naviTitle = 'ŠC’êÁ–h';
				break;
				
			case $init->landNursery:
				// —{Bê
				$image = 'Nursery.gif';
				$naviTitle = '—{Bê';
				$naviText = "{$lv}0{$init->unitPop}‹K–Í";
				break;
				
			case $init->landWaste:
				// r’n
				if($lv == 1) {
					$image = 'land13.gif'; // ’…’e“_
				} else {
					$image = 'land1.gif';
				}
				$naviTitle = 'r’n';
				break;
				
			case $init->landPlains:
				// •½’n
				$image = 'land2.gif';
				$naviTitle = '•½’n';
				break;
				
			case $init->landPoll:
				// ‰˜õ“yë
				$image = 'poll.gif';
				$naviTitle = '‰˜õ“yë';
				$naviText = "‰˜õƒŒƒxƒ‹{$lv}";
				break;
				
			case $init->landForest:
				// X
				if($mode == 1) {
					$image = 'land6.gif';
					$naviText= "${lv}{$init->unitTree}";
				} else {
					// ŠÏŒõÒ‚Ìê‡‚Í–Ø‚Ì–{”‰B‚·
					$image = 'land6.gif';
				}
				$naviTitle = 'X';
				break;
				
			case $init->landTown:
				// ’¬
				$p; $n;
				if($lv < 30) {
					$p = 3;
					$naviTitle = '‘º';
				} else if($lv < 100) {
					$p = 4;
					$naviTitle = '’¬';
				} else if($lv < 200) {
					$p = 5;
					$naviTitle = '“ss';
				} else {
					$p = 52;
					$naviTitle = '‘å“ss';
				}
				$image = "land{$p}.gif";
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landProcity:
				// –hĞ“ss
				if($lv < 110) {
					$naviTitle = '–hĞ“ssƒ‰ƒ“ƒN‚d';
				} else if($lv < 130) {
					$naviTitle = '–hĞ“ssƒ‰ƒ“ƒN‚c';
				} else if($lv < 160) {
					$naviTitle = '–hĞ“ssƒ‰ƒ“ƒN‚b';
				} else if($lv < 200) {
					$naviTitle = '–hĞ“ssƒ‰ƒ“ƒN‚a';
				} else {
					$naviTitle = '–hĞ“ssƒ‰ƒ“ƒN‚`';
				}
				$image = "bousai.gif";
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landNewtown:
				// ƒjƒ…[ƒ^ƒEƒ“
				$level = Util::expToLevel($l, $lv);
				$nwork = (int)($lv/15);
				$image = 'new.gif';
				$naviTitle = 'ƒjƒ…[ƒ^ƒEƒ“';
				$naviText = "{$lv}{$init->unitPop}/Eê{$nwork}0{$init->unitPop}";
				break;
				
			case $init->landBigtown:
				// Œ»‘ã“ss
				$level = Util::expToLevel($l, $lv);
				$mwork = (int)($lv/20);
				$lwork = (int)($lv/30);
				$image = 'big.gif';
				$naviTitle = 'Œ»‘ã“ss';
				$naviText = "{$lv}{$init->unitPop}/Eê{$lwork}0{$init->unitPop}/”_ê{$mwork}0{$init->unitPop}";
				break;
				
			case $init->landFarm:
				// ”_ê
				$image = 'land7.gif';
				$naviTitle = '”_ê';
				$naviText = "{$lv}0{$init->unitPop}‹K–Í";
				if($lv > 25) {
				// ƒh[ƒ€Œ^”_ê
				$image = 'land71.gif';
				$naviTitle = 'ƒh[ƒ€Œ^”_ê';
				}
				break;
				
			case $init->landSfarm:
				// ŠC’ê”_ê
				$image = 'land72.gif';
				$naviTitle = 'ŠC’ê”_ê';
				$naviText = "{$lv}0{$init->unitPop}‹K–Í";
				break;
				
			case $init->landFactory:
				// Hê
				$image = 'land8.gif';
				$naviTitle = 'Hê';
				$naviText = "{$lv}0{$init->unitPop}‹K–Í";
				if($lv > 100) {
				// ‘åHê
				$image = 'land82.gif';
				$naviTitle = '‘åHê';
				}
				break;
				
			case $init->landCommerce:
				// ¤‹Æƒrƒ‹
				$image = 'commerce.gif';
				$naviTitle = '¤‹Æƒrƒ‹';
				$naviText = "{$lv}0{$init->unitPop}‹K–Í";
				if($lv > 150) {
				// –{Ğƒrƒ‹
				$image = 'commerce2.gif';
				$naviTitle = '–{Ğƒrƒ‹';
				}
				break;
				
			case $init->landHatuden:
				// ”­“dŠ
				$image = 'hatuden.gif';
				$naviTitle = '”­“dŠ';
				$naviText = "{$lv}000kw";
				if($lv > 100) {
				// ‘åŒ^”­“dŠ
				$image = 'hatuden2.gif';
				$naviTitle = '‘åŒ^”­“dŠ';
				}
				break;
				
			case $init->landBank:
				// ‹âs
				$image = 'bank.gif';
				$naviTitle = '‹âs';
					break;
					
			case $init->landBase:
				if($mode == 0 || $mode == 2) {
					// ŠÏŒõÒ‚Ìê‡‚ÍX‚Ì‚Ó‚è
					$image = 'land6.gif';
					$naviTitle = 'X';
				} else {
					// ƒ~ƒTƒCƒ‹Šî’n
					$level = Util::expToLevel($l, $lv);
					$image = 'land9.gif';
					$naviTitle = 'ƒ~ƒTƒCƒ‹Šî’n';
					$naviText = "ƒŒƒxƒ‹ ${level} / ŒoŒ±’l {$lv}";
				}
				break;
				
			case $init->landSbase:
				// ŠC’êŠî’n
				if($mode == 0 || $mode == 2) {
					// ŠÏŒõÒ‚Ìê‡‚ÍŠC‚Ì‚Ó‚è
					$image = 'land0.gif';
					$naviTitle = 'ŠC';
				} else {
					$level = Util::expToLevel($l, $lv);
					$image = 'land12.gif';
					$naviTitle = 'ŠC’êŠî’n';
					$naviText = "ƒŒƒxƒ‹ ${level} / ŒoŒ±’l {$lv}";
				}
				break;
				
			case $init->landDefence:
				// –h‰q{İ
				if($mode == 0 || $mode == 2) {
					$image = 'land10.gif';
					$naviTitle = '–h‰q{İ';
				} else {
					$image = 'land10.gif';
					$naviTitle = '–h‰q{İ';
					$naviText = "‘Ï‹v—Í {$lv}";
				}
				break;
				
			case $init->landHaribote:
				// ƒnƒŠƒ{ƒe
				$image = 'land10.gif';
				if($mode == 0 || $mode == 2) {
					// ŠÏŒõÒ‚Ìê‡‚Í–h‰q{İ‚Ì‚Ó‚è
					$naviTitle = '–h‰q{İ';
				} else {
					$naviTitle = 'ƒnƒŠƒ{ƒe';
				}
				break;
				
			case $init->landSdefence:
				// ŠC’ê–h‰q{İ
				if($mode == 0 || $mode == 2) {
					$image = 'land102.gif';
					$naviTitle = 'ŠC’ê–h‰q{İ';
				} else {
					$image = 'land102.gif';
					$naviTitle = 'ŠC’ê–h‰q{İ';
					$naviText = "‘Ï‹v—Í {$lv}";
				}
				break;
				
			case $init->landOil:
				// ŠC’ê–û“c
				$image = 'land16.gif';
				$naviTitle = 'ŠC’ê–û“c';
				break;
				
			case $init->landMountain:
				// R
				if($lv > 0) {
					$image = 'land15.gif';
					$naviTitle = 'ÌŒ@ê';
					$naviText = "{$lv}0{$init->unitPop}‹K–Í";
				} else {
					$image = 'land11.gif';
					$naviTitle = 'R';
				}
				break;
				
			case $init->landMyhome:
				// ©‘î
				$image = "home{$lv}.gif";
				$naviTitle = 'ƒ}ƒCƒz[ƒ€';
				$naviText = "{$lv}l‰Æ‘°";
				break;
				
			case $init->landSoukoM:
				$flagm = 1;
			case $init->landSoukoF:
				// ‘qŒÉ
				if($flagm == 1) {
					$naviTitle = '‹àŒÉ';
				} else {
					$naviTitle = 'H—¿ŒÉ';
				}
				$image = "souko.gif";
				$sec = (int)($lv / 100);
				$tyo = $lv % 100;
				if($l == $init->landSoukoM) {
					if($tyo == 0) {
						$naviText = "ƒZƒLƒ…ƒŠƒeƒBF{$sec}A’™‹àF‚È‚µ";
					} else {
						$naviText = "ƒZƒLƒ…ƒŠƒeƒBF{$sec}A’™‹àF{$tyo}000{$init->unitMoney}";
					}
				} else {
					if($tyo == 0) {
						$naviText = "ƒZƒLƒ…ƒŠƒeƒBF{$sec}A’™HF‚È‚µ";
					} else {
						$naviText = "ƒZƒLƒ…ƒŠƒeƒBF{$sec}A’™HF{$tyo}000{$init->unitFood}";
					}
				}
				break;
				
			case $init->landMonument:
				// ‹L”O”è
				$image = "monument{$lv}.gif";
				$naviTitle = '‹L”O”è';
				$naviText = $init->monumentName[$lv];
				break;
				
			case $init->landMonster:
			case $init->landSleeper:
				// ‰öb
				$monsSpec = Util::monsterSpec($lv);
				$spec = $monsSpec['kind'];
				$special = $init->monsterSpecial[$spec];
				$image = "monster{$spec}.gif";
				if($l == $init->landSleeper) {
					$naviTitle = '‰öbi‡–°’†j';
				} else {
					$naviTitle = '‰öb';
				}
				
				// d‰»’†?
				if((($special & 0x4) && (($this->islandTurn % 2) == 1)) ||
					 (($special & 0x10) && (($this->islandTurn % 2) == 0))) {
					// d‰»’†
					$image = $init->monsterImage[$monsSpec['kind']];
				}
				$naviText = "‰öb{$monsSpec['name']}(‘Ì—Í{$monsSpec['hp']})";
		}
		if($mode == 1 || $mode == 2) {
			print "<a href=\"javascript: void(0);\" onclick=\"ps($x,$y)\">";
			$naviText = "{$comStr}\\n{$naviText}";
		}
		print "<img src=\"{$image}\" width=\"32\" height=\"32\" alt=\"{$point} {$naviTitle} {$comStr}\" onMouseOver=\"Navi({$naviPos},'{$image}', '{$naviTitle}', '{$point}', '{$naviText}', {$naviExp});\" onMouseOut=\"NaviClose(); return false\">";
		
		// À•Wİ’è•Â‚¶
		if($mode == 1 || $mode == 2) {
			print "</a>";
		}
	}
}

//--------------------------------------------------------------------
class Main {

	function execute() {
		$hako = new Hako;
		$cgi = new Cgi;
		
		$cgi->parseInputData();
		$cgi->getCookies();
		
		if(!$hako->readIslands($cgi)) {
			HTML::header($cgi->dataSet);
			Error::noDataFile();
			HTML::footer();
			Util::unlock($lock);
			exit();
		}
		$lock = Util::lock($fp);
		if(FALSE == $lock) {
			exit;
		}
		$cgi->setCookies();
		$cgi->lastModified();

		if($cgi->dataSet['DEVELOPEMODE'] == "java") {
			$html = new HtmlJS;
			$com = new MakeJS;
		} else {
			$html = new HtmlMap;
			$com = new Make;
		}
		switch($cgi->mode) {
			case "turn":
				$turn = new Turn;
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$turn->turnMain($hako, $cgi->dataSet); 
				$html->main($hako, $cgi->dataSet); // ƒ^[ƒ“ˆ—ŒãATOPƒy[ƒWopen
				$html->footer();
				break;
				
			case "owner":
				$html->header($cgi->dataSet);
				$html->owner($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "command":
				$html->header($cgi->dataSet);
				$com->commandMain($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "new":
				$html->header($cgi->dataSet);
				$com->newIsland($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "comment":
				$html->header($cgi->dataSet);
				$com->commentMain($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "print":
				$html->header($cgi->dataSet);
				$html->visitor($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "targetView":
				$html->header($cgi->dataSet);
				$html->printTarget($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "change":
				$html->header($cgi->dataSet);
				$com->changeMain($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "ChangeOwnerName":
				$html->header($cgi->dataSet);
				$com->changeOwnerName($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "lbbs":
				$lbbs = new Make;
				$html->header($cgi->dataSet);
				$lbbs->localBbsMain($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "skin":
				$html = new HtmlSetted;
				$html->header($cgi->dataSet);
				$html->setSkin();
				$html->footer();
				break;
			case "imgset":
				$html = new HtmlSetted;
				$html->header($cgi->dataSet);
				$html->setImg();
				$html->footer();
				break;
			case "conf":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$html->regist($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "log":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$html->log();
				$html->footer();
				break;
				
			default: 
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$html->main($hako, $cgi->dataSet);
				$html->footer();
		}
		Util::unlock($lock);
		exit();
	}
}

$start = new Main;
$start->execute();

?>
