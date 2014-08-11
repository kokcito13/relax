<?php

class MassController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->blocksArray = array();
        $array = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($array as $key => $value) {
            $this->view->blocksArray[$key] = $value->getContent()->getFields();
        }

        $this->view->menu = 'main';
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->mass = Application_Model_Kernel_Mass::getByIdPage($this->view->idPage);
        if (Kernel_City::findCityFromUrl()) {
            $url = 'http://'.SITE_NAME.$this->view->mass->getRoute()->getUrl();
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$url);
            exit();
        }
        $this->view->contentPage = $this->view->mass->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function listAction()
    {
        if (Kernel_City::findCityFromUrl()) {
            $url = 'http://'.SITE_NAME.'/massages/';
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$url);
            exit();
        }
        $this->view->page = (int)$this->_getParam('page');
        $this->view->masses = Application_Model_Kernel_Mass::getList(false, false, true, true, false, false, $this->view->page, 15, false, true, false);

        if ($this->view->page == 1) {
            $this->view->headText = "<link rel='next' href='/massages/page".($this->view->page+1).".html' />";
        } else {
            if ($this->view->page == 2)
                $this->view->headText = "<link rel='prev' href='/massages/' />";
            else
                $this->view->headText = "<link rel='prev' href='/massages/page".($this->view->page-1).".html' />";
            $this->view->headText .=  "<link rel='next' href='/massages/page".($this->view->page+1).".html' />
                                    <link rel='canonical' href='/massages/' />";
        }

        $this->view->title = 'Все виды эротического массажа от каталога салонов viprelax.';
        $this->view->keywords = 'виды, эротического, массажа';
        $this->view->description = 'Описания видов и элементов эротического массажа от салонов нашего каталога.';
    }
}