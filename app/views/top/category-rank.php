<h2>各部門ランキング</h2>
    <div class="table-responsive">
        <table class="table table-condensed">
<?php
		$element   = array('point', 'money', 'food', 'pop', 'area', 'fire', 'pots', 'gold', 'rice', 'peop', 'monster', 'taiji', 'farm', 'factory', 'commerce', 'hatuden', 'mountain', 'team');
		$bumonName = array("総合ポイント", $init->nameFunds, $init->nameFood, $init->namePopulation, $init->nameArea, "軍事力", "成長", "収入", "収穫", "人口増加", "怪獣出現数", "怪獣退治数", "農場", "工場", "商業", "発電所", "採掘場", "サッカー");
		$bumonUnit = array('pts', $init->unitMoney, $init->unitFood, $init->unitPop, $init->unitArea, "機密事項", "pts↑", $init->unitMoney, $init->unitFood, $init->unitPop, $init->unitMonster, $init->unitMonster, "0{$init->unitPop}", "0{$init->unitPop}", "0{$init->unitPop}", "000kw", "0{$init->unitPop}", 'pts');

		for($r = 0; $r < sizeof($element); $r++) {
			$max = 0;
			for($i = 0; $i < $hako->islandNumber; $i++) {
				$island = $hako->islands[$i];
				if(($island[$element[$r]] > $max) && ($island['isBF'] != 1)) {
					$max = $island[$element[$r]];
					$rankid[$r] = $i;
				}
			}
			if($max == 0) {
				if(($r % 6) == 0) {
					echo "<tr>\n";
				}
				echo "<td width=\"15%\" class=\"M\">";
				
				echo "<table class=\"table table-bordered\" style=\"border:0\">\n";
				echo "<thead><tr><th {$init->bgTitleCell}>{$init->tagTH_}{$bumonName[$r]}{$init->_tagTH}</th></tr></thead>\n";
				echo "<tr><td class=\"TenkiCell\">{$init->tagName_}-{$init->_tagName}</td></tr>\n";
				echo "<tr><td class=\"TenkiCell\">-</td></tr>\n";
				echo "</table>";
				
				echo "</td>";

				if(($r % 6) == 5) {
					echo "</tr>\n";
				}

			} else {
				if($r == 5) {
					$max = "";
				}
				if(($r % 6) == 0) {
					echo "<tr>\n";
				}
				
				$island = $hako->islands[$rankid[$r]];
				$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
				echo "<td width=\"15%\" class=\"M\">";
				
				echo "<table class=\"table table-bordered\">\n";
				echo "<thead><tr><th {$init->bgTitleCell}>{$init->tagTH_}{$bumonName[$r]}{$init->_tagTH}</th></tr></thead>\n";
				echo "<tr><td class=\"TenkiCell\"><a href=\"{$this_file}?Sight={$island['id']}\">{$init->tagName_}{$name}{$init->_tagName}</a></td></tr>\n";
				echo "<tr><td class=\"TenkiCell\">{$max}{$bumonUnit[$r]}</td></tr>\n";
				echo "</table>";
				echo "</td>";
				
				if(($r % 6) == 5) {
					echo "</tr>\n";
				}
			}
		}
?>
        </table>
    </div>
<hr>