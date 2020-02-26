<?php
//---------------------------------------------------
// 災害に関する設定（確率：設定値 x 0.1%）
//---------------------------------------------------

trait Disaster {
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
}
