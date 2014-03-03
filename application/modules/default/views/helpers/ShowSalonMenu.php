<?php

class Zend_View_Helper_ShowSalonMenu
{
    public function ShowSalonMenu($salon, $current)
    {
        $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
        $view->salon = $salon;
        $view->current = $current;

        $view->countAllComments = count($salon->getComments());

        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($view->blocks as $key => $value) {
            $view->blocks[$key] = $value->getContent()->getFields();
        }

        return $view->render('block/salon_menu.phtml');
    }
}