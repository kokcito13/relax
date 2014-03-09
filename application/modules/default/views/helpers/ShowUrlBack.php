<?php

class Zend_View_Helper_ShowUrlBack
{

    public function ShowUrlBack($salon)
    {
        $view = new Zend_View(array ('basePath' => APPLICATION_PATH . '/modules/default/views'));

        $view->salon    = $salon;
        $view->salonContent = $salon->getContent()->getFields();
        $view->area = $salon->getArea();
        $view->areaContent = $view->area->getContent()->getFields();
        $view->city = $salon->getCity();
        $view->cityUrl = Kernel_City::getUrlForLink($view->city);
        $view->cityContent = $view->city->getContent()->getFields();

        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($view->blocks as $key => $value) {
            $view->blocks[$key] = $value->getContent()->getFields();
        }

        return $view->render('block/back_url.phtml');
    }
}