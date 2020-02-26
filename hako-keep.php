<?php
/**
 * 箱庭諸島 S.E - 島預かり管理用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once CONTROLLER_PATH.'/admin/keep.php';

$start = new Keep();
$start->execute();
