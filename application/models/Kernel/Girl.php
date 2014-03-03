<?php
class Application_Model_Kernel_Girl
{

    protected $id;
    protected $_idContentPack;
    protected $salon_id;
    protected $age;

    private $idGallery;
    private $_gallery = null;

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


    public function __construct($id, $idContentPack, $salon_id, $idGallery, $age)
    {
        $this->id             = $id;
        $this->_idContentPack = $idContentPack;
        $this->salon_id       = $salon_id;
        $this->idGallery      = $idGallery;
        $this->age            = $age;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSalonId()
    {
        return $this->salon_id;
    }

    public function getIdGallery()
    {
        return $this->idGallery;
    }

    public function setIdGallery($idGallery)
    {
        $this->idGallery = $idGallery;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @name getById
     * @param int $id
     * @throws Exception
     * @return Application_Model_Kernel_Girl
     */
    public static function getById($id)
    {
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('girls');
        $select->where('girls.id = ?', (int)$id);
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
            $this->_content = Application_Model_Kernel_Content_Language::get($this->_idContentPack, 1);
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
        return new self($data->id, $data->idContentPack, $data->salon_id, $data->idGallery, $data->age);
    }

    public static function getList($salon_id)
    {
        $db     = Zend_Registry::get('db');
        $return = array ();
        $select = $db->select()->from('girls');
        $select->where('girls.salon_id = ?', (int)$salon_id);
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
            'salon_id'      => $this->salon_id,
            'idGallery'     => $this->idGallery,
            'age'           => $this->age
        );
        $db   = Zend_Registry::get('db');
        if (is_null($this->id)) {
            if (is_null($this->_contentManager))
                throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
            $this->_contentManager->saveContentData(); //Сохраняем весь конент через меджер
            $this->_idContentPack  = $this->_contentManager->getIdContentPack(); //ставим AI idContent
            $data['idContentPack'] = $this->_idContentPack;

            $this->_gallery = new Application_Model_Kernel_Gallery(null, time(), time(), 0);
            $this->_gallery->save();
            $this->_idGallery  = $this->_gallery->getId();
            $data['idGallery'] = $this->_idGallery;

            $db->insert('girls', $data);
            $this->id = $db->lastInsertId();
        } else {
            $this->getContentManager()->saveContentData();
            $db->update('girls', $data, 'id = ' . intval($this->id));
        }
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('girls', "girls.id = {$this->id}");
        $this->getContentManager()->delete();
        $this->getGallery()->delete();
    }

    public function getGallery()
    {
        if (is_null($this->_gallery)) {
            $this->_gallery = Application_Model_Kernel_Gallery::getById($this->idGallery);
        }

        return $this->_gallery;
    }
}