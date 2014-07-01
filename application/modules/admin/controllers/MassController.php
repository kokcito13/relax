<?php

class Admin_MassController extends Zend_Controller_Action
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
        $this->view->headTitle()->append('Список видов массажей');
    }

    public function indexAction()
    {
        $this->view->add = (object)array(
            'link' => $this->view->url(array(), 'admin-mass-add'),
            'alt'  => 'Добавить вид',
            'text' => 'Добавить вид'
        );

        $this->view->page = (int)$this->_getParam('page');

        $this->view->breadcrumbs->add('Список видов массажей', '');
        $this->view->headTitle()->append('Список видов массажей');
        $this->view->masses = Application_Model_Kernel_Mass::getList(false, false, true, true, false, false, $this->view->page, 15, false, true, false);
    }

    public function addAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->idPage = null;
        $this->view->tinymce = true;
        $this->view->back = true;
        $this->view->edit = false;
        $this->view->idPhoto1 = 0;

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

                $url = new Application_Model_Kernel_Routing_Url("/massages/".$data->url.'.html');
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams();
                $route = new Application_Model_Kernel_Routing(null, Application_Model_Kernel_Routing::TYPE_ROUTE, '~public', 'default', 'mass', 'show', $url, $defaultParams, Application_Model_Kernel_Routing::STATUS_ACTIVE);

                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);
                $this->view->mass = new Application_Model_Kernel_Mass(null,
                                                                        $this->view->idPhoto1,
                                                                        null, null, null,
                                                                        time(), Application_Model_Kernel_Page_ContentPage::STATUS_SHOW, 0
                                                                        );

                $this->view->mass->setContentManager($contentManager);
                $this->view->mass->setRoute($route);
                $this->view->mass->validate($data);
                $this->view->mass->save();

                $this->_redirect($this->view->url(array('page' => 1), 'admin-mass-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }

        $this->view->breadcrumbs->add('Добавить вид', '');
        $this->view->headTitle()->append('Добавить');
    }

    public function editAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->back = true;
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce = true;
        $this->view->edit = true;
        $this->view->mass = Application_Model_Kernel_Mass::getById((int)$this->_getParam('id'));

        $this->view->idPhoto1 = $this->view->mass->getIdPhoto1();

        $getContent = $this->view->mass->getContentManager()->getContent();
        $this->view->idPage = $this->view->mass->getIdPage();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                Application_Model_Kernel_Content_Fields::setFieldsForModel($data->content, $getContent, $this->view->mass);

                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
                $this->view->mass->setIdPhoto1($this->view->idPhoto1);

                $this->view->mass->getRoute()->setUrl('/massages/'.$data->url.'.html');

                $this->view->mass->validate($data);
                $this->view->mass->save();

                $this->_redirect($this->view->url(array('page' => 1), 'admin-mass-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

            $_POST['url'] = mb_substr(Application_Model_Kernel_Routing::getById($this->view->mass->getIdRoute())->getUrl(), 1);
            $_POST['url'] = mb_substr($_POST['url'], 9, -5);

            $_POST['content'] = $this->view->mass->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
        }
        $this->view->breadcrumbs->add('Редактировать', '');
        $this->view->headTitle()->append('Редактировать');
    }


    public function deleteAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        $this->view->mass = Application_Model_Kernel_Mass::getById((int)$this->_getParam('id'));
        if ($this->view->mass) {
            try {
                $this->view->mass->delete();
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
        $this->_redirect($this->view->url(array('page' => 1), 'admin-mass-index'));
    }
}