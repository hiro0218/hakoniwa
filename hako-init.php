<?php
/**
 * 箱庭諸島 S.E
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once CONSTANT_PATH.'Alliance.php';
require_once CONSTANT_PATH.'Command.php';
require_once CONSTANT_PATH.'Disaster.php';
require_once CONSTANT_PATH.'Item.php';
require_once CONSTANT_PATH.'Military.php';
require_once CONSTANT_PATH.'Monster.php';
require_once CONSTANT_PATH.'Monument.php';
require_once CONSTANT_PATH.'Name.php';
require_once CONSTANT_PATH.'Prize.php';
require_once CONSTANT_PATH.'Satellite.php';
require_once CONSTANT_PATH.'Ship.php';
require_once CONSTANT_PATH.'Tag.php';
require_once CONSTANT_PATH.'Terrain.php';
require_once CONSTANT_PATH.'Unit.php';
require_once CONSTANT_PATH.'Zin.php';

class Init {
    use Alliance,
        Command,
        Disaster,
        Item,
        Military,
        Monster,
        Monument,
        Name,
        Prize,
        Satellite,
        Ship,
        Tag,
        Terrain,
        Unit,
        Zin;

	function __construct() {
		$this->CPU_start = microtime();
		$this->setpubliciable();
		mt_srand($_SERVER['REQUEST_TIME']);
		// 日本時間にあわせる
	}

	// 各種設定値

	//---------------------------------------------------
	// プログラムファイルに関する設定
	//---------------------------------------------------
	// プログラムを置くディレクトリ
	public $baseDir = "http://localhost:8888/hakoniwa";

	// 画像を置くディレクトリ
	public $imgDir  = "public/assets/img";

	// CSSファイルを置くディレクトリ
	public $cssDir  = "public/assets/css";
	// CSSリスト
	public $css = 'style.css';

	public $jsDir = "public/assets/js";
	public $js = "hakojima.js";

	// データディレクトリの名前（必ず変更してください）
	public $dirName = "logs/data";
	// ディレクトリ作成時のパーミション
	public $dirMode = 0777;

	//パスワードの暗号化 true: 暗号化、false: 暗号化しない
	public $cryptOn      = true;
	// パスワード・ファイル
	public $passwordFile = "password.php";

	// アクセスログファイルの名前
	public $logname = "ip.csv";
	// アクセスログ最大記録レコード数
	public $axesmax = 300;

	//---------------------------------------------------
	// ゲーム全般に関する設定
	//---------------------------------------------------
	// ゲームタイトル
	public $title        = "hako";
	public $adminName    = "hiro";
	public $urlTopPage   = "http://b.0218.jp/";
	public $twitterID    = "hiro0218";

	// 1ターンが何秒か
	public $unitTime     = 10800; // 3時間（これ以上短くすることはオススメ出来ません）

	// ターン更新時の連続更新を許可するか？(0:しない、1:する)
	public $contUpdate   = 1; // 1にすると負荷対策になります

	// 島の最大数（最大250島まで）
	public $maxIsland    = 30; // これ以上増やすとバグが生じやすくなります

	// 島の大きさ
	public $islandSize   = 20; // 馬鹿みたいに広くしてデータ壊れても知りません

	// 初期資金
	public $initialMoney = 1000;
	// 初期食料
	public $initialFood  = 100;
	// 初期面積（設定しない場合は、0）
	public $initialSize  = 0;
	// 初期島データ（使用しない場合は""、使用する場合は"island.txt"として島データファイルを作ってください）
	public $initialLand  = "";

	// 資金最大値
	public $maxMoney     = 99999; // バランス的にこのくらいが妥当かと
	// 食料最大値
	public $maxFood      = 99999;
	// 木材最大値
	public $maxWood      = 10000;

	// 新規島の登録モード (0:通常、1:管理人)
	public $registerMode   = 0;
	// 管理人モード
	public $adminMode;

	// 負荷計測するか？(0:しない、1:する)
	public $performance  = 1;
	public $CPU_start = 0;

	//---------------------------------------------------
	// バックアップに関する設定
	//---------------------------------------------------
	// セーフモードなら1をそうでないなら0を設定してください
	public $safemode    = 1;
	// バックアップを何ターンおきに取るか
	public $backupTurn  = 1;
	// バックアップを何回分残すか
	public $backupTimes = 5;

	//---------------------------------------------------
	// 表示に関する設定
	//---------------------------------------------------
	// TOPページに一度に表示する島の数(0なら全島表示)
	public $islandListRange = 10;//10;

	// 資金表示モード
	public $moneyMode  = true; // true: 100の位で四捨五入, false: そのまま
	// トップページに表示するログのターン数
	public $logTopTurn = 4;
	// ログファイル保持ターン数
	public $logMax     = 8;
	// 整地ログを１本化するか？(0:しない 1:座標あり 2:座標なし)
	public $logOmit    = 2;

	// 発見ログ保持行数
	public $historyMax = 10;

	// お知らせ
	public $noticeFile   = "notice.txt";
	// 記事表示部の最大の高さ。
	public $divHeight  = 150;

	// 放棄コマンド自動入力ターン数
	public $giveupTurn = 30;

	// コマンド入力限界数
	public $commandMax = 30;

	// スタイルシートを改変していないので、ここに記述
	public $tagMoney_  = '<span style="color:#999933; font-weight:bold;">';
	public $_tagMoney  = '</span>';

	// コメントの自動リンク (0:しない 1:する)
	public $autoLink   = 1;
}
