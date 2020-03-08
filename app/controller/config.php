<?php

namespace Controller;

require_once PRESENTER_PATH . '/Config.php';
require_once MODEL_PATH . '/File/Hako.php';
require_once MODEL_PATH . '/Cgi.php';

class Config
{
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
            HTML::header();
            ErrorHandler::noDataFile();
            HTML::footer();
            exit();
        }

        $html = new \Presenter\Config($hako, $cgi->dataSet);
        $html->header();
        $html->renderPage();
        $html->footer();
    }
}
