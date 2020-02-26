<h1>プレゼントツール</h1>
<h2>援助</h2>

<?php
echo <<<END
<form action="{$this_file}" method="post">
	<select name="ISLANDID">$hako->islandList</select>に、
	資金：<input type="text" size="10" name="MONEY" value="0">{$init->unitMoney}、
	食料：<input type="text" size="10" name="FOOD"  value="0">{$init->unitFood}を
	<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
	<input type="hidden" name="mode" value="PRESENT">
	<input type="submit" value="プレゼントする">
</form>

<h2>制裁</h2>
<form action="{$this_file}" method="post" name="InputPlan">
	<select name="ISLANDID" onchange="settarget(this);">$hako->islandList</select>の、(
	<select name="POINTX">
	<option value="0" selected>0</option>
END;
?>
	<?php
	for($i = 1; $i < $init->islandSize; $i++) {
		echo "<option value=\"{$i}\">{$i}</option>\n";
	}
	?>
	</select>,
	<select name="POINTY">
		<option value="0" selected>0</option>
		<?php
		for($i = 1; $i < $init->islandSize; $i++) {
			echo "<option value=\"{$i}\">{$i}</option>\n";
		}
		?>
	</select> )に、

	<select name="PUNISH">
		<option VALUE="0">キャンセル</option>
		<option VALUE="1">地震</option>
		<option VALUE="2">津波</option>
		<option VALUE="3">怪獣</option>
		<option VALUE="4">地盤沈下</option>
		<option VALUE="5">台風</option>
		<option VALUE="6">巨大隕石○</option>
		<option VALUE="7">隕石○</option>
		<option VALUE="8">噴火○</option>
	</select>を
	<input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD'] ?>">
	<input type="hidden" name="mode" value="PUNISH">
	<input type="submit" value="プレゼントしちゃう"><br>
	<input type="button" value="目標捕捉" onClick="javascript: targetopen();">
</form>

<h2>現在のプレゼントリスト</h2>
<?php
for ($i=0; $i < $hako->islandNumber; $i++) {
	$present =&$hako->islands[$i]['present'];
    $name =&$hako->islands[$i]['name'];

    if (!isset($present['item'])) {
        continue;
    }

	if ( $present['item'] == 0 ) {
		if ( $present['px'] != 0 ) {
			$money = $present['px'] . $init->unitMoney;
			echo "{$init->tagName_}{$name}{$init->nameSuffix}{$init->_tagName}に<strong>{$money}</strong>の資金<br>\n";
		}
		if ( $present['py'] != 0 ) {
			$food = $present['py'] . $init->unitFood;
			echo "{$init->tagName_}{$name}{$init->nameSuffix}{$init->_tagName}に<strong>{$food}</strong>の食料<br>\n";
		}
	} elseif ( $present['item'] > 0 ) {
		$items = array ('地震','津波','怪獣','地盤沈下','台風','巨大隕石','隕石','噴火');
		$item = $items[$present['item'] - 1];
		if ( $present['item'] < 9 ) {
			$point = ($present['item'] < 6) ? '' : '(' . $present['px'] . ',' . $present['py'] . ')';
			echo "{$init->tagName_}{$name}{$init->nameSuffix}{$point}{$init->_tagName}に{$init->tagDisaster_}{$item}{$init->_tagDisaster}<br>\n";
		}
	}
}
?>

<script>
var w;
var p = 0;

function settarget(part){
	p = part.options[part.selectedIndex].value;
}

function targetopen() {
	w = window.open("<?= $main_file ?>?target=" + p, "","width=<?= $width ?>,height=<?= $height ?>,scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}
</script>
