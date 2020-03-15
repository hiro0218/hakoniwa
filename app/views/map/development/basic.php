<h1 class="text-center">
    <?= $init->tagName_ ?><?= $name ?><?= $init->_tagName ?>開発計画
</h1>
<?php $this->islandInfo($island, $number, 1); ?>
<div class="table-responsive">
<table class="table table-bordered">
    <tr>
        <td <?= $init->bgInputCell ?>>
            <div class="text-center">
                <form action="<?= $this_file ?>" method="post" name="InputPlan">
                    <input type="hidden" name="mode" value="command">
                    <input type="hidden" name="ISLANDID" value="<?= $island['id'] ?>">
                    <input type="hidden" name="PASSWORD" value="<?= $data['defaultPassword'] ?>">
                    <input type="submit" class="btn btn-primary" value="計画送信">

                    <hr>

                    <b>計画番号</b>
                    <select name="NUMBER">
                    <?php
                    for($i = 0; $i < $init->commandMax; $i++) {
                        $j = $i + 1;
                        echo "<option value=\"{$i}\">{$j}</option>";
                    }
                    ?>
                    </select>

                    <hr>

                    <b>開発計画</b><br>
                    <select name="COMMAND">
                    <?php
                    // コマンド
                    $comCnt = count($init->comList);
                    for($i = 0;  $i < $comCnt; $i++) {
                        $kind = $init->comList[$i];
                        $cost = $init->comCost[$kind];
                        $s = '';

                        if($cost == 0) {
                            $cost = '無料';
                        } elseif($cost < 0) {
                            $cost = - $cost;
                            if($kind == $init->comSellTree) {
                                $cost .= $init->unitTree;
                            } else {
                                $cost .= $init->unitFood;
                            }
                        } else {
                            $cost .= $init->unitMoney;
                        }
                        if ( isset($data['defaultKind']) ) {
                            if($kind == $data['defaultKind']) {
                                $s = 'selected';
                            }
                        }

                        echo "<option value=\"{$kind}\" {$s}>{$init->comName[$kind]} ({$cost})</option>\n";
                    }
                    ?>
                    </select>

                    <hr>

                    <b>座標</b>(
                    <select name="POINTX">
                    <?php
                    for($i = 0; $i < $init->islandSize; $i++) {

                        if ( isset($data['defaultX']) ) {
                            if($i == $data['defaultX']) {
                                echo "<option value=\"{$i}\" selected>{$i}</option>\n";
                            } else {
                                echo "<option value=\"{$i}\">{$i}</option>\n";
                            }
                        } else {
                            echo "<option value=\"{$i}\">{$i}</option>\n";
                        }
                    }
                    ?>
                    </select>,
                    <select name="POINTY">
                    <?php
                    for($i = 0; $i < $init->islandSize; $i++) {
                        if ( isset($data['defaultY']) ) {
                            if($i == $data['defaultY']) {
                                echo "<option value=\"{$i}\" selected>{$i}</option>\n";
                            } else {
                                echo "<option value=\"{$i}\">{$i}</option>\n";
                            }
                        } else {
                            echo "<option value=\"{$i}\">{$i}</option>\n";
                        }

                    }
                    ?>
                    </select>)

                    <hr>

                    <b>数量</b>
                    <select name="AMOUNT">
                    <?php
                    for($i = 0; $i < 100; $i++) {
                        echo "<option value=\"{$i}\">{$i}</option>\n";
                    }
                    ?>
                    </select>

                    <hr>

                    <b>目標の島</b><br>
                    <select name="TARGETID" onchange="settarget(this);">
                        <?= strip_tags($hako->islandList, '<option>') ?>
                    </select>

                    <input type="button" value="目標捕捉" onClick="targetopen();">

                    <hr>

                    <b>動作</b><br>
                    <label class="radio-inline">
                        <input type="radio" name="COMMANDMODE" id="insert" value="insert" checked>
                        <label for="insert">挿入</label>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="COMMANDMODE" id="write" value="write">
                        <label for="write">上書き</label><BR>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="COMMANDMODE" id="delete" value="delete">
                        <label for="delete">削除</label>
                    </label>

                    <hr>

                    <input type="hidden" name="DEVELOPEMODE" value="cgi">
                    <input type="submit" class="btn btn-primary" value="計画送信">

                </form>

                <p>ミサイル発射上限数[<b> <?= $island['fire'] ?> </b>]発</p>
            </div>
        </td>

        <td <?= $init->bgMapCell ?>>
            <?php $this->islandMap($hako, $island, 1); // 島の地図、所有者モード ?>
        </td>

        <td <?= $init->bgCommandCell ?>>
        <?php
            $command = $island['command'];
            $commandMax = $init->commandMax;
            for($i = 0; $i < $commandMax; $i++) {
                $this->tempCommand($i, $command[$i], $hako);
            }
        ?>
        </td>
    </tr>
</table>
</div>

<hr>

<div id='CommentBox'>
    <h2>コメント更新</h2>
    <form action="<?= $this_file ?>" method="post">
        <div class="row">
            <div class="col-xs-12">
                <div class="input-group">
                    <input type="text" name="MESSAGE" class="form-control" size="80" value="<?= $island['comment'] ?>" placeholder="コメントする">
                    <input type="hidden" name="PASSWORD" value="<?= $data['defaultPassword'] ?>">
                    <input type="hidden" name="mode" value="comment">
                    <input type="hidden" name="ISLANDID" value="<?= $island['id'] ?>">
                    <input type="hidden" name="DEVELOPEMODE" value="cgi">
                    <span class="input-group-btn">
                        <input type="submit" class="btn btn-primary" value="コメント更新">
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
var w;
var p = <?= $defaultTarget ?>;

function ps(x, y) {
    document.InputPlan.POINTX.options[x].selected = true;
    document.InputPlan.POINTY.options[y].selected = true;
    return true;
}

function ns(x) {
    document.InputPlan.NUMBER.options[x].selected = true;
    return true;
}

function settarget(part){
    p = part.options[part.selectedIndex].value;
}
function targetopen() {
    w = window.open("<?= $this_file ?>?target=" + p, "","width=<?= $width ?>,height=<?= $height ?>,scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}
</script>
