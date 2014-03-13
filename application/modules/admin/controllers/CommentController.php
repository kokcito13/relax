<?php
class Admin_CommentController extends Zend_Controller_Action {
	
	public function preDispatch(){
		if(!Application_Model_Admin_Admin::isAuthorized())
			$this->_redirect($this->view->url(array(),'admin-login'));
		else
			$this->view->blocks = (object)array('menu' => true);
		$this->view->add = false;
		$this->view->back = false;
		$this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
		$this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
		$this->view->headTitle()->append('Комменты');

        $this->view->salon_id = (int)$this->_getParam('salon_id');
	}

	public function indexAction() {
		
        $this->view->breadcrumbs->add('Комменты', '');
        $this->view->headTitle()->append('Комменты');
        $this->view->type = (int)$this->_getParam('type');
        $this->view->ownerId = (int)$this->_getParam('idProduct');
		$this->view->comments = Application_Model_Kernel_Comment::getList($this->view->salon_id);
	}
	
	public function editAction() {
		$this->view->headTitle()->append('Комменты');
		$this->view->back = true;
        $this->view->comment = Application_Model_Kernel_Comment::getById((int)$this->_getParam('id'));


        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            $this->view->comment->setCommentStatus($data->status);
            $this->view->comment->setCommentAdminText($data->text);
            $this->view->comment->setCommentAdminDate(time());
            $this->view->comment->setCommentNick($data->name);
            $this->view->comment->setCommentText($data->text_comment);
            $this->view->comment->save();

            $this->_redirect($this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-comment-index'));
        }
	}

    public function deleteAction()
    {
        $this->view->id       = (int)$this->_getParam('id');
        $this->view->comment = Application_Model_Kernel_Comment::getById($this->view->id);
        $this->view->comment->delete();

        $this->_redirect($this->view->url(array ('salon_id' => $this->view->salon_id), 'admin-comment-index'));

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }
}