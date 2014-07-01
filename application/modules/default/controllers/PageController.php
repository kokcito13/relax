<?php
class PageController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->menu = 'main';
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();

        $this->view->text = $this->view->contentPage['content']->getFieldText();
    }

    public function popularAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();

        $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, false, false, false, true, ' products.productStatusPopular = 2 ');

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';

        $this->view->menu = $this->view->page->getRoute()->getUrl();
    }

    public function sitemapAction()
    {
//        $salons = Application_Model_Kernel_Salon::getList(false, false, true, true, false, 1, false, false, false)->data;
//        $cities = Application_Model_Kernel_City::getList();

        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();

        $this->view->text = $this->view->contentPage['content']->getFieldText();

//        $this->view->cities = $cities;
//        $this->view->salons = $salons;
    }

    public function sitemapxmlAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        header("Content-Type: text/xml; charset=utf-8");

        $city = Kernel_City::findCityFromUrl();
        if ($city) {
            $content = $city->getContent()->getFields();
            $text = $content['sitemap']->getFieldText();
        } else {
            $settings = Application_Model_Kernel_SiteSetings::getBy();
            $text = $settings->getSitemap();
        }

        echo $text;

        exit(0);
    }

    public function robotstxtAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        header("Content-type:text/plain");

        $city = Kernel_City::findCityFromUrl();
        if ($city) {
            $content = $city->getContent()->getFields();
            $text = $content['robots']->getFieldText();
        } else {
            $settings = Application_Model_Kernel_SiteSetings::getBy();
            $text = $settings->getRobots();
        }

        echo $text;

        exit(0);
    }
}