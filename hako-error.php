<?php

class Error {

	static function wrongPassword() {
		Util::makeTagMessage("パスワードが違います。", "danger");
	    HTML::footer();
	    exit;
	}

	static function wrongID() {
		Util::makeTagMessage("IDが違います。", "danger");
		HTML::footer();
		exit;
	}

	// hakojima.datがない
	static function noDataFile() {
		Util::makeTagMessage("データファイルが開けません。", "danger");
		HTML::footer();
		exit;
	}

	static function newIslandFull() {
		Util::makeTagMessage("申し訳ありません、島が一杯で登録できません！！", "danger");
		HTML::footer();
		exit;
	}

	static function newIslandNoName() {
		Util::makeTagMessage("島につける名前が必要です。", "danger");
		HTML::footer();
		exit;
	}

	static function newIslandBadName() {
		Util::makeTagMessage(",?()&lt;&gt;\$とか入ってたり、変な名前はやめましょう。", "danger");
		HTML::footer();
		exit;
	}

	static function newIslandAlready() {
		Util::makeTagMessage("その島ならすでに発見されています。", "danger");
		HTML::footer();
		exit;
	}

	static function newIslandNoPassword() {
		Util::makeTagMessage("パスワードが必要です。", "danger");
		HTML::footer();
		exit;
	}

	static function changeNoMoney() {
		Util::makeTagMessage("資金不足のため変更できません", "danger");
		HTML::footer();
		exit;
	}

	static function changeNothing() {
		Util::makeTagMessage("名前、パスワードともに空欄です", "danger");
		HTML::footer();
		exit;
	}

	static function problem() {
		Util::makeTagMessage("問題が発生しました。", "danger");
		HTML::footer();
		exit;
	}

	static function lockFail() {
		Util::makeTagMessage("同時アクセスエラーです。<BR>ブラウザの「戻る」ボタンを押し、<BR>しばらく待ってから再度お試し下さい。", "danger");
		HTML::footer();
		exit;
	}

}
