<?php
/**
 * Static content controller.
 *
 * This file will render views from Views/Students
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

//App::uses('AppController', 'Controller');
App::uses('UsersController', 'Users.Controller');

// app/Controller/CommonsController.php
class CommonsController extends UsersController {


/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Commons';
	public $uses = array ('Common'); //, 'StudentPreference', 'StudentProfile', 'ZipSearch', 'Tutor');
    public $components = array('Paginator','RequestHandler');
    public $helpers = array('Js','ZipCode', 'Ajax');

   // var $helpers = array('Ajax');



/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

		parent::beforeFilter();
        $userType = $this->Session->read('loggedInUserType');

        if($userType == 'Auth.Student') {
            AuthComponent::$sessionKey = 'Auth.Student';
            Configure::write('allsubjects',$this->get_all_subjects());

        } else if($userType == 'Auth.Tutor') {
            AuthComponent::$sessionKey = 'Auth.Tutor';
        } else {
             AuthComponent::$sessionKey = 'Auth.Common';
        }

         //$this->_setupPagination();
          $this->set('model', 'Common');

        //$userType = $this->Session->read('loggedInUserType');
        if($userType != null && !empty($userType) ) {
              $this->Session->write('commonLayout', $this->Session->read('view_layout') );
              $this->set('userType', $userType);

        } else {

            $this->Session->write('commonLayout', 'default' );
            $this->Session->write('view_layout', 'default' );
            $this->set('userType', $userType);
        }
 }

public function index() {

    $this->set('title_for_layout', 'Find a Private Tutor | Daraji Tutoring');
    if($this->Session->check('view_layout') ) {
	      $this->layout = $this->Session->read('view_layout');
    }

   if($this->layout == 'tutor') {
       return  $this->redirect(array('controller' => 'tutors', 'action' => 'home'));
   } else  if($this->layout == 'student') {
      return  $this->redirect(array('controller' => 'students', 'action' => 'home'));
   }

}

public function resources() {
    $this->layout = 'default';
   if($this->Session->check('commonLayout') ) {
      $this->layout = $this->Session->read('commonLayout');
   }

}


public function aboutus() {
   
   $this->layout = 'default';
   $this->set('title_for_layout', 'About Daraji | Daraji Tutoring');

     if($this->Session->check('view_layout') ) {
	      $this->layout = $this->Session->read('view_layout');
          //debug($this->layout); die();
   }
}

public function faqs_help() {
    $this->layout = 'default';
    $this->set('title_for_layout', 'Faqs-Help | Daraji Tutoring');

    if($this->Session->check('view_layout') ) {
	      $this->layout = $this->Session->read('view_layout');

   }
}

public function contactus() {
        $this->layout = 'default';
       $this->set('title_for_layout', 'Contact Us | Daraji Tutoring');
       if($this->Session->check('commonLayout') ) {
         $this->layout = $this->Session->read('commonLayout');
      }
}

public function tutor_details_profile() {
      $loggedInUserType = $this->Session->read('loggedInUserType');
      $this->layout = 'default';
      $this->set('title_for_layout', 'Tutor Search | Daraji Tutoring');
	  if($this->Session->check('commonLayout') ) {
	 	  $this->layout = $this->Session->read('commonLayout');
       }
 }

public function welcome() {

        $userType = $this->Session->read('loggedInUserType');
        //debug($userType); //die();
        if($userType === 'Auth.Student') {
           // AuthComponent::$sessionKey = 'Auth.Student';
            $this->redirect(array('controller' => 'students', 'action' => 'welcome'));
        } else if($userType === 'Auth.Tutor') {
           // AuthComponent::$sessionKey = 'Auth.Tutor';
            $this->redirect(array('controller' => 'tutors', 'action' => 'welcome'));
        }
}


public function login() {

       if ($this->Auth->loggedIn()) {
         	 return $this->redirect(array('action' => 'welcome'));
        }

       $this->layout = 'login_default';
       //$this->layout = 'default';
       //debug("test me out"); die();
       if ($this->request->is('post')) {
          if(!empty($this->request->data[$this->modelClass])) {
                   //  debug($this->modelClass); die();
                          $throttle_delay = $this->throttleUserLogin($this->request->data[$this->modelClass]['email']);
                          $user = $this->{$this->modelClass}->getUserRecord($this->request->data[$this->modelClass]['email'], $options = array());
                         // debug($user); die();
                          if(!empty($user)) {
                             $lock_account = $user[$this->modelClass]['lock_account'];

                             //Need to make sure user email has previously been verified
                             $email_verified = $user[$this->modelClass]['email_verified'];
                             $this->Session->write('email_verified', $email_verified);

                             if(isset($lock_account) && !empty($lock_account)) {
                                 $message = "This account has been temporarily suspended. Please contact Wizwonk Tech Support for further informtion. ";
                                 $this->Session->setFlash($message, 'custom_msg');
                                 $this->redirect($this->Auth->loginAction);
                              }
                              if(isset($user[$this->modelClass]['captcha']) || !empty($user[$this->modelClass]['captcha'])) {
                                  // debug("testme"); //die();
                                  $this->Session->write('u_captcha', $user[$this->modelClass]['captcha'] );
                                  $this->_setCookie($options=array(), 'user_recaptcha');
                                  $this->set('user_recaptcha', $this->Cookie->read('user_recaptcha'));
                              } //else {
                                //$this->Recaptcha->destroyCookie();
                              //}

                          }
                          if(!empty($throttle_delay) && $throttle_delay > 0) {
                                        $delay = $this->Session->read('delay');
                                        $this->Session->delete('delay');

                                        $message = "Too many failed logins. You must wait " .$delay. " seconds before you can attempt another login again ";
                                        $this->Session->setFlash($message, 'custom_msg');
                                        $this->redirect($this->Auth->loginAction);
                         }    //debug($user[$this->modelClass]['captcha']); debug($this->request->data[$this->modelClass]['captcha']);

                        /** if(!empty($user[$this->modelClass]['captcha'])) { //} && empty($this->request->data[$this->modelClass]['captcha']) ) {
                                           $message = 'Please resolve the Captcha!';
                                           $this->Session->setFlash($message, 'custom_msg');
                                           $this->redirect($this->Auth->loginAction);

                         } else**/

                         if(!empty($user[$this->modelClass]['captcha']) && !empty($this->request->data[$this->modelClass]['captcha'])){

                                  if(!empty($this->request->data['g-recaptcha-response'])) {
                                	    $response = $this->{$this->modelClass}->solveCaptcha($this->request->data['g-recaptcha-response']);
                                        if(!$response) {
                                            $message = 'Please Resolve Captcha Error and Try Again!!';
                                            $this->Session->setFlash($message, 'custom_msg');
                                            $this->redirect($this->referer('/'));
                                        }
                                     } else {
                                            $message = 'Please Check the Captcha Box to prove You are NOT a ROBOT';
                                            $this->Session->setFlash($message, 'custom_msg');
                                            $this->redirect($this->referer('/'));
                                      }
                        }
                       //  debug("I am here"); die();
                        parent::login();
              }

      }

       $this->set('user_recaptcha', $this->Cookie->read('user_recaptcha'));
       $success = $this->Session->read('success');
      //debug($success); die();
       $this->set('success', $success);
       $this->Session->delete('success');


   }

    protected function flatten($array, $prefix = '') {
    $result = array();
    foreach($array as $key=>$value) {
        if(is_array($value)) {
            $result = $result + $this->flatten($value,  $key . '.');
        }
        else {
            $result[$key] = $value;
        }
    }
    return $result;
}
   protected function get_all_subjects() {

     $tutors_model = new Tutor();

     $subjects_array = $tutors_model->get_all_subjects();
    // $subjects_array = json_encode($tutors_model->get_all_subjects());
     $subjects_array = $this->flatten($subjects_array, '');
    // debug($subjects_array) ; die();
    /**
     $return_array = array();
     foreach ($subjects_array as $key1 => $value1) {
               //debug($key1);
      //$return_array[]  =  implode($key1, $value1);
      $return_array[]  =  implode('(int) => 0 ', $value1);
    }
  **/
   // debug($subjects_array) ; //die();
  // debug($return_array) ; die();
    //$subjects_array = $return_array;
     asort($subjects_array);
   // debug($subjects_array) ; //die();
    $subjects_array = $first_sub = array(self::SUBJECT_ID_100 => 'All Subjects') + $subjects_array;

   // debug($subjects_array) ; die();
    return $subjects_array;
    //Configure::write('allsubjects',$subjects_array);

    }

}