<?php
/**
 * 箱庭諸島 S.E
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

class Error {

	static function wrongPassword() {
		Util::makeTagMessage("パスワードが違います。", "danger");
	}

	static function wrongID() {
		Util::makeTagMessage("IDが違います。", "danger");
	}

	// hakojima.datがない
	static function noDataFile() {
		Util::makeTagMessage("データファイルが開けません。", "danger");
	}

	static function newIslandFull() {
		Util::makeTagMessage("申し訳ありません、島が一杯で登録できません！！", "danger");
	}

	static function newIslandNoName() {
		Util::makeTagMessage("島につける名前が必要です。", "danger");
	}

	static function newIslandBadName() {
		Util::makeTagMessage(",?()&lt;&gt;\$とか入ってたり、変な名前はやめましょう。", "danger");
	}

	static function newIslandAlready() {
		Util::makeTagMessage("その島ならすでに発見されています。", "danger");
	}

	static function newIslandNoPassword() {
		Util::makeTagMessage("パスワードが必要です。", "danger");
	}

	static function changeNoMoney() {
		Util::makeTagMessage("資金不足のため変更できません", "danger");
	}

	static function changeNothing() {
		Util::makeTagMessage("名前、パスワードともに空欄です", "danger");
	}

	static function problem() {
		Util::makeTagMessage("問題が発生しました。", "danger");
	}

	static function lockFail() {
		Util::makeTagMessage("同時アクセスエラーです。\nブラウザの「戻る」ボタンを押し、しばらく待ってから再度お試し下さい。", "danger");
	}

	static function wrongMasterPassword(){
		Util::makeTagMessage("マスタパスワードが入力されていないか間違っています。", "danger");
	}

	static function wrongSpecialPassword(){
		Util::makeTagMessage("特殊パスワードが入力されていないか間違っています。", "danger");
	}

	function __destruct() {
		HTML::footer();
		exit();
	}

	/**
	 * 同盟
	 */
	// すでにその名前の同盟がある場合
	static function newAllyAlready() {
		Util::makeTagMessage("その同盟ならすでに結成されています。", "danger");
	}
	// すでにそのマークの同盟がある場合
	static function markAllyAlready() {
		Util::makeTagMessage("そのマークはすでに使用されています。", "danger");
	}
	// 別の同盟を結成している
	static function leaderAlready() {
		Util::makeTagMessage("盟主は、自分の同盟以外には加盟できません。", "danger");
	}
	// 別の同盟に加盟している
	static function otherAlready() {
		Util::makeTagMessage("ひとつの同盟にしか加盟できません。", "danger");
	}
	// 資金足りず
	static function noMoney() {
		Util::makeTagMessage("資金不足です(/_<。)", "danger");
	}
	// IDチェックにひっかかる
	static function wrongAlly() {
		Util::makeTagMessage("あなたは盟主ではないと思う。", "danger");
	}
	// 新規で同盟がない場合
	static function newAllyNoName() {
		Util::makeTagMessage("同盟につける名前が必要です。", "danger");
	}
	// 管理者以外結成不可
	static function newAllyForbbiden() {
		Util::makeTagMessage("申し訳ありません、受付を中止しています。", "danger");
	}

}
