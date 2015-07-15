<?php
/*******************************************************************

	箱庭諸島 S.E

	- 初期設定用ファイル -

	config.php by SERA - 2013/07/06

*******************************************************************/

// 開発用の設定
ini_set('display_errors', 1);
set_time_limit(0);
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

define("READ_LINE", 1024);
