<div id="ChangeInfo">
	<h2>島の名前とパスワードの変更</h2>

	<p class="alert alert-info" role="alert">
		(注意) 名前の変更には<?= $init->costChangeName ?><?= $init->unitMoney ?>かかります。
	</p>

	<form action="<?= $this_file ?>" method="post">
		<div class="form-group">
			<label>どの島ですか？</label>
			<select NAME="ISLANDID" class="form-control">
				<?= $islandList ?>
			</select>
		</div>

		<div class="form-group">
			<label>どんな名前に変えますか？(変更する場合のみ)</label>
			<div class="input-group">
				<input type="text" class="form-control" name="ISLANDNAME" size="32" maxlength="32">
				<span class="input-group-addon"><?= $init->nameSuffix; ?></span>
			</div>
		</div>

		<div class="form-group">
			<label>パスワードは？(必須)</label>
			<input type="password" class="form-control" name="OLDPASS" size="32" maxlength="32" required>
		</div>
		<div class="form-group">
			<label>新しいパスワードは？(変更する時のみ)</label>
			<input type="password" class="form-control" name="PASSWORD" size="32" maxlength="32">
		</div>
		<div class="form-group">
			<label>念のためパスワードをもう一回(変更する時のみ)</label>
			<input type="password" class="form-control" name="PASSWORD2" size="32" maxlength="32">
		</div>
		<div class="form-group">
			<input type="submit" class="btn btn-primary" value="変更する">
		</div>
		<input type="hidden" name="mode" value="change">
	</form>
</div>

