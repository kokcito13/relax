<?php
class Admin_MassageController extends Zend_Controller_Action
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
        $this->view->headTitle()->append('Массаж');
    }

    public function indexAction()
    {
        $this->view->add = (object)array (
            'link' => $this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-massage-add'),
            'alt'  => 'Добавить массаж',
            'text' => 'Добавить массаж'
        );

        $this->view->breadcrumbs->add('Массаж', '');
        $this->view->headTitle()->append('Массаж');
        $this->view->massages = Application_Model_Kernel_Massage::getList($this->view->salon_id);
    }

    public function addAction()
    {
        $this->view->back = true;
        $this->view->breadcrumbs->add('Добавление массажа');
        $this->view->headTitle()->append('Добавление массажа');
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

                $this->view->massage = new Application_Model_Kernel_Massage(null, null, $this->view->salon_id, $data->price);
                $this->view->massage->setContentManager($contentManager);
                $this->view->massage->validate();
                $this->view->massage->save();

                $this->_redirect($this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-massage-index'));
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
        $this->view->langs   = Kernel_Language::getAll();
        $this->view->id      = (int)$this->_getParam('id');
        $this->view->massage = Application_Model_Kernel_Massage::getById($this->view->id);
        $getContent          = $this->view->massage->getContentManager()->getContent();
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
                        $this->view->massage->getContentManager()->setLangContent($lang->getId(), $fields);
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

                $this->view->massage->setPrice($data->price);
                $this->view->massage->validate();
                $this->view->massage->save();

                $this->_redirect($this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-massage-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $_POST['price']   = $this->view->massage->getPrice();
            $_POST['content'] = $this->view->massage->getContentManager()->getContents();
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
        $this->view->salon = Application_Model_Kernel_Massage::getById($this->view->id);
        $this->view->salon->delete();

        $this->_redirect($this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-massage-index'));

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }
}