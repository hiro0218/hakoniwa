<?php
/**
 * 箱庭諸島 S.E - 初期設定用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

// set default charset
ini_set('default_charset', 'UTF-8');

// TimeZone settings
date_default_timezone_set('Asia/Tokyo');

// 開発用の設定
ini_set('display_errors', 1);
set_time_limit(0);
error_reporting(E_ALL);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");   // 過去の日付


// 箱庭の設定
$ISLAND_TURN; // ターン数
define("DEBUG", true); 					// true: デバッグ false: 通常
define("LOCK_RETRY_COUNT", 10);			// ファイルロック処理のリトライ回数
define("LOCK_RETRY_INTERVAL", 1000);	// 再ロック処理実施までの時間(ミリ秒)。最低でも500くらいを指定
define("READ_LINE", 1024);


// PATHの定数
define('DOC_ROOT', __DIR__.DIRECTORY_SEPARATOR);  // ドキュメントルート パス
define('CONSTANT_PATH', realpath(__DIR__.'/constant/').DIRECTORY_SEPARATOR);
define('APP_PATH', realpath(__DIR__.'/app/').DIRECTORY_SEPARATOR);  // アプリケーションディレクトリ パス
define('CONTROLLER_PATH', realpath(APP_PATH.'/controller/').DIRECTORY_SEPARATOR);  // コントローラ パス
define('HELPER_PATH', realpath(APP_PATH.'/helper/').DIRECTORY_SEPARATOR);  // ヘルパー パス
define('MODEL_PATH', realpath(APP_PATH.'/model/').DIRECTORY_SEPARATOR);  // モデル パス
define('PRESENTER_PATH', realpath(APP_PATH.'/presenter/').DIRECTORY_SEPARATOR);  // プレゼンター パス
define('VIEWS_PATH', realpath(APP_PATH.'/views/').DIRECTORY_SEPARATOR);  //


// 共通
require_once DOC_ROOT.'Init.php';
require_once APP_PATH.'/helper/util.php';

$init  = new Init();
