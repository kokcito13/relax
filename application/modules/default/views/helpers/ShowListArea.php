<?php

class Zend_View_Helper_ShowListArea {

    public function ShowListArea( $area = false ){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        $view->areas = Application_Model_Kernel_Area::getList();


        return $view->render('block/area_list.phtml');
    }
}