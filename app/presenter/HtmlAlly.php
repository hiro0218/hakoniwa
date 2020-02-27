<?php
require_once PRESENTER_PATH.'/HTML.php';

class HtmlAlly extends HTML {
	//--------------------------------------------------
	// 初期画面
	//--------------------------------------------------
	function allyTop($hako, $data) {
		global $init;
		$this_file  = $init->baseDir . "/hako-ally.php";

		echo "<div class='row'>";
		echo "<div class='col-xs-12'>";
		echo "<h1>同盟管理ツール</h1>\n";

		if($init->allyUse) {
			echo <<<END
<input type="button" class="btn btn-default" value="同盟の結成・変更・解散・加盟・脱退はこちらから" onClick="JavaScript:location.replace('{$this_file}?JoinA=1')">
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
		$this_file  = $init->baseDir . "/hako-ally.php";

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

			$name      = ($num) ? "{$init->tagName_}{$ally['name']}{$init->_tagName}" : "<a href=\"{$this_file}?AmiOfAlly={$ally['id']}\">{$ally['name']}</a>";
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
		<td {$init->bgInfoCell}>{$ally['number']}{$init->nameSuffix}</td>
		<td {$init->bgInfoCell}>{$pop}</td>
		<td {$init->bgInfoCell}>{$ally['occupation']}%</td>
		<td {$init->bgInfoCell}>{$farm}</td>
		<td {$init->bgInfoCell}>{$factory}</td>
		<td {$init->bgInfoCell}>{$commerce}</td>
		<td {$init->bgInfoCell}>{$mountain}</td>
		<td {$init->bgInfoCell}>{$hatuden}</td>
	</tr>
	<tr>
		<td {$init->bgCommentCell} colspan=9>{$init->tagTH_}<a href="{$this_file}?Allypact={$ally['id']}">{$ally['oName']}</a>：{$init->_tagTH}{$ally['comment']}</td>
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
		$this_file  = $init->baseDir . "/hako-ally.php";

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
		<TH {$init->bgTitleCell}>{$init->tagTH_}{$init->nameSuffix}{$init->_tagTH}</TH>
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
		$this_file  = $init->baseDir . "/hako-ally.php";

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
<div class='text-center'>
	{$init->tagBig_}コメント変更（{$init->tagName_}{$ally['name']}{$init->_tagName}）{$init->_tagBig}<br>
</div>

<DIV ID='changeInfo'>
<table border=0 width=50%>
<tr>
	<td class="M">
		<FORM action="{$this_file}" method="POST">
			<B>盟主パスワードは？</B><BR>
			<INPUT TYPE="password" NAME="Allypact" VALUE="{$data['defaultPassword']}" SIZE=32 MAXLENGTH=32 class="f form-control">
			<INPUT TYPE="hidden"  NAME="ALLYID" VALUE="{$ally['id']}">
			<INPUT TYPE="submit" VALUE="送信" NAME="AllypactButton"><BR>

			<B>コメント</B><small>(全角{$init->lengthAllyComment}字まで：トップページの「各同盟の状況」欄に表示されます)</small>
			<INPUT TYPE="text" NAME="ALLYCOMMENT" VALUE="{$ally['comment']}" MAXLENGTH="50" class="form-control">

			<B>メッセージ・盟約など</B><small>(「同盟の情報」欄の上に表示されます)</small><BR>
			タイトル<small>(全角{$init->lengthAllyTitle}字まで)</small>
			<INPUT TYPE="text" NAME="ALLYTITLE"  VALUE="{$ally['title']}" MAXLENGTH="50" class="form-control">

			メッセージ<small>(全角{$init->lengthAllyMessage}字まで)</small>
			<TEXTAREA COLS=50 ROWS=16 NAME="ALLYMESSAGE" WRAP="soft" class="form-control">{$allyMessage}</TEXTAREA>
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
		$this_file  = $init->baseDir . "/hako-ally.php";

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
		$str1 = ($adminMode ? '(メンテナンス)' : $init->allyJoinComUse) ? '' : '・加盟・脱退';
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
<div class='text-center'>
{$init->tagBig_}同盟の{$str0}変更・解散{$str1}{$init->_tagBig}<br>
</div>

<DIV ID='changeInfo'>
<table border=0 width=50%><tr><td class="M"><P>
<FORM name="AcForm" action="{$this_file}" method="POST">
{$str3}のパスワードは？（必須）<BR>
<INPUT TYPE="password" NAME="PASSWORD" SIZE="32" MAXLENGTH="32" class="f" class="form-control">
END;
		if($hako->allyNumber) {
			$str4 = ($adminMode ? '・結成・変更' : $init->allyJoinComUse) ? '' : '・加盟・脱退';
			$str5 = ($adminMode || $init->allyJoinComUse) ? '' : '<INPUT TYPE="submit" VALUE="加盟・脱退" NAME="JoinAllyButton" class="btn btn-default">';
			echo <<<END
<BR>
<BR><B>［解散{$str4}］</B>
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
		$str7 = $adminMode ? "盟主島の変更(上のメニューで同盟を選択)<BR> or 同盟の新規作成(上のメニューは無効)<BR><SELECT NAME=\"ALLYID\"><option value=\"$max\">新規作成\n{$hako->islandList}</option></SELECT><BR>" : "<BR><B>［{$str0}変更］</B><BR>";
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
