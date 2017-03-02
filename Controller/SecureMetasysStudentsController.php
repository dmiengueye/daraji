<?php
App::uses('UsersController', 'Users.Controller');
class StudentsController extends UsersController {
    public function beforeFilter() {
        parent::beforeFilter();
        $this->modelClass = 'Student';
        $this->set('model', 'Student');
        $this->Auth->allow('complete','passwordchangeconfirm','passwordrecoveryconfirm');
    }
    protected function _setupAuth() {
        $this->modelClass = 'Student';
        parent::_setupAuth();        
        AuthComponent::$sessionKey = 'Auth.Student';
        $this->Auth->logoutRedirect = array('plugin' => null, 'controller' => 'students', 'action' => 'login');
        $this->Auth->loginAction    = array('admin' => false, 'plugin' => null, 'controller' => 'students', 'action' => 'login');                
    }
    public function register() {
        $this->layout = 'default';
        // Call Function of UserPlugin
        $this->add();
    }
    public function complete() {
        $this->layout = 'default';
        if($this->Session->check('completeEmail')){
            $this->set('completeEmail',$this->Session->read('completeEmail'));
            $this->Session->delete('completeEmail');
        }else{
            return $this->redirect(array('action' => 'login','controller'=>'students'));
        }
    }
    public function passwordchangeconfirm(){
	}
	public function passwordrecoveryconfirm(){
	}
    public function home() {
        $this->set('title_for_layout', 'Daraji- Student Home');
        $this->layout = 'student';
    }
    public function welcome() {
        $this->layout = 'student';
    }
    public function account_settings() {
        $this->layout = 'student';
    }
    public function manage_profile(){
        $this->layout = 'student';
    }
    public function change_password(){
        $this->layout = 'student';
    }
    public function change_email(){
        $this->layout = 'student';
    }
    public function email_preference(){
        $this->layout = 'student';
    }

    public function homeroomempty() {
        $this->layout = 'student';
    }

    public function home_room() {
        $this->layout = 'student';
    }

    public function tutorsearchresultslistview() {
        $this->set('title_for_layout', 'Daraji-Tutor Search Results');
        $this->layout = 'default';
    }

    public function studentsearchresults() {
        $this->set('title_for_layout', 'Daraji-Tutor Search Results');
        $this->layout = 'searchresults';
    }

    public function tutorprofiledetail() {
        $this->set('title_for_layout', 'Daraji-Tutor Search Results');
        $this->layout = 'student';
    }

    public function tutor_details_profile_auth() {
        $this->set('title_for_layout', 'Daraji-Tutor Search Results');
        $this->layout = 'student';
    }

    public function tutor_details_profile() {
        $this->set('title_for_layout', 'Daraji-Tutor Search Results');
        $this->layout = 'student';
    }

    public function studentpasswordrecovery() {
        //$this->layout='default';
        //logic to route to the logic for verifying submitted email and recovering pwd
        //if successful (ie, user was found in DB) then send
        return $this->redirect(array('action' => 'passwordrecoveryconfirm'));
        //else {
        // return $this->redirect(array('action' => 'usernotfound'));
        //}
    //
  }

    public function usernotfound() {
        $this->layout = 'default';
    }

    public function chgPwd() {
        $this->layout = 'student';
    }

    public function addEmailAddress() {
        $this->layout = 'student';
    }

    public function requestTutor() {
        $this->layout = 'student';
    }

    public function tellYourFriends() {
        $this->layout = 'student';
    }

    public function contact() {
        $this->layout = 'default';
    }
    public function helpstudent() {
        $this->layout = 'student';
    }

    public function tutorsearchresultsauth() {
        $this->layout = 'student';
    }

    public function tutorsearchresultsauthwithbootstrapmin() {
        $this->layout = 'student';
    }

    public function safetytips() {
        $this->layout = 'student';
    }

    public function post_job() {
        $this->layout = 'student';
    }

    public function myaccount() {
        $this->layout = 'student';
    }

    public function request_tutor() {
        $this->layout = 'default';
    }

    public function lesson_scheduling() {
        $this->layout = 'student';
    }

    public function my_lessons() {
        $this->layout = 'student';
    }

    public function my_scheduled_lessons() {
        $this->layout = 'student';
    }

    public function my_completed_lessons() {
        $this->layout = 'student';
    }

    public function my_tutors() {
        $this->layout = 'student';
    }

    public function my_tutor_watch_list() {
        $this->layout = 'student';
    }

    public function my_tutor_search_agents() {
        $this->layout = 'student';
    }

    public function tutor_search_tools() {
        $this->layout = 'student';
    }

    public function my_pending_feedback() {
        $this->layout = 'student';
    }

    public function myfeedback() {
        $this->layout = 'student';
    }

    public function student_review_of_daraji() {
        $this->layout = 'student';
    }

    public function student_review_of_tutor() {
        $this->layout = 'student';
    }

    public function notes_on_tutor() {
        $this->layout = 'student';
    }

    public function account_confirm() {
        $this->layout = 'default';
    }

    /*public function logout() {
        $this->layout = 'student';
        $this->Session->delete("LOGGEDIN_USER_TYPE");
        $this->Auth->logout();

        return $this->redirect($this->Auth->logout());
    }*/

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