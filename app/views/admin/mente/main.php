<h1 class="title">メンテナンスツール</h1>

<?php if ($this->exist_log): ?>
    <hr>
    <?php if (!$this->is_backup): ?>
    <h2>現役データ</h2>
    <?php else: ?>
    <h2>バックアップ<?= $suf; ?></h2>
    <?php endif; ?>

    <strong>ターン<?= $this->lastTurn; ?></strong><br>
    <strong>最終更新時間</strong>:<?= $this->timeString; ?><br>
    <strong>最終更新時間(秒数表示)</strong>:1970年1月1日から<?= $this->lastTime ?> 秒<br>
    <form action="<?= $this->this_file; ?>" method="post">
        <input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD']; ?>">
        <input type="hidden" name="mode" value="DELETE">
        <input type="hidden" name="NUMBER" value="<?= $suf; ?>">
        <input type="submit" value="このデータを削除">
    </form>

    <?php if (!$this->is_backup): ?>
    <h3>最終更新時間の変更</h3>
    <form action="<?= $this->this_file ?>" method="post">
        <input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD'] ?>">
        <input type="hidden" name="mode" value="NTIME">
        <input type="hidden" name="NUMBER" value="<?= $suf ?>">
        <input type="text" size="4" name="YEAR" value="<?= $this->time['tm_year'] ?>">年
        <input type="text" size="2" name="MON" value="<?= $this->time['tm_mon'] ?>">月
        <input type="text" size="2" name="DATE" value="<?= $this->time['tm_mday'] ?>">日
        <input type="text" size="2" name="HOUR" value="<?= $this->time['tm_hour'] ?>">時
        <input type="text" size="2" name="MIN" value="<?= $this->time['tm_min'] ?>">分
        <input type="text" size="2" name="NSEC" value="<?= $this->time['tm_sec'] ?>">秒
        <input type="submit" value="変更">
    </form>
    <form action="<?= $this->this_file ?>" method="post">
        <input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD'] ?>">
        <input type="hidden" name="mode" value="STIME">
        <input type="hidden" name="NUMBER" value="<?= $suf ?>">
        1970年1月1日から<input type="text" size="32" name="SSEC" value="<?= $this->lastTime ?>">秒
        <input type="submit" value="秒指定で変更">
    </form>
    <?php endif; ?>

<?php else: ?>
    <hr>
    <form action="<?= $this->this_file; ?>" method="post">
        <input type="hidden" name="PASSWORD" value="<?= $data['PASSWORD']; ?>">
        <input type="hidden" name="mode" value="NEW">
        <input type="submit" value="新しいデータを作る">
    </form>
<?php endif; ?>
