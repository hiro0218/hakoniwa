<?php

namespace Controller;

require_once PRESENTER_PATH . '/Config.php';
require_once MODEL_PATH . '/File/Hako.php';
require_once MODEL_PATH . '/Cgi.php';
require_once MODEL_PATH . '/Make/Core.php';

class Config
{
    private $allow_mode = ['new', 'change', 'ChangeOwnerName'];

    function __construct()
    {
        $this->view();
    }

    function view()
    {
        $hako = new \Hako();
        $cgi = new \Cgi();

        $cgi->parseInputData();
        $cgi->getCookies();

        $result = $hako->readIslands($cgi);
        if (!$result) {
            \Util::renderErrorPage();
            exit();
        }

        // Viewの描画
        $html = new \Presenter\Config($hako, $cgi->dataSet);
        $html->header();

        // フォームの更新処理がある場合
        if (in_array($cgi->mode, $this->allow_mode, true)) {
            $com = new \Make();

            // 新しい島を探す
            if ($cgi->mode === "new") {
                $com->newIsland($hako, $cgi->dataSet);
            }

            // 島の名前とパスワードの変更
            if ($cgi->mode === "change") {
                $com->changeMain($hako, $cgi->dataSet);
            }

            // オーナー名の変更
            if ($cgi->mode === "ChangeOwnerName") {
                $com->changeOwnerName($hako, $cgi->dataSet);
            }
        } else {
            $html->renderPage();
        }

        $html->footer();
    }
}
