<?php

/*******************************************************************

	箱庭諸島 S.E

	- 同盟管理用ファイル -

	hako-ally.php by SERA - 2012/04/03

*******************************************************************/

require_once 'config.php';
require_once ABSOLUTE_PATH.'hako-init.php';
require_once ABSOLUTE_PATH.'hako-cgi.php';
require_once ABSOLUTE_PATH.'hako-html.php';

$init = new Init();
$THIS_FILE  = $init->baseDir . "/hako-ally.php";

//------------------------------------------------------------
//
//------------------------------------------------------------
class HtmlAlly extends HTML {
	//--------------------------------------------------
	// 初期画面
	//--------------------------------------------------
	function allyTop($hako, $data) {
		global $init;

		echo "<div class='row'>";
		echo "<div class='col-xs-12'>";
		echo "<h1>同盟管理ツール</h1>\n";

		if($init->allyUse) {
			echo <<<END
<input type="button" class="btn btn-default" value="同盟の結成・変更・解散・加盟・脱退はこちらから" onClick="JavaScript:location.replace('{$GLOBALS['THIS_FILE']}?JoinA=1')">
<h2>各同盟の状況</h2>
END;
		}
		$this->allyInfo($hako);

		echo "</div>";
		echo "</div>";

	}
	//--------------------------------------------------
	// 同盟の状況
	//--------------------------------------------------
	function allyInfo($hako, $num = 0) {
		global $init;

		$tag = "";
		$allyNumber = (int)$hako->allyNumber;
		if ( $allyNumber <= 0 ) {
			echo "同盟がありません。";
			return;
		}

		echo <<<END
占有率は、同盟加盟の<b>総人口</b>により算出されたものです。
<div id="IslandView" class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}同盟{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}マーク{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}島の数{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}総人口{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}占有率{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameCommercialScale}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
	<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerPlantScale}{$init->_tagTH}</th>
</tr>
</thead>
END;

		for($i=0; $i<$allyNumber; $i++) {
			if($num && ($i != $hako->idToAllyNumber[$num])) {
				continue;
			}
			$ally = $hako->ally[$i];
			$j = $i + 1;
			$pop = $farm = $factory = $commerce = $mountain = $hatuden = $missiles = 0;

			$num = (int)$ally['number'];
			for($k=0; $k<$num; $k++) {
				$id = $ally['memberId'][$k];
				$island = $hako->islands[$hako->idToNumber[$id]];
				$pop      += $island['pop'];
				$farm     += $island['farm'];
				$factory  += $island['factory'];
				$commerce += $island['commerce'];
				$mountain += $island['mountain'];
				$hatuden  += $island['hatuden'];
			}

			$name      = ($num) ? "{$init->tagName_}{$ally['name']}{$init->_tagName}" : "<a href=\"{$GLOBALS['THIS_FILE']}?AmiOfAlly={$ally['id']}\">{$ally['name']}</a>";
			$pop       = $pop . $init->unitPop;
			$farm      = ($farm <= 0)     ? $init->notHave : $farm * 10 . $init->unitPop;
			$factory   = ($factory <= 0)  ? $init->notHave : $factory * 10 . $init->unitPop;
			$commerce  = ($commerce <= 0) ? $init->notHave : $commerce * 10 . $init->unitPop;
			$mountain  = ($mountain <= 0) ? $init->notHave : $mountain * 10 . $init->unitPop;
			$hatuden   = ($hatuden <= 0)  ? "0kw" : $hatuden * 1000 . kw;

			$ally['comment'] = isset($ally['comment']) ? $ally['comment'] : "";


			echo <<<END
<tbody>
	<tr>
		<th {$init->bgNumberCell} rowspan=2>{$init->tagNumber_}$j{$init->_tagNumber}</th>
		<td {$init->bgNameCell} rowspan=2>{$name}</td>
		<td {$init->bgMarkCell}><b><font color="{$ally['color']}">{$ally['mark']}</font></b></td>
		<td {$init->bgInfoCell}>{$ally['number']}島</td>
		<td {$init->bgInfoCell}>{$pop}</td>
		<td {$init->bgInfoCell}>{$ally['occupation']}%</td>
		<td {$init->bgInfoCell}>{$farm}</td>
		<td {$init->bgInfoCell}>{$factory}</td>
		<td {$init->bgInfoCell}>{$commerce}</td>
		<td {$init->bgInfoCell}>{$mountain}</td>
		<td {$init->bgInfoCell}>{$hatuden}</td>
	</tr>
	<tr>
		<td {$init->bgCommentCell} colspan=9>{$init->tagTH_}<a href="{$GLOBALS['THIS_FILE']}?Allypact={$ally['id']}">{$ally['oName']}</a>：{$init->_tagTH}{$ally['comment']}</td>
	</tr>
<tbody>
END;
		}
		echo <<<END
</table>
</div>
<p>※ 同盟の名前をクリックすると「同盟の情報」欄へ、盟主島の名前だと「コメント変更」欄へ移動します。</p>
END;



	}
	//--------------------------------------------------
	// 同盟の情報
	//--------------------------------------------------
	function amityOfAlly($hako, $data) {
		global $init;

		$num = $data['ALLYID'];
		$ally = $hako->ally[$hako->idToAllyNumber[$num]];
		$allyName = "<FONT COLOR=\"{$ally['color']}\"><B>{$ally['mark']}</B></FONT>{$ally['name']}";

		echo <<<END
<div class='text-center'>
	{$init->tagBig_}{$init->tagName_}{$allyName}{$init->_tagName}の情報{$init->_tagBig}<br>
</div>

<div ID='campInfo'>
END;
		// 同盟状況の表示
		if($ally['number']) {
			$this->allyInfo($hako, $num);
		}
		// メッセージ・盟約の表示
		if($ally['message'] != '') {
			$allyTitle = $ally['title'];
			if($allyTitle == '') {
				$allyTitle = '盟主からのメッセージ';
			}
			$allyMessage = $ally['message'];
			if($init->autoLink) {
				//preg_replace("/(^|[^=\\\"'])(http:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/", "<a href='$2' target='_blank'>$2</a>", $allyMessage);
				$allyMessage = Util::string_autolink($allyMessage);
			}
			echo <<<END
<hr>

<table class="table table-bordered" width="80%">
	<TR><TH {$init->bgTitleCell}>{$init->tagTH_}$allyTitle{$init->_tagTH}</TH></TR>
	<TR><TD {$init->bgCommentCell}><blockquote>$allyMessage</blockquote></TD></TR>
</table>
END;
		}
        // メンバー一覧の表示
		echo <<<END
<HR>
<TABLE class="table table-bordered">
	<TR>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameRank}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}島{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->namePopulation}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameArea}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFunds}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFood}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFarmSize}{$init->_tagTH}</TH>
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameFactoryScale}{$init->_tagTH}</TH>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameCommercialScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->nameMineScale}{$init->_tagTH}</th>
		<th {$init->bgTitleCell}>{$init->tagTH_}{$init->namePowerPlantScale}{$init->_tagTH}</th>
	</TR>
END;
		if(!$ally['number']) {
			echo "<TR><TH colspan=12>所属している島がありません！</TH></TR>";
		}
		foreach ($ally['memberId'] as $id) {
			$number = $hako->idToNumber[$id];
			if(!($number > -1)) continue;
			$island = $hako->islands[$number];
			$money = AllyUtil::aboutMoney($island['money']);
			$farm = $island['farm'];
			$factory = $island['factory'];
			$commerce = $island['commerce'];
			$mountain = $island['mountain'];
			$hatuden = $island['hatuden'];
            $ranking = $number + 1;
			$name = AllyUtil::islandName($island, $hako->ally, $hako->idToAllyNumber);
			if($island['absent']  == 0) {
				$name = "{$init->tagName_}<a href=\"{$init->baseDir}/hako-main.php?Sight={$island['id']}\">{$name}{$init->_tagName}</a>";
			} else {
				$name = "{$init->tagName2_}<a href=\"{$init->baseDir}/hako-main.php?Sight={$island['id']}\">{$name}</a>({$island['absent']}){$init->_tagName2}";
			}
			$farm = ($farm == 0) ? $init->notHave : "{$farm}0$init->unitPop";
			$factory = ($factory == 0) ? $init->notHave : "{$factory}0$init->unitPop";
			$commerce  = ($commerce == 0) ? $init->notHave : "{$commerce}0$init->unitPop";
			$mountain = ($mountain == 0) ? $init->notHave : "{$mountain}0$init->unitPop";
			$hatuden  = ($hatuden == 0) ? "0kw" : "{$hatuden}000kw";

			echo <<<END
<TR>
	<TH {$init->bgNumberCell}>{$init->tagNumber_}$ranking{$init->_tagNumber}</TH>
	<TD {$init->bgNameCell}>$name</TD>
	<TD {$init->bgInfoCell}>{$island['pop']}$init->unitPop</TD>
	<TD {$init->bgInfoCell}>{$island['area']}$init->unitArea</TD>
	<TD {$init->bgInfoCell}>$money</TD>
	<TD {$init->bgInfoCell}>{$island['food']}$init->unitFood</TD>
	<TD {$init->bgInfoCell}>$farm</TD>
	<TD {$init->bgInfoCell}>$factory</TD>
	<TD {$init->bgInfoCell}>$commerce</TD>
	<TD {$init->bgInfoCell}>$mountain</TD>
	<TD {$init->bgInfoCell}>$hatuden</TD>
</TR>
END;
		}
		echo "</TABLE>\n";
    }
	//--------------------------------------------------
	// 同盟コメントの変更
	//--------------------------------------------------
	function tempAllyPactPage($hako, $data) {
		global $init;

		$num = $data['ALLYID'];
		$ally = $hako->ally[$hako->idToAllyNumber[$num]];
		$allyMessage = $ally['message'];

		$allyMessage = str_replace("<br>", "\n", $allyMessage);
		$allyMessage = str_replace("&amp;", "&", $allyMessage);
		$allyMessage = str_replace("&lt;", "<", $allyMessage);
		$allyMessage = str_replace("&gt;", ">", $allyMessage);
		$allyMessage = str_replace("&quot;", "\"", $allyMessage);
		$allyMessage = str_replace("&#039;", "'", $allyMessage);

		$data['defaultPassword'] = isset($data['defaultPassword']) ? $data['defaultPassword'] : "";
		echo <<<END
<DIV align='center'>
{$init->tagBig_}コメント変更（{$init->tagName_}{$ally['name']}{$init->_tagName}）{$init->_tagBig}<br>
</DIV>

<DIV ID='changeInfo'>
<table border=0 width=50%>
<tr>
	<td class="M">
		<FORM action="{$GLOBALS['THIS_FILE']}" method="POST">
			<B>盟主パスワードは？</B><BR>
			<INPUT TYPE="password" NAME="Allypact" VALUE="{$data['defaultPassword']}" SIZE=32 MAXLENGTH=32 class="f" class="form-control">
			<INPUT TYPE="hidden"  NAME="ALLYID" VALUE="{$ally['id']}">
			<INPUT TYPE="submit" VALUE="送信" NAME="AllypactButton"><BR>
			<B>コメント</B><small>(全角{$init->lengthAllyComment}字まで：トップページの「各同盟の状況」欄に表示されます)</small><BR>
			<INPUT TYPE="text" NAME="ALLYCOMMENT"  VALUE="{$ally['comment']}" SIZE=100 MAXLENGTH=50><BR>
			<BR>
			<B>メッセージ・盟約など</B><small>(「同盟の情報」欄の上に表示されます)</small><BR>
			タイトル<small>(全角{$init->lengthAllyTitle}字まで)</small><BR>
			<INPUT TYPE="text" NAME="ALLYTITLE"  VALUE="{$ally['title']}" SIZE=100 MAXLENGTH=50><BR>
			メッセージ<small>(全角{$init->lengthAllyMessage}字まで)</small><BR>
			<TEXTAREA COLS=50 ROWS=16 NAME="ALLYMESSAGE" WRAP="soft">{$allyMessage}</TEXTAREA>
			<BR>
			「タイトル」を空欄にすると『盟主からのメッセージ』というタイトルになります。<BR>
			「メッセージ」を空欄にすると「同盟の情報」欄には何も表示されなくなります。
		</FORM>
	</td>
	</tr>
</table>
</DIV>
END;
	}
	//--------------------------------------------------
	// 同盟の結成・変更・解散・加盟・脱退
	//--------------------------------------------------
	function newAllyTop($hako, $data) {
		global $init;

		$adminMode = 0;

		$jsAllyList      = "";
		$jsAllyIdList    = "";
		$jsAllyMarkList  = "";
		$jsAllyColorList = "";

		$data['defaultPassword'] = isset($data['defaultPassword']) ? $data['defaultPassword'] : "";
		if(AllyUtil::checkPassword("", $data['defaultPassword'])) {
			// 管理者の判定は、お菓子のパスワード、盟主の変更可
			$adminMode = 1;
		} elseif(!$init->allyUse) {
			$this->allyTop($hako, $data);
		}

		$jsIslandList    = "";
		$num = (int)$hako->islandNumber;
		for($i=0; $i<$num; $i++) {
			$name = $hako->islands[$i]['name'];
			$name = preg_replace("/'/", "\'", $name);
			$id = $hako->islands[$i]['id'];
			$jsIslandList .= "island[$id] = '$name';\n";
		}
		$data['defaultID'] = isset($data['defaultID']) ? $data['defaultID'] : "";
		$n = '';
		$n = isset($hako->idToAllyNumber[$data['defaultID']]) ? $hako->idToAllyNumber[$data['defaultID']] : "";

		if($n == '') {
			$allyname = '';
			$defaultMark = $hako->ally[0];
			$defaultAllyId = '';
		} else {
			$allyname = $hako->ally[$n]['name'];
			$allyname = preg_replace("/'/", "\'", $allyname);
			$defaultMark = $hako->ally[$n]['mark'];
			$defaultAllyId = $hako->ally[$n]['id'];
		}
		$defaultMark = '';
		$markList = "";
		foreach ($init->allyMark as $aMark) {
			$s = '';
			if($aMark == $defaultMark) {
				$s = ' selected';
			}
			$markList .= "<option value=\"$aMark\"$s>$aMark</option>\n";
		}

		$hx = array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F');
		$colorList = array('','','','','','','');
		for($i=1; $i<7; $i++) {
			if($n == '') {
				$allycolor[$i] = '0';
			} else {
				$allycolor[$i] = substr($hako->ally[$n]['color'], $i, 1);
			}
			for($j=0; $j<count($hx); $j++) {
				$s = '';
				if($hx[$j] == $allycolor[$i]) {
					$s = ' selected';
				}
				$colorList[$i] .= "<option value=\"{$hx[$j]}\"$s>{$hx[$j]}</option>\n";
			}
		}

		$max = 201;
		if($hako->allyNumber) {
			$jsAllyList      = "var ally = [";
			$jsAllyIdList    = "var allyID = [";
			$jsAllyMarkList  = "var allyMark = [";
			$jsAllyColorList = "var allyColor = [";
			for($i=0; $i<count($hako->ally); $i++) {
				$s = "";
				if($hako->ally[$i]['id'] == $defaultAllyId) $s = ' selected';
				$allyList = "";
				$allyList .= "<option value=\"$i\"$s>{$hako->ally[$i]['name']}</option>\n";
				$jsAllyList .= "'{$hako->ally[$i]['name']}'";
				$jsAllyIdList .= "{$hako->ally[$i]['id']}";
				$jsAllyMarkList .= "'{$hako->ally[$i]['mark']}'";
				$jsAllyColorList .= "[";
				for($j=0; $j<6; $j++) {
					$jsAllyColorList .= '\'' . substr($hako->ally[$i]['color'], $j, 1) . '\'';
					if($j < 5) $jsAllyColorList .= ',';
				}
				$jsAllyColorList .= "]";
				if($i < count($hako->ally)) {
					$jsAllyList .= ",\n";
					$jsAllyIdList .= ",\n";
					$jsAllyMarkList .= ",\n";
					$jsAllyColorList .= ",\n";
				}
				if($max <= $hako->ally[$i]['id']) $max = $hako->ally[$i]['id'] + 1;
			}
			$jsAllyList .= "];\n";
			$jsAllyIdList .= "];\n";
			$jsAllyMarkList .= "];\n";
			$jsAllyColorList .= "];\n";
		}
		$str1 = $adminMode ? '(メンテナンス)' : $init->allyJoinComUse ? '' : '・加盟・脱退';
		$str2 = $adminMode ? '' : 'onChange="colorPack()" onClick="colorPack()"';
		$makeCost = $init->costMakeAlly ? "{$init->costMakeAlly}{$init->unitMoney}" : '無料';
		$keepCost = $init->costKeepAlly ? "{$init->costKeepAlly}{$init->unitMoney}" : '無料';
		$joinCost = isset($init->comCost[$init->comAlly]) ? "{$init->comCost[$init->comAlly]}{$init->unitMoney}" : '無料';
		$joinStr = $init->allyJoinComUse ? '' : "加盟・脱退の際の費用は、{$init->tagMoney_}$joinCost{$init->_tagMoney}です。<BR>";
		$str3 = $adminMode ? "特殊パスワードは？（必須）<BR>
<INPUT TYPE=\"password\" NAME=\"OLDPASS\" VALUE=\"{$data['defaultPassword']}\" SIZE=32 MAXLENGTH=32 class=f><BR>同盟" : "<div class='alert alert-info'><span class='attention'>(注意)</span><BR>
同盟の結成・変更の費用は、{$init->tagMoney_}{$makeCost}{$init->_tagMoney}です。<BR>
また、毎ターン必要とされる維持費は{$init->tagMoney_}$keepCost{$init->_tagMoney}です。<BR>
（維持費は同盟に所属する島で均等に負担することになります）<BR>
{$joinStr}
</div>

あなたの島は？（必須）<BR>
<SELECT NAME=\"ISLANDID\" {$str2}>
{$hako->islandList}
</SELECT><BR>あなた";
		$str0 = ($adminMode || ($init->allyUse == 1)) ? '結成・' : '';
		echo <<<END
<DIV align='center'>
{$init->tagBig_}同盟の{$str0}変更・解散{$str1}{$init->_tagBig}<br>
</DIV>

<DIV ID='changeInfo'>
<table border=0 width=50%><tr><td class="M"><P>
<FORM name="AcForm" action="{$GLOBALS['THIS_FILE']}" method="POST">
{$str3}のパスワードは？（必須）<BR>
<INPUT TYPE="password" NAME="PASSWORD" SIZE="32" MAXLENGTH="32" class="f" class="form-control">
END;
		if($hako->allyNumber) {
			$str4 = $adminMode ? '・結成・変更' : $init->allyJoinComUse ? '' : '・加盟・脱退';
			$str5 = ($adminMode || $init->allyJoinComUse) ? '' : '<INPUT TYPE="submit" VALUE="加盟・脱退" NAME="JoinAllyButton" class="btn btn-default">';
			echo <<<END
<BR>
<BR><B><FONT SIZE=4>［解散{$str4}］</FONT></B>
<BR>どの同盟ですか？<BR>
<SELECT NAME="ALLYNUMBER" onChange="allyPack()" onClick="allyPack()">
{$allyList}
</SELECT>
<BR>
<INPUT TYPE="submit" VALUE="解散" NAME="DeleteAllyButton" class="btn btn-danger">
{$str5}
<BR>
END;
		}
		$str7 = $adminMode ? "盟主島の変更(上のメニューで同盟を選択)<BR> or 同盟の新規作成(上のメニューは無効)<BR><SELECT NAME=\"ALLYID\"><option value=\"$max\">新規作成\n{$hako->islandList}</option></SELECT><BR>" : "<BR><B><FONT SIZE=4>［{$str0}変更］</FONT></B><BR>";
		echo <<<END
<BR>
{$str7}
同盟の名前（変更）<small>(全角{$init->lengthAllyName}字まで)</small><BR>
<INPUT TYPE="text" NAME="ALLYNAME" VALUE="$allyname" SIZE=32 MAXLENGTH=32 class="form-control"><BR>
マーク（変更）<BR>
<SELECT NAME="MARK" onChange="colorPack()" onClick="colorPack()">{$markList}</SELECT>
<br>
<ilayer name="PARENT_CTBL" width="100%" height="100%">
   <layer name="CTBL" width="200"></layer>
   <span id="CTBL"></span>
</ilayer>
マークの色コード（変更）<BR>
<TABLE class="table table-bordered table-condensed">
<TR>
	<TD align='center'>RED</TD>
	<TD align='center'>GREEN</TD>
	<TD align='center'>BLUE</TD>
</TR>
<TR>
	<TD>
		<SELECT NAME="COLOR1" onChange="colorPack()" onClick="colorPack()">{$colorList[1]}</SELECT>
		<SELECT NAME="COLOR2" onChange="colorPack()" onClick="colorPack()">{$colorList[2]}</SELECT>
	</TD>
	<TD>
		<SELECT NAME="COLOR3" onChange="colorPack()" onClick="colorPack()">{$colorList[3]}</SELECT>
		<SELECT NAME="COLOR4" onChange="colorPack()" onClick="colorPack()">{$colorList[4]}</SELECT>
	</TD>
	<TD>
		<SELECT NAME="COLOR5" onChange="colorPack()" onClick="colorPack()">{$colorList[5]}</SELECT>
		<SELECT NAME="COLOR6" onChange="colorPack()" onClick="colorPack()">{$colorList[6]}</SELECT>
	</TD>
</TR>
</TABLE>

<INPUT TYPE="submit" VALUE="結成 (変更)" NAME="NewAllyButton" class="btn btn-primary">
END;
		if(!$adminMode) {
			echo <<<END
<script>
function colorPack() {
	var island = new Array(128);
	{$jsIslandList}
	var a = document.AcForm.COLOR1.value;
	var b = document.AcForm.COLOR2.value;
	var c = document.AcForm.COLOR3.value;
	var d = document.AcForm.COLOR4.value;
	var e = document.AcForm.COLOR5.value;
	var f = document.AcForm.COLOR6.value;
	var mark = document.AcForm.MARK.value;
	var number = document.AcForm.ISLANDID.value;

	str = "#" + a + b + c + d + e + f;

	str = '表示サンプル：『<B><span class="number"><FONT color="' + str +'">' + mark + '</FONT></B>'
		+ island[number] + '島</span>』';

	document.getElementById("CTBL").innerHTML = str;

	return true;
}
function allyPack() {
	{$jsAllyList}
	{$jsAllyMarkList}
	{$jsAllyColorList}
	document.AcForm.ALLYNAME.value = ally[document.AcForm.ALLYNUMBER.value];
	document.AcForm.MARK.value     = allyMark[document.AcForm.ALLYNUMBER.value];
	document.AcForm.COLOR1.value   = allyColor[document.AcForm.ALLYNUMBER.value][0];
	document.AcForm.COLOR2.value   = allyColor[document.AcForm.ALLYNUMBER.value][1];
	document.AcForm.COLOR3.value   = allyColor[document.AcForm.ALLYNUMBER.value][2];
	document.AcForm.COLOR4.value   = allyColor[document.AcForm.ALLYNUMBER.value][3];
	document.AcForm.COLOR5.value   = allyColor[document.AcForm.ALLYNUMBER.value][4];
	document.AcForm.COLOR6.value   = allyColor[document.AcForm.ALLYNUMBER.value][5];
	colorPack();
	return true;
}
END;
		} else {
			echo <<<END

function colorPack() {
	var island = new Array(128);
	{$jsIslandList}
	var a = document.AcForm.COLOR1.value;
	var b = document.AcForm.COLOR2.value;
	var c = document.AcForm.COLOR3.value;
	var d = document.AcForm.COLOR4.value;
	var e = document.AcForm.COLOR5.value;
	var f = document.AcForm.COLOR6.value;
	var mark = document.AcForm.MARK.value;

	var str = "#" + a + b + c + d + e + f;

	str = '表示サンプル：『<B><span class="number"><FONT color="' + str +'">' + mark + '</FONT></B>'
		+ 'さんぷる島</span>』';

	document.getElementById("CTBL").innerHTML = str;

	return true;
}

function allyPack() {
	{$jsAllyList}
	{$jsAllyIdList}
	{$jsAllyMarkList}
	{$jsAllyColorList}
	document.AcForm.ALLYID.value   = allyID[document.AcForm.ALLYNUMBER.value];
	document.AcForm.ALLYNAME.value = ally[document.AcForm.ALLYNUMBER.value];
	document.AcForm.MARK.value     = allyMark[document.AcForm.ALLYNUMBER.value];
	document.AcForm.COLOR1.value   = allyColor[document.AcForm.ALLYNUMBER.value][0];
	document.AcForm.COLOR2.value   = allyColor[document.AcForm.ALLYNUMBER.value][1];
	document.AcForm.COLOR3.value   = allyColor[document.AcForm.ALLYNUMBER.value][2];
	document.AcForm.COLOR4.value   = allyColor[document.AcForm.ALLYNUMBER.value][3];
	document.AcForm.COLOR5.value   = allyColor[document.AcForm.ALLYNUMBER.value][4];
	document.AcForm.COLOR6.value   = allyColor[document.AcForm.ALLYNUMBER.value][5];
	colorPack();
	return true;
}
END;
		}
		echo <<<END
colorPack();
</script>
</form>

</td>
</tr>
</table>
</div>
END;
	}
}
//------------------------------------------------------------
//
//------------------------------------------------------------
class AllySetted extends HtmlAlly {
	// 盟主コメント変更完了
	static function allyPactOK($name) {
		echo "{$name}のコメントを変更しました。\n";
	}
	// 同盟データの再構成
	static function allyDataUp() {
		echo "同盟データを再構成しました。\n";
	}
}
//------------------------------------------------------------
//
//------------------------------------------------------------
class AllyError extends Error {
	// すでにその名前の同盟がある場合
	static function newAllyAlready() {
		global $init;
		echo "その同盟ならすでに結成されています。\n";
	}
	// すでにそのマークの同盟がある場合
	static function markAllyAlready() {
		global $init;
		echo "そのマークはすでに使用されています。\n";
	}
	// 別の同盟を結成している
	static function leaderAlready() {
		global $init;
		echo "盟主は、自分の同盟以外には加盟できません。\n";
	}
	// 別の同盟に加盟している
	static function otherAlready() {
		global $init;
		echo "ひとつの同盟にしか加盟できません。\n";
	}
	// 資金足りず
	static function noMoney() {
		global $init;
		echo "資金不足です(/_<。)\n";
	}
	// IDチェックにひっかかる
	static function wrongAlly() {
		global $init;
		echo "あなたは盟主ではないと思う。\n";
	}
	// 新規で同盟がない場合
	static function newAllyNoName() {
		global $init;
		echo "同盟につける名前が必要です。\n";
	}
	// 管理者以外結成不可
	static function newAllyForbbiden() {
		global $init;
		echo "申し訳ありません、受付を中止しています。\n";
	}

	static function newIslandBadName() {
		global $init;
		echo ",?()&lt;&gt;\$とか入ってたり、変な名前はやめましょう。\n";
	}
}

/**
 *
 */
class MakeAlly {
	/**
	 * 結成・変更メイン
	 * @param  [type] $hako [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	function makeAllyMain($hako, $data) {
		global $init;

		$currentID = $data['ISLANDID'];
		$allyID = isset($data['ALLYID']) ? $data['ALLYID'] : "";
		$currentAnumber = isset($data['ALLYNUMBER']) ? $data['ALLYNUMBER'] : "";
		$allyName = htmlspecialchars($data['ALLYNAME']);
		$allyMark = $data['MARK'];
		$allyColor = "{$data['COLOR1']}{$data['COLOR2']}{$data['COLOR3']}{$data['COLOR4']}{$data['COLOR5']}{$data['COLOR6']}";
		$adminMode = 0;

		// パスワードチェック
		$data['OLDPASS'] = isset($data['OLDPASS']) ? $data['OLDPASS'] : "";
		if(AllyUtil::checkPassword("", $data['OLDPASS'])) {
			$adminMode = 1;
			if($allyID > 200) {
				$max = $allyID;
				if($hako->allyNumber) {
					for($i=0; $i < count($hako->ally); $i++) {
						if($max <= $hako->ally[$i]['id']) {
							$max = $hako->ally[$i]['id'] + 1;
						}
					}
				}
				$currentID = $max;
			} else {
				$currentID = $hako->ally[$currentAnumber]['id'];
			}
		}
		if(!$init->allyUse && !$adminMode) {
			AllyError::newAllyForbbiden();
			return;
		}
		// 同盟名があるかチェック
		if($allyName == '') {
			AllyError::newAllyNoName();
			return;
		}
		// 同盟名が正当かチェック
		if(preg_match("/[,\?\(\)\<\>\$]|^無人|^沈没$/", $allyName)) {
			// 使えない名前
			AllyError::newIslandBadName();
			return;
		}
		// 名前の重複チェック
		$currentNumber = $hako->idToNumber[$currentID];
		if(!($adminMode && ($allyID == '') && ($allyID < 200)) &&
			((AllyUtil::nameToNumber($hako, $allyName) != -1) ||
			((AllyUtil::aNameToId($hako, $allyName) != -1) && (AllyUtil::aNameToId($hako, $allyName) != $currentID)))) {
			// すでに結成ずみ
			AllyError::newAllyAlready();
			return;
		}
		// マークの重複チェック
		if(!($adminMode && ($allyID == '') && ($allyID < 200)) &&
			((AllyUtil::aMarkToId($hako, $allyMark) != -1) && (AllyUtil::aMarkToId($hako, $allyMark) != $currentID))) {
			// すでに使用ずみ
			AllyError::markAllyAlready();
			return;
		}
		// passwordの判定
		$island = $hako->islands[$currentNumber];
		if(!$adminMode && !AllyUtil::checkPassword($island['password'], $data['PASSWORD'])) {
			// password間違い
			Error::wrongPassword();
			return;
		}
		if(!$adminMode && $island['money'] < $init->costMakeAlly) {
			AllyError::noMoney();
			return;
		}
		$n = $hako->idToAllyNumber[$currentID];
		if($n != '') {
			if($adminMode && ($allyID != '') && ($allyID < 200)) {
				$allyMember = $hako->ally[$n]['memberId'];
				$aIsland = $hako->islands[$hako->idToNumber[$allyID]];
				$flag = 0;
				foreach ($allyMember as $id) {
					if($id == $allyID) {
						$flag = 1;
						break;
					}
				}
				if(!$flag) {
					if($aIsland['allyId'][0] == '') {
						$flag = 2;
					}
				}
				if(!$flag) {
					echo "変更できません。\n";
					return;
				}
				$hako->ally[$n]['id']       = $allyID;
				$hako->ally[$n]['oName']    = $aIsland['name'];
				if($flag == 2) {
					$hako->ally[$n]['password'] = $aIsland['password'];
					$hako->ally[$n]['score']    = $aIsland['pop'];
					$hako->ally[$n]['number'] ++;
					array_push($hako->ally[$n]['memberId'], $aIsland['id']);
					array_push($aIsland['allyId'], $aIsland['id']);
				}
			} else {
				// すでに結成ずみなら変更
			}
		} else {
			// 他の島の同盟に入っている場合は、結成できない
			$flag = 0;
			for($i = 0; $i < $hako->allyNumber; $i++) {
				$allyMember = $hako->ally[$i]['memberId'];
				foreach ($allyMember as $id) {
					if($id == $currentID) {
						$flag = 1;
						break;
					}
				}
				if($flag) {
					break;
				}
			}
			if($flag) {
				AllyError::otherAlready();
				return;
			}
			if(($init->allyUse == 2) && !$adminMode && !AllyUtil::checkPassword("", $data['PASSWORD'])) {
				AllyError::newAllyForbbiden();
				return;
			}
			// 新規
			$n = $hako->allyNumber;
			$hako->ally[$n]['id']           = $currentID;
			$memberId = array();
			if($allyID < 200) {
				$hako->ally[$n]['oName']    = $island['name'] . "島";
				$hako->ally[$n]['password'] = $island['password'];
				$hako->ally[$n]['number']   = 1;
				$memberId[0]                = $currentID;
				$hako->ally[$n]['score']    = $island['pop'];
			} else {
				$hako->ally[$n]['oName']    = '';
				$hako->ally[$n]['password'] = AllyUtil::encode($data['PASSWORD']);
				$hako->ally[$n]['number']   = 0;
				$hako->ally[$n]['score']    = 0;
			}
			$hako->ally[$n]['occupation']   = 0;
			$hako->ally[$n]['memberId']     = $memberId;
			$island['allyId']               = $memberId;
			$ext = array(0,);
			$hako->ally[$n]['ext']          = $ext;
			$hako->idToAllyNumber[$currentID] = $n;
			$hako->allyNumber++;
		}

		// 同盟の各種の値を設定
		$hako->ally[$n]['name']     = $allyName;
		$hako->ally[$n]['mark']     = $allyMark;
		$hako->ally[$n]['color']    = "$allyColor";

		// 費用をいただく
		if(!$adminMode) {
			$island['money'] -= $init->costMakeAlly;
		}
		// データ格納先へ
		$hako->islands[$currentNumber] = $island;

		// データ書き出し
		AllyUtil::allyOccupy($hako);
		AllyUtil::allySort($hako);
		$hako->writeAllyFile();

		// トップへ
		$html = new HtmlAlly();
		$html->allyTop($hako, $data);
	}
	//--------------------------------------------------
	// 解散
	//--------------------------------------------------
	function deleteAllyMain($hako, $data) {
		global $init;

		$currentID = $data['ISLANDID'];
		$currentAnumber = $data['ALLYNUMBER'];
		$currentNumber = $hako->idToNumber[$currentID];
		$island = $hako->islands[$currentNumber];
		$n = $hako->idToAllyNumber[$currentID];
		$adminMode = 0;

		// パスワードチェック
		$passCheck = isset($data['OLDPASS']) ? AllyUtil::checkPassword("", $data['OLDPASS']) : false;
		if ($passCheck) {
			$n = $currentAnumber;
			$currentID = $hako->ally[$n]['id'];
			$adminMode = 1;
		} else {
			// passwordの判定
			if(!(AllyUtil::checkPassword($island['password'], $data['PASSWORD']))) {
				// 島 Password 間違い
				Error::wrongPassword();
				return;
			}
			if(!(AllyUtil::checkPassword($hako->ally[$n]['password'], $data['PASSWORD']))) {
				// 同盟 Password 間違い
				Error::wrongPassword();
				return;
			}
			// 念のためIDもチェック
			if($hako->ally[$n]['id'] != $currentID) {
				AllyError::wrongAlly();
				return;
			}
		}
		$allyMember = $hako->ally[$n]['memberId'];

		if($adminMode && (($allyMember[0] != '') || ($n == ''))){
			echo "削除できません。\n";
			return;
		}
		foreach ($allyMember as $id) {
			$island = $hako->islands[$hako->idToNumber[$id]];
			$newId = array();
			foreach ($island['allyId'] as $aId) {
				if($aId != $currentID) {
					array_push($newId, $aId);
				}
			}
			$island['allyId'] = $newId;
		}
		$hako->ally[$n]['dead'] = 1;
		$hako->idToAllyNumber[$currentID] = '';
		$hako->allyNumber --;

		// データ格納先へ
		$hako->islands[$currentNumber] = $island;

		// データ書き出し
		AllyUtil::allyOccupy($hako);
		AllyUtil::allySort($hako);
		$hako->writeAllyFile();

		// トップへ
		$html = new HtmlAlly();
		$html->allyTop($hako, $data);
	}
	//--------------------------------------------------
	// 加盟・脱退
	//--------------------------------------------------
	function joinAllyMain($hako, $data) {
		global $init;

		$currentID = $data['ISLANDID'];
		$currentAnumber = $data['ALLYNUMBER'];
		$currentNumber = $hako->idToNumber[$currentID];
		$island = $hako->islands[$currentNumber];

		// パスワードチェック
		if(!(AllyUtil::checkPassword($island['password'], $data['PASSWORD']))) {
			// password間違い
			Error::wrongPassword();
			return;
		}

		// 盟主チェック
		if($hako->idToAllyNumber[$currentID]) {
			AllyError::leaderAlready();
			return;
		}
		// 複数加盟チェック
		$ally = $hako->ally[$currentAnumber];
		if($init->allyJoinOne && ($island['allyId'][0] != '') && ($island['allyId'][0] != $ally['id'])) {
			AllyError::otherAlready();
			return;
		}

		$allyMember = $ally['memberId'];
		$newAllyMember = array();
		$flag = 0;

		foreach ($allyMember as $id) {
			if(!($hako->idToNumber[$id] > -1)) {
			} elseif($id == $currentID) {
				$flag = 1;
			} else {
				array_push($newAllyMember, $id);
			}
		}

		if($flag) {
			// 脱退
			$newAlly = array();
			foreach ($island['allyId'] as $id) {
				if($id != $ally['id']) {
					array_push($newAlly, $id);
				}
			}
			$island['allyId'] = $newAlly;
			$ally['score'] -= $island['pop'];
			$ally['number'] --;
		} else {
			// 加盟
			array_push($newAllyMember, $currentID);
			array_push($island['allyId'], $ally['id']);
			$ally['score'] += $island['pop'];
			$ally['number'] ++;
		}
		$island['money'] -= $init->comCost[$init->comAlly];
		$ally['memberId'] = $newAllyMember;

		// データ格納先へ
		$hako->islands[$currentNumber] = $island;
		$hako->ally[$currentAnumber] = $ally;

		// データ書き出し
		AllyUtil::allyOccupy($hako);
		AllyUtil::allySort($hako);
		$hako->writeAllyFile();

		// トップへ
		$html = new HtmlAlly();
		$html->allyTop($hako, $data);
	}
	//--------------------------------------------------
	// 盟主コメントモード
	//--------------------------------------------------
	function allyPactMain($hako, $data) {
		$ally = $hako->ally[$hako->idToAllyNumber[$data['ALLYID']]];

		if(AllyUtil::checkPassword($ally['password'], $data['Allypact'])) {
			$ally['comment'] = AllyUtil::htmlEscape($data['ALLYCOMMENT']);
			$ally['title'] = AllyUtil::htmlEscape($data['ALLYTITLE']);
			$ally['message'] = AllyUtil::htmlEscape($data['ALLYMESSAGE'], 1);

			$hako->ally[$hako->idToAllyNumber[$data['ALLYID']]] = $ally;
			// データ書き出し
			$hako->writeAllyFile();

			// 変更成功
			AllySetted::allyPactOK($ally['name']);
		} else {
			// password間違い
			Error::wrongPassword();
			return;
		}
	}
	//--------------------------------------------------
	// 箱庭データとのデータ統合処理
	//--------------------------------------------------
	function allyReComp(&$hako) {
		$rt1 = $this->allyDelete($hako);    // 盟主不在により同盟データから削除
		$rt2 = $this->allyMemberDel($hako);    // 放棄、無人島を同盟データから削除
		$rt3 = $this->allyPopComp($hako);    // 人口の再集計（ターン処理に組み込んでいないため）

		if($rt1 || $rt2 || $rt3) {
			// データ書き出し
			AllyUtil::allyOccupy($hako);
			AllyUtil::allySort($hako);
			$hako->writeAllyFile();

			// メッセージ出力
			AllySetted::allyDataUp();
			return 1;
		}
		return 0;
	}
	//--------------------------------------------------
	// 盟主不在により同盟データから削除
	//--------------------------------------------------
	function allyDelete(&$hako) {
		$count = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$id = $hako->ally[$i]['id'];
			if(!($hako->idToNumber[$id] > -1)) {
				// 配列から削除
				$hako->ally[$i]['dead'] = 1;
				$hako->idToAllyNumber[$id] = '';
				$count ++;
			}
		}

		if($count) {
			$hako->allyNumber -= $count;
			if($hako->allyNumber < 0) {
				$hako->allyNumber = 0;
			}
			// データ格納先へ
			$hako->islands[$currentNumber] = $island;
			return 1;
		}
		return 0;
	}
	//--------------------------------------------------
	// 放棄、無人島を同盟データから削除
	//--------------------------------------------------
	function allyMemberDel(&$hako) {
		$flg = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$count = 0;
			$allyMember = $hako->ally[$i]['memberId'];
			$newAllyMember = array();
			foreach ($allyMember as $id) {
				if($hako->idToNumber[$id] > -1) {
					array_push($newAllyMember, $id);
					$count ++;
				}
			}
			if($count != $hako->ally[$i]['number']) {
				$hako->ally[$i]['memberId'] = $newAllyMember;
				$hako->ally[$i]['number'] = $count;
				$flg = 1;
			}
		}
		if($flg) {
			return 1;
		}
		return 0;
    }

	//--------------------------------------------------
	// 人口の再集計（ターンに組み込めば処理不要）
	//--------------------------------------------------
	function allyPopComp(&$hako) {
		$flg = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$score = 0;
			$allyMember = $hako->ally[$i]['memberId'];
			foreach ($allyMember as $id) {
				$island = $hako->islands[$hako->idToNumber[$id]];
				$score += $island['pop'];
			}
			if($score != $hako->ally[$i]['score']) {
				$hako->ally[$i]['score'] = $score;
				$flg = 1;
			}
		}
		if($flg) {
			return 1;
		}
		return 0;
	}
}
//------------------------------------------------------------
// Ally
//------------------------------------------------------------
class Ally extends AllyIO {
	var $islandList;    // 島リスト
	var $targetList;    // ターゲットの島リスト
	var $defaultTarget;    // 目標補足用ターゲット

	//--------------------------------------------------
	//
	//--------------------------------------------------
	function readIslands(&$cgi) {
		global $init;

		$m = $this->readIslandsFile();
		$this->islandList = $this->getIslandList($cgi->dataSet['defaultID']);

		if($init->targetIsland == 1) {
			// 目標の島 所有の島が選択されたリスト
			$this->targetList = $this->islandList;
		} else {
			// 順位がTOPの島が選択された状態のリスト
			$this->targetList = $this->getIslandList($cgi->dataSet['defaultTarget']);
		}
		return $m;
	}
	//--------------------------------------------------
	// 島リスト生成
	//--------------------------------------------------
	function getIslandList($select = 0) {
		global $init;

		$list = "";
		for($i = 0; $i < $this->islandNumber; $i++) {
			if($init->allyUse) {
				$name = AllyUtil::islandName($this->islands[$i], $this->ally, $this->idToAllyNumber); // 同盟マークを追加
			} else {
				$name = $this->islands[$i]['name'];
			}
			$id   = $this->islands[$i]['id'];

			// 攻撃目標をあらかじめ自分の島にする
			if(empty($this->defaultTarget)) {
				$this->defaultTarget = $id;
			}
			if($id == $select) {
				$s = "selected";
			} else {
				$s = "";
			}
			if($init->allyUse) {
				$list .= "<option value=\"$id\" $s>{$name}</option>\n"; // 同盟マークを追加
			} else {
				$list .= "<option value=\"$id\" $s>{$name}島</option>\n";
			}
		}
		return $list;
	}
}
//------------------------------------------------------------
// AllyIO
//------------------------------------------------------------
class AllyIO {
	var $islandTurn;     // ターン数
	var $islandLastTime; // 最終更新時刻
	var $islandNumber;   // 島の総数
	var $islandNextID;   // 次に割り当てる島ID
	var $islands;        // 全島の情報を格納
	var $idToNumber;
	var $allyNumber;     // 同盟の総数
	var $ally;           // 各同盟の情報を格納
	var $idToAllyNumber; // 同盟

	//--------------------------------------------------
	// 同盟データ読みこみ
	//--------------------------------------------------
	function readAllyFile() {
		global $init;

		$fileName = "{$init->dirName}/{$init->allyData}";
		if(!is_file($fileName)) {
			return false;
		}
		$fp = fopen($fileName, "r");
		AllyUtil::lockr($fp);
		$this->allyNumber   = chop(fgets($fp, READ_LINE));
		if($this->allyNumber == '') {
			$this->allyNumber = 0;
		}
		for($i = 0; $i < $this->allyNumber; $i++) {
			$this->ally[$i] = $this->readAlly($fp);
			$this->idToAllyNumber[$this->ally[$i]['id']] = $i;
		}
		// 加盟している同盟のIDを格納
		for($i = 0; $i < $this->allyNumber; $i++) {
			$member = $this->ally[$i]['memberId'];
			$j = 0;
			foreach ($member as $id) {
				$n = $this->idToNumber[$id];
				if(!($n > -1)) {
					continue;
				}
				array_push($this->islands[$n]['allyId'], $this->ally[$i]['id']);
			}
		}
		AllyUtil::unlock($fp);
		fclose($fp);
		return true;
	}
	//--------------------------------------------------
	// 同盟ひとつ読みこみ
	//--------------------------------------------------
	function readAlly($fp) {
		$name       = chop(fgets($fp, READ_LINE));
		$mark       = chop(fgets($fp, READ_LINE));
		$color      = chop(fgets($fp, READ_LINE));
		$id         = chop(fgets($fp, READ_LINE));
		$ownerName  = chop(fgets($fp, READ_LINE));
		$password   = chop(fgets($fp, READ_LINE));
		$score      = chop(fgets($fp, READ_LINE));
		$number     = chop(fgets($fp, READ_LINE));
		$occupation = chop(fgets($fp, READ_LINE));
		$tmp        = chop(fgets($fp, READ_LINE));
		$allymember = explode(",", $tmp);
		$tmp        = chop(fgets($fp, READ_LINE));
		$ext        = explode(",", $tmp);                // 拡張領域
		$comment    = chop(fgets($fp, READ_LINE));
		$title      = chop(fgets($fp, READ_LINE));
		list($title, $message) = array_pad(explode("<>", $title), 2, NULL);

		return array(
			'name'       => $name,
			'mark'       => $mark,
			'color'      => $color,
			'id'         => $id,
			'oName'      => $ownerName,
			'password'   => $password,
			'score'      => $score,
			'number'     => $number,
			'occupation' => $occupation,
			'memberId'   => $allymember,
			'ext'        => $ext,
			'comment'    => $comment,
			'title'      => $title,
			'message'    => $message,
		);
	}
	//--------------------------------------------------
	// 同盟データ書き込み
	//--------------------------------------------------
	function writeAllyFile() {
		global $init;

		$fileName = "{$init->dirName}/{$init->allyData}";
		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "w");
		AllyUtil::lockw($fp);
		fputs($fp, $this->allyNumber . "\n");

		for($i = 0; $i < $this->allyNumber; $i++) {
			$this->writeAlly($fp, $this->ally[$i]);
		}
		AllyUtil::unlock($fp);
		fclose($fp);
		return true;
	}
	//--------------------------------------------------
	// 同盟ひとつ書き込み
	//--------------------------------------------------
	function writeAlly($fp, $ally) {
		fputs($fp, $ally['name'] . "\n");
		fputs($fp, $ally['mark'] . "\n");
		fputs($fp, $ally['color'] . "\n");
		fputs($fp, $ally['id'] . "\n");
		fputs($fp, $ally['oName'] . "\n");
		fputs($fp, $ally['password'] . "\n");
		fputs($fp, $ally['score'] . "\n");
		fputs($fp, $ally['number'] . "\n");
		fputs($fp, $ally['occupation'] . "\n");
		$allymember = join(",", $ally['memberId']);
		fputs($fp, $allymember . "\n");
		$ext = join(",", $ally['ext']);
		fputs($fp, $ext . "\n");
		if (isset($ally['comment'])) {
			fputs($fp, $ally['comment'] . "\n");
		}
		if ( isset($ally['title']) && isset($ally['message']) ) {
			fputs($fp, $ally['title'] . '<>' . $ally['message'] . "\n");
		}
	}
	//---------------------------------------------------
	// 全島データを読み込む
	//---------------------------------------------------
	function readIslandsFile() {
		global $init;

		$fileName = "{$init->dirName}/hakojima.dat";
		if(!is_file($fileName)) {
			return false;
		}
		$fp = fopen($fileName, "r");
		AllyUtil::lockr($fp);
		$this->islandTurn     = chop(fgets($fp, READ_LINE));
		$this->islandLastTime = chop(fgets($fp, READ_LINE));
		$this->islandNumber   = chop(fgets($fp, READ_LINE));
		$this->islandNextID   = chop(fgets($fp, READ_LINE));

		for($i = 0; $i < $this->islandNumber; $i++) {
			$this->islands[$i] = $this->readIsland($fp);
			$this->idToNumber[$this->islands[$i]['id']] = $i;
			$this->islands[$i]['allyId'] = array();
		}
		AllyUtil::unlock($fp);
		fclose($fp);

		if($init->allyUse) {
			$this->readAllyFile();
		}
		return true;
	}
	//---------------------------------------------------
	// 島ひとつ読み込む
	//---------------------------------------------------
	function readIsland($fp) {
		$name     = chop(fgets($fp, READ_LINE));

		list($name, $owner, $monster, $port, $passenger, $fishingboat, $tansaku, $senkan, $viking) = array_pad(explode(",", $name), 10, NULL);
		$id       = chop(fgets($fp, READ_LINE));
		list($id, $starturn) = explode(",", $id);
		$prize    = chop(fgets($fp, READ_LINE));
		$absent   = chop(fgets($fp, READ_LINE));
		$comment  = chop(fgets($fp, READ_LINE));
		list($comment, $comment_turn) = explode(",", $comment);
		$password = chop(fgets($fp, READ_LINE));
		$point    = chop(fgets($fp, READ_LINE));
		list($point, $pots) = explode(",", $point);
		$eisei    = chop(fgets($fp, READ_LINE));
		list($eisei0, $eisei1, $eisei2, $eisei3, $eisei4, $eisei5) = array_pad(explode(",", $eisei), 6, NULL);
		$zin      = chop(fgets($fp, READ_LINE));
		list($zin0, $zin1, $zin2, $zin3, $zin4, $zin5, $zin6) = array_pad(explode(",", $zin), 7, NULL);
		$item     = chop(fgets($fp, READ_LINE));
		list($item0, $item1, $item2, $item3, $item4, $item5, $item6, $item7, $item8, $item9, $item10, $item11, $item12, $item13, $item14, $item15, $item16, $item17, $item18, $item19) = array_pad(explode(",", $item), 20, NULL);
		$money    = chop(fgets($fp, READ_LINE));
		list($money, $lot, $gold) = array_pad(explode(",", $money), 3, NULL);
		$food     = chop(fgets($fp, READ_LINE));
		list($food, $rice) = explode(",", $food);
		$pop      = chop(fgets($fp, READ_LINE));
		list($pop, $peop) = explode(",", $pop);
		$area     = chop(fgets($fp, READ_LINE));
		$job      = chop(fgets($fp, READ_LINE));
		list($farm, $factory, $commerce, $mountain, $hatuden) = explode(",", $job);
		$power    = chop(fgets($fp, READ_LINE));
		list($taiji, $rena, $fire) = explode(",", $power);
		$tenki    = chop(fgets($fp, READ_LINE));
		$soccer   = chop(fgets($fp, READ_LINE));
		list($soccer, $team, $shiai, $kachi, $make, $hikiwake, $kougeki, $bougyo, $tokuten, $shitten) = array_pad(explode(",", $soccer), 10, NULL);

		return array(
			'name'         => $name,
			'owner'        => $owner,
			'id'           => $id,
			'starturn'     => $starturn,
			'prize'        => $prize,
			'absent'       => $absent,
			'comment'      => $comment,
			'comment_turn' => $comment_turn,
			'password'     => $password,
			'point'        => $point,
			'pots'         => $pots,
			'money'        => $money,
			'lot'          => $lot,
			'gold'         => $gold,
			'food'         => $food,
			'rice'         => $rice,
			'pop'          => $pop,
			'peop'         => $peop,
			'area'         => $area,
			'farm'         => $farm,
			'factory'      => $factory,
			'commerce'     => $commerce,
			'mountain'     => $mountain,
			'hatuden'      => $hatuden,
			'monster'      => $monster,
			'taiji'        => $taiji,
			'rena'         => $rena,
			'fire'         => $fire,
			'tenki'        => $tenki,
			'soccer'       => $soccer,
			'team'         => $team,
			'shiai'        => $shiai,
			'kachi'        => $kachi,
			'make'         => $make,
			'hikiwake'     => $hikiwake,
			'kougeki'      => $kougeki,
			'bougyo'       => $bougyo,
			'tokuten'      => $tokuten,
			'shitten'      => $shitten,
			'land'         => (isset($land)) ? $land : "",
			'landValue'    => (isset($landValue)) ? $landValue : "",
			'command'      => (isset($command)) ? $command : "",
			'port'         => (isset($port)) ? $port : "",
			'ship'         => array('passenger' => $passenger, 'fishingboat' => $fishingboat, 'tansaku' => $tansaku, 'senkan' => $senkan, 'viking' => $viking),
			'eisei'        => array(0 => $eisei0, 1 => $eisei1, 2 => $eisei2, 3 => $eisei3, 4 => $eisei4, 5 => $eisei5),
			'zin'          => array(0 => $zin0, 1 => $zin1, 2 => $zin2, 3 => $zin3, 4 => $zin4, 5 => $zin5, 6 => $zin6),
			'item'         => array(0 => $item0, 1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4, 5 => $item5, 6 => $item6, 7 => $item7, 8 => $item8, 9 => $item9, 10 => $item10, 11 => $item11, 12 => $item12, 13 => $item13, 14 => $item14, 15 => $item15, 16 => $item16, 17 => $item17, 18 => $item18, 19 => $item19),
		);
	}
}

//------------------------------------------------------------
class AllyUtil {
	//---------------------------------------------------
	// 資金の表示
	//---------------------------------------------------
	static function aboutMoney($money = 0) {
		global $init;

		if($init->moneyMode) {
			if($money < 500) {
				return "推定500{$init->unitMoney}未満";
			} else {
				return "推定" . round($money / 1000) . "000" . $init->unitMoney;
			}
		} else {
			return $money . $init->unitMoney;
		}
	}

	//---------------------------------------------------
	// 同盟の占有率の計算
	//---------------------------------------------------
	static function allyOccupy(&$hako) {
		$totalScore = 0;

		for($i=0; $i<$hako->allyNumber; $i++) {
			$totalScore += $hako->ally[$i]['score'];
		}
		for($i=0; $i<$hako->allyNumber; $i++) {
			if($totalScore != 0) {
				$hako->ally[$i]['occupation'] = (int)($hako->ally[$i]['score'] / $totalScore * 100);
			} else {
				$hako->ally[$i]['occupation'] = (int)(100 / $hako->allyNumber);
			}
		}
		return;
	}

	//---------------------------------------------------
	// 人口順にソート(同盟バージョン)
	//---------------------------------------------------
	static function allySort(&$hako) {
		usort($hako->ally, 'scoreComp');
	}

	//---------------------------------------------------
	// 島の名前から番号を算出
	//---------------------------------------------------
	static function nameToNumber($hako, $name) {
		// 全島から探す
		for($i = 0; $i < $hako->islandNumber; $i++) {
			if(strcmp($name, "{$hako->islands[$i]['name']}") == 0) {
				return $i;
			}
		}
		// 見つからなかった場合
		return -1;
    }

	//---------------------------------------------------
	// 同盟の名前からIDを得る
	//---------------------------------------------------
	static function aNameToId($hako, $name) {
		// 全島から探す
		for($i = 0; $i < $hako->allyNumber; $i++) {
			if($hako->ally[$i]['name'] == $name) {
				return $hako->ally[$i]['id'];
			}
		}
		// 見つからなかった場合
		return -1;
	}

	//---------------------------------------------------
	// 同盟のマークからIDを得る
	//---------------------------------------------------
	static function aMarkToId($hako, $mark) {
		// 全島から探す
		for($i = 0; $i < $hako->allyNumber; $i++) {
			if($hako->ally[$i]['mark'] == $mark) {
				return $hako->ally[$i]['id'];
			}
		}
		// 見つからなかった場合
		return -1;
	}

	//---------------------------------------------------
	// エスケープ文字の処理
	//---------------------------------------------------
	static function htmlEscape($s, $mode = 0) {
		$s = htmlspecialchars($s);
		$s = str_replace('"','&quot;', $s);
		$s = str_replace("'","&#039;", $s);

		if ($mode) {
			$s = str_replace("\r\n", "<br>", $s);
			$s = str_replace("\r", "<br>", $s);
			$s = str_replace("\n", "<br>", $s);
			$s = preg_replace("/(<br>){5,}/", "<br>", $s); // 大量改行対策
		}
		return $s;
	}

	//---------------------------------------------------
	// 島名を返す
	//---------------------------------------------------
	static function islandName($island, $ally, $idToAllyNumber) {
		$name = '';
		foreach ($island['allyId'] as $id) {
			$i = $idToAllyNumber[$id];
			$mark  = $ally[$i]['mark'];
			$color = $ally[$i]['color'];
			$name .= '<FONT COLOR="' . $color . '"><B>' . $mark . '</B></FONT> ';
		}
		$name .= $island['name'] . "島";
		return ($name);
	}

	//---------------------------------------------------
	// パスワードチェック
	//---------------------------------------------------
	static function checkPassword($p1 = "", $p2 = "") {
		global $init;

		// nullチェック
		if(empty($p2)) {
			return false;
		}
		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		// マスターパスワードチェック
		if(strcmp($masterPassword, crypt($p2, 'ma')) == 0) {
			return true;
		}
		if(strcmp($p1, AllyUtil::encode($p2)) == 0) {
			return true;
		}
		return false;
	}

	//---------------------------------------------------
	// パスワードのエンコード
	//---------------------------------------------------
	static function encode($s) {
		global $init;

		if($init->cryptOn) {
			return crypt($s, 'h2');
		} else {
			return $s;
		}
	}

	//---------------------------------------------------
	// ファイルをロックする(書き込み時)
	//---------------------------------------------------
	static function lockw($fp) {
		set_file_buffer($fp, 0);
		if(!flock($fp, LOCK_EX)) {
			Error::lockFail();
		}
		rewind($fp);
	}

	//---------------------------------------------------
	// ファイルをロックする(読み込み時)
	//---------------------------------------------------
	static function lockr($fp) {
		set_file_buffer($fp, 0);
		if(!flock($fp, LOCK_SH)) {
			Error::lockFail();
		}
		rewind($fp);
	}

	//---------------------------------------------------
	// ファイルをアンロックする
	//---------------------------------------------------
	static function unlock($fp) {
		flock($fp, LOCK_UN);
	}
}


//------------------------------------------------------------
// メイン処理
//------------------------------------------------------------
class Main {
	public $mode;
	public $dataSet = array();
	//--------------------------------------------------
	// モード分岐
	//--------------------------------------------------
	function execute() {
		global $init;

		$ally = new Ally();
		$cgi = new Cgi();

		$this->parseInputData();
		$cgi->getCookies();

		if(!$ally->readIslands($cgi)) {
			HTML::header($cgi->dataSet);
			Error::noDataFile();
			HTML::footer();
			exit();
		}
		$cgi->setCookies();

		$html = new HtmlAlly();
		$com = new MakeAlly();
		$html->header($cgi->dataSet);
		switch($this->mode) {
			case "JoinA":
				// 同盟の結成・変更・解散・加盟・脱退
				$html->newAllyTop($ally, $this->dataSet);
				break;

			case "newally":
				// 同盟の結成・変更
				$com->makeAllyMain($ally, $this->dataSet);
				break;

			case "delally":
				// 同盟の解散
				$com->deleteAllyMain($ally, $this->dataSet);
				break;

			case "inoutally":
				// 同盟の加盟・脱退
				$com->joinAllyMain($ally, $this->dataSet);
				break;

			case "Allypact":
				// コメントの変更
				$html->tempAllyPactPage($ally, $this->dataSet);
				break;

			case "AllypactUp":
				// コメントの更新
				$com->allyPactMain($ally, $this->dataSet);
				break;

			case "AmiOfAlly":
				// 同盟の情報
				$html->amityOfAlly($ally, $this->dataSet);
				break;

			default:
				// 箱庭データとのデータ統合処理（ターン処理に組み込んでいないため）
				if($com->allyReComp($ally)) {
					break;
				}
				$html->allyTop($ally, $this->dataSet);
			break;
		}
		$html->footer();
	}
	//---------------------------------------------------
	// POST、GETのデータを取得
	//---------------------------------------------------
	function parseInputData() {
		global $init;

		if ( isset($_POST['mode']) ) {
			$this->mode = $_POST['mode'];
		}
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				$value = str_replace(",", "", $value);
				// JcodeConvert($value, 0, 2);
				// $value = HANtoZEN_UTF8($value);

				if($init->stripslashes == true) {
					$this->dataSet["{$name}"] = stripslashes($value);
				} else {
					$this->dataSet["{$name}"] = $value;
				}
			}
			if( isset($this->dataSet['Allypact']) ) {
				$this->mode = "AllypactUp";
			}
			if(array_key_exists('NewAllyButton', $_POST)) {
				$this->mode = "newally";
			}
			if(array_key_exists('DeleteAllyButton', $_POST)) {
				$this->mode = "delally";
			}
			if(array_key_exists('JoinAllyButton', $_POST)) {
				$this->mode = "inoutally";
			}
		}
		if(!empty($_GET['AmiOfAlly'])) {
			$this->mode = "AmiOfAlly";
			$this->dataSet['ALLYID'] = $_GET['AmiOfAlly'];
		}
		if(!empty($_GET['Allypact'])) {
			$this->mode = "Allypact";
			$this->dataSet['ALLYID'] = $_GET['Allypact'];
		}
		if(!empty($_GET['JoinA'])) {
			$this->mode = "JoinA";
			$this->dataSet['ALLYID'] = $_GET['JoinA'];
		}
	}
}

$start = new Main();
$start->execute();

// 人口を比較、同盟一覧用
function scoreComp($x, $y) {
	if($x['dead'] == 1) {
		// 死滅フラグが立っていれば後ろへ
		return +1;
	}
	if($y['dead'] == 1) {
		return -1;
	}
	if($x['score'] == $y['score']) {
		return 0;
	}
	return ($x['score'] > $y['score']) ? -1 : +1;
}
