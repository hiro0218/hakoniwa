<script>
function go(obj) {
    if(obj.menulist.value) {
        obj.action = obj.menulist.value;
    }
}
</script>

<h1>管理室入り口</h1>
<h2>管理室へ</h2>
<form method="post" onSubmit="go(this)">
    <b>パスワード：</b>
    <input type="password" size="32" maxlength="32" name="PASSWORD">
    <input type="hidden" name="mode" value="enter">

    <select name="menulist">
    <?php
    $urllistCnt = (int)count($urllist);
    for ( $i = 0; $i < $urllistCnt; $i++ ) {
        if ($i === 0) {
            echo "<option value=\"{$init->baseDir}{$urllist[$i]}\" selected=\"selected\">{$menulist[$i]}</option>\n";
        } else {
            echo "<option value=\"{$init->baseDir}{$urllist[$i]}\">{$menulist[$i]}</option>\n";
        }
    }
    ?>
    </select>
    <input type="submit" value="管理室へ">
</form>
