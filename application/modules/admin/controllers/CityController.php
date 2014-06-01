<?php
class Admin_CityController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array (), 'admin-login'));
        else
            $this->view->blocks = (object)array ('menu' => true);
        $this->view->add         = false;
        $this->view->back        = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page        = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->headTitle()->append('Города');
    }

    public function indexAction()
    {
        $this->view->add = (object)array (
            'link' => $this->view->url(array (), 'admin-city-add'),
            'alt'  => 'Добавить город',
            'text' => 'Добавить город'
        );

        $this->view->breadcrumbs->add('Города', '');
        $this->view->headTitle()->append('Города');
        $this->view->cities = Application_Model_Kernel_City::getList();
    }

    public function addAction()
    {
        $this->view->back = true;
        $this->view->breadcrumbs->add('Добавление нового города');
        $this->view->headTitle()->append('Добавление нового города');
        $this->view->langs = Kernel_Language::getAll();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->parentId = $data->parentId;
                $content              = array ();
                $i                    = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->category = new Application_Model_Kernel_City(null, null, null, $data->status);
                $this->view->category->setContentManager($contentManager);
                $this->view->category->setUrl($data->url);
                $this->view->category->validate();
                $this->view->category->save();

                $this->_redirect($this->view->url(array (), 'admin-city-index'));

            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
    }

    public function editAction()
    {
        $this->view->headTitle()->append('Редактировать город');
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->langs    = Kernel_Language::getAll();
        $this->view->id       = (int)$this->_getParam('id');
        $this->view->category = Application_Model_Kernel_City::getById($this->view->id);
        $getContent           = $this->view->category->getContentManager()->getContent();

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                Application_Model_Kernel_Content_Fields::setFieldsForModel($data->content, $getContent, $this->view->category);

                $this->view->category->setUrl($data->url);
                $this->view->category->setStatus($data->status);
                $this->view->category->validate();
                $this->view->category->save();
                $this->_redirect($this->view->url(array (), 'admin-city-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $_POST['content'] = $this->view->category->getContentManager()->getContents();
            $_POST['url']     = $this->view->category->getUrl();
            $_POST['status']     = $this->view->category->getStatus();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
        }
    }

    public function deleteAction()
    {
        $this->view->id       = (int)$this->_getParam('id');
        $this->view->city = Application_Model_Kernel_City::getById($this->view->id);
        $this->view->city->delete();

        $this->_redirect($this->view->url(array (), 'admin-city-index'));

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }
}