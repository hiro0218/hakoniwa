<?php
/**
 * 箱庭諸島 S.E - ログ出力用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */
//hako-turn.php

class LogIO {
    public $this_file = '';

	private $logPool       = array();
	private $secretLogPool = array();
	private $lateLogPool   = array();


	public function __construct() {
		global $init;
		$this->this_file = $init->baseDir . "/hako-main.php";
	}

	//---------------------------------------------------
	// ログファイルを後ろにずらす
	//---------------------------------------------------
	function slideBackLogFile() {
		global $init;
		for($i = $init->logMax - 1; $i >= 0; $i--) {
			$j = $i + 1;
			$s = "{$init->dirName}/hakojima.log{$i}";
			$d = "{$init->dirName}/hakojima.log{$j}";
			if(is_file($s)) {
				if(is_file($d)) {
					unlink($d);
				}
				rename($s, $d);
			}
		}
	}
	//---------------------------------------------------
	// 最近の出来事を出力
	//---------------------------------------------------
	static function logFilePrint($num = 0, $id = 0, $mode = 0) {
		global $init;
		$fileName = $init->dirName . "/hakojima.log" . $num;
		if(!is_file($fileName)) {
			return;
		}
		$fp = fopen($fileName, "r");
		$row = 1;

		echo "<div>";
		while($line = chop(fgets($fp, READ_LINE))) {
			list($m, $turn, $id1, $id2, $message) = explode(",", $line, 5);
			if($m == 1) {
				if(($mode == 0) || ($id1 != $id)) {
					continue;
				}
				$m = "<strong>(機密)</strong>";
			} else {
				$m = "";
			}
			if($id != 0) {
				if(($id != $id1) && ($id != $id2)) {
					continue;
				}
			}
			if($row == 1) {
				echo "<h2>{$init->tagNumber_}【ターン{$turn}の出来事】{$init->_tagNumber}</h2>\n";
				$row++;
			}
			echo "<ul class='list-unstyled'>";
			echo "<li>{$message}</li>\n";
			echo "</ul>";
		}
		echo "</div>";

		fclose($fp);
	}
	//---------------------------------------------------
	// 発見の記録を出力
	//---------------------------------------------------
	static function historyPrint() {
		global $init;
		$fileName = $init->dirName . "/hakojima.his";
		if(!is_file($fileName)) {
			return;
		}
		$fp = fopen($fileName, "r");
		$history = array();
		$k = 0;
		while($line = chop(fgets($fp, READ_LINE))) {
			array_push($history, $line);
			$k++;
		}
		echo "<ul>";
		for($i = 0; $i < $k; $i++) {
			list($turn, $his) = explode(",", array_pop($history), 2);
			echo "<li>{$init->tagNumber_}ターン{$turn}{$init->_tagNumber}：$his</li>\n";
		}
		echo "</ul>";

	}
	//---------------------------------------------------
	// 発見の記録を保存
	//---------------------------------------------------
	function history($str) {
		global $init;

		$fileName = "{$init->dirName}/hakojima.his";

		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "a");
		fputs($fp, "{$GLOBALS['ISLAND_TURN']},{$str}\n");
		fclose($fp);
		// chmod($fileName, 0666);
	}
	//---------------------------------------------------
	// 発見の記録ログ調整
	//---------------------------------------------------
	function historyTrim() {
		global $init;

		$count = 0;
		$fileName = "{$init->dirName}/hakojima.his";
		if(is_file($fileName)) {
			$fp = fopen($fileName, "r");

			$line = array();
			while($l = chop(fgets($fp, READ_LINE))) {
				array_push($line, $l);
				$count++;
			}
			fclose($fp);
			if($count > $init->historyMax) {
				if(!is_file($fileName)) {
					touch($fileName);
				}
				$fp = fopen($fileName, "w");
				for($i = ($count - $init->historyMax); $i < $count; $i++) {
					fputs($fp, "{$line[$i]}\n");
				}
				fclose($fp);
				// chmod($fileName, 0666);
			}
		}
	}
	//---------------------------------------------------
	// ログ
	//---------------------------------------------------
	function out($str, $id = "", $tid = "") {
		array_push($this->logPool, "0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// 機密ログ
	//---------------------------------------------------
	function secret($str, $id = "", $tid = "") {
		array_push($this->secretLogPool,"1,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// 遅延ログ
	//---------------------------------------------------
	function late($str, $id = "", $tid = "") {
		array_push($this->lateLogPool,"0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// ログ書き出し
	//---------------------------------------------------
	function flush() {
		global $init;

		$fileName = "{$init->dirName}/hakojima.log0";

		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "w");

		// 全部逆順にして書き出す
		if(!empty($this->secretLogPool)) {
			for($i = count($this->secretLogPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->secretLogPool[$i]}\n");
			}
		}
		if(!empty($this->lateLogPool)) {
			for($i = count($this->lateLogPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->lateLogPool[$i]}\n");
			}
		}
		if(!empty($this->logPool)) {
			for($i = count($this->logPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->logPool[$i]}\n");
			}
		}
		fclose($fp);
		// chmod($fileName, 0666);
	}
	//---------------------------------------------------
	// お知らせを出力
	//---------------------------------------------------
	static function infoPrint() {
		global $init;

		if($init->infoFile == "") {
			return;
		}

		$fileName = "{$init->infoFile}";
		if(!is_file($fileName)) {
			return;
		}

		$fp = fopen($fileName, "r");
		while($line = fgets($fp, READ_LINE)) {
			$line = chop($line);
			echo "{$line}<br>\n";
		}
		fclose($fp);

	}
}

//--------------------------------------------------------------------
class Log extends LogIO {

	function discover($id, $name) {
		global $init;
		$this->history("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が発見される。");
	}
	function changeName($name1, $name2) {
		global $init;
		//$this->history("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name1}島</A>{$init->_tagName}、名称を<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name2}島</A>{$init->_tagName}に変更する。");
		$this->history("{$init->tagName_}{$name1}島{$init->_tagName}、名称を{$init->tagName_}{$name2}島{$init->_tagName}に変更する。");
	}
	// 資金をプレゼント
	function presentMoney($id, $name, $value) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}に、{$init->nameFunds}<strong>{$value}{$init->unitMoney}</strong>をプレゼントしました。", $id);
	}
	// 食料をプレゼント
	function presentFood($id, $name, $value) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}に、{$init->nameFood}<strong>{$value}{$init->unitFood}</strong>をプレゼントしました。", $id);
	}
	// 受賞
	function prize($id, $name, $pName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<strong>$pName</strong>を受賞しました。",$id);
		$this->history("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}、<strong>$pName</strong>を受賞");
	}
	// 死滅
	function dead($id, $name) {
		global $init;
		$this->out("{$init->tagName_}${name}島{$init->_tagName}から人がいなくなり、<strong>滅亡</strong>しました。", $id);
		$this->history("{$init->tagName_}${name}島{$init->_tagName}、人がいなくなり<strong>滅亡</strong>する。");
	}
	// 島の強制削除
	function deleteIsland($id, $name) {
		global $init;
		$this->history("{$init->tagName_}{$name}島{$init->_tagName}に、箱庭大明神の<strong>天罰が降り</strong><span class=attention>海の中に没し</span>ました。");
	}
	function doNothing($id, $name, $comName) {
		//global $init;
		//$this->out("{$init->tagName_}{$name}島{$init->_tagName}で{$init->tagComName_}${comName}{$init->_tagComName}が行われました。",$id);
	}
	// 資金足りない
	function noMoney($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、資金不足のため中止されました。",$id);
	}
	// 食料足りない
	function noFood($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、備蓄食料不足のため中止されました。",$id);
	}
	// 木材足りない
	function noWood($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、木材不足のため中止されました。",$id);
	}
	// 衛星足りない
	function NoAny($id, $name, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、{$str}ため中止されました。",$id);
	}
	// 対象地形の種類による失敗
	function landFail($id, $name, $comName, $kind, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}が<strong>{$kind}</strong>だったため中止されました。",$id);
	}
	// 対象地形の条件による失敗
	function JoFail($id, $name, $comName, $kind, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}が条件を満たしていない<strong>{$kind}</strong>だったため中止されました。",$id);
	}
	// 都市の種類による失敗
	function BokuFail($id, $name, $comName, $kind, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}が条件を満たした都市でなかったため中止されました。",$id);
	}
	// 周りに町がなくて失敗
	function NoTownAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に{$init->namePopulation}がいなかったため中止されました。",$id);
	}
	// 成功
	function landSuc($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
	}
	// 倉庫関係
	function Souko($id, $name, $comName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}<strong>{$str}</strong>が行われました。",$id);
	}
	// 倉庫関係
	function SoukoMax($id, $name, $comName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の<strong>{$str}</strong>ため中止されました。",$id);
	}
	// 倉庫関係
	function SoukoLupin($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>へ{$init->tagDisaster_}大怪盗が侵入したようです！！{$init->_tagDisaster}",$id);
	}
	// 整地系ログまとめ
	function landSucMatome($id, $name, $comName, $point) {
		global $init;
		$this->out("<strong>⇒</strong> {$init->tagName_}{$point}{$init->_tagName}",$id);
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
	}
	// 埋蔵金
	function maizo($id, $name, $comName, $value) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>{$value}{$init->unitMoney}もの埋蔵金</strong>が発見されました。",$id);
	}
	function noLandAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に陸地がなかったため中止されました。",$id);
	}
	// 卵発見
	function EggFound($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>何かの卵</strong>を発見しました。",$id);
	}
	// 卵孵化
	function EggBomb($id, $name, $mName, $point, $lName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の{$lName}から<strong>怪獣{$mName}</strong>が生まれました。",$id);
	}
	// お土産
	function Miyage($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}側のお土産屋さん</strong>から<strong>{$value}{$str}</strong>もの収入がありました。",$id);
	}
	// 収穫
	function Syukaku($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>がもたらした豊作により、さらに<strong>{$str}</strong>もの{$init->nameFood}が収穫されました。",$id);
	}
	// 銀行化
	function Bank($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が銀行になりました。",$id);
	}
	// 衛星打ち上げ成功
	function Eiseisuc($id, $name, $kind, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で{$init->tagComName_}{$kind}{$str}{$init->_tagComName}に成功しました。",$id);
	}
	// 衛星撃沈
	function Eiseifail($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われましたが打ち上げは{$init->tagDisaster_}失敗{$init->_tagDisaster}したようです。",$id);
	}
	// 衛星破壊成功
	function EiseiAtts($id, $tId, $name, $tName, $comName, $tEiseiname) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島{$init->_tagName}</A>が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$tEiseiname}</strong>に命中。<strong>$tEiseiname</strong>は跡形もなく消し飛びました。",$id, $tId);
	}
	// 衛星破壊失敗
	function EiseiAttf($id, $tId, $name, $tName, $comName, $tEiseiname) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}の<strong>{$tEiseiname}</strong>に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、何にも命中せず宇宙の彼方へと飛び去ってしまいました。",$id, $tId);
	}
	// 衛星レーザー
	function EiseiLzr($id, $tId, $name, $tName, $comName, $tLname, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島{$init->_tagName}</A>が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$point}{$init->_tagName}に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$tLname}</strong>に命中。一帯が{$str}",$id, $tId);
	}
	// 油田発見
	function oilFound($id, $name, $point, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}で<strong>{$str}</strong>の予算をつぎ込んだ{$init->tagComName_}{$comName}{$init->_tagComName}が行われ、<strong>油田が掘り当てられました</strong>。",$id);
	}
	// 油田発見ならず
	function oilFail($id, $name, $point, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}で<strong>{$str}</strong>の予算をつぎ込んだ{$init->tagComName_}{$comName}{$init->_tagComName}が行われましたが、油田は見つかりませんでした。",$id);
	}
	// 防衛施設、自爆セット
	function bombSet($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>の<strong>自爆装置がセット</strong>されました。",$id);
	}
	// 防衛施設、自爆作動
	function bombFire($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>、{$init->tagDisaster_}自爆装置作動！！{$init->_tagDisaster}",$id);
	}
	// メルトダウン発生
	function CrushElector($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>で、{$init->tagDisaster_}メルトダウン発生！！{$init->_tagDisaster}一帯が水没しました。",$id);
	}
	// 停電発生
	function Teiden($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で、{$init->tagDisaster_}停電発生！！{$init->_tagDisaster}",$id);
	}
	// 日照り発生
	function Hideri($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で、{$init->tagDisaster_}日照りが続き{$init->_tagDisaster}、都市部の{$init->namePopulation}が減少しました。",$id);
	}
	// にわか雨発生
	function Niwakaame($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で、{$init->tagDisaster_}にわか雨{$init->_tagDisaster}が降り、森が潤いました。",$id);
	}
	// 植林orミサイル基地
	function PBSuc($id, $name, $comName, $point) {
		global $init;
		$this->secret("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
		$this->out("こころなしか、{$init->tagName_}{$name}島{$init->_tagName}の<strong>森</strong>が増えたようです。",$id);
	}
	// ハリボテ
	function hariSuc($id, $name, $comName, $comName2, $point) {
		global $init;
		$this->secret("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
		$this->landSuc($id, $name, $comName2, $point);
	}
	// 記念碑、発射
	function monFly($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が<strong>轟音とともに飛び立ちました</strong>。",$id);
	}
	// 実行許可ターン
	function Forbidden($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、実行が許可されませんでした。",$id);
	}
	// 管理人預かり中のため許可されない
	function CheckKP($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、目標の島が管理人預かり中のため実行が許可されませんでした。",$id);
	}
	// 電力不足
	function Enehusoku($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、電力不足のため中止されました。",$id);
	}
	// ミサイル撃とうとしたが天気が悪い
	function msNoTenki($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、悪天候のため中止されました。",$id);
	}
	// ミサイル撃とうとした(or 怪獣派遣しようとした)がターゲットがいない
	function msNoTarget($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、目標の島に人が見当たらないため中止されました。",$id);
	}
	// ミサイル撃とうとしたが基地がない
	function msNoBase($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>ミサイル設備を保有していない</strong>ために実行できませんでした。",$id);
	}
	// ミサイル撃とうとしたが最大発射数を超えた
	function msMaxOver($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>最大発射数を超えた</strong>ために実行できませんでした。",$id);
	}
	// ステルスミサイルログ
	function mslogS($id, $tId, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE) {
		global $init;
		$missileBE = $missileB + $missileE;
		$missileH = $missiles - $missileA - $missileC - $missileBE;
		$this->secret("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$missiles}発{$init->_tagComName}の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。(有効{$missileH}発/怪獣命中{$missileD}発/怪獣無効{$missileC}発/防衛{$missileBE}発/無効{$missileA}発)",$id, $tId);
		$this->late("<strong>何者か</strong>が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$missiles}発{$init->_tagComName}の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。(有効{$missileH}発/怪獣命中{$missileD}発/怪獣無効{$missileC}発/防衛{$missileBE}発/無効{$missileA}発)",$tId);
	}
	// その他ミサイルログ
	function mslog($id, $tId, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE) {
		global $init;
		$missileBE = $missileB + $missileE;
		$missileH = $missiles - $missileA - $missileC - $missileBE;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$missiles}発{$init->_tagComName}の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。(有効{$missileH}発/怪獣命中{$missileD}発/怪獣無効{$missileC}発/防衛{$missileBE}発/無効{$missileA}発)",$id, $tId);
	}
	// 陸地破壊弾、山に命中
	function msLDMountain($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に命中。<strong>{$tLname}</strong>は消し飛び、荒地と化しました。",$id, $tId);
	}
	// 陸地破壊弾、海底基地に命中
	function msLDSbase($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}に着水後爆発、同地点にあった<strong>{$tLname}</strong>は跡形もなく吹き飛びました。",$id, $tId);
	}
	// 陸地破壊弾、怪獣に命中
	function msLDMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}に着弾し爆発。陸地は<strong>怪獣{$tLname}</strong>もろとも水没しました。",$id, $tId);
	}
	// 陸地破壊弾、浅瀬に命中
	function msLDSea1($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に着弾。海底がえぐられました。",$id, $tId);
	}
	// 陸地破壊弾、その他の地形に命中
	function msLDLand($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に着弾。陸地は水没しました。",$id, $tId);
	}
	// 地形隆起弾、海底基地に命中
	function msLUSbase($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}に着水後爆発、同地点にあった<strong>{$tLname}</strong>は浅瀬に埋まりました。",$id, $tId);
	}
	// 地形隆起弾、深い海に命中
	function msLUSea0($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に着水。海底が隆起し浅瀬となりました。",$id, $tId);
	}
	// 地形隆起弾、浅瀬に命中
	function msLUSea1($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に着弾。海底が隆起し荒地となりました。",$id, $tId);
	}
	// 地形隆起弾、怪獣に命中
	function msLUMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}に着弾。陸地は隆起し山となり、<strong>怪獣{$tLname}</strong>は生埋めとなりました。",$id, $tId);
	}
	// 地形隆起弾、その他の地形に命中
	function msLULand($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に着弾。陸地は隆起し山となりました。",$id, $tId);
	}
	// バイオミサイル着弾、汚染
	function msPollution($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に着弾。一帯が汚染されました。",$id, $tId);
	}
	// ステルスミサイル、怪獣に命中、硬化中にて無傷
	function msMonNoDamageS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中、しかし硬化状態だったため効果がありませんでした。",$id, $tId);
		$this->out("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中、しかし硬化状態だったため効果がありませんでした。",$tId);
	}
	// 通常ミサイル、怪獣に命中、硬化中にて無傷
	function msMonNoDamage($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中、しかし硬化状態だったため効果がありませんでした。",$id, $tId);
	}
	// ステルスミサイル撃ったが怪獣に叩き落とされる
	function msMonsCaughtS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}の<strong>怪獣{$tLname}</strong>に叩き落とされました。",$id, $tId);
		$this->late("-{$tPoint}の<strong>怪獣{$tLname}</strong>に叩き落とされました。",$tId);
	}
	// 通常ミサイル撃ったが怪獣に叩き落とされる
	function msMonsCaught($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>怪獣{$tLname}</strong>に叩き落とされました。",$id, $tId);
	}
	// ステルスミサイル、怪獣に命中、殺傷
	function msMonsKillS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は力尽き、倒れました。",$id, $tId);
		$this->late("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は力尽き、倒れました。", $tId);
	}
	// 通常ミサイル、怪獣に命中、殺傷
	function msMonsKill($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は力尽き、倒れました。",$id, $tId);
	}
	// 怪獣の死体（ステルス）
	function msMonMoneyS($id, $tId, $tLname, $value) {
		global $init;
		$this->secret("-<strong>怪獣{$tLname}</strong>の残骸には、<strong>{$value}{$init->unitMoney}</strong>の値が付きました。",$id, $tId);
		$this->late("-<strong>怪獣{$tLname}</strong>の残骸には、<strong>{$value}{$init->unitMoney}</strong>の値が付きました。",$tId);
	}
	// 怪獣の死体（通常）
	function msMonMoney($id, $tId, $tLname, $value) {
		global $init;
		$this->out("-<strong>怪獣{$tLname}</strong>の残骸には、<strong>{$value}{$init->unitMoney}</strong>の値が付きました。",$id, $tId);
	}
	// ステルスミサイル、怪獣に命中、ダメージ
	function msMonsterS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は苦しそうに咆哮しました。",$id, $tId);
		$this->late("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は苦しそうに咆哮しました。",$tId);
	}
	// バイオミサイル、怪獣に命中、突然変異
	function msMutation($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>に突然変異が生じました。",$id, $tId);
	}
	// 催眠弾が怪獣に命中
	function MsSleeper($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$name}島</A>{$init->_tagName}の<strong>怪獣{$tLname}</strong>は催眠弾によって眠ってしまったようです。",$id, $tId);
	}
	// 睡眠中の怪獣にミサイル命中
	function MsWakeup($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で眠っていた<strong>怪獣{$tLname}</strong>にミサイルが命中、<strong>怪獣{$tLname}</strong>は目を覚ましました。",$id, $tId);
	}
	// 睡眠中の怪獣が目覚める
	function MonsWakeup($id, $name, $lName, $point, $mName) {
		global $init;
			$this->out("<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で眠っていた<strong>怪獣{$mName}</strong>は目を覚ましました。",$id);
	}
	// 通常ミサイル、怪獣に命中、ダメージ
	function msMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は苦しそうに咆哮しました。",$id, $tId);
	}
	// ステルスミサイル通常地形に命中
	function msNormalS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}の<strong>{$tLname}</strong>に命中、一帯が壊滅しました。",$id, $tId);
		$this->late("-{$tPoint}の<strong>{$tLname}</strong>に命中、一帯が壊滅しました。",$tId);
	}
	// 通常ミサイル通常地形に命中
	function msNormal($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に命中、一帯が壊滅しました。",$id, $tId);
	}
	// ステルスミサイル規模減少
	function msGensyoS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}の<strong>{$tLname}</strong>に命中、規模が減少しました。",$id, $tId);
		$this->late("-{$tPoint}の<strong>{$tLname}</strong>に命中、規模が減少しました。",$tId);
	}
	// 通常ミサイル規模減少
	function msGensyo($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に命中、規模が減少しました。",$id, $tId);
	}
	// 通常ミサイル防衛施設に命中
	function msDefence($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}の<strong>{$tLname}</strong>に命中しましたが被害はありませんでした。",$id, $tId);
	}
	// ステルスミサイル防衛施設に命中
	function msDefenceS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}の<strong>{$tLname}</strong>に命中しましたが被害はありませんでした。",$id, $tId);
	$this->late("-{$tPoint}の<strong>{$tLname}</strong>に命中しましたが被害はありませんでした。",$tId);
	}
	// ミサイル難民到着
	function msBoatPeople($id, $name, $achive) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}にどこからともなく<strong>{$achive}{$init->unitPop}もの難民</strong>が漂着しました。<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}は快く受け入れたようです。",$id);
	}
	// 怪獣派遣
	function monsSend($id, $tId, $name, $tName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<strong>人造怪獣</strong>を建造。<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}へ送りこみました。",$id, $tId);
	}
	// 衛星消滅？！
	function EiseiEnd($id, $name, $tEiseiname) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}の<strong>{$tEiseiname}</strong>は{$init->tagDisaster_}崩壊{$init->_tagDisaster}したようです！！",$id);
	}
	// 戦艦、怪獣に攻撃
	function SenkanMissile($id, $tId, $name, $tName, $lName, $point, $tPoint, $tmonsName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}<strong>{$lName}</strong>が多弾頭ミサイルを発射し、{$tPoint}の<strong>{$tmonsName}</strong>に命中しました。",$id, $tId);
	}
	// 怪獣あうち
	function BariaAttack($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>が強力な力場に押し潰されました。",$id);
	}
	// 怪獣輸送に失敗
	function MonsNoSleeper($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、睡眠中の怪獣がいなかったため中止されました。",$id);
	}
	// 怪獣輸送
	function monsSendSleeper($id, $tId, $name, $tName, $lName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で眠っていた<strong>怪獣{$lName}</strong>が、<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}へ送りこまれました。",$id, $tId);
	}
	// 輸出
	function sell($id, $name, $comName, $value, $unit) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<strong>{$value}{$unit}</strong>の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。",$id);
	}
	// 援助
	function aid($id, $tId, $name, $tName, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}へ<strong>{$str}</strong>の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。",$id, $tId);
	}
	// 誘致活動
	function propaganda($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
	}
	// 放棄
	function giveup($id, $name) {
		global $init;
		$this->out("{$init->tagName_}{$name}島{$init->_tagName}は放棄され、<strong>滅亡</strong>しました。",$id);
		$this->history("{$init->tagName_}{$name}島{$init->_tagName}、放棄され<strong>滅亡</strong>する。");
	}
	// 油田からの収入
	function oilMoney($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>から、<strong>{$str}</strong>の収益が上がりました。",$id);
	}
	// 油田枯渇
	function oilEnd($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は枯渇したようです。",$id);
	}
	// 宝くじ購入
	function buyLot($id, $name, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で<strong>{$str}</strong>分の{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
	}
	// 宝くじ完売
	function noLot($id, $name, $comName) {
		global $init;
		$this->out("<strong>宝くじ完売のため</strong>、<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}は、{$init->tagComName_}{$comName}{$init->_tagComName}が出来ませんでした。",$id);
	}
	// 宝くじ収入
	function LotteryMoney($id, $name, $str, $syo) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<strong>宝くじ{$syo}等賞</strong>に当選！<strong>{$str}</strong>の当選金を受け取りました。",$id);
	}
	// 遊園地からの収入
	function ParkMoney($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<B>{$lName}</B>から、<B>{$str}</B>の収益が上がりました。",$id);
	}
	// 遊園地のイベント
	function ParkEvent($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<B>{$lName}</B>でイベントが開催され、<B>{$str}</B>の{$init->nameFood}が消費されました。",$id);
	}
	// 遊園地のイベント増収
	function ParkEventLuck($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<B>{$lName}</B>で開催されたイベントが成功して<B>{$str}</B>の収益が上がりました。",$id);
	}
	// 遊園地のイベント減収
	function ParkEventLoss($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<B>{$lName}</B>で開催されたイベントが失敗して<B>{$str}</B>の損失がでました。",$id);
	}
	// 遊園地が閉園
	function ParkEnd($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<B>{$lName}</B>は施設が老朽化したため閉園となりました。",$id);
	}
	// 怪獣、防衛施設を踏む
	function monsMoveDefence($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<strong>怪獣{$mName}</strong>が<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>へ到達、<strong>{$lName}の自爆装置が作動！！</strong>",$id);
	}
	// 怪獣が自爆する
	function MonsExplosion($id, $name, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>が<strong>大爆発</strong>を起こしました！",$id);
	}
	// 怪獣分裂
	function monsBunretu($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>に<strong>怪獣{$mName}</strong>が分裂しました。",$id);
	}
	// 怪獣動く
	function monsMove($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が<strong>怪獣{$mName}</strong>に踏み荒らされました。",$id);
	}
	// ぞらす動く
	function ZorasuMove($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が<strong>ぞらす</strong>に破壊されました。",$id);
	}
	// 火災
	function fire($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が{$init->tagDisaster_}火災{$init->_tagDisaster}により壊滅しました。",$id);
	}
	// 火災未遂
	function firenot($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が{$init->tagDisaster_}火災{$init->_tagDisaster}により被害を受けました。",$id);
	}
	// 広域被害、海の建設
	function wideDamageSea2($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は跡形もなくなりました。",$id);
	}
	// 広域被害、怪獣水没
	function wideDamageMonsterSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の陸地は<strong>怪獣{$lName}</strong>もろとも水没しました。",$id);
	}
	// 広域被害、水没
	function wideDamageSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は<strong>水没</strong>しました。",$id);
	}
	// 広域被害、怪獣
	function wideDamageMonster($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$lName}</strong>は消し飛びました。",$id);
	}
	// 広域被害、荒地
	function wideDamageWaste($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は一瞬にして<strong>荒地</strong>と化しました。",$id);
	}
	// 地震発生
	function earthquake($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で大規模な{$init->tagDisaster_}地震{$init->_tagDisaster}が発生！！",$id);
	}
	// 地震被害
	function eQDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}地震{$init->_tagDisaster}により壊滅しました。",$id);
	}
	// 地震被害未遂
	function eQDamagenot($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}地震{$init->_tagDisaster}により被害を受けました。",$id);
	}
	// 飢餓
	function starve($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}の{$init->tagDisaster_}{$init->nameFood}が不足{$init->_tagDisaster}しています！！",$id);
	}
	// 暴動発生
	function pooriot($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で<strong>失業率悪化による</strong>{$init->tagDisaster_}暴動{$init->_tagDisaster}が発生！！",$id);
	}
	// 暴動被害（人口減）
	function riotDamage1($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>で{$init->tagDisaster_}暴動{$init->_tagDisaster}により死傷者が多数出た模様です。",$id);
	}
	// 暴動被害（壊滅）
	function riotDamage2($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が{$init->tagDisaster_}暴動{$init->_tagDisaster}により壊滅しました。",$id);
	}
	// 食料不足被害
	function svDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>に<strong>{$init->nameFood}を求めて住民が殺到</strong>。<strong>{$lName}</strong>は壊滅しました。",$id);
	}
	// 津波発生
	function tsunami($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}付近で{$init->tagDisaster_}津波{$init->_tagDisaster}発生！！",$id);
	}
	// 津波被害
	function tsunamiDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}津波{$init->_tagDisaster}により崩壊しました。",$id);
	}
	// 怪獣現る
	function monsCome($id, $name, $mName, $point, $lName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}に<strong>怪獣{$mName}</strong>出現！！{$init->tagName_}{$point}{$init->_tagName}の<strong>{$lName}</strong>が踏み荒らされました。",$id);
	}
	// 船派遣した
	function shipSend($id, $tId, $name, $sName, $point, $tName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>が{$point}{$init->_tagName}の<strong>{$sName}</strong>を<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}に{$init->tagComName_}派遣{$init->_tagComName}しました。",$id, $tId);
	}
	// 船帰還した
	function shipReturn($id, $tId, $name, $sName, $point, $tName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}{$point}の<strong>{$sName}</strong>を{$init->tagComName_}帰還{$init->_tagComName}させました。",$id, $tId);
	}
	// 財宝回収
	function RecoveryTreasure($id, $name, $sName, $value) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<strong>{$sName}</strong>が発見した<strong>{$value}億円相当</strong>の{$init->tagDisaster_}財宝{$init->_tagDisaster}を回収しました。",$id);
	}
	// 船失敗
	function shipFail($id, $name, $comName, $kind) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>{$kind}</strong>だったため中止されました。",$id, $tId);
	}
	// ぞらす現る
	function ZorasuCome($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}に<strong>ぞらす</strong>出現！！",$id);
	}
	// 怪獣呼ばれる
	function monsCall($id, $name, $mName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>が天に向かって咆哮しました！",$id);
	}
	// 怪獣ワープ
	function monsWarp($id, $tId, $name, $mName, $point, $tName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}にワープしました！",$id, $tId);
	}
	// 怪獣による資金増加
	function MonsMoney($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>が<strong>{$str}</strong>の金をばら撒きました。",$id);
	}
	// 怪獣による食料増加
	function MonsFood($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>が撒き散らした栄養たっぷり💩の影響で、{$init->nameFood}が<strong>{$str}</strong>増産されました。",$id);
	}
	// 怪獣による資金減少
	function MonsMoney2($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>によって、島の資金<strong>{$str}</strong>が強奪されました。",$id);
	}
	// 怪獣による食料減少
	function MonsFood2($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>怪獣{$mName}</strong>が撒き散らした悪臭漂う💩の影響で、{$init->nameFood}が<strong>{$str}</strong>腐敗しました。",$id);
	}
	// 地盤沈下発生
	function falldown($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で{$init->tagDisaster_}地盤沈下{$init->_tagDisaster}が発生しました！！",$id);
	}
	// 地盤沈下被害
	function falldownLand($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は海の中へ沈みました。",$id);
	}
	// 台風発生
	function typhoon($id, $name) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}に{$init->tagDisaster_}台風{$init->_tagDisaster}上陸！！",$id);
	}
	// 台風被害
	function typhoonDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}台風{$init->_tagDisaster}で飛ばされました。",$id);
	}
	// ストライキ
	function Sto($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>社員が{$init->tagDisaster_}ストライキ{$init->_tagDisaster}を起こし<strong>商業規模</strong>が減少した模様です。",$id);
	}
	// 隕石、その他
	function hugeMeteo($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点に{$init->tagDisaster_}巨大隕石{$init->_tagDisaster}が落下！！",$id);
	}
	// 記念碑、落下
	function monDamage($id, $name, $point) {
		global $init;
		$this->out("<strong>何かとてつもないもの</strong>が<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点に落下しました！！",$id);
	}
	// 家族の力
	function kazokuPower($id, $name, $power) {
		global $init;
		$this->out("<strong>何かとてつもないもの</strong>が<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}に接近！<strong>{$power}発動！</strong>島の危機は免れたが、{$init->tagDisaster_}１人の犠牲者{$init->_tagDisaster}が出てしまいました…。",$id);
	}
	// 隕石、海
	function meteoSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下しました。",$id);
	}
	// 隕石、山
	function meteoMountain($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、<strong>{$lName}</strong>は消し飛びました。",$id);
	}
	// 隕石、海底基地
	function meteoSbase($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、<strong>{$lName}</strong>は崩壊しました。",$id);
	}
	// 隕石、怪獣
	function meteoMonster($id, $name, $lName, $point) {
		global $init;
		$this->out("<strong>怪獣{$lName}</strong>がいた<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、陸地は<strong>怪獣{$lName}</strong>もろとも水没しました。",$id);
	}
	// 隕石、浅瀬
	function meteoSea1($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、海底がえぐられました。",$id);
	}
	// 隕石、その他
	function meteoNormal($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、一帯が水没しました。",$id);
	}
	// 噴火
	function eruption($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点で{$init->tagDisaster_}火山が噴火{$init->_tagDisaster}、<strong>山</strong>が出来ました。",$id);
	}
	// 噴火、浅瀬
	function eruptionSea1($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で陸地になりました。",$id);
	}
	// 噴火、海or海基
	function eruptionSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で海底が隆起、浅瀬になりました。",$id);
	}
	// 噴火、その他
	function eruptionNormal($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で壊滅しました。",$id);
	}
	// 海底探索の油田
	function tansakuoil($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>が油田を発見！",$id);
	}
	// 周りに海がなくて失敗
	function NoSeaAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に海がなかったため中止されました。",$id);
	}
	// 周りに浅瀬がなくて失敗
	function NoShoalAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に浅瀬がなかったため中止されました。",$id);
	}
	// 海がなくて失敗
	function NoSea($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地が海でなかったため中止されました。",$id);
	}
	// 港がないので、造船失敗
	function NoPort($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、周辺に<b>港</b>がなかったため中止されました。",$id);
	}
	// 船破棄
	function ComeBack($id, $name, $comName, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<strong>{$lName}</strong>を{$init->tagComName_}{$comName}{$init->_tagComName}しました。",$id);
	}
	// 船の最大所有数
	function maxShip($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>船の最大所有数条約に違反してしまう</strong>ため許可されませんでした。",$id);
	}
	// 港閉鎖
	function ClosedPort($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<B>{$lName}</B>は閉鎖したようです。",$id);
	}
	// 資金不足のため船舶放棄
	function shipRelease($id, $tId, $name, $tName, $point, $tshipName) {
		global $init;
		$this->late("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島所属</A>{$init->_tagName}<b>{$tshipName}</b>は、資金不足のため破棄されました。",$id, $tId);
	}
	// 海賊船現る
	function VikingCome($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}に<B>海賊船</B>出現！！",$id);
	}
	// 海賊船去る
	function VikingAway($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}から<B>海賊船</B>がどこかに去っていきました。",$id);
	}
	// 海賊船攻撃
	function VikingAttack($id, $tId, $name, $tName, $lName, $point, $tPoint, $tshipName) {
		global $init;
		$this->late("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<b>{$lName}</b>が{$tPoint}の<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}<B>{$tshipName}</B>を攻撃しました。",$id, $tId);
	}
	// 戦艦攻撃
	function SenkanAttack($id, $tId, $name, $tName, $lName, $point, $tpoint, $tshipName) {
		global $init;
		$this->late("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}<b>{$lName}</b>が{$tpoint}の<B>{$tshipName}</B>を攻撃しました。",$id, $tId);
	}
	// 海戦沈没
	function BattleSinking($id, $tId, $name, $lName, $point) {
		global $init;
		$this->late("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<b>{$lName}</b>は沈没しました。",$id, $tId);
	}
	// 船舶沈没
	function ShipSinking($id, $tId, $name, $tName, $lName, $point) {
		global $init;
		$this->late("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}<b>{$lName}</b>は沈没しました。",$id, $tId);
	}
	// 海賊船の財宝
	function VikingTreasure($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}に{$init->tagDisaster_}財宝が眠っている{$init->_tagDisaster}と噂されています。",$id);
	}
	// 財宝発見
	function FindTreasure($id, $tId, $name, $tName, $point, $tshipName, $value) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}<B>{$tshipName}</B>が<b>{$value}億円相当</b>の{$init->tagDisaster_}財宝{$init->_tagDisaster}を発見しました。",$id);
	}
	// 海賊船、強奪
	function RobViking($id, $name, $point, $tshipName, $money, $food) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<b>{$tshipName}</b>が<b>{$money}{$init->unitMoney}</b>の金と<b>{$food}{$init->unitFood}</b>の{$init->nameFood}を強奪していきました。",$id);
	}
	// 船座礁
	function RunAground($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$point}{$init->_tagName}の<b>$lName</b>は{$init->tagDisaster_}座礁{$init->_tagDisaster}しました。",$id);
	}
	// 戦艦ステルスミサイル迎撃
	function msInterceptS($id, $tId, $name, $tName, $comName, $point, $missileE) {
		global $init;
		$this->secret("-{$init->tagName_}{$missileE}発{$init->_tagName}は<strong>戦艦</strong>によって迎撃されたようです。",$id, $tId);
		$this->late("-{$init->tagName_}{$missileE}発{$init->_tagName}は<strong>戦艦</strong>によって迎撃されたようです。",$tId);
	}
	// 戦艦通常ミサイル迎撃
	function msIntercept($id, $tId, $name, $tName, $comName, $point, $missileE) {
		global $init;
		$this->out("-{$init->tagName_}{$missileE}発{$init->_tagName}は<strong>戦艦</strong>によって迎撃されたようです。",$id, $tId);
	}
	// アイテム探索ログ開始
	// アイテム発見
	function ItemFound($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>{$point}</strong>が発見されました。",$id);
	}
	// マスターソード発見
	function SwordFound($id, $name, $mName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}の<strong>怪獣{$mName}</strong>の残骸から天空を切り裂く眩い閃光が駆け抜ける！<strong>マスターソード</strong>が発見されました。",$id);
	}
	// レッドダイヤ発見
	function RedFound($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}の<strong>海底探索船</strong>が<strong>{$point}</strong>を発見しました。",$id);
	}
	// ジン発見
	function ZinFound($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>{$point}</strong>を捕まえました。",$id);
	}
	// ウィスプ発見
	function Zin3Found($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>{$point}</strong>が襲撃してきました！<strong>マスターソード</strong>を振りかざし、見事<strong>{$point}</strong>を捕まえました。",$id);
	}
	// ルナ発見
	function Zin5Found($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、雷鳴とともに、<strong>マナ・クリスタル</strong>が輝く。その白光の中から<strong>{$point}</strong>が現れました。",$id);
	}
	// ジン発見
	function Zin6Found($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、土の中から<strong>{$point}</strong>を発見！<strong>{$point}</strong>を捕まえました。",$id);
	}
	// すでにある
	function IsFail($id, $name, $comName, $land) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、すでに<strong>{$land}</strong>があるため中止されました。",$id);
	}
	// サッカー成功
	function SoccerSuc($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が実施されました。",$id);
	}
	// サッカー失敗
	function SoccerFail($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>スタジアム</strong>が無かったため実行出来ませんでした。",$id);
	}
	// サッカー失敗２
	function SoccerFail2($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>対戦相手</strong>が正常に選択されていなかったため実行出来ませんでした。",$id);
	}
	// 試合失敗
	function GameFail($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、相手島に<strong>スタジアム</strong>が無かったため実行出来ませんでした。",$id);
	}
	// 試合勝利
	function GameWin($id, $tId, $name, $tName, $comName, $it, $tt) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}と{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$it}点対{$tt}点</strong>で勝利しました。",$id, $tId);
	}
	// 試合敗退
	function GameLose($id, $tId, $name, $tName, $comName, $it, $tt) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}と{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$it}点対{$tt}点</strong>で敗退しました。",$id, $tId);
	}
	// 試合引き分け
	function GameDraw($id, $tId, $name, $tName, $comName, $it, $tt) {
		global $init;
		$this->out("<A href=\"{$this->this_file}?Sight={$id}\">{$init->tagName_}{$name}島</A>{$init->_tagName}が<A href=\"{$this->this_file}?Sight={$tId}\">{$init->tagName_}{$tName}島</A>{$init->_tagName}と{$init->tagComName_}{$comName}{$init->_tagComName}を行い、<strong>{$it}点対{$tt}点</strong>で引き分けました。",$id, $tId);
	}
}
