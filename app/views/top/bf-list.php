<?php
// Battle Fieldに設定された島がある場合のみ表示
if ($hako->islandNumberNoBF < $hako->islandNumber) {
    echo "<div ID=\"IslandView\">\n";
    echo "<h2>Battle Fieldの状況</h2>\n";

    $this->islandTable($hako, $hako->islandNumberNoBF, $hako->islandNumber);

    echo "<hr>\n";
}
