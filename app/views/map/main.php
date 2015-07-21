<h1 class="text-center">
    <?= $init->tagName_ ?>「<?= $name ?>」<?= $init->_tagName ?>へようこそ！！
</h1>
<?php
		// 情報
		$this->islandInfo($island, $number, 0);

		// マップ
		$this->islandMap($hako, $island, 0);

		// 他の島へ
?>
<form action="<?= $this_file ?>" method="get" class="text-center">
	<select name="Sight">
        <?= strip_tags($hako->islandList, '<option>') ?>
    </select>
	<input type="submit" value="観光">
</form>

<?php
		// 近況
		$this->islandRecent($island, 0);
