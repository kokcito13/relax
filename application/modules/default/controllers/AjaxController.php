<?php

class AjaxController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }

    public function addCommentAction()
    {
        $response = array();
        if ($this->getRequest()->isPost()) {
            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);
            if (empty($data->first_name)) {
                $response['error']['Ваше имя'] = 'Пустое поле!';
            }

            if (empty($data->text)) {
                $response['error']['Контактный телефон'] = 'Пустое поле!';
            }

            if (!isset($data->type)) {
                $response['error']['Укажите'] = 'оценку комментария';
            }

            if (!empty($data->last_name)) {
                $response['error']['Вы'] = ' - робот!';
            }

            if (!isset($response['error']) || empty($response['error'])) {
                $comment = new Application_Model_Kernel_Comment(null, $data->salon, 0, $data->first_name, '', $data->text, time(), ip2long($_SERVER['REMOTE_ADDR']),
                                                                $data->type, Application_Model_Kernel_Comment::STATUS_CREATE, '', time());
                $comment->save();

                $response['success'] = true;
            }
        } else {
            $response['error']['Not POST'] = 'Запрос не постовый';
        }

        echo json_encode($response);

        return false;
    }

    public function addSubscribeAction()
    {
        $response = array();
        if ($this->getRequest()->isPost()) {
            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);
            if (empty($data->email)) {
                $response['error']['Ваше имя'] = 'Пустое поле!';
            }

            if (!isset($response['error']) || empty($response['error'])) {
                $subscribe = new Application_Model_Kernel_Subscribe(null, $data->city_id, $data->area_id, $data->email);
                $subscribe->save();

                $response['success'] = true;
            }
        } else {
            $response['error']['Not POST'] = 'Запрос не постовый';
        }

        echo json_encode($response);

        return false;
    }
}
