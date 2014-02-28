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

        $this->view->girls = Application_Model_Kernel_Girl::getList($this->view->salon->getId());

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function aboutAction()
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

        $this->view->comments = $this->view->salon->getComments();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function areaAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->page = $this->_getParam('page');

        $this->view->area = Application_Model_Kernel_Area::getByUrl($this->view->url_key);
        $this->view->areaContent = $this->view->area->getContent()->getFields();

        $city = Kernel_City::findCityFromUrl();
        $where = 'salons.area_id = '.$this->view->area->getId().' AND salons.city_id = '.$city->getId();

        $this->view->salons = Application_Model_Kernel_Salon::getList('salons.id', "DESC", true, true, false, 1, $this->view->page, 3, false, true, $where);

        $this->view->title = '';
        $this->view->keywords = '';
        $this->view->description = '';
    }

    public function akciyAction()
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
}