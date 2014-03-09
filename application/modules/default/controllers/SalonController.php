<?php

class SalonController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $array = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($array as $key => $value) {
            $this->view->blocksArray[$key] = $value->getContent()->getFields();
        }
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

        $title = Kernel_Block::getText('Описание заведения.', $this->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Описание и дополнительная инофрмация,', $this->blocksArray).' '.$description;

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

        $title = Kernel_Block::getText('Расположение на карте.', $this->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Расположение на карте,', $this->blocksArray).' '.$description;

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

        $title = Kernel_Block::getText('Виды массажа и цены.', $this->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Стоимость и описание услуг,', $this->blocksArray).' '.$description;

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
        $description = $this->view->contentPage['name']->getFieldText();

        $title = Kernel_Block::getText('Отзывы и рейтинг.', $this->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Только актуальные отзывы пользователей салона эротического массажа', $this->blocksArray).' '.$description;

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function areaAction()
    {
        $word = false;
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->page = (int)$this->_getParam('page');
        $this->view->word = $this->_getParam('word');
        if ($this->view->word !== '0') {
            $word = '%'.$this->view->word.'%';
        }

        $this->view->area = Application_Model_Kernel_Area::getByUrl($this->view->url_key);
        $this->view->areaContent = $this->view->area->getContent()->getFields();

        $city = Kernel_City::findCityFromUrl();
        $where = 'salons.area_id = '.$this->view->area->getId().' AND salons.city_id = '.$city->getId();

        $this->view->salons = Application_Model_Kernel_Salon::getList('salons.id', "DESC", true, true, $word, 1, $this->view->page, Application_Model_Kernel_Salon::ITEM_ON_PAGE, false, true, $where);

        $title = $this->view->areaContent['title']->getFieldText();
        $keywords = $this->view->areaContent['keywords']->getFieldText();
        $description = $this->view->areaContent['description']->getFieldText();

        $this->view->text        = $this->view->areaContent['content']->getFieldText();
        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function akciyAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->salon = Application_Model_Kernel_Salon::getByUrlKey($this->view->url_key);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $title = Kernel_Block::getText('Акции и скидки.', $this->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Акции и скидки,', $this->blocksArray).' '.$description;

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }
}