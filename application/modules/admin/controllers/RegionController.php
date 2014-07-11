<?php
class Admin_RegionController extends Zend_Controller_Action
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

        $this->view->area_id       = (int)$this->_getParam('area_id');
        $this->view->area = Application_Model_Kernel_Area::getById($this->view->area_id);
        $this->view->areaContent = $this->view->area->getContent()->getFields();

        $this->view->headTitle()->append('Микрорайон в районе - '.$this->view->areaContent['contentName']->getFieldText());
    }

    public function indexAction()
    {
        $this->view->add = (object)array (
            'link' => $this->view->url(array ('area_id'=>$this->view->area_id ), 'admin-region-add'),
            'alt'  => 'Добавить микрорайон',
            'text' => 'Добавить микрорайон'
        );

        $this->view->breadcrumbs->add('Микрорайоны в районе - <a href="'.$this->view->url(array ('city_id'=>$this->view->area->getCityId() ), 'admin-area-index').'">'.$this->view->areaContent['contentName']->getFieldText(), '').'</a>';
        $this->view->headTitle()->append('Микрорайоны в районе - '.$this->view->areaContent['contentName']->getFieldText());
        $this->view->regions = Application_Model_Kernel_Region::getList($this->view->area_id);
    }

    public function addAction()
    {
        $this->view->back = true;
        $this->view->breadcrumbs->add('Добавление нового микрорайона в районе - '.$this->view->areaContent['contentName']->getFieldText());
        $this->view->headTitle()->append('Добавление нового микрорайона в районе - '.$this->view->areaContent['contentName']->getFieldText());
        $this->view->langs = Kernel_Language::getAll();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $content              = array ();
                $i                    = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->region = new Application_Model_Kernel_Region(null, null, $this->view->area_id, $data->url, $data->status );
                $this->view->region->setContentManager($contentManager);
                $this->view->region->validate();
                $this->view->region->save();

                $this->_redirect($this->view->url(array ('area_id'=> $this->view->area_id), 'admin-region-index'));
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
        $this->view->region = Application_Model_Kernel_Region::getById($this->view->id);
        $getContent           = $this->view->region->getContentManager()->getContent();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                Application_Model_Kernel_Content_Fields::setFieldsForModel($data->content, $getContent, $this->view->region);

                $this->view->region->setStatus($data->status);
                $this->view->region->setUrl($data->url);
                $this->view->region->validate();
                $this->view->region->save();
                $this->_redirect($this->view->url(array ('area_id'=>$this->view->area_id), 'admin-region-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $_POST['url']     = $this->view->region->getUrl();
            $_POST['status']     = $this->view->region->getStatus();
            $_POST['content']     = $this->view->region->getContentManager()->getContents();
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
        $this->view->region = Application_Model_Kernel_Region::getById($this->view->id);
        $this->view->region->delete();

        $this->_redirect($this->view->url(array ('area_id'=>$this->view->area_id), 'admin-region-index'));

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }
}