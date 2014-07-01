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

        $this->view->masses = Application_Model_Kernel_Mass::getList(false, false, true, true, false, false, $this->view->page, 15, false, true, false);

        $this->view->title = 'Все виды эротического массажа от каталога салонов viprelax.';
        $this->view->keywords = 'виды, эротического, массажа';
        $this->view->description = 'Описания видов и элементов эротического массажа от салонов нашего каталога.';
    }
}