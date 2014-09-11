<?php

class Application_Model_Kernel_Comment
{

    private $idComment;
    private $idOwner;
    private $parentIdComment;
    private $commentNick;
    private $commentEmail;
    private $commentText;
    private $commentDate;
    private $commentIp;
    private $commentType = 1;
    private $commentStatus = 0;

    private $commentAdminText = '';
    private $commentAdminDate;

    const STATUS_CREATE = 0;
    const STATUS_SHOW   = 1;

    private $owner = null;

    public function __construct($idComment, $idOwner, $parentIdComment,
                                $commentNick, $commentEmail, $commentText,
                                $commentDate, $commentIp, $commentType,
                                $commentStatus = self::STATUS_CREATE,
                                $commentAdminText = '', $commentAdminDate = null
    )
    {
        $this->idComment        = $idComment;
        $this->idOwner          = $idOwner;
        $this->parentIdComment  = $parentIdComment;
        $this->commentNick      = $commentNick;
        $this->commentEmail     = $commentEmail;
        $this->commentText      = $commentText;
        $this->commentDate      = $commentDate;
        $this->commentIp        = $commentIp;
        $this->commentType      = $commentType;
        $this->commentStatus    = $commentStatus;
        $this->commentAdminText = $commentAdminText;
        $this->commentAdminDate = $commentAdminDate;

        if (is_null($this->commentAdminDate)) {
            $this->commentAdminDate = time();
        }
    }

    public function getIdComment()
    {
        return $this->idComment;
    }

    public function getCommentStatus()
    {
        return $this->commentStatus;
    }

    public function readComment()
    {
        $this->commentStatus = self::STATUS_SHOW;
    }

    public function getCommentNick()
    {
        return $this->commentNick;
    }

    public function getCommentText()
    {
        return $this->commentText;
    }

    public function getCommentAdminText()
    {
        return $this->commentAdminText;
    }

    public function getCommentEmail()
    {
        return $this->commentEmail;
    }

    public function getCommentDate()
    {
        return $this->commentDate;
    }

    public function getParentIdComment()
    {
        return $this->parentIdComment;
    }

    public static function getSelf($data)
    {
        return new self($data->idComment, $data->idOwner, $data->parentIdComment,
                        $data->commentNick, $data->commentEmail, $data->commentText,
                        $data->commentDate, $data->commentIp, $data->commentType,
                        $data->commentStatus, $data->commentAdminText, $data->commentAdminDate);
    }

    public static function getList($salon_id, $status = false, $limit = false, $where = false)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('comments');
        $select->where('comments.idOwner = ?', $salon_id);
        if ($status) {
            $select->where('comments.commentStatus = ?', $status);
        }
        if ($where !== false) {
            $select->where($where);
        }
        $select->order('comments.commentDate DESC');
        if ($limit !== false)
            $select->limit($limit);

        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('comments');
        $return = $cache->load(md5($select->assemble()));
        if ($return !== false && is_array($return)) {
            return $return;
        } else {
            $return = array();
            $i = 0;
            $results = $db->fetchAll($select);
            foreach ($results as $result) {
                $return[$result->idComment] = self::getSelf($result);
                $i++;
            }
            $cache->save($return);
        }

        return $return;
    }

    public function save()
    {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->beginTransaction();
            $data = array (
                'idOwner'         => $this->idOwner,
                'parentIdComment' => $this->parentIdComment,
                'commentNick'     => $this->commentNick,
                'commentEmail'    => $this->commentEmail,
                'commentText'     => $this->commentText,
                'commentDate'     => $this->commentDate,
                'commentIp'       => $this->commentIp,
                'commentType'     => $this->commentType,
                'commentStatus'   => $this->commentStatus,
                'commentAdminText'   => $this->commentAdminText,
                'commentAdminDate'   => $this->commentAdminDate
            );
            if (is_null($this->idComment)) {
                $db->insert('comments', $data);
                $this->idComment = $db->lastInsertId();
            } else {
                $db->update('comments', $data, 'idComment = ' . (int)$this->idComment);
            }
            $this->clearCache();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';comments.php');
            throw new Exception($e->getMessage());
        }
    }

    private function clearCache()
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('comments');
        foreach ($cache->getIds () as $k=>$v) {
            $cache->remove($v);
        }
    }

    public static function getById($idComment)
    {
        $idComment = (int)$idComment;
        $db        = Zend_Registry::get('db');
        $select    = $db->select()->from('comments');
        $select->where('comments.idComment = ?', $idComment);
        $select->limit(1);
        $result = $db->fetchRow($select);

        return self::getSelf($result);
    }

    public static function getByParentIdComment($parentIdComment)
    {
        $parentIdComment = (int)$parentIdComment;
        $db              = Zend_Registry::get('db');
        $return          = array ();
        $select          = $db->select()->from('comments');
        $select->where('comments.parentIdComment = ?', $parentIdComment);
        $returnGeter = $db->fetchAll($select);
        $i           = 0;
        foreach ($returnGeter as $result) {
            $return[$i] = self::getSelf($result);
            $i++;
        }

        return $return;
    }

    public static function getByidOwner($idOwner)
    {
        $idOwner = (int)$idOwner;
        $db      = Zend_Registry::get('db');
        $return  = array ();
        $select  = $db->select()->from('comments');
        $select->where('comments.idOwner = ?', $idOwner);
        $select->order('idComment');
        foreach ($db->fetchAll($select) as $result) {
            $return[] = self::getSelf($result);
        }

        return $return;
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('comments', "comments.idComment = ".(int)$this->idComment);
        $this->clearCache();
    }

    public function getCommentType()
    {
        return $this->commentType;
    }

    public function getCommentTypeText()
    {
        $arr = array(
            0 => 'отрицательный',
            1 => 'нейтральный',
            2 => 'положительный'
        );

        return $arr[$this->commentType];
    }

    public function setCommentAdminText($text)
    {
        $this->commentAdminText = trim($text);

        return $this;
    }

    public function setCommentAdminDate($d)
    {
        $this->commentAdminDate = $d;

        return $this;
    }

    public function setCommentStatus($status)
    {
        $this->commentStatus = $status;

        return $this;
    }

    public function setCommentNick($d)
    {
        $this->commentNick = trim($d);

        return $this;
    }

    public function setCommentText($t)
    {
        $this->commentText = trim($t);

        return $this;
    }

    public function getOwner()
    {
        if (is_null($this->owner)) {
            $this->owner = Application_Model_Kernel_Salon::getById($this->idOwner);
        }

        return $this->owner;
    }

    public function getDateFormat($f)
    {
        return date($f, $this->getCommentDate());
    }
}