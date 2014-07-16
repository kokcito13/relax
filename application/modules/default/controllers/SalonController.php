<?php

class SalonController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->blocksArray = array();
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

        $area = $this->view->salon->getArea();
        $areaContent = $area->getContent()->getFields();
        $city = $this->view->salon->getCity();
        $cityContent = $city->getContent()->getFields();

        if (empty($title)) {
            $title = Kernel_Block::getText('Салон эротического массажа', $this->view->blocksArray);
            $title .= ' '.$this->view->contentPage['name']->getFieldText().' ';
            $title .= Kernel_Block::getText('в', $this->view->blocksArray);
            $title .= ' '.$cityContent['contentName']->getFieldText().'.';
        }
        if (empty($keywords)) {
            $keywords = Kernel_Block::getText('Салон, эротического, массажа,', $this->view->blocksArray);
            $keywords .= ' '.$this->view->contentPage['name']->getFieldText().', ';
            $keywords .= ' '.$cityContent['contentName']->getFieldText().', ';
            $keywords .= ' '.$areaContent['contentName']->getFieldText().' ';
            $keywords .= Kernel_Block::getText('район', $this->view->blocksArray).'.';
        }
        if (empty($description)) {
            $description = Kernel_Block::getText('Виды массажа, отзывы и девушки салона эротического массажа', $this->blocksArray);
            $description .= ' '.$this->view->contentPage['name']->getFieldText().' ';
            $description .= Kernel_Block::getText('в', $this->view->blocksArray);
            $description .= ' '.$cityContent['contentName']->getFieldText().', ';
            $description .= ' '.$areaContent['contentName']->getFieldText().' ';
            $description .= Kernel_Block::getText('район', $this->view->blocksArray).'.';
        }

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';
    }

    public function aboutAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->salon = Application_Model_Kernel_Salon::getByUrlKey($this->view->url_key);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $title = Kernel_Block::getText('Описание заведения.', $this->view->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Описание и дополнительная инофрмация,', $this->view->blocksArray).' '.$description;

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';
    }

    public function mapAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->salon = Application_Model_Kernel_Salon::getByUrlKey($this->view->url_key);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $name = $this->view->contentPage['name']->getFieldText();
        $cityFields =  $this->view->salon->getCity()->getContent()->getFields();
        $cityname = $cityFields['contentName']->getFieldText();
        $areaFields = $this->view->salon->getArea()->getContent()->getFields();
        $areaName = $areaFields['contentName']->getFieldText();

        $title = Kernel_Block::getText('Салон эротического массажа', $this->view->blocksArray).' '.$name.' '.Kernel_Block::getText('на карте  города', $this->view->blocksArray).' '.$cityname;
        $description = Kernel_Block::getText('Точное расположение салона эротического массажа', $this->view->blocksArray).' '.$name.' '.Kernel_Block::getText('на карте  города', $this->view->blocksArray).' '.$cityname.' '.Kernel_Block::getText('в районе', $this->view->blocksArray).' '.$areaName;
        $keywords = Kernel_Block::getText('Салон, эротического, массажа,', $this->view->blocksArray).' '.$name.', '.$cityname.', '.$areaName.', '.Kernel_Block::getText('район, карта, проехать', $this->view->blocksArray).'.';

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';
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

        $title = Kernel_Block::getText('Виды массажа и цены.', $this->view->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Стоимость и описание услуг,', $this->view->blocksArray).' '.$description;

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';
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

        $title = Kernel_Block::getText('Отзывы и рейтинг.', $this->view->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Только актуальные отзывы пользователей салона эротического массажа', $this->view->blocksArray).' '.$description;

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';
    }

    public function areaAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->page = (int)$this->_getParam('page');
        $this->view->word = $this->_getParam('word');
        $this->view->reqion = false;

        $this->view->area = Application_Model_Kernel_Area::getByUrl($this->view->url_key);
        $this->view->areaContent = $this->view->area->getContent()->getFields();

        $city = Kernel_City::findCityFromUrl();
        $cityContent = $city->getContent()->getFields();

        $where = 'salons.area_id = '.$this->view->area->getId().' AND salons.city_id = '.$city->getId();
        $this->view->salons = Application_Model_Kernel_Salon::getList('salons.call_price', "DESC", true, true, false, 1, $this->view->page, Application_Model_Kernel_Salon::ITEM_ON_PAGE, false, true, $where);

        $title = trim($this->view->areaContent['title']->getFieldText());
        $keywords = trim($this->view->areaContent['keywords']->getFieldText());
        $description = trim($this->view->areaContent['description']->getFieldText());

        if (empty($title)) {
            $title = Kernel_Block::getText('Салоны эротического массажа в', $this->view->blocksArray);
            $title .= ' '.$this->view->areaContent['contentName']->getFieldText().' ';
            $title .= Kernel_Block::getText('районе', $this->view->blocksArray);
            $title .= ' '.$cityContent['contentName']->getFieldText().'. ';
            $title .= Kernel_Block::getText('viprelax.net', $this->view->blocksArray);
        }
        if (empty($keywords)) {
            $keywords = Kernel_Block::getText('салоны, эротического, массажа,', $this->view->blocksArray);
            $keywords .= ' '.$this->view->areaContent['contentName']->getFieldText().', ';
            $keywords .= Kernel_Block::getText('район', $this->view->blocksArray);
            $keywords .= ', '.$cityContent['contentName']->getFieldText().'.';
        }
        if (empty($description)) {
            $description = Kernel_Block::getText('Все салоны эротического массажа в', $this->view->blocksArray);
            $description .= ' '.$this->view->areaContent['contentName']->getFieldText().' ';
            $description .= Kernel_Block::getText('районе, реальные девушки, рейтинги и отзывы на сайте vip-relax', $this->view->blocksArray).'.';
        }

        $this->view->text        = $this->view->areaContent['content']->getFieldText();
        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->areaContent['head'])?$this->view->areaContent['head']->getFieldText():'';

        if ($this->view->word !== '0') {
            $this->view->reqion = Application_Model_Kernel_Region::getByUrl($this->view->word);
            $this->view->regionContent = $this->view->reqion->getContent()->getFields();


            $this->view->text        = $this->view->regionContent['content']->getFieldText();
            $this->view->title = $this->view->regionContent['title']->getFieldText();;
            $this->view->keywords = $this->view->regionContent['keywords']->getFieldText();;
            $this->view->description = $this->view->regionContent['description']->getFieldText();;
        }
    }

    public function akciyAction()
    {
        $this->view->url_key = $this->_getParam('url_key');
        $this->view->salon = Application_Model_Kernel_Salon::getByUrlKey($this->view->url_key);
        $this->view->contentPage = $this->view->salon->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $title = Kernel_Block::getText('Акции и скидки.', $this->view->blocksArray).' '.$title;
        $description = Kernel_Block::getText('Акции и скидки,', $this->view->blocksArray).' '.$description;

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';
    }
}