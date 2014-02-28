<?php

class IndexController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->menu = 'main';
    }

    public function indexAction()
    {
        $this->view->idPage      = (int)$this->_getParam('idPage');
        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();

//        $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, 1, Application_Model_Kernel_Product::ITEM_ON_PAGE, Application_Model_Kernel_Product::ITEM_ON_PAGE, true, false);

        $this->view->text        = $this->view->contentPage['content']->getFieldText();
        $this->view->title       = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords    = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();

        $this->view->salons = Application_Model_Kernel_Salon::getList('salons.id', "DESC", true, true, false, 1, 1, 3, false, true, false);
    }
}