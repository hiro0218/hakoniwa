<?php
//---------------------------------------------------
// 軍事に関する設定
//---------------------------------------------------

trait Military {
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
	public $baseLevelUp   = [20, 60, 120, 200]; // ミサイル基地
	public $sBaseLevelUp  = [50, 200]; // 海底基地

	// 防衛施設の最大耐久力
	public $dBaseHP       = 5;
	// 海底防衛施設の最大耐久力
	public $sdBaseHP      = 3;
	// 防衛施設が怪獣に踏まれた時自爆するなら1、しないなら0
	public $dBaseAuto     = 1;

	// 目標の島 所有の島が選択された状態でリストを生成 1、順位がTOPの島なら0
	// ミサイルの誤射が多い場合などに使用すると良いかもしれない
	public $targetIsland  = 1;
}
