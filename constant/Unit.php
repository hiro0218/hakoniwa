<?php
//---------------------------------------------------
// 各種単位の設定
//---------------------------------------------------

trait Unit {
	// ●●島
	public $nameSuffix  = "島";

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

	// 保有せず
	public $notHave 	= "保有せず";

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
}
