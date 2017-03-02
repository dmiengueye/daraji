<?php
App::uses('UsersController', 'Users.Controller');
class LoginsController extends UsersController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('complete','passwordchangeconfirm','passwordrecoveryconfirm');
    }
    protected function _setupAuth() {
        if(!empty($this->request->data['Student'])){
            $this->modelClass = 'Student';
        }elseif(!empty($this->request->data['Tutor'])){
            $this->modelClass = 'Tutor';
        }
        parent::_setupAuth();
        $this->Auth->logoutRedirect = Configure::read('logoutRedirect');
		$this->Auth->loginAction = Configure::read('loginAction');
    }
	
	
    public function render($view = null, $layout = null) {
        if (is_null($view)) {
            $view = $this->action;
        }
        $viewPath = substr(get_class($this), 0, strlen(get_class($this)) - 10);
        if (!file_exists(APP . 'View' . DS . $viewPath . DS . $view . '.ctp')) {
            $this->plugin = 'Users';
        } else {
            $this->viewPath = $viewPath;
        }
        return parent::render($view, $layout);
    }

}