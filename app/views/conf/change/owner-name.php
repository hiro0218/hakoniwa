<div id="ChangeOwnerName">
	<h2>オーナー名の変更</h2>
	<form action="<?= $this->this_file ?>" method="post">
		<div class="form-group">
			<label>どの島ですか？</label>
			<select name="ISLANDID" class="form-control">
				<?= $this->islandList ?>
			</select>
		</div>
		<div class="form-group">
			<label>新しいオーナー名は？</label>
			<input type="text" name="OWNERNAME" class="form-control" size="32" maxlength="32">
		</div>
		<div class="form-group">
			<label>パスワードは？</label>
			<input type="password" name="OLDPASS" class="form-control" size="32" maxlength="32" required>
		</div>
		<div class="form-group">
			<input type="submit" class="btn btn-primary" value="変更する">
		</div>
		<input type="hidden" name="mode" value="ChangeOwnerName">
	</form>
</div>
