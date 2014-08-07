<?php

class Admin_SalonController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array(), 'admin-login'));
        else
            $this->view->blocks = (object)array('menu' => true);
        $this->view->add = false;
        $this->view->back = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->headTitle()->append('Список салонов');
        $this->view->cities = Application_Model_Kernel_City::getList();
        $this->view->areas = Application_Model_Kernel_Area::getList();
    }

    public function indexAction()
    {
        $this->view->add = (object)array(
            'link' => $this->view->url(array(), 'admin-salon-add'),
            'alt'  => 'Добавить салон',
            'text' => 'Добавить салон'
        );

        $this->view->page = (int)$this->_getParam('page');

        $this->view->breadcrumbs->add('Список салонов', '');
        $this->view->headTitle()->append('Список салонов');
        $this->view->salons = Application_Model_Kernel_Salon::getList(false, false, true, true, false, false, $this->view->page, 15, false, true, false);
    }

    public function addAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->idPage = null;
        $this->view->tinymce = true;
        $this->view->back = true;
        $this->view->edit = false;

        $this->view->idPhoto1 = 0;
        $this->view->idPhoto2 = 0;
        $this->view->idPhoto3 = 0;
        $this->view->idPhoto4 = 0;


        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
                $this->view->idPhoto2 = (int)$data->idPhoto2;
                $this->view->photo2 = Application_Model_Kernel_Photo::getById($this->view->idPhoto2);
                $this->view->idPhoto3 = (int)$data->idPhoto3;
                $this->view->photo3 = Application_Model_Kernel_Photo::getById($this->view->idPhoto3);
                $this->view->idPhoto4 = (int)$data->idPhoto4;
                $this->view->photo4 = Application_Model_Kernel_Photo::getById($this->view->idPhoto4);

                $url = new Application_Model_Kernel_Routing_Url("/");
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams();
                $route = new Application_Model_Kernel_Routing(null, Application_Model_Kernel_Routing::TYPE_ROUTE, '~public', 'default', 'salon', 'show', $url, $defaultParams, Application_Model_Kernel_Routing::STATUS_ACTIVE);

                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);
                $this->view->salon = new Application_Model_Kernel_Salon(null,
                                                                        $this->view->idPhoto1, $this->view->idPhoto2, $this->view->idPhoto3, $this->view->idPhoto4,
                                                                        null, null, null,
                                                                        time(), Application_Model_Kernel_Page_ContentPage::STATUS_SHOW, 0,
                                                                        null, null, null, null, null, null);

                $this->view->salon->setContentManager($contentManager);
                $this->view->salon->setRoute($route);
                
                $this->view->salon->setPhone($data->phone);
                $this->view->salon->setLat($data->lat);
                $this->view->salon->setLng($data->lng);
                $this->view->salon->setCityId($data->city_id);
                if (isset($data->area_id[$data->city_id]))
                    $this->view->salon->setAreaId($data->area_id[$data->city_id]);
                $this->view->salon->setCallPrice($data->call_price);

                $this->view->salon->setPath($data);
                $this->view->salon->validate($data);

                $this->view->salon->save();
                $this->view->salon->updatePath();

                $this->_redirect($this->view->url(array('page' => 1), 'admin-salon-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }

        $this->view->breadcrumbs->add('Добавить салон', '');
        $this->view->headTitle()->append('Добавить');
    }

    public function editAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->back = true;
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce = true;
        $this->view->edit = true;
        $this->view->salon = Application_Model_Kernel_Salon::getById((int)$this->_getParam('id'));

        $this->view->idPhoto1 = $this->view->salon->getIdPhoto1();
        $this->view->idPhoto2 = $this->view->salon->getIdPhoto2();
        $this->view->idPhoto3 = $this->view->salon->getIdPhoto3();
        $this->view->idPhoto4 = $this->view->salon->getIdPhoto4();

        $getContent = $this->view->salon->getContentManager()->getContent();
        $this->view->idPage = $this->view->salon->getIdPage();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                Application_Model_Kernel_Content_Fields::setFieldsForModel($data->content, $getContent, $this->view->salon);

                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
                $this->view->salon->setIdPhoto1($this->view->idPhoto1);

                $this->view->idPhoto2 = (int)$data->idPhoto2;
                $this->view->photo2 = Application_Model_Kernel_Photo::getById($this->view->idPhoto2);
                $this->view->salon->setIdPhoto2($this->view->idPhoto2);

                $this->view->idPhoto3 = (int)$data->idPhoto3;
                $this->view->photo3 = Application_Model_Kernel_Photo::getById($this->view->idPhoto3);
                $this->view->salon->setIdPhoto3($this->view->idPhoto3);

                $this->view->idPhoto4 = (int)$data->idPhoto4;
                $this->view->photo4 = Application_Model_Kernel_Photo::getById($this->view->idPhoto4);
                $this->view->salon->setIdPhoto4($this->view->idPhoto4);

                $this->view->salon->setPhone($data->phone);
                $this->view->salon->setLat($data->lat);
                $this->view->salon->setLng($data->lng);
                $this->view->salon->setCityId($data->city_id);
                $this->view->salon->setAreaId($data->area_id[$data->city_id]);
                $this->view->salon->setCallPrice($data->call_price);

                $this->view->salon->validate($data);
                $this->view->salon->save();

                $this->_redirect($this->view->url(array('page' => 1), 'admin-salon-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
            $this->view->photo2 = Application_Model_Kernel_Photo::getById($this->view->idPhoto2);
            $this->view->photo3 = Application_Model_Kernel_Photo::getById($this->view->idPhoto3);
            $this->view->photo4 = Application_Model_Kernel_Photo::getById($this->view->idPhoto4);

            $_POST['url'] = mb_substr(Application_Model_Kernel_Routing::getById($this->view->salon->getIdRoute())->getUrl(), 1);
            $_POST['url'] = mb_substr($_POST['url'], 0, -5);

            $_POST['phone'] = $this->view->salon->getPhone();
            $_POST['lat'] = $this->view->salon->getLat();
            $_POST['lng'] = $this->view->salon->getLng();
            $_POST['city_id'] = $this->view->salon->getCityId();
            $_POST['area_id'] = $this->view->salon->getAreaId();
            $_POST['call_price'] = $this->view->salon->getCallPrice();

            $_POST['content'] = $this->view->salon->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
        }
        $this->view->breadcrumbs->add('Редактировать', '');
        $this->view->headTitle()->append('Редактировать');
    }

    public function statuschangeAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();

            $this->view->project = Application_Model_Kernel_Product::getById((int)$data->idProduct);
            if ($this->view->project->getProductStatusPopular() != 2)
                $this->view->project->setProductStatusPopular(2);
            else
                $this->view->project->setProductStatusPopular(1);
            $this->view->project->save();
            echo 1;
            exit();
        }
        echo 0;
        exit();
    }

    public function mainchangeAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();

            $this->view->project = Application_Model_Kernel_Project::getById((int)$data->idProject);
            if ($this->view->project->getProjectMain() == 1)
                $this->view->project->setProjectMain(0);
            else
                $this->view->project->setProjectMain(1);
            $this->view->project->save();
            echo 1;
            exit();
        }
        echo 0;
        exit();
    }

    public function changepositionprojectAction()
    {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            Application_Model_Kernel_Project::changePosition((int)$data->id, (int)$data->val);
            echo 1;
        }
    }
}