<div id="islandMap" class="text-center">
    <div class="table-responsive">
        <table border="1">
            <tr>
                <td>
                    <?php
                    for($y = 0; $y < $init->islandSize; $y++) {
                    	if($y % 2 == 0) {
                    		echo "<img src=\"{$init->imgDir}/land0.gif\" width=\"16\" height=\"32\" alt=\"{$y}\" title=\"{$y}\">";
                    	}
                    	for($x = 0; $x < $init->islandSize; $x++) {
                    		$hako->landString($land[$x][$y], $landValue[$x][$y], $x, $y, $mode, $comStr);
                    	}
                    	if($y % 2 == 1) {
                    		echo "<img src=\"{$init->imgDir}/land0.gif\" width=\"16\" height=\"32\" alt=\"{$y}\" title=\"{$y}\">";
                    	}
                    	echo "<br>";
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <div id="NaviView"></div>
</div>
