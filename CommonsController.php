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
App::uses('MessageArchive', 'Model');
App::uses('Tutor', 'Model');

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
   
   

public function message() {
		
		
		$userType = $this->Session->read('loggedInUserType');

		//debug()
		//debug(AuthComponent::$sessionKey); die();
        if($userType == 'Auth.Student') {
			$this->layout	=	'student';
			$students_model = new Student();
			//$users = $students_model->findById($this->Session->read('Auth.Student.id'));
			$students_model->recursive = -1;
			$users = $students_model->find('all', array(
	            'conditions' => array('Student.id' => $this->Auth->user('id')),
	   		    'contain' => array(
	   		        'Tutor'
	   		        //'TutorProfile',
	   		        //'TutorLocation'
	   		        ),
					'order' => array('Student.created' => 'desc')
            ));
			
			//debug($users); die();
			// $users = $this->flatten($users, '');
			//debug($users); die();
			
        } else if($userType == 'Auth.Tutor') {
			$this->layout	=	'tutor';
			$tutors_model = new Tutor();
            //$users = $tutors_model->findById($this->Session->read('Auth.Tutor.id'));
			
			$users =  $tutors_model->find('all', array(
	            'conditions' => array('Tutor.id' => $this->Auth->user('id')),
	   		    'contain' => array(
	   		        'Student'
	   		        //'TutorProfile',
	   		        //'TutorLocation'
	   		        )
            ));
			
			//debug($users['Student']); die();
        } 
		
		//debug($users['Student']); die();
		$data = array(
						'usertype' => $userType,
						'users'    => $users
						);
						
		//debug($data); die();
		//$tutor = $this->Tutor->findById($this->Session->read('Auth.Tutor.id')); 
		
		$this->set($data);
		
		
}

public function getChat()
	{
		$to 	= null;
		$from 	= null;
		
		if( $this->request->is('ajax') ) {
			$this->autoRender = false;
		}
		if ($this->request->isPost()) {
			$to 	= $this->request->data['to'];
			$from 	= $this->request->data['from'];
		}
		
		//debug($to);
		//debug($from); die();
		$message_archive_model  = new MessageArchive();
		$chats_message = $message_archive_model->getmessage($to, $from );
		$messages  = array();
		$today = date("d-m-Y");
		foreach($chats_message as $msg){
			$d = date("M d, H:iA", strtotime($msg['MessageArchive']['created']));
			$dateOnly = date("d-m-Y", strtotime($msg['MessageArchive']['created']));
			if($dateOnly == $today){
				$d = date("H:iA", strtotime($msg['MessageArchive']['created']));
			}
			$chat = array(
							'message'	=> $msg['MessageArchive']['message'],
							'to'		=> $msg['MessageArchive']['to_user'],
							'from'		=> $msg['MessageArchive']['from_user'],
							'date'		=>  $d,
						);
			array_push($messages, $chat);
		}
		echo json_encode($messages);
	}
	
	//**************************************
	public function message_relay() {
			
		$app_id =     Configure::read('Pusher.credentials.appKey');   // App ID
		$app_key =    Configure::read('Pusher.credentials.appSecret');  // App Key
		$app_secret = Configure::read('Pusher.credentials.appId');    // App Secret
		
		$pusher = new Pusher($app_key, $app_secret, $app_id);

                //debug($pusher); die();
		// Check the receive message
		if( $this->request->is('ajax') ) {
			$this->autoRender = false;
		}
		if ($this->request->isPost()) {
			//$data['name'] 		= $this->request->data['name'];
			$data['message'] 	= $this->request->data['message'];
			$data['to'] 		= $this->request->data['to'];
			$data['from']		= $this->request->data['from'];
			$data['date']		= date("H:iA");
			
			$db = array(
						'message' => $this->request->data['message'],
						'to' 	  => $this->request->data['to'],
						'from'	  => $this->request->data['from']
			);
			//debug($data); 
			//debug($db); die();
			if($pusher->trigger('message', 'my_event', $data)) {
				
				$message_archive_model  = new MessageArchive();
				
				//debug($data); 
			   //debug($db); die();
			   $user_chat_message = array();
			   $user_chat_message['message'] = $db['message'];
			   $user_chat_message['to_user'] = $db['to'];
			   $user_chat_message['from_user'] = $db['from'];
			   
			  // debug($user_chat_message); die();
			   
				$chats_message = $message_archive_model->saveMessage($user_chat_message );
				echo 'success';			
			} else {
				echo 'error';	
			}
		}
		//$this->Pusher->subscribe('private-my-great-channel');
	}
	
	public function typing()
	{
		$app_id =     Configure::read('Pusher.credentials.appKey');   // App ID
		$app_key =    Configure::read('Pusher.credentials.appSecret');  // App Key
		$app_secret = Configure::read('Pusher.credentials.appId');    // App Secret
		
		$pusher = new Pusher($app_key, $app_secret, $app_id);

                //debug($pusher); die();
		// Check the receive message
		if( $this->request->is('ajax') ) {
			$this->autoRender = false;
		}
		if ($this->request->isPost()) {
			$data['to'] 		= $this->request->data['to'];
			$data['from']		= $this->request->data['from'];
			
			if($pusher->trigger('typing_channel', 'typing_event', $data)) {
				echo 'success';			
			} else {
				echo 'error';	
			}
		}
	}
	
	
	public function channelauth()
	{
		if (!$this->Auth->loggedIn()) {
			$this->Auth->authError = false;
			
        } else  {
		
			$app_id =     Configure::read('Pusher.credentials.appKey');   // App ID
			$app_key =    Configure::read('Pusher.credentials.appSecret');  // App Key
			$app_secret = Configure::read('Pusher.credentials.appId');    // App Secret
		
			$pusher = new Pusher($app_key, $app_secret, $app_id);
			// Check the receive message
			if( $this->request->is('ajax') ) {
				$this->autoRender = false;
			}
			if ($this->request->isPost()) {
				$auth 		= $pusher->socket_auth($this->request->data['channel_name'], $this->request->data['socket_id']);
				echo $auth;
			}
		}
	}
// ****************************//

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

public function pre_tutor_request() {

   $this->set('title_for_layout', 'About Daraji | Daraji Tutoring');

     if($this->Session->check('view_layout') ) {
	      $this->layout = $this->Session->read('view_layout');
          //debug($this->layout); die();
   }
}

public function about_us() {

   $this->set('title_for_layout', 'About Daraji | Daraji Tutoring');

     if($this->Session->check('view_layout') ) {
	      $this->layout = $this->Session->read('view_layout');
          //debug($this->layout); die();
   }
}

public function faqs_help() {

    $this->set('title_for_layout', 'Faqs-Help | Daraji Tutoring');

    if($this->Session->check('view_layout') ) {
	      $this->layout = $this->Session->read('view_layout');

   }
}

public function contactus() {

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

public function how_it_works_student() {
           $this->layout='default';
}

 public function how_it_works_tutor() {
           $this->layout='default';
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
                       // debug("I am here"); die();
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