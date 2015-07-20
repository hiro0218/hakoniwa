<h1>島預かり管理ツール</h1>

<form action="<?= $this_file ?>" method="post">
	<h2>変更</h2>
	<select name="ISLANDID" required>
		<?= $hako->islandListNoKP ?>
	</select>
	<input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD'] ?>">
	<input type="hidden" name="mode" value="TOKP">
	<input type="submit" value="管理人預かりに変更">
</form>

<form action="<?= $this_file ?>" method="post">
	<h2>解除</h2>
	<select name="ISLANDID" required>
		<?= $hako->islandListKP ?>
	</select>
	<input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD'] ?>">
	<input type="hidden" name="mode" value="FROMKP">
	<input type="submit" value="管理人預かりを解除">
</form>