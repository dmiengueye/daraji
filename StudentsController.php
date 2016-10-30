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
 * For full copyright and license information, please see the LICENSE.txtYour Job Post failed!! Please corect all Errors below and resubmit!



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
App::uses('Subject', 'Model');
App::uses('Categorie', 'Model');
App::uses('TutorSubject', 'Model');
App::uses('StudentSearchAgent', 'Model');
App::uses('StudentWatchList', 'Model');




class StudentsController extends UsersController {

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
    public $helpers = array('Js','ZipCode', 'SearchAgent', 'Ajax');

   // var $helpers = array('Ajax');

/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {


if($this->action == 'tutor_search_results_auth'  || $this->action == 'tutor_search_results') {
         $this->disableCache();
     }
		parent::beforeFilter();
        AuthComponent::$sessionKey = 'Auth.Student';

	//	$this->Security->blackHoleCallback = 'blackhole';
        $this->Auth->allow('complete');
		$this->_setupPagination();
		$this->set('model', $this->modelClass);

       	$this->Session->delete('view_layout');
		$this->Session->write('view_layout', 'student');

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
                    $this->Session->write('student_user', $user_data);

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
        Configure::write('allsubjects',$this->get_all_subjects());
		
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

     if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'home'));
     }

// debug("wpwp"); die();
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
                        // debug("I am here"); die();
                         parent::login();//$this->login();
              }
      }

       $this->set('st_recaptcha', $this->Cookie->read('st_recaptcha'));
}
public function register() {

  if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'home'));
  }

 $this->layout = 'login_default';
//$this->layout = 'default';

     if($this->request->is('post')) {
   // debug($this->request->data); die();
             // verify recaptcha
      if(empty($this->request->data[$this->modelClass]['request_sign_up'])) {
            if(!empty($this->request->data['g-recaptcha-response'])) {
        	    $response = $this->{$this->modelClass}->solveCaptcha($this->request->data['g-recaptcha-response']);
                //debug($response); die();
                if(!$response) {
                    $message = 'Please Resolve Captcha Error and Try Again!!';
                    $this->Session->setFlash($message, 'custom_msg');
                    $this->redirect($this->referer('/'));
                }
             }else {
                $message = 'Please Check the Captcha Box to prove You are NOT a ROBOT';
                $this->Session->setFlash($message, 'custom_msg');
                $this->redirect($this->referer('/'));
             }
	  }
	         $id = null;
	         $postData = array();
			 $student_request = null;
             //$this->Session->delete('error_array');
        if (!empty($this->request->data)) {
             Configure::write('Users.role', 'student');
             $this->request->data['Student']['referral_source'] = $this->request->data['Student']['contactbox'];
			 
			  if (!empty($this->request->data['Student']['request_sign_up'])) { //is student requesting a tutor and signing up at same time?
			      $student_request = $this->Session->read('student_request');
			      $this->request->data['Student']['zip_code'] = !empty($student_request['Request']['zip_code']) ? $student_request['Request']['zip_code'] : $student_request['Request']['zip'];
		     }
			 
			 // debug($student_request); 
	        //  debug($this->request->data); die();
             // Call add() function of Parent (Plugin::UsersController)
             $this->add();
			 
			 if(!empty($this->request->data['Student']['reg_regis']) && $this->Auth->login()) {
				 
				$this->{$this->modelClass}->id = $this->Auth->user('id');
                $this->Session->write('first_login', true);
                $this->{$this->modelClass}->saveField('last_login', date('Y-m-d H:i:s'));
				
				return $this->redirect(array('action' => 'welcome'));
			 }
			 
			 
			
			 if(!empty($this->request->data['Student']['request_sign_up']) && $this->Auth->login()) {
			
                $this->{$this->modelClass}->id = $this->Auth->user('id');
                $this->Session->write('first_login', true);
                $this->{$this->modelClass}->saveField('last_login', date('Y-m-d H:i:s'));
		
		         //$student_request = $this->Session->read('student_request');
                //Since the beforFilter() method is already hit by the time we get here
                //and at that time the Student info did not exist yet.. All of the user info below we normally retreive in beforeFilter()  would not have
                //been in Session.... So we have to put it in there
                $user_data = $this->{$this->modelClass}->findById($this->Auth->user('id'));
               
			   // debug($student_request); //die();
				//debug($this->request->data); die();
				
				//debug("here land"); //die();

		 		if(!empty($user_data)) {
					
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
                    $last_name = $user_data[$this->modelClass]['last_name'];
		 		    $last_login = $user_data[$this->modelClass]['last_login'];
                    $user_zip_code = $user_data[$this->modelClass]['zip_code'];
					
					/** 
					 $user_fname = $this->request->data['Student']['first_name'];
                     $last_name =  $this->request->data['Student']['last_name'];
					 $user_zip_code  = $this->request->data['Student']['zip_code'];
					**/
					
		            $this->Session->write('username', $user_fname);
                    $this->Session->write('lastname', $last_name);
		            $this->Session->write('last_login', $last_login);
                    $this->Session->write('student_zip_code', $user_zip_code);
                    
					$tutors_model = new Tutor();
                    $result = $tutors_model->find_city_ByZipCode($user_zip_code);

                   	if(!empty($result)) {
                        $student_city = $result['city'];
                        $student_state = $result['state'];

                        $this->Session->write('student_city', $student_city);
                        $this->Session->write('student_state', $student_state);
                      }
                }
				
				//debug("here land"); die();
				
              // debug($user_data); die();
                //Now that we have successfully registered the student, We need to Send the Message to Tutor.
                //So we will swap the request data for the Student Message Data
                $member_id = $student_request['Request']['member_id'];
                $this->request->data = array();
				
				$this->request->data['StudentTutorContact']['grade_level'] = $student_request['Request']['level'];
				$this->request->data['StudentTutorContact']['lesson_start'] = $student_request['Request']['lesson_start'];
				$this->request->data['StudentTutorContact']['lesson_location'] = $student_request['Request']['lesson_location'];			
                
			    $this->request->data['StudentTutorContact']['subject_name'] = 
				            !empty($student_request['Request']['subject'])?  $student_request['Request']['subject']: $student_request['Request']['subj'];
							
				 $this->request->data['StudentTutorContact']['message'] = 
			                !empty($student_request['Request']['message']) ? $student_request['Request']['message']: 'I need help with '.$this->request->data['StudentTutorContact']['subject_name'];
								 
                $this->request->data['StudentTutorContact']['member_id'] =  $student_request['Request']['member_id'];
                $this->request->data['StudentTutorContact']['copy_me'] = 1;
                $this->request->data['StudentTutorContact']['message_channel'] = 'tutor_request_sign_up';				
             
			   // debug($this->request->data); die();
                $this->contact_tutor();
            
				 //return $this->redirect(array('action' => 'welcome'));
			 }
        }
   }
}

public function complete() {
        $this->layout = 'default';
	    //debug("I am ahere"); die();
        if($this->Session->check('completeEmail')){
            $this->set('completeEmail',$this->Session->read('completeEmail'));
            $this->Session->delete('completeEmail');
        }else{
            return $this->redirect(array('action' => 'login','controller'=>'students'));
        }
  }


/**
public function index() {

     $this->set('title_for_layout', 'Daraji- Student Home');
     $this->layout='student';
     $this->set('students', $this->Paginator->paginate($this->modelClass));
}
**/

public function index() {
  if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'home'));
   } else {
      return $this->redirect(array('controller' => 'commons', 'action' => 'index'));
   }

 }



 public function home() {
   	   if (!$this->Auth->loggedIn()) {
			return $this->redirect(array('action' => '/students/login'));
        }
          $this->set('title_for_layout', 'Daraji- Student Home');
        $this->layout='student';
        $this->set('zip', $this->Session->read('cur_zip_code'));
      //  $this->Student->recursive = 0;
      //  $this->set('students', $this->paginate());
      //$this->set('students', $this->Paginator->paginate($this->modelClass));
    }


public function welcome() {

     $this->layout='student';
     //$first_login = true;
    // debug("from"); die();
    if($this->Session->check('first_login')) {
       $first_login = $this->Session->read('first_login');
       //Moved the delete to homeroomempty action
      // $this->Session->delete('first_login');
    }
    // debug($first_login); die();
    //debug($this->Auth->user('last_login'));
    // debug("test"); die();
    if(!$first_login) {
           return $this->redirect(array('action' => 'homeroomempty'));
    }
    //debug("test"); die();
    if($this->Session->check('tutor_request')) {
        $tutor_request = $this->Session->read('tutor_request');

        if($tutor_request === 'success'){

              $this->set('success', true);
              $this->Session->delete('tutor_request');
              $this->Session->setFlash('Your Job Post was successfully submitted. It is Pending Review. You will be notified when approved in the next 10 minutes!!', 'custom_msg');

         }  else if($tutor_request === 'failure'){
    	     $this->set('success', false);
             $this->Session->delete('tutor_request');
       	     $this->Session->setFlash('Your Job Post failed!! Please corect all Errors below and resubmit!', 'custom_msg');

          }
   }

}

public function homeroomempty() {
       $this->layout='student';

     //delete here and not from welcome action.. because if for some reason
     //user refreshes screens from welcome and the session was deleted, user
     //would be forwarded here whether he/she likes it or not. We do not want that to happen
      if($this->Session->check('first_login')) {
              $this->Session->delete('first_login');
      }
      $user = $this->dashboard();
     // debug($user); die();
      $this->set('user', $user);
}

public function home_room() {

     $this->layout='student';

     //delete here and not from welcome action.. because if for some reason
     //user refreshes screens from welcome and the session was deleted, user
     //would be forwarded here whether he/she likes it or not. We do not want that to happen
      if($this->Session->check('first_login')) {
              $this->Session->delete('first_login');
      }
      $user = $this->dashboard();
      $this->set('user', $user);
}

public function tutor_search_results() {

     if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'tutor_search_results_auth'));
       }

       $this->set('title_for_layout', 'Daraji-Tutor Search Results');
       $this->layout='default';

	   //debug
       //This was put in Session from tutor_detail_profile because
       //we are forwarding here
       //And want it available
       $warning = $this->Session->read('warning');
       //debug($warning); die();
       $this->set('warning', $warning);
       $this->Session->delete('warning');
      // debug($this->Session->read('results')); die();

        $radiusSearch = new ZipSearch();
        $tutor = new Tutor();
        $conditions = array();
        $tutors_model = new Tutor();
        $tutor_subject_model = new TutorSubject();
        $subject_model = new Subject();
        $cat_array = array('All Categories');
        $subj_array = array('All Subjects');

   if($this->request->is('get')) {
     $id = null;
     $rs = null;
     // pagination
     $posts_per_page = 3;
     $total_post_count = 0;
     $cur_page = 1;
     $start_page = 1;
     $display_page_navigation = 9;

     if(!empty($_GET['cur_page'])){
      $cur_page = $_GET['cur_page'];
    }

    if($this->Session->check('params_url')) {
        $this->Session->delete('params_url');
     }

      //The user entered zip code always takes priority over the computed zip code.
       //$cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : ""; //$this->Session->read('cur_zip_code');
       // ovewrites the computed zip code in the session if user entered a zip code manually
       //debug($this->params->query['zip_code']); die();
       //$this->set('city', $this->_set_city_for_zip($cur_zip_code));
       //$this->Session->write('cur_zip_code', $cur_zip_code);
	   
	   //debug($this->params->query); die();
	   
	    if(!empty($this->params->query['user_subject']) && strtolower($this->params->query['user_subject']) === strtolower('Mathematics')) {
		$this->params->query['user_subject'] = 'Math';
		
	    } else  if(!empty($this->params->query['user_subject']) && strtolower($this->params->query['user_subject']) === strtolower('Tech')) {
		$this->params->query['user_subject'] = 'Technology';
		
	    }
	   
            $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
            $is_advanced = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;

            $this->params->query['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "";
            $cur_zip_code =  $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code'); //$cur_zip_code;

            if(empty($this->params->query['user_subject_id'] ) && !empty($this->params->query['user_subject'])) {
                $this->params->query['user_subject_id'] = $subject_model->get_subject_id($this->params->query['user_subject']);
             } else {
                $this->params->query['user_subject_id'] = !empty($this->params->query['user_subject_id']) ? $this->params->query['user_subject_id'] :  self::SUBJECT_ID_100; //"100";

             }

            if(!$this->validateUSAZip($cur_zip_code)) {

                $this->set('success', false);
                //$this->Session->setFlash('You have entered an Invalid Zip Code', 'custom_msg');
                $this->Session->setFlash
					(
					sprintf(__d('users', 'You have entered an invalid Zip Code.')),
					'default',
					 array('class' => 'alert alert-danger')
					);
                return $this->redirect('tutor_search_results');
             }

            $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
            $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;

    if (!empty($this->params->query) && !empty($this->params->query['distance'])) { // if submit
          // debug("3"); die();
        // debug($this->params->query['user_subject']); //die();
		 $this->params->query['user_subject'] = !empty($this->params->query['user_subject'])? $this->params->query['user_subject']: "";
		 $ini_user_subject = $this->params->query['user_subject']; //save it here before it changes
         $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;

         $this->params->query['zip_code'] = $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
         $this->params->query['distance'] = $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;

         $this->params->query['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "All Subjects";
		 
         $this->params->query['subject_id'] = !empty($this->params->query['user_subject_id']) ? $this->params->query['user_subject_id'] : "AllSubjects";
         $this->params->query['category'] = !empty($this->params->query['user_category']) ? $this->params->query['user_category'] : "All Categories";
         $this->params->query['category_id'] = !empty($this->params->query['user_category_id']) ? $this->params->query['user_category_id'] : "AllCategories";

          $this->params->query['user_category'] = $this->params->query['category'];
          $this->params->query['user_category_id'] = $this->params->query['category_id'];
          $this->params->query['user_subject'] = $this->params->query['subject'];
          $this->params->query['user_subject_id'] = $this->params->query['subject_id'];


         $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
         $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate'])? $this->params->query['amount_min_rate']: 10;
         $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate'])? $this->params->query['amount_max_rate']: 250;
         $this->params->query['min_age'] = !empty($this->params->query['min_age'])? $this->params->query['min_age']: 18;
         $this->params->query['max_age'] = !empty($this->params->query['max_age'])? $this->params->query['max_age']: 100;
         $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
         $this->params->query['bg_checked'] = !empty($this->params->query['bg_checked'])? $this->params->query['bg_checked']: 0;

         $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
    	 $kwd =  $this->params->query['kwd'];

         // debug($this->params->query); die();
       if(!preg_match('/^[0-9]{1,3}$/', $this->params->query['distance'])) {
        	$this->Session->setFlash
								(
											sprintf(__d('users', 'You did not enter a properly formatted distance.')),
										    'default',
											 array('class' => 'alert alert-warning')
								 );
      }

       $this->set('city', $this->_set_city_for_zip($cur_zip_code));
       $this->Session->write('cur_zip_code', $cur_zip_code);


      if(empty($cur_zip_code)  || $cur_zip_code === "") {
         $this->Session->write('cur_zip_code', "");
       } else {
          $this->Session->write('cur_zip_code', $cur_zip_code);
       }


     try{

        /**
         * make condition array
         */
        $conditions_for_search = array();
        $conditions_for_search['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "All Subjects";

        $conditions_for_search['category'] = !empty($this->params->query['user_category']) ? $this->params->query['user_category'] : "All Categories";

        $conditions_for_search['hourly_rate'] = "";
        $conditions_for_search['age'] = "";
        $conditions_for_search['gender'] = "";
        $conditions_for_search['bg_checked'] = "";
        $conditions_for_search['is_advanced'] = false;
		
		

        // advanced search
		$this->params->query['is_advanced'] = 1;
		//debug("dshj"); die();
       if($this->params->query['is_advanced'] == 1){
           $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
            $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate']) ? $this->params->query['amount_min_rate'] : 10;
            $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate']) ? $this->params->query['amount_max_rate'] : 250;
            $this->params->query['min_age'] = !empty($this->params->query['min_age']) ? $this->params->query['min_age'] : 18;
            $this->params->query['max_age'] = !empty($this->params->query['max_age']) ? $this->params->query['max_age'] : 100;
            $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
            $this->params->query['location'] = !empty($this->params->query['location']) ? $this->params->query['location'] : 'offline';
        
              $conditions_for_search['is_advanced'] = true;
              // hourly rate
             if(!empty($this->params->query['amount_min_rate'])
                &&
                !empty($this->params->query['amount_max_rate'])){
                $conditions_for_search['hourly_rate'] = $this->params->query['amount_min_rate'] . "," . $this->params->query['amount_max_rate'];
              }
              // end hourly rate

          // age
          if(!empty($this->params->query['min_age'])
            &&
            !empty($this->params->query['max_age'])){
            $conditions_for_search['age'] = $this->params->query['min_age'] . "," . $this->params->query['max_age'];
          }
          // end age
          $conditions_for_search['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : "0";
          $conditions_for_search['bg_checked'] = !empty($this->params->query['bg_checked']) ? $this->params->query['bg_checked'] : 0;
		  $conditions_for_search['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;

		 //debug($this->params->query['gender']); die();
        $this->set('is_advanced', $this->params->query['is_advanced']);
        $this->set('bg_checked', $this->params->query['bg_checked']);
        $this->set('amount_min_rate', $this->params->query['amount_min_rate']);
        $this->set('amount_max_rate', $this->params->query['amount_max_rate']);
        $this->set('min_age', $this->params->query['min_age']);
        $this->set('max_age', $this->params->query['max_age']);
		$this->set('distance', $this->params->query['distance']);
		$this->set('gender', $this->params->query['gender']);
        $this->set('location', $this->params->query['location']);

    } else {

          $this->set('is_advanced',0);
          $this->set('gender', 0);
          $this->set('bg_checked', 0);
          $this->set('amount_min_rate',10);
          $this->set('amount_max_rate', 500);
          $this->set('min_age', 18);
          $this->set('max_age',100);
    }

	//debug($conditions_for_search);// die();
         
	//if(!empty($ini_user_subject) && !ini_user_subject==='All Subjects') { 
        $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], 
		$this->Session->read('cur_zip_code'), $this->params->query);
	//}else if(empty($ini_user_subject) || $ini_user_subject === 'All Subjects') {
	//	$result = $tutors_model->get_all_tutors($cur_zip_code, null, $this->Session->read('cur_zip_code'), $distance, $kwd,$this->params->query );
	//}
	
	//debug( $result); die();
	
       // $subjects_and_categories = $tutors_model->get_all_subjects_and_categories();
		//debug($result); die();
		/**
	//Line 850
	//This new code is introduced to handle search terms entered from home page serach box
	//change was made to fix the issue related to when user enters search terms in home page box
	
	   $category = new Categorie();
	   $subject = new Subject();
	   
	   $subj =  $subject->find('first', array('conditions' => array('name' => $this->params->query['user_subject']))); 				
	   $cat = $category->find('first', array('conditions' => array('name' => $this->params->query['user_subject']))); 			
				
      if(!empty($cat)) { 
	      //debug("here cat");
	       //User must've entered a category name as subject. So we make the switch. It came in a subject param
		  //But it's a category. For Example, User just entered "Math" instead of "Algebra" in the Home Page Search box
		  $this->params->query['user_category'] = $this->params->query['user_subject'];
		 // $this->params->query['user_subject'] = "All Subjects";
		  
		  $this->params->query['user_category_id'] = $cat['Categorie']['category_id'];
		  //$this->params->query['user_subject_id'] = self::SUBJECT_ID_100;
		  $this->params->query['user_subject_id'] = $cat['Categorie']['category_id'];
		  
		   $this->params->query['subject_id'] = $cat['Categorie']['category_id'];
		   $this->params->query['category_id'] = $cat['Categorie']['category_id'];
		   
		   
		  
	  }	else if(empty($subj)) {
		  
		  $this->params->query['user_category'] = "All Categories";
		  $this->params->query['user_subject'] = "All Subjects";
		  $this->params->query['user_category_id'] = "AllCategories";
		  $this->params->query['user_subject_id'] = self::SUBJECT_ID_100;
		  
		  $this->params->query['subject_id'] = self::SUBJECT_ID_100;
		  $this->params->query['category_id'] = "AllCategories";
		   
		   
		  
	  }	else if(!empty($subj)) { 
	   //debug("here subj");
	     //User entered a Subject name (ie, Algebra)	
		  $this->params->query['user_subject'] = $subj['Subject']['name'];
		  $this->params->query['user_category'] = $subj['Subject']['category_name'];
		   $this->params->query['user_subject_id'] = $subj['Subject']['subject_id'];
		  $this->params->query['user_category_id'] = $subj['Subject']['category_id'];
       		 
	  }	
	  
	 // debug( $this->params->query['user_subject']);
	 // debug( $this->params->query['user_category']);
	  //die();
	//Line 901 End of changes related to Home Page Search terms entered in Home Page Search box
	**/   
	   
        $return_array = $this->_get_nav_data($result, $cur_page);

          $this->set('tutors', $return_array);
          $this->set('distance', $this->params->query['distance']);
          $this->set('zip', $this->Session->read('cur_zip_code'));
       
	   if(!empty($this->params->query['user_subject']) && 
	      $this->params->query['user_subject'] != 'All Subjects') 
	   {
          $this->set('subject', $this->params->query['user_subject']);
		  $this->set('subject_id', $this->params->query['user_subject_id']);
	   }
          $this->set('category', $this->params->query['user_category']);
          
          $this->set('category_id', $this->params->query['user_category_id']);
          $this->set('cur_page', $cur_page);
          $this->set('kwd', $this->params->query['kwd']);

        
       // $this->set('subjects_and_categories', $subjects_and_categories);

        if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
        } else {
            $this->set('sortBy', "Best Match");
        }

       } catch (NotException $e) {
         $this->redirect(array('action' => 'tutor_search_results'));
       }


         $this->set('subject', $this->params->query['user_subject']);
         $this->set('category', $this->params->query['user_category']);
		 
		 if($this->Session->check('subject')) {
		   $this->Session->delete('subject');	      
		 }
		 if($this->params->query['user_subject'] != 'All Subjects') {
		    $this->Session->write('subject', $this->params->query['user_subject']);
		 }
		 //debug( $this->params->query);

    }
    else{ // if not submit
        
       // ovewrites the computed zip code in the session if user entered a zip code manually
	   
	  // debug($this->params->query); die();
         
       $distance = $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
	   $kwd = $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";

	   /**
       $this->params->query['user_subject']  =  'All Subjects';    //$this->params->query['user_subject'];
       $this->params->query['user_category'] =  'All Categories'; // $this->params->query['user_category'];

       $this->params->query['user_subject_id']  =  'AllSubjects';    //$this->params->query['user_subject'];
       $this->params->query['user_category_id'] =  'AllCategories'; // $this->params->query['user_category'];

      $this->params->query['subject']  =  'All Subjects';    //$this->params->query['user_subject'];
       $this->params->query['category'] =  'All Categories'; // $this->params->query['user_category'];

      $this->params->query['subject_id']  =  'All Subjects';    //$this->params->query['user_subject'];
       $this->params->query['category_id'] =  'All Categories'; // $this->params->query['user_category'];
  **/
  
  //$this->params->query['user_subject'] = 

       $this->Session->write('cur_zip_code', $cur_zip_code);
       if(empty($cur_zip_code)  || $cur_zip_code === "") {
         $this->Session->write('cur_zip_code', "");
       }
	   

      //$result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $this->params->query['distance']);
      //$result = $tutors_model->get_all_tutors($cur_zip_code, null, $this->Session->read('cur_zip_code'), $distance, $kwd,$this->params->query );
       $result = $tutors_model->get_all_tutors($cur_zip_code, null, $this->Session->read('cur_zip_code'), $distance, $kwd,$this->params->query );

	  //debug( $result); die();
      $return_array = $this->_get_nav_data($result, $cur_page);

	  //debug( $return_array); die();
      $this->set('tutors', $return_array);

      $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('distance', $distance);

	  /**
      $this->set('subject', $this->params->query['subject']);
      $this->set('category', $this->params->query['category']);

      $this->set('subject_id', $this->params->query['subject_id']);
      $this->set('category_id', $this->params->query['category_id']);
  **/
      $this->set('cur_page', $cur_page);
      $this->set('kwd', $this->params->query['kwd']);

      $this->set('gender', 0);
      $this->set('bg_checked', 0);
      $this->set('amount_min_rate', 10);
      $this->set('amount_max_rate', 250);
      $this->set('min_age', 18);
      $this->set('max_age', 250);
      $this->set('is_advanced', 0);

      if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
      } else {
            $this->set('sortBy', "Best Match");
      }

     $subject = $this->Session->delete('subject');
     //debug($subject); die();
    

   }
   
   /**
    $sess_subj = $this->Session->read('subject');
   
    if($this->Session->check('subject') && $sess_subj == 'All Subjects') {
		   $this->Session->delete('subject');	      
	}
	if( !empty($this->params->query['user_subject']) != 'All Subjects') {
		  $this->Session->write('subject', $this->params->query['user_subject']);
	}
	**/	  
			//  debug($this->params->query['user_subject_id']);
			  
		 /**  $this->Session->write('subject', $this->params->query['user_subject']);
		   $this->Session->write('subject_id', $this->params->query['user_subject_id']);
		   
		   $this->Session->write('user_subject', $this->params->query['user_subject']);
		   $this->Session->write('user_subject_id', $this->params->query['user_subject_id']);
		   
		   $this->Session->write('user_category', $this->params->query['user_category']);
		   $this->Session->write('user_category_id', $this->params->query['category_id']);
		   
		   $this->Session->write('category', $this->params->query['user_category']);
		   $this->Session->write('category_id', $this->params->query['category_id']);
		**/	  

     }
	 
	 //return $this->params->query;

 }

public function studentsearchresults() {
     $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='searchresults';
  }

public function StudentProfiledetail() {
     $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='student';
  }

public function tutor_details_profile_auth($member_id=null) {

     $this->set('title_for_layout', 'Daraji-Tutor Profile');
     $this->layout='student';
     $message = 'Tutor Profile could not be found';
     $warning = false;

    // debug($member_id); die();

     //This is here in case user Sign In after Viewing tutor details profile
     //from Non-Authenticated View.

     /**$session_member_id = $this->Session->read('session_member_id');
     if(empty($member_id) && !empty($session_member_id)) {
        $member_id = $session_member_id;
        $this->Session->delete('session_member_id');
     } else {
        throw new NotFoundException(__('Invalid Request'));
     }
    **/

     $tutor_profile = array();
     $tutor_model = new Tutor();

   if($this->request->is('get')) {
      // $id = null;
     if(empty($member_id) || (!$tutor = $tutor_model->find('first', array(
                            'conditions' => array(
                                'Tutor.member_id' => $member_id,
                                'Tutor.email_verified' => 1,
                                'Tutor.profile_status' => 1,
                                'Tutor.active' => 1))))
      )  {
         //$message = 'Tutor Profile could not be found';
         $warning = true;
         $this->Session->write('warning', $warning);
         $this->Session->setFlash($message, 'custom_msg');
        /** $this->Session->setFlash(
								 sprintf(__d('users', 'Tutor Profile was not found')),
						         'default',
								  array('class' => 'alert alert-warning')
							   );
          **/
        return $this->redirect(array('action' => 'tutor_search_results_auth'));
        }
        //debug($tutor['StudentWatchList']); die();
       if(!empty($tutor) && !empty($tutor['TutorProfile'])) {

              $first_name = $tutor['Tutor']['first_name'];
              $last_name = $tutor['Tutor']['last_name'];

              $last_name = substr($last_name,0,1);
              $blank = "  ";
              $full_name = "$first_name $blank $last_name";
              $this->set('full_name', $full_name);
              $this->set('first_name', $first_name);

              $this->set('about_me', $tutor['TutorProfile']['description']);
              $this->set('city', $tutor['TutorProfile']['city']);
              $this->set('state', $tutor['TutorProfile']['state']);
              $this->set('profile_zip_code',  $tutor['TutorProfile']['zip_code']);
              $this->set('degree', $tutor['TutorProfile']['degree']);
              $this->set('school', $tutor['TutorProfile']['school']);
              $this->set('hourly_rate', $tutor['TutorProfile']['hourly_rate']);
              $this->set('travel_radius', $tutor['TutorProfile']['travel_radius']);
              $this->set('bg_checked', $tutor['TutorProfile']['background_checked']);
              $this->set('rating_score', $tutor['TutorProfile']['avg_rating_score']);
              $this->set('ratings', $tutor['TutorProfile']['ratings']);

              $tutor_id = $tutor['Tutor']['id'];

              if(!empty($tutor_id)) {

                $this->set('tutor_id', $tutor_id);

                $tutor_profile_photo = $tutor_model->get_tutor_profile_pic($tutor_id);
                if(!empty($tutor_profile_photo)){
                   $this->set('profile_pic', $tutor_profile_photo);
                }

                $tutor_subjects = $tutor_model->get_all_subjects_for_tutor($tutor_id);
               // debug($tutor_subjects); die();
                if(empty($tutor_subjects)) {
				         //$message = 'Tutor Profile could not be found';
				         $warning = true;
				         $this->Session->write('warning', $warning);
				         $this->Session->setFlash($message, 'custom_msg');

				         return $this->redirect(array('action' => 'tutor_search_results_auth'));
                }
                 $tutor_subjects = $this->flatten($tutor_subjects, '');
                 asort($tutor_subjects);

                 $this->set('tutor_subjects', $tutor_subjects);

                 $tutor_subjects =  array(self::SUBJECT_ID_100 => 'Subjects taught by tutor') +  $tutor_subjects;
                 Configure::write('all_tutor_subjects',$tutor_subjects);
                 // $this->set('default_subject', 'All Subjects');
                }

             }



             if(!empty($tutor['StudentWatchList'])) {
                 $myKey = array_keys($tutor['StudentWatchList']);
                // debug($myKey[0]);
                 foreach($tutor['StudentWatchList'] as $studentWatchList) {
                    //debug($studentWatchList);
                    if($studentWatchList['student_id'] === $this->Auth->user('id')
                      && $studentWatchList['Tutor']['id'] === $tutor_id)     {
                        $this->set('on_watch_list', true);
                        $this->set('note_on_tutor', $studentWatchList['note_on_tutor']);
                        $this->set('watch_list_pk', $studentWatchList['id']);
                        //$this->set('tutor_id', $studentWatchList['Tutor']['id']);


                    }
                 }

             }
             //Needs this for the message form
            // $this->Session->write('member_id', $member_id);
       }

   // }

    //die();
}

public function tutor_request_sign_up ($member_id=null) {
	
	 $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='default';
     $message = 'Tutor Profile could not be found';
     $warning = false;
	
 // debug($member_id); die();

    /** if(empty($member_id) ) {
       throw new NotFoundException(__('Invalid Request'));
     }
    **/
	$student_request = $this->Session->read('student_request');
	
	if(!empty($student_request) && !empty($student_request['Request']['member_id'])) {
         $member_id =  $student_request['Request']['member_id'];
	} else {
		
		  $message = 'Requested Instructor could not be found';
          $this->Session->setFlash($message, 'custom_msg');
          // $this->redirect($this->referer('/'));
		  return $this->redirect(array('action' => 'tutor_search_results'));
	}
	
   $tutor_profile = array();
   $tutor_model = new Tutor();

   if($this->request->is('get')) {
      // $id = null;
     if(empty($member_id) || (!$tutor = $tutor_model->find('first', array(
                            'conditions' => array(
                                'Tutor.member_id' => $member_id,
                                'Tutor.email_verified' => 1,
                                'Tutor.profile_status' => 1,
                                'Tutor.active' => 1))))
      ) {
        // $message = 'Tutor Profile could not be found';
         $warning = true;
         $this->Session->write('warning', $warning);
         $this->Session->setFlash($message, 'custom_msg');

        return $this->redirect(array('action' => 'tutor_search_results'));
        }
		
        //debug($tutor['StudentWatchList']); die();
       if(!empty($tutor) && !empty($tutor['TutorProfile']) && !empty($tutor['Tutor'])) {

              $first_name = $tutor['Tutor']['first_name'];
              $last_name = $tutor['Tutor']['last_name'];
			   //$member_id = $tutor['Tutor']['member_id'];

              $last_name = substr($last_name,0,1);
              $blank = "  ";
              $full_name = "$first_name $blank $last_name";
              $this->set('full_name', $full_name);
              $this->set('first_name', $first_name);
			  $this->set('last_name', $last_name);
              $this->set('about_me', $tutor['TutorProfile']['description']);
              $this->set('city', $tutor['TutorProfile']['city']);
              $this->set('state', $tutor['TutorProfile']['state']);
              $this->set('profile_zip_code',  $tutor['TutorProfile']['zip_code']);
              $this->set('degree', $tutor['TutorProfile']['degree']);
              $this->set('school', $tutor['TutorProfile']['school']);
              $this->set('hourly_rate', $tutor['TutorProfile']['hourly_rate']);
              $this->set('travel_radius', $tutor['TutorProfile']['travel_radius']);
              $this->set('bg_checked', $tutor['TutorProfile']['background_checked']);
              $this->set('rating_score', $tutor['TutorProfile']['avg_rating_score']);
              $this->set('ratings', $tutor['TutorProfile']['ratings']);
			  $this->set('title', $tutor['TutorProfile']['title']);
			  $this->set('cancel_policy', $tutor['TutorProfile']['cancel_policy']);
			  $this->set('member_id', $member_id);

              $tutor_id = $tutor['Tutor']['id'];

              if(!empty($tutor_id)) {

                $this->set('tutor_id', $tutor_id);

                $tutor_profile_photo = $tutor_model->get_tutor_profile_pic($tutor_id);
                if(!empty($tutor_profile_photo)){
                   $this->set('profile_pic', $tutor_profile_photo);
                }

                 $tutor_subjects = $tutor_model->get_all_subjects_for_tutor($tutor_id);
				// debug($tutor_subjects); die();
                 if(empty($tutor_subjects)) {
					  $warning = true;
					  $this->Session->write('warning', $warning);
					  $this->Session->setFlash($message, 'custom_msg');

					  return $this->redirect(array('action' => 'tutor_search_results'));
                 }

                 $tutor_subjects = $this->flatten($tutor_subjects, '');

                 asort($tutor_subjects);
                 $this->set('tutor_subjects', $tutor_subjects);

                 $tutor_subjects =  array(self::SUBJECT_ID_100 => 'Choose a Subject') +  $tutor_subjects;
                 Configure::write('all_tutor_subjects',$tutor_subjects);

                 // $this->set('all_tutor_subjects', $tutor_subjects);
             }


       }
        // $student_request_session = $this->Session->read('student_request');  //, $this->request->data['Request']);
		// if(empty( $student_request_session) ||  empty($student_request_session['tutor_request'])) {
			// return $this->redirect(referer('/'));
		// }
    } else if($this->request->is('post')) { 
	  // debug($this->request->data); die();
	   if(!empty($this->request->data)) {
             //Student is registering and messaging tutor at same time
             //Separate the Student Regitration Form from the Student Message Form

		   $student_request = $this->Session->read('student_request'); //, $this->request->data['Request']);
           // $studentMessage['StudentMessage'] = $this->request->data['StudentTutorContact'];
           // debug($student_request); //die();
			//debug($this->request->data); die();
            unset($this->request->data['StudentTutorContact']);
           // debug($this->request->data); //die();
            $this->request->data['Student']['referral_source'] = $this->request->data['Student']['contactbox'];

            Configure::write('Users.role', 'student');
            Configure::write('join_via_request', 'message_tutor');

            $this->add();

            //Now that we've successfully registered user,
            //make sure his/her message is verified before being sent as we should not allowed the inclusion
            //of any direct contact info (email, phone or social Network profile) unless the student has a payment on file

           //Automatically log user in
            if($this->Auth->login()) {

                $this->{$this->modelClass}->id = $this->Auth->user('id');
                $this->Session->write('first_login', true);
                $this->{$this->modelClass}->saveField('last_login', date('Y-m-d H:i:s'));

                //Since the beforFilter() method is already hit by the time we get here
                //and at that time the Student info did not exist yet.. All of the user info below we normally retreive in beforeFilter()  would not have
                //been in Session.... So we have to put it in there
                $user_data = $this->{$this->modelClass}->findById($this->Auth->user('id'));
                //debug($user_data); die();

		 		if(!empty($user_data)) {
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
                    $last_name = $user_data[$this->modelClass]['last_name'];
		 		    $last_login = $user_data[$this->modelClass]['last_login'];
                    $user_zip_code = $user_data[$this->modelClass]['zip_code'];

		            $this->Session->write('username', $user_fname);
                    $this->Session->write('lastname', $last_name);
		            $this->Session->write('last_login', $last_login);
                    $this->Session->write('student_zip_code', $user_zip_code);

                    $result = $tutors_model->find_city_ByZipCode($user_zip_code);

                   	if(!empty($result)) {
                        $student_city = $result['city'];
                        $student_state = $result['state'];

                        $this->Session->write('student_city', $student_city);
                        $this->Session->write('student_state', $student_state);
                      }

                }
              // debug($user_data); die();
                //Now that we have successfully registered the student, We need to Send the Message to Tutor.
                //So we will swap the request data for the Student Message Data
                $member_id = $this->request->data['Student']['member_id'];
                $this->request->data = array();
                $this->request->data['StudentTutorContact'] = $studentMessage['StudentMessage'];
                $this->request->data['StudentTutorContact']['member_id'] =  $member_id;
                $this->request->data['StudentTutorContact']['copy_me'] = 1;
                $this->request->data['StudentTutorContact']['message_channel'] = 'tutorDetailProfile';

               //$this->Session->write('message_tutor', 'message_tutor');
               // $this->send_message();

               $this->contact_tutor();
               // return $this->redirect(array('action' => 'welcome'));
            }

            //$this->Session->write('error_array', $this->{$this->modelClass}->validationErrors);
			 
			 
		      return $this->redirect(array('action' => 'tutor_request_sign_up'));
            }	   
		  
	   }	
	}

public function tutor_request($member_id=null) {
	
	 $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='default';
     $message = 'Tutor Profile could not be found';
     $warning = false;
	
// debug($member_id); die();

   /** if(empty($member_id) ) {
       //throw new NotFoundException(__('Invalid Request'));
	    $warning = true;
         $this->Session->write('warning', $warning);
         $this->Session->setFlash($message, 'custom_msg');
	   return $this->redirect(array('action' => 'tutor_search_results'));
     }
   **/ 
    
     $tutor_profile = array();
     $tutor_model = new Tutor();
    
	 
   if($this->request->is('get')) {
      // $id = null;
	  
     if(empty($member_id) || (!$tutor = $tutor_model->find('first', array(
                            'conditions' => array(
                                'Tutor.member_id' => $member_id,
                                'Tutor.email_verified' => 1,
                                'Tutor.profile_status' => 1,
                                'Tutor.active' => 1))))
      ) {
        // $message = 'Tutor Profile could not be found';
         $warning = true;
         $this->Session->write('warning', $warning);
         $this->Session->setFlash($message, 'custom_msg');

        return $this->redirect(array('action' => 'tutor_search_results'));
        }

        //debug($tutor['StudentWatchList']); die();
       if(!empty($tutor) && !empty($tutor['TutorProfile']) && !empty($tutor['Tutor'])) {

              $first_name = $tutor['Tutor']['first_name'];
              $last_name = $tutor['Tutor']['last_name'];
			   $member_id = $tutor['Tutor']['member_id'];

              $last_name = substr($last_name,0,1);
              $blank = "  ";
              $full_name = "$first_name $blank $last_name";
              $this->set('full_name', $full_name);
              $this->set('first_name', $first_name);
			  $this->set('last_name', $last_name);
              $this->set('about_me', $tutor['TutorProfile']['description']);
              $this->set('city', $tutor['TutorProfile']['city']);
              $this->set('state', $tutor['TutorProfile']['state']);
              $this->set('profile_zip_code',  $tutor['TutorProfile']['zip_code']);
              $this->set('degree', $tutor['TutorProfile']['degree']);
              $this->set('school', $tutor['TutorProfile']['school']);
              $this->set('hourly_rate', $tutor['TutorProfile']['hourly_rate']);
              $this->set('travel_radius', $tutor['TutorProfile']['travel_radius']);
              $this->set('bg_checked', $tutor['TutorProfile']['background_checked']);
              $this->set('rating_score', $tutor['TutorProfile']['avg_rating_score']);
              $this->set('ratings', $tutor['TutorProfile']['ratings']);
			  $this->set('title', $tutor['TutorProfile']['title']);
			  $this->set('cancel_policy', $tutor['TutorProfile']['cancel_policy']);
			  //$this->set('member_id', $member_id);
			  
			 // debug($member_id); die();

              $tutor_id = $tutor['Tutor']['id'];

              if(!empty($tutor_id)) {

                $this->set('tutor_id', $tutor_id);

                $tutor_profile_photo = $tutor_model->get_tutor_profile_pic($tutor_id);
                if(!empty($tutor_profile_photo)){
                   $this->set('profile_pic', $tutor_profile_photo);
                }

                 $tutor_subjects = $tutor_model->get_all_subjects_for_tutor($tutor_id);
				// debug($tutor_subjects); die();
                 if(empty($tutor_subjects)) {
					  $warning = true;
					  $this->Session->write('warning', $warning);
					  $this->Session->setFlash($message, 'custom_msg');

					  return $this->redirect(array('action' => 'tutor_search_results'));
                 }

                 $tutor_subjects = $this->flatten($tutor_subjects, '');

                 asort($tutor_subjects);
                 $this->set('tutor_subjects', $tutor_subjects);

                 $tutor_subjects =  array(self::SUBJECT_ID_100 => 'Choose a Subject') +  $tutor_subjects;
                 Configure::write('all_tutor_subjects',$tutor_subjects);

                 // $this->set('all_tutor_subjects', $tutor_subjects);
             }


       }

    } else  if($this->request->is('post')) { 
	   
	   if(!empty($this->request->data)) {	
	   
	     //debug($this->request->data); die();
            
		   $this->Session->write('subject', $this->request->data['Request']['subj']);
		   $this->Session->write('cur_zip_code', $this->request->data['Request']['zip']);
		   $this->Session->write('student_message', $this->request->data['Request']['message']);
		   $this->Session->write('student_request', $this->request->data);
		   
		  // debug($this->request->data); //die();
		   return $this->redirect(array('action' => 'tutor_request_sign_up'));
	   }
	
	}

    
  }

public function tutor_details_profile($member_id=null) {
         $this->set('title_for_layout', 'Daraji-Tutor Search Results');
         $this->layout='default';
         $message = 'Tutor Profile could not be found';
         $warning = false;

    // debug($member_id); die();

     if(empty($member_id) ) {
       throw new NotFoundException(__('Invalid Request'));
     }
    
     if ($this->Auth->loggedIn()) {

	   	  	return $this->redirect(array('action' => 'tutor_details_profile_auth/'.$member_id));
     }

     $tutor_profile = array();
     $tutor_model = new Tutor();

   if($this->request->is('get')) {
      // $id = null;
     if(empty($member_id) || (!$tutor = $tutor_model->find('first', array(
                            'conditions' => array(
                                'Tutor.member_id' => $member_id,
                                'Tutor.email_verified' => 1,
                                'Tutor.profile_status' => 1,
                                'Tutor.active' => 1))))
      ) {
        // $message = 'Tutor Profile could not be found';
         $warning = true;
         $this->Session->write('warning', $warning);
         $this->Session->setFlash($message, 'custom_msg');

        return $this->redirect(array('action' => 'tutor_search_results'));
        }

        //debug($tutor['StudentWatchList']); die();
       if(!empty($tutor) && !empty($tutor['TutorProfile']) && !empty($tutor['Tutor'])) {

              $first_name = $tutor['Tutor']['first_name'];
              $last_name = $tutor['Tutor']['last_name'];
			  

              $last_name = substr($last_name,0,1);
              $blank = "  ";
              $full_name = "$first_name $blank $last_name";
              $this->set('full_name', $full_name);
              $this->set('first_name', $first_name);
			  $this->set('last_name', $last_name);
              $this->set('about_me', $tutor['TutorProfile']['description']);
              $this->set('city', $tutor['TutorProfile']['city']);
              $this->set('state', $tutor['TutorProfile']['state']);
              $this->set('profile_zip_code',  $tutor['TutorProfile']['zip_code']);
              $this->set('degree', $tutor['TutorProfile']['degree']);
              $this->set('school', $tutor['TutorProfile']['school']);
              $this->set('hourly_rate', $tutor['TutorProfile']['hourly_rate']);
              $this->set('travel_radius', $tutor['TutorProfile']['travel_radius']);
              $this->set('bg_checked', $tutor['TutorProfile']['background_checked']);
              $this->set('rating_score', $tutor['TutorProfile']['avg_rating_score']);
              $this->set('ratings', $tutor['TutorProfile']['ratings']);
			  $this->set('title', $tutor['TutorProfile']['title']);
			  $this->set('cancel_policy', $tutor['TutorProfile']['cancel_policy']);

              $tutor_id = $tutor['Tutor']['id'];

              if(!empty($tutor_id)) {

                $this->set('tutor_id', $tutor_id);

                $tutor_profile_photo = $tutor_model->get_tutor_profile_pic($tutor_id);
                if(!empty($tutor_profile_photo)){
                   $this->set('profile_pic', $tutor_profile_photo);
                }

                 $tutor_subjects = $tutor_model->get_all_subjects_for_tutor($tutor_id);
				// debug($tutor_subjects); die();
                 if(empty($tutor_subjects)) {
					  $warning = true;
					  $this->Session->write('warning', $warning);
					  $this->Session->setFlash($message, 'custom_msg');

					  return $this->redirect(array('action' => 'tutor_search_results'));
                 }

                 $tutor_subjects = $this->flatten($tutor_subjects, '');

                 asort($tutor_subjects);
                 $this->set('tutor_subjects', $tutor_subjects);

                 $tutor_subjects =  array(self::SUBJECT_ID_100 => 'Choose a Subject') +  $tutor_subjects;
                 Configure::write('all_tutor_subjects',$tutor_subjects);

                 // $this->set('all_tutor_subjects', $tutor_subjects);
             }


       }

    }

    //die();
  }



  public function requestTutor() {
    $this->layout='student';

  }

  public function tellYourFriends() {
    $this->layout='student';

  }

 public function contact_tutor() {

   $this->layout='student';
   $tutor_model = new Tutor();
   if($this->request->is('post')) {
	         $id = null;
	         $postData = array();
              if (!empty($this->request->data)) {
	    	    //debug($this->request->data); die();
                        if((!$data = $tutor_model->find('first', array(
                                            'conditions' => array(
                                                'Tutor.member_id' => $this->request->data['StudentTutorContact']['member_id']
                                                  )))))
                        {
                                     throw new NotFoundException(__('Invalid Request'));
                                     $this->Session->setFlash(
                								 sprintf(__d('users', '')),
                						         'default',
                								  array('class' => 'alert alert-warning')
                							   );
                                  // return $this->redirect(array('action' => 'tutor_details_profile_auth/'.$member_id));
                                    return $this->redirect(array('action' => 'tutor_details_profie_auth', $this->request->data['TutorStudentTutor']['member_id']));

                        }

                      $message_id = uniqid(rand(), true);
                      $result = String::tokenize($message_id, '.');
                      $message_id  = $result[1];
                      $this->request->data['StudentTutorContact']['message_id'] = $message_id;
					  
                      $this->request->data['StudentTutorContact']['student_id'] = $this->Auth->user('id');
                      if(!empty($data['Tutor']['member_id'])) {
                              $this->request->data['StudentTutorContact']['tutor_id'] = $data['Tutor']['id'] ;
                      }
					  
                      $message_date = date('Y-m-d H:i:s');
                      $this->request->data['StudentTutorContact']['message_date'] = $message_date;
                      $this->request->data['StudentTutorContact']['sender_role'] = 'Student';
                     // $this->request->data['StudentTutorContact']['message_subject'] = 'Need Help with '.$this->request->data['StudentTutorContact']['subject_name'];

                      if(empty($this->request->data['StudentTutorContact']['copy_me'])) {
                            $this->request->data['StudentTutorContact']['copy_me'] = 0;
                      }

                      $student_user = $this->{$this->modelClass}->findById($this->Auth->user('id')); //$this->Session->read('student_user');

                      //Sender and Receiver's internal wizwonk email addresses
                      $this->request->data['StudentTutorContact']['message_from'] = 'donald@wizwonk.com'; //$student_user[$this->modelClass]['assigned_email'] ;
                      $this->request->data['StudentTutorContact']['message_to'] = 'dame@wizwonk.com'; //$data['Tutor']['assigned_email'];

                      //Sender and Receiver's externa personal email addresses
                      $this->request->data['StudentTutorContact']['message_from_ext'] = $student_user[$this->modelClass]['email'];
                      $this->request->data['StudentTutorContact']['message_to_ext'] = $data['Tutor']['email'];
                     
					// debug($this->request->data); die();
					   $postData = array();
					   $postData['MessageArchive']['student_id'] =  $this->request->data['StudentTutorContact']['student_id'];
					   $postData['MessageArchive']['tutor_id'] = $this->request->data['StudentTutorContact']['tutor_id'];
					   $postData['MessageArchive']['from_user'] =  $this->request->data['StudentTutorContact']['student_id'];
					   $postData['MessageArchive']['to_user'] = $this->request->data['StudentTutorContact']['tutor_id'];
					   $postData['MessageArchive']['message'] = $this->request->data['StudentTutorContact']['message'];
								
					   $message_archive_model  = new MessageArchive();
					   
                      //Check if Tutor and Student have ever been in contact, if they have, then store in message_archives
                       if(($data = $this->{$this->modelClass}->StudentsTutor->find('first', array(
                                            'conditions' => array(
                                                'StudentsTutor.tutor_id' => $this->request->data['StudentTutorContact']['tutor_id'],
                                                'StudentsTutor.student_id' => $this->request->data['StudentTutorContact']['student_id'],
                                                  )))))
                       {
							   $this->request->data['StudentTutorContact']['first_contact'] = 0; //meaning it's Not true that this is the first time 
							   //Since this is a first contact intiated, the student automatically becomes a
							   //new Connection for the tutoro being contacted.
							   //So add student to students_tutors_connections table
							   
								
								
							    if($message_archive_model->saveMessage($postData))
							    {
							          return $this->redirect(array('action' => 'message'));
							    }
								
							
						  return $this->redirect(array('action' => 'message'));
						   
                      }  else {
                            $this->request->data['StudentTutorContact']['first_contact'] = 1;
                        // }
                          //  debug($this->request->data); die();
                         //Since this is HBTM relationship, Cake automagically create the StudentsTutor behind scene
                         //if the default option was chosen in the respective models (Student/Tutor)
                          $this->{$this->modelClass}->StudentsTutor->set(array(

                                   'student_id' => $this->request->data['StudentTutorContact']['student_id'],
                                   'tutor_id' => $this->request->data['StudentTutorContact']['tutor_id'],

                                   'first_contact' => $this->request->data['StudentTutorContact']['first_contact'],
                                   'message_date' => $message_date, //$this->request->data['StudentTutorContact']['message_date'],
                                   'message_channel' => $this->request->data['StudentTutorContact']['message_channel'],
                                   'message_id' => $this->request->data['StudentTutorContact']['message_id'],
                                   'sender_role' =>  $this->request->data['StudentTutorContact']['sender_role'],
                                   'message' => $this->request->data['StudentTutorContact']['message'],
                                   //'message_body' => $this->request->data['StudentTutorContact']['message_body'],
                                   'message_from' => $this->request->data['StudentTutorContact']['message_from'],
                                   'message_to' => $this->request->data['StudentTutorContact']['message_to'],
                                   'message_from_ext' => $this->request->data['StudentTutorContact']['message_from_ext'],
                                   'message_to_ext' => $this->request->data['StudentTutorContact']['message_to_ext'],
                                   'subject_name' => $this->request->data['StudentTutorContact']['subject_name'],
                                  // 'subject_id' => $this->request->data['StudentTutorContact']['subject_id']
                         ));
             //if( $this->{$this->modelClass}->StudentsTutor->validates(array('fieldList' => array('validate' => false))))
             if( $this->{$this->modelClass}->StudentsTutor->validates(array('fieldList' => array('message', 'subject_name'))))

             {
                   if($this->{$this->modelClass}->StudentsTutor->save($id, $this->request->data) )

        		   {
        		                  //Send the message before returning to user

                                   //if(  $this->_send_message($this->request->data)) {

                                          // $this->_send_message($this->request->data);
                                   //}
						//debug($postData); die();
				       if($message_archive_model->saveMessage($postData) )
        		       {
						    return $this->redirect(array('action' => 'message'));
					   }
                         
                               
                           $this->Session->setFlash
        	    									(
        	    												sprintf(__d('users', 'Your Message has been successfully sent. Tutor will reply promptly.')),
        	    											   'default',
        	    												array('class' => 'alert alert-success')
        	    									 );
                        // return $this->redirect(array('action' => 'tutor_details_profile_auth', $this->request->data['StudentTutorContact']['member_id']));
						  return $this->redirect(array('action' => 'message'));


                   } else {
        	    					      $this->Session->setFlash
        	 						     		(
        	 						     					sprintf(__d('users', 'Something went wrong.')),
        	 						     					'default',
        	 						     					 array('class' => 'alert alert-warning')
        	    									 );

                                 return $this->redirect(array('action' => 'tutor_details_profile_auth', $this->request->data['StudentTutorContact']['member_id']));

        				  }

            } else {

                        $this->Session->write('error_array', $this->{$this->modelClass}->validationErrors);
                        return $this->redirect(array('action' => 'tutor_details_profile_auth', $this->request->data['StudentTutorContact']['member_id']));


                     }

		  }
        }

     }

 }

public function accountsettings() {
          $this->layout='student';
}

public function helpstudent() {
         $this->layout='student';
}

public function tutor_search_results_auth() {

    $this->layout = 'student';
    $radiusSearch = new ZipSearch();
    $tutor = new Tutor();
    $conditions = array();
    $tutors_model = new Tutor();
    $categorie_model = new Categorie();
    $subject_model = new Subject();
    $search_agent_model = new StudentSearchAgent();
    $watchListModel = new StudentWatchList();

    $cat_array = array('All Categories', '400');
    $subj_array = array('All Subjects', '200', '100');
     //debug("test")

    $warning = $this->Session->read('warning');
    $this->set('warning', $warning);
    $this->Session->delete('warning');

   if($this->request->is('get')) {

     $id = null;
     $rs = null;
     // pagination
     $posts_per_page = 3;
     $total_post_count = 0;
     $cur_page = 1;
     $start_page = 1;
     $display_page_navigation = 9;

     if(!empty($_GET['cur_page'])){
      $cur_page = $_GET['cur_page'];
    }

    if($this->Session->check('params_url')) {
        $this->Session->delete('params_url');
     }
       //debug($this->params->query); // die();
	   
	  

     if (!empty($this->params->query['user_category']) && in_array($this->params->query['user_category'], $cat_array) &&
             (!empty($this->params->query['user_subject'])  &&  in_array($this->params->query['user_subject'], $subj_array) )) { // if submit
             // debug("in_array_in_array"); die();

          //$cat = $this->params->query['user_category'];
         //The user entered zip code always takes priority over the computed zip code.
         //debug($this->params->query); //die();
		 
	
             
                   //$this->params->query['user_subject'] = 'Math';

     $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;

     $this->params->query['zip_code'] = $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
     $this->params->query['distance'] = $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;

     $this->params->query['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "All Subjects";
     $this->params->query['subject_id'] = !empty($this->params->query['user_subject_id']) ? $this->params->query['user_subject_id'] : "AllSubjects";

     $this->params->query['category'] = !empty($this->params->query['user_category']) ? $this->params->query['user_category'] : "All Categories";
     $this->params->query['category_id'] = !empty($this->params->query['user_category_id']) ? $this->params->query['user_category_id'] : "AllCategories";

      $this->params->query['user_category'] = $this->params->query['category'];
      $this->params->query['user_category_id'] = $this->params->query['category_id'];
      $this->params->query['user_subject'] = $this->params->query['subject'];
      $this->params->query['user_subject_id'] = $this->params->query['subject_id'];


     $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
     $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate'])? $this->params->query['amount_min_rate']: 10;
     $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate'])? $this->params->query['amount_max_rate']: 250;
     $this->params->query['min_age'] = !empty($this->params->query['min_age'])? $this->params->query['min_age']: 18;
     $this->params->query['max_age'] = !empty($this->params->query['max_age'])? $this->params->query['max_age']: 100;
     $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
     $this->params->query['bg_checked'] = !empty($this->params->query['bg_checked'])? $this->params->query['bg_checked']: 0;

     $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
	 $kwd =  $this->params->query['kwd'];

     $this->params->query_agent = $this->params->query;

        // debug($cur_zip_code); die();
       // ovewrites the computed zip code in the session
       //if user entered a zip code manually
       $this->Session->write('cur_zip_code', $cur_zip_code);
       if(empty($cur_zip_code)  || $cur_zip_code === "") {
         $this->Session->write('cur_zip_code', "");
       }

	    //debug($this->params->query);  die();
        // advanced search
       if($this->params->query['is_advanced'] == 1) {


               $conditions_for_search = array();
               $conditions_for_search['is_advanced'] = true;
               $conditions_for_search['subject'] = $this->params->query['user_subject']; //!empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : ""; //"All Subjects";

            // debug($conditions_for_search['subject']); die();
               $conditions_for_search['hourly_rate'] = "";
               $conditions_for_search['age'] = "";
               $conditions_for_search['gender'] = "";
               $conditions_for_search['bg_checked'] = "";

              // hourly rate
             if(!empty($this->params->query['amount_min_rate'])
                &&
                !empty($this->params->query['amount_max_rate'])){
                $conditions_for_search['hourly_rate'] = $this->params->query['amount_min_rate'] . "," . $this->params->query['amount_max_rate'];
              }
              // end hourly rate

          // age
          if(!empty($this->params->query['min_age'])
            &&
            !empty($this->params->query['max_age'])){
            $conditions_for_search['age'] = $this->params->query['min_age'] . "," . $this->params->query['max_age'];
          }
          // end age
          $conditions_for_search['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : "";
          $conditions_for_search['bg_checked'] = !empty($this->params->query['bg_checked']) ? $this->params->query['bg_checked'] : 0;

         //debug($conditions_for_search); //die();
        // debug($this->params->query); //die();
          $result = $tutors_model->find_by_params($kwd, "",  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);

          $this->set('is_advanced', $this->params->query['is_advanced']);
          $this->set('gender', $this->params->query['gender']);
          $bg_checked = !empty($this->params->query['bg_checked'])? $this->params->query['bg_checked']:0;

          $this->set('bg_checked', $bg_checked);
          $this->set('amount_min_rate', $this->params->query['amount_min_rate']);
          $this->set('amount_max_rate', $this->params->query['amount_max_rate']);

          $this->set('min_age', $this->params->query['min_age']);
          $this->set('max_age', $this->params->query['max_age']);
     } else {
       // debug("here"); die();
        $result = $tutors_model->get_all_tutors($cur_zip_code, null, $this->Session->read('cur_zip_code'), $distance, $kwd,$this->params->query );
        $this->set('gender', 0);
        $this->set('bg_checked', 0);
        $this->set('amount_min_rate', 10);
        $this->set('amount_max_rate', 250);
        $this->set('min_age', 18);
        $this->set('max_age', 250);
        $this->set('is_advanced', 0);

     }
      //debug($this->params->query); // die();
      $return_array = $this->_get_nav_data($result, $cur_page);

      $this->set('tutors', $return_array);

      $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('distance', $distance);
      $this->set('subject', $this->params->query['user_subject']);
      $this->set('header_subject', $this->params->query['user_subject']);
      $this->set('category', $this->params->query['user_category']);
      $this->set('subject_id', $this->params->query['user_subject_id']);
      $this->set('category_id', $this->params->query['user_category_id']);
      $this->set('cur_page', $cur_page);
      $this->set('kwd', $this->params->query['kwd']);




      if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
      } else {
            $this->set('sortBy', "Best Match");
      }


   } else if (!empty($this->params->query['user_category']) && !in_array($this->params->query['user_category'], $cat_array) &&
             (!empty($this->params->query['user_subject'])  &&  in_array($this->params->query['user_subject'], $subj_array) )) { // if submit

         //debug("Notin_array_in_array"); //die();
        // debug($this->params->query); //die();
     $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;

     $this->params->query['zip_code'] = $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
     $this->params->query['distance'] = $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;

     $this->params->query['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "All Subjects";
     $this->params->query['subject_id'] = !empty($this->params->query['user_subject_id']) ? $this->params->query['user_subject_id'] : "AllSubjects";

     // $this->params->query['subject'] = !empty($this->params->query['user_subject_pop']) ? $this->params->query['user_subject_pop'] : "All Subjects";
	 	// $this->params->query['subject_id'] = !empty($this->params->query['user_subject_pop_id']) ? $this->params->query['user_subject_pop_id'] : "AllSubjects";
    //debug($this->params->query['subject']); die();

     $this->params->query['category'] = !empty($this->params->query['user_category']) ? $this->params->query['user_category'] : "All Categories";
     $this->params->query['category_id'] = !empty($this->params->query['user_category_id']) ? $this->params->query['user_category_id'] : "AllCategories";

     $this->params->query['user_category'] = $this->params->query['category'];
      $this->params->query['user_category_id'] = $this->params->query['category_id'];
      $this->params->query['user_subject'] = $this->params->query['subject'];
      $this->params->query['user_subject_id'] = $this->params->query['subject_id'];


     $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
     $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate'])? $this->params->query['amount_min_rate']: 10;
     $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate'])? $this->params->query['amount_max_rate']: 250;
     $this->params->query['min_age'] = !empty($this->params->query['min_age'])? $this->params->query['min_age']: 18;
     $this->params->query['max_age'] = !empty($this->params->query['max_age'])? $this->params->query['max_age']: 100;
     $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
     $this->params->query['bg_checked'] = !empty($this->params->query['bg_checked'])? $this->params->query['bg_checked']: 0;

     $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
	 $kwd =  $this->params->query['kwd'];

    // $this->params->query_agent = $this->params->query;
        //debug($this->params->query); die();
         $result = "";
            // ovewrites the computed zip code in the session if user entered a zip code manually
       $this->Session->write('cur_zip_code', $cur_zip_code);
       if(empty($cur_zip_code)  || $cur_zip_code === "") {
         $this->Session->write('cur_zip_code', "");
       }
   debug($this->params->query);  die();
        // advanced search
       if($this->params->query['is_advanced'] == 1){

            $conditions_for_search = array();
            $conditions_for_search['is_advanced'] = true;
            //$conditions_for_search['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "All Subjects";


            $conditions_for_search['hourly_rate'] = "";
            $conditions_for_search['age'] = "";
            $conditions_for_search['gender'] = "";
            $conditions_for_search['bg_checked'] = "";
              // hourly rate
             if(!empty($this->params->query['amount_min_rate'])
                &&
                !empty($this->params->query['amount_max_rate'])){
                $conditions_for_search['hourly_rate'] = $this->params->query['amount_min_rate'] . "," . $this->params->query['amount_max_rate'];
              }
              // end hourly rate

          // age
          if(!empty($this->params->query['min_age'])
            &&
            !empty($this->params->query['max_age'])){
            $conditions_for_search['age'] = $this->params->query['min_age'] . "," . $this->params->query['max_age'];
          }
          // end age
          $conditions_for_search['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : "";
          $conditions_for_search['bg_checked'] = !empty($this->params->query['bg_checked']) ? $this->params->query['bg_checked'] : 0;

          $conditions_for_search['subject'] = $this->params->query['user_category'];

          $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        //$result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);

          $this->set('is_advanced', $this->params->query['is_advanced']);
          $this->set('gender', $this->params->query['gender']);
          $bg_checked = !empty($this->params->query['bg_checked'])? $this->params->query['bg_checked']:0;

          $this->set('bg_checked', $bg_checked);
          $this->set('amount_min_rate', $this->params->query['amount_min_rate']);
          $this->set('amount_max_rate', $this->params->query['amount_max_rate']);

          $this->set('min_age', $this->params->query['min_age']);
          $this->set('max_age', $this->params->query['max_age']);

     } else {
         $conditions_for_search['subject'] = $this->params->query['user_category'];
         $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", "", "", "", "", false, $this->Session->read('cur_zip_code'), $this->params->query);

        //$result = $tutors_model->get_all_tutors($cur_zip_code, $cat, $this->Session->read('cur_zip_code'), $distance, $kwd,$this->params->query );
        $this->set('gender', 0);
        $this->set('bg_checked', 0);
        $this->set('amount_min_rate', 10);
        $this->set('amount_max_rate', 250);
        $this->set('min_age', 18);
        $this->set('max_age', 250);
        $this->set('is_advanced', 0);
     }
       //Have to do this.. because the search agent is looking for the subject index
       //in the params->query. User did not choose a subject so the Category will be the subject
       //for query purposes
       // $this->params->query['subject'] = $this->params->query['user_subject']; //$this->params->query['user_category'];
       // $this->params->query['category'] = $this->params->query['user_category'];


        $return_array = $this->_get_nav_data($result, $cur_page);
        //debug($return_array);

        $this->set('tutors', $return_array);
       $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('distance', $distance);
      $this->set('subject', $this->params->query['user_subject']);
      $this->set('header_subject', $this->params->query['user_subject']);
      $this->set('category', $this->params->query['user_category']);
      $this->set('subject_id', $this->params->query['user_subject_id']);
      $this->set('category_id', $this->params->query['user_category_id']);
      $this->set('cur_page', $cur_page);
      $this->set('kwd', $this->params->query['kwd']);

      if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
      } else {
            $this->set('sortBy', "Best Match");
      }

       //debug($this->params->query) ;  //die();

   } else if (!empty($this->params->query) && !empty($this->params->query['distance'])) { // if submit
      //debug("idistance"); //die();
       //debug($this->params->query); die();
         //we need to have access to the search criteria for
         //when the user decides to save the Search resuts as a Search Agent
         //So we put them in the Session
         //$this->Session->write('$session_search_criteria', $this->params->query);
		  //debug($this->params->query);  die();
     $result = null;
     $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;

     $this->params->query['zip_code'] = $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
     $this->params->query['distance'] = $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;

     $this->params->query['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "All Subjects";
     $this->params->query['subject_id'] = !empty($this->params->query['user_subject_id']) ? $this->params->query['user_subject_id'] : "AllSubjects";
     $this->params->query['category'] = !empty($this->params->query['user_category']) ? $this->params->query['user_category'] : "All Categories";
     $this->params->query['category_id'] = !empty($this->params->query['user_category_id']) ? $this->params->query['user_category_id'] : "AllCategories";

     $this->params->query['user_category'] = $this->params->query['category'];
      $this->params->query['user_category_id'] = $this->params->query['category_id'];
      $this->params->query['user_subject'] = $this->params->query['subject'];
      $this->params->query['user_subject_id'] = $this->params->query['subject_id'];

     $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
     $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate'])? $this->params->query['amount_min_rate']: 10;
     $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate'])? $this->params->query['amount_max_rate']: 250;
     $this->params->query['min_age'] = !empty($this->params->query['min_age'])? $this->params->query['min_age']: 18;
     $this->params->query['max_age'] = !empty($this->params->query['max_age'])? $this->params->query['max_age']: 100;
     $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
     $this->params->query['bg_checked'] = !empty($this->params->query['bg_checked'])? $this->params->query['bg_checked']: 0;

     $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
	 $kwd =  $this->params->query['kwd'];

        $cur_zip_code =  $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code'); //$cur_zip_code;

            if(!$this->validateUSAZip($cur_zip_code)) {

                $this->set('success', false);
                //$this->Session->setFlash('You have entered an Invalid Zip Code', 'custom_msg');
                $this->Session->setFlash
					(
					sprintf(__d('users', 'You have entered an invalid Zip Code.')),
					'default',
					 array('class' => 'alert alert-danger')
					);
                return $this->redirect('tutor_search_results_auth');

            }


         debug($this->params->query); //die();
            //$this->params->query_agent = $this->params->query;
           //debug($this->params->query_agent); die();


               //we need to have access to the search criteria for
              //when the user decides to save the Search resuts as a Search Agent
             //So we put them in the Session

    // end init default parameters

       if(!preg_match('/^[0-9]{1,3}$/', $this->params->query['distance'])) {
        	$this->Session->setFlash
								(
											sprintf(__d('users', 'You did not enter a properly formatted distance.')),
										    'default',
											 array('class' => 'alert alert-warning')
								 );
      }

       $this->set('city', $this->_set_city_for_zip($cur_zip_code));
       $this->Session->write('cur_zip_code', $cur_zip_code);


      if(empty($cur_zip_code)  || $cur_zip_code === "") {
         $this->Session->write('cur_zip_code', "");
       } else {
          $this->Session->write('cur_zip_code', $cur_zip_code);
       }


     try{

        /**
         * make condition array
         */


         $conditions_for_search = array();

        /**
         if(strtolower($this->params->query['user_subject']) === strtolower('Mathematics') ) {
                   $this->params->query['user_subject'] = 'Math';
                   //$conditions_for_search['subject'] = 'Math';
         } else if(strtolower($this->params->query['user_subject']) === strtolower('Technology' )) {
                  $this->params->query['user_subject'] = 'Tech';
                  //$this->params->query['user_subject'] = 'Tech';
         }
         **/

        $conditions_for_search['subject'] = !empty($this->params->query['user_subject']) ? $this->params->query['user_subject'] : "";
        $conditions_for_search['hourly_rate'] = "";
        $conditions_for_search['age'] = "";
        $conditions_for_search['gender'] = "";
        $conditions_for_search['bg_checked'] = "";
        $conditions_for_search['is_advanced'] = false;

        // advanced search
       if($this->params->query['is_advanced'] == 1){

            $conditions_for_search['is_advanced'] = true;

              // hourly rate
             if(!empty($this->params->query['amount_min_rate'])
                &&
                !empty($this->params->query['amount_max_rate'])){
                $conditions_for_search['hourly_rate'] = $this->params->query['amount_min_rate'] . "," . $this->params->query['amount_max_rate'];
              }
              // end hourly rate

              // age
              if(!empty($this->params->query['min_age'])
                &&
                !empty($this->params->query['max_age'])){
                $conditions_for_search['age'] = $this->params->query['min_age'] . "," . $this->params->query['max_age'];
              }
              // end age

              $conditions_for_search['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : "";
              $conditions_for_search['bg_checked'] = !empty($this->params->query['bg_checked']) ? $this->params->query['bg_checked'] : 0;

              $this->set('is_advanced', $this->params->query['is_advanced']);
              $this->set('gender', $this->params->query['gender']);
              $bg_checked = !empty($this->params->query['bg_checked'])? $this->params->query['bg_checked']:0;

              $this->set('bg_checked', $bg_checked);
              $this->set('amount_min_rate', $this->params->query['amount_min_rate']);
              $this->set('amount_max_rate', $this->params->query['amount_max_rate']);

              $this->set('min_age', $this->params->query['min_age']);
              $this->set('max_age', $this->params->query['max_age']);

      } else {
         $this->set('gender', 0);
        $this->set('bg_checked', 0);
        $this->set('amount_min_rate', 10);
        $this->set('amount_max_rate', 250);
        $this->set('min_age', 18);
        $this->set('max_age', 250);
        $this->set('is_advanced', 0);
      }

    //  debug($this->params->query['user_subject']); //die();
      //This whole block of code deals was introduced to deal with the Header Subject Search

         $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);


        $real_cat_array = array('Math', 'Mathematics', 'Science', 'Technology', 'Tech', 'Engineering', 'Computer Science', 'Computer Technology');

        if(!in_array($this->params->query['user_subject'], $subj_array) ) {
          // debug("hsgdh"); die();
             $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);

            // debug($result); //die();
        } else {
            $result = $tutors_model->find_by_params($kwd, "",  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        }

       /** if(in_array($this->params->query['user_subject'], $real_cat_array))  {
            //User has sent in a Category from Header Search

               //debug($this->params->query['user_subject']); //die();
              if(strtolower($this->params->query['user_subject']) === strtolower('Mathematics')
              || strtolower($this->params->query['user_subject']) === strtolower('Math') ) {

                   //$this->params->query['user_subject'] = 'Math';

                   $this->params->query['user_category'] = 'Math';
                   $this->params->query['user_category_id'] = 'Math';

              } else if(strtolower($this->params->query['user_subject']) === strtolower('Technology' )
              || strtolower($this->params->query['user_subject']) === strtolower('Tech' )) {
                  // $this->params->query['user_subject'] = 'Tech';
                   $this->params->query['user_category'] = 'Technology';
                   $this->params->query['user_category_id'] = 'Tech';

                  // debug("hetet"); die();
              } else {

                  $this->params->query['user_category'] = $this->params->query['user_subject'];
                  $this->params->query['user_category_id'] = $categorie_model->get_category_id($this->params->query['user_category']); //$this->params->query['user_subject'];
                  //debug($this->params->query['user_category_id']); die();
              }

              $this->set('header_subject', $this->params->query['user_subject']);

              $this->params->query['user_subject'] = 'All Subjects';
              $this->params->query['user_subject_id'] = 'AllSubjects';

        } else **/

        // if(!in_array($this->params->query['user_subject'], $real_cat_array) && ! in_array($this->params->query['user_subject'], $subj_array))  {
            //We have a real subject from Header

              if(in_array($this->params->query['user_category'], $cat_array )) { //Must be coming from Header
                  $this->params->query['user_subject_id'] = $subject_model->get_subject_id($this->params->query['user_subject']);
                //debug("hsgdh"); die();
                  $this->params->query['user_category_id'] = $this->Session->read('cat_id'); //$subject_model->get_subject_id($this->params->query['user_subject']);
                  $this->params->query['user_category'] = $this->Session->read('cat_name'); //$subject_model->get_subject_id($this->params->query['user_subject']);

                  $this->params->query['category_id'] = $this->Session->read('cat_id'); //$subject_model->get_subject_id($this->params->query['user_subject']);
                  $this->params->query['category'] = $this->Session->read('cat_name'); //$subject_model->get_subject_id($this->params->query['user_subject']);

                  $this->Session->delete('cat_name');
                  $this->Session->delete('cat_id');


                   //Set this now before it gets modified beow..
                   //So that user can see that the subject he/she entered is not found

                 /**
                  if(empty ($this->params->query['user_subject_id'])) {
                      $this->params->query['user_subject'] = 'All Subjects';
                      $this->params->query['user_subject_id'] = 'AllSubjects';
                  }

                  if(empty ($this->params->query['user_category_id'])) {
                     $this->params->query['user_category'] = 'All Categories';
                     $this->params->query['user_category_id'] = 'AllCategories';
                  }
                  **/
                  //debug($this->params->query); die();
              }

        //}
        /** else {
            $this->set('header_subject', $this->params->query['user_subject']);

        }
        **/

         //$result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);


      // Header Subject Search  Ends

        // $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);

         //$subjects_and_categories = $tutors_model->get_all_subjects_and_categories();

       // $return_array = $this->return_tutor_array($result);
        $return_array = $this->_get_nav_data($result, $cur_page);
        $return_array = $this->return_tutor_array($return_array);


          $this->set('tutors', $return_array);

          $this->set('zip', $this->Session->read('cur_zip_code'));
          $this->set('distance', $distance);
          $this->set('subject', $this->params->query['user_subject']);
          $this->set('header_subject', $this->params->query['user_subject']);

          $this->set('category', $this->params->query['user_category']);
          $this->set('subject_id', $this->params->query['user_subject_id']);
          $this->set('category_id', $this->params->query['user_category_id']);
          $this->set('cur_page', $cur_page);
          $this->set('kwd', $this->params->query['kwd']);

         // $this->set('subjects_and_categories', $subjects_and_categories);

        if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
        } else {
            $this->set('sortBy', h("Best Match"));
        }

       // $this->set('tutors', $result);
       } catch (NotException $e) {
         $this->redirect(array('action' => 'tutor_search_results_auth'));
       }

       //debug( $this->params->query); //die();

    } else { // if not submit
       //debug("no submit"); die();
       $distance = $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
	   $kwd = $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";

       $this->params->query['user_subject']  =  'All Subjects';    //$this->params->query['user_subject'];
       $this->params->query['user_category'] =  'All Categories'; // $this->params->query['user_category'];

       $this->params->query['user_subject_id']  =  'AllSubjects';    //$this->params->query['user_subject'];
       $this->params->query['user_category_id'] =  'AllCategories'; // $this->params->query['user_category'];

      $this->params->query['subject']  =  'All Subjects';    //$this->params->query['user_subject'];
       $this->params->query['category'] =  'All Categories'; // $this->params->query['user_category'];

      $this->params->query['subject_id']  =  'All Subjects';    //$this->params->query['user_subject'];
       $this->params->query['category_id'] =  'All Categories'; // $this->params->query['user_category'];

       if(empty($cur_zip_code)  || $cur_zip_code === "") {
         $this->Session->write('cur_zip_code', "");
       } else {
          $this->Session->write('cur_zip_code', $cur_zip_code);
       }

     //ovewrites the computed zip code in the session if user entered a zip code manually
      $this->Session->write('cur_zip_code', $cur_zip_code);
      $result = $tutors_model->get_all_tutors($cur_zip_code, $this->Session->read('cur_zip_code'), null, $distance, $kwd, $this->params->query);
      $return_array = $this->_get_nav_data($result, $cur_page);

      $i=0;
      $j=0;
      foreach ($return_array as $key => $value) {
                foreach ($return_array[$i]['StudentWatchList'] as $key1 => $value1) {

                    if( empty($return_array[$i]['StudentWatchList'][$j]) || sizeof($return_array[$i]['StudentWatchList']) <=0
                          || $return_array[$i]['StudentWatchList'][$j]['student_id'] != $this->Auth->user('id')) {

                        unset($return_array[$i]['StudentWatchList'][$j]);

                    }
                    $j++;
             }
             $i++;
             $j=0;
         }


      $this->set('tutors', $return_array);

      $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('distance', $distance);


      $this->set('subject', $this->params->query['subject']);
      $this->set('category', $this->params->query['category']);
      $this->set('header_subject', $this->params->query['user_subject']);


      $this->set('subject_id', $this->params->query['subject_id']);
      $this->set('category_id', $this->params->query['category_id']);

      /**$this->set('subject_id', "AllSubjects");
      $this->set('category_id', "AllCategories");
      **/
      $this->set('cur_page', $cur_page);
      $this->set('kwd', $this->params->query['kwd']);

      $this->set('gender', 0);
      $this->set('bg_checked', 0);
      $this->set('amount_min_rate', 10);
      $this->set('amount_max_rate', 250);
      $this->set('min_age', 18);
      $this->set('max_age', 250);
      $this->set('is_advanced', 0);


      if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
      } else {
            $this->set('sortBy', "Best Match");
      }

    }



    //debug($this->params->query); die();
    if((!empty($this->params->query['user_subject']) && !empty($this->params->query['user_subject_id'])) &&
    (!empty($this->params->query['user_category']) && !empty($this->params->query['user_category_id']))) {

         if(($this->params->query['user_subject_id'] === self::SUBJECT_ID_100 || $this->params->query['user_subject_id'] === self::SUBJECT_ID_200)
             && ($this->params->query['user_category_id'] === 'AllCategories' || $this->params->query['user_category_id'] === '400')
             ){
                // debug( $this->params->query); die();
                  $subjects_array = $tutors_model->get_all_subjects();
                  $subjects_array = $this->flatten($subjects_array, '');
                  /**
                  $return_array = array();
                  foreach ($subjects_array as $key1 => $value1) {

                               $return_array[]  =  implode('(int) 0 =>', $value1);
                  }

                  $subjects_array = $return_array;
                  **/
                  asort($subjects_array);
                  $subjects_array = $first_sub = array(self::SUBJECT_ID_100 => 'All Subjects') + $subjects_array;
                  Configure::write('popularsubjects',$subjects_array);
                   $subjects_array = $subjects_array;
              Configure::write('ddsubjects',$subjects_array);

            } else if(!in_array($this->params->query['user_subject_id'], array(self::SUBJECT_ID_100, self::SUBJECT_ID_200, self::SUBJECT_ID_300))) {
                           //debug("2");
                          // debug($this->params->query); //die();
                     if(!empty($this->params->query['user_category_id']) &&
                         $this->params->query['user_category_id'] != 'AllCategories') {
                            //debug($this->params->query['category']); die();
                          //  debug( $this->params->query); die();
                         $subjects_array = $tutors_model->get_all_subjects($this->params->query['user_category_id']);
                         $subjects_array = $this->flatten($subjects_array, '');
                         //$subjects_array = $tutors_model->get_all_subjects();
                         /**
                          $return_array = array();
                               foreach ($subjects_array as $key1 => $value1) {

                                       $return_array[]  =  implode('(int) 0 =>', $value1);
                                }


                       $subjects_array = $return_array;
                       **/
                        // debug($subjects_array); die();
                        asort($subjects_array);
                         $subject_id = $this->params->query['user_subject_id'];
                         $subject =    $this->params->query['user_subject'];

                         $subjects_array =  array($subject_id => $subject) + array(self::SUBJECT_ID_100 => 'All Subjects') +  $subjects_array;
                         Configure::write('popularsubjects',$subjects_array);
                          $subjects_array = $subjects_array;
              Configure::write('ddsubjects',$subjects_array);

                     } else if(!empty($this->params->query['user_category_id']) &&
                         $this->params->query['user_category_id'] === 'AllCategories') {
                         $subjects_array = $tutors_model->get_all_subjects();
                         $subjects_array = $this->flatten($subjects_array, '');
                       //  debug( $this->params->query); die();
                       /**
                         $return_array = array();
                         foreach ($subjects_array as $key1 => $value1) {

                                           $return_array[]  =  implode('(int) 0 =>', $value1);
                         }

                         $subjects_array = $return_array;
                         **/
                         asort($subjects_array);
                         $subject_id = $this->params->query['user_subject_id'];
                         $subject =    $this->params->query['user_subject'];

                         $subjects_array =  array($subject_id => $subject) +  array(self::SUBJECT_ID_100 => 'All Subjects') +  $subjects_array;
                         Configure::write('popularsubjects',$subjects_array);
                          $subjects_array = $subjects_array;
              Configure::write('ddsubjects',$subjects_array);
                     }

                     // $this->set('category', $this->params->query['category_id']);
            }else if(($this->params->query['user_subject_id'] === self::SUBJECT_ID_100 ||
                       $this->params->query['user_subject_id'] === self::SUBJECT_ID_200) ) {
                       //debug("3");
                      // debug( $this->params->query); die();
                         if($this->params->query['user_category_id'] != 'AllCategories' &&
                                $this->params->query['user_category_id'] != '400') {
                            // debug( $this->params->query); //die();
                              $subjects_array = $tutors_model->get_all_subjects($this->params->query['category_id']);
                              $subjects_array = $this->flatten($subjects_array, '');
                              $subject_id = $this->params->query['user_subject_id'];
                              $subject =    $this->params->query['user_subject'];

                           //  debug($this->params->query['user_category_id']);
                             //debug($this->params->query['user_category']);  //die();
                             /**
                              $return_array = array();
                                   foreach ($subjects_array as $key1 => $value1) {

                                           $return_array[]  =  implode('(int) 0 =>', $value1);
                                    }


                              $subjects_array = $return_array;
                              **/
                              asort($subjects_array);
                              $subjects_array =  array($subject_id => $subject) + array(self::SUBJECT_ID_100 => 'All Subjects') +  $subjects_array;
                              Configure::write('popularsubjects',$subjects_array);
                               $subjects_array = $subjects_array;
              Configure::write('ddsubjects',$subjects_array);
                         }


                  }
     }
    //Today end
    //debug($this->params->query); die();
      $update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 0;
	  $this->set('update_agent', $update_agent);

	  $agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
	  $this->set('agent_id', $agent_id);

      $agent_name= !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
	  $this->set('agent_name', $agent_name);

       $id = !empty($this->params->query['id']) ? $this->params->query['id'] : 0;
       $this->set('id', $id);

       $search_agents = $this->getAllSearchAgents($this->Auth->user('id'));
       $agent_count =   count($search_agents) ;
       $this->Session->write('agent_count', $agent_count);
       $this->set('search_agents', $search_agents);

       $watch_list_1 = $this->retreiveWatchList($this->Auth->user('id'));
       $watch_list = $tutors_model->retreiveWatchList($this->Auth->user('id'));
       $this->set('watch_lists', $watch_list);
       //have to do this in order to be able to cleanly check the size of the watch List w/o
       //the Tutor array it belongs to
       $this->set('watch_list_1', $watch_list_1);

       $this->Session->delete('agent_name');
       $this->Session->delete('agent_id');
       $this->Session->delete('id');

      }

      //This is for the header sujects drop down fill
     Configure::write('allsubjects',$this->get_all_subjects());
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

  protected function getAllSearchAgents($id) {

     $search_agents = null; //$search_agent_model;
     $search_agents  = $this->{$this->modelClass}->findSearchAgents($id);

      return $search_agents;

  }

  protected function retreiveWatchList($id) {

     $watch_list = null; //$search_agent_model;
     $watch_list  = $this->{$this->modelClass}->retreiveWatchList($id);

      return $watch_list;

  }
  protected function _get_nav_data($result_array, $cur_page){
    //debug($result_array); die();
    $posts_per_page = 15 ;
    $return_value = array();
    $total_post_count = sizeof($result_array);
    //debug($total_post_count); die();
    $total_page_count = ceil($total_post_count / $posts_per_page);

    //rearrange the array so the indexing below is right
	//debug($result_array);
    if(!empty($result_array)) {
        $result_array = array_values($result_array);
     }
  //  debug($result_array); die();
    $start_page = $cur_page - 4;

    if($start_page + 8 > $total_page_count){
      $start_page = $total_page_count - 8;
    }

    if($start_page <= 0){
      $start_page = 1;
    }

    $end_page = $start_page + 8;
    if($end_page > $total_page_count){
      $end_page = $total_page_count;
    }
     //debug($end_page); die();

    for ($i=0; $i < $posts_per_page; $i++) {
      $print_num = $i + ($cur_page - 1) * $posts_per_page;
     // $print_num = sizeof($result_array);
       //debug($print_num);
      if($print_num <= $total_post_count){
        if(!empty($result_array[$print_num])){
          $return_value[] = $result_array[$print_num];
          //debug($return_value);
        }
      }
    } //die();

  $this->set('total_post_count', $total_post_count);
  $this->set('posts_per_page', $posts_per_page);
  $this->set('cur_page', $cur_page);
  $this->set('start_page', $start_page);
  $this->set('end_page', $end_page);
  $this->set('total_page_count', $total_page_count);

    return $return_value;
  }


public function tutorsearchresultsauthwithbootstrapmin() {
               $this->layout='student';
   }


   public function safetytips() {
               $this->layout='student';
   }

public function close_job_post($id=null, $job_id=null) {

     $this->layout='student';
     //debug( $this->request->data); die();

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }
   // debug("hey"); die();
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }

     if ( empty($job_id) || !($data = $this->{$this->modelClass}->StudentJobPost->find(
                            'first', array('conditions' => array('StudentJobPost.job_id' => $job_id)))))
    {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'tutor_search_results_auth'));
     }

     if ($data['StudentJobPost']['id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }

      $this->request->data['StudentJobPost']['closed'] = 1;
    if($this->{$this->modelClass}->StudentJobPost->saveJobPost($id, $this->request->data)) {


        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Job has been successfully closed and will no longer appear on Job Search Results.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'my_job_posts'));

     } else {

         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'deleted failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
     }

}
public function post_job() {
    $id = null;
    $this->layout='student';
    $tutors_model = new Tutor();

    //debug($this->Session->read('exp_result')); die();

 if($this->request->is('post') || $this->request->is('ajax')) {
     $id = null;
     //debug($this->request->data); die();
	     if (!empty($this->request->data)) {
	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
			          $this->request->data['StudentJobPost']['student_id'] = $this->request->data[$this->modelClass]['id'];


   					  if (!empty($id) || ($data = $this->{$this->modelClass}->StudentJobPost->find(
                            'first', array(
                            'conditions' => array(
                                'StudentJobPost.student_id' => $this->Auth->user('id'),
                                'StudentJobPost.id'  => $id)))) )
                     {

                           //Blackhole Request
                            throw new NotFoundException(__('Job Description Must be a New One'));

                     }




                     //date_default_timezone_set('America/New_York');  //replaced by below on 7/29/16
                    //$tz = Configure::read('Config.timezone');
                     //debug($tz); die();
                     //date_default_timezone_set($tz);

                     if ($this->Session->check('user_tz'))  {
                         $tz = $this->Session->read('user_tz');
                        // debug($tz); //die();
                         $this->set_timezone($tz);

                     } else {
                      date_default_timezone_set('America/New_York');
                     // $tz = Configure::read('Config.timezone');
                     }



                     $post_date = date('Y-m-d H:i:s');
                     $exp_date = date('Y-m-d H:i:s', strtotime($post_date. ' + 30 days'));

                     $this->{$this->modelClass}->StudentJobPost->set(array(
                                  'job_description' =>  $this->request->data['StudentJobPost']['job_description'],
                                  'job_category' =>     $this->request->data['StudentJobPost']['job_category'],
                                  'job_subject' =>      $this->request->data['StudentJobPost']['job_subject'],
                                  'job_title' =>        $this->request->data['StudentJobPost']['job_title'],
                                  'job_category_id' =>  $this->request->data['StudentJobPost']['job_category_id'],
                                  'job_subject_id' =>   $this->request->data['StudentJobPost']['job_subject_id'],

                         ));

                        // debug("test"); die();
                        // $this->{$this->modelClass}->StudentJobPost->create();
                       // debug($this->request->data); die();

                      if($this->{$this->modelClass}->StudentJobPost->validates(array('fieldList' => array(
                                                            'job_description',
					                                        'job_category',
                                                            'job_subject',
                                                            'job_title',
                                                            'job_category_id',
                                                            'job_subject_id' ))))
                      {

                        $job_id = uniqid(rand(), true);
                        $result = String::tokenize($job_id, '.');

                        $job_state = $this->Session->read('student_state');
                        $job_city = $this->Session->read('student_city');
                        $job_zip_code = $this->Session->read('student_zip_code');

                        $first_name = $this->Session->read('username');
                        $last_name = substr($this->Session->read('lastname'),0,1);
                        $student_name = $first_name.' '.$last_name;
                         // debug($student_name); die();
                        $job_id  = $job_state.$result[1];
                       // debug($job_id); die();

                        $this->request->data['StudentJobPost']['verified'] = 0;
                        $this->request->data['StudentJobPost']['closed'] = 0;
                        $this->request->data['StudentJobPost']['post_date'] = $post_date;
                        $this->request->data['StudentJobPost']['exp_date'] = $exp_date;

                        $this->request->data['StudentJobPost']['student_name'] = $student_name;
                        $this->request->data['StudentJobPost']['job_id'] = $job_id;
                        $this->request->data['StudentJobPost']['job_city'] = $job_city;
                        $this->request->data['StudentJobPost']['job_state'] = $job_state;
                        $this->request->data['StudentJobPost']['job_zip_code'] = $job_zip_code;


                       // $this->request->data['StudentJobPost']['created'] = date('Y-m-d H:i:s');
                        $this->{$this->modelClass}->StudentJobPost->create();



					  if($this->{$this->modelClass}->StudentJobPost->saveJobPost($id, $this->request->data))
					   {
						/**
                         * $this->Session->setFlash
									(
												sprintf(__d('users', 'Your Job Post is Successfully Saved and Pending Review. You will be notified when approved in the next 10 minutes!!')),
											   'default',
												array('class' => 'alert alert-success')
									 );
                            **/
                            if($this->Session->check('tutor_request')) {
                                $this->Session->write('tutor_request', 'success');
                                return $this->redirect(array('action' => 'welcome'));
                            }
                            $this->set('success', true);
                            $this->Session->setFlash('Your Job Post is Successfully Saved and Pending Review. You will be notified when approved in the next 10 minutes!!', 'custom_msg');


					  }
                  } else {

                                //$this->Session->write('tutor_request', false);
                            $this->Session->write('validation_error_array', $this->{$this->modelClass}->StudentJobPost->validationErrors);

                                if($this->Session->check('tutor_request')) {
                                    $this->Session->write('tutor_request', 'failure');
                                    return $this->redirect(array('action' => 'welcome'));
                                }
                                $this->set('success', false);
                                $this->Session->setFlash('Job Post failed!! Please correct all Errors', 'custom_msg');

                  }
            }
      }

        $student_zip_code = $this->Session->read('student_zip_code');

        //This the Zip Code Student uses when registering.
        //May or may not be the same as the Zip Code from Which he is currently at
        $result = $tutors_model->find_city_ByZipCode($student_zip_code);

        $student_city = $result['city'];
        $student_state = $result['state'];

        $this->Session->write('student_city', $student_city);
        $this->Session->write('student_state', $student_state);


}

public function set_timezone($user_tz) {

   if(!empty($user_tz)){
             if($user_tz === '-04:00') {
                date_default_timezone_set('America/New_York');
                Configure::write('Config.timezone', 'America/New_York');
             } else if($user_tz === '-05:00') {
                date_default_timezone_set('America/Chicago');
                Configure::write('Config.timezone', 'America/Chicago');
             }else  if($user_tz === '-07:00') {
                date_default_timezone_set('America/Los_Angeles');
                Configure::write('Config.timezone', 'America/Los_Angeles');
             }else if($user_tz === '-06:00') {
                date_default_timezone_set('America/Phoenix');
                Configure::write('Config.timezone', 'America/Phoenix');
             }
	   }
}

public function post_job_edit() {
    $id = null;
  $this->layout='student';
   $tutors_model = new Tutor();
 if($this->request->is('post') || $this->request->is('ajax')) {
     $id = null;

	     if (!empty($this->request->data)) {
	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
			          $this->request->data['StudentJobPost']['student_id'] = $this->request->data[$this->modelClass]['id'];


   					  if (!empty($id) || ($data = $this->{$this->modelClass}->StudentJobPost->find(
                            'first', array(
                            'conditions' => array(
                                'StudentJobPost.student_id' => $this->Auth->user('id'),
                                'StudentJobPost.id'  => $id)))) )
                     {

                           //Blackhole Request
                            throw new NotFoundException(__('Job Description Must be a New One'));

                     }
                     $post_date = date('Y-m-d');
                     $exp_date = date('Y-m-d', strtotime($post_date. ' + 30 days'));


                      $this->{$this->modelClass}->StudentJobPost->set(array(
                                  'job_description' => $this->request->data['StudentJobPost']['job_description'],
                                  'job_category' => $this->request->data['StudentJobPost']['job_category'],
                                  'job_subject' =>  $this->request->data['StudentJobPost']['job_subject'],
                                  'post_date' =>    $post_date, //date('Y-m-d'), //$this->request->data['StudentJobPost']['post_date'],
                                  'exp_date' =>     $exp_date, //$this->request->data['StudentJobPost']['exp_date'],
                                  'verified' => '0'
                         ));

                         $this->{$this->modelClass}->StudentJobPost->create();
                         //$this->request->data['StudentJobPost']['created'] = date('Y-m-d H:i:s');

                      if( $this->{$this->modelClass}->StudentJobPost->validates(array('fieldList' => array(
                                                            'job_description',
					                                        'job_category',
                                                            'job_subject' ))))
                      {
					  if($this->{$this->modelClass}->StudentJobPost->saveJobPost($id, $this->request->data))
					   {
							$this->Session->setFlash
									(
												sprintf(__d('users', 'Your Job Post is Successfully Saved and Pending Review. You will be notified when approved in the next 10 minutes!!')),
											   'default',
												array('class' => 'alert alert-success')
									 );
					  }
                  }
            }
      }


}

function ajax_subjects() {

   if (!$this->request->is('ajax')) {
        //debug('Donald'); //die();
        throw new MethodNotAllowedException();
    }
      debug($this->request->data['amount_min_rate']); 
	  debug($this->request->data['amount_max_rate']);die();
	  
    $this->layout = 'ajax';
    $this->autoRender = false;

    $tutors_model = new Tutor();
    $id = $this->request->data['value'];
    $data = json_encode($tutors_model->get_all_subjects($id));
    $this->set('data',$tutors_model->get_all_subjects($id));
    return $data;
    //$this->set('data',$tutors_model->get_all_subjects($id));
    //$this->set('data', $data);
    //$this->render('/students/tutor_search_resuts_auth');

    //$this->render('/General/SerializeJson/');
    //return $data;
    //$this->render('/elements/ajax_dropdown');
}

   public function myaccount() {
              $this->layout='student';
   }

public function request_tutor() {

    $this->layout='default';

   if ($this->request->is('post')) {

       if (!empty($this->request->data)) {
            //debug($this->request->data); die();
             $tutors_model = new Tutor();

             if(!empty($this->request->data['g-recaptcha-response'])) {
                	    $response = $this->{$this->modelClass}->solveCaptcha($this->request->data['g-recaptcha-response']);
                        //debug($response); die();
                        if(!$response) {
                            $message = 'Please Resolve Captcha Error and try Again!!';
                            $this->Session->setFlash($message, 'custom_msg');
                            $this->redirect($this->referer('/'));
                        }
                     }else {
                        $message = 'Please Check the Captcha Box to prove You are NOT a ROBOT';
                        $this->Session->setFlash($message, 'custom_msg');
                        $this->redirect($this->referer('/'));
               }
               $this->{$this->modelClass}->StudentJobPost->set(array(
			                                     'job_description' =>  $this->request->data['StudentJobPost']['job_description'],
			                                     'job_category' =>     $this->request->data['StudentJobPost']['job_category'],
			                                     'job_subject' =>      $this->request->data['StudentJobPost']['job_subject'],
			                                     'job_title' =>        $this->request->data['StudentJobPost']['job_title'],
			                                     'job_category_id' =>  $this->request->data['StudentJobPost']['job_category_id'],
			                                     'job_subject_id' =>   $this->request->data['StudentJobPost']['job_subject_id'],

			                            ));

			                           // debug("test"); die();
			                           // $this->{$this->modelClass}->StudentJobPost->create();
			                          // debug($this->request->data); die();

               //we want to return student to page if for some reason, the job details he/she entered are not validated
			   if(!$this->{$this->modelClass}->StudentJobPost->validates(array('fieldList' => array(
			                                                               'job_description',
			   					                                        'job_category',
			                                                               'job_subject',
			                                                               'job_title',
			                                                               'job_category_id',
			                                                               'job_subject_id' ))))
			   {

			     $this->Session->write('validation_error_array', $this->{$this->modelClass}->StudentJobPost->validationErrors);
                // return $this->redirect(array('action' => 'welcome'));
                 $this->redirect($this->referer('/'));
			   }

             //Student is registering and requesting tutor at same time
             //Separate the Student Regitration Form from the JobPost Form
            $jobPost['StudentJobPost'] = $this->request->data['StudentJobPost'];
          //  debug($jobPost); //die();
            unset($this->request->data['StudentJobPost']);
           // debug($this->request->data); //die();
            $this->request->data['Student']['referral_source'] = $this->request->data['Student']['contactbox'];

            Configure::write('Users.role', 'student');
            Configure::write('join_via_request', 'request_tutor');
           // $this->Session->write('student_request', Configure::read('Users.role'));
            // Call add() function of Parent (Plugin::UsersController)
            //check the uniqueness of email
            $this->add();

            //Now that we/ve successfully registered user, make sure his/her job is posted
           //Automatically log user in
            if($this->Auth->login()) {


                $this->Session->write('view_layout', 'student');
                $loggedInUserType = $this->Session->read('loggedInUserType');
                $this->Session->write('loggedInUserType', 'Auth.Student');

                $this->{$this->modelClass}->id = $this->Auth->user('id');
                $this->Session->write('first_login', true);
                $this->{$this->modelClass}->saveField('last_login', date('Y-m-d H:i:s'));

                //Since the beforFilter() method is already hit by the time we get here
                //and at that time the Student info did not exist yet.. All of the user info below we normally retreive in beforeFilter()  would not have
                //been in Session.... So we have to put it in there
                $user_data = $this->{$this->modelClass}->findById($this->Auth->user('id'));
                //debug($user_data); die();
		 		if(!empty($user_data)) {
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
                    $last_name = $user_data[$this->modelClass]['last_name'];
		 		    $last_login = $user_data[$this->modelClass]['last_login'];
                    $user_zip_code = $user_data[$this->modelClass]['zip_code'];

		            $this->Session->write('username', $user_fname);
                    $this->Session->write('lastname', $last_name);
		            $this->Session->write('last_login', $last_login);
                    $this->Session->write('student_zip_code', $user_zip_code);

                    $result = $tutors_model->find_city_ByZipCode($user_zip_code);

                   	if(!empty($result)) {
                        $student_city = $result['city'];
                        $student_state = $result['state'];

                        $this->Session->write('student_city', $student_city);
                        $this->Session->write('student_state', $student_state);
                      }

                }
               // debug($user_data); die();
                //Now that we have successfully registered the student, We need to Post the Job.
                //So we will
                //swap the request data for the Job Post Data
                $this->request->data = array();
                $this->request->data['StudentJobPost'] = $jobPost['StudentJobPost'];
                $this->Session->write('tutor_request', 'tutor_request');
                //debug($this->request->data) die();
                $this->post_job();
                //return $this->redirect(array('action' => 'welcome'));
            }

            //$this->Session->write('error_array', $this->{$this->modelClass}->validationErrors);
            }
         }
   }

public function message_tutor() {

    $this->layout='default';
    // debug($tutor_id); die();
    if ($this->request->is('post')) {

       if (!empty($this->request->data)) {
             //debug($this->request->data); die();
             $this->request->data['Student'] = $this->request->data['StudentTutor'];
              unset($this->request->data['StudentTutor']);
             //debug($this->request->data); die();
             $tutors_model = new Tutor();
             if(!empty($this->request->data['g-recaptcha-response'])) {
        	    $response = $this->{$this->modelClass}->solveCaptcha($this->request->data['g-recaptcha-response']);
                //debug($response); die();
                if(!$response) {
                    $message = 'Please Resolve Captcha Error and Try Again!!';
                    $this->Session->setFlash($message, 'custom_msg');
                    $this->redirect($this->referer('/'));
                }
             }else {
                $message = 'Please Check the Captcha Box to prove You are NOT a ROBOT';
                $this->Session->setFlash($message, 'custom_msg');
                $this->redirect($this->referer('/'));
             }
             //Student is registering and messaging tutor at same time
             //Separate the Student Regitration Form from the Student Message Form

            $studentMessage['StudentMessage'] = $this->request->data['StudentTutorContact'];
          //  debug($jobPost); //die();
            unset($this->request->data['StudentTutorContact']);
           // debug($this->request->data); //die();
            $this->request->data['Student']['referral_source'] = $this->request->data['Student']['contactbox'];

            Configure::write('Users.role', 'student');
            Configure::write('join_via_request', 'message_tutor');

            $this->add();

            //Now that we've successfully registered user,
            //make sure his/her message is verified before being sent as we should not allowed the inclusion
            //of any direct contact info (email, phone or social Network profile) unless the student has a payment on file

           //Automatically log user in
            if($this->Auth->login()) {

                $this->{$this->modelClass}->id = $this->Auth->user('id');
                $this->Session->write('first_login', true);
                $this->{$this->modelClass}->saveField('last_login', date('Y-m-d H:i:s'));

                //Since the beforFilter() method is already hit by the time we get here
                //and at that time the Student info did not exist yet.. All of the user info below we normally retreive in beforeFilter()  would not have
                //been in Session.... So we have to put it in there
                $user_data = $this->{$this->modelClass}->findById($this->Auth->user('id'));
                //debug($user_data); die();

		 		if(!empty($user_data)) {
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
                    $last_name = $user_data[$this->modelClass]['last_name'];
		 		    $last_login = $user_data[$this->modelClass]['last_login'];
                    $user_zip_code = $user_data[$this->modelClass]['zip_code'];

		            $this->Session->write('username', $user_fname);
                    $this->Session->write('lastname', $last_name);
		            $this->Session->write('last_login', $last_login);
                    $this->Session->write('student_zip_code', $user_zip_code);

                    $result = $tutors_model->find_city_ByZipCode($user_zip_code);

                   	if(!empty($result)) {
                        $student_city = $result['city'];
                        $student_state = $result['state'];

                        $this->Session->write('student_city', $student_city);
                        $this->Session->write('student_state', $student_state);
                      }

                }
              // debug($user_data); die();
                //Now that we have successfully registered the student, We need to Send the Message to Tutor.
                //So we will swap the request data for the Student Message Data
                $member_id = $this->request->data['Student']['member_id'];
                $this->request->data = array();
                $this->request->data['StudentTutorContact'] = $studentMessage['StudentMessage'];
                $this->request->data['StudentTutorContact']['member_id'] =  $member_id;
                $this->request->data['StudentTutorContact']['copy_me'] = 1;
                $this->request->data['StudentTutorContact']['message_channel'] = 'tutorDetailProfile';

               //$this->Session->write('message_tutor', 'message_tutor');
               // $this->send_message();

               $this->contact_tutor();
               // return $this->redirect(array('action' => 'welcome'));
            }

            //$this->Session->write('error_array', $this->{$this->modelClass}->validationErrors);
            }
         }
   }

    public function lesson_scheduling() {
              $this->layout='student';
   }

    public function my_lessons() {
               $this->layout='student';
   }

    public function my_scheduled_lessons() {
                  $this->layout='student';
   }

   public function my_completed_lessons() {
                  $this->layout='student';
   }


    public function my_tutors() {
                  $this->layout='student';
   }

   public function tutor_search_agents() {
                  $this->layout='student';

    if($this->request->is('get')) {

         $tutor_search_agents = null; //$search_agent_model;
         $tutor_search_agents  = $this->getAllSearchAgents($this->Auth->user('id'));
        // debug($job_search_agents); die();
         $this->set('search_agents', $tutor_search_agents);
     }
   }

public function tutor_search_tools_bkup() {
    $this->layout='student';
}

public function watch_list_center_bkup() {

    $this->layout = 'student';
   }
public function watch_list_center() {

    $this->layout = 'student';

    $radiusSearch = new ZipSearch();
    $tutor = new Tutor();
    $conditions = array();
    $tutors_model = new Tutor();
    $search_agent_model = new StudentSearchAgent();
    $watchListModel = new StudentWatchList();

 if($this->request->is('get')) {
     $id = null;
     $rs = null;
     // pagination
     $posts_per_page = 8;
     $total_post_count = 0;
     $cur_page = 1;
     $start_page = 1;
     $display_page_navigation = 9;

     if(!empty($_GET['cur_page'])){
       $cur_page = $_GET['cur_page'];
     }

       $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');

	   $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
      //$result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $this->params->query['distance']);
       $result = $tutors_model->get_all_tutors($cur_zip_code, null, $this->Session->read('cur_zip_code'),  $distance, $kwd, $this->params->query);


       $watch_list_1 = $this->retreiveWatchList($this->Auth->user('id'));
       $watch_list = array();
       $return_array = array();
      // debug($watch_list_1); die();
      if(!empty($watch_list_1) && sizeof($watch_list_1) > 0) {
         if(!empty($this->params->query['kwd'])) {
           $watch_list = $tutors_model->retreiveWatchList($this->Auth->user('id'), $this->params->query['kwd']);

         } else {
            $watch_list = $tutors_model->retreiveWatchList($this->Auth->user('id'));

         }

         $this->set('watch_list_side', $watch_list);

       //debug($watch_list); die();
         $return_array = $this->_get_nav_data($watch_list, $cur_page);
         $this->set('watch_lists', $return_array);
       } else {
            $return_array = $this->_get_nav_data($watch_list, $cur_page);
            $this->set('watch_lists', $return_array);

       }

     //  debug($this->params->query['kwd']); die();
       if(!empty($this->params->query['kwd'])) {
         //debug($this->params->query['kwd']); die();
             $this->set('sortBy', $this->params->query['kwd']);
            } else {
                $this->set('sortBy', h("Most Recent"));
         }
       //have to do this in order to be able to cleanly check the size of the watch List w/o
       //the Tutor array it belongs to
       $this->set('watch_list_1', $watch_list_1);
       $this->set('zip', $this->Session->read('cur_zip_code'));
       $this->set('distance', $distance);

    }
}

public function my_job_posts() {

    $this->layout='student';

    if($this->request->is('get')) {
         $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
         $posts_per_page = 7;
         $total_post_count = 0;
         $cur_page = 1;
         $start_page = 1;
         $display_page_navigation = 9;

         if(!empty($_GET['cur_page'])){
          $cur_page = $_GET['cur_page'];
         }

         $subject_model = new Subject();
         $this->params->query['subject'] = !empty($this->params->query['job_subject']) ? $this->params->query['job_subject'] :  self::SUBJECT_ID_100; //"100";

             //debug($this->params->query['subject']);  die();
            // debug($this->params->query['subject_id']); die();
             if(empty($this->params->query['job_subject_id'] ) &&
                !empty($this->params->query['job_subject'])) {
                $this->params->query['subject_id'] = $subject_model->get_subject_id($this->params->query['subject']);
             } else {
                $this->params->query['subject_id'] = !empty($this->params->query['job_subject_id']) ? $this->params->query['job_subject_id'] :  self::SUBJECT_ID_100; //"100";
                //$this->params->query['subject'] = !empty($this->params->query['job_subject']) ? $this->params->query['job_subject'] : self::SUBJECT_ID_100; //"100";
             }

             $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;
             $this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";

            $conditions_for_search = array();
            $conditions_for_search['subject'] = $this->params->query['subject']; //!empty($this->params->query['subject']) ? $this->params->query['subject'] : "All Subjects";
            $conditions_for_search['subject_id'] = $this->params->query['subject_id']; //!empty($this->params->query['subject']) ? $this->params->query['subject'] : "All Subjects";


         $my_job_posts= null;
         $job_post_subjects = array();

         //This call will filter for just the Jobs applied for in the Specific Subject as specified in Search Conditions
         $my_job_posts  = $this->findJobPosts($this->Auth->user('id'), $conditions_for_search, $this->params->query['kwd']);
        // debug($my_job_posts); die();
         //make a second call with empty Search conditions. So that All the Job Applied for are brought back
         //for display in the "Most Recent Job Apps" section" and also make sure the drop down shows all Subjects applied for
         $my_job_posts_all  = $this->findJobPosts($this->Auth->user('id'),"",  $this->params->query['kwd']);

       // debug($my_job_posts); die();
        if(!empty($my_job_posts_all)) {
            $i=0;
          foreach($my_job_posts_all as $key => $value) {
                 $job_post_subjects[] = array($value['job_id'] => h($value['job_subject']));
                 $i++;
          }
        }

        $return_array = array();

        $i=0;
        if(!empty($job_post_subjects)) {
          foreach ($job_post_subjects as $key1 => $value1) {
                           $return_array[]  =  implode('(int) 0 =>', $value1);
                           $i++;
             }
           }

       // die();
       // debug($return_array); die();
         $job_post_subjects = $return_array;
        //$job_post_subjects = array(self::SUBJECT_ID_100 => 'All') + $job_post_subjects;
        //Remove duplicate elements from Array so drop down only have unique subjects
         //$job_post_subjects = array_unique($job_post_subjects);
         sort($job_post_subjects);

         //debug($job_post_subjects);
         //Had to do this b/c for whatever reason the optin default behaviour does not work on View Screen. Weired!!!

       if(!empty($this->params->query['job_subject']) && $this->params->query['job_subject'] != 'All') {

         $subject_araay = array('0' => $this->params->query['job_subject']);
         $job_post_subjects =  array(self::SUBJECT_ID_100 => 'All') + $job_post_subjects;

         $job_post_subjects = array_merge($subject_araay, $job_post_subjects);

        } else {
            $job_post_subjects = array(self::SUBJECT_ID_100 => 'All') + $job_post_subjects;

        }
         $job_post_subjects = array_unique($job_post_subjects);
        // sort($job_post_subjects);
       // debug($job_post_subjects); die();
        Configure::write('job_app_subjects',$job_post_subjects);

         $this->set('recent_job_posts', h($my_job_posts_all));

         $my_job_posts = $this->_get_nav_data($my_job_posts, $cur_page);
         $this->set('job_posts', $my_job_posts);

          if(!empty($this->params->query['kwd'])) {
             $this->set('sortBy', $this->params->query['kwd']);
            } else {
                $this->set('sortBy', h("Most Recent"));
         }
         //debug($this->params->query['job_subject']); die();
         if(!empty($this->params->query['job_subject']) && $this->params->query['job_subject'] != 'All') {
             $this->set('job_subject', $this->params->query['job_subject']);
         }
     }
}

protected function findJobPosts($id, $conditions_for_search=array(), $kwd) {

   	 $this->layout='student';
     $my_job_posts = null;
     $my_job_posts  = $this->{$this->modelClass}->findJobPosts($id, $conditions_for_search, $kwd);

     return $my_job_posts;

    //query the student_job_posts table and
}


    public function my_pending_feedback() {
           $this->layout='student';
   }


   public function myfeedback() {
         $this->layout='student';
   }

    public function student_review_of_daraji() {
            $this->layout='student';
   }


   public function student_review_of_tutor() {
               $this->layout='student';
   }

     public function notes_on_tutor() {
                  $this->layout='student';
   }

   public function account_confirm() {
         $this->layout='default';
   }
   
/**
public function isAuthorized($user) {

   if($this->params['controller']=='tutors') {
   $this->Session->setFlash
      				(
      				  sprintf(__d('users', 'You are nosy dude.')),
      				   'default',
      					array('class' => 'alert alert-warning')
   					);
    }
    return false;

}
**/

/**
 The following methods deal with user account settings.
* Must be top notch security
**/

public function change_email() {
   $this->layout = 'student';
   $this->changeEmail();

}
public function change_password() {
   $this->layout = 'student';
   $this->changePassword();

}
public function manage_profile() {
   $this->layout='student';

        if($this->request->is('post')) {
        $id = null;

   	     if (!empty($this->request->data)) {
   	    // debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['StudentProfile']['student_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['StudentProfile']['id']))
   			                $id = $this->request->data['StudentProfile']['id'];     //the Pk of Associated model (StudentProfile)
                        //debug($id); die();


   					  if (empty($id)  || !($data = $this->{$this->modelClass}->StudentProfile->find(
                            'first', array(
                            'conditions' => array(
                                'StudentProfile.student_id' => $this->Auth->user('id'),
                                'StudentProfile.id'  => $id)))))
                     {

                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Profile'));

                     }
                      if ($data['StudentProfile']['id'] != $id) {
                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Record'));
                     }
                      $this->{$this->modelClass}->StudentProfile->set(array(
                                  'gender' => $this->request->data['StudentProfile']['gender'],
                                  'education' => $this->request->data['StudentProfile']['education'],
                                  //'degree' => $this->request->data['StudentProfile']['degree'],
                                  'school' => $this->request->data['StudentProfile']['school'],

                                  'address_1' => $this->request->data['StudentProfile']['address_1'],
                                  'address_2' => $this->request->data['StudentProfile']['address_2'],
                                  'city' => $this->request->data['StudentProfile']['city'],
                                  'state' => $this->request->data['StudentProfile']['state'],
                                  'state_abbr' => $this->request->data['StudentProfile']['state'],
                                  'zip_code' => $this->request->data['StudentProfile']['zip_code'],

                                  //'maddress_1' => $this->request->data['StudentProfile']['maddress_1'],
                                  //'maddress_2' => $this->request->data['StudentProfile']['maddress_2'],
                                  //'mcity' => $this->request->data['StudentProfile']['mcity'],
                                  //'mstate' => $this->request->data['StudentProfile']['mstate'],
                                  //'mzip_code' => $this->request->data['StudentProfile']['mzip_code'],

                                  'primary_phone' => $this->request->data['StudentProfile']['primary_phone'],
                                  'secondary_phone' => $this->request->data['StudentProfile']['secondary_phone'],
                                  'pphone_type' => $this->request->data['StudentProfile']['pphone_type'],
                                  'sphone_type' => $this->request->data['StudentProfile']['sphone_type']


                         ));

           if( $this->{$this->modelClass}->StudentProfile->validates(array('fieldList' => array(
                                                            'gender',
					                                        'education',
                                                            //'degree',
                                                            'school',
                                                            'address_1',
                                                            'city',
                                                            'state',
                                                            'zip_code',
                                                            //'maddress_1',
                                                            //'mcity',
                                                            //'mstate',
                                                            //'mzip_code',
                                                            'primary_phone',
                                                            'pphone_type'

                                                            ))))
                  {

                         $status = $this->request->data['StudentProfile']['basic_profile_status'];
                         if(!$status ) {

                            $this->request->data['StudentProfile']['basic_profile_status'] = 1;
                            //$this->request->data['StudentProfile']['profile_status_count']++;
                       }

                     if($this->{$this->modelClass}->StudentProfile->saveProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
   					  }
                  } else {

                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );

                    }
               }
         }


             //set the primary key of preference table in the view and send it back as a hidden field

   	    $stProfileModel  = $this->{$this->modelClass}->StudentProfile->find('first', array(
		 		   			  		  		  	'conditions' => array('StudentProfile.student_id' => $this->Auth->user('id'))
                     ));
        /**
        $tutor_model  = $this->{$this->modelClass}->find('first', array(
		 		   			  		  		  	'conditions' => array('Student.id' => $this->Auth->user('id'))
                     ));
        **/
        //debug($tutor_model); die();
        if(!empty($tutor_model)) {
            $this->set('zip',   h($tutor_model['Student']['zip_code']));
        }
        //debug($stProfileModel); die();
          $this->set('fn',     h($this->Session->read('username')));
          $this->set('ln',     h($this->Session->read('lastname')));
   	      if(!empty($stProfileModel)) {
   	                //debug($stProfileModel); die();
   	                $this->set('prpk',   h($stProfileModel['StudentProfile']['id']));
                    $this->set('gender', h($stProfileModel['StudentProfile']['gender']));
   	                $this->set('ed',     h($stProfileModel['StudentProfile']['education']));
   	                $this->set('school', h($stProfileModel['StudentProfile']['school']));
   	                $this->set('add1',   h($stProfileModel['StudentProfile']['address_1']));
   	                $this->set('add2',   h($stProfileModel['StudentProfile']['address_2']));
   	                $this->set('city',   h($stProfileModel['StudentProfile']['city']));
   	                $this->set('st',     h($stProfileModel['StudentProfile']['state']));
   	                $this->set('zip',    h($stProfileModel['StudentProfile']['zip_code']));
   	                $this->set('pp',     h($stProfileModel['StudentProfile']['primary_phone']));
   	                $this->set('sp',     h($stProfileModel['StudentProfile']['secondary_phone']));
   	                $this->set('mhop',   h($stProfileModel['StudentProfile']['pphone_type']));
   	                $this->set('mhos',   h($stProfileModel['StudentProfile']['sphone_type']));
                    $this->set('bps',    h($stProfileModel['StudentProfile']['basic_profile_status']));
             }

}


public function manage_preferences() {

     $this->layout='student';
     if($this->request->is('post')) {
     $id = null;

	     if (!empty($this->request->data)) {
	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
			          $this->request->data['StudentPreference']['student_id'] = $this->request->data[$this->modelClass]['id'];

			          if(!empty($this->request->data['StudentPreference']['id']))
			                $id = $this->request->data['StudentPreference']['id'];     //the Pk of Associated model (StudentPreference)
                           // debug($id); debug($this->modelClass); //die();


   					 if (!($data = $this->{$this->modelClass}->StudentPreference->find(
                            'first', array(
                            'conditions' => array(
                                'StudentPreference.student_id' => $this->Auth->user('id'),
                                'StudentPreference.id'  => $id))))
                        ) {

                                  throw new NotFoundException(__('Invalid Profile'));
                      }

                    if ($data['StudentPreference']['id'] != $id) {
                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Record'));
                     }

              $this->{$this->modelClass}->StudentPreference->set(array(
                                  'new_features' => $this->request->data['StudentPreference']['new_features'],
                                  'promos' => $this->request->data['StudentPreference']['promos'],
                                  'daily_digest' => $this->request->data['StudentPreference']['daily_digest'],
                                  'new_tutor' => $this->request->data['StudentPreference']['new_tutor'],
                                  'lesson_review' => $this->request->data['StudentPreference']['lesson_review'],
                                  'sms_alerts' => $this->request->data['StudentPreference']['sms_alerts'],
                                  'phone_number' => $this->request->data['StudentPreference']['phone_number'],
                                  'carrier' => $this->request->data['StudentPreference']['carrier']

                                  ));

					  if($this->{$this->modelClass}->StudentPreference->savePreferences($id, $this->request->data))
					   {
							$this->Session->setFlash
									(
												sprintf(__d('users', 'Email/Sms Preferences successfully saved.')),
											   'default',
												array('class' => 'alert alert-success')
									 );
					  }
            }
      }


          //set the primary key of preference table in the view and send it back as a hidden field
	      //$stPrefModel = $this->{$this->modelClass}->StudentPreference->find
	              //    (
	                 //   'first',
	                  //   array('field' => 'student_id',
	                  //  'value' => $this->Auth->user('id')
	                // ));
	      $stPrefModel = $this->{$this->modelClass}->StudentPreference->find('first', array(
		   			  		  		  	'conditions' => array('StudentPreference.student_id' => $this->Auth->user('id'))
                     ));

	      if(!empty($stPrefModel)) {
	                //debug($stPrefModel); die();
	                $this->set('ppk',  h($stPrefModel['StudentPreference']['id']));
	                $this->set('nf',   h($stPrefModel['StudentPreference']['new_features']));
	                $this->set('pmos', h($stPrefModel['StudentPreference']['promos']));
	                $this->set('dd',   h($stPrefModel['StudentPreference']['daily_digest']));
	                $this->set('nt',   h($stPrefModel['StudentPreference']['new_tutor']));
	                $this->set('lr',   h($stPrefModel['StudentPreference']['lesson_review']));
	                $this->set('sa',   h($stPrefModel['StudentPreference']['sms_alerts']));
	                $this->set('pn',   h($stPrefModel['StudentPreference']['phone_number']));
	                $this->set('cr',   h($stPrefModel['StudentPreference']['carrier']));
          }

}


public function update_entry($datastring=null) {

     if (!$this->request->is('ajax')) {
        //debug('Donald'); //die();
        throw new MethodNotAllowedException();
    }

    $this->layout = 'ajax';
    $this->autoRender = false;

      $data = $this->request->data;
      //debug($data); die();
     // debug($data['editAct']);
      if(!empty($this->request->data['id']))
            $id = $this->request->data['id'];     //the Pk of Associated model (StudentProfile)

       if (($data = $this->{$this->modelClass}->StudentProfile->find(
                            'first', array(
                            'conditions' => array(
                                'StudentProfile.student_id' => $this->Auth->user('id'),
                                'StudentProfile.id'  => $id)))) && $data['StudentProfile']['id'] != $id)
                     {

                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Profile'));

                     }
switch ($this->request->data['editAct']) {
    case 'editName':
    // debug($this->request->data); die();
         $this->{$this->modelClass}->StudentProfile->set(array(
                 'first_name' => $this->request->data['first_name'],
                 'last_name' => $this->request->data['last_name'],
         ));

          $this->{$this->modelClass}->set(array(
                 'first_name' => $this->request->data['first_name'],
                 'last_name' => $this->request->data['last_name'],
         ));

          if(!empty($this->request->data) )  {
            // debug("hereee");
            //debug($this->request->data['first_name']); die();
            if( $this->{$this->modelClass}->StudentProfile->validates(
                      array('fieldList' => array('first_name','last_name'))))
             {
               // debug("here"); die();
             $this->{$this->modelClass}->StudentProfile->id = $this->request->data['id'];
             $this->{$this->modelClass}->StudentProfile->saveField('first_name', $this->request->data['first_name']);
             $this->{$this->modelClass}->StudentProfile->saveField('last_name', $this->request->data['last_name']);

             $this->{$this->modelClass}->id = $this->Auth->user('id'); //$this->request->data['uid'];
             $this->{$this->modelClass}->saveField('first_name', $this->request->data['first_name']);
             $this->{$this->modelClass}->saveField('last_name', $this->request->data['last_name']);


             } else {
                      throw new NotFoundException(__('Invalid Request'));

             }

      }
      break;
     case 'editEducation' :
     //debug('here now'); die();
     // debug($this->request->data); die();
     $this->{$this->modelClass}->StudentProfile->set(array(
             'education' => $this->request->data['ed'],
             'school' => $this->request->data['school']
     ));

      if(!empty($this->request->data) )  {

        if( $this->{$this->modelClass}->StudentProfile->validates(
                  array('fieldList' => array('education','school' ))))
         {
              //debug($this->request->data); die();
             $this->{$this->modelClass}->StudentProfile->id = $this->request->data['id'];
             $this->{$this->modelClass}->StudentProfile->saveField('education', $this->request->data['ed']);
             //$this->{$this->modelClass}->StudentProfile->saveField('degree', $this->request->data['degree']);
             $this->{$this->modelClass}->StudentProfile->saveField('school', $this->request->data['school']);

               //debug($this->request->data['degree']);

                 //$this->{$this->modelClass}->StudentProfile->updateAll(
                    //  array('education' => $this->request->data['ed'],
                    //  'school' => $this->request->data['school']),
                    //  array('tutor_id' => $this->Auth->user('id')));

         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->StudentProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->StudentProfile->validationErrors[$this->request->data['datum']]);
         }

      }
      break;
      case 'editCadd' :
      //debug($this->request->data); die();
     $this->{$this->modelClass}->StudentProfile->set(array(
             'address_1' => $this->request->data['addr1'],
             'address_2' => $this->request->data['addr2'],
             'city' => $this->request->data['city'],
             'state' => $this->request->data['state'],
             'state_abbr' => $this->request->data['state'],
             'zip' => $this->request->data['zipCode'],

     ));
    //debug($this->request->data); die();
      if(!empty($this->request->data) )  {

        if( $this->{$this->modelClass}->StudentProfile->validates(
                  array('fieldList' => array('address_1','city', 'state', 'zip_code' ))))
         {

             $this->{$this->modelClass}->StudentProfile->id = $this->request->data['id'];
             $this->{$this->modelClass}->StudentProfile->saveField('address_1', $this->request->data['addr1']);
             $this->{$this->modelClass}->StudentProfile->saveField('address_2', $this->request->data['addr2']);
             $this->{$this->modelClass}->StudentProfile->saveField('city', $this->request->data['city']);
             $this->{$this->modelClass}->StudentProfile->saveField('state', $this->request->data['state']);
             $this->{$this->modelClass}->StudentProfile->saveField('state_abbr', $this->request->data['state']);
             $this->{$this->modelClass}->StudentProfile->saveField('zip_code', $this->request->data['zipCode']);



         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->StudentProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->StudentProfile->validationErrors[$this->request->data['datum']]);
         }

      }
      break;

   case 'editCinfo' :
     //debug($this->request->data); die();
     //debug('In Cinfo'); die();
     $this->{$this->modelClass}->StudentProfile->set(array(
             'primary_phone' => $this->request->data['pphone'],
             'pphone_type' => $this->request->data['pphoneType'],
             'secondary_phone' => $this->request->data['sphone'],
             'sphone_type' => $this->request->data['sphoneType']


     ));

      if(!empty($this->request->data) )  {
        //debug($this->request->data);
        if( $this->{$this->modelClass}->StudentProfile->validates(
                  array('fieldList' => array('primary_phone', 'pphone_type')))) //, 'secondary_phone', 'sphone_type'))))
         {

               //  debug('validated'); die();
             $this->{$this->modelClass}->StudentProfile->id = $this->request->data['id'];
             $this->{$this->modelClass}->StudentProfile->saveField('primary_phone', $this->request->data['pphone']);
             $this->{$this->modelClass}->StudentProfile->saveField('pphone_type', $this->request->data['pphoneType']);
             $this->{$this->modelClass}->StudentProfile->saveField('secondary_phone', $this->request->data['sphone']);
             $this->{$this->modelClass}->StudentProfile->saveField('sphone_type', $this->request->data['sphoneType']);

                // $this->{$this->modelClass}->StudentProfile->updateAll(
                    //  array('StudentProfile.primary_phone' => $this->request->data['pphone'],
                      //'StudentProfile.pphone_type' => $this->request->data['pphoneType'],
                      //'StudentProfile.secondary_phone' => $this->request->data['sphone'],
                      //'StudentProfile.sphone_type' => $this->request->data['sphoneType']),
                      //array('StudentProfile.tutor_id' => $this->Auth->user('id')));

         } else {
                  throw new NotFoundException(__('Invalid Request'));

         }

      }
      break;


  }

}

public function edit_watch_list($id=null) {

 if($this->request->is('post') || $this->request->is('ajax')) {
    //debug($id); die();
	 if(!empty($this->request->data))
	 {
	    //debug($this->request->data); die();
       // debug($this->request->data['StudentWatchList']['editYourNotes']);
       // debug($this->request->data['StudentWatchList']['editNotes']); die();
       // $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
       // $this->request->data['StudentWatchList']['student_id'] = $this->request->data[$this->modelClass]['id'];

        if(!empty($this->request->data['StudentWatchList']['id'])) {
        // debug("here a"); die();
                  $id = $this->request->data['StudentWatchList']['id'];     //the Pk of Associated model (StudentSearchAgent)
        }

        //debug($id); die();
        if (!empty($id) && (!$data = $this->{$this->modelClass}->StudentWatchList->find(
                            'first', array(
                            'conditions' => array(
                                //'StudentSearchAgent.student_id' => $this->Auth->user('id'),
                                'StudentWatchList.id'  => $id)))))
                     {


                            throw new NotFoundException(__('Invalid Record.'));

                     }

                if(!empty($this->request->data['StudentWatchList']['editYourNotes']))  {
                  $this->{$this->modelClass}->StudentWatchList->set(array(
                                  //'tutor_name' => $this->request->data['tutor_name'],
                                  'note_on_tutor' => $this->request->data['StudentWatchList']['editYourNotes']
                                  //'tutor_id' => $this->request->data['tutor_id'],
                                 // 'on_watch_list' => $this->request->data['on_watch_list']
                         ));
                    } else if(!empty($this->request->data['StudentWatchList']['editNotes']))  {
                         $this->{$this->modelClass}->StudentWatchList->set(array(
                                  //'tutor_name' => $this->request->data['tutor_name'],
                                  'note_on_tutor' => $this->request->data['StudentWatchList']['editNotes']
                                  //'tutor_id' => $this->request->data['tutor_id'],
                                 // 'on_watch_list' => $this->request->data['on_watch_list']
                         ));
                    } else {
                        //throw new NotFoundException(__('Invalid Update.'));
                        	$this->Session->setFlash
   									(
   												sprintf(__d('users', 'You have attempted to update an Empty Note. No Update was made.')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
                               return $this->redirect(array('action' => 'tutor_search_results_auth'));


                    }

              if( $this->{$this->modelClass}->StudentWatchList->validates(
                  array('fieldList' => array('note_on_tutor'))))
               {
                      //debug($this->request->data); die();
                     if($this->{$this->modelClass}->StudentWatchList->saveTutorProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Notes have been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
                               //return $this->redirect(array('action' => 'tutor_search_results_auth'));
   					           return $this->redirect($this->referer());
                         } else {

                             $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Failed to Save your changes. Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );

                              // return $this->redirect(array('action' => 'tutor_search_results_auth'));
                               return $this->redirect($this->referer());
                    }

                 }

              } //if this request data

      }


}


public function add_to_watch_list($datastring=null) {

  if (!$this->request->is('ajax')) {
        //debug('Donald'); //die();
        throw new MethodNotAllowedException();
    }

    $this->layout = 'ajax';
    $this->autoRender = false;

      $data = $this->request->data;

     // debug("alert here"); die();
     // debug($data['editAct']);

   //$this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   $this->request->data['student_id'] = $this->Auth->user('id'); //$this->request->data[$this->modelClass]['id'];


      if(!empty($this->request->data) )  {

          $this->{$this->modelClass}->StudentWatchList->set(array(
                                  'tutor_name' => $this->request->data['tutor_name'],
                                  'note_on_tutor' => $this->request->data['note_on_tutor'],
                                  'tutor_id' => $this->request->data['tutor_id'],
                                  'on_watch_list' => $this->request->data['on_watch_list']
                         ));

           if( $this->{$this->modelClass}->StudentWatchList->validates(array('fieldList' => array(
                                                            //'tutor_name',
					                                        'tutor_id'

                                                            ))))
                  {

                      //debug($this->request->data); die();
                     if($this->{$this->modelClass}->StudentWatchList->saveTutorProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Tutor has been successfully added to Your Watch List.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );



                              // return $this->redirect(array('action' => 'tutor_search_resuts_auth'));
   					  } else {
   					    	$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Tutor Was NOT added to watch List! Please correct all Errors and try again!!.')),
   											   'default',
   												array('class' => 'alert alert-danger')
   									 );
   					  }
                  } else {

                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );

                    }


      } //End $this->request->data


}
public function tutor_search() {

    // $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     //$this->layout='default';

     	if (!$this->Auth->loggedIn()) {
     	  return $this->redirect(array('action' => 'tutor_search_results'));
        } else {

            return $this->redirect(array('action' => 'tutor_search_results_auth'));
        }
      //$this->layout='student';

}

/*
	* @Method      :createSearchAgent
	* @Description :for create search agent
	* @access      :registered User Group
	* @param       :
	* @return      :null
	*/
function search_agent($id=null, $agent_id=null){

        //("test"); die();
     $search_query = $this->Session->read('search_criteria');
     debug($search_query); // die();
		$this->layout='student';
        $subject ="";
        $zip_code = $this->Session->read('cur_zip_code');
        $distance = 20;
        $min_age = 18;
        $max_age = 100;
        $min_rate = 18;
        $max_rate = 250;
        $gender = 0;
        $bg_checked = 0;
        $is_advanced = false;
        $cur_page = 1;
        $kwd = "";
        $agent_id = 0;

        $session_search_criteria = null;
        $search_query = null;
        $params_url = null;

        $subject_model = new Subject();

if(!$this->request->is('ajax') && $this->request->is('post')) {
    //$session_criteria = $this->Session->read('search_criteria');
    //debug($session_criteria); die();
    // $search_query = $this->Session->read('search_query');

      $session_search_criteria  = $this->Session->read('search_criteria');
      $params_url = $session_search_criteria; //= $params_url;

     $id = null;
	 if(!empty($this->request->data))
	 {

        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['StudentSearchAgent']['student_id'] = $this->request->data[$this->modelClass]['id'];

        if(!empty($this->request->data['StudentSearchAgent']['id'])) {
        // debug("here a"); die();
                  $id = $this->request->data['StudentSearchAgent']['id'];     //the Pk of Associated model (StudentSearchAgent)
        }
        //The record should NOT exist.. This is a Totally new Record
        //If it does, this MUST have been injected/hacked

		/**
         if (empty($id) && ($data = $this->{$this->modelClass}->StudentSearchAgent->find(
                            'first', array(
                            'conditions' => array(
                                'StudentSearchAgent.student_id' => $this->Auth->user('id'),
                                'StudentSearchAgent.id'  => $id)))))
                     {


                            throw new NotFoundException(__('Invalid Record.'));

                     }
             **/

             /**
             if(empty($id)) { //if true, Must be a new record
                    $r = $this->{$this->modelClass}->StudentSearchAgent->find('all', array(
                              array('field' => 'MAX(StudentSearchAgent.agent_id) as agent_id',
			   		     	        'value' => $this->Auth->user('id')
                              ),
                              'order' => 'agent_id DESC'
                     ));
                if(!empty($r)) {
                        //order by DESC should gives us the biggest Id first
                        $this->request->data['StudentSearchAgent']['agent_id'] = $r[0]['StudentSearchAgent']['agent_id'] + 1;
                         $agent_id = $this->request->data['StudentSearchAgent']['agent_id'];
                     } else {
                      $this->request->data['StudentSearchAgent']['agent_id']  = 0;
                      $agent_id = 0;
                      //debug("test");
                     }
                } else if (!empty($this->request->data['StudentSearchAgent']['agent_id'])){
                    //User must be trying to edit the Agent.
                    //So an agent_id should already be present in request
                    $agent_id = $this->request->data['StudentSearchAgent']['agent_id'];
                }
           **/

           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
            {

                $conditions = array(
								'student_id' => $this->Auth->user('id'),
								'agent_name' => $this->request->data['StudentSearchAgent']['agent_name'],
						 );

            if ($this->{$this->modelClass}->StudentSearchAgent->hasAny($conditions)){ //Cannot have a Duplicate Agent Name

                 $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'It appears that an Agent with same Name already exists.<br />Please save your Agent under a different name!!')),
                                               'default',
 												array('class' => 'alert alert-warning')

                                        );

                return; // $this->redirect()

             } else  { //Create the record

			    $r = $this->getAllSearchAgents($this->Auth->user('id'));
       //$agent_count =   count($search_agents) ;
       //$this->Session->write('agent_count', $agent_count);

                if(!empty($r) && count($r) >= 5) { //user is Only allowed 5 Search agents for now

				  $this->set('agent_count', count($r));
                  $this->Session->write('agent_count', count($r));

                  $this->Session->setFlash
       									(
       												sprintf(__d('users', 'You are Only allowed to save 5 Agents')),
       											   'default',
       												array('class' => 'alert alert-warning')
       									 );
				  return $this->redirect(array('action' => 'tutor_search_results_auth'));
                }

			   for($i=1; $i<6; $i++) { //this is so that the agent_id is always between 1 and 5

						$data = $this->{$this->modelClass}->StudentSearchAgent->find(
										'first', array(
										//'order' => array('StudentSearchAgent.agent_id' => 'DESC'),
										'conditions' => array(
											'StudentSearchAgent.student_id' => $this->Auth->user('id'),
											'StudentSearchAgent.agent_id'  => $i)));
						if (!$data) {
							$this->request->data['StudentSearchAgent']['agent_id'] = $i; //$data['StudentSearchAgent']['agent_id'] + 1;

							break;
						} else {
						   $this->request->data['StudentSearchAgent']['agent_id'] = $data['StudentSearchAgent']['agent_id']; //1;
						}

               }
             	$this->{$this->modelClass}->StudentSearchAgent->set($conditions);
                $this->{$this->modelClass}->StudentSearchAgent->create();
               // $this->request->data['StudentSearchAgent']['created'] = date('Y-m-d H:i:s');

                //$session_search_criteria = $this->Session->read('session_search_criteria');
				 //debug($session_search_criteria); die();

            //	if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)

                	if(!empty($session_search_criteria['subject']) && $session_search_criteria['subject'] != "All Subjects")
					{
					   $this->request->data['StudentSearchAgent']['subject'] = $session_search_criteria['subject'];
                       $subject = $this->request->data['StudentSearchAgent']['subject'];

                    } else
					{
					   $this->request->data['StudentSearchAgent']['subject'] = "All Subjects";
                       $subject = $this->request->data['StudentSearchAgent']['subject'];
                    }

                   	if(!empty($session_search_criteria['subject_id']))
					{
					   $this->request->data['StudentSearchAgent']['subject_id'] = $session_search_criteria['subject_id'];
                       $subject_id = $this->request->data['StudentSearchAgent']['subject_id'];
                    } else
					{
					   $this->request->data['StudentSearchAgent']['subject_id'] = "AllSubjects";
                       $subject_id = $this->request->data['StudentSearchAgent']['subject_id'];
                    }

                    if($session_search_criteria['subject'] != "All Subjects" && $session_search_criteria['subject_id'] != "AllSubjects") {
                       $data = $subject_model->findSubjectCategory($subject_id, $subject);
                       debug($data); //die();
                       $category = $data[0]['Subject']['category_name'];
                      // debug($category); die();
                       $category_id = $data[0]['Subject']['category_id'];

                        $this->request->data['StudentSearchAgent']['category'] = $category;
                        $this->request->data['StudentSearchAgent']['category_id'] = $category_id;
                    } else {


                     if(!empty($session_search_criteria['category']))
					{
					   $this->request->data['StudentSearchAgent']['category'] = $session_search_criteria['category'];
                       $category = $this->request->data['StudentSearchAgent']['category'];
                    } else
					{
					   $this->request->data['StudentSearchAgent']['category'] = "All Categories";
                       $category = $this->request->data['StudentSearchAgent']['category'];
                    }

                   if(!empty($session_search_criteria['category_id']))
					{
					   $this->request->data['StudentSearchAgent']['category_id'] = $session_search_criteria['category_id'];
                       $category_id = $this->request->data['StudentSearchAgent']['category_id'];
                    } else
					{
					   $this->request->data['StudentSearchAgent']['category_id'] = "AllCategories";
                       $category_id = $this->request->data['StudentSearchAgent']['category_id'];
                    }
                  }
					if(!empty($session_search_criteria['zip_code']))
					{
					   $this->request->data['StudentSearchAgent']['zip_code'] = $session_search_criteria['zip_code'];
                       $zip_code = $this->request->data['StudentSearchAgent']['zip_code'];
                    } else {

					   $this->request->data['StudentSearchAgent']['zip_code'] = $this->Session->read('cur_zip_code');
                       $zip_code = $this->request->data['StudentSearchAgent']['zip_code'];
                     }

					if(!empty($session_search_criteria['distance']) && $session_search_criteria['distance']>=20)
					 {

                       //debug("Here"); die();
					   $this->request->data['StudentSearchAgent']['distance'] = $session_search_criteria['distance'];
                       $distance = $this->request->data['StudentSearchAgent']['distance'];
                       //debug($distance); die();
                     }
					else
					{
					   $this->request->data['StudentSearchAgent']['distance'] = 20;
                       $distance = $this->request->data['StudentSearchAgent']['distance'];

                    }

					if(!empty($session_search_criteria['min_age']) && $session_search_criteria['min_age']>=18)
					{
					   $this->request->data['StudentSearchAgent']['min_age'] = $session_search_criteria['min_age'];
                       $min_age = $this->request->data['StudentSearchAgent']['min_age'];
                    }
					else
					{
					   $this->request->data['StudentSearchAgent']['min_age'] = 18;
                       $min_age = $this->request->data['StudentSearchAgent']['min_age'];
                    }

					if(!empty($session_search_criteria['max_age']) && $session_search_criteria['max_age']<=100)
					{
					   $this->request->data['StudentSearchAgent']['max_age'] = $session_search_criteria['max_age'];
                       $max_age = $this->request->data['StudentSearchAgent']['max_age'];

                    }
					else
					{
					   $this->request->data['StudentSearchAgent']['max_age'] = 100;
                       $max_age = $this->request->data['StudentSearchAgent']['max_age'];
                    }

					if(!empty($session_search_criteria['amount_min_rate']) && $session_search_criteria['amount_min_rate']>=10)
					{
					   $this->request->data['StudentSearchAgent']['min_rate'] = $session_search_criteria['amount_min_rate'];
                       $min_rate = $this->request->data['StudentSearchAgent']['min_rate'];
                     }
					else
					{
					   $this->request->data['StudentSearchAgent']['min_rate'] = 10;
                       $min_rate = $this->request->data['StudentSearchAgent']['min_rate'];
                     }

					if(!empty($session_search_criteria['amount_max_rate']) && $session_search_criteria['amount_max_rate']<=250)
					{
					   $this->request->data['StudentSearchAgent']['max_rate'] = $session_search_criteria['amount_max_rate'];
                       $max_rate = $this->request->data['StudentSearchAgent']['max_rate'];
                    }
					else
					{
					   $this->request->data['StudentSearchAgent']['max_rate'] = 250;
                       $max_rate = $this->request->data['StudentSearchAgent']['max_rate'];
                     }

					if(!empty($session_search_criteria['gender']))
					{
					   $this->request->data['StudentSearchAgent']['gender'] = $session_search_criteria['gender'];
                       $gender = $this->request->data['StudentSearchAgent']['gender'];
                    }
					else
					{
					   $this->request->data['StudentSearchAgent']['gender'] = 0;
                       $gender = $this->request->data['StudentSearchAgent']['gender'];
                    }

                   	if(!empty($session_search_criteria['bg_checked']))
					{
					   $this->request->data['StudentSearchAgent']['bg_checked'] = $session_search_criteria['bg_checked'];
                       $bg_checked = $this->request->data['StudentSearchAgent']['bg_checked'];
                    }
					else
					{
					   $this->request->data['StudentSearchAgent']['bg_checked'] = 0;
                       $bg_checked = $this->request->data['StudentSearchAgent']['bg_checked'];
                    }

					if(!empty($session_search_criteria['kwd']))
					{
					   $this->request->data['StudentSearchAgent']['kwd'] = $session_search_criteria['kwd'];
					   $kwd = $this->request->data['StudentSearchAgent']['kwd'];
                    }
                    else
					{
					   $this->request->data['StudentSearchAgent']['kwd'] = "";
                       $kwd = $this->request->data['StudentSearchAgent']['kwd'];
                     }
                     //debug($session_search_criteria['is_advanced']); die();
                   	//if(!empty($session_search_criteria['is_advanced']) && in_array($session_search_criteria['is_advanced'], array(0,1)))
					if(!empty($session_search_criteria['is_advanced']))
                    {
					   $this->request->data['StudentSearchAgent']['is_advanced'] = $session_search_criteria['is_advanced'];
                       $is_advanced = $this->request->data['StudentSearchAgent']['is_advanced'];
                    }else
					{
					   $this->request->data['StudentSearchAgent']['is_advanced'] = 0;
                       $is_advanced = $this->request->data['StudentSearchAgent']['is_advanced'];
                    }

                    if(!empty($session_search_criteria['cur_page']))
					{
					   $this->request->data['StudentSearchAgent']['cur_page'] = $session_search_criteria['cur_page'];
                       $cur_page = $this->request->data['StudentSearchAgent']['cur_page'];
                    }else
					{
					   $this->request->data['StudentSearchAgent']['cur_page'] = 1;
                       $cur_page = $this->request->data['StudentSearchAgent']['cur_page'];
                    }


                       //$search_query =  '?user_category='.$category.'&user_category_id='.$category_id.'&user_subject='.$subject.'&user_subject_id='.$subject_id.'&category='.$category.'&category_id='.$category_id.'&subject='.$subject.'&subject_id='.$subject_id.'&zip_code='.$zip_code.'&is_advanced='.$is_advanced.'&cur_page='.$cur_page.'&distance='.$distance.'&amount_min_rate='.$min_rate.'&amount_max_rate='.$max_rate.'&min_age='.$min_age.'&max_age='.$max_age.'&gender='.$gender.'&bg_checked='.$bg_checked.'&kwd='.$kwd;
                      //debug($params_url); //die();
                       $is_advanced = !empty($params_url['is_advanced'])? $params_url['is_advanced']:0;
                       $gender = !empty($params_url['gender'])? $params_url['gender']:0;
                       $bg_checked = !empty($params_url['bg_checked'])? $params_url['bg_checked']:0;


                       $search_query =  '?user_category='.$params_url['category'].'&user_category_id='.$params_url['category_id'].'&user_subject='.$params_url['subject'].'&user_subject_id='.$params_url['subject_id'].'&category='.$params_url['category'].'&category_id='.$params_url['category_id'].'&subject='.$params_url['subject'].'&subject_id='.$params_url['subject_id'].'&zip_code='.$params_url['zip_code'].'&is_advanced='.$is_advanced.'&cur_page='.$params_url['cur_page'].'&distance='.$params_url['distance'].'&amount_min_rate='.$params_url['amount_min_rate'].'&amount_max_rate='.$params_url['amount_max_rate'].'&min_age='.$params_url['min_age'].'&max_age='.$params_url['max_age'].'&gender='.$gender.'&bg_checked='.$bg_checked.'&kwd='.$params_url['kwd'];

                       $this->request->data['StudentSearchAgent']['search_query']  = $search_query; //$params_url;

                       if($this->{$this->modelClass}->StudentSearchAgent->saveSearchAgent($id, $this->request->data))
						  //if($this->{$this->modelClass}->StudentSearchAgent->saveAll($this->request->data))

						 {
										$this->Session->setFlash
												(
															sprintf(__d('users', 'Your Search has been successfully saved.')),
														   'default',
															array('class' => 'alert alert-success')
												 );
												 $r = $this->getAllSearchAgents($this->Auth->user('id'));
                                                 $this->Session->write('agent_count', count($r));

                                                   //$this->Session->delete('session_search_criteria_not_in_in');
                                                 // $this->Session->delete('search_criteria');
                                                  $this->Session->delete('params_url');
                                                  // $this->Session->delete('session_search_criteria_no_submit');
                                                 // $this->Session->delete('session_search_criteria_in_in');

                                                 return $this->redirect(array('action' => 'tutor_search_results_auth'.$search_query));

						 }
               }

			} else {
			    	$this->Session->setFlash
       									(
       												sprintf(__d('users', 'Your have no new Search Criteria.Please Search first and build your Agent')),
       											   'default',
       												array('class' => 'alert alert-warning')
       									 );

			}                  return $this->redirect(array('action' => 'tutor_search_results_auth'));
		}//request->data

     } //is->post())

     else if ($this->request->is('ajax') ) {
            $this->render('edit', 'ajax');
            $this->{$this->modelClass}->StudentSearchAgent->id = $id;
            $ssa = $this->{$this->modelClass}->StudentSearchAgent->findById($id);

        }

       // $params_url = $this->Session->read('search_criteria');
       // debug($params_url); //die();

}

function edit_search_agent($id=null, $agent_id=null, $agent_name=null){

        //("test"); die();
		$this->layout='student';
        $subject ="";
        $zip_code = $this->Session->read('cur_zip_code');
        $distance = 20;
        $min_age = 18;
        $max_age = 100;
        $min_rate = 18;
        $max_rate = 250;
        $gender = 0;
        $bg_checked = 0;
        $is_advanced = false;
        $cur_page = 1;
        $kwd = "";
        $agent_id = 0;
        $session_search_criteria = null;

        // $session_search_criteria = $this->Session->read('search_criteria');
        // debug($session_search_criteria); die();

 if($this->request->is('post')) {
     //$id = null;
	 if(!empty($this->request->data))
	 {
	    //debug($this->request->data);
       // if( $this->Session->check('params_url') ) {
       if( $this->Session->check('search_criteria') ) {
             //$session_search_criteria = $this->Session->read('params_url');
              $session_search_criteria = $this->Session->read('search_criteria');
            // debug($session_search_criteria); die();
       }

      //debug($session_search_criteria); die();
        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['StudentSearchAgent']['student_id'] = $this->request->data[$this->modelClass]['id'];

        if(!empty($this->request->data['StudentSearchAgent']['id'])) {
        // debug("here a"); die();
                  $id = $this->request->data['StudentSearchAgent']['id'];     //the Pk of Associated model (StudentSearchAgent)
        }

        //debug($id); die();
        if (!empty($id) && (!$data = $this->{$this->modelClass}->StudentSearchAgent->find(
                            'first', array(
                            'conditions' => array(
                                //'StudentSearchAgent.student_id' => $this->Auth->user('id'),
                                'StudentSearchAgent.id'  => $id)))))
                     {


                            throw new NotFoundException(__('Invalid Record.'));

                     }

           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
                $this->{$this->modelClass}->StudentSearchAgent->set(array(
                                  'agent_name' => $this->request->data['StudentSearchAgent']['agent_name'],
                                   'agent_id' => $this->request->data['StudentSearchAgent']['agent_id'],
                                   'id' => $this->request->data['StudentSearchAgent']['id'],

                                       'subject' => $session_search_criteria['subject'],
                                       'category' => $session_search_criteria['category'],
                                       'subject_id' => $session_search_criteria['subject_id'],
                                       'category_id' => $session_search_criteria['category_id'],

                                      'zip_code' => $session_search_criteria['zip_code'],
                                      'distance' => $session_search_criteria['distance'],
                                      'min_age' => $session_search_criteria['min_age'],
                                      'min_age' => $session_search_criteria['max_age'],
                                      'min_rate' => $session_search_criteria['amount_min_rate'],
                                      'max_rate' => $session_search_criteria['amount_max_rate'],
                                      'gender' => $session_search_criteria['gender'],
                                      'bg_checked' => $session_search_criteria['bg_checked'],
                                      'cur_page' => $session_search_criteria['cur_page'],
                                      'kwd' => $session_search_criteria['kwd'],
                                      'is_advanced' => $session_search_criteria['is_advanced']


                         ));
         }

       $postData = array();

      if( $this->{$this->modelClass}->StudentSearchAgent->validates(
                  array('fieldList' => array('agent_name'))))
         {

           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
             $postData['StudentSearchAgent']['agent_name'] = $agent_name = $this->request->data['StudentSearchAgent']['agent_name'];
             $postData['StudentSearchAgent']['agent_id'] = $agent_id = $this->request->data['StudentSearchAgent']['agent_id'];
			 //$postData['StudentSearchAgent']['id'] = $this->request->data['StudentSearchAgent']['id'];
			 $id = $this->request->data['StudentSearchAgent']['id'];
			 $postData['StudentSearchAgent']['subject'] = $subject = $session_search_criteria['subject'];
             $postData['StudentSearchAgent']['category'] = $category = $session_search_criteria['category'];
             $postData['StudentSearchAgent']['subject_id'] = $subject_id = $session_search_criteria['subject_id'];
             $postData['StudentSearchAgent']['category_id'] = $category_id = $session_search_criteria['category_id'];

             $postData['StudentSearchAgent']['zip_code'] = $zip_code = $session_search_criteria['zip_code'];
             $postData['StudentSearchAgent']['distance'] = $distance = $session_search_criteria['distance'];
             $postData['StudentSearchAgent']['min_age'] = $min_age = $session_search_criteria['min_age'];
             $postData['StudentSearchAgent']['max_age'] = $max_age = $session_search_criteria['max_age'];
             $postData['StudentSearchAgent']['min_rate'] = $min_rate = $session_search_criteria['amount_min_rate'];
             $postData['StudentSearchAgent']['max_rate'] = $max_rate = $session_search_criteria['amount_max_rate'];
             $postData['StudentSearchAgent']['gender'] = $gender = $session_search_criteria['gender'];
             $postData['StudentSearchAgent']['cur_page'] = $cur_page = $session_search_criteria['cur_page'];
             $postData['StudentSearchAgent']['is_advanced'] = $is_advanced = $session_search_criteria['is_advanced'];
			 $postData['StudentSearchAgent']['kwd'] = $kwd = $session_search_criteria['kwd'];
             $postData['StudentSearchAgent']['bg_checked'] = $bg_checked = $session_search_criteria['bg_checked'];

		         //$search_query =  '?user_category='.$category.'&user_category_id='.$category_id.'&user_subject='.$subject.'&user_subject_id='.$subject_id.'category='.$category.'&category_id='.$category_id.'&subject='.$subject.'&subject_id='.$subject_id.'&zip_code='.$zip_code.'&is_advanced='.$is_advanced.'&cur_page='.$cur_page.'&distance='.$distance.'&amount_min_rate='.$min_rate.'&amount_max_rate='.$max_rate.'&min_age='.$min_age.'&max_age='.$max_age.'&gender='.$gender.'&bg_checked='.$bg_checked.'&kwd='.$kwd;
                  $search_query =  '?user_category='.$category.'&user_category_id='.$category_id.'&user_subject='.$subject.'&user_subject_id='.$subject_id.'&category='.$category.'&category_id='.$category_id.'&subject='.$subject.'&subject_id='.$subject_id.'&zip_code='.$zip_code.'&is_advanced='.$is_advanced.'&cur_page='.$cur_page.'&distance='.$distance.'&amount_min_rate='.$min_rate.'&amount_max_rate='.$max_rate.'&min_age='.$min_age.'&max_age='.$max_age.'&gender='.$gender.'&bg_checked='.$bg_checked.'&kwd='.$kwd;

                  $postData['StudentSearchAgent']['search_query']  = $search_query;

			 if($this->{$this->modelClass}->StudentSearchAgent->saveSearchAgent($id, $postData))

                 {
       							$this->Session->setFlash
       									(
       												sprintf(__d('users', 'Your Search Agent has been successfully updated.')),
       											   'default',
       												array('class' => 'alert alert-success')
       									 );



                                          $this->Session->delete('params_url');
                                          $this->Session->delete('search_criteria');

                                          $this->Session->delete('agent_name');
										  $this->Session->delete('agent_id');
                                          $this->Session->delete('id');

										  return $this->redirect(array('action' => 'tutor_search_results_auth'.$search_query));
    		     } else {

				               $this->Session->setFlash
       									(
       												sprintf(__d('users', 'Save failed.')),
       											   'default',
       												array('class' => 'alert alert-warning')
       									 );


                                          $this->Session->delete('params_url');
                                          $this->Session->delete('search_criteria');

										  $this->Session->delete('agent_name');
										  $this->Session->delete('agent_id');
                                          $this->Session->delete('id');

										  return $this->redirect(array('action' => 'tutor_search_results_auth'));
				  }

             }

         }
      }
   } else if($this->request->is('get')) {

    if (!empty($this->params->query)) {

      // debug($this->params->query); //die();

      $agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
      $agent_name = !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
      $id = !empty($this->params->query['id']) ? $this->params->query['id'] : 0;
      $update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 1;

      $session_search_criteria['user_subject'] =  $this->params->query['my_subject'];
	  $session_search_criteria['user_subject_id'] =  $this->params->query['my_subject_id'];
	  $session_search_criteria['user_category'] =  $this->params->query['my_category'];
	  $session_search_criteria['user_category_id'] =  $this->params->query['my_category_id'];

     $session_search_criteria['subject'] =  $this->params->query['my_subject'];
	 $session_search_criteria['subject_id'] =  $this->params->query['my_subject_id'];
	 $session_search_criteria['category'] =  $this->params->query['my_category'];
	 $session_search_criteria['category_id'] =  $this->params->query['my_category_id'];

	$session_search_criteria['zip_code'] =  $this->params->query['my_zip_code'];
	$session_search_criteria['distance'] =  $this->params->query['my_distance'];
	$session_search_criteria['min_age'] =  $this->params->query['my_min_age'];
	$session_search_criteria['max_age'] =  $this->params->query['my_max_age'];

	$session_search_criteria['amount_min_rate'] =  $this->params->query['my_amount_min_rate'];
	$session_search_criteria['amount_max_rate'] =  $this->params->query['my_amount_max_rate'];
	$session_search_criteria['gender'] =  $this->params->query['my_gender'];
	$session_search_criteria['bg_checked'] =  $this->params->query['my_bg_checked'];
    $session_search_criteria['is_advanced'] =  $this->params->query['my_is_advanced'];
    $session_search_criteria['kwd'] =  $this->params->query['my_kwd'];
    $session_search_criteria['cur_page'] =  $this->params->query['my_cur_page'];



     $this->Session->write('search_criteria', $session_search_criteria);


    // $params_url = $this->Session->read('params_url');
     //debug($params_url); die();
     //debug($agent_id);
    // debug($id); die();
     $this->set('agent_name', $agent_name);
     $this->set('agent_id', $agent_id);
     $this->set('id', $id);
     $this->set('update_agent', $update_agent);

     $this->Session->write('agent_name', $agent_name);
     $this->Session->write('agent_id', $agent_id);
     $this->Session->write('id', $id);
     $this->Session->write('update_agent', $update_agent);

    }
    // debug($this->params->query); die();
      return $this->redirect(array('action' => 'search_agent'));

    }
}


public function render($view = null, $layout = null) {
           if (is_null($view)) {
               $view = $this->action;
           }
           //debug($layout); die();
           $viewPath = substr(get_class($this), 0, strlen(get_class($this)) - 10);
            //debug($this->viewPath); die();
           if (!file_exists(APP . 'View' . DS . $viewPath . DS . $view . '.ctp')) {
           // debug("test"); die();
               $this->plugin = 'Users';
           } else {
               $this->viewPath = $viewPath;
               //debug($this->viewPath);
           }
          // debug("test"); die();
           return parent::render($view, $layout);
     }


protected function _set_city_for_zip($zip_code){

        //$this->Session->delete('city');
		//$cur_city = $this->Session->read('search_city');
        $radiusSearch = new ZipSearch();
        $tutors_model = new Tutor();
        $search_city = "";
        $result = array();

        $curr_session_zip = $this->Session->read('cur_zip_code');


        //debug($curr_session_zip);
        //debug($zip_code); die();
	   	if(empty($curr_session_zip) || $curr_session_zip != $zip_code ){
	   	   // debug($curr_session_zip); die();
            //$this->Session->delete('cur_zip_code');
            $result = $tutors_model->find_city_ByZipCode($zip_code);
             if(!empty($result) && isset($result['city'])) {
              $search_city = $result['city'];
            }
            $this->Session->write('search_city', $search_city);
         }
        //debug($search_city); die();
        // if(!empty($search_city)){
	   		//	$this->Session->write('search_city', $search_city);
   		// }

      //usort($myArray, 'sortByOrder');

         return $search_city;
   }

   //$result[$i]['Tutor']


   public function sortByOrder($a, $b) {
    return $b['Tutor']['0'] - $a['Tutor']['0'];
   }

public function delete_agent($id=null) {
     $this->layout='student';

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }
   // debug("hey"); die();
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }

     if ( empty($id) || !($data = $this->{$this->modelClass}->StudentSearchAgent->find(
                            'first', array('conditions' => array('StudentSearchAgent.id' => $id)))))
    {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'tutor_search_results_auth'));
     }

     if ($data['StudentSearchAgent']['id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }
    if($this->{$this->modelClass}->StudentSearchAgent->delete($id)) {

        //$r=$this->agentCount();
        $r = $this->getAllSearchAgents($this->Auth->user('id'));
        $this->Session->write('agent_count', count($r));

       // if( !empty($r)  && count($r) < 5) {

		 // $this->Session->delete('agent_count');
		//}

        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Agent has been successfully deleted.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'tutor_search_results_auth'));

     } else {

         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'deleted failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
     }

}

public function remove_tutor($id=null, $watchlist=null) {
     $this->layout='student';

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }
   // debug("hey"); die();
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }

     if ( empty($id) || !($data = $this->{$this->modelClass}->StudentWatchList->find(
                            'first', array('conditions' => array('StudentWatchList.id' => $id)))))
     {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          //$this->redirect(array('action' => 'tutor_search_results_auth'));
           $this->redirect($this->referer());
     }

     if ($data['StudentWatchList']['id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }
    if($this->{$this->modelClass}->StudentWatchList->delete($id)) {


        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Tutor has been successfully removed from List.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

       if(!empty($watchlist)) {
         return $this->redirect(array('action' => 'watch_list_center '));
        } else {
            //return $this->redirect(array('action' => 'tutor_search_results_auth '));
            return $this->redirect($this->referer());
        }
     } else {

         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Failed to remove tutor. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
             return $this->redirect($this->referer());
     }

}

protected function return_tutor_array($return_array=array()) {

    $i=0;
    $j=0;
    foreach ($return_array as $key => $value) {
                foreach ($return_array[$i]['StudentWatchList'] as $key1 => $value1) {
                   // debug($return_array[$i]['StudentWatchList']); //die()
                    if( empty($return_array[$i]['StudentWatchList'][$j])
                          || $return_array[$i]['StudentWatchList'][$j]['student_id'] != $this->Auth->user('id')) {

                        unset($return_array[$i]['StudentWatchList'][$j]);

                    } else if (sizeof($return_array[$i]['StudentWatchList']) == 0){
                         // debug($return_array[$i]['StudentWatchList']); die();
                          unset($return_array[$i]['StudentWatchList']);

                    }
                    $j++;
             }
             $i++;
             $j=0;
         }

         return $return_array;
    }


function ajax_cat_subjects() {

   if (!$this->request->is('ajax')) {
        //debug('Donald'); //die();
        throw new MethodNotAllowedException();
    }

    $tutor_subject_model = new TutorSubject();
    $this->layout = 'ajax';
    $this->autoRender = false;
    $tutors_model = new Tutor();


    $id = $this->request->data['value'];

    if($id === "AllCategories"){
        //This may be the case where user clicked on "Find A Tutor"
        $subjects_array = $tutors_model->get_all_subjects();
        //sort($data);
       // debug($data); die();
        $subjects_array = json_encode($tutors_model->get_all_subjects());
    } else {

        $subjects_array = $tutors_model->get_all_subjects($id);
        //debug($subjects_array); //die();
       $subjects_array = $this->flatten($subjects_array, '');
       // arsort($subjects_array);
        ksort($subjects_array);
       // debug($subjects_array); //die();

        //get all subjects tied to this specific category id
        $subjects_array = json_encode($tutors_model->get_all_subjects($id));
       // debug($subjects_array); die();
       // $subjects_array = $this->flatten($subjects_array, '');
       // arsort($subjects_array);
      // ksort($subjects_array);
       //return json_encode(array('results' => json_decode($result)));
       // debug($subjects_array); die();
    }

    //debug(sort($data)); die();
    return $subjects_array;

   }
   
 
function all_ajax_cat_subjects() {

   //if (!$this->request->is('ajax')) {
        //debug('Donald'); //die();
     //   throw new MethodNotAllowedException();
   // }

    $tutor_subject_model = new TutorSubject();
    $this->layout = 'ajax';
    $this->autoRender = false;
    $tutors_model = new Tutor();
	
	$subjects_and_categories = $tutors_model->get_all_subjects_and_categories();
	//debug($subjects_and_categories); die();
   // get the q parameter from URL
   //$q = $_REQUEST["q"];
   $q = $this->request->data['q'];
   $hint = "";

// lookup all hints from array if $q is different from "" 
if ($q !== "") {
    $q = strtolower($q);
    $len=strlen($q);
    foreach($subjects_and_categories as $name) {
        if (stristr($q, substr($name, 0, $len))) {
            if ($hint === "") {
                $hint = $name;
            } else {
                $hint .= ", $name";
            }
        }
    }
}

//   Output "no suggestion" if no hint was found or output correct values 
    echo $hint === "" ? "no suggestion" : $hint;

   } 

}

?>