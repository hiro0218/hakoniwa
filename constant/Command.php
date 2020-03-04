<?php
/********************
    コマンド
********************/

trait Command {
	// コマンド分割
	// このコマンド分割だけは、自動入力系のコマンドは設定しないで下さい。
	public $commandDivido =
		[
			'開発,0,10',      // 計画番号00～10
			'建設,11,25',     // 計画番号11～20
			'建設2,26,50',    // 計画番号21～30
			'サッカー,51,60', // 計画番号51～60
			'攻撃1,61,70',    // 計画番号61～80
			'攻撃2,71,80',    // 計画番号61～80
			'運営,81,90'      // 計画番号81～90
		];
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
	public $ax = [0, 1, 1, 1, 0,-1, 0, 1, 2, 2, 2, 1, 0,-1,-1,-2,-1,-1, 0];
	public $ay = [0,-1, 0, 1, 1, 0,-1,-2,-1, 0, 1, 2, 2, 2, 1, 0,-1,-2,-2];

	//////////////////////////////////////////////////

	private function setpubliciable() {
		$this->pointNumber = $this->islandSize * $this->islandSize;
		$this->comList = [
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
		];

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
}
