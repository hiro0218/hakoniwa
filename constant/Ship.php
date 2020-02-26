<?php
//---------------------------------------------------
// 船舶に関する設定
//---------------------------------------------------

trait Ship {
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
}
