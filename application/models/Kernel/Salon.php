<?php

class Application_Model_Kernel_Salon extends Application_Model_Kernel_Page
{

    private $id;
    private $idPhoto1;
    private $photo1 = null;

    private $idPhoto2;
    private $photo2 = null;

    private $idPhoto3;
    private $photo3 = null;

    private $idPhoto4;
    private $photo4 = null;

    private $phone;
    private $lat;
    private $lng;
    private $city_id;
    private $area_id;

    private $call_price = 0;
    private $url_key;

    const ITEM_ON_PAGE = 8;

    private $comments = array();
    private $girls = null;
    private $area = null;
    private $city = null;

    public function __construct(
        $id,
        $idPhoto1, $idPhoto2, $idPhoto3, $idPhoto4,
        $idPage, $idRoute, $idContentPack,
        $pageEditDate, $pageStatus, $position,
        $phone, $lat, $lng, $city_id, $area_id, $call_price, $url_key = ''
    )
    {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_SALON, $position);
        $this->id = $id;

        $this->idPhoto1 = $idPhoto1;
        $this->idPhoto2 = $idPhoto2;
        $this->idPhoto3 = $idPhoto3;
        $this->idPhoto4 = $idPhoto4;

        $this->phone = $phone;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->city_id = $city_id;
        $this->area_id = $area_id;
        $this->call_price = $call_price;
        $this->url_key = $url_key;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPhone($item)
    {
        $this->phone = $item;

        return $this;
    }

    public function setLat($item)
    {
        $this->lat = $item;

        return $this;
    }

    public function setLng($item)
    {
        $this->lng = $item;

        return $this;
    }

    public function setCityId($item)
    {
        $this->city_id = $item;

        return $this;
    }

    public function setAreaId($item)
    {
        $this->area_id = $item;

        return $this;
    }

    public function setCallPrice($item)
    {
        $this->call_price = $item;

        return $this;
    }

    public function setUrlKey($item)
    {
        $this->url_key = $item;

        return $this;
    }


    public function save()
    {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->beginTransaction();
            $insert = is_null($this->_idPage);
            $this->savePageData(); //сохраняем даные страницы
            $data = array(
                'idPage'   => $this->getIdPage(),

                'idPhoto1' => $this->idPhoto1,
                'idPhoto2' => $this->idPhoto2,
                'idPhoto3' => $this->idPhoto3,
                'idPhoto4' => $this->idPhoto4,

                'phone' => $this->phone,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'city_id' => $this->city_id,
                'area_id' => $this->area_id,
                'call_price' => $this->call_price,
                'url_key' => $this->url_key
            );
            if ($insert) {
                $db->insert('salons', $data);
                $this->id = $db->lastInsertId();
            }
            else {
                $db->update('salons', $data, 'id = ' . intval($this->id));
            }
            $db->commit();
            $this->clearCache();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';product.php');
            throw new Exception($e->getMessage());
        }
    }

    private function clearCache()
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('salons');
        foreach ($cache->getIds () as $k=>$v) {
            $cache->remove($v);
        }
    }

    public function validate($data = false)
    {$this->clearCache();
        $e = new Application_Model_Kernel_Exception();
        $this->getRoute()->validate($e);
        $this->validatePageData($e);

        if ($data != false) {
            $data->url = trim($this->getRoute()->getUrl());
            if (empty($data->url))
                throw new Exception(' Пустой URL ');
            $langs = Kernel_Language::getAll();
            foreach ($langs as $lang) {
                if (empty($data->content[$lang->getId()]['name']))
                    throw new Exception(' Пустое поле "Название" ' . $lang->getFullName());
            }
        }

        if ((bool)$e->current())
            throw $e;
    }

    private static function getSelf(stdClass &$data)
    {
        return new self($data->id,
                        $data->idPhoto1, $data->idPhoto2, $data->idPhoto3, $data->idPhoto4,
                        $data->idPage, $data->idRoute, $data->idContentPack,
                        $data->pageEditDate, $data->pageStatus, $data->position,
                        $data->phone, $data->lat, $data->lng, $data->city_id,
                        $data->area_id, $data->call_price, $data->url_key
                        );
    }

    public static function loadCache($id)
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('project');

        return $cache->load($id);
    }

    public static function getById($id)
    {
        $id = (int)$id;
//		$cachemanager = Zend_Registry::get('cachemanager');
//		$cache = $cachemanager->getCache('department');
//		if (($project = $cache->load($idProject)) !== false) {
//			return $project;
//		} else {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('salons');
        $select->join('pages', 'salons.idPage = pages.idPage');
        $select->where('id = ?', $id);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
//				$project->completelyCache();
            return self::getSelf($productData);
        }
        else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
//		}
    }

    public static function getByUrlKey($url)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('salons');
        $select->join('pages', 'salons.idPage = pages.idPage');
        $select->where('url_key = ?', $url);
        $select->limit(1);
        if (($data = $db->fetchRow($select)) !== false) {
            return self::getSelf($data);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
    }

    public static function getByIdPage($idPage)
    {
        $idPage = intval($idPage);

        $db = Zend_Registry::get('db');
        $select = $db->select()->from('salons');
        $select->join('pages', 'salons.idPage = pages.idPage');
        $select->where('pages.idPage = ?', $idPage);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
            return self::getSelf($productData);
        }
        else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
    }

    public function completelyCache()
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('product');
        $cache->load($this->getIdPage());
        $this->getidPhoto1();
        $this->getRoute();
        $this->getContent();
        $cache->save($this);
    }

    public static function getList($order, $orderType, $content, $route, $searchName, $status, $page, $onPage, $limit, $group = true, $wher = false, $area = false, $nextorder = false)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('salons');
        $select->join('pages', 'pages.idPage = salons.idPage');

        if ($route) {
            $select->join('routing', 'pages.idRoute = routing.idRoute');
        }
        if ($content) {
            $select->join('content', 'content.idContentPack = pages.idContentPack');
            $select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
            if ($searchName) {
                $select->join('fields', "fields.idContent = content.idContent AND fields.fieldName = 'name'");
                $select->where('fields.fieldText LIKE ?', $searchName);
            }
        }
        $select->where('pages.pageType = ?', self::TYPE_SALON);
        if ($wher) {
            $select->where($wher);
        }
        if ($order && $orderType) {
            if ($order == 'BY' && $orderType == 'RAND') {
                $select->order(new Zend_Db_Expr('RAND()'));
            }
            else {
                $select->order($order . ' ' . $orderType);
            }
        }
        else {
            if (!$nextorder) {
                $select->order('pages.idPage DESC');
            }
        }
        if ($nextorder) {
            $select->order($nextorder);
        }
        if ($status !== false)
            $select->where('pages.pageStatus = ?', $status);
        if ($group !== false)
            $select->group('salons.id');
        if ($limit !== false)
            $select->limit($limit);

        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('salons');
        if (($return = $cache->load(md5($select->assemble())."_".(int)$onPage."_".(int)$page)) !== false) {
            return $return;
        } else {
            $return = new stdClass();
            if ($page !== false) {
                $paginator = Zend_Paginator::factory($select);
                $paginator->setItemCountPerPage($onPage);
                $paginator->setPageRange(5);
                $paginator->setCurrentPageNumber($page);
                $return->paginator = $paginator;
            } else {
                $return->paginator = $db->fetchAll($select);
            }
            $return->data = array();
            $i = 0;
            foreach ($return->paginator as $projectData) {
                $return->data[$i] = self::getSelf($projectData);
                if ($route) {
                    $url = new Application_Model_Kernel_Routing_Url($projectData->url);
                    $defaultParams = new Application_Model_Kernel_Routing_DefaultParams($projectData->defaultParams);
                    $route = new Application_Model_Kernel_Routing($projectData->idRoute, $projectData->type, $projectData->name, $projectData->module, $projectData->controller, $projectData->action, $url, $defaultParams, $projectData->routeStatus);
                    $return->data[$i]->setRoute($route);
                }
                if ($content) {
                    $contentLang = new Application_Model_Kernel_Content_Language($projectData->idContent, $projectData->idLanguage, $projectData->idContentPack);
                    $contentLang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($projectData->idContent));
                    $return->data[$i]->setContent($contentLang);
                }
                $i++;
            }
            $cache->save($return);
        }

        return $return;
    }

    public function show()
    {
        $db = Zend_Registry::get('db');
        $this->_pageStatus = self::STATUS_SHOW;
        $this->savePageData();
//        $this->clearCache();
    }

    public function hide()
    {
        $db = Zend_Registry::get('db');
        $this->_pageStatus = self::STATUS_HIDE;
        $this->savePageData();
//        $this->clearCache();
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('salons', "salons.idPage = {$this->_idPage}");
        $this->deletePage();
    }

    public static function changePosition($idPage, $position)
    {

        $db = Zend_Registry::get('db');
        $db->update('salons', array("projectPosition" => $position), 'idPage = ' . (int)$idPage);

        return true;
    }

    public function getIdPhoto1()
    {
        return $this->idPhoto1;
    }

    public function getIdPhoto2()
    {
        return $this->idPhoto2;
    }

    public function getIdPhoto3()
    {
        return $this->idPhoto3;
    }

    public function getIdPhoto4()
    {
        return $this->idPhoto4;
    }
    public function getPhoto1()
    {
        if (is_null($this->photo1))
            $this->photo1 = Application_Model_Kernel_Photo::getById($this->idPhoto1);

        return $this->photo1;
    }

    public function getPhoto2()
    {
        if (is_null($this->photo2))
            $this->photo2 = Application_Model_Kernel_Photo::getById($this->idPhoto2);
        if ($this->photo2->getPhotoPath() == 'image.jpg') {
            $this->photo2->setPhotoPath('banner3.png');
        }

        return $this->photo2;
    }

    public function getPhoto3()
    {
        if (is_null($this->photo3))
            $this->photo3 = Application_Model_Kernel_Photo::getById($this->idPhoto3);
        if ($this->photo3->getPhotoPath() == 'image.jpg') {
            $this->photo3->setPhotoPath('banner1.jpg');
        }

        return $this->photo3;
    }

    public function getPhoto4()
    {
        if (is_null($this->photo4))
            $this->photo4 = Application_Model_Kernel_Photo::getById($this->idPhoto4);
        if ($this->photo4->getPhotoPath() == 'image.jpg') {
            $this->photo4->setPhotoPath('banner2.jpg');
        }

        return $this->photo4;
    }
    
    public function setPhoto1(Application_Model_Kernel_Photo &$photo1)
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function setPhoto2(Application_Model_Kernel_Photo &$photo2)
    {
        $this->photo2 = $photo2;

        return $this;
    }

    public function setPhoto3(Application_Model_Kernel_Photo &$photo3)
    {
        $this->photo3 = $photo3;

        return $this;
    }

    public function setPhoto4(Application_Model_Kernel_Photo &$photo4)
    {
        $this->photo4 = $photo4;

        return $this;
    }
    
    public function setIdPhoto1($idPhoto1)
    {
        $this->idPhoto1 = $idPhoto1;
    }

    public function setIdPhoto2($idPhoto2)
    {
        $this->idPhoto2 = $idPhoto2;
    }

    public function setIdPhoto3($idPhoto3)
    {
        $this->idPhoto3 = $idPhoto3;
    }

    public function setIdPhoto4($idPhoto4)
    {
        $this->idPhoto4 = $idPhoto4;
    }

    public function setPath($data)
    {
        $path = Application_Model_Kernel_TextRedactor::makeTranslit($data->content[1]["name"]);
        $this->getRoute()->setUrl('/'.$path.'.html');
    }

    public function updatePath()
    {
        $path = $this->getRoute()->getUrl();
        $path = substr($path, 0, -5);

        $this->getRoute()->setUrl($path.'_'.(int)$this->id.'.html');
        $this->getRoute()->save();

        $this->setUrlKey(mb_substr($path, 1).'_'.(int)$this->id);
        $this->save();
    }


    public function getPhone()
    {
        return $this->phone;
    }

    public function getLat()
    {
        return $this->lat;
    }

    public function getLng()
    {
        return $this->lng;
    }

    public function getCityId()
    {
        return $this->city_id;
    }

    public function getAreaId()
    {
        return $this->area_id;
    }

    public function getCallPrice()
    {
        return $this->call_price;
    }

    public function getUrlKey()
    {
        return $this->url_key;
    }

    public function getComments()
    {
        if (empty($this->comments)) {
            $this->comments = Application_Model_Kernel_Comment::getList($this->id, Application_Model_Kernel_Comment::STATUS_SHOW);
        }

        return $this->comments;
    }

    public function getNewComments()
    {
        if (empty($this->comments)) {
            $this->comments = Application_Model_Kernel_Comment::getList($this->id, Application_Model_Kernel_Comment::STATUS_CREATE);
        }

        return $this->comments;
    }

    public function getArea()
    {
        if (is_null($this->area)) {
            $this->area = Application_Model_Kernel_Area::getById($this->area_id);
        }

        return $this->area;
    }

    public function getCity()
    {
        if (is_null($this->city)) {
            $this->city = Application_Model_Kernel_City::getById($this->city_id);
        }

        return $this->city;
    }

    public function getBadComments()
    {
        $arr = array();
        $comments = $this->getComments();

        foreach ($comments as $comment) {
            if ($comment->getCommentType() == 0) {
                $arr[] = $comment;
            }
        }

        return $arr;
    }

    public function getGoodComments()
    {
        $arr = array();
        $comments = $this->getComments();

        foreach ($comments as $comment) {
            if ($comment->getCommentType() == 2) {
                $arr[] = $comment;
            }
        }

        return $arr;
    }

    public function getGirls()
    {
        if (is_null($this->girls)) {
            $this->girls = Application_Model_Kernel_Girl::getList($this->id);
        }

        return $this->girls;
    }

    public function setSessionPhone()
    {
        $session = new Zend_Session_Namespace("phone");
        if (!isset($session->phones) || !$session->phones) {
            $session->phones = array();
        }
        $session->phones[$this->getId()] = $this->getId();
    }

    public function chackeOpenPhone()
    {
        $session = new Zend_Session_Namespace("phone");

        return isset($session->phones)&&isset($session->phones[$this->getId()]);
    }
}
