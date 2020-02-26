<?php
/********************
    外見関係
********************/

trait Tag {
	// 大きい文字
	public $tagBig_       = '<span class="big">';
	public $_tagBig       = '</span>';
	// 島の名前など
	public $tagName_      = '<span class="islName">';
	public $_tagName      = '</span>';
	// 薄くなった島の名前
	public $tagName2_     = '<span class="islName2">';
	public $_tagName2     = '</span>';
	// 順位の番号など
	public $tagNumber_    = '<span class="number">';
	public $_tagNumber    = '</span>';
	// 順位表における見だし
	public $tagTH_        = '<span class="head">';
	public $_tagTH        = '</span>';
	// 開発計画の名前
	public $tagComName_   = '<span class="command">';
	public $_tagComName   = '</span>';
	// 災害
	public $tagDisaster_  = '<span class="disaster">';
	public $_tagDisaster  = '</span>';
	// 順位表、セルの属性
	public $bgTitleCell   = 'class="TitleCell"';   // 順位表見出し
	public $bgNumberCell  = 'class="NumberCell"';  // 順位表順位
	public $bgNameCell    = 'class="NameCell"';    // 順位表島の名前
	public $bgInfoCell    = 'class="InfoCell"';    // 順位表島の情報
	public $bgMarkCell    = 'class="MarkCell"';    // 同盟のマーク
	public $bgCommentCell = 'class="CommentCell"'; // 順位表コメント欄
	public $bgInputCell   = 'class="InputCell"';   // 開発計画フォーム
	public $bgMapCell     = 'class="MapCell"';     // 開発計画地図
	public $bgCommandCell = 'class="CommandCell"'; // 開発計画入力済み計画
}
