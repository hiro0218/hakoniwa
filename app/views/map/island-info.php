<div id="islandInfo" class="table-responsive">
    <table class="table table-bordered table-condensed">
        <thead>
            <tr>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameRank ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->namePopulation ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameArea ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameFunds ?><?= $init->_tagTH ?><?= $lots ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameFood ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameUnemploymentRate ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameFarmSize ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameFactoryScale ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameCommercialScale ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->nameMineScale ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->namePowerPlantScale ?><?= $init->_tagTH ?>
                </th>
                <th <?= $init->bgTitleCell ?>>
                    <?= $init->tagTH_ ?><?= $init->namePowerSupplyRate ?><?= $init->_tagTH ?>
                </th>
            </tr>
        </thead>
        <tr>
            <th <?= $init->bgNumberCell ?> rowspan="4">
                <?= $init->tagNumber_ ?><?= $rank ?><?= $init->_tagNumber ?>
            </th>
            <td <?= $init->bgInfoCell ?>>
                <?= $pop ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $area ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $money ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $food ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $unemployed ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $farm ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $factory ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $commerce ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $mountain ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $hatuden ?>
            </td>
            <td <?= $init->bgInfoCell ?>>
                <?= $ene ?>
            </td>
        </tr>
        <tr>
            <th <?= $init->bgTitleCell ?>><?= $init->tagTH_ ?><?= $init->nameWeather ?><?= $init->_tagTH ?></th>
            <td class="TenkiCell"><?= $sora ?></td>
            <th <?= $init->bgTitleCell ?>><?= $init->tagTH_ ?><?= $init->nameMilitaryTechnology ?><?= $init->_tagTH ?></th>
            <td <?= $init->bgInfoCell ?>><?= $arm ?></td>
            <th <?= $init->bgTitleCell ?>><?= $init->tagTH_ ?><?= $init->nameMonsterExterminationNumber ?><?= $init->_tagTH ?></th>
            <td <?= $init->bgInfoCell ?>><?= $taiji ?></td>
            <th <?= $init->bgTitleCell ?>><?= $init->tagTH_ ?><?= $init->nameSatellite ?><?= $init->_tagTH ?></th>
            <td class="ItemCell" colspan="4"><?= $eiseis ?></td>
        </tr>
        <tr>
            <th <?= $init->bgTitleCell ?>><?= $init->tagTH_ ?>ジン<?= $init->_tagTH ?></th>
            <td class="ItemCell" colspan="5"><?= $zins ?></td>
            <th <?= $init->bgTitleCell ?>><?= $init->tagTH_ ?>アイテム<?= $init->_tagTH ?></th>
            <td class="ItemCell" colspan="4"><?= $items ?></td>
        </tr>
        <tr>
            <td colspan="11" <?= $init->bgCommentCell ?>><?= $comment ?></td>
        </tr>
    </table>
</div>
