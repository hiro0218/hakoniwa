<h1><?= $init->title ?> トップ</h1>
<?php
if (DEBUG === true) {
	require_once(VIEWS_PATH . '/debug.php');
}
?>
<h2 class='Turn'>ターン<?= $hako->islandTurn ?></h2>

<?php $this->lastModified($hako);  // 最終更新時刻 ＋ 次ターン更新時刻出力 ?>

<hr>

<div class="row">
	<div class="col-xs-6">
	<?php
		if (!empty($hako->islandList)) {
			require_once(VIEWS_PATH . '/top/my_island.php');
		}
	?>
	</div>
</div>

<hr>
