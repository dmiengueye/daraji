<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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

App::uses('UsersController', 'Users.Controller');
App::uses('ZipSearch', 'Model');
App::uses('Tutor', 'Model');
App::uses('StudentSearchAgent', 'Model');
App::uses('StudentWatchList', 'Model');


class UsersController extends UsersController {

    const BASE_URL = 'https://{host}/maps/api/geocode/json?';
	const DEFAULT_HOST = 'maps.googleapis.com';
    

	const ACC_COUNTRY = 0;
	const ACC_AAL1 = 1;
	const ACC_AAL2 = 2;
	const ACC_AAL3 = 3;
	const ACC_POSTAL = 4;
	const ACC_LOC = 5;
	const ACC_SUBLOC = 6;
	const ACC_ROUTE = 7;
	const ACC_INTERSEC = 8;
	const ACC_STREET = 9;

	const SUBJECT_ID_100 = '100';
	const SUBJECT_ID_200 = '200';
	const SUBJECT_ID_300 = '300';

	const UNIT_NAUTICAL = 'N';
	const UNIT_FEET = 'F';
	const UNIT_INCHES = 'I';
	const UNIT_MILES = 'M';
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Students';
	public $uses = array ('Student', 'StudentPreference', 'StudentProfile', 'ZipSearch', 'Tutor');
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
        AuthComponent::$sessionKey = 'Auth.User';

	//	$this->Security->blackHoleCallback = 'blackhole';
        $this->Auth->allow('complete');
		$this->_setupPagination();
		$this->set('model', $this->modelClass);

       	$this->Session->delete('view_layout');
		$this->Session->write('view_layout', 'user');

		$id = $this->Auth->user('id'); //Using the session's user id to find logged in user
        if($this->Session->check(AuthComponent::$sessionKey . 'first_name')) {
                  $this->Session->write('username', $this->Session->read(AuthComponent::$sessionKey . 'first_name'));
        } else {
                $user_data = $this->{$this->modelClass}->findById($id);
		 		if($user_data != null  && !empty($user_data)) {
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
                    $last_name = $user_data[$this->modelClass]['last_name'];
		 		    $last_login = $user_data[$this->modelClass]['last_login'];
                    $user_zip_code = $user_data[$this->modelClass]['zip_code'];
		            $this->set('fname', $user_fname);

		            $this->Session->write('username', $user_fname);
                    $this->Session->write('lastname', $last_name);
		            $this->Session->write('last_login', $last_login);
                    $this->Session->write('student_zip_code', $user_zip_code);

                }
        }



        /**
			* Changed in version 2.4: Sometimes, you want to display the authorization error only
			* if the user has already logged-in. You can suppress this message by setting its value to boolean false
		**/

		if (!$this->Auth->loggedIn()) {
			$this->Auth->authError = false;
			$this->Session->delete('view_layout'); // In case it was lingering in the session under tutor
			$this->Session->write('view_layout', 'default');
        } else {
           $this->Session->delete('view_layout'); // In case it was lingering in the session under tutor
           $this->Session->write('view_layout', 'student');
        }

        //$this->Security->unlockedActions = array('update_entry');
}

/**
public function blackhole($type) {
    // Handle errors.
    debug($type);
    throw new BadRequestException(__d('cake_dev', 'The request has been blackholed.'));
    //return $this->redirect('logout');
   // $this->redirect($this->Auth->logout());
}
**/

public function solveCaptcha_a($captcha_response=null) {
    
    $response = null;
    $recaptchaStatus = false;
    if(!empty($captcha_response)) {                
                   $recaptcha = new ReCaptcha(Configure::read('Recaptcha.privateKey'));
                  //debug($recaptcha); die();
                   $response = $recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $captcha_response);
                   //debug($response); die();
                   if ($response->success){
                         $recaptchaStatus = true;
                        // debug($recaptchaPassed); die();
                         $this->set('recaptcha', $recaptchaStatus);                       
                   } else {
                          $this->set('errorCode',$response->errorCodes[0]);
                         // debug($response->errorCodes[0]); die();
                          $recaptchaStatus = false;                          
                   }       
    } 
              
    return $recaptchaStatus; 
    
}

public function login() {
    
    if ($this->request->is('post')) {
        // debug("test me out"); die();
       
        if(!empty($this->request->data[$this->modelClass])) {
            
                          $throttle_delay = $this->throttleUserLogin($this->request->data[$this->modelClass]['email']);                             
                          $user = $this->{$this->modelClass}->getUserRecord($this->request->data[$this->modelClass]['email'], $options = array());
                          
                          if(!empty($user)) {
                             $lock_account = $user[$this->modelClass]['lock_account'];                                              
                             if(isset($lock_account) && !empty($lock_account)) {
                                 $message = "This account has been temporarily suspended. Please contact Wizwonk Tech Support for further informtion. ";                                                                             
                                 $this->Session->setFlash($message, 'custom_msg');
                                 $this->redirect($this->Auth->loginAction);
                              }
                              if(isset($user[$this->modelClass]['captcha']) && !empty($user[$this->modelClass]['captcha'])) {
                                  // debug("testme"); //die(); 
                                  $this->Session->write('st_captcha', $user[$this->modelClass]['captcha'] );                          
                                  $this->_setCookie($options=array(), 'st_recaptcha'); 
                                  $this->set('st_recaptcha', $this->Cookie->read('st_recaptcha'));
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
                         }   
                         if(!empty($user[$this->modelClass]['captcha']) && empty($this->request->data[$this->modelClass]['captcha']) ) {
                                           $message = 'Please resolve the Captcha!';                       
                                           $this->Session->setFlash($message, 'custom_msg');
                                           $this->redirect($this->Auth->loginAction);
                                           
                         } else if(!empty($user[$this->modelClass]['captcha']) && !empty($this->request->data[$this->modelClass]['captcha'])){
                                         
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
                         //debug("I am here"); die();
                         parent::login();//$this->login();
              }
      }
      
       $this->set('st_recaptcha', $this->Cookie->read('st_recaptcha'));
   }

}