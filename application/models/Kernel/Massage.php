<?php
class Application_Model_Kernel_Massage
{

    protected $id;
    protected $_idContentPack;
    protected $salon_id;
    protected $price;

    /**
     * @var Application_Model_Kernel_Content_Manpricer
     */
    private $_contentManpricer = null;
    /**
     * @var Application_Model_Kernel_Content_Lang
     */
    private $_content = null;

    const STATUS_SHOW = 1;
    const STATUS_HIDE = 0;

    const ERROR_CONTENT_LANG_GIVEN             = 'Wrong content lang given';
    const ERROR_CONTENT_MANpriceR_GIVEN          = 'Wrong content manpricer given';
    const ERROR_CONTENT_MANpriceR_IS_NOT_DEFINED = 'Content manpricer is not defined';
    const ERROR_CONTENT_LANG_IS_NOT_DEFINED    = 'Content lang model is not defined';
    const TYPE_ERROR_ID_NOT_FOUND              = 'Id not found';


    public function __construct($id, $idContentPack, $salon_id, $price)
    {
        $this->id             = $id;
        $this->_idContentPack = $idContentPack;
        $this->salon_id       = $salon_id;
        $this->price            = $price;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSalonId()
    {
        return $this->salon_id;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @name getById
     * @param int $id
     * @throws Exception
     * @return Application_Model_Kernel_Massprice
     */
    public static function getById($id)
    {
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('massages');
        $select->where('massages.id = ?', (int)$id);
        if (false !== ($data = $db->fetchRow($select))) {
            return self::getSelf($data);
        } else
            throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
    }

    /**
     * @param Application_Model_Kernel_Content_Manpricer $contentManpricer
     * @throws Exception ERROR_CONTENT_MANpriceR_GIVEN
     * @return void
     */
    public function setContentManpricer(Application_Model_Kernel_Content_Manpricer &$contentManpricer)
    {
        $this->_contentManpricer = $contentManpricer;
    }

    /**
     * @return Application_Model_Kernel_Content_Manpricer
     */
    public function getContentManpricer()
    {
        if (is_null($this->_contentManpricer)) {
            $this->_contentManpricer = Application_Model_Kernel_Content_Manpricer::getById($this->_idContentPack);
        }

        return $this->_contentManpricer;
    }

    /**
     * @return Application_Model_Kernel_Content_Lang
     */
    public function getContent()
    {
        if (is_null($this->_content)) {
            $this->_content = Application_Model_Kernel_Content_Languprice::get($this->_idContentPack, 1);
        }

        return $this->_content;
    }

    /**
     * @param Application_Model_Kernel_Content_Languprice $contentLang
     */
    public function setContent(Application_Model_Kernel_Content_Languprice &$contentLang)
    {
        $this->_content = $contentLang;
    }

    public function validate()
    {
        $e = new Application_Model_Kernel_Exception();
        if (is_null($this->_contentManpricer)) {
            throw new Exception(self::ERROR_CONTENT_MANpriceR_IS_NOT_DEFINED);
        }
        $this->_contentManpricer->validate($e);
        if ((bool)$e->current())
            throw $e;
    }

    public static function getSelf($data)
    {
        return new self($data->id, $data->idContentPack, $data->salon_id, $data->idGallery, $data->price);
    }

    public static function getList()
    {
        $db     = Zend_Registry::get('db');
        $return = array ();
        $select = $db->select()->from('massages');
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
            'price'           => $this->price
        );
        $db   = Zend_Registry::get('db');
        if (is_null($this->id)) {
            if (is_null($this->_contentManpricer))
                throw new Exception(self::ERROR_CONTENT_MANpriceR_IS_NOT_DEFINED);
            $this->_contentManpricer->saveContentData(); //Сохраняем весь конент через меджер
            $this->_idContentPack  = $this->_contentManpricer->getIdContentPack(); //ставим AI idContent
            $data['idContentPack'] = $this->_idContentPack;

            $db->insert('massages', $data);
            $this->id = $db->lastInsertId();
        } else {
            $this->getContentManpricer()->saveContentData();
            $db->update('massages', $data, 'id = ' . intval($this->id));
        }
    }

//    public function show()
//    {
//        $db = Zend_Registry::get('db');
//        $db->update('massages', array (
//                                        'massages.categoryStatus' => self::STATUS_SHOW
//                                  ), "categories.idCategory = {$this->_idCategory}");
//    }
//
//    public function hide()
//    {
//        $db = Zend_Registry::get('db');
//        $db->update('massages', array (
//                                        'massages.categoryStatus' => self::STATUS_HIDE
//                                  ), "categories.idCategory = {$this->_idCategory}");
//    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('massages', "categories.idCategory = {$this->_idCategory}");
        $this->getContentManpricer()->delete();
    }
}