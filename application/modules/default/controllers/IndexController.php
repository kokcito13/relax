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
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);

        $city = Kernel_City::findCityFromUrl();
        if ($city) {
            $this->view->contentPage = $city->getContent()->getFields();
        } else {
            $this->view->contentPage = $this->view->page->getContent()->getFields();
        }

        $this->view->text        = $this->view->contentPage['content']->getFieldText();
        $this->view->title       = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords    = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();

        $this->view->salons = Application_Model_Kernel_Salon::getList('salons.id', "DESC", true, true, false, 1, 1, Application_Model_Kernel_Salon::ITEM_ON_PAGE, false, true, false);
    }
}