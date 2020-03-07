<?php

namespace Controller;

require_once MODEL_PATH.'/Turn.php';
require_once PRESENTER_PATH.'/History.php';

class History {

	function __construct() {
        $this->view();
    }

    function view() {
        $html = new \Presenter\History();
        $html->header();
        $html->renderPage();
        $html->footer();
    }
}
