<?php

namespace Presenter;

require_once MODEL_PATH . '/Cgi.php';
require_once MODEL_PATH . '/File/Hako.php';
require_once PRESENTER_PATH . '/HTML.php';
require_once PRESENTER_PATH . '/HtmlTop.php';

class Ranking extends \HTML
{
    function __construct()
    {
        global $init;

        $this->init = $init;
        $this->this_file = $init->baseDir . "/hako-main.php";

        $this->hako = new \Hako();
        $cgi = new \Cgi();

        $result = $this->hako->readIslands($cgi);
        if (!$result) {
            \Util::renderErrorPage();
            exit();
        }
    }

    function renderPage() {
        echo $this->render(VIEWS_PATH.'/category/index.php');
    }

}
