<?php

class IndexController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->menu = 'main';
        $this->view->blocksArray = array();
        $array = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($array as $key => $value) {
            $this->view->blocksArray[$key] = $value->getContent()->getFields();
        }
    }

    public function indexAction()
    {
        $where = false;
        $this->view->idPage      = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);

        $city = Kernel_City::findCityFromUrl();
        if ($city) {
            $this->view->contentPage = $city->getContent()->getFields();
            $where = 'salons.city_id = '.$city->getId();
        } else {
            $this->view->contentPage = $this->view->page->getContent()->getFields();
        }

        $title       = trim($this->view->contentPage['title']->getFieldText());
        $keywords    = trim($this->view->contentPage['keywords']->getFieldText());
        $description = trim($this->view->contentPage['description']->getFieldText());

        if ($city) {
            if (empty($title)) {
                $title = Kernel_Block::getText('Эротический массаж в', $this->blocksArray).' '.$this->view->contentPage['contentName']->getFieldText().'. '.Kernel_Block::getText('Все салоны эротического массажа.', $this->blocksArray);
            }
            if (empty($keywords)) {
                $keywords = Kernel_Block::getText('Эротический, массаж,', $this->blocksArray).' '.$this->view->contentPage['contentName']->getFieldText().', '.Kernel_Block::getText('салоны.', $this->blocksArray);
            }
            if (empty($description)) {
                $description = Kernel_Block::getText('Все салоны эротического массаж', $this->blocksArray).' '.$this->view->contentPage['contentName']->getFieldText().'. '.Kernel_Block::getText('на одном сайте - месторасположение салонов, девушки, виды массаж и отзывы.', $this->blocksArray);
            }
        }

        $this->view->text        = $this->view->contentPage['content']->getFieldText();
        $this->view->title       = $title;
        $this->view->keywords    = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';

        $this->view->salons = Application_Model_Kernel_Salon::getList('salons.call_price', "DESC", true, true, false, 1, 1, Application_Model_Kernel_Salon::ITEM_ON_PAGE, false, true, $where);
    }
}