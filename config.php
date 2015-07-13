<?php
/*******************************************************************

	箱庭諸島 S.E

	- 初期設定用ファイル -

	config.php by SERA - 2013/07/06

*******************************************************************/

// 開発用の設定
ini_set('display_errors', 1);
set_time_limit (0);
error_reporting(E_ALL);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // 過去の日付

date_default_timezone_set('Asia/Tokyo');

define("DEBUG", true); 								// true: デバッグ false: 通常
define("LOCK_RETRY_COUNT", 10);				// ファイルロック処理のリトライ回数
define("LOCK_RETRY_INTERVAL", 1000);	// 再ロック処理実施までの時間(ミリ秒)。最低でも500くらいを指定

// 絶対パス定義
defined("DS") || define("DS", DIRECTORY_SEPARATOR);
define("ABSOLUTE_PATH", dirname(__FILE__) . DS);

//--------------------------------------------------------------------
class Init {

   //function __construct() {}
   //function __destruct() {}

	// 各種設定値

	//---------------------------------------------------
	// プログラムファイルに関する設定
	//---------------------------------------------------
	// プログラムを置くディレクトリ
	public $baseDir = "http://localhost/hakoniwa";

	// 画像を置くディレクトリ
	public $imgDir  = "img";

	// CSSファイルを置くディレクトリ
	public $cssDir  = "css";
	// CSSリスト
	public $cssList = array('SkyBlue.css');

	public $jsDir = "js";
	public $js = "hakojima.js";

	// データディレクトリの名前（必ず変更してください）
	public $dirName = "data";
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
	public $registMode   = 0;
	// 管理人モード
	public $adminMode;

	// 負荷計測するか？(0:しない、1:する)
	public $performance  = 1;
	public $CPU_start;

	//---------------------------------------------------
	// バックアップに関する設定
	//---------------------------------------------------
	// セーフモードなら1をそうでないなら0を設定してください
	public $safemode    = 1;
	// バックアップを何ターンおきに取るか
	public $backupTurn  = 6;
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
	public $infoFile   = "info.txt";
	// 記事表示部の最大の高さ。
	public $divHeight  = 150;

	// 放棄コマンド自動入力ターン数
	public $giveupTurn = 30;

	// コマンド入力限界数
	public $commandMax = 30;

	//---------------------------------------------------
	// 名称の定義
	//---------------------------------------------------
	public $nameRank                       = "順位";
	public $namePopulation                 = "人口";
	public $nameArea                       = "面積";
	public $nameFunds                      = "資金";
	public $nameFood                       = "食料";
	public $nameUnemploymentRate           = "失業率";
	public $nameFarmSize                   = "農場規模";
	public $nameFactoryScale               = "工場規模";
	public $nameCommercialScale            = "商業規模";
	public $nameMineScale                  = "採掘場規模";
	public $namePowerPlantScale            = "発電所規模";
	public $namePowerSupplyRate            = "電力供給率";
	public $nameWeather                    = "天気";
	public $nameMilitaryTechnology         = "軍事技術";
	public $nameMonsterExterminationNumber = "怪獣退治数";
	public $nameSatellite                  = "人工衛星";
	public $nameGin                        = "ジン";
	public $nameItems                      = "アイテム";


	//---------------------------------------------------
	// 各種単位の設定
	//---------------------------------------------------
	// 人口の単位
	public $unitPop     = "00人";
	// 食料の単位
	public $unitFood    = "00トン";
	// 広さの単位
	public $unitArea    = "00万坪";
	// 木の数の単位
	public $unitTree    = "00本";
	// お金の単位
	public $unitMoney   = "億円";
	// 怪獣の単位
	public $unitMonster = "匹";

	// 木の単位当たりの売値
	public $treeValue   = 10;

	// 名前変更のコスト
	public $costChangeName = 500;

	// 人口1単位あたりの食料消費料
	public $eatenFood   = 0.2;

	// 油田の収入
	public $oilMoney    = 1000;
	// 油田の枯渇確率
	public $oilRatio    = 40;

	// 何ターン毎に宝くじの抽選が行われるか？
	public $lottery     = 50;
	// 当選金
	public $lotmoney    = 30000;

	//---------------------------------------------------
	// 同盟に関する設定
	//---------------------------------------------------
	public $comAlly;

	// 同盟作成を許可するか？(0:しない、1:する、2:管理者のみ)
	public $allyUse     = 1;

	// ひとつの同盟にしか加盟できないようにするか？(0:しない、1:する)
	public $allyJoinOne = 1;

	// 同盟データの管理ファイル
	public $allyData    = 'ally.dat';

	// 同盟のマーク
	public $allyMark = array(
		"🐶","🐵","🐦",
		// 'Б','Г','Д','Ж','Й',
		// 'Ф','Ц','Ш','Э','Ю',
		// 'Я','б','Θ','Σ','Ψ',
		// 'Ω','ゑ','ゐ','¶','‡',
		// '†','♪','♭','♯','‰',
		// 'Å','∽','∇','∂','∀',
		// '⇔','∨','〒','￡','￠',
		// '＠','★','♂','♀','＄',
		// '￥','℃','仝','〆',
	);

	// 入力文字数の制限 (全角文字数で指定) 実際は、<input> 内の MAXLENGTH を直に修正してください。 (;^_^A
	public $lengthAllyName    = 15;   // 同盟の名前
	public $lengthAllyComment = 40;   // 「各同盟の状況」欄に表示される盟主のコメント
	public $lengthAllyTitle   = 30;   // 「同盟の情報」欄の上に表示される盟主メッセージのタイトル
	public $lengthAllyMessage = 1500; // 「同盟の情報」欄の上に表示される盟主メッセージ

	// スタイルシートを改変していないので、ここに記述
	public $tagMoney_  = '<span style="color:#999933; font-weight:bold;">';
	public $_tagMoney  = '</span>';

	// コメントの自動リンク (0:しない 1:する)
	public $autoLink   = 1;

	// 以下は、表示関連で使用しているだけで、実際の機能を有していません、さらなる改造で実現可能です。

	// 加盟・脱退をコマンドで行うようにする？(0:しない、1:する)
	public $allyJoinComUse = 0;

	// 同盟に加盟することで通常災害発生確率が減少？(0:しない)
	// 対象となる災害：地震、津波、台風、隕石、巨大隕石、噴火
	public $allyDisDown  = 0;    // 設定する場合、通常時に対する倍率を設定。(例)0.5なら半減。2なら倍増(^^;;;
	public $costMakeAlly = 1000; // 同盟の結成・変更にかかる費用
	public $costKeepAlly = 500;  // 同盟の維持費(加盟している島で均等に負担)

	//---------------------------------------------------
	// 軍事に関する設定
	//---------------------------------------------------
	// ミサイル発射禁止ターン
	public $noMissile     = 20; // これより前には実行が許可されない
	// 援助禁止ターン
	public $noAssist      = 50; // これより前には実行が許可されない

	// 複数地点へのミサイル発射を可能にするか？1:Yes 0:No
	public $multiMissiles = 1;

	// ミサイル基地
	// 経験値の最大値
	public $maxExpPoint   = 200; // ただし、最大でも255まで

	// レベルの最大値
	public $maxBaseLevel  = 5; // ミサイル基地
	public $maxSBaseLevel = 3; // 海底基地

	// 経験値がいくつでレベルアップか
	public $baseLevelUp   = array(20, 60, 120, 200); // ミサイル基地
	public $sBaseLevelUp  = array(50, 200); // 海底基地

	// 防衛施設の最大耐久力
	public $dBaseHP       = 5;
	// 海底防衛施設の最大耐久力
	public $sdBaseHP      = 3;
	// 防衛施設が怪獣に踏まれた時自爆するなら1、しないなら0
	public $dBaseAuto     = 1;

	// 目標の島 所有の島が選択された状態でリストを生成 1、順位がTOPの島なら0
	// ミサイルの誤射が多い場合などに使用すると良いかもしれない
	public $targetIsland  = 1;

	//---------------------------------------------------
	// 船舶に関する設定
	//---------------------------------------------------
	// 船の最大所有数
	public $shipMax  = 5;

	// 船舶の種類（造船対象の船舶）
	public $shipKind = 4; // 最大15

	// 船舶の名前（10以降は災害船舶と定義）
	public $shipName = array (
		'輸送船',         # 0
		'漁船',           # 1
		'海底探索船',     # 2
		'戦艦',           # 3
		'',               # 4
		'',               # 5
		'',               # 6
		'',               # 7
		'',               # 8
		'',               # 9
		'海賊船',         # 10
		'',               # 11
		'',               # 12
		'',               # 13
		''                # 14
		);

	// 船舶維持費
	public $shipCost = array(100, 200, 300, 500, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

	// 船舶体力（最大15）
	public $shipHP   = array(1, 2, 3, 10, 1, 1, 1, 1, 1, 1, 10, 1, 1, 1, 1);

	// 船舶経験値の最大値（最大でも255まで）
	public $shipEX   = 100;

	// レベルの最大値
	public $shipLv   = 5;

	// 経験値がいくつでレベルアップか
	public $shipLevelUp   = array(10, 30, 60, 100);

	// 船舶設定値（確率：設定値 x 0.1%）
	public $shipIncom          =  200; // 輸送船収入
	public $shipFood           =  100; // 漁船の食料収入
	public $shipIntercept      =  200; // 戦艦がミサイルを迎撃する確率
	public $disRunAground1     =   10; // 座礁確率  座礁処理に入るための確率
	public $disRunAground2     =   10; // 座礁確率  船 個別の判定
	public $disZorasu          =   30; // ぞらす 出現確率
	public $disViking          =   10; // 海賊船 出現確率 船１つあたり（たくさん船を持てばその分確率UP）
	public $disVikingAway      =   30; // 海賊船 去る確率
	public $disVikingRob       =   50; // 海賊船強奪
	public $disVikingAttack    =  500; // 海賊が攻撃してくる確率
	public $disVikingMinAtc    =    1; // 海賊船が与える最低ダメージ
	public $disVikingMaxAtc    =    3; // 海賊船が与える最大ダメージ

	//---------------------------------------------------
	// 災害に関する設定（確率：設定値 x 0.1%）
	//---------------------------------------------------
	public $disEarthquake =   5;  // 地震
	public $disTsunami    =  10;  // 津波
	public $disTyphoon    =  20;  // 台風
	public $disMeteo      =  15;  // 隕石
	public $disHugeMeteo  =   3;  // 巨大隕石
	public $disEruption   =   5;  // 噴火
	public $disFire       =  10;  // 火災
	public $disMaizo      =  30;  // 埋蔵金
	public $disSto        =  10;  // ストライキ
	public $disTenki      =  30;  // 天気
	public $disTrain      = 300;  // 電車
	public $disPoo        =  30;  // 失業暴動
	public $disPooPop     = 500;  // 暴動が発生する最低人口（50000人）

	// 地盤沈下
	public $disFallBorder = 290;//100; // 安全限界の広さ(Hex数)
	public $disFalldown   = 10;//30;  // その広さを超えた場合の確率

	//---------------------------------------------------
	// 怪獣に関する設定
	//---------------------------------------------------
	public $disMonsBorder1 = 2000;  // 人口基準1(怪獣レベル1)
	public $disMonsBorder2 = 4000;  // 人口基準2(怪獣レベル2)
	public $disMonsBorder3 = 6000;  // 人口基準3(怪獣レベル3)
	public $disMonsBorder4 = 8000;  // 人口基準4(怪獣レベル4)
	public $disMonsBorder5 = 10000; // 人口基準5(怪獣レベル5)
	public $disMonster     = 2.5;   // 単位面積あたりの出現率(0.01%単位)

	public $monsterLevel1  = 4;     // サンジラまで
	public $monsterLevel2  = 9;     // いのらゴーストまで
	public $monsterLevel3  = 15;    // かおくと（♀）まで
	public $monsterLevel4  = 23;    // 迎撃いのらまで
	public $monsterLevel5  = 26;    // インベーダーまで

	public $monsterNumber  = 27;    // 怪獣の種類
	// 怪獣の名前
	public $monsterName = array (
		'メカいのら',         # 0
		'いのら（♂）',       # 1
		'いのら（♀）',       # 2
		'サンジラ（♂）',     # 3
		'サンジラ（♀）',     # 4
		'レッドいのら（♂）', # 5
		'レッドいのら（♀）', # 6
		'ダークいのら（♂）', # 7
		'ダークいのら（♀）', # 8
		'いのらゴースト',     # 9
		'クジラ（♂）',       # 10
		'クジラ（♀）',       # 11
		'ワープいのら',       # 12
		'おじー',             # 13
		'イナッシュ（♀）',   # 14
		'かおくと（♀）',     # 15
		'かおくと（♂）',     # 16
		'グレーターおじー',   # 17
		'イナッシュ（♂）',   # 18
		'キングいのら（♂）', # 19
		'キングいのら（♀）', # 20
		'うおが（♂）',       # 21
		'うおが（♀）',       # 22
		'迎撃いのら',         # 23
		'ハートいのら',       # 24
		'姫いのら',           # 25
		'インベーダー',       # 26
	);
	// 怪獣の画像(硬化中)
	public $monsterImage   = array ('', '', '', 'kouka.gif', 'kouka.gif', '', '', '', '', '', 'kouka.gif', 'kouka.gif', '', 'kouka1.gif', '', 'kouka3.gif', 'kouka3.gif', 'kouka2.gif', '', '', '', '', '', '', '', '');

	// 最低体力、体力の幅、特殊能力、経験値、死体の値段
	public $monsterBHP     = array(10, 1, 1, 1, 1, 2, 3, 2, 2, 2, 3, 3, 9, 5, 4, 4, 3, 5, 9, 4, 5, 6, 6, 7, 8, 5, 99);
	public $monsterDHP     = array( 0, 2, 1, 2, 1, 2, 2, 2, 1, 1, 2, 2, 0, 1, 2, 1, 2, 2, 0, 3, 2, 2, 2, 2, 1, 0, 0);
	public $monsterSpecial = array(0x0, 0x0, 0x0, 0x4, 0x4, 0x1, 0x1, 0x120, 0x20, 0x2, 0x11, 0x10, 0x40, 0x4, 0x200, 0x20000, 0x410, 0x5, 0x240, 0x1020, 0x2020, 0x4400, 0x10100, 0x101, 0x21, 0x2121, 0x42);
	public $monsterExp     = array(20, 6, 5, 7, 6, 9, 8, 17, 12, 10, 10, 9, 30, 13, 15, 20, 25, 22, 40, 45, 43, 50, 50, 48, 60, 100, 200);
	public $monsterValue   = array(1000, 300, 200, 400, 300, 600, 500, 900, 700, 600, 800, 700, 2000, 900, 1000, 500, 1800, 1200, 2500, 3000, 2700, 5000, 4000, 3500, 7000, 10000, 50000);
	// 特殊能力の内容は、(各能力は 1bit に割り当てる)
	// 0x0 特になし
	// 0x1 足が速い(最大2歩あるく)
	// 0x2 足がとても速い(最大何歩あるくか不明)
	// 0x4 奇数ターンは硬化
	// 0x10 偶数ターンは硬化
	// 0x20 仲間を呼ぶ
	// 0x40 ワープする
	// 0x100 ミサイル叩き落とす
	// 0x200 飛行移動能力
	// 0x400 瀕死になると大爆発
	// 0x1000 金増やす
	// 0x2000 食料増やす
	// 0x4000 金減らす
	// 0x10000 食料減らす
	// 0x20000 分裂する

	//---------------------------------------------------
	// 賞に関する設定
	//---------------------------------------------------
	// ターン杯を何ターン毎に出すか
	public $turnPrizeUnit = 100;
	// 賞の名前
	public $prizeName = array (
		'ターン杯', '繁栄賞', '超繁栄賞', '究極繁栄賞', '平和賞', '超平和賞', '究極平和賞', '災難賞', '超災難賞', '究極災難賞', '素人怪獣討伐賞', '怪獣討伐賞', '超怪獣討伐賞', '究極怪獣討伐賞', '怪獣討伐王賞',
	);

	//---------------------------------------------------
	// 記念碑に関する設定
	//---------------------------------------------------
	// 何種類あるか
	public $monumentNumber = 54;
	// 名前
	public $monumentName = array (
		'戦の碑', '農の碑', '鉱の碑', '匠の碑', '平和の碑', 'キャッスル城', 'モノリス', '聖樹', '戦いの碑', 'ラスカル', '棺桶', 'ヨーゼフ', 'くま', 'くま', 'くま', '貯金箱', 'モアイ', '地球儀', 'バッグ', 'ごみ箱', 'ダークいのら像', 'テトラ像', 'はねはむ像', 'ロケット', 'ピラミッド', 'アサガオ', 'チューリップ', 'チューリップ', '水仙', 'サボテン', '仙人掌', '魔方陣', '神殿', '神社', '闇石', '地石', '氷石', '風石', '炎石', '光石', '卵', '卵', '卵', '卵', '古代遺跡', 'サンタクロース', '壊れた侵略者', '憩いの公園', '桜', '向日葵', '銀杏', 'クリスマスツリー2001', '雪うさぎ', '幸福の女神像'
	);

	//---------------------------------------------------
	// 人工衛星に関する設定
	//---------------------------------------------------
	// 何種類あるか
	public $EiseiNumber = 6;
	// 名前
	public $EiseiName = array (
		'気象衛星', '観測衛星', '迎撃衛星', '軍事衛星', '防衛衛星', 'イレギュラー'
	);

	//---------------------------------------------------
	// ジンに関する設定
	//---------------------------------------------------
	// 何種類あるか
	public $ZinNumber = 7;
	// 名前
	public $ZinName = array (
		'ノーム', 'ウィスプ', 'シェイド', 'ドリアード', 'ルナ', 'ジン', 'サラマンダー'
	);

	//---------------------------------------------------
	// アイテムに関する設定
	//---------------------------------------------------
	// 何種類あるか
	public $ItemNumber = 21;
	// 名前
	public $ItemName = array (
		'地図１', 'ノコギリ', '禁断の書', 'マスク', 'ポチョムキン', '地図２', '科学書', '物理書', '第三の脳', 'マスターソード', '植物図鑑', 'ルーペ', '苗木', '数学書', '技術書', 'マナ・クリスタル', '農作物図鑑', '経済書', 'リング', 'レッドダイヤ', '木材'
	);

	/********************
		外見関係
	********************/
	// 大きい文字
	public $tagBig_       = '<span class="big">';
	public $_tagBig       = '</span>';
	// 島の名前など
	public $tagName_      = '<span class="islName">';
	public $_tagName      = '</span>';
	// 薄くなった島の名前
	public $tagName2_     = '<span class="islName2">';
	public $_tagName2     = '</span>';
	// 順位の番号など
	public $tagNumber_    = '<span class="number">';
	public $_tagNumber    = '</span>';
	// 順位表における見だし
	public $tagTH_        = '<span class="head">';
	public $_tagTH        = '</span>';
	// 開発計画の名前
	public $tagComName_   = '<span class="command">';
	public $_tagComName   = '</span>';
	// 災害
	public $tagDisaster_  = '<span class="disaster">';
	public $_tagDisaster  = '</span>';
	// 順位表、セルの属性
	public $bgTitleCell   = 'class="TitleCell"';   // 順位表見出し
	public $bgNumberCell  = 'class="NumberCell"';  // 順位表順位
	public $bgNameCell    = 'class="NameCell"';    // 順位表島の名前
	public $bgInfoCell    = 'class="InfoCell"';    // 順位表島の情報
	public $bgMarkCell    = 'class="MarkCell"';    // 同盟のマーク
	public $bgCommentCell = 'class="CommentCell"'; // 順位表コメント欄
	public $bgInputCell   = 'class="InputCell"';   // 開発計画フォーム
	public $bgMapCell     = 'class="MapCell"';     // 開発計画地図
	public $bgCommandCell = 'class="CommandCell"'; // 開発計画入力済み計画

	/********************
		地形番号
	********************/
	public $landSea       =  0; // 海
	public $landWaste     =  1; // 荒地
	public $landPlains    =  2; // 平地
	public $landTown      =  3; // 町系
	public $landForest    =  4; // 森
	public $landFarm      =  5; // 農場
	public $landFactory   =  6; // 工場
	public $landBase      =  7; // ミサイル基地
	public $landDefence   =  8; // 防衛施設
	public $landMountain  =  9; // 山
	public $landMonster   = 10; // 怪獣
	public $landSbase     = 11; // 海底基地
	public $landOil       = 12; // 海底油田
	public $landMonument  = 13; // 記念碑
	public $landHaribote  = 14; // ハリボテ
	public $landPark      = 15; // 遊園地
	public $landFusya     = 16; // 風車
	public $landSyoubou   = 17; // 消防署
	public $landNursery   = 18; // 養殖場
	public $landSeaSide   = 19; // 海岸(砂浜)
	public $landSeaResort = 20; // 海の家
	public $landCommerce  = 21; // 商業ビル
	public $landPort      = 22; // 港
	public $landSeaCity   = 23; // 海底都市
	public $landSdefence  = 24; // 海底防衛施設
	public $landSfarm     = 25; // 海底農場
	public $landSsyoubou  = 26; // 海底消防署
	public $landHatuden   = 27; // 発電所
	public $landBank      = 28; // 銀行
	public $landPoll      = 29; // 汚染土壌
	public $landProcity   = 30; // 防災都市
	public $landZorasu    = 31; // ぞらす
	public $landSoccer    = 32; // スタジアム
	public $landRail      = 33; // 線路
	public $landStat      = 34; // 駅
	public $landTrain     = 35; // 電車
	public $landSleeper   = 36; // 怪獣（睡眠中）
	public $landNewtown   = 37; // ニュータウン
	public $landBigtown   = 38; // 現代都市
	public $landMyhome    = 39; // 自宅
	public $landFroCity   = 40; // 海上都市
	public $landSoukoM    = 41; // 金庫
	public $landSoukoF    = 42; // 食料庫
	public $landShip      = 43; // 船舶

	/********************
		コマンド
	********************/
	// コマンド分割
	// このコマンド分割だけは、自動入力系のコマンドは設定しないで下さい。
	public $commandDivido =
		array(
			'開発,0,10',      // 計画番号00～10
			'建設,11,25',     // 計画番号11～20
			'建設2,26,50',    // 計画番号21～30
			'サッカー,51,60', // 計画番号51～60
			'攻撃1,61,70',    // 計画番号61～80
			'攻撃2,71,80',    // 計画番号61～80
			'運営,81,90'      // 計画番号81～90
		);
	// 注意：スペースは入れないように
	// ○→ '開発,0,10',   # 計画番号00～10
	// ×→ '開発, 0,10', # 計画番号00～10

	public $commandTotal = 68; // コマンドの種類

	// 順序
	public $comList;

	// 整地系
	public $comPrepare      = 01; // 整地
	public $comPrepare2     = 02; // 地ならし
	public $comReclaim      = 03; // 埋め立て
	public $comDestroy      = 04; // 掘削
	public $comDeForest     = 05; // 伐採

	// 作る系
	public $comPlant        = 11; // 植林
	public $comSeaSide      = 12; // 砂浜整備
	public $comFarm         = 13; // 農場整備
	public $comSfarm        = 14; // 海底農場整備
	public $comNursery      = 15; // 養殖場設置
	public $comFactory      = 16; // 工場建設
	public $comCommerce     = 17; // 商業ビル建設
	public $comMountain     = 18; // 採掘場整備
	public $comHatuden      = 19; // 発電所
	public $comBase         = 20; // ミサイル基地建設
	public $comDbase        = 21; // 防衛施設建設
	public $comSdbase       = 22; // 海底防衛施設
	public $comSbase        = 23; // 海底基地建設
	public $comMonument     = 24; // 記念碑建造
	public $comHaribote     = 25; // ハリボテ設置
	public $comFusya        = 26; // 風車設置
	public $comSyoubou      = 27; // 消防署建設
	public $comSsyoubou     = 28; // 海底消防署
	public $comPort         = 29; // 港建設
	public $comMakeShip     = 30; // 造船
	public $comSendShip     = 31; // 船派遣
	public $comReturnShip   = 32; // 船派遣
	public $comShipBack     = 33; // 船破棄
	public $comSeaResort    = 34; // 海の家建設
	public $comPark         = 35; // 遊園地建設
	public $comSoccer       = 36; // スタジアム建設
	public $comRail         = 37; // 線路敷設
	public $comStat         = 38; // 駅建設
	public $comSeaCity      = 39; // 海底都市建設
	public $comProcity      = 40; // 防災都市
	public $comNewtown      = 41; // ニュータウン建設
	public $comBigtown      = 42; // 現代都市建設
	public $comMyhome       = 43; // 自宅建設
	public $comSoukoM       = 44; // 金庫
	public $comSoukoF       = 45; // 食料庫

	// サッカー系
	public $comOffense      = 51; // 攻撃力強化
	public $comDefense      = 52; // 守備力強化
	public $comPractice     = 53; // 総合練習
	public $comPlaygame     = 54; // 交流試合

	// 発射系
	public $comMissileNM    = 61; // ミサイル発射
	public $comMissilePP    = 62; // PPミサイル発射
	public $comMissileST    = 63; // STミサイル発射
	public $comMissileBT    = 64; // BTミサイル発射
	public $comMissileSP    = 65; // 催眠弾発射
	public $comMissileLD    = 66; // 陸地破壊弾発射
	public $comMissileLU    = 67; // 地形隆起弾発射
	public $comMissileSM    = 68; // ミサイル撃ち止め
	public $comEisei        = 69; // 人工衛星発射
	public $comEiseimente   = 70; // 人工衛星発修復
	public $comEiseiAtt     = 71; // 人工衛星破壊
	public $comEiseiLzr     = 72; // 衛星レーザー
	public $comSendMonster  = 73; // 怪獣派遣
	public $comSendSleeper  = 74; // 怪獣輸送

	// 運営系
	public $comDoNothing    = 81; // 資金繰り
	public $comSell         = 82; // 食料輸出
	public $comSellTree     = 83; // 木材輸出
	public $comMoney        = 84; // 資金援助
	public $comFood         = 85; // 食料援助
	public $comLot          = 86; // 宝くじ購入
	public $comPropaganda   = 87; // 誘致活動
	public $comBoku         = 88; // 僕の引越し
	public $comHikidasi     = 89; // 倉庫引き出し
	public $comGiveup       = 90; // 島の放棄

	// 自動入力系
	public $comAutoPrepare  = 91; // フル整地
	public $comAutoPrepare2 = 92; // フル地ならし
	public $comAutoDelete   = 93; // 全コマンド消去
	public $comAutoReclaim  = 94; // フル埋め立て

	public $comName;
	public $comCost;

	// 島の座標数
	public $pointNumber;

	// 周囲2ヘックスの座標
	public $ax = array(0, 1, 1, 1, 0,-1, 0, 1, 2, 2, 2, 1, 0,-1,-1,-2,-1,-1, 0);
	public $ay = array(0,-1, 0, 1, 1, 0,-1,-2,-1, 0, 1, 2, 2, 2, 1, 0,-1,-2,-2);

	// コメントなどに、予\定のように\が勝手に追加される
	public $stripslashes;


	//////////////////////////////////////////////////

	private function setpubliciable() {
		$this->pointNumber = $this->islandSize * $this->islandSize;
		$this->comList = array(
			$this->comPrepare,
			$this->comPrepare2,
			$this->comReclaim,
			$this->comDestroy,
			$this->comDeForest,
			$this->comPlant,
			$this->comSeaSide,
			$this->comFarm,
			$this->comSfarm,
			$this->comNursery,
			$this->comFactory,
			$this->comCommerce,
			$this->comMountain,
			$this->comHatuden,
			$this->comBase,
			$this->comDbase,
			$this->comSbase,
			$this->comSdbase,
			$this->comMonument,
			$this->comHaribote,
			$this->comFusya,
			$this->comSyoubou,
			$this->comSsyoubou,
			$this->comPort,
			$this->comMakeShip,
			$this->comSendShip,
			$this->comReturnShip,
			$this->comShipBack,
			$this->comSeaResort,
			$this->comPark,
			$this->comSoccer,
			$this->comRail,
			$this->comStat,
			$this->comSeaCity,
			$this->comProcity,
			$this->comNewtown,
			$this->comBigtown,
			$this->comMyhome,
			$this->comSoukoM,
			$this->comSoukoF,
			$this->comMissileNM,
			$this->comMissilePP,
			$this->comMissileST,
			$this->comMissileBT,
			$this->comMissileSP,
			$this->comMissileLD,
			$this->comMissileLU,
			$this->comMissileSM,
			$this->comEisei,
			$this->comEiseimente,
			$this->comEiseiAtt,
			$this->comEiseiLzr,
			$this->comSendMonster,
			$this->comSendSleeper,
			$this->comOffense,
			$this->comDefense,
			$this->comPractice,
			$this->comPlaygame,
			$this->comDoNothing,
			$this->comSell,
			$this->comSellTree,
			$this->comMoney,
			$this->comFood,
			$this->comLot,
			$this->comPropaganda,
			$this->comBoku,
			$this->comHikidasi,
			$this->comGiveup,
			$this->comAutoPrepare,
			$this->comAutoPrepare2,
			$this->comAutoReclaim,
			$this->comAutoDelete,
		);

		// 計画の名前と値段
		$this->comName[$this->comPrepare]      = '整地';
		$this->comCost[$this->comPrepare]      = 5;
		$this->comName[$this->comPrepare2]     = '地ならし';
		$this->comCost[$this->comPrepare2]     = 100;
		$this->comName[$this->comReclaim]      = '埋め立て';
		$this->comCost[$this->comReclaim]      = 150;
		$this->comName[$this->comDestroy]      = '掘削';
		$this->comCost[$this->comDestroy]      = 200;
		$this->comName[$this->comDeForest]     = '伐採';
		$this->comCost[$this->comDeForest]     = 0;
		$this->comName[$this->comPlant]        = '植林';
		$this->comCost[$this->comPlant]        = 50;
		$this->comName[$this->comSeaSide]      = '砂浜整備';
		$this->comCost[$this->comSeaSide]      = 100;
		$this->comName[$this->comFarm]         = '農場整備';
		$this->comCost[$this->comFarm]         = 20;
		$this->comName[$this->comSfarm]        = '海底農場整備';
		$this->comCost[$this->comSfarm]        = 500;
		$this->comName[$this->comNursery]      = '養殖場設置';
		$this->comCost[$this->comNursery]      = 20;
		$this->comName[$this->comFactory]      = '工場建設';
		$this->comCost[$this->comFactory]      = 100;
		$this->comName[$this->comCommerce]     = '商業ビル建設';
		$this->comCost[$this->comCommerce]     = 500;
		$this->comName[$this->comMountain]     = '採掘場整備';
		$this->comCost[$this->comMountain]     = 300;
		$this->comName[$this->comHatuden]      = '発電所建設';
		$this->comCost[$this->comHatuden]      = 300;
		$this->comName[$this->comPort]         = '港建設';
		$this->comCost[$this->comPort]         = 1500;
		$this->comName[$this->comMakeShip]     = '造船';
		$this->comCost[$this->comMakeShip]     = 500;
		$this->comName[$this->comSendShip]     = '船派遣';
		$this->comCost[$this->comSendShip]     = 200;
		$this->comName[$this->comReturnShip]   = '船帰還';
		$this->comCost[$this->comReturnShip]   = 200;
		$this->comName[$this->comShipBack]     = '船破棄';
		$this->comCost[$this->comShipBack]     = 500;
		$this->comName[$this->comRail]         = '線路敷設';
		$this->comCost[$this->comRail]         = 100;
		$this->comName[$this->comStat]         = '駅建設';
		$this->comCost[$this->comStat]         = 500;
		$this->comName[$this->comSoccer]       = 'スタジアム建設';
		$this->comCost[$this->comSoccer]       = 1000;
		$this->comName[$this->comPark]         = '遊園地建設';
		$this->comCost[$this->comPark]         = 700;
		$this->comName[$this->comSeaResort]    = '海の家建設';
		$this->comCost[$this->comSeaResort]    = 100;
		$this->comName[$this->comFusya]        = '風車建設';
		$this->comCost[$this->comFusya]        = 1000;
		$this->comName[$this->comSyoubou]      = '消防署建設';
		$this->comCost[$this->comSyoubou]      = 600;
		$this->comName[$this->comSsyoubou]     = '海底消防署建設';
		$this->comCost[$this->comSsyoubou]     = 1000;
		$this->comName[$this->comBase]         = 'ミサイル基地建設';
		$this->comCost[$this->comBase]         = 300;
		$this->comName[$this->comDbase]        = '防衛施設建設';
		$this->comCost[$this->comDbase]        = 800;
		$this->comName[$this->comSbase]        = '海底基地建設';
		$this->comCost[$this->comSbase]        = 8000;
		$this->comName[$this->comSdbase]       = '海底防衛施設建設';
		$this->comCost[$this->comSdbase]       = 1000;
		$this->comName[$this->comSeaCity]      = '海底都市建設';
		$this->comCost[$this->comSeaCity]      = 3000;
		$this->comName[$this->comProcity]      = '防災都市化';
		$this->comCost[$this->comProcity]      = 3000;
		$this->comName[$this->comNewtown]      = 'ニュータウン建設';
		$this->comCost[$this->comNewtown]      = 1000;
		$this->comName[$this->comBigtown]      = '現代都市建設';
		$this->comCost[$this->comBigtown]      = 10000;
		$this->comName[$this->comMyhome]       = '自宅建設';
		$this->comCost[$this->comMyhome]       = 8000;
		$this->comName[$this->comSoukoM]       = '金庫建設';
		$this->comCost[$this->comSoukoM]       = 1000;
		$this->comName[$this->comSoukoF]       = '食料庫建設';
		$this->comCost[$this->comSoukoF]       = -1000;
		$this->comName[$this->comMonument]     = '記念碑建造';
		$this->comCost[$this->comMonument]     = 9999;
		$this->comName[$this->comHaribote]     = 'ハリボテ設置';
		$this->comCost[$this->comHaribote]     = 1;
		$this->comName[$this->comMissileNM]    = 'ミサイル発射';
		$this->comCost[$this->comMissileNM]    = 20;
		$this->comName[$this->comMissilePP]    = 'PPミサイル発射';
		$this->comCost[$this->comMissilePP]    = 50;
		$this->comName[$this->comMissileST]    = 'STミサイル発射';
		$this->comCost[$this->comMissileST]    = 100;
		$this->comName[$this->comMissileBT]    = 'BTミサイル発射';
		$this->comCost[$this->comMissileBT]    = 300;
		$this->comName[$this->comMissileSP]    = '催眠弾発射';
		$this->comCost[$this->comMissileSP]    = 100;
		$this->comName[$this->comMissileLD]    = '陸地破壊弾発射';
		$this->comCost[$this->comMissileLD]    = 500;
		$this->comName[$this->comMissileLU]    = '地形隆起弾発射';
		$this->comCost[$this->comMissileLU]    = 500;
		$this->comName[$this->comMissileSM]    = 'ミサイル撃ち止め';
		$this->comCost[$this->comMissileSM]    = 0;
		$this->comName[$this->comEisei]        = '人工衛星打ち上げ';
		$this->comCost[$this->comEisei]        = 9999;
		$this->comName[$this->comEiseimente]   = '人工衛星修復';
		$this->comCost[$this->comEiseimente]   = 5000;
		$this->comName[$this->comEiseiAtt]     = '衛星破壊砲発射';
		$this->comCost[$this->comEiseiAtt]     = 30000;
		$this->comName[$this->comEiseiLzr]     = '衛星レーザー発射';
		$this->comCost[$this->comEiseiLzr]     = 20000;
		$this->comName[$this->comSendMonster]  = '怪獣派遣';
		$this->comCost[$this->comSendMonster]  = 3000;
		$this->comName[$this->comSendSleeper]  = '怪獣輸送';
		$this->comCost[$this->comSendSleeper]  = 1500;
		$this->comName[$this->comOffense]      = '攻撃力強化';
		$this->comCost[$this->comOffense]      = 300;
		$this->comName[$this->comDefense]      = '守備力強化';
		$this->comCost[$this->comDefense]      = 300;
		$this->comName[$this->comPractice]     = '総合練習';
		$this->comCost[$this->comPractice]     = 500;
		$this->comName[$this->comPlaygame]     = '交流試合';
		$this->comCost[$this->comPlaygame]     = 500;
		$this->comName[$this->comDoNothing]    = '資金繰り';
		$this->comCost[$this->comDoNothing]    = 0;
		$this->comName[$this->comSell]         = '食料輸出';
		$this->comCost[$this->comSell]         = -100;
		$this->comName[$this->comSellTree]     = '木材輸出';
		$this->comCost[$this->comSellTree]     = -10;
		$this->comName[$this->comMoney]        = '資金援助';
		$this->comCost[$this->comMoney]        = 100;
		$this->comName[$this->comFood]         = '食料援助';
		$this->comCost[$this->comFood]         = -100;
		$this->comName[$this->comLot]          = '宝くじ購入';
		$this->comCost[$this->comLot]          = 300;
		$this->comName[$this->comPropaganda]   = '誘致活動';
		$this->comCost[$this->comPropaganda]   = 1000;
		$this->comName[$this->comBoku]         = '僕の引越し';
		$this->comCost[$this->comBoku]         = 1000;
		$this->comName[$this->comHikidasi]     = '倉庫引き出し';
		$this->comCost[$this->comHikidasi]     = 100;
		$this->comName[$this->comGiveup]       = '島の放棄';
		$this->comCost[$this->comGiveup]       = 0;
		$this->comName[$this->comAutoPrepare]  = '整地自動入力';
		$this->comCost[$this->comAutoPrepare]  = 0;
		$this->comName[$this->comAutoPrepare2] = '地ならし自動入力';
		$this->comCost[$this->comAutoPrepare2] = 0;
		$this->comName[$this->comAutoReclaim]  = '浅瀬埋め立て自動入力';
		$this->comCost[$this->comAutoReclaim]  = 0;
		$this->comName[$this->comAutoDelete]   = '全計画を白紙撤回';
		$this->comCost[$this->comAutoDelete]   = 0;
	}

	function Init() {
		$this->CPU_start = microtime();
		$this->setpubliciable();
		mt_srand($_SERVER['REQUEST_TIME']);
		// 日本時間にあわせる
		// 海外のサーバに設置する場合は次の行にある//をはずす。
		// putenv("TZ=JST-9");
		// 予\定のように\が勝手に追加される
		$this->stripslashes = get_magic_quotes_gpc();
	}

}
