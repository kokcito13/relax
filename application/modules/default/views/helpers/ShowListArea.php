<?php

class Zend_View_Helper_ShowListArea {

    public function ShowListArea( $area = false ){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));

        $view->areas = array();
        $view->currentCity = Kernel_City::findCityFromUrl();
        $view->currentArea = new Application_Model_Kernel_Area(null, null, null, null);

        if ($view->currentCity) {
            $view->areas = Application_Model_Kernel_Area::getList();
            $view->currentArea = new Application_Model_Kernel_Area(null, null, null, null);
            if ($area) {
                $view->currentArea = $area;
            }
        }

        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($view->blocks as $key => $value) {
            $view->blocks[$key] = $value->getContent()->getFields();
        }

        return $view->render('block/area_list.phtml');
    }
}