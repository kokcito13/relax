<?php
/**
 * Application_Model_Kernel_Content_Fields
 *
 * Manage content of one Field
 *
 * @author <oklosovich@gmail.com>
 * @package Content
 * @version 1.0
 */
class Application_Model_Kernel_Content_Fields
{

    private $idField;
    private $idContent;
    private $fieldName;
    private $fieldText;

    const ERROR_NO_FILDS_CONTENT = 'Not fields in this content';

    public function __construct($idField, $idContent, $fieldName, $fieldText)
    {
        $this->idField   = $idField;
        $this->idContent = $idContent;
        $this->fieldName = $fieldName;
        $this->fieldText = $fieldText;
    }

    public function getIdField()
    {
        return $this->idField;
    }

    public function getIdContent()
    {
        return $this->idContent;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getFieldText()
    {
        return $this->fieldText;
    }

    public function __call($method, $params)
    {
        //	echo $method." - ".$params;
    }

    public static function getFieldsByIdContent($idContent)
    {
        $idContent = (int)$idContent;
        $return    = array ();
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache        = $cachemanager->getCache('field');
        if (($lang = $cache->load('FIELD_BY_CONTENT_' . $idContent)) !== false) {
            return $lang;
        } else {
            $db        = Zend_Registry::get('db');
            $select    = $db->select()->from('fields');
            $select->where('fields.idContent = ?', $idContent);
            $result = $db->fetchAll($select);
            if ($result !== false) {
                foreach ($result as $value) {
                    $return[$value->fieldName] = new self($value->idField, $value->idContent, $value->fieldName, $value->fieldText);
                }
                $cache->save($return);

                return $return;
            } else {
                throw new Exception(self::ERROR_NO_FILDS_CONTENT);
            }
        }

    }

    public static function getFieldByIdContentAndNameField($idContent, $fildName)
    {
        $idContent = (int)$idContent;
        $return    = array ();
        $db        = Zend_Registry::get('db');
        $select    = $db->select()->from('fields');
        $select->where('fields.idContent = ?', $idContent);
        $select->where('fields.fieldName = ?', $fildName);
        $result = $db->fetchRow($select);
        if ($result !== false) {
            $return = new self($result->idField, $result->idContent, $result->fieldName, $result->fieldText);

            return $return;
        } else {
            throw new Exception(self::ERROR_NO_FILDS_CONTENT);
        }
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('content', "idContent = " . intval($this->_idContent));
        $this->clearCache();
    }

    private function clearCache()
    {
        $cacheManager = Zend_Registry::get('cachemanager');
        $cacheManager->getCache('field')->clean();
    }

    public static function deleteCache()
    {
        $cacheManager = Zend_Registry::get('cachemanager');
        $cacheManager->getCache('field')->clean();
    }

    public static function getById($id)
    {

    }

    public static function getContentManagerByText($text)
    {
        $text   = str_replace(" ", "%", trim($text));
        $return = array ();
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('fields');
        $select->join('content', 'content.idContent = fields.idContent');
        $select->join('pages', 'pages.idContentPack	 = content.idContentPack');
        $select->where('fields.fieldText LIKE "%' . $text . '%"');
        $select->group('fields.idContent');
        $result = $db->fetchAll($select);
        if ($result !== false) {
            $i = 0;
            foreach ($result as $value) {
                $return[$i]            = new stdClass();
                $return[$i]->idPage    = $value->idPage;
                $return[$i]->pageType  = $value->pageType;
                $return[$i]->fieldText = $value->fieldText;
                $i++;
            }

            return $return;
        } else {
            throw new Exception(self::ERROR_NO_FILDS_CONTENT);
        }
    }

    protected function clearStyle($Content)
    {
        if (preg_match('/(style\=[a-z0-9\;\,\"\'\ \.\-\:\#\%]+)/i', $Content, $result)) {
            $Content = str_replace($result[1], "", $Content);
            $Content = $this->clearStyle($Content);
        }

        return str_replace(array (
                                 '<pre>',
                                 '</pre>'
                           ), array (
                                    '<p>',
                                    '</p>'
                              ), $Content);
    }


    public function save()
    {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $insert = is_null($this->idField);
            $data   = array (
                'idContent' => $this->idContent,
                'fieldName' => $this->fieldName,
                'fieldText' => $this->fieldText,
            );
            if ($insert) {
                $db->insert('fields', $data);
                $this->idField = $db->lastInsertId();
            } else {
                $db->update('fields', $data, 'idField = ' . (int)$this->idField);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }
    }

    public function setFieldText($text)
    {
        $this->fieldText = trim($text);

        return $this;
    }

    public static function setFieldsForModel($postContent, $getContent, &$model)
    {
        self::deleteCache();
        $langs = Kernel_Language::getAll();
        foreach ($langs as $lang) {
            $idContent = $getContent[$lang->getId()]->getId();
            $contentFields = $getContent[$lang->getId()]->getFields();
            foreach ($postContent[$lang->getId()] as $key => $value) {
                if (isset($contentFields[$key])) {
                    $contentFields[$key]
                        ->setFieldText($value)
                        ->save();
                } else {
                    $field = new Application_Model_Kernel_Content_Fields(null, $idContent, $key, $value);
                    $field->save();
                }
            }
        }

        if (count($postContent) > count($getContent)) {
            foreach ($langs as $lang) {
                if (!isset($getContent[$lang->getId()])) {
                    $content = new Application_Model_Kernel_Content_Language(null, $lang->getId(), $model->getContentManager()->getIdContentPack());
                    foreach ($postContent[$lang->getId()] as $key => $value) {
                        $content->setFields($key, $value);
                    }
                    $model->getContentManager()->addContentLanguage($lang->getId(), $content);
                    $model->getContentManager()->saveContentData();
                }
            }
        }
    }
}
