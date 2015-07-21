<h2>自分の島へ</h2>
<form action="<?= $this_file ?>" method="post">
	<div class="form-group">
		<label>あなたの島の名前は？</label>
		<select name="ISLANDID" class="form-control">
			<?= strip_tags($hako->islandList, '<option>') ?>
		</select>
	</div>
	<div class="form-group">
		<label>パスワード</label>
		<input type="password" name="PASSWORD" class="form-control" value="<?= $defaultPassword ?>" size="32" maxlength="32" required>
	</div>

	<input type="hidden" name="mode" value="owner">

	<div class="form-group">
		<label class="radio-inline">
		  <input type="radio" name="DEVELOPEMODE" value="cgi" id="cgi" <?= $radio ?>>
			<label for="cgi">通常モード</label>
		</label>
		<label class="radio-inline">
		  <input type="radio" name="DEVELOPEMODE" value="javascript" id="javascript" <?= $radio2 ?>>
			<label for="javascript">JavaScript高機能モード</label>
		</label>
	</div>

	<div class="form-group">
		<input type="submit" class="btn btn-primary" value="開発しに行く">
	</div>
</form>
