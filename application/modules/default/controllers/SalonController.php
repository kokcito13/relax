<?php

class SalonController extends Zend_Controller_Action
{

    public function preDispatch()
    {
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->salon = Application_Model_Kernel_Salon::getByIdPage($this->view->idPage);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function mapAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->salon = Application_Model_Kernel_Salon::getByUrlKey($this->view->url_key);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function vidyMassageAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->salon = Application_Model_Kernel_Salon::getByUrlKey($this->view->url_key);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $this->view->massages = Application_Model_Kernel_Massage::getList($this->view->salon->getId());

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }


    public function reviewAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->salon = Application_Model_Kernel_Salon::getByUrlKey($this->view->url_key);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $this->view->comments = Application_Model_Kernel_Comment::getList($this->view->salon->getId(), Application_Model_Kernel_Comment::STATUS_SHOW);

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }
}