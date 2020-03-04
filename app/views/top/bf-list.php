<?php
// Battle Fieldに設定された島がある場合のみ表示
if ($hako->islandNumberNoBF < $hako->islandNumber): ?>

<div ID="IslandView">
    <h2>Battle Fieldの状況</h2>

    <div class="table-responsive">
        <?php $this->islandTable($hako, $hako->islandNumberNoBF, $hako->islandNumber); ?>
    </div>

    <hr>
</div>
<?php endif; ?>
