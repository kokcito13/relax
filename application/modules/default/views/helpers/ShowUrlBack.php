<?php

class Zend_View_Helper_ShowUrlBack
{

    public function ShowUrlBack($salon)
    {
        $view = new Zend_View(array ('basePath' => APPLICATION_PATH . '/modules/default/views'));

        $view->salon    = $salon;
        $view->area = $salon->getArea();
        $view->cityUrl = Kernel_City::getUrlForLink($salon->getCity());

        return $view->render('block/back_url.phtml');
    }
}