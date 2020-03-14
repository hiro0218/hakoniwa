<?php

namespace Presenter;

require_once MODEL_PATH . '/Cgi.php';
require_once MODEL_PATH . '/File/Hako.php';
require_once PRESENTER_PATH . '/HTML.php';
require_once PRESENTER_PATH . '/HtmlTop.php';

class Ranking extends \HTML
{
    public $element   = [];
    public $bumonName = [];
    public $bumonUnit = [];

    function __construct()
    {
        global $init;

        $this->init = $init;
        $this->this_file = $init->baseDir . "/hako-main.php";

        $this->element   = ['point', 'money', 'food', 'pop', 'area', 'fire', 'pots', 'gold', 'rice', 'peop', 'monster', 'taiji', 'farm', 'factory', 'commerce', 'hatuden', 'mountain', 'team'];
        $this->bumonName = ["総合ポイント", $this->init->nameFunds, $this->init->nameFood, $this->init->namePopulation, $this->init->nameArea, "軍事力", "成長", "収入", "収穫", "人口増加", "怪獣出現数", "怪獣退治数", "農場", "工場", "商業", "発電所", "採掘場", "サッカー"];
        $this->bumonUnit = ['pts', $this->init->unitMoney, $this->init->unitFood, $this->init->unitPop, $this->init->unitArea, "機密事項", "pts↑", $this->init->unitMoney, $this->init->unitFood, $this->init->unitPop, $this->init->unitMonster, $this->init->unitMonster, "0{$this->init->unitPop}", "0{$this->init->unitPop}", "0{$this->init->unitPop}", "000kw", "0{$this->init->unitPop}", 'pts'];

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
