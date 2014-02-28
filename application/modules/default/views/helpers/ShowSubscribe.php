<?php

class Zend_View_Helper_ShowSubscribe
{
    public function ShowSubscribe()
    {
        $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
        $view->city = Kernel_City::findCityFromUrl();
        $view->cityContent = false;
        if ($view->city) {
            $view->cityContent = $view->city->getContent()->getFields();
        }

        return $view->render('block/subscribe.phtml');
    }
}