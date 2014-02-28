<?php
class Application_Model_Kernel_Subscribe
{

    protected $id;
    protected $city_id = 0;
    protected $area_id = 0;
    protected $email;

    public function __construct($id, $city_id, $area_id, $email)
    {
        $this->id      = $id;
        $this->city_id = $city_id;
        $this->area_id = $area_id;
        $this->email   = $email;
    }


    /**
     * @name getById
     * @param int $idCategory
     * @throws Exception
     * @return Application_Model_Kernel_Category
     */
    public static function getById($idCategory)
    {
        $idCategory = (int)$idCategory;
        $db         = Zend_Registry::get('db');
        $select     = $db->select()->from('subscribes');
        $select->where('subscribes.id = ?', $idCategory);
        if (false !== ($data = $db->fetchRow($select))) {
            return self::getSelf($data);
        } else
            throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
    }

    public function getId()
    {
        return $this->_idCategory;
    }

    public function validate()
    {
//        $e = new Application_Model_Kernel_Exception();
//        if (is_null($this->_contentManager)) {
//            throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
//        }
//        $this->_contentManager->validate($e);
//        if ((bool)$e->current())
//            throw $e;
    }

    public static function getSelf($data)
    {
        return new self($data->id, $data->city_id, $data->area_id, $data->email );
    }

    public static function getList()
    {
        $db     = Zend_Registry::get('db');
        $return = array ();
        $select = $db->select()->from('subscribes');
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
            'city_id' => $this->city_id,
            'area_id'    => $this->area_id,
            'email' => $this->email
        );
        $db   = Zend_Registry::get('db');
        if (is_null($this->id)) {
            $db->insert('subscribes', $data);
            $this->id = $db->lastInsertId();
        } else {
            $db->update('subscribes', $data, 'id = ' . intval($this->id));
        }
    }
}