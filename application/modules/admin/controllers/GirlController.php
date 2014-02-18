<?php
class Admin_GirlController extends Zend_Controller_Action
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

        $this->view->salon_id = (int)$this->_getParam('salon_id');
        $this->view->headTitle()->append('Девушки Салона');
    }

    public function indexAction()
    {
        $this->view->add = (object)array (
            'link' => $this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-girl-add'),
            'alt'  => 'Добавить девушку',
            'text' => 'Добавить девушку'
        );

        $this->view->breadcrumbs->add('Девушки', '');
        $this->view->headTitle()->append('Девушки');
        $this->view->girls = Application_Model_Kernel_Girl::getList($this->view->salon_id);
    }

    public function addAction()
    {
        $this->view->back = true;
        $this->view->breadcrumbs->add('Добавление девушки');
        $this->view->headTitle()->append('Добавление девушки');
        $this->view->langs = Kernel_Language::getAll();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $content = array ();
                $i       = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->salon = new Application_Model_Kernel_Girl(null, null, $this->view->salon_id, null, $data->age);
                $this->view->salon->setContentManager($contentManager);
                $this->view->salon->validate();
                $this->view->salon->save();

                $this->_redirect($this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-girl-index'));
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
        $this->view->langs = Kernel_Language::getAll();
        $this->view->id    = (int)$this->_getParam('id');
        $this->view->salon = Application_Model_Kernel_Girl::getById($this->view->id);
        $getContent        = $this->view->salon->getContentManager()->getContent();
        foreach ($getContent as $key => $value) {
            $getContent[$key]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($getContent[$key]->getId()));
        }
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $fields = array ();
                foreach ($this->view->langs as $lang) {
                    foreach ($data->content[$lang->getId()] as $keyLang => $valueLang) {
                        foreach ($getContent as $key => $value) {
                            if ($value->getIdLang() == $lang->getId()) {
                                foreach ($value->getFields() as $keyField => $valueFields) {
                                    $gContent = $value->getFields();
                                    if ($keyLang === $valueFields->getFieldName()) {
                                        if ($valueLang !== $valueFields->getFieldText()) {
                                            $fields[] = new Application_Model_Kernel_Content_Fields($valueFields->getIdField(), $valueFields->getIdContent(), $valueFields->getFieldName(), $valueLang);
                                        } else {
                                            break;
                                        }
                                    } else if (!isset($gContent[$keyLang])) {
                                        $field = new Application_Model_Kernel_Content_Fields(null, $value->getId(), $keyLang, $valueLang);
                                        $field->save();
                                    }
                                }
                            }
                        }
                    }
                    if (isset($getContent[$lang->getId()])) {
                        $this->view->salon->getContentManager()->setLangContent($lang->getId(), $fields);
                        $fields = array ();
                    }
                }
                if (count($data->content) > count($getContent)) {
                    foreach ($getContent as $key => $value) {
                        $idContentPack = $value->getIdContentPack();
                        unset($data->content[$value->getIdLang()]);
                    }
                    foreach ($data->content as $key => $value) {
                        $content = new Application_Model_Kernel_Content_Language(null, $key, $idContentPack);
                        foreach ($value as $k => $v)
                            $content->setFields($k, $v);
                        $content->save();
                    }
                }

                $this->view->salon->setAge($data->age);
                $this->view->salon->validate();
                $this->view->salon->save();

                $this->_redirect($this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-girl-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $_POST['age']     = $this->view->salon->getAge();
            $_POST['content'] = $this->view->salon->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }

        }
    }
}