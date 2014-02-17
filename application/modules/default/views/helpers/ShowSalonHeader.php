<?php

class Zend_View_Helper_ShowSalonHeader {

    public function ShowSalonHeader($salon){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        
        $view->salon = $salon;
        $view->contentPage = $salon->getContent()->getFields();

        return $view->render('block/salon_header.phtml');
    }
}