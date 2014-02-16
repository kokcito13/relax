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

    const ITEM_ON_PAGE = 16;

    public function __construct(
        $id, $idPhoto1, $idPhoto2, $idPhoto3, $idPhoto4, $idPage,
        $idRoute, $idContentPack, $pageEditDate,
        $pageStatus, $position, $phone,
        $lat, $lng, $city_id, $area_id
    )
    {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_PROJECT, $position);
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
    }

    public function getId()
    {
        return $this->id;
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
                'price' => $this->price,

                'idAmazon' =>$this->idAmazon,
                'sameProducts' =>$this->sameProducts,
                'productUrl' =>$this->productUrl,
                'productStatus' => $this->productStatus,
                'productStatusPopular' => $this->productStatusPopular
            );
            if ($insert) {
                $db->insert('salons', $data);
                $this->id = $db->lastInsertId();
            }
            else {
                $db->update('salons', $data, 'id = ' . intval($this->id));
            }
            $db->commit();
//            $this->clearCache();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';product.php');
            throw new Exception($e->getMessage());
        }
    }

    private function clearCache()
    {
        if (!is_null($this->getidProject())) {
            $cachemanager = Zend_Registry::get('cachemanager');
            $cache = $cachemanager->getCache('product');
            if (!is_null($cache)) {
                $cache->remove($this->getid());
            }
        }
    }

    public function validate($data = false)
    {
        $e = new Application_Model_Kernel_Exception();
        $this->getRoute()->validate($e);
        $this->validatePageData($e);

        if ($data != false) {
            $data->url = trim($data->url);
            if (empty($data->url))
                throw new Exception(' Пустой URL ');
            $langs = Kernel_Language::getAll();
            foreach ($langs as $lang) {
                if (empty($data->content[$lang->getId()]['contentName']))
                    throw new Exception(' Пустой поле "Название" ' . $lang->getFullName());
            }
        }

        if ((bool)$e->current())
            throw $e;
    }

    private static function getSelf(stdClass &$data)
    {
        return new self($data->id, $data->idPhoto1,
                        $data->idPage, $data->idRoute, $data->idContentPack,
                        $data->pageEditDate, $data->pageStatus, $data->position,
                        $data->price,
                        $data->idAmazon, $data->sameProducts, $data->productUrl,
                        $data->productStatus, $data->productStatusPopular
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
        $return = new stdClass();
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('salons');
        $select->join('pages', 'pages.idPage = salons.idPage');
//        $select->join('categorie_product', 'salons.id = categorie_product.id');
//        $select->joinLeft('comments', '( salons.id = comments.idOwner AND comments.commentType = 1 )', array('countComm' => 'COUNT(comments.idOwner)'));

        //ORDER BY countComm DESC

        if ($route) {
            $select->join('routing', 'pages.idRoute = routing.idRoute');
        }
        if ($content) {
            $select->join('content', 'content.idContentPack = pages.idContentPack');
            $select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
            if ($searchName) {
                $select->where('content.contentName = ?', $searchName);
            }
        }
        $select->where('pages.pageType = ?', self::TYPE_PROJECT);
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
        if ($page !== false) {
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($onPage);
            $paginator->setPageRange(5);
            $paginator->setCurrentPageNumber($page);
            $return->paginator = $paginator;
        }
        else {
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

        return $this->photo2;
    }

    public function getPhoto3()
    {
        if (is_null($this->photo3))
            $this->photo3 = Application_Model_Kernel_Photo::getById($this->idPhoto3);

        return $this->photo3;
    }

    public function getPhoto4()
    {
        if (is_null($this->photo4))
            $this->photo4 = Application_Model_Kernel_Photo::getById($this->idPhoto4);

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
}