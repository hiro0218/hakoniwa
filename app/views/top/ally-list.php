<div class="IslandView">
    <h2>同盟の状況</h2>
    <table class="table table-bordered">
<?php
echo <<<END
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
END;

for($i=0; $i<$hako->allyNumber; $i++) {
    //if($num && ($i != $hako->idToAllyNumber[$num])) {
    //	continue;
    //}
    $ally = $hako->ally[$i];
    $j = $i + 1;

    $pop = $farm = $factory = $commerce = $mountain = $hatuden = $missiles = 0;
    for($k=0; $k<$ally['number']; $k++) {
        $id = $ally['memberId'][$k];
        $island = $hako->islands[$hako->idToNumber[$id]];
        $pop += $island['pop'];
        $farm += $island['farm'];
        $factory += $island['factory'];
        $commerce += $island['commerce'];
        $mountain += $island['mountain'];
        $hatuden += $island['hatuden'];
    }
    $name = /*($num) ? "{$init->tagName_}{$ally['name']}{$init->_tagName}" : */"<a href=\"{$allyfile}?AmiOfAlly={$ally['id']}\">{$ally['name']}</a>";
    $pop = $pop . $init->unitPop;
    $farm = ($farm <= 0) ? $init->notHave : $farm * 10 . $init->unitPop;
    $factory = ($factory <= 0) ? $init->notHave : $factory * 10 . $init->unitPop;
    $commerce = ($commerce <= 0) ? $init->notHave : $commerce * 10 . $init->unitPop;
    $mountain = ($mountain <= 0) ? $init->notHave : $mountain * 10 . $init->unitPop;
    $hatuden = ($hatuden <= 0) ? "0kw" : $hatuden * 1000 . kw;

    echo <<<END
<tr>
    <th {$init->bgNumberCell} rowspan="2">{$init->tagNumber_}$j{$init->_tagNumber}</th>
    <td {$init->bgNameCell} rowspan="2">{$name}</td>
    <td class="TenkiCell"><b><font color="{$ally['color']}">{$ally['mark']}</font></b></td>
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
    <td {$init->bgCommentCell} colspan="9">
        {$init->tagTH_}<a href="{$allyfile}?Allypact={$ally['id']}">{$ally['oName']}</a>：{$init->_tagTH}{$ally['comment']}
    </td>
</tr>
END;
}
?>
</table>
