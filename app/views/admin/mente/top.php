<h1 class="title">メンテナンスツール</h1>

<form action="<?= $this->this_file; ?>" method="post">
<?php if(file_exists("{$init->passwordFile}")): ?>
    <strong>パスワード：</strong>
    <input type="password" size="32" maxlength="32" name="PASSWORD">
    <input type="hidden" name="mode" value="enter">
    <input type="submit" value="メンテナンス">
<?php else: ?>
    <H2>マスタパスワードと特殊パスワードを決めてください。</H2>
    <P>※入力ミスを防ぐために、それぞれ２回ずつ入力してください。</P>
    <B>マスタパスワード：</B><BR>
    (1) <INPUT type="password" name="MPASS1" value="">&nbsp;&nbsp;(2) <INPUT type="password" name="MPASS2" value="$mpass2"><BR>
    <BR>
    <B>特殊パスワード：</B><BR>
    (1) <INPUT type="password" name="SPASS1" value="">&nbsp;&nbsp;(2) <INPUT type="password" name="SPASS2" value="$spass2"><BR>
    <BR>
    <input type="hidden" name="mode" value="setup">
    <INPUT type="submit" value="パスワードを設定する">
<?php endif; ?>
</form>
