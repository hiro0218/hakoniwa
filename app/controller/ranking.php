<?php

namespace Controller;

require_once PRESENTER_PATH.'/Ranking.php';

class Ranking
{
    function __construct() {
        $this->view();
    }

    function view() {
        $html = new \Presenter\Ranking();
        $html->header();
        $html->renderPage();
        $html->footer();
    }

}
