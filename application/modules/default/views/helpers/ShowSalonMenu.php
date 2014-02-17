<?php

class Zend_View_Helper_ShowSalonMenu
{
    public function ShowSalonMenu($salon, $current)
    {
        $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
        $view->salon = $salon;
        $view->current = $current;

        return $view->render('block/salon_menu.phtml');
    }
}