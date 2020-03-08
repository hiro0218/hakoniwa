<?php

namespace Presenter;

require_once PRESENTER_PATH . '/HTML.php';

class Config extends \HTML
{
    private $data = [];

    function __construct($hako, $dataSet)
    {
        global $init;

        $this->init = $init;
        $this->this_file = $init->baseDir . "/hako-main.php";
        $this->hako = $hako;
        $this->islandList = $hako->islandList;
        $this->dataSet = $dataSet;
    }

    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getData($key)
    {
        return $this->data[$key] ?? '';
    }

    /**
     * 島の登録と設定
     */
    function renderPage()
    {
        echo $this->render(VIEWS_PATH . '/conf/index.php');
    }

    /**
     * 新しい島を探す
     */
    function discovery()
    {
        echo $this->render(VIEWS_PATH . '/conf/discovery.php');
    }

    /**
     * 島の名前とパスワードの変更
     */
    function changeIslandInfo()
    {
        echo $this->render(VIEWS_PATH . '/conf/change/island-info.php');
    }

    /**
     * オーナー名の変更
     */
    function changeOwnerName()
    {
        echo $this->render(VIEWS_PATH . '/conf/change/owner-name.php');
    }
}
