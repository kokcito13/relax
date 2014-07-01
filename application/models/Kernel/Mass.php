<?php

class Application_Model_Kernel_Mass extends Application_Model_Kernel_Page
{

    private $id;
    private $idPhoto1;
    private $photo1 = null;

    const ITEM_ON_PAGE = 10;
    const TABLE_NAME = 'mass';

    public function __construct(
        $id,
        $idPhoto1,
        $idPage, $idRoute, $idContentPack,
        $pageEditDate, $pageStatus, $position
    )
    {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_MASS, $position);
        $this->id = $id;

        $this->idPhoto1 = $idPhoto1;
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
                'idPage' => $this->getIdPage(),
                'idPhoto1' => $this->idPhoto1
            );
            if ($insert) {
                $db->insert(self::TABLE_NAME, $data);
                $this->id = $db->lastInsertId();
            } else {
                $db->update(self::TABLE_NAME, $data, 'id = ' . intval($this->id));
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
            $data->idPhoto1,
            $data->idPage, $data->idRoute, $data->idContentPack,
            $data->pageEditDate, $data->pageStatus, $data->position
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
        $select = $db->select()->from(self::TABLE_NAME);
        $select->join('pages', self::TABLE_NAME . '.idPage = pages.idPage');
        $select->where('id = ?', $id);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
//				$project->completelyCache();
            return self::getSelf($productData);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
//		}
    }

    public static function getByUrlKey($url)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(self::TABLE_NAME);
        $select->join('pages', self::TABLE_NAME . '.idPage = pages.idPage');
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
        $select = $db->select()->from(self::TABLE_NAME);
        $select->join('pages', self::TABLE_NAME . '.idPage = pages.idPage');
        $select->where('pages.idPage = ?', $idPage);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
            return self::getSelf($productData);
        } else {
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
        $select = $db->select()->from(self::TABLE_NAME);
        $select->join('pages', 'pages.idPage = ' . self::TABLE_NAME . '.idPage');

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
        $select->where('pages.pageType = ?', self::TYPE_MASS);
        if ($wher) {
            $select->where($wher);
        }
        if ($order && $orderType) {
            if ($order == 'BY' && $orderType == 'RAND') {
                $select->order(new Zend_Db_Expr('RAND()'));
            } else {
                $select->order($order . ' ' . $orderType);
            }
        } else {
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
            $select->group(self::TABLE_NAME . '.id');
        if ($limit !== false)
            $select->limit($limit);

//        $cachemanager = Zend_Registry::get('cachemanager');
//        $cache = $cachemanager->getCache('salons');
//        if (($return = $cache->load(md5($select->assemble())."_".(int)$onPage."_".(int)$page)) !== false) {
//            return $return;
//        } else {
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
//            $cache->save($return);
//        }

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
        $db->delete(self::TABLE_NAME, self::TABLE_NAME . ".idPage = {$this->_idPage}");
        $this->deletePage();
    }


    public function getIdPhoto1()
    {
        return $this->idPhoto1;
    }

    public function getPhoto1()
    {
        if (is_null($this->photo1))
            $this->photo1 = Application_Model_Kernel_Photo::getById($this->idPhoto1);

        return $this->photo1;
    }

    public function setPhoto1(Application_Model_Kernel_Photo &$photo1)
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function setIdPhoto1($idPhoto1)
    {
        $this->idPhoto1 = $idPhoto1;
    }

    public function setPath($data)
    {
        $path = Application_Model_Kernel_TextRedactor::makeTranslit($data->content[1]["name"]);
        $this->getRoute()->setUrl('/' . $path . '.html');
    }

    public function updatePath()
    {
        $path = $this->getRoute()->getUrl();
        $path = substr($path, 0, -5);

        $this->getRoute()->setUrl($path . '_' . (int)$this->id . '.html');
        $this->getRoute()->save();

        $this->setUrlKey(mb_substr($path, 1) . '_' . (int)$this->id);
        $this->save();
    }
}