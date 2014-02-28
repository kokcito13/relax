<?php
class Admin_AreaController extends Zend_Controller_Action
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

        $this->view->city_id       = (int)$this->_getParam('city_id');
        $this->view->city = Application_Model_Kernel_City::getById($this->view->city_id);
        $this->view->cityContent = $this->view->city->getContent()->getFields();

        $this->view->headTitle()->append('Районы города - '.$this->view->cityContent['contentName']->getFieldText());
    }

    public function indexAction()
    {
        $this->view->add = (object)array (
            'link' => $this->view->url(array ('city_id'=>$this->view->city_id ), 'admin-area-add'),
            'alt'  => 'Добавить район в городе - '.$this->view->cityContent['contentName']->getFieldText(),
            'text' => 'Добавить район в городе - '.$this->view->cityContent['contentName']->getFieldText()
        );

        $this->view->breadcrumbs->add('Районы города - '.$this->view->cityContent['contentName']->getFieldText(), '');
        $this->view->headTitle()->append('Районы города - '.$this->view->cityContent['contentName']->getFieldText());
        $this->view->areas = Application_Model_Kernel_Area::getList();
    }

    public function addAction()
    {
        $this->view->back = true;
        $this->view->breadcrumbs->add('Добавление нового района города - '.$this->view->cityContent['contentName']->getFieldText());
        $this->view->headTitle()->append('Добавление нового района города - '.$this->view->cityContent['contentName']->getFieldText());
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

                $this->view->area = new Application_Model_Kernel_Area(null, null, $this->view->city_id, $data->url );
                $this->view->area->setContentManager($contentManager);
                $this->view->area->validate();
                $this->view->area->save();

                $this->_redirect($this->view->url(array ('city_id'=> $this->view->city_id), 'admin-area-index'));
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
        $this->view->area = Application_Model_Kernel_Area::getById($this->view->id);
        $getContent           = $this->view->area->getContentManager()->getContent();
        foreach ($getContent as $key => $value) {
            $getContent[$key]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($getContent[$key]->getId()));
        }
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $fields               = array ();
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
                        $this->view->area->getContentManager()->setLangContent($lang->getId(), $fields);
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
                $this->view->area->setUrl($data->url);
                $this->view->area->validate();
                $this->view->area->save();
                $this->_redirect($this->view->url(array ('city_id'=>$this->view->city_id), 'admin-area-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $_POST['url']     = $this->view->area->getUrl();
            $_POST['content']     = $this->view->area->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }

        }
    }
}