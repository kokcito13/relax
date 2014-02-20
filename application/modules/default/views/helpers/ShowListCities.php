<?php

class Zend_View_Helper_ShowListCities {

    public function ShowListCities(){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));

        $view->cities = Application_Model_Kernel_City::getList();

        return $view->render('block/city_list.phtml');
    }
}