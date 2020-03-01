<h1 class="title">メンテナンスツール</h1>

<?php if (is_dir("{$init->dirName}")): ?>
    <hr>
    <?php $this->dataPrint($data); ?>
<?php else: ?>
    <hr>
    <form action="<?= $this->this_file; ?>" method="post">
        <input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD']; ?>">
        <input type="hidden" name="mode" value="NEW">
        <input type="submit" value="新しいデータを作る">
    </form>
<?php endif; ?>
