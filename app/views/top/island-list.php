<div class="IslandView">
    <h2>諸島の状況</h2>
    <p>島の名前をクリックすると、<strong>観光</strong>することができます。</p>
    <div class="table-responsive">
<?php
		$islandListStart = 0;
		$islandListSentinel = 0;

		if ($hako->islandNumber != 0) {
			$islandListStart = $data['islandListStart'];
			if ($init->islandListRange == 0) {
				$islandListSentinel = $hako->islandNumberNoBF;
			} else {
				$islandListSentinel = $islandListStart + $init->islandListRange - 1;
				if ( $islandListSentinel > $hako->islandNumberNoBF ) {
					$islandListSentinel = $hako->islandNumberNoBF;
				}
			}
		}

		if (($islandListStart  != 1) || ($islandListSentinel != $hako->islandNumberNoBF)) {
			for ($i = 1; $i <= $hako->islandNumberNoBF ; $i += $init->islandListRange) {
				$j = $i + $init->islandListRange - 1;
				if ($j > $hako->islandNumberNoBF) {
					$j = $hako->islandNumberNoBF;
				}
				echo " ";
				if ( $i != $islandListStart ) {
					echo "<a href=\"" . $this_file . "?islandListStart=" . $i ."\">";
				}
				echo " [ ". $i . " - " . $j . " ]";

				if ($i != $islandListStart) {
					echo "</a>";
				}
			}
		}
		$islandListStart--;
		$this->islandTable($hako, $islandListStart, $islandListSentinel);
?>
</div>
</div>

<hr>
