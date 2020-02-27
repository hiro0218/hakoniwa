<?php
require_once HELPER_PATH.'/message/success.php';
require_once MODEL_PATH.'/Make/Core.php';
require_once PRESENTER_PATH.'/HtmlMapJS.php';

class MakeJS extends Make {

	//---------------------------------------------------
	// コマンドモード
	//---------------------------------------------------
	function commandMain($hako, $data) {
		global $init;

		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];

		// パスワード
		if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
			// password間違い
			HakoError::wrongPassword();
			return;
		}
		// モードで分岐
		$command = $island['command'];
		$comary = explode(" " , $data['COMARY']);

		for($i = 0; $i < $init->commandMax; $i++) {
			$pos = $i * 5;
			$kind   = $comary[$pos];
			$x      = $comary[$pos + 1];
			$y      = $comary[$pos + 2];
			$arg    = $comary[$pos + 3];
			$target = $comary[$pos + 4];

			// コマンド登録
			if($kind == 0) {
				$kind = $init->comDoNothing;
			}
			$command[$i] = array (
				'kind'   => $kind,
				'x'      => $x,
				'y'      => $y,
				'arg'    => $arg,
				'target' => $target
			);
		}
		Success::commandAdd();

		// データの書き出し
		$island['command'] = $command;
		$hako->islands[$num] = $island;
		$hako->writeIslandsFile($island['id']);

		// owner modeへ
		$html = new HtmlMapJS();
		$html->owner($hako, $data);
	}
}
