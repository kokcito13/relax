<?php
class Application_Model_Kernel_SiteSetings
{

    protected $id;
    protected $idPhoto1;
    protected $idPhoto2;

    protected $url1;
    protected $url2;

    protected $photo1 = null;
    protected $photo2 = null;

    protected $robots = '';
    protected $sitemap = '';

    protected $head = '';
    protected $body = '';
    protected $mass_links = '';

    public function __construct($id, $idPhoto1, $idPhoto2, $url1, $url2, $robots = '', $sitemap = '', $head = '', $body = '', $mass_links = '')
    {
        $this->id = $id;
        $this->idPhoto1 = $idPhoto1;
        $this->idPhoto2 = $idPhoto2;
        $this->url1 = $url1;
        $this->url2 = $url2;

        $this->robots = $robots;
        $this->sitemap = $sitemap;

        $this->head = $head;
        $this->body = $body;
        $this->mass_links = $mass_links;
    }

    public function setMassLinks($text)
    {
        $this->mass_links = $text;

        return $this;
    }

    public function getMassLinks()
    {
        return $this->mass_links;
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

    public function getIdPhoto2()
    {
        return $this->idPhoto2;
    }

    public function getPhoto2()
    {
        if (is_null($this->photo2))
            $this->photo2 = Application_Model_Kernel_Photo::getById($this->idPhoto2);

        return $this->photo2;
    }

    public function setPhoto2(Application_Model_Kernel_Photo &$photo2)
    {
        $this->photo2 = $photo2;

        return $this;
    }

    public function setIdPhoto2($idPhoto2)
    {
        $this->idPhoto2 = $idPhoto2;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getRobots()
    {
        return $this->robots;
    }

    public function setRobots($text)
    {
        $this->robots = $text;

        return $this;
    }

    public function getSitemap()
    {
        return $this->sitemap;
    }

    public function setSitemap($text)
    {
        $this->sitemap = $text;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($text)
    {
        $this->body = $text;

        return $this;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function setHead($text)
    {
        $this->head = $text;

        return $this;
    }

    public function validate()
    {
//        if ($this->getName() === '') {
//            throw new Exception('Enter block name');
//        }
//        if (strlen($this->getName()) <= 3) {
//            throw new Exception('Block name must me more then 3 letter');
//        }
    }

    /**
     * Save block data
     * @access public
     * @return void
     */
    public function save()
    {
        $data = array(
            'idPhoto1'     => $this->idPhoto1,
            'idPhoto2'         => $this->idPhoto2,
            'url1' => $this->url1,
            'url2' => $this->url2,
            'robots' => $this->robots,
            'sitemap' => $this->sitemap,
            'head' => $this->head,
            'body' => $this->body,
            'mass_links' => $this->mass_links
        );
        $db = Zend_Registry::get('db');
        $db->update('site_setings', $data, 'id = ' . $this->getId());
    }

    public static function getBy()
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('site_setings');
        $select->where('site_setings.id = 1');
        $select->limit(1);
        if (($block = $db->fetchRow($select)) !== false) {
            return new self($block->id, $block->idPhoto1, $block->idPhoto2,
                $block->url1, $block->url2, $block->robots,
                $block->sitemap, $block->head, $block->body,
                $block->mass_links);
        }
        else {
            throw new Exception('Table NOT FOUND');
        }
    }

    public function getUrl1()
    {
        return $this->url1;
    }

    public function getUrl2()
    {
        return $this->url2;
    }

    public function setUrl1($url)
    {
        $this->url1 = $url;

        return $this;
    }

    public function setUrl2($url)
    {
        $this->url2 = $url;

        return $this;
    }
}