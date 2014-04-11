<?php
class PageController extends Zend_Controller_Action
{

    public function preDispatch()
    {
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';

        $this->view->menu = $this->view->page->getRoute()->getUrl();
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
        $salons = Application_Model_Kernel_Salon::getList(false, false, true, true, false, 1, false, false, false)->data;
        $cities = Application_Model_Kernel_City::getList();

        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();

        $this->view->cities = $cities;
        $this->view->salons = $salons;
    }

    public function sitemapxmlAction()
    {
        $pages = array();
        $contentPages = Application_Model_Kernel_Page_ContentPage::getList(false, false, false, true, false, 1, false, false, false)->data;
        $city = Kernel_City::findCityFromUrl();
        if ($city) {
            $areas = $city->getAreas();
            $salons = Application_Model_Kernel_Salon::getList(false, false, true, true, false, 1, false, false, false)->data;
            $pages = array_merge($salons, $pages, $areas);
        }

        $pages = array_merge($contentPages, $pages);

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $container = new Zend_Navigation();

        foreach ($pages as $page) {
            if ( get_class($page) === 'Application_Model_Kernel_Area') {
                $uri = $page->getUrl();
            } else {
                $uri = $page->getRoute()->getUrl();
            }
            $container->addPage(Zend_Navigation_Page::factory(array(
                'uri' => $uri,
            )));
        }

        echo $this->view->navigation()->sitemap($container);
    }
}