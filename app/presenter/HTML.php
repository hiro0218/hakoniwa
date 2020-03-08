<?php
/**
 * 箱庭諸島 S.E - 画面出力用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

class HTML {

	public function render($file) {
		ob_start();
		include $file;
		return ob_get_clean();
	}

	/**
	 * HTML <head>
	 * @return [type] [description]
	 */
	static function header() {
		global $init;
		require_once(VIEWS_PATH.'/header.php');
		require_once(VIEWS_PATH.'/body.php');
	}

	static function head() {
		global $init;
		require_once(VIEWS_PATH.'/header.php');
	}


	/**
	 * HTML <footer>
	 * @return [type] [description]
	 */
	static function footer() {
		global $init;
		require_once(VIEWS_PATH.'/footer.php');
	}

	/**
	 * 最終更新時刻 ＋ 次ターン更新時刻出力
	 * @param  [type] $hako [description]
	 * @return [type]       [description]
	 */
	function lastModified($hako) {
		global $init;
		require_once(VIEWS_PATH.'/lastModified.php');
	}

	/**
	 * [timeToString description]
	 * @param  [type] $t [description]
	 * @return [type]    [description]
	 */
	function timeToString($t) {
		$time = localtime($t, TRUE);
		$time['tm_year'] += 1900;
		$time['tm_mon']++;
		return "{$time['tm_year']}年 {$time['tm_mon']}月 {$time['tm_mday']}日 {$time['tm_hour']}時 {$time['tm_min']}分 {$time['tm_sec']}秒";
	}
}
