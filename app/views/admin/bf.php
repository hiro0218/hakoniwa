<h1>BattleFields管理ツール</h1>

<form action="<?= $this_file ?>" method="post">
	<h2>通常の島からBattleFieldに変更</h2>
	<select name="ISLANDID">
		<?= $hako->islandListNoBF ?>
	</select>
	<input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD'] ?>">
	<input type="hidden" name="mode" value="TOBF">
	<input type="submit" value="BattleFieldに変更">
</form>

<form action="<?= $this_file ?>" method="post">
	<h2>BattleFieldから通常の島に変更</h2>
	<select name="ISLANDID">
		<?= $hako->islandListBF ?>
	</select>
	<input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD'] ?>">
	<input type="hidden" name="mode" value="FROMBF">
	<input type="submit" value="通常の島に変更">
</form>