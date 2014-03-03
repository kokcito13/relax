<?php
class Kernel_Block
{
    public function __construct()
    {
    }

    public static function getText($key, $array)
    {
        $text = strip_tags(self::getCode($key, $array));

        return trim($text);
    }

    public static function getCode($key, $array)
    {
        $text = $key;

        if (isset($array[$key])) {
            $text = $array[$key]['contentName']->getFieldText();
        } else {
            $block = Application_Model_Kernel_Block::getByName($key);
            if ($block) {
                $content = $block->getContent()->getFields();
                $text = $content['contentName']->getFieldText();
            } else {
                self::saveNewKey($key);
            }
        }

        return $text;
    }

    public static function saveNewKey($key)
    {
        $content = array();
        $i = 0;
        foreach (Kernel_Language::getAll() as $lang) {
            $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
            $content[$i]->setFields('contentName', $key);
            $content[$i]->setFields('name', $key);
            $i++;
        }
        $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

        $block = new Application_Model_Kernel_Block(null, null, $key);
        $block->setContentManager($contentManager);
        $block->save();
    }
}