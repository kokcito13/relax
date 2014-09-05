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


                $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
                $view->comment = $comment;

                $html = $view->render('block/_mail_comment.phtml');

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($html);
                $mail->setFrom('info@my-relax.net', 'Новый отзыв на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('pavlova08@gmail.com', 'Новый отзыв на '.$_SERVER['SERVER_NAME']);
                $mail->setSubject('Новый отзыв на '.$_SERVER['SERVER_NAME']);
                $mail->send();

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


    public function getSalonsAction()
    {
        $response = array();
        if ($this->getRequest()->isGet()) {
            $data = $_GET;
            $where = false;

            $word = trim($data['word']);
            $city = (int)$data['city'];
            $area = (int)$data['area'];
            $page = (int)$data['page'];

            if (!empty($word)) {
                $word = '%'.$this->view->word.'%';
            } else {
                $word = false;
            }

            if ($city != 0) {
                $where = 'salons.city_id = '.$city;
                if ($area != 0) {
                    $where .= " AND salons.area_id = ".$area;
                }
            }

            $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
            $view->salons = Application_Model_Kernel_Salon::getList('salons.call_price', "DESC", true, true, $word, 1, $page, Application_Model_Kernel_Salon::ITEM_ON_PAGE, false, true, $where);
            $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
            foreach ($view->blocks as $key => $value) {
                $view->blocks[$key] = $value->getContent()->getFields();
            }

            if ($page == $view->salons->paginator->count()) {
                $page = 0;
            } else {
                $page++;
            }
            $view->siteSetings = Application_Model_Kernel_SiteSetings::getBy();

            $response['html'] = $view->render('block/list.phtml');
            $response['page'] = $page;
            $response['success'] = true;
        } else {
            $response['error']['Not POST'] = 'Запрос не постовый';
        }

        echo json_encode($response);

        return false;
    }

    public function saveSessionPhoneAction()
    {
        $response = array();
        if ($this->getRequest()->isGet()) {
            $data = $_GET;
            $salonId = (int)$data['salon_id'];

            $salon = Application_Model_Kernel_Salon::getById($salonId);
            if ($salon) {
                $salon->setSessionPhone();
            }

            $response['success'] = true;
        } else {
            $response['error']['Not POST'] = 'Запрос не постовый';
        }

        echo json_encode($response);

        return false;
    }
}
