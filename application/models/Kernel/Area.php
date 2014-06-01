<?php
class Application_Model_Kernel_Area
{

    protected $id;
    protected $_idContentPack;
    protected $city_id;
    protected $url;
    protected $status;

    /**
     * @var Application_Model_Kernel_Content_Manager
     */
    private $_contentManager = null;
    /**
     * @var Application_Model_Kernel_Content_Lang
     */
    private $_content = null;

    const STATUS_SHOW = 1;
    const STATUS_HIDE = 0;

    const ERROR_CONTENT_LANG_GIVEN             = 'Wrong content lang given';
    const ERROR_CONTENT_MANAGER_GIVEN          = 'Wrong content manager given';
    const ERROR_CONTENT_MANAGER_IS_NOT_DEFINED = 'Content manager is not defined';
    const ERROR_CONTENT_LANG_IS_NOT_DEFINED    = 'Content lang model is not defined';
    const TYPE_ERROR_ID_NOT_FOUND              = 'Id not found';


    public function __construct($id, $idContentPack, $city_id, $url, $status = 1)
    {
        $this->id             = $id;
        $this->_idContentPack = $idContentPack;
        $this->city_id        = $city_id;
        $this->url        = $url;
        $this->status        = $status;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCityId()
    {
        return $this->city_id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @name getById
     * @param int $id
     * @throws Exception
     * @return Application_Model_Kernel_Area
     */
    public static function getById($id)
    {
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('areas');
        $select->where('areas.id = ?', (int)$id);
        if (false !== ($data = $db->fetchRow($select))) {
            return self::getSelf($data);
        } else
            throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
    }

    public function getByUrl($url)
    {
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('areas');
        $select->where('areas.url = ?', $url);
        if (false !== ($data = $db->fetchRow($select))) {
            return self::getSelf($data);
        } else
            throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
    }

    /**
     * @param Application_Model_Kernel_Content_Manager $contentManager
     * @throws Exception ERROR_CONTENT_MANAGER_GIVEN
     * @return void
     */
    public function setContentManager(Application_Model_Kernel_Content_Manager &$contentManager)
    {
        $this->_contentManager = $contentManager;
    }

    /**
     * @return Application_Model_Kernel_Content_Manager
     */
    public function getContentManager()
    {
        if (is_null($this->_contentManager)) {
            $this->_contentManager = Application_Model_Kernel_Content_Manager::getById($this->_idContentPack);
        }

        return $this->_contentManager;
    }

    /**
     * @return Application_Model_Kernel_Content_Lang
     */
    public function getContent()
    {
        if (is_null($this->_content)) {
            $this->_content = Application_Model_Kernel_Content_Language::get($this->_idContentPack, Kernel_Language::getCurrent()->getId());
        }

        return $this->_content;
    }

    /**
     * @param Application_Model_Kernel_Content_Language $contentLang
     */
    public function setContent(Application_Model_Kernel_Content_Language &$contentLang)
    {
        $this->_content = $contentLang;
    }


    public function validate()
    {
        $e = new Application_Model_Kernel_Exception();
        if (is_null($this->_contentManager)) {
            throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
        }
        $this->_contentManager->validate($e);
        if ((bool)$e->current())
            throw $e;
    }

    public static function getSelf($data)
    {
        return new self($data->id, $data->idContentPack, $data->city_id, $data->url, $data->status);
    }

    public static function getList($city_id = false)
    {
        $db     = Zend_Registry::get('db');
        $return = array ();
        $select = $db->select()->from('areas');
        if ($city_id) {
            $select->where('city_id = ?', (int)$city_id);
        }
        if (false !== ($result = $db->fetchAll($select))) {
            foreach ($result as $category) {
                $return[] = self::getSelf($category);
            }
        }

        return $return;
    }

    public function save()
    {
        $data = array (
            'idContentPack' => $this->_idContentPack,
            'city_id'           => $this->city_id,
            'url'           => $this->url,
            'status' => $this->status
        );
        $db   = Zend_Registry::get('db');
        if (is_null($this->id)) {
            if (is_null($this->_contentManager))
                throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
            $this->_contentManager->saveContentData(); //Сохраняем весь конент через меджер
            $this->_idContentPack  = $this->_contentManager->getIdContentPack(); //ставим AI idContent
            $data['idContentPack'] = $this->_idContentPack;
            $db->insert('areas', $data);
            $this->id = $db->lastInsertId();
        } else {
            $this->getContentManager()->saveContentData();
            $db->update('areas', $data, 'id = ' . intval($this->id));
        }
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('areas', "areas.id = {$this->id}");
        $this->getContentManager()->delete();
    }
}