<?php
/**
 * 箱庭諸島 S.E - メンテナンス用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/admin.php';
require_once MODELPATH.'/hako-cgi.php';
require_once PRESENTER.'/hako-html.php';
require_once CONTROLLERPATH.'/admin/mente.php';

$start = new Mente();
$start->execute();
