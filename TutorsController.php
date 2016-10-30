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
App::uses('Categorie', 'Model');
App::uses('Subject', 'Model');
App::uses('TutorSubject', 'Model');
App::uses('Student', 'Model');
App::uses('StudentJobPost', 'Model');
App::uses('File', 'Utility');


class TutorsController extends UsersController {

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
	public $name = 'Tutors';
	public $uses = array ('Tutor', 'TutorPreference', 'TutorProfile', 'TutorImage', 'TutorSubject');
    public $helpers = array('ZipCode', 'Html');


/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

		parent::beforeFilter();
        AuthComponent::$sessionKey = 'Auth.Tutor';
		$this->set('model', $this->modelClass);
	//	$this->Security->blackHoleCallback = 'blackhole';

        $this->Auth->allow('complete');
		/**
		if ($this->request->action == 'joinus') {
		   			$this->Components->disable('Auth');
   		}
   		**/
		$this->_setupPagination();
		$this->set('model', $this->modelClass);

		$this->Session->delete('view_layout');
		$this->Session->write('view_layout', 'tutor');

		$id = $this->Auth->user('id'); //Using the session's user id is fine because it doesn't change/update
        // debug($id); die();
        if($this->Session->check(AuthComponent::$sessionKey . 'first_name')) {
                  $this->Session->write('username', $this->Session->read(AuthComponent::$sessionKey . 'first_name'));
                  //debug($this->Session->read('Auth.Tutor.first_name')); die();
        } else {
                $user_data = $this->{$this->modelClass}->findById($id);
		 		if($user_data != null) {
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
		 		    $last_name = $user_data[$this->modelClass]['last_name'];
                    $zip_code = $user_data[$this->modelClass]['zip_code'];
                    $email_addr =  $user_data[$this->modelClass]['email'];

                    //$this->set('zip_code', h($zip_code));
		            $this->set('fname', h($user_fname));
                     $this->Session->write('zip_code', $zip_code);
		            $this->Session->write('username', $user_fname);
		            $this->Session->write('lastname', $last_name);
                    $this->Session->write('email_addr', $email_addr);
                }
        }

        /**
			* Changed in version 2.4: Sometimes, you want to display the authorization error only if
			* the user has already logged-in. You can suppress this message by setting its value to boolean false
		**/

		 if (!$this->Auth->loggedIn()) {
			$this->Auth->authError = false;
			$this->Session->write('view_layout', 'default');
        }

       // $this->Security->requirePost('delete');
        //$this->Security->requirePost('update_photo');
       // $this->Security->unlockedActions = array('update_photo');
       // $this->Security->unlockedActions = array('update_entry');
}
/**
public function blackhole($type) {
    // Handle errors.
    $this->layout = 'tutor';
    debug($type);
    throw new BadRequestException(__d('cake_dev', 'The request has been blackholed.'));
    //return $this->redirect('logout');
   // $this->redirect($this->Auth->logout());
}
**/

public function login() {
	
	//debug("test me out"); die();

     if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'home'));
     }
    if ($this->request->is('post')) {
         //debug("test me out"); die();

        if(!empty($this->request->data[$this->modelClass])) {

                          $throttle_delay = $this->throttleUserLogin($this->request->data[$this->modelClass]['email']);
                          $user = $this->{$this->modelClass}->getUserRecord($this->request->data[$this->modelClass]['email'], $options = array());

                          if(!empty($user)) {
                             $lock_account = $user[$this->modelClass]['lock_account'];
                             $this->Session->write('email_vefrified', $user[$this->modelClass]['email_verified']);

                             if(isset($lock_account) && !empty($lock_account)) {
                                 $message = "This account has been temporarily suspended. Please contact Wizwonk Tech Support for further informtion. ";
                                 $this->Session->setFlash($message, 'custom_msg');
                                 $this->redirect($this->Auth->loginAction);
                              }
                              if(isset($user[$this->modelClass]['captcha']) && !empty($user[$this->modelClass]['captcha'])) {
                                  // debug("testme"); //die();
                                  $this->Session->write('tt_captcha', $user[$this->modelClass]['captcha'] );
                                  $this->_setCookie($options=array(), 'tt_recaptcha');
                                  $this->set('tt_recaptcha', $this->Cookie->read('tt_recaptcha'));
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

       //debug($this->Cookie->read('recaptcha'));
    $this->set('tt_recaptcha', $this->Cookie->read('tt_recaptcha'));
}
public function joinus() {

  if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'home'));
   }
    //$this->layout = 'default';
	$this->layout = 'login_default';
    if($this->request->is('post')) {
         if (!empty($this->request->data)) {

           // debug($this->request->data); die();

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

             $tutors_model = new Tutor();
             //debug($this->request->data); die();
             Configure::write('Users.role', 'tutor');

             $member_id = uniqid(rand(), true);
             $result = String::tokenize($member_id, '.');
             $member_state = ''; //$this->Session->read('student_state');
            $city_state_result = $tutors_model->find_city_ByZipCode($this->request->data[$this->modelClass]['zip_code']);
            if(!empty($city_state_result) && isset($city_state_result['state'])) {

                $member_state = $city_state_result['state'];
                if(!empty($member_state)){
                    $member_id  = $member_state.$result[1]; //concatinate generated Random Unique Nbr with State Abbreviated 2 letters
                }
             }
                //debug($member_id); die();
                $this->request->data[$this->modelClass]['member_id'] = $member_id;
                // Call add() function of Parent (Plugin::UsersController)
                //check the uniqueness of email
                $this->add();
            //$this->validationErrors;
            //debug($this->Tutor->validationErrors);
            }
    }

    //debug("test"); die();
}

public function join_via_job() {


 if($this->request->is('post')) {
      $tutors_model = new Tutor();
   // debug($this->request->data); die();
	         $id = null;
	         $postData = array();
             //$this->Session->delete('error_array');
        if (!empty($this->request->data)) {

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

            Configure::write('Users.role', 'tutor');
            Configure::write('join_via_request', 'apply_for_job');
            //$job_id = $this->request->data['Tutor']['job_id'];
            //$this->Session->write('join_via_job', $job_id);
            // Call add() function of Parent (Plugin::UsersController)
            //check the uniqueness of email
             $member_id = uniqid(rand(), true);
             $result = String::tokenize($member_id, '.');
         //$member_state = $this->Session->read('student_state');
             $city_state_result = $tutors_model->find_city_ByZipCode($this->request->data[$this->modelClass]['zip_code']);

        if(!empty($city_state_result) && isset($city_state_result['state'])) {

            $member_state = $city_state_result['state'];
            $member_id  = $member_state.$result[1];
         } else {

            $member_id  = $member_state.$result[1];
         }

            //debug($member_id); die();
            $this->request->data[$this->modelClass]['member_id'] = $member_id;
            $this->add();
            //$this->Session->delete('join_via_job');

           //Automatically log user in
            if($this->Auth->login()) {
                $this->Session->write('view_layout', 'tutor');
                $loggedInUserType = $this->Session->read('loggedInUserType');
                $this->Session->write('loggedInUserType', 'Auth.Tutor');
                //debug($loggedInUserType);
                //debug($this->Session->read('view_layout')); die();
                $this->{$this->modelClass}->id = $this->Auth->user('id');
                $this->Session->write('first_login', true);
                $this->{$this->modelClass}->saveField('last_login', date('Y-m-d H:i:s'));

                return $this->redirect(array('action' => 'welcome'));
            }

           // $this->Session->write('error_array', $this->{$this->modelClass}->validationErrors);

         }
   }

}

public function complete() {
        $this->layout = 'default';
        if($this->Session->check('completeEmail')){
            $this->set('completeEmail',$this->Session->read('completeEmail'));
            $this->Session->delete('completeEmail');
        } else{
            return $this->redirect(array('controller'=>'tutors', 'action' => 'login'));
        }
}

public function index() {
  if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'home'));
   } else {
      return $this->redirect(array('controller' => 'commons', 'action' => 'index'));
   }

 }
public function home() {
        $this->set('title_for_layout', 'Daraji - Tutor Home');
        $this->layout='tutor';
      //  $this->Student->recursive = 0;
      //  $this->set('students', $this->paginate());
    }

public function welcome() {

         $this->layout='tutor';
	    //$first_login = true;
	     if($this->Session->check('first_login')) {
	        $first_login = $this->Session->read('first_login');
	        //$this->Session->delete('first_login');
	     }
         //debug($first_login); die();
	    // debug($this->Auth->user('last_login')); die();
	     if(!$first_login) {
	            //$this->redirect($this->Auth->loginRedirect());
	            return $this->redirect(array('action' => 'tutor_dashboard'));
	     }
}

public function market_place() {
     $this->layout='tutor';

	         if($this->request->is('post')) {
	         $id = null;
	         $postData = array();

             if (!empty($this->request->data)) {
	    	   // debug($this->request->data); die();
      		         $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
                     $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

                     //check if the record exists, then check if a valid id (not null or empty) is passed in the request,
                     //then compare the two. if they match make the assignment

                     if(!empty($this->request->data['TutorProfile']['id']) &&
                                $this->request->data['TutorProfile']['id'] != null) {
                            $id = $this->request->data['TutorProfile']['id'];
                     }

                     // if(!empty($id) && $id != null) {
                              // There should not be a record yet. So there must not be a pk
                             // throw new NotFoundException(__('Invalid Request'));
                      // }
                        $postData = $this->request->data;
                       // debug($this->request->data); die();
                        $mstatus = $postData['TutorProfile']['mktplace_status'];
                        if(!$mstatus) {

                             // debug($this->request->data); die();
                           $this->request->data['TutorProfile']['mktplace_status'] = 1;
                           $postData['TutorProfile']['mkt_place_rules'] = 1;
                           $this->request->data['TutorProfile']['profile_status_count']++ ;

                            if($postData['TutorProfile']['profile_status_count'] == 4) {
                                $postData['TutorProfile']['profile_ready'] = 1;
                            }
                       }


                        $this->{$this->modelClass}->TutorProfile->set(array(
                                  'mkt_place_rules' => $this->request->data['TutorProfile']['mkt_place_rules']
                                  ));

                        if ($this->{$this->modelClass}->TutorProfile->validates(
							  				array('fieldList' => array(
							  					'mkt_place_rules'))))
                         {
	    					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))

	    					   {
	    					        //debug('test'); die();
	    							$this->Session->setFlash
	    									(
	    												sprintf(__d('users', 'Marketplace rules Agreement has been successfully saved.')),
	    											   'default',
	    												array('class' => 'alert alert-success')
	    									 );

	    								return $this->redirect(array('action' => 'basic_profile'));
	    					  } else {
	    					      $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'Something went wrong.')),
	 						     					'default',
	 						     					 array('class' => 'alert alert-warning')
	    									 );
	    					  }
	    				 } else {
	    				     $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'You must agree to the terms and conditions. Please read the rules of the Online tutoring Market Place and check the box at the bottom of screen')),
	 						     					'default',
	 						     					 array('class' => 'alert error-message')
									     	);
                                             //debug($this->{$this->modelClass}->TutorProfile->validationErrors);    die();
	    				 }
	                }
	          }

         $mktPlaceModel =  $this->{$this->modelClass}->TutorProfile->find('first', array('conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))));
		 if(!empty($mktPlaceModel)) {
		   //debug($mktPlaceModel); die();
   	                $this->set('prpk',                    h($mktPlaceModel['TutorProfile']['id']));
                    $this->set('mkt_place_rules',         h($mktPlaceModel['TutorProfile']['mkt_place_rules']));
                    $this->set('profile_status_count',    h($mktPlaceModel['TutorProfile']['profile_status_count']));
                    $this->set('mktplace_status',         h($mktPlaceModel['TutorProfile']['mktplace_status']));

                    $this->Session->write('profile_status_count',    h($mktPlaceModel['TutorProfile']['profile_status_count']));
                    $this->Session->write('mktplace_status',         h($mktPlaceModel['TutorProfile']['mktplace_status']));

   	                $mktPlaceStatus = $mktPlaceModel['TutorProfile']['mktplace_status'];

   	                if(!empty($mktPlaceStatus) && $mktPlaceStatus) {
   	                   return $this->redirect(array('action' => 'basic_profile'));
   	                }
          }
  }

  public function manage_market_place() {
                 $this->layout='tutor';

	         if($this->request->is('post')) {
	         $id = null;
	         $postData = array();

             if (!empty($this->request->data)) {
	    	    // debug($this->request->data); die();
      		         $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
                     $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];
                     $id = $this->request->data['TutorProfile']['id'];

                     // if(!empty($id) && $id != null) {
                              // There should not be a record yet. So there must not be a pk
                             // throw new NotFoundException(__('Invalid Request'));
                      // }
                        $postData = $this->request->data;
                        $postData['TutorProfile']['mktplace_status'] = 1;
                        $postData['TutorProfile']['mkt_place_rules'] = 1;
                        $postData['TutorProfile']['profile_status_count']++;

                        $this->{$this->modelClass}->TutorProfile->set(array(
                                  'mktplace_rules' => $this->request->data['TutorProfile']['mktplace_rules']
                                  ));

                        if ($this->{$this->modelClass}->TutorProfile->validates(
							  				array('fieldList' => array(
							  					'mktplace_rules'))))
                         {
	    					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))

	    					   {
	    					        //debug('test'); die();
	    							$this->Session->setFlash
	    									(
	    												sprintf(__d('users', 'Marketplace rules has been successfully saved.')),
	    											   'default',
	    												array('class' => 'alert alert-success')
	    									 );

	    								return $this->redirect(array('action' => 'manage_basic_profile'));
	    					  } else {
	    					      $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'Something went wrong.')),
	 						     					'default',
	 						     					 array('class' => 'alert alert-warning')
	    									 );
	    					  }
	    				 } else {
	    				     $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'You must agree to the Online Marketplace rules. Please read the rules of the Online tutoring Market Place and check the box at the bottom of screen')),
	 						     					'default',
	 						     					 array('class' => 'alert error-message')
									     	);
                                             //debug($this->{$this->modelClass}->TutorProfile->validationErrors);    die();
	    				 }
	                }
	          }

                $mktPlaceModel =  $this->{$this->modelClass}->TutorProfile->find('first', array('conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))));
		if(!empty($mktPlaceModel)) {
		       //debug($mktPlaceModel); die();
   	                $this->set('prpk',   h($mktPlaceModel['TutorProfile']['id']));
                        $this->set('mkt_place_rules',   h($mktPlaceModel['TutorProfile']['mkt_place_rules']));
                        $this->set('profile_status_count',    h($mktPlaceModel['TutorProfile']['profile_status_count']));

   	                $mktPlaceStatus = $mktPlaceModel['TutorProfile']['mktplace_status'];

   	                if(!empty($mktPlaceStatus) && $mktPlaceStatus) {
   	                   return $this->redirect(array('action' => 'basic_profile'));
   	                }
   	        }
  }

public function basic_profile() {
   $this->layout='tutor';
   if($this->request->is('post')) {
         $id = null;
   	     if (!empty($this->request->data)) {
   	                // debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['TutorProfile']['id'])) {
   			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)
   			          }

                     if (empty($id) || !($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'),
                                'TutorProfile.id'  => $id))))) {

                          //error flash message
                          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
                   											   'default',
                   												array('class' => 'alert error-message')
                							       );
                          $this->redirect(array('action' => 'market_place'));

                     }

                     if ($data['TutorProfile']['id'] != $id) {
                           //Blackhole Request
                            throw new BadRequestException();
                     }
                     if(!empty($this->request->data['TutorProfile']['birthdate'])) {
                          $month = $this->request->data['TutorProfile']['birthdate']['month'];
                          $day = $this->request->data['TutorProfile']['birthdate']['day'];
                          $year = $this->request->data['TutorProfile']['birthdate']['year'];

                          $dob = $month.'/'.$day.'/'.$year;

                          //debug($dob); die();
                          $then = DateTime::createFromFormat("m/d/Y", $dob);
                          $diff = $then->diff(new DateTime());
                          $age = $diff->format("%y");
                          //debug($age); die();

                          $this->request->data['TutorProfile']['birthdate'] = $dob;
                          $this->request->data['TutorProfile']['age'] = $age;
                       }

                      $this->{$this->modelClass}->TutorProfile->set(array(
                                 // 'first_name' => $this->request->data['TutorProfile']['first_name'],
                                 // 'last_name' => $this->request->data['TutorProfile']['last_name'],
                                  'gender' => $this->request->data['TutorProfile']['gender'],
                                  'birthdate' => $this->request->data['TutorProfile']['birthdate'],
                                  'age' => $this->request->data['TutorProfile']['age'],
                                  'education' => $this->request->data['TutorProfile']['education'],
                                  'degree' => $this->request->data['TutorProfile']['degree'],
                                  'school' => $this->request->data['TutorProfile']['school'],

                                  'address_1' => $this->request->data['TutorProfile']['address_1'],
                                  'address_2' => $this->request->data['TutorProfile']['address_2'],
                                  'city' => $this->request->data['TutorProfile']['city'],
                                  'state' => $this->request->data['TutorProfile']['state'],
                                  'state_abbr' => $this->request->data['TutorProfile']['state'],
                                  'zip_code' => $this->request->data['TutorProfile']['zip_code'],

                                  'maddress_1' => $this->request->data['TutorProfile']['maddress_1'],
                                  'maddress_2' => $this->request->data['TutorProfile']['maddress_2'],
                                  'mcity' => $this->request->data['TutorProfile']['mcity'],
                                  'mstate' => $this->request->data['TutorProfile']['mstate'],
                                  'mstate_abbr' => $this->request->data['TutorProfile']['mstate'],
                                  'mzip_code' => $this->request->data['TutorProfile']['mzip_code'],
                                  'primary_phone' => $this->request->data['TutorProfile']['primary_phone'],
                                  'pphone_type' => $this->request->data['TutorProfile']['pphone_type'],
                                  'secondary_phone' => $this->request->data['TutorProfile']['secondary_phone'],
                                  'sphone_type' => $this->request->data['TutorProfile']['sphone_type']

                       ));
                      if ($this->{$this->modelClass}->TutorProfile->validates(
                              array('fieldList' => array(
                                     // 'first_name', 'last_name',
                                      'education',
                                      'gender',
                                      'birthdate',
                                      'age',
                                      'degree',
                                      'school',
                                      'address_1',
                                      'city',
                                      'state',
                                      'zip_code',
                                      'maddress_1',
									  'mcity',
									  'mstate',
                                      'mzip_code',
                                      'primary_phone',
                                      'pphone_type'
                                      //'secondary_phone',
                                      //'sphone_type'
                                      ))))
                   {
                         $postData = $this->request->data;
                         $status = $postData['TutorProfile']['basicProfile_status'];
                         if(!$status ) {

                            $postData['TutorProfile']['basicProfile_status'] = 1;
                            $postData['TutorProfile']['profile_status_count']++;

                            if($postData['TutorProfile']['profile_status_count'] == 4) {
                                $postData['TutorProfile']['profile_ready'] = 1;
                            }
                       }
   					     if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
   					        {
									$this->Session->setFlash
											(
														sprintf(__d('users', 'Basic Profile has been successfully saved.')),
													   'default',
														array('class' => 'alert alert-success')
											 );

								       return $this->redirect(array('action' => 'public_profile'));

   					         } else {
   					            $this->Session->setFlash
											(
														sprintf(__d('users', 'Basic Profile has NOT been saved. Please try again!!')),
													   'default',
														array('class' => 'alert error-message')
											 );

								       //return $this->redirect(array('action' => 'basic_profile'));
   					         }
                  } else {
                    //$this->Session->setFlash
 									//(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 											//	sprintf(__d('users', '%s', h($this->{$this->modelClass}->TutorProfile->validationErrors))),
 											  // 'default',
 											//	array('class' => 'alert error-message')
 								//	 );

                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please correct all errors below and resubmit form', true)),
                                               'default',
 												array('class' => 'alert error-message')

                                        );
                  }
               }
         }


             //set the primary key of preference table in the view and send it back as a hidden field
            $basicProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array('conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))));
   	   //debug($this->request->data[$this->modelClass]['first_name']); die();

           if(!empty($basicProfileModel)) {
               $bProfileStatus = $basicProfileModel['TutorProfile']['basicProfile_status'];
               $mktPlaceStatus = $basicProfileModel['TutorProfile']['mktplace_status'];
           }

           if(empty($mktPlaceStatus) ||  !$mktPlaceStatus) {
                    return $this->redirect(array('action' => 'market_place'));

           } else if(!empty($bProfileStatus) && $bProfileStatus) {
   	               return $this->redirect(array('action' => 'public_profile'));
           }

   	      if(!empty($basicProfileModel)) {
   	                //debug($basicProfileModel); die();
   	                $this->set('prpk',   h($basicProfileModel['TutorProfile']['id']));
   	                $this->set('fn',     h($this->Session->read('username')));
   	                $this->set('ln',     h($this->Session->read('lastname')));
                    $this->set('gn',     h($basicProfileModel['TutorProfile']['gender']));

   	                $this->set('ed',     h($basicProfileModel['TutorProfile']['education']));
   	                $this->set('degree', h($basicProfileModel['TutorProfile']['degree']));
   	                $this->set('school', h($basicProfileModel['TutorProfile']['school']));

   	                $this->set('add1',   h($basicProfileModel['TutorProfile']['address_1']));
   	                $this->set('add2',   h($basicProfileModel['TutorProfile']['address_2']));
   	                $this->set('city',   h($basicProfileModel['TutorProfile']['city']));
   	                $this->set('st',     h($basicProfileModel['TutorProfile']['state']));

                    if(!empty($basicProfileModel['TutorProfile']['zip_code'])) {
                       $this->set('zip',    h($basicProfileModel['TutorProfile']['zip_code']));
                     } else {
                        $this->set('zip',    h($this->Session->read('zip_code')));
                     }
   	                $this->set('madd1',   h($basicProfileModel['TutorProfile']['maddress_1']));
					$this->set('madd2',   h($basicProfileModel['TutorProfile']['maddress_2']));
					$this->set('mcity',   h($basicProfileModel['TutorProfile']['mcity']));
					$this->set('mst',     h($basicProfileModel['TutorProfile']['mstate']));
   	                $this->set('mzip',    h($basicProfileModel['TutorProfile']['mzip_code']));

   	                $this->set('pp',     h($basicProfileModel['TutorProfile']['primary_phone']));
   	                $this->set('sp',     h($basicProfileModel['TutorProfile']['secondary_phone']));
   	                $this->set('mhop',   h($basicProfileModel['TutorProfile']['pphone_type']));
   	                $this->set('mhos',   h($basicProfileModel['TutorProfile']['sphone_type']));
                    $this->set('profile_status_count',    h($basicProfileModel['TutorProfile']['profile_status_count']));
                    $this->set('bps',    h($basicProfileModel['TutorProfile']['basicProfile_status']));

                     $this->Session->write('bps',   h($basicProfileModel['TutorProfile']['basicProfile_status']));
                     $this->Session->write('profile_status_count',    h($basicProfileModel['TutorProfile']['profile_status_count']));


             }



}

public function public_profile() {
   $this->layout='tutor';

        if($this->request->is('post')) {
        $id = null;
        $postData = array();

   	     if (!empty($this->request->data)) {
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			             if(!empty($this->request->data['TutorProfile']['id'])) {
					            $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)
					     }

                     if (!($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'),
                                'TutorProfile.id'  => $id))))) {

                          //error flash message
                          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
                   											   'default',
                   												array('class' => 'alert error-message')
                							       );
                          $this->redirect(array('action' => 'market_place'));

                     }

                     if ($data['TutorProfile']['id'] != $id) {
                           //Blackhole Request
                            throw new BadRequestException();
                     }


                          $this->{$this->modelClass}->TutorProfile->set(array(
                                  'hourly_rate' => $this->request->data['TutorProfile']['hourly_rate'],
                                  'travel_radius' => $this->request->data['TutorProfile']['travel_radius'],
                                  'cancel_policy' => $this->request->data['TutorProfile']['cancel_policy'],
                                  'title' => $this->request->data['TutorProfile']['title'],
                                  'description' => $this->request->data['TutorProfile']['description']

                                  ));

					     if ($this->{$this->modelClass}->TutorProfile->validates(
					                               array('fieldList' => array(
					                                       'hourly_rate',
					                                       'travel_radius',
					                                       'cancel_policy',
					                                       'title',
					                                       'description'))))

                        {
                              $postData = $this->request->data;
                              $bpstatus = $postData['TutorProfile']['publicProfile_status'];
                              if(!$bpstatus) {

                                $postData['TutorProfile']['publicProfile_status'] = 1;
                                $postData['TutorProfile']['profile_status_count']++;

                                if($postData['TutorProfile']['profile_status_count'] == 4) {
                                     $postData['TutorProfile']['profile_ready'] = 1;
                                }
                       }
							 if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
									   {
											$this->Session->setFlash
													(
																sprintf(__d('users', 'Public Profile has been successfully saved.')),
															   'default',
																array('class' => 'alert alert-success')
													 );

											return $this->redirect(array('action' => 'independent_contractor__agreement'));
										 } else {
										    $this->Session->setFlash
											(
														sprintf(__d('users', 'Public Profile has Not been saved.')),
													   'default',
														array('class' => 'alert alert-success')
											 );

								           return $this->redirect(array('action' => 'public_profile'));

										 }

   					 } else {

                          $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please correct all errors belwo and Resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );
   					 }
               }
         }

          //set the primary key of preference table in the view and send it back as a hidden field

           $publicProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
		  		   					         'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
          if(!empty($publicProfileModel)) {
                $pProfileStatus = $publicProfileModel['TutorProfile']['publicProfile_status'];
                 $bProfileStatus = $publicProfileModel['TutorProfile']['basicProfile_status'];
            }
          if(empty($bProfileStatus) || !$bProfileStatus) {
                     return $this->redirect(array('action' => 'basic_profile'));
          } else if(!empty($pProfileStatus) && $pProfileStatus) {
                     return $this->redirect(array('action' => 'independent_contractor__agreement'));
          }
   	      if(!empty($publicProfileModel)) {
   	                $this->set('prpk',   $publicProfileModel['TutorProfile']['id']);
                    $this->set('hr',     h($publicProfileModel['TutorProfile']['hourly_rate']));
   	                $this->set('tr',     h($publicProfileModel['TutorProfile']['travel_radius']));
   	                $this->set('cp',     h($publicProfileModel['TutorProfile']['cancel_policy']));

   	                $this->set('title',         h($publicProfileModel['TutorProfile']['title']));
   	                $this->set('description',  h($publicProfileModel['TutorProfile']['description']));
                    $this->set('pps',    h($publicProfileModel['TutorProfile']['publicProfile_status']));
                    $this->set('profile_status_count',    h($publicProfileModel['TutorProfile']['profile_status_count']));


                     $this->Session->write('pps',   h($publicProfileModel['TutorProfile']['publicProfile_status']));
                     $this->Session->write('profile_status_count',    h($publicProfileModel['TutorProfile']['profile_status_count']));


             }

}

public function independent_contractor__agreement() {
         $this->layout='tutor';
      if($this->request->is('post')) {
                 $postData = array();
	             $id = null;
	    	     if (!empty($this->request->data)) {
	    	                  //debug($this->request->data); die();
	    	                  $this->{$this->modelClass}->TutorProfile->set($this->request->data);
	    	                  $postData = $this->request->data;
	    			          $this->request->data['TutorProfile']['tutor_id'] = $this->Auth->user('id');

	    			          if(!empty($this->request->data['TutorProfile']['id']))
	    			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)


                             if ( empty($id) ||!($data = $this->{$this->modelClass}->TutorProfile->find(
                                                        'first', array(
                                                        'conditions' => array(
                                                            'TutorProfile.tutor_id' => $this->Auth->user('id'),
                                                            'TutorProfile.id'  => $id)))))
                                 {

                                      //error flash message
                                      $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
                               											   'default',
                               												array('class' => 'alert error-message')
                            							       );
                                      $this->redirect(array('action' => 'independent_contractor__agreement'));

                                 }

                                 if ($data['TutorProfile']['id'] != $id) {
                                       //Blackhole Request
                                        throw new NotFoundException(__('Invalid ICA'));;
                                 }


	    			            //The signature consists of first and last name concatenated together

	                           //debug($postData); die();

                             $this->{$this->modelClass}->TutorProfile->set(array(
                                  'terms_of_use' => $this->request->data['TutorProfile']['terms_of_use'],
                                  'work_auth' => $this->request->data['TutorProfile']['work_auth'],
                                  'first_name' => $this->request->data['TutorProfile']['first_name'],
                                  'last_name' => $this->request->data['TutorProfile']['last_name']));


                   if ($this->{$this->modelClass}->TutorProfile->validates(
							        array('fieldList' => array(
							          'terms_of_use',
							          'work_auth',
							          'first_name',
                                      'last_name'))))
                              {
                                       $postData['TutorProfile']['tutor_signature'] = $postData['TutorProfile']['first_name'].$postData['TutorProfile']['last_name'];
	                                   if(!empty($postData['TutorProfile']['tutor_signature'])) {
	                                           $postData['TutorProfile']['signed_agreement'] = 1;
                                        }

                                      //$postData = $this->request->data;
                                      $ica_status = $postData['TutorProfile']['ica_status'];
                                      if(!$ica_status) {

                                        $postData['TutorProfile']['ica_status'] = 1;
                                        $postData['TutorProfile']['profile_status_count']++;

                                        if($postData['TutorProfile']['profile_status_count'] == 4) {
                                            $postData['TutorProfile']['profile_ready'] = 1;
                                         }
                                      }

							       if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
								  	  {
								  	     $this->Session->setFlash(sprintf(__d('users', 'Agreement details has been successfully saved.')),
								  	    							'default',
								  	    							array('class' => 'alert alert-success')
								  	    						);
								  	        return $this->redirect(array('action' => 'add_subjects'));
	    					          } else {
	    					              $this->Session->setFlash(sprintf(__d('users', 'Agreement details save failed.')),
								  	    							'default',
								  	    							array('class' => 'alert error-message')
								  	    						);

	    					          }

	                     } else {

                              $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all errors below and Re-Submit form')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );
	                          }
	               }
             }

             //set the primary key of preference table in the view and send it back as a hidden field


           $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array('conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id')) ));
             //debug($tProfileModel); die();
          if(!empty($tProfileModel)) {

            $icaStatus = $tProfileModel['TutorProfile']['ica_status'];
           }

           $pProfileStatus = $tProfileModel['TutorProfile']['publicProfile_status'];

           if(empty($pProfileStatus) || !$pProfileStatus ) {
                    return $this->redirect(array('action' => 'public_profile'));
            }else if(!empty($icaStatus) && $icaStatus) {
                    return $this->redirect(array('action' => 'add_subjects'));
            }

            if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
                    $this->set('gn',     h($tProfileModel['TutorProfile']['gender']));
                    $this->set('age',     h($tProfileModel['TutorProfile']['age']));

   	                $this->set('ed',     h($tProfileModel['TutorProfile']['education']));
   	                $this->set('degree', h($tProfileModel['TutorProfile']['degree']));
   	                $this->set('school', h($tProfileModel['TutorProfile']['school']));

   	                $this->set('add1',   h($tProfileModel['TutorProfile']['address_1']));
   	                $this->set('add2',   h($tProfileModel['TutorProfile']['address_2']));
   	                $this->set('city',   h($tProfileModel['TutorProfile']['city']));
   	                $this->set('st',     h($tProfileModel['TutorProfile']['state']));

                     if(!empty($tProfileModel['TutorProfile']['zip_code'])) {
                        //debug("teststs");
                       $this->set('zip',    h($tProfileModel['TutorProfile']['zip_code']));
                     } else {
                        $this->set('zip',    h($this->Session->read('zip_code')));
                     }
   	                $this->set('madd1',   h($tProfileModel['TutorProfile']['maddress_1']));
					$this->set('madd2',   h($tProfileModel['TutorProfile']['maddress_2']));
					$this->set('mcity',   h($tProfileModel['TutorProfile']['mcity']));
					$this->set('mst',     h($tProfileModel['TutorProfile']['mstate']));
   	                $this->set('mzip',    h($tProfileModel['TutorProfile']['mzip_code']));

               	    $this->set('pp',     h($tProfileModel['TutorProfile']['primary_phone']));
   	                $this->set('sp',     h($tProfileModel['TutorProfile']['secondary_phone']));
   	                $this->set('mhop',   h($tProfileModel['TutorProfile']['pphone_type']));
   	                $this->set('mhos',   h($tProfileModel['TutorProfile']['sphone_type']));

                    //$this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));

                    $this->set('pics',    h($tProfileModel['TutorProfile']['profilePic_status']));
                    $this->set('sub',    h($tProfileModel['TutorProfile']['subj_status']));

                   // $this->set('ica',    h($tProfileModel['TutorProfile']['ica_status']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
             }
   }

public function add_subjects() {

    $this->layout='tutor';
	$postData = array();
    $category = new Categorie();

if($this->request->is('post')) { //1

  if(!empty($this->request->data)) { //2
     $conditions = array(
	         'tutor_id' => $this->Auth->user('id'),
		 'name' => $this->request->data['TutorSubjectCat']['category'],
         'category_id' => $this->request->data['TutorSubjectCat']['category_id']
	 );
	 if (!$this->{$this->modelClass}->TutorCategorie->hasAny($conditions)) {
         $this->{$this->modelClass}->TutorCategorie->set($conditions);
         $this->{$this->modelClass}->TutorCategorie->save() ;
      }

	  if ($this->{$this->modelClass}->TutorCategorie->hasAny($conditions)){  //3
		  $categoryRow = $this->{$this->modelClass}->TutorCategorie->find
			   		     	      ( 'first',
			   		     	         array('field' => 'tutor_id',
			   		     	        'value' => $this->Auth->user('id')
		     	                 ));

		  $cat_id =  $categoryRow['TutorCategorie']['id'];
	      foreach ($this->request->data['TutorSubject'] as $key => $value) { //4
            // debug($value); die();
           //  if ($this->{$this->modelClass}->TutorSubject->validates(array
  	                               //  ('fieldList' => array(
  	                                //  'TutorSubject' .'.' . $key
  	                                //  ))))
  	       // {

             if($value == '1') { // Even though all Subjects on the Form come through, we only store the ones selected (checked) by user
				 foreach ($this->request->data['TutorSubjectIds'] as $cle => $val) {
					    //$cle = strtolower(substr($key, 0, 5));
   	                    $subject_row = $category->{'Subject'}->find('all', array('conditions' => array('Subject'.'.'.'name' => $key)));
                       // debug($subject_row); die();
                        $cle = $subject_row[0]['Subject']['subject_id'];
					    //debug($cle); die();
						$conditions = array(
								'tutor_id' => $this->Auth->user('id'),
								'tutor_categorie_id' => $cat_id,
								'subject_name' => $key,
								'subject_id' => $cle, // french for key --:)
								'subject_category_name' => $this->request->data['TutorSubjectCat']['category'],
								'subject_category_id' => $this->request->data['TutorSubjectCat']['category_id'],
								'delete_status' => 'N',
								'approval_status' => 'N/A',
								'searchable_status' => 0,
								'credentials_status' => 0   //0=Not submiited, 1="Submitted"
						 );
						 unset($this->request->data['TutorSubjectIds'][$cle]); //already processed, removed from array.
						 break;  //we are breaking cause we need to process with the conditions we just built first
				   } // end foreach

				   if (!$this->{$this->modelClass}->TutorSubject->hasAny($conditions)){

				        $this->{$this->modelClass}->TutorSubject->create();
					$this->{$this->modelClass}->TutorSubject->set($conditions);
                                       // debug($this->request->data); die();
					if($this->{$this->modelClass}->TutorSubject->save($this->request->data, array('validate' => false))) {
							next($this->request->data);
					 } else {
								 $this->Session->setFlash(sprintf(__d('users', 'Subjects have NOT been Saved.')),
																	  'default',
																	   array('class' => 'alert alert-warming')
														 );
							   return $this->redirect(array('action' => 'add_subjects'));
						 }


					} else {
							// throw new CakeException(__d('cake_dev', 'Fail to save!!!.'));
							  $this->Session->setFlash(sprintf(__d('users', 'One or more subjects have previously been added and were skipped!!! ')),
																	  'default',
																	   array('class' => 'alert alert-warning')
													  );
							return $this->redirect(array('action' => 'add_subjects'));
					  }
				} // end if ($value == 1)
			}  // end foreach
		 }
	   }

	      $this->Session->setFlash(sprintf(__d('users', 'Subjects have been successfully added to your offering. Pending reviews and approval')),
	   						    'default',
	   						    array('class' => 'alert alert-success'));

        // debug($this->request->data); die();
         $postData = array(); //$this->request->data;
         $status = $this->request->data['TutorProfile']['subj_status'];
         $id = $this->request->data['TutorProfile']['id'];
         if(empty($status) || !$status ) {
             // debug($this->request->data); die();

                $postData['TutorProfile']['subj_status'] = 1; //$this->request->data['TutorProfile']['subj_status'];
                $postData['TutorProfile']['profile_status_count'] = $this->request->data['TutorProfile']['profile_status_count'];
                $postData['TutorProfile']['basicProfile_status'] = $this->request->data['TutorProfile']['basicProfile_status'];
                $postData['TutorProfile']['publicProfile_status'] = $this->request->data['TutorProfile']['publicProfile_status'];

                $postData['TutorProfile']['profile_status_count']++;
                //if($postData['TutorProfile']['profile_status_count'] == 4) {
                 if($postData['TutorProfile']['basicProfile_status'] == 1 &&
                    $postData['TutorProfile']['publicProfile_status'] == 1) {
                       $postData['TutorProfile']['profile_ready'] = 1;
               }

         // debug($postData); die();
          $this->{$this->modelClass}->TutorProfile->set(array(
                                  'subj_status' => $this->request->data['TutorProfile']['subj_status'],
                                  'profile_status_count' => $this->request->data['TutorProfile']['profile_status_count'],
                                  ));

           $this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData);
          }



        $this->Session->write('tabName', $this->request->data['TutorSubjectCat']['category']);
		return $this->redirect(array('action' => 'manage_subjects'));
	 }


	   $options['joins'] = array(
	       array('table' => 'tutor_subjects',
	           'alias' => 'TutorSubject',
	           'type' => 'LEFT',
	           'foreignKey' => false,
	           'conditions' => array(
	               'TutorSubject.subject_id = Subject.subject_id',
	           ),
	       )
	   );

	    $options2['joins'] = array(
	   	       array('table' => 'subjects',
	   	           'alias' => 'Subject',
	   	           'type' => 'RIGHT',
	   	           'conditions' => array(
	   	               'Subject.subject_id != TutorSubject.subject_id',
	   	           ),
	   	       )
	   );

       $cats = $category->find('all', array('order' => array('Categorie.name ASC')));

	   //$category->find('all', array('conditions' => array('name' => 'Math')));  //$category->find('all', array('conditions' => array('name' => 'Math')));
	   $this->set('categories',$cats);
	   $subjects = $category->{'Subject'}->find('all', array('order' => array('Subject.name ASC')));

	  // $subjects = $category->{'Subject'}->find('all', $options);
	   //$subjects = $this->{$this->modelClass}->TutorSubject->find('all', $options2);

       //debug($subjects); die();
       $i=0;
       $viewData = $subjects; //();
       foreach($subjects as $subject) {
			$conditions = array(
				 'tutor_id' => $this->Auth->user('id'),
				 'subject_name' => $subject['Subject']['name'],
				 'subject_id' => $subject['Subject']['subject_id']
			);
			//debug($conditions); die();
		  if($this->{$this->modelClass}->TutorSubject->hasAny($conditions)) {
				//debug($conditions); die();
				//unset($subjects[$subject['Subject']['name']]);
				//unset($subjects[$subject['Subject']['subject_id']]);
				//debug($viewData[$i]['Subject']['name']);

				unset($viewData[$i]['Subject']);
				unset($viewData[$i]['Subject']);

				//debug($viewData); die();
				//$subjects = $viewData;
		  }
		  $i++;
		  $subjects = $viewData;
	  }
	  //debug($viewData); die();
	  $this->set('subjects', $subjects);

       $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
			  		  		  	'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));

            //debug($tProfileModel) ; die();
       $tSubjectModel = $this->{$this->modelClass}->TutorSubject->find('first', array(
			  		  		  	'conditions' => array('TutorSubject.tutor_id' => $this->Auth->user('id'))
                     ));

      //debug($tSubjectModel); die();

       $tImageModel = $this->{$this->modelClass}->TutorImage->find('first', array(
			  		  		  	'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))
                     ));

        if(!empty($tSubjectModel)) {
            //debug("here");
            $this->set('sub',    1);

        }

        if(!empty($tImageModel)) {
            $this->set('pics',    '1');

        }
      if(!empty($tProfileModel)) {
        //debug($tProfileModel['TutorProfile']['profile_status_count']);

                    $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
                     //$this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));

                   // $this->set('pics',    h($tProfileModel['TutorProfile']['profilePic_status']));
                   // $this->set('sub',    h($tProfileModel['TutorProfile']['subj_status']));

             }

  if(!empty($this->params['url']['cat_id'])) {
    $this->Session->write('open_cat' , $this->params['url']['cat_id']);
  }

}

public function add_location() {

    $this->layout='tutor';

if($this->request->is('post')) {

         $id = null;
   	     if (!empty($this->request->data)) {
   	       //debug($this->request->data); die();
   	          		  // $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');

                       $this->request->data['TutorLocation']['tutor_id'] = $this->Auth->user('id'); //$this->request->data[$this->modelClass]['id'];
                       $this->{$this->modelClass}->TutorLocation->set(array(
                                  'location_name' => $this->request->data['TutorLocation']['name'],
                                  'address_1' => $this->request->data['TutorLocation']['address_1'],
                                  'address_2' => $this->request->data['TutorLocation']['address_2'],
                                  'city' => $this->request->data['TutorLocation']['city'],
                                  'state' => $this->request->data['TutorLocation']['state'],
                                  'state_abbr' => $this->request->data['TutorLocation']['state'],
                                  'zip_code' => $this->request->data['TutorLocation']['zip_code'],
                                  'distance' => $this->request->data['TutorLocation']['distance']
                        ));

                      $this->request->data['TutorLocation']['first_name'] =  $this->Session->read('username');
                      $this->request->data['TutorLocation']['last_name'] =  $this->Session->read('lastname');
                      $this->request->data['TutorLocation']['state_abbr'] = $this->request->data['TutorLocation']['state'];

                      $location_id = uniqid(rand(), true);
                      $result = String::tokenize($location_id, '.');

                     // debug($result); //die();
                      $location_id  = $this->request->data['TutorLocation']['state'].$result[1];

                     // debug($location_id);// die();
                      $this->request->data['TutorLocation']['location_id'] = $location_id;


         //debug($this->request->data); die();
         if($this->{$this->modelClass}->TutorLocation->validates(array('fieldList' => array(
                                                            'location_name',
                                                            'address_1',
                                                            'city',
                                                            'state',
                                                            'zip_code'))))
         {


                     // $postData = $this->request->data;
                     // debug($this->request->data); //die();
   					  if($this->{$this->modelClass}->TutorLocation->saveTutorLocation($id, $this->request->data))
   					   {
   					       // debug("hehe"); die();
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Location has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

                             $this->Session->write('location_name', $this->request->data['TutorLocation']['name']);

                             $this->redirect(array('action' => 'schedule_lesson'));

   					  } else {
   					     //debug($this->{$this->modelClass}->TutorLocation->validationErrors); die();
   					        $this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 												sprintf(__d('users', 'Location Save Failed')),
 											   'default',
 												array('class' => 'alert error-message')
 									 );
                            $this->redirect(array('action' => 'schedule_lesson'));

   					  }
               } else {


                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );

                           $this->redirect(array('action' => 'schedule_lesson'));

               }
         }
    }


             $tutor_locations = $this->{$this->modelClass}->TutorLocation->find('all', array(
			  		  		  	'conditions' => array('TutorLocation.tutor_id' => $this->Auth->user('id'))
                     ));
                        // debug("i am here");
               // debug($tutor_locations); die();
              //Configure::write('tutor_locations', $tutor_locations);
              $this->Session->write('tutor_locations', $tutor_locations);
             // debug($this->{$this->modelClass}->TutorLocation->validationErrors); //die();
              // debug($this->Tutor->validationErrors); //die();
              $this->redirect(array('action' => 'schedule_lesson'));


}

public function manage_basic_profile() {
   $this->layout='tutor';
   $go_public =0;

if($this->request->is('post')) {
        $id = null;

   	     if (!empty($this->request->data)) {
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['TutorProfile']['id']))
   			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)


                     if (!($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'),
                                'TutorProfile.id'  => $id))))) {

                          //error flash message
                          /** $this->Session->setFlash(sprintf(__d('users', '<center>You have attempted to update your profile before setting it up!!!! Profile must be set up in the following order:<br /><br />
                          1.<b>Market Place Rules</b> (Read & Sign) <br /> 2. <b>Basic Profile</b> <br /> 3.<b>Public Profile</b> <br /> 4.<b>Tutor Contract Agreement (Read & Sign)</b> <br /><br /> Click on <b>Market Place Rules</b> below to start.</center>')),
                   											   'default',
                   												array('class' => 'alert error-message'));
                           **/
                          $this->Session->setFlash('There were errors. Please try again!', 'custom_msg');

                          $this->redirect(array('action' => 'manage_basic_profile'));

                     }


                    if ($data['TutorProfile']['id'] != $id) {
                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Profile'));
                     }

                     $dob = "";
                     $age = "";
       if(!empty($this->request->data['TutorProfile']['birthdate'])) {
                  $month = $this->request->data['TutorProfile']['birthdate']['month'];
                  $day = $this->request->data['TutorProfile']['birthdate']['day'];
                  $year = $this->request->data['TutorProfile']['birthdate']['year'];

                  $dob = $month.'/'.$day.'/'.$year;

                  //debug($dob); die();
                  $then = DateTime::createFromFormat("m/d/Y", $dob);
                  $diff = $then->diff(new DateTime());
                  $age = $diff->format("%y");
                  //debug($age); die();

                  $this->request->data['TutorProfile']['birthdate'] = $dob;
                  $this->request->data['TutorProfile']['age'] = $age;
               }
                  $this->{$this->modelClass}->TutorProfile->set(array(
                                 // 'first_name' => $this->request->data['TutorProfile']['first_name'],
                                  //'last_name' => $this->request->data['TutorProfile']['last_name'],
                                  'gender' => $this->request->data['TutorProfile']['gender'],
                                  'age' => $this->request->data['TutorProfile']['age'],
                                  'education' => $this->request->data['TutorProfile']['education'],
                                  'degree' => $this->request->data['TutorProfile']['degree'],
                                  'birthdate' => $this->request->data['TutorProfile']['birthdate'],
                                  'school' => $this->request->data['TutorProfile']['school'],

                                  'address_1' => $this->request->data['TutorProfile']['address_1'],
                                  'address_2' => $this->request->data['TutorProfile']['address_2'],
                                  'city' => $this->request->data['TutorProfile']['city'],
                                  'state' => $this->request->data['TutorProfile']['state'],
                                  'state_abbr' => $this->request->data['TutorProfile']['state'],
                                  'zip_code' => $this->request->data['TutorProfile']['zip_code'],

                                  //'maddress_1' => $this->request->data['TutorProfile']['maddress_1'],
                                  //'maddress_2' => $this->request->data['TutorProfile']['maddress_2'],
                                  //'mcity' => $this->request->data['TutorProfile']['mcity'],
                                 // 'mstate' => $this->request->data['TutorProfile']['mstate'],
                                 //'mstate_abbr' => $this->request->data['TutorProfile']['mstate'],
                                 // 'mzip_code' => $this->request->data['TutorProfile']['mzip_code'],

                                  'primary_phone' => $this->request->data['TutorProfile']['primary_phone'],
                                  'secondary_phone' => $this->request->data['TutorProfile']['secondary_phone'],
                                  'pphone_type' => $this->request->data['TutorProfile']['pphone_type'],
                                  'sphone_type' => $this->request->data['TutorProfile']['sphone_type']


           ));

           //debug("test val"); die();
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array(
                                                            //'first_name',
                                                            //'last_name',
                                                            'gender',
                                                            //'age',
					                                        'education',
                                                            'degree',
                                                            //'birthdate',
                                                            'school',
                                                            'address_1',
                                                            'city','state',
                                                            'zip_code',
                                                           // 'maddress_1',
                                                            //'mcity',
                                                            //'mstate',
                                                            //'mzip_code',
                                                            'primary_phone',
                                                            'pphone_type'

                                                            ))))
                {

                        //$postData = $this->request->data;

                         $postData = $this->request->data;
                         $status = $this->request->data['TutorProfile']['basicProfile_status'];

                         if(!$status ) {

                            $this->request->data['TutorProfile']['basicProfile_status'] = 1;
                            $this->request->data['TutorProfile']['profile_status_count']++;

                           // if($postData['TutorProfile']['profile_status_count'] == 4) {
                            //We check to see if public profile is ready so we can
                            //mark the entire profile ready to be searched by students..

                            if($postData['TutorProfile']['publicProfile_status'] == 1) {
                                $postData['TutorProfile']['profile_ready'] = 1;
                            } else {
                                $go_public = 1;
                            }
                       }
   					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Basic Profile has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
                      if($go_public) {
                       $this->redirect(array('action' => 'manage_public_profile'));
                       }
                       /*** if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) &&
                                         !$this->request->data['TutorProfile']['publicProfile_status'])

                             { //&& basic_profile is already taken care of
                                        $this->redirect(array('action' => 'manage_public_profile'));

                             } else if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) &&
                                         $this->request->data['TutorProfile']['publicProfile_status']) {

                                            $this->redirect(array('action' => 'independent_contractor__agreement'));
                             }
                       ***/
   					  } else {
   					        $this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 												sprintf(__d('users', 'Basic Profile Save Failed')),
 											   'default',
 												array('class' => 'alert error-message')
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


           $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
			  		  		  	'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));

            //debug($tProfileModel) ; die();
       $tSubjectModel = $this->{$this->modelClass}->TutorSubject->find('first', array(
			  		  		  	'conditions' => array('TutorSubject.tutor_id' => $this->Auth->user('id'))
                     ));

      //debug($tSubjectModel); die();

       $tImageModel = $this->{$this->modelClass}->TutorImage->find('first', array(
			  		  		  	'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))
                     ));

        if(!empty($tSubjectModel)) {
            //debug("here");
            $this->set('sub',    1);

        }

        if(!empty($tImageModel)) {
            $this->set('pics',    '1');

        }

          $this->set('fn',     h($this->Session->read('username')));
          $this->set('ln',     h($this->Session->read('lastname')));

   	      if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
                    $this->set('gn',     h($tProfileModel['TutorProfile']['gender']));
                    $this->set('age',     h($tProfileModel['TutorProfile']['age']));

   	                $this->set('ed',     h($tProfileModel['TutorProfile']['education']));
   	                $this->set('degree', h($tProfileModel['TutorProfile']['degree']));
   	                $this->set('school', h($tProfileModel['TutorProfile']['school']));

   	                $this->set('add1',   h($tProfileModel['TutorProfile']['address_1']));
   	                $this->set('add2',   h($tProfileModel['TutorProfile']['address_2']));
   	                $this->set('city',   h($tProfileModel['TutorProfile']['city']));
   	                $this->set('st',     h($tProfileModel['TutorProfile']['state']));
   	               // $this->set('fn',     h($tProfileModel['TutorProfile']['first_name']));
   	               // $this->set('ln',     h($tProfileModel['TutorProfile']['last_name']));

                     if(!empty($tProfileModel['TutorProfile']['zip_code'])) {
                        //debug("teststs");
                       $this->set('zip',    h($tProfileModel['TutorProfile']['zip_code']));
                     } else {
                        $this->set('zip',    h($this->Session->read('zip_code')));
                     }
   	                $this->set('madd1',   h($tProfileModel['TutorProfile']['maddress_1']));
					$this->set('madd2',   h($tProfileModel['TutorProfile']['maddress_2']));
					$this->set('mcity',   h($tProfileModel['TutorProfile']['mcity']));
					$this->set('mst',     h($tProfileModel['TutorProfile']['mstate']));
   	                $this->set('mzip',    h($tProfileModel['TutorProfile']['mzip_code']));

   	                $this->set('pp',     h($tProfileModel['TutorProfile']['primary_phone']));
   	                $this->set('sp',     h($tProfileModel['TutorProfile']['secondary_phone']));
   	                $this->set('mhop',   h($tProfileModel['TutorProfile']['pphone_type']));
   	                $this->set('mhos',   h($tProfileModel['TutorProfile']['sphone_type']));

                    //$this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));

                    $this->set('pics',    h($tProfileModel['TutorProfile']['profilePic_status']));
                    $this->set('sub',    h($tProfileModel['TutorProfile']['subj_status']));

                   // $this->set('ica',    h($tProfileModel['TutorProfile']['ica_status']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
             }

}


public function manage_public_profile() {
   $this->layout='tutor';
   $go_basic = 0;

        if($this->request->is('post')) {
        $id = null;

   	     if (!empty($this->request->data)) {
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['TutorProfile']['id']))
   			                   $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)



                     if (!($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'),
                                'TutorProfile.id'  => $id)))) ||
                                $data['TutorProfile']['id'] != $id
                        ) {

                           //error flash message
                          /** $this->Session->setFlash(sprintf(__d('users', '<center>You have attempted to update your profile before setting it up!!!! Profile must be set up in the following order:<br /><br />
                          1.<b>Market Place Rules</b> (Read & Sign) <br /> 2. <b>Basic Profile</b> <br /> 3.<b>Public Profile</b> <br /> 4.<b>Tutor Contract Agreement (Read & Sign)</b> <br /><br /> Click on <b>Market Place Rules</b> below to start.</center>')),
                   											   'default',
                   												array('class' => 'alert error-message'));
                           **/
                          $this->Session->setFlash('There were errors. Please try again!', 'custom_msg');

                          $this->redirect(array('action' => 'manage_public_profile'));

                      }

                    if ($data['TutorProfile']['id'] != $id) {
                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Profile'));
                     }

                      $this->{$this->modelClass}->TutorProfile->set(array(
                                  'hourly_rate' => $this->request->data['TutorProfile']['hourly_rate'],
                                  'travel_radius' => $this->request->data['TutorProfile']['travel_radius'],
                                  'cancel_policy' => $this->request->data['TutorProfile']['cancel_policy'],
                                  'title' => $this->request->data['TutorProfile']['title'],
                                  'description' => $this->request->data['TutorProfile']['description']

                                  ));


                if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array(
					                                        'hourly_rate','travel_radius',
                                                            'cancel_policy','title','description'))))
                {


                         $postData = $this->request->data;
                         $status = $this->request->data['TutorProfile']['publicProfile_status'];
                         if(!$status ) {

                            $this->request->data['TutorProfile']['publicProfile_status'] = 1;
                            $this->request->data['TutorProfile']['profile_status_count']++;

                            // if($postData['TutorProfile']['profile_status_count'] == 4) {
                                //We check to see if basic profile is ready so we can
                            //mark the entire profile ready to be searched by students..

                             if($postData['TutorProfile']['basicProfile_status'] == 1) {
                                $postData['TutorProfile']['profile_ready'] = 1;
                              } else {
                                $go_basic = 1;
                            }
                       }

   					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Public Profile has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
                        if($go_basic) {
                            $this->redirect(array('action' => 'manage_basic_profile'));
                        }

                            /** if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) &&
                                         !$this->request->data['TutorProfile']['basicProfile_status'])

                             { //&& basic_profile is already taken care of
                                        $this->redirect(array('action' => 'manage_basic_profile'));

                             } else if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) &&
                                         $this->request->data['TutorProfile']['basicProfile_status']) {

                                            $this->redirect(array('action' => 'independent_contractor__agreement'));
                             }
                             **/

   					  } else {
   					     $this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
										        sprintf(__d('users', 'Public Profile Save Failed. Please try Again!!')),
 											   'default',
 												array('class' => 'alert error-message')
 									 );

   					  }
                  } else {


                          $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct All Errors below and Resubmit Form')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );
                     }

               }
         }

     //debug($tProfileModel) ; die();
       $tSubjectModel = $this->{$this->modelClass}->TutorSubject->find('first', array(
			  		  		  	'conditions' => array('TutorSubject.tutor_id' => $this->Auth->user('id'))
                     ));

      //debug($tSubjectModel); die();

       $tImageModel = $this->{$this->modelClass}->TutorImage->find('first', array(
			  		  		  	'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))
                     ));

        if(!empty($tSubjectModel)) {
            //debug("here");
            $this->set('sub',    1);

        }

        if(!empty($tImageModel)) {
            $this->set('pics',    '1');

        }
   	      $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
			  		  		  	'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
   	      if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
   	                $this->set('hr',     h($tProfileModel['TutorProfile']['hourly_rate']));
   	                $this->set('tr',     h($tProfileModel['TutorProfile']['travel_radius']));
   	                $this->set('cp',     h($tProfileModel['TutorProfile']['cancel_policy']));

   	                $this->set('title',         h($tProfileModel['TutorProfile']['title']));
   	                $this->set('description',  h($tProfileModel['TutorProfile']['description']));

                    //$this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));
                    //$this->set('ica',    h($tProfileModel['TutorProfile']['ica_status']));
                     $this->set('pics',    h($tProfileModel['TutorProfile']['profilePic_status']));
                    $this->set('sub',    h($tProfileModel['TutorProfile']['subj_status']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
                    //debug($tProfileModel['TutorProfile']['profile_status_count']); die();


             }

}

public function manage_photos() {

    $this->layout='tutor';
    $id = null;
   // debug($_SERVER['DOCUMENT_ROOT']) ; die();
  if ($this->request->is('post')) {
        //debug($this->request->data); die();

       // move_uploaded_file(
       // $this->data['TutorImage']['image']['tmp_name'],
       // $_SERVER['DOCUMENT_ROOT'] . '/files/uploads' . $this->data['TutorImage']['image']['name']
       // );

        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['TutorImage']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

          if(!empty($this->request->data['TutorImage']['id']))
                  $id = $this->request->data['TutorImage']['id'];     //the Pk of Associated model (TutorImage)

      // debug($this->request->data['TutorImage']); die();
         $conditions = array(
				'tutor_id' => $this->Auth->user('id'),
                //'image' => '/files/uploads/'. $this->request->data['TutorImage']['image']['name']
                 //'image' => $this->request->data['TutorImage']['image']['name']
                'image' => 'https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimg/'.$this->request->data['TutorImage']['image']['name']

              //https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimg/natashal3--2-.png
			);

     // debug($conditions); die();
   if (!$this->{$this->modelClass}->TutorImage->hasAny($conditions)){
        $this->request->data['TutorImage']['created'] = date('Y-m-d H:i:s');
        $this->request->data['TutorImage']['status'] = 0;
        $this->request->data['TutorImage']['featured'] = 0;

       for($i=1; $i<5; $i++) { //do not like this but will do for now. There has got to be a better way
            $data = $this->{$this->modelClass}->TutorImage->find(
                            'first', array(
                    		//'order' => array('TutorImage.data_id' => 'DESC'),
                            'conditions' => array(
                                'TutorImage.tutor_id' => $this->Auth->user('id'),
                                'TutorImage.data_id'  => $i))); //,
              		            //'limit' => 1));
        	if (!$data) {
        		$this->request->data['TutorImage']['data_id'] = $i; //$data['TutorImage']['data_id'] + 1;
                break;
        	} else {
               $this->request->data['TutorImage']['data_id'] = $data['TutorImage']['data_id']; //1;
        	}

       }



       $this->{$this->modelClass}->TutorImage->create();
       //$this->{$this->modelClass}->TutorImage->set($conditions);
       //()
       if ($this->{$this->modelClass}->TutorImage->save($this->request->data)) {

            {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile Photo has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
            }
       }

        //debug($this->request->data); die();
         $postData = array(); //$this->request->data;
         $status = $this->request->data['TutorProfile']['profilePic_status'];
         $id = $this->request->data['TutorProfile']['id'];
         if(empty($status) || !$status ) {

                $postData['TutorProfile']['profilePic_status'] = 1; //$this->request->data['TutorProfile']['profilePic_status'];
                $postData['TutorProfile']['profile_status_count'] = $this->request->data['TutorProfile']['profile_status_count'];
                $postData['TutorProfile']['basicProfile_status'] = $this->request->data['TutorProfile']['basicProfile_status'];
                $postData['TutorProfile']['publicProfile_status'] = $this->request->data['TutorProfile']['publicProfile_status'];

                $postData['TutorProfile']['profile_status_count']++;
               // if($postData['TutorProfile']['profile_status_count'] >= 4) {
                if($postData['TutorProfile']['basicProfile_status'] == 1 &&
                $postData['TutorProfile']['publicProfile_status'] == 1) {
                     $postData['TutorProfile']['profile_ready'] = 1;

               }

         // debug($postData); die();
          $this->{$this->modelClass}->TutorProfile->set(array(
                                  'profilePic_status' => $this->request->data['TutorProfile']['profilePic_status'],
                                  'profile_status_count' => $this->request->data['TutorProfile']['profile_status_count'],
                                  ));

           $this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData);
          }

    } else {

         //debug($this->Recipe->validationErrors);

        $this->Session->setFlash
   									(
   												sprintf(__d('users', 'A photo of same name already exists.')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
        }

        //$this->redirect($this->referer());
         //$this->redirect(array('controller' => 'tutors' , 'action' => 'manage_photos'));

    }

     // $images = $this->{$this->modelClass}->TutorImage->find(
                          // 'all', array('conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))));
      //$id_array =  array(0,1);
      //$order = "FIELD(id,". implode(“, “, $id_array).")";
      $images = $this->{$this->modelClass}->TutorImage->find(
                          'all', array(
                           'order' => array('TutorImage.status' => 'DESC', 'TutorImage.featured' => 'DESC' ),
                           'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id')
                           //'order' => array('TutorImage.status' => 'DESC')
                           )));

                          // array('order' => array('Subject.name ASC')


      $this->set('images',$images);
      $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
			  		  		  	'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));

            //debug($tProfileModel) ; die();
       $tSubjectModel = $this->{$this->modelClass}->TutorSubject->find('first', array(
			  		  		  	'conditions' => array('TutorSubject.tutor_id' => $this->Auth->user('id'))
                     ));

      //debug($tSubjectModel); die();

       $tImageModel = $this->{$this->modelClass}->TutorImage->find('first', array(
			  		  		  	'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))
                     ));

        if(!empty($tSubjectModel)) {
            //debug("here");
            $this->set('sub',    1);

        }

        if(!empty($tImageModel)) {
            $this->set('pics',    '1');

        }
      if(!empty($tProfileModel)) {
        //debug($tProfileModel['TutorProfile']['profile_status_count']);

                    $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
                     //$this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));

                   // $this->set('pics',    h($tProfileModel['TutorProfile']['profilePic_status']));
                   // $this->set('sub',    h($tProfileModel['TutorProfile']['subj_status']));

             }
}

public function upload_photo() {

    $this->layout='tutor';
    $id = null;
   // debug($_SERVER['DOCUMENT_ROOT']) ; die();
  if ($this->request->is('post')) {
        //debug($this->request->data); die();

       // move_uploaded_file(
       // $this->data['TutorImage']['image']['tmp_name'],
       // $_SERVER['DOCUMENT_ROOT'] . '/files/uploads' . $this->data['TutorImage']['image']['name']
       // );

        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['TutorImage']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

          if(!empty($this->request->data['TutorImage']['id']))
                  $id = $this->request->data['TutorImage']['id'];     //the Pk of Associated model (TutorImage)


         $conditions = array(
				'tutor_id' => $this->Auth->user('id'),
                //'image' => '/files/uploads/'. $this->request->data['TutorImage']['image']['name']
                 //'image' => $this->request->data['TutorImage']['image']['name']
                'image' => 'https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimg/'. $this->request->data['TutorImage']['image']['name']
			);
           // https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimg/Cmum.jpg
            //https://s3-us-west-2.amazonaws.com/www.daraji.com/images/users/Chrysanthemum.jpg

   if (!$this->{$this->modelClass}->TutorImage->hasAny($conditions)){
        $this->request->data['TutorImage']['created'] = date('Y-m-d H:i:s');
        $this->request->data['TutorImage']['status'] = 0;
        $this->request->data['TutorImage']['featured'] = 0;

       for($i=1; $i<5; $i++) { //do not like this but will do for now. There has got to be a better way
            $data = $this->{$this->modelClass}->TutorImage->find(
                            'first', array(
                    		//'order' => array('TutorImage.data_id' => 'DESC'),
                            'conditions' => array(
                                'TutorImage.tutor_id' => $this->Auth->user('id'),
                                'TutorImage.data_id'  => $i))); //,
              		            //'limit' => 1));
        	if (!$data) {
        		$this->request->data['TutorImage']['data_id'] = $i; //$data['TutorImage']['data_id'] + 1;
                break;
        	} else {
               $this->request->data['TutorImage']['data_id'] = $data['TutorImage']['data_id']; //1;
        	}

       }

       $this->{$this->modelClass}->TutorImage->create();
       //$this->{$this->modelClass}->TutorImage->set($conditions);
       if ($this->{$this->modelClass}->TutorImage->save($this->request->data)) {

            {
                          // debug("here"); die();
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile Photo has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

                                   //  return $this->redirect(array('action' => 'upload_photo'));
            }
       }

    } else {

         //debug($this->Recipe->validationErrors);

        $this->Session->setFlash
   									(
   												sprintf(__d('users', 'A photo of same name already exists.')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
        }


    }

     // $images = $this->{$this->modelClass}->TutorImage->find(
                          // 'all', array('conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))));
      //$id_array =  array(0,1);
      //$order = "FIELD(id,". implode(“, “, $id_array).")";
      $images = $this->{$this->modelClass}->TutorImage->find(
                          'all', array(
                           'order' => array('TutorImage.status' => 'DESC', 'TutorImage.featured' => 'DESC' ),
                           'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id')
                           //'order' => array('TutorImage.status' => 'DESC')
                           )));

                          // array('order' => array('Subject.name ASC')


      $this->set('images',$images);
      //debug($images); die();

      // if(!empty($tProfilePhotoModel)) {
        //   $this->set('ppk',  $tProfilePhotoModel['TutorImage']['id']);
        //   $this->set('image',  $tProfilePhotoModel['TutorImage']['image']);
       // }
}

public function update_entry($datastring=null) {

     //if (!$this->ResquestHandler->isAjax()) {
     if (!$this->request->is('ajax')) {
        throw new MethodNotAllowedException();
     }

     $this->layout = 'ajax';
     $this->autoRender = false;

     $data = $this->request->data;
     // debug($this->request->data); die();
     //debug($data['editAct']);   die();
    switch ($this->request->data['editAct']) {

    case 'editName':
     //debug($this->request->data); die();
         $this->{$this->modelClass}->TutorProfile->set(array(
                 'first_name' => $this->request->data['first_name'],
                 'last_name' => $this->request->data['last_name'],
         ));

          if(!empty($this->request->data) )  {
            // debug("hereee");
            //debug($this->request->data['first_name']); die();
            if( $this->{$this->modelClass}->TutorProfile->validates(
                      array('fieldList' => array('first_name','last_name'))))
             {
               // debug("here"); die();
             $this->{$this->modelClass}->TutorProfile->id = $this->request->data['uid'];
             $this->{$this->modelClass}->TutorProfile->saveField('first_name', $this->request->data['first_name']);
             $this->{$this->modelClass}->TutorProfile->saveField('last_name', $this->request->data['last_name']);

             $this->{$this->modelClass}->id = $this->Auth->user('id'); //$this->request->data['uid'];
             $this->{$this->modelClass}->saveField('first_name', $this->request->data['first_name']);
             $this->{$this->modelClass}->saveField('last_name', $this->request->data['last_name']);

           /** $this->{$this->modelClass}->TutorProfile->updateAll(
                          array('TutorProfile.first_name' => $this->request->data['first_name'],
                          'TutorProfile.last_name' => $this->request->data['last_name']),
                          array('TutorProfile.tutor_id' => $this->Auth->user('id')));
             **/
             } else {
                      throw new NotFoundException(__('Invalid Request'));

             }

      }
      break;
     case 'editHrate':
            $this->{$this->modelClass}->TutorProfile->set(array('hourly_rate' => $this->request->data['datum']));
        if(!empty($this->request->data['datum']) )  {
             //debug('test'); die();
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('hourly_rate'))))
         {


                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.hourly_rate' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                   throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }


     }
      //debug('test1'); die();
     break;
        case 'editTrad':
           $this->{$this->modelClass}->TutorProfile->set(array('travel_radius' => $this->request->data['datum']));
        if(!empty($this->request->data['datum']) )  {
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('travel_radius'))))
         {

                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.travel_radius' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                   throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }

     }
     break;

     case 'editCancelPolicy':
      $this->{$this->modelClass}->TutorProfile->set(array('cancel_policy' => $this->request->data['datum']));
     if(!empty($this->request->data['datum']) )  {
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('cancel_policy'))))
         {
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.cancel_policy' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
     }
     break;

    case 'editTitle':
     $this->{$this->modelClass}->TutorProfile->set(array('title' => $this->request->data['datum']));
    // debug($this->request->data['datum']); die();
     if(!empty($this->request->data['datum']) )  {
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('title'))))
         {
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.title' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                   throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
     }
     break;
  case 'editDesc':
   $this->{$this->modelClass}->TutorProfile->set(array('description' => $this->request->data['datum']));
    // debug($this->request->data['datum']); die();
     if(!empty($this->request->data['datum']) )  {
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('description'))))
         {
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.description' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
     }
     break;
     case 'editEducation' :
     //debug('here now'); die();
    //debug($this->request->data); //die();
     $this->{$this->modelClass}->TutorProfile->set(array(
             'education' => $this->request->data['ed'],
             'degree' => $this->request->data['degree'],
             'school' => $this->request->data['school']
     ));

      if(!empty($this->request->data) )  {

        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('education','degree', 'school' ))))
         {
             //$this->{$this->modelClass}->TutorProfile->id = $this->Auth->user('id');
             //$this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.education', $this->request->data['ed']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.degree', $this->request->data['degree']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.school', $this->request->data['school']);

               //debug($this->request->data['degree']);

                 $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.education' => $this->request->data['ed'],
                      'TutorProfile.degree' => $this->request->data['degree'],
                      'TutorProfile.school' => $this->request->data['school']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id')));

         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }

      }
      break;
      case 'editCadd' :
     // debug($this->request->data); die();
     $this->{$this->modelClass}->TutorProfile->set(array(
             'address_1' => $this->request->data['addr1'],
             'address_2' => $this->request->data['addr2'],
             'city' => $this->request->data['city'],
             'state' => $this->request->data['state'],
             'state_abbr' => $this->request->data['state'],
             'zip' => $this->request->data['zipCode']

     ));

      if(!empty($this->request->data) )  {

        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('address_1','address_2', 'city', 'state', 'zip_code' ))))
         {

            // $this->{$this->modelClass}->TutorProfile->id = $this->Auth->user('id');
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.address_1', $this->request->data['addr1']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.address_2', $this->request->data['addr2']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.city', $this->request->data['city']);
             //$this->{$this->modelClass}->TutorProfile->id = $this->Auth->user('id');
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.state', $this->request->data['state']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.state_abbr', $this->request->data['state']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.zip_code', $this->request->data['zipCode']);

                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.address_1' => $this->request->data['addr1'],
                      'TutorProfile.address_2' => $this->request->data['addr2'],
                      'TutorProfile.city' => $this->request->data['city'],
                      'TutorProfile.state' => $this->request->data['state'],
                      'TutorProfile.state_abbr' => $this->request->data['state'],
                      'TutorProfile.zip_code' => $this->request->data['zipCode']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id')));


                 // debug('After Save'); die();
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }

      }
      break;
      case 'editMadd' :
    // debug('In Madd'); die();
     $this->{$this->modelClass}->TutorProfile->set(array(
             'maddress_1' => $this->request->data['maddr1'],
             'maddress_2' => $this->request->data['maddr2'],
             'mcity' => $this->request->data['mcity'],
             'mstate' => $this->request->data['mstate'],
             'mstate_abbr' => $this->request->data['mstate'],
             'mzip' => $this->request->data['mzipCode'],

     ));

      if(!empty($this->request->data) )  {

        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('maddress_1','maddress_2', 'mcity', 'mstate', 'mzip_code' ))))
         {

                 $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.maddress_1' => $this->request->data['maddr1'],
                      'TutorProfile.maddress_2' => $this->request->data['maddr2'],
                      'TutorProfile.mcity' => $this->request->data['mcity'],
                      'TutorProfile.mstate' => $this->request->data['mstate'],
                      'TutorProfile.mstate_abbr' => $this->request->data['mstate'],
                      'TutorProfile.mzip_code' => $this->request->data['mzipCode']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id')));

         } else {
                  throw new NotFoundException(__('Invalid Request'));

         }

      }
      break;
   case 'editCinfo' :
     //debug($this->request->data); die();
    // debug('In Cinfo'); die();
     $this->{$this->modelClass}->TutorProfile->set(array(
             'primary_phone' => $this->request->data['pphone'],
             'pphone_type' => $this->request->data['pphoneType'],
             'secondary_phone' => $this->request->data['sphone'],
             'sphone_type' => $this->request->data['sphoneType']


     ));

      if(!empty($this->request->data) )  {
        //debug($this->request->data);
        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('primary_phone', 'pphone_type')))) //, 'secondary_phone', 'sphone_type'))))
         {

               //  debug('validated'); die();
             $this->{$this->modelClass}->TutorProfile->id = $this->request->data['id'];
             $this->{$this->modelClass}->TutorProfile->saveField('primary_phone', $this->request->data['pphone']);
             $this->{$this->modelClass}->TutorProfile->saveField('pphone_type', $this->request->data['pphoneType']);
             $this->{$this->modelClass}->TutorProfile->saveField('secondary_phone', $this->request->data['sphone']);
             $this->{$this->modelClass}->TutorProfile->saveField('sphone_type', $this->request->data['sphoneType']);

                // $this->{$this->modelClass}->TutorProfile->updateAll(
                    //  array('TutorProfile.primary_phone' => $this->request->data['pphone'],
                      //'TutorProfile.pphone_type' => $this->request->data['pphoneType'],
                      //'TutorProfile.secondary_phone' => $this->request->data['sphone'],
                      //'TutorProfile.sphone_type' => $this->request->data['sphoneType']),
                      //array('TutorProfile.tutor_id' => $this->Auth->user('id')));

         } else {
                  throw new NotFoundException(__('Invalid Request'));

         }

      }
      break;


  }

}
public function update_photo($id=null) {

    $this->layout = 'ajax';
    $this->autoRender = false;
   //debug($this->request->data); die();

     if (!$this->request->is('ajax')) {
        throw new MethodNotAllowedException();
    }

    if ( empty($id) ||!($data = $this->{$this->modelClass}->TutorImage->find(
                            'first', array(
                            'conditions' => array(
                                'TutorImage.tutor_id' => $this->Auth->user('id'),
                                'TutorImage.data_id'  => $id)))))
     {

          //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_photos'));

     }

     if ($data['TutorImage']['data_id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }

    $this->{$this->modelClass}->TutorImage->updateAll(
          array('TutorImage.featured' => 0),
          array('TutorImage.tutor_id' => $this->Auth->user('id'))
          );

    if($this->{$this->modelClass}->TutorImage->updateAll(
          array('TutorImage.featured' => 1),
          array('TutorImage.id'  => $data['TutorImage']['id']) //makes the row unique
          )
    ) {

        $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile Pic updated on your public profile.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_photos'));

    } else {

        $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Update failed.')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );

            return $this->redirect(array('action' => 'manage_photos'));
    }


}

public function delete_subject($id=null) {
     $this->layout='tutor';

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }

    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }

     if ( empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find(
                            'first', array('conditions' => array('TutorSubject.id' => $id)))))
    {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_subjects'));
     }

     if ($data['TutorSubject']['id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }
    if($this->{$this->modelClass}->TutorSubject->delete($id)) {

        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Subject has been successfully deleted.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_subjects'));

     } else {

         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'deleted failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
     }

}

public function deactivate_subject($id=null) {
     $this->layout='tutor';

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }

    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }
    if ( empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find(
                            'first', array('conditions' => array('TutorSubject.id' => $id)))))
    {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_subjects'));
     }

     if ($data['TutorSubject']['id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }
    $this->{$this->modelClass}->TutorSubject->id = $id;

    //$this->Post->saveField('title', 'A New Title for a N")
    if($this->{$this->modelClass}->TutorSubject->saveField('opt_out', 1)) {

        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Subject has been successfully deactivated.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_subjects'));

     } else {

         $this->Session->setFlash(
   												sprintf(__d('users', 'Subject deactivation failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
								 );
     }
}

public function reactivate_subject($id=null) {
     $this->layout='tutor';

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }

    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }

    if ( empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find(
                            'first', array('conditions' => array('TutorSubject.id' => $id)
       )))) {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_photos'));
     }


     if ($data['TutorSubject']['id'] != $id) {
                                //BLACKHOLE
                               // debug('hi'); die();
           //$this->Security->blackHoleCallback =
           //'blackhole';
           //blackhole($type);
            throw new BadRequestException();
     }
    $this->{$this->modelClass}->TutorSubject->id = $id;

    //$this->Post->saveField('title', 'A New Title for a N")
    if($this->{$this->modelClass}->TutorSubject->saveField('opt_out', 0)) {

        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Subject has been successfully reactivated.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_subjects'));

     } else {

         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Subject reactivation failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
     }


}
public function delete_photo($id=null) {
    //May need to revisit this: Need to think about deleting with data_id and NOT PK
    //If that is the case, Search conditions must be changed and deleteAll() will be used

    //debug($id); die();
   // debug($this->request->data); die();
    $this->layout='tutor';

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }

    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }

    if ( empty($id) || !($data = $this->{$this->modelClass}->TutorImage->find(
                            'first', array('conditions' => array('TutorImage.id' => $id)))))
    {
          //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_photos'));
     }

     if ($data['TutorImage']['id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }

    // debug($data); die();
    if($this->{$this->modelClass}->TutorImage->delete($id))
    {
       // $this->{$this->modelClass}->TutorImage->deleteFiles(WWW_ROOT . 'img/files/uploads/Chrysanthemum.jpg');
      // $this->{$this->modelClass}->TutorImage->deleteFiles($id);

      //$filename1 = $data['TutorImage']['image'];
      $filename2 = $data['TutorImage']['thumb_image'];
      $filename3 = $data['TutorImage']['thumb_medium'];

      $fileNames = array($filename2, $filename3);
       foreach($fileNames as $fileName) {
           // debug($filename); die();
            //$file = new File(WWW_ROOT . 'img'.$fileName, false); //, 0777);
            $file =  new File($fileName);
            if($file->delete()) {
                    echo 'image deleted.....';
            }
      }
      $this->Session->setFlash
   									(
   											//	sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
                                                sprintf(__d('users', 'The photo has been successfully deleted.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_photos'));
    }

    return $this->redirect(array('action' => 'manage_photos'));

}

public function credentials_submittal($pid=null) {

  //if (!$this->ResquestHandler->isAjax()) {
    if (!$this->request->is('ajax')) {
        throw new MethodNotAllowedException();
     }

    $this->layout = 'ajax';
    $this->autoRender = false;

if(!empty($this->request->data) )  {

       //debug($this->request->data['id']);
       //debug($this->request->data['datum']);die();

       $this->request->data['TutorSubject']['tutor_id'] = $this->Auth->user('id');
       $id = $this->request->data['id'];

       if (empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find('first', array('conditions' => array('TutorSubject.id' => $id)))))
       {

        //the PK must already have existed when the tutor added the subject
        //this operation is always an update. Need to worry if we did not find the PK

         throw new MethodNotAllowedException();
       }

       $this->{$this->modelClass}->TutorSubject->set('subject_credentials', $this->request->data['datum']);
       $this->{$this->modelClass}->TutorSubject->set('credentials_status', 1);

       if( $this->{$this->modelClass}->TutorSubject->validates(array('fieldList' => array('subject_credentials'))))
       {
           if($this->{$this->modelClass}->TutorSubject->saveSubjectCredentials($id, $this->request->data)) {
 			 $this->Session->setFlash(sprintf(__d('users', 'Subject Credentials have been successfully saved.')),'default',array('class' => 'alert alert-success'));
            } else {
                          debug("here"); die();
 					     //throw new MethodNotAllowedException();
                        //$this->Session->setFlash(sprintf(__d('users', 'Subject Credentials have NOT been saved.')),'default',array('class' => 'alert alert-warning'));
 	          }

         } else {
                        //debug("here"); die();
                         $this->{$this->modelClass}->validationErrors;
                         //$this->Session->setFlash(sprintf(__d('users', 'Subject Credentials have NOT been saved.')),'default',array('class' => 'alert alert-warning'));
 					    throw new InternalErrorException('Credentials Must be at least 100 Characters or more!');
         }
      }

   }


public function subject_credentials() {

      $this->layout='tutor';
      if($this->request->is('post')) {
      debug($this->request->data); die();



     if (!empty($this->request->data)) {
               $id = $this->request->data['TutorSubject']['id'];

              if (empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find('first', array('conditions' => array('TutorSubject.id' => $id)))))
              {

               //the PK must already have existed when the tutor added the subject
               //this operation is always an update. Need to worry if we did not find the PK
               throw new MethodNotAllowedException();
              }


 	        $this->request->data['TutorSubject']['tutor_id'] = $this->Auth->user('id');

                $this->{$this->modelClass}->TutorSubject->set('subject_credentials', $this->request->data['TutorSubject']['subject_credentials']);

                if ($this->{$this->modelClass}->TutorSubject->validates(
					                                array('fieldList' => array(
					                                        'subject_credentials'))))
                {

                      $this->request->data['TutorSubject']['credentials_status'] = 1;
                      if($this->{$this->modelClass}->TutorSubject->saveSubjectCredentials($id, $this->request->data))
 					   {
 							$this->Session->setFlash
 									(
 												sprintf(__d('users', 'Subject Credentials have been successfully saved.')),
 											   'default',
 												array('class' => 'alert alert-success')
 									 );
 					  } else {

                          	$this->Session->setFlash
 									(
 												sprintf(__d('users', 'Subject Credentials have NOT been saved.')),
 											   'default',
 												array('class' => 'alert alert-warning')
 									 );
 					  }

                 } else {

                          	$this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 												sprintf(__d('users', '%s', h($this->{$this->modelClass}->TutorSubject->validationErrors['subject_credentials'][0]))),
 											   'default',
 												array('class' => 'alert error-message')
 									 );
 					  }

                 $errors = $this->validationErrors;
                //debug($this->{$this->modelClass}->TutorSubject->validationErrors);    die();
         }
     }
     return $this->redirect(array('action' => 'manage_subjects'));

}
public function manage_preferences() {

      $this->layout='tutor';
      if($this->request->is('post')) {
     // debug($this->request->data); die();
      $id = null;

 	     if (!empty($this->request->data)) {
 	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
 			          $this->request->data['TutorPreference']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

              if(!empty($this->request->data['TutorPreference']['id']))
 			                $id = $this->request->data['TutorPreference']['id'];     //the Pk of Associated model (TutorPreference)

               if (empty($id) || !($data = $this->{$this->modelClass}->TutorPreference->find(
                            'first', array(
                            'conditions' => array(
                                'TutorPreference.tutor_id' => $this->Auth->user('id'),
                                'TutorPreference.id'  => $id))))
                        ) {

                                  throw new NotFoundException(__('Invalid Record'));
                      }

                    if ($data['TutorPreference']['id'] != $id) {
                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Record'));
                     }

              $this->{$this->modelClass}->TutorPreference->set(array(
                                  'new_features' => $this->request->data['TutorPreference']['new_features'],
                                  'promos' => $this->request->data['TutorPreference']['promos'],
                                  'daily_digest' => $this->request->data['TutorPreference']['daily_digest'],
                                  'new_students' => $this->request->data['TutorPreference']['new_students'],
                                  'lesson_submission' => $this->request->data['TutorPreference']['lesson_submission'],
                                  'sms_alerts' => $this->request->data['TutorPreference']['sms_alerts'],
                                  'phone_number' => $this->request->data['TutorPreference']['phone_number'],
                                  'carrier' => $this->request->data['TutorPreference']['carrier']

                                  ));
              //if ($this->{$this->modelClass}->TutorPreference->validates(
					                               //array('fieldList' => array(
                                                           // 'new_features',
                                                           // 'promos',
                                                            //'daily_digest',
                                                            //'new_students',
                                                           // 'lesson_submission',
                                                            //'sms_alerts',
					                                       // 'phone_number',
					                                        //'carrier'
                                                           // ))))
		      // {
 					  if($this->{$this->modelClass}->TutorPreference->savePreferences($id, $this->request->data))
 					   {
 							$this->Session->setFlash
 									(
 												sprintf(__d('users', 'Email/Sms Preferences successfully saved.')),
 											   'default',
 												array('class' => 'alert alert-success')
 									 );
 					  } else {
 					      $this->Session->setFlash
 									(
 												sprintf(__d('users', 'Email/Sms Preferences Not saved. Please try Again!!')),
 											   'default',
 												array('class' => 'error-message')
 									 );
 					  }
 				//}
             }
       }


           //set the primary key of preference table in the view and send it back as a hidden field
 	      $tPrefModel =  $this->{$this->modelClass}->TutorPreference->find('first', array(
			  		  		  	'conditions' => array('TutorPreference.tutor_id' => $this->Auth->user('id'))
                     ));
 	       // debug($this->Auth->user('id')); die();
 	      //  debug($tPrefModel); die();
 	      if(!empty($tPrefModel)) {
 	                //debug($tPrefModel); die();
 	                $this->set('ppk',  h($tPrefModel['TutorPreference']['id']));
 	                $this->set('nf',   h($tPrefModel['TutorPreference']['new_features']));
 	                $this->set('pmos', h($tPrefModel['TutorPreference']['promos']));
 	                $this->set('dd',   h($tPrefModel['TutorPreference']['daily_digest']));
 	                $this->set('ns',   h($tPrefModel['TutorPreference']['new_students']));
 	                $this->set('ls',   h($tPrefModel['TutorPreference']['lesson_submission']));
 	                $this->set('sa',   h($tPrefModel['TutorPreference']['sms_alerts']));
 	                $this->set('pn',   h($tPrefModel['TutorPreference']['phone_number']));
 	                $this->set('cr',   h($tPrefModel['TutorPreference']['carrier']));
           }

 }

public function manage_subjects() {
          $this->layout='tutor';

       $warning = $this->Session->read('warning');
       //debug($warning); die();
       $this->set('warning', $warning);
       $this->Session->delete('warning');

		  $subjects = $this->{$this->modelClass}->TutorSubject->find('all',
		  array(
			    'conditions' => array('TutorSubject.tutor_id' => $this->Auth->user('id'), 'TutorSubject.delete_status' => 'N'),
			    'order' => array('TutorSubject.subject_name ASC'))
		       );


         //Making sure that the Categories retreived are the ones found in Tutor_Subject table
         //We do not want to display an Empty Category (ie, a Cat w/o Subject)
          $conditions = array('TutorSubject.tutor_id' => $this->Auth->user('id'));
          $subj_cats_id = $this->{$this->modelClass}->TutorSubject->find('all', array('conditions' => $conditions,'fields' => array('TutorSubject.subject_category_id')));

           $subj_cats_id = $this->flatten_my_array($subj_cats_id, array());
          // debug($subj_cats_id); die();
           $cats = $this->{$this->modelClass}->TutorCategorie->find('all',
           array(
                'conditions' => array('TutorCategorie.tutor_id' => $this->Auth->user('id'), 'TutorCategorie.category_id' => $subj_cats_id),
                'order' => array('TutorCategorie.name ASC'))
               );
         // debug($cats); die();
		  $this->set('categories',h($cats));
		  $this->set('subjects',h($subjects));
          $this->set('tabName', h($this->Session->read('tabName')));
}


protected function flatten_my_array($array,$return) {
 foreach ($array AS $key => $value) {
    if(is_array($value))
    {
        $return = $this->flatten_my_array($value,$return);
    }
    else
    {
        if($value)
        {
            $return[] = $value;
        }
    }
}
return $return;
}

public function tutor_dashboard_data() {
      $this->layout='tutor';
      if($this->Session->check('first_login')) {
              $this->Session->delete('first_login');
     }

}

public function tutor_dashboard() {
     $this->layout='tutor';

      if($this->Session->check('first_login')) {
              $this->Session->delete('first_login');
     }
           //return $this->redirect(array('action' => 'welcome'));

      $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
			  		  		  	'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));

            //debug($tProfileModel) ; die();
       $tSubjectModel = $this->{$this->modelClass}->TutorSubject->find('first', array(
			  		  		  	'conditions' => array('TutorSubject.tutor_id' => $this->Auth->user('id'))
                     ));

      //debug($tSubjectModel); die();

       $tImageModel = $this->{$this->modelClass}->TutorImage->find('first', array(
			  		  		  	'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))
                     ));

        if(!empty($tSubjectModel)) {
            //debug("here");
            $this->set('sub',    1);

        }

        if(!empty($tImageModel)) {
            $this->set('pics',    '1');

        }
      if(!empty($tProfileModel)) {
        //debug($tProfileModel['TutorProfile']['profile_status_count']);

                    $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
                     //$this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));

                   // $this->set('pics',    h($tProfileModel['TutorProfile']['profilePic_status']));
                   // $this->set('sub',    h($tProfileModel['TutorProfile']['subj_status']));

             }

              $user = $this->dashboard();
             // debug($user); die();
              $this->set('user', $user);
}

public function job_search() {

    // $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     //$this->layout='default';

     	if (!$this->Auth->loggedIn()) {
     	  return $this->redirect(array('action' => 'job_search_results'));
        } else {

            return $this->redirect(array('action' => 'job_search_results_auth'));
        }
      //$this->layout='student';

}

  public function tellYourFriends() {
    $this->layout='tutor';

  }

   public function accountsettings() {
            $this->layout='tutor';
    }

    public function payment_policy() {
            $this->layout='tutor';
    }
   public function ica_new() {
      $this->layout='tutor';
      if($this->request->is('post')) {
                 $postData = array();
	             $id = null;
	    	     if (!empty($this->request->data)) {
	    	                  //debug($this->request->data); die();
	    	                  $this->{$this->modelClass}->TutorProfile->set($this->request->data);
	    	                  $postData = $this->request->data;
	    			          $this->request->data['TutorProfile']['tutor_id'] = $this->Auth->user('id');

	    			          if(empty($this->request->data['TutorProfile']['id'])) {
	    			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)
                                    throw new NotFoundException(__('Invalid ICA'));;
                               }

                             if (!($data = $this->{$this->modelClass}->TutorProfile->find(
                                                        'first', array(
                                                        'conditions' => array(
                                                            'TutorProfile.tutor_id' => $this->Auth->user('id'),
                                                            'TutorProfile.id'  => $this->request->data['TutorProfile']['id'])))))
                                 {

                                      //error flash message
                                      $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
                               											   'default',
                               												array('class' => 'alert error-message')
                            							       );
                                      $this->redirect(array('action' => 'ica_new'));

                                 }


                             $this->{$this->modelClass}->TutorProfile->set(array(
                                  'terms_of_use' => $this->request->data['TutorProfile']['terms_of_use'],
                                  'work_auth' => $this->request->data['TutorProfile']['work_auth'],
                                  'first_name' => $this->request->data['TutorProfile']['first_name'],
                                  'last_name' => $this->request->data['TutorProfile']['last_name']));


                   if ($this->{$this->modelClass}->TutorProfile->validates(
							        array('fieldList' => array(
							          'terms_of_use',
							          'work_auth',
							          'first_name',
                                      'last_name'))))
                              {
                                       $postData['TutorProfile']['tutor_signature'] = $postData['TutorProfile']['first_name'].$postData['TutorProfile']['last_name'];
	                                   if(!empty($postData['TutorProfile']['tutor_signature'])) {
	                                           $postData['TutorProfile']['signed_agreement'] = 1;
                                        }

                                      //$postData = $this->request->data;
                                      $ica_status = $postData['TutorProfile']['ica_status'];
                                      if(!$ica_status) {

                                        $postData['TutorProfile']['ica_status'] = 1;
                                       // $postData['TutorProfile']['profile_status_count']++;

                                       // if($postData['TutorProfile']['profile_status_count'] == 4) {
                                          //  $postData['TutorProfile']['profile_ready'] = 1;
                                        // }
                                      }

							       if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
								  	  {
								  	     $this->Session->setFlash(sprintf(__d('users', 'Agreement details has been successfully saved.')),
								  	    							'default',
								  	    							array('class' => 'alert alert-success')
								  	    						);
								  	       // return $this->redirect(array('action' => 'add_subjects'));
	    					          } else {
	    					              $this->Session->setFlash(sprintf(__d('users', 'Agreement details save failed.')),
								  	    							'default',
								  	    							array('class' => 'alert error-message')
								  	    						);

	    					          }

	                     } else {

                              $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all errors below and Re-Submit form')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );
	                          }
	               }
             }

             //set the primary key of preference table in the view and send it back as a hidden field


           $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array('conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id')) ));
             //debug($tProfileModel); die();
          if(!empty($tProfileModel)) {

            $icaStatus = $tProfileModel['TutorProfile']['ica_status'];
           }

           $pProfileStatus = $tProfileModel['TutorProfile']['publicProfile_status'];

          /** if(empty($pProfileStatus) || !$pProfileStatus ) {
                    return $this->redirect(array('action' => 'public_profile'));
            }else if(!empty($icaStatus) && $icaStatus) {
                    return $this->redirect(array('action' => 'add_subjects'));
            }
**/
            if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
                    $this->set('gn',     h($tProfileModel['TutorProfile']['gender']));
                    $this->set('age',     h($tProfileModel['TutorProfile']['age']));
                    $this->set('ica_status',     h($tProfileModel['TutorProfile']['ica_status']));
                    $this->set('fn',     h($tProfileModel['TutorProfile']['first_name']));
                    $this->set('ln',     h($tProfileModel['TutorProfile']['last_name']));

   	                $this->set('ed',     h($tProfileModel['TutorProfile']['education']));
   	                $this->set('degree', h($tProfileModel['TutorProfile']['degree']));
   	                $this->set('school', h($tProfileModel['TutorProfile']['school']));

   	                $this->set('add1',   h($tProfileModel['TutorProfile']['address_1']));
   	                $this->set('add2',   h($tProfileModel['TutorProfile']['address_2']));
   	                $this->set('city',   h($tProfileModel['TutorProfile']['city']));
   	                $this->set('st',     h($tProfileModel['TutorProfile']['state']));

                     if(!empty($tProfileModel['TutorProfile']['zip_code'])) {
                        //debug("teststs");
                       $this->set('zip',    h($tProfileModel['TutorProfile']['zip_code']));
                     } else {
                        $this->set('zip',    h($this->Session->read('zip_code')));
                     }
   	                $this->set('madd1',   h($tProfileModel['TutorProfile']['maddress_1']));
					$this->set('madd2',   h($tProfileModel['TutorProfile']['maddress_2']));
					$this->set('mcity',   h($tProfileModel['TutorProfile']['mcity']));
					$this->set('mst',     h($tProfileModel['TutorProfile']['mstate']));
   	                $this->set('mzip',    h($tProfileModel['TutorProfile']['mzip_code']));

               	    $this->set('pp',     h($tProfileModel['TutorProfile']['primary_phone']));
   	                $this->set('sp',     h($tProfileModel['TutorProfile']['secondary_phone']));
   	                $this->set('mhop',   h($tProfileModel['TutorProfile']['pphone_type']));
   	                $this->set('mhos',   h($tProfileModel['TutorProfile']['sphone_type']));

                    //$this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));

                    $this->set('pics',    h($tProfileModel['TutorProfile']['profilePic_status']));
                    $this->set('sub',    h($tProfileModel['TutorProfile']['subj_status']));

                   // $this->set('ica',    h($tProfileModel['TutorProfile']['ica_status']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
             }
    }

    public function contact_student() {
            $this->layout='tutor';
    }

    public function bg_check() {
            $this->layout='tutor';
    }

    public function mkt_place() {
            $this->layout='tutor';

	         if($this->request->is('post')) {
	         $id = null;
	         $postData = array();

             if (!empty($this->request->data)) {
	    	   // debug($this->request->data); die();
      		         $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
                     $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

                     //check if the record exists, then check if a valid id (not null or empty) is passed in the request,
                     //then compare the two. if they match make the assignment

                     if(empty($this->request->data['TutorProfile']['id'])) {

                           // $id = $this->request->data['TutorProfile']['id'];
                            throw new NotFoundException(__('Invalid Request'));
                     }

                     // if(!empty($id) && $id != null) {
                              // There should not be a record yet. So there must not be a pk
                             // throw new NotFoundException(__('Invalid Request'));
                      // }
                        $postData = $this->request->data;
                       // debug($this->request->data); die();
                        $mstatus = $postData['TutorProfile']['mktplace_status'];
                        if(!$mstatus) {

                             // debug($this->request->data); die();
                           $this->request->data['TutorProfile']['mktplace_status'] = 1;
                           $postData['TutorProfile']['mkt_place_rules'] = 1;
                           //$this->request->data['TutorProfile']['profile_status_count']++ ;

                            //if($postData['TutorProfile']['profile_status_count'] == 4) {
                               // $postData['TutorProfile']['profile_ready'] = 1;
                            //}
                       }


                        $this->{$this->modelClass}->TutorProfile->set(array(
                                  'mkt_place_rules' => $this->request->data['TutorProfile']['mkt_place_rules']
                                  ));

                        if ($this->{$this->modelClass}->TutorProfile->validates(
							  				array('fieldList' => array(
							  					'mkt_place_rules'))))
                         {
	    					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))

	    					   {
	    					        //debug('test'); die();
	    							$this->Session->setFlash
	    									(
	    												sprintf(__d('users', 'Marketplace rules Agreement has been successfully saved.')),
	    											   'default',
	    												array('class' => 'alert alert-success')
	    									 );

	    								//return $this->redirect(array('action' => 'basic_profile'));
	    					  } else {
	    					      $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'Something went wrong.')),
	 						     					'default',
	 						     					 array('class' => 'alert alert-warning')
	    									 );
	    					  }
	    				 } else {
	    				     $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'You must agree to the terms and conditions. Please read the rules of the Online tutoring Market Place and check the box at the bottom of screen')),
	 						     					'default',
	 						     					 array('class' => 'alert error-message')
									     	);
                                             //debug($this->{$this->modelClass}->TutorProfile->validationErrors);    die();
	    				 }
	                }
	          }

         $mktPlaceModel =  $this->{$this->modelClass}->TutorProfile->find('first', array('conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))));
		 if(!empty($mktPlaceModel)) {
		   //debug($mktPlaceModel); die();
   	                $this->set('prpk',                    h($mktPlaceModel['TutorProfile']['id']));
                    $this->set('mkt_place_rules',         h($mktPlaceModel['TutorProfile']['mkt_place_rules']));
                    $this->set('profile_status_count',    h($mktPlaceModel['TutorProfile']['profile_status_count']));
                    $this->set('mktplace_status',         h($mktPlaceModel['TutorProfile']['mktplace_status']));

                    $this->Session->write('profile_status_count',    h($mktPlaceModel['TutorProfile']['profile_status_count']));
                    $this->Session->write('mktplace_status',         h($mktPlaceModel['TutorProfile']['mktplace_status']));

   	                $mktPlaceStatus = $mktPlaceModel['TutorProfile']['mktplace_status'];

   	                //if(!empty($mktPlaceStatus) && $mktPlaceStatus) {
   	                  // return $this->redirect(array('action' => 'basic_profile'));
   	                //}
          }
    }



 public function manage() {
            $this->layout='tutor';
    }

 public function mysubjects() {
            $this->layout='tutor';
    }

  public function mydaraji() {
	            $this->layout='tutor';
    }

public function my_students() {
	            $this->layout='tutor';
    }

   public function studentsandlessons() {
		     $this->layout='tutor';
    }

    public function alllessons() {
			     $this->layout='tutor';
    }

     public function myscheduledlessons() {
				     $this->layout='tutor';
    }

     public function mysubmittedlessons() {
					$this->layout='tutor';
    }

    public function lesson_submission() {
				$this->layout='tutor';
    }

public function submit_lesson() {

       $this->layout='tutor';
       $tutor_locations = array();
       $tutor_subjects = array();
       $tutor_students = array();
       $hourly_rate = 0;


       if($this->request->is('post')) {

         $id = null;
    	 if(!empty($this->request->data))
    	 {

           $id = null;
           $this->request->data['TutorLessonSubmittal']['tutor_id'] = $this->Auth->user('id'); //$this->request->data[$this->modelClass]['id'];

           $input_date = DateTime::createFromFormat('Y-m-d', $this->request->data['TutorLessonSubmittal']['lesson_dte']);
           $lesson_date = $input_date->format('Y-m-d H:i:s');
           $this->request->data['TutorLessonSubmittal']['lesson_date'] = $lesson_date;

           $input_date = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));
           $submit_date = $input_date->format('Y-m-d H:i:s');
           $this->request->data['TutorLessonSubmittal']['submit_date'] = $submit_date;

           $this->{$this->modelClass}->TutorLessonSubmittal->set(array(

                                  'subject_name' => $this->request->data['TutorLessonSubmittal']['subject_name'],
                                  'student_name' => $this->request->data['TutorLessonSubmittal']['student_name'],
                                  'submit_date' => $this->request->data['TutorLessonSubmittal']['submit_date'],
                                  //'start_time' => $this->request->data['TutorLessonSubmittal']['start_time'],
                                  //'end_time' => $this->request->data['TutorLessonSubmittal']['end_time'],
                                  'duration' => $this->request->data['TutorLessonSubmittal']['duration'],
                                  //'total_charges' => $this->request->data['TutorLessonSubmittal']['total_charges'],
                                  //'net_pay' => $this->request->data['TutorLessonSubmittal']['net_pay'],

                        ));

                      $this->request->data['TutorLessonSubmittal']['first_name'] =  $this->Session->read('username');
                      $this->request->data['TutorLessonSubmittal']['last_name'] =  $this->Session->read('lastname');
                      $this->request->data['TutorLessonSubmittal']['tutor_name'] = $this->request->data['TutorLessonSubmittal']['first_name'].' '.$this->request->data['TutorLessonSubmittal']['last_name'];

                      if(empty($this->request->data['TutorLessonSubmittal']['lesson_id'])) {
                          $lesson_id = uniqid(rand(), true);
                          $result = String::tokenize($lesson_id, '.');
                          $lesson_id  = $result[1];
                          $this->request->data['TutorLessonSubmittal']['lesson_id'] = $lesson_id;
                       }

                      $this->request->data['TutorLessonSubmittal']['status'] = 'Submitted';
                      $this->request->data['TutorLessonSubmittal']['notify_student'] = '1'; //$this->request->data['TutorLessonSubmittal']['notification'];
                      $this->request->data['TutorLessonSubmittal']['total_charges'] = $this->request->data['TutorLessonSubmittal']['charges'];



                       if(empty($this->request->data['TutorLessonSubmittal']['location_id'] )) {
                          $this->request->data['TutorLessonSubmittal']['location_name'] = 'N/A';
                       }


                     //debug($this->request->data); die();

                      if($this->{$this->modelClass}->TutorLessonSubmittal->validates(array('fieldList' => array(
                                                            'subject_name',
                                                            'student_name',
                                                            'submit_date',
                                                            //'start_time',
                                                            //'end_time',
                                                            'duration'))))
                       {

                       // debug(substr($this->request->data['TutorLessonSubmittal']['start_time']['hour'], 0,1)); //die();
                      if(substr($this->request->data['TutorLessonSubmittal']['start_time']['hour'], 0,1) === '0') {
                          $this->request->data['TutorLessonSubmittal']['start_time']['hour'] = substr($this->request->data['TutorLessonSubmittal']['start_time']['hour'], 1);
                      }

                       if(substr($this->request->data['TutorLessonSubmittal']['end_time']['hour'], 0,1) === '0') {
                          $this->request->data['TutorLessonSubmittal']['end_time']['hour'] = substr($this->request->data['TutorLessonSubmittal']['end_time']['hour'], 1);
                        }

                      $this->request->data['TutorLessonSubmittal']['start_time'] = $this->request->data['TutorLessonSubmittal']['start_time']['hour'].':'.$this->request->data['TutorLessonSubmittal']['start_time']['min'].' '.$this->request->data['TutorLessonSubmittal']['start_time']['meridian'];
                      $this->request->data['TutorLessonSubmittal']['end_time'] = $this->request->data['TutorLessonSubmittal']['end_time']['hour'].':'.$this->request->data['TutorLessonSubmittal']['end_time']['min'].' '.$this->request->data['TutorLessonSubmittal']['end_time']['meridian'];

                    //debug($this->request->data); die();
   					  if($this->{$this->modelClass}->TutorLessonSubmittal->saveTutorLessonSubmittal($id, $this->request->data))
   					   {
   					       // debug("hehe"); die();
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Lesson has been successfully submitted.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

                             $this->redirect(array('action' => 'submit_lesson'));

   					  } else {
   					     //debug($this->{$this->modelClass}->TutorLocation->validationErrors); die();

                               $this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 												sprintf(__d('users', 'Lesson Submission Save Failed')),
 											   'default',
 												array('class' => 'alert error-message')
 									 );
                            $this->redirect(array('action' => 'submit_lesson'));

   					  }
               } else {

                        debug($this->{$this->modelClass}->TutorLessonSubmittal->validationErrors); die();

                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );

                           $this->redirect(array('action' => 'submit_lesson'));

               }
           }
       }

       $location_name = "";
       if($this->Session->check('location_name')) {
         $location_name = $this->Session->read('location_name'); //, $this->request->data['TutorLocation']['name']);
         $this->Session->delete('location_name');
         $this->set('location_name', $location_name);
       }
       $this->{$this->modelClass}->recursive = 1;
       $data = $this->{$this->modelClass}->find('all', array(
                      'conditions'=>array('Tutor.id' => $this->Auth->user('id'))
            ));

       //debug($data); die();
        $subj =    $data[0]['TutorSubject'];
        $loc =     $data[0]['TutorLocation'];
        $profile = $data[0]['TutorProfile'];
       // $stu = $data[0]['TutorStudent'];


         if(!empty($subj)){
			foreach ($subj as $key => $value) {
				if(!empty($value['subject_name']) &&
                  !empty($value['subject_id'])){
                    $tutor_subjects[] = array($value['subject_id'] => $value['subject_name']);

				}
			}
		  }

         if(!empty($profile)){
		    $hourly_rate = $profile['hourly_rate'];
		  }

          /**
          if(!empty($stu)){
			foreach ($stu as $key => $value) {
				if(!empty($value['student_name']) &&
                  !empty($value['student_id'])){
                    $tutor_students[] = array($value['student_id'] => $value['student_name']);

				}
			}
		  }
        **/

         if(!empty($loc)){
			foreach ($loc as $key => $value) {
				if(!empty($value['location_name']) &&
                  !empty($value['location_id'])){
                    $tutor_locations[] = array($value['location_id'] => $value['location_name']);

				}
			}
		  }

          $tutor_subjects = $this->flatten($tutor_subjects, '');
          $tutor_locations = $this->flatten($tutor_locations, '');
           //$tutor_students = $this->flatten($tutor_students, '');

          // debug($tutor_subjects);
          // debug($tutor_locations);
          // debug($hourly_rate); die();

           asort($tutor_subjects);
           asort($tutor_locations);

          Configure::write('tutor_locations', $tutor_locations);
          Configure::write('tutor_subjects', $tutor_subjects);
          $this->set('hourly_rate', $hourly_rate);

 }
 public function schedule_lesson() {

       $this->layout='tutor';

       $tutor_locations = array();
       $tutor_subjects = array();
       $tutor_students = array();
       $hourly_rate = 0;

       if($this->request->is('post')) {
         $id = null;
    	 if(!empty($this->request->data))
    	 {
    	   //debug($this->request->data['TutorSchedule']['schedule_dte']); //die();
            $id = null;
            $this->request->data['TutorSchedule']['tutor_id'] = $this->Auth->user('id'); //$this->request->data[$this->modelClass]['id'];

           $input_date = DateTime::createFromFormat('Y-m-d H:i:s', $this->request->data['TutorSchedule']['schedule_dte'].' 00:00:00');
          //debug($input_date); die();
           $sch_date = $input_date->format('Y-m-d H:i:s');
          // debug($sch_date); //die();
            //$sch_date = strtotime($this->request->data['TutorSchedule']['schedule_dte']);
            //$sch_date = date("Y-m-d", $sch_date);
             //$sch_date = DateTime::createFromFormat("m-d-Y", $this->request->data['TutorSchedule']['schedule_dte']);
            $this->request->data['TutorSchedule']['schedule_date'] = $sch_date;
            //debug($sch_date); die();
            $this->{$this->modelClass}->TutorSchedule->set(array(

                                  'subject_name' => $this->request->data['TutorSchedule']['subject_name'],
                                  'student_name' => $this->request->data['TutorSchedule']['student_name'],
                                  'schedule_date' => $this->request->data['TutorSchedule']['schedule_date'],
                                  //'start_time' => $this->request->data['TutorSchedule']['start_time'],
                                  //'end_time' => $this->request->data['TutorSchedule']['end_time'],
                                  'duration' => $this->request->data['TutorSchedule']['duration'],

                        ));

                      $this->request->data['TutorSchedule']['first_name'] =  $this->Session->read('username');
                      $this->request->data['TutorSchedule']['last_name'] =  $this->Session->read('lastname');
                      $this->request->data['TutorSchedule']['tutor_name'] = $this->request->data['TutorSchedule']['first_name'].' '.$this->request->data['TutorSchedule']['last_name'];

                      $lesson_id = uniqid(rand(), true);
                      $result = String::tokenize($lesson_id, '.');
                      $lesson_id  = $result[1];
                      $this->request->data['TutorSchedule']['lesson_id'] = $lesson_id;

                      $this->request->data['TutorSchedule']['status'] = 'Scheduled';
                      $this->request->data['TutorSchedule']['notify_student'] = $this->request->data['TutorSchedule']['notification'];

                      if(empty($this->request->data['TutorSchedule']['repeat_schedule'] )) {

                        $this->request->data['TutorSchedule']['frequency'] = '0';
                        $this->request->data['TutorSchedule']['sessions'] = '0';
                      }

                       if(empty($this->request->data['TutorSchedule']['location_id'] )) {
                          $this->request->data['TutorSchedule']['location_name'] = 'N/A';
                       }


                     // debug($this->request->data); die();

                      if($this->{$this->modelClass}->TutorSchedule->validates(array('fieldList' => array(
                                                            'subject_name',
                                                            'student_name',
                                                            'schedule_date',
                                                            //'start_time',
                                                            //'end_time',
                                                            'duration'))))
                       {

                       // debug(substr($this->request->data['TutorSchedule']['start_time']['hour'], 0,1)); //die();
                      if(substr($this->request->data['TutorSchedule']['start_time']['hour'], 0,1) === '0') {
                          $this->request->data['TutorSchedule']['start_time']['hour'] = substr($this->request->data['TutorSchedule']['start_time']['hour'], 1);
                      }

                       if(substr($this->request->data['TutorSchedule']['end_time']['hour'], 0,1) === '0') {
                          $this->request->data['TutorSchedule']['end_time']['hour'] = substr($this->request->data['TutorSchedule']['end_time']['hour'], 1);
                        }

                      $this->request->data['TutorSchedule']['start_time'] = $this->request->data['TutorSchedule']['start_time']['hour'].':'.$this->request->data['TutorSchedule']['start_time']['min'].' '.$this->request->data['TutorSchedule']['start_time']['meridian'];
                      $this->request->data['TutorSchedule']['end_time'] = $this->request->data['TutorSchedule']['end_time']['hour'].':'.$this->request->data['TutorSchedule']['end_time']['min'].' '.$this->request->data['TutorSchedule']['end_time']['meridian'];


   					  if($this->{$this->modelClass}->TutorSchedule->saveTutorSchedule($id, $this->request->data))
   					   {
   					       // debug("hehe"); die();
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Lesson has been successfully scheduled.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

                             $this->redirect(array('action' => 'schedule_lesson'));

   					  } else {
   					     //debug($this->{$this->modelClass}->TutorLocation->validationErrors); die();

                               $this->Session->setFlash
 									(

 												sprintf(__d('users', 'Lesson Schedule Save Failed')),
 											   'default',
 												array('class' => 'alert error-message')
 									 );
                            $this->redirect(array('action' => 'schedule_lesson'));

   					  }
               } else {

                        debug($this->{$this->modelClass}->TutorSchedule->validationErrors); die();

                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );

                           $this->redirect(array('action' => 'schedule_lesson'));

               }
           }
       }

       $location_name = "";
       if($this->Session->check('location_name')) {
         $location_name = $this->Session->read('location_name'); //, $this->request->data['TutorLocation']['name']);
         $this->Session->delete('location_name');
         $this->set('location_name', $location_name);
       }
      // $this->{$this->modelClass}->recursive = 1;
      // $data = $this->{$this->modelClass}->find('all', array(
                    //  'conditions'=>array('Tutor.id' => $this->Auth->user('id'))
          // ));

          //http://book.cakephp.org/2.0/en/core-libraries/behaviors/containable.html
        $data = $this->{$this->modelClass}->find('all', array(
	            'conditions' => array('Tutor.id' => $this->Auth->user('id')),
	   		    'contain' => array(
	   		        'TutorSubject',
	   		        'TutorProfile',
	   		        'TutorLocation'
	   		        )
           ));


      // debug($data); die();
        $subj =    $data[0]['TutorSubject'];
        $loc =     $data[0]['TutorLocation'];
        $profile = $data[0]['TutorProfile'];
       // $stu = $data[0]['TutorStudent'];


         if(!empty($subj)){
			foreach ($subj as $key => $value) {
				if(!empty($value['subject_name']) &&
                  !empty($value['subject_id'])){
                    $tutor_subjects[] = array($value['subject_id'] => $value['subject_name']);

				}
			}
		  }

         if(!empty($profile)){
		    $hourly_rate = $profile['hourly_rate'];
		  }

          /**
          if(!empty($stu)){
			foreach ($stu as $key => $value) {
				if(!empty($value['student_name']) &&
                  !empty($value['student_id'])){
                    $tutor_students[] = array($value['student_id'] => $value['student_name']);

				}
			}
		  }
        **/

         if(!empty($loc)){
			foreach ($loc as $key => $value) {
				if(!empty($value['location_name']) &&
                  !empty($value['location_id'])){
                    $tutor_locations[] = array($value['location_id'] => $value['location_name']);

				}
			}
		  }

          $tutor_subjects = $this->flatten($tutor_subjects, '');
          $tutor_locations = $this->flatten($tutor_locations, '');
           //$tutor_students = $this->flatten($tutor_students, '');

          // debug($tutor_subjects);
          // debug($tutor_locations);
          // debug($hourly_rate); die();

           asort($tutor_subjects);
           asort($tutor_locations);

          Configure::write('tutor_locations', $tutor_locations);
          Configure::write('tutor_subjects', $tutor_subjects);
          $this->set('hourly_rate', $hourly_rate);

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

    public function studentfeedback() {
				$this->layout='tutor';
    }


     public function helptutor() {
					$this->layout='tutor';
    }

    public function background_check_consent() {
						$this->layout='tutor';
    }



    public function successtips() {
			$this->layout='tutor';
    }

/*
	* @Method      :search_agent
	* @Description :for create search agent
	* @access      :registered User Group
	* @param       :
	* @return      :null
	*/
function save_job_results($id=null, $agent_id=null){

        //("test"); die();
		$this->layout='tutor';
        $subject ="";
        $subject_id ="";
        $zip_code = $this->Session->read('cur_zip_code');
        $distance = 20;
        $agent_id = 0;

       // debug($this->request->data); die();

//debug($this->Session->read('session_search_criteria')); die();
if(!$this->request->is('ajax') && $this->request->is('post')) {
     $id = null;
	 if(!empty($this->request->data))
	 {
	    debug($this->request->data);// die();
        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['JobSearchAgent']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

        if(!empty($this->request->data['JobSearchAgent']['id'])) {
        // debug("here a"); die();
                  $id = $this->request->data['JobSearchAgent']['id'];     //the Pk of Associated model (StudentSearchAgent)
        }
           $agent_name = $this->request->data['JobSearchAgent']['agent_name'];

           $session_search_criteria = $this->Session->read('session_search_criteria');
           debug($session_search_criteria); //die();
           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
                if(!empty($session_search_criteria['kwd'])) {
                    $kwd = $session_search_criteria['kwd'];
                } else {
                    $kwd = "";
                }
            $conditions = array(
								'tutor_id' => $this->Auth->user('id'),
								'agent_name' => $this->request->data['JobSearchAgent']['agent_name'],
						 );

            if ($this->{$this->modelClass}->JobSearchAgent->hasAny($conditions)){ //Cannot have a Duplicate Agent Name

                 $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'It appears that an Agent with same Name already exists.<br />Please save your Agent under a different name!!')),
                                               'default',
 												array('class' => 'alert alert-warning')

                                        );

                return; // $this->redirect()

             } else  { //Create the record

			    $r = $this->getAllJobSearchAgents($this->Auth->user('id'));
       //$agent_count =   count($search_agents) ;
       //$this->Session->write('agent_count', $agent_count);

                if(!empty($r) && count($r) >= 15) { //user is Only allowed 15 Search agents for now

				  $this->set('agent_count', count($r));
                  $this->Session->write('agent_count', count($r));

                  $this->Session->setFlash
       									(
       												sprintf(__d('users', 'You are Only allowed to save 5 Agents')),
       											   'default',
       												array('class' => 'alert alert-warning')
       									 );
				  return $this->redirect(array('action' => 'job_search_results_auth'));
                }



			   for($i=1; $i<16; $i++) { //this is so that the agent_id is always between 1 and 15

						$data = $this->{$this->modelClass}->JobSearchAgent->find(
										'first', array(
										//'order' => array('StudentSearchAgent.agent_id' => 'DESC'),
										'conditions' => array(
											'JobSearchAgent.tutor_id' => $this->Auth->user('id'),
											'JobSearchAgent.agent_id'  => $i)));
						if (!$data) {
							$this->request->data['JobSearchAgent']['agent_id'] = $i; //$data['StudentSearchAgent']['agent_id'] + 1;

							break;
						} else {
						   $this->request->data['JobSearchAgent']['agent_id'] = $data['JobSearchAgent']['agent_id']; //1;
						}

               }
             	$this->{$this->modelClass}->JobSearchAgent->set($conditions);
                $this->{$this->modelClass}->JobSearchAgent->create();
               // $this->request->data['StudentSearchAgent']['created'] = date('Y-m-d H:i:s');

                //$session_search_criteria = $this->Session->read('session_search_criteria');
				 //debug($session_search_criteria); die();
			//	if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
			//	{
			  // debug("hagdghs"); //die();
                      $session_search_criteria['subject'] = $session_search_criteria['job_subject'];
                      $session_search_criteria['job_subject_id'] = $session_search_criteria['subject_id'];

                      $session_search_criteria['category'] = $session_search_criteria['category'];
                      $session_search_criteria['category_id'] = $session_search_criteria['category_id'];

                    debug($session_search_criteria); //die();
                	if(!empty($session_search_criteria['subject']))
					{
					   $this->request->data['JobSearchAgent']['subject'] = $session_search_criteria['subject'];
                       $subject = $this->request->data['JobSearchAgent']['subject'];
                    } else
					{
					   $this->request->data['JobSearchAgent']['subject'] = "";
                       $subject = $this->request->data['JobSearchAgent']['subject'];
                    }

                    if(!empty($session_search_criteria['subject_id']))
					{
					   $this->request->data['JobSearchAgent']['subject_id'] = $session_search_criteria['subject_id'];
                       $subject_id = $this->request->data['JobSearchAgent']['subject_id'];
                    } else
					{
					   $this->request->data['JobSearchAgent']['subject_id'] = "";
                       $subject_id = $this->request->data['JobSearchAgent']['subject_id'];
                    }


					if(!empty($session_search_criteria['zip_code']))
					{
					   $this->request->data['JobSearchAgent']['zip_code'] = $session_search_criteria['zip_code'];
                       $zip_code = $this->request->data['JobSearchAgent']['zip_code'];
                    } else {

					   $this->request->data['JobSearchAgent']['zip_code'] = $this->Session->read('cur_zip_code');
                       $zip_code = $this->request->data['JobSearchAgent']['zip_code'];
                     }

					if(!empty($session_search_criteria['distance']) && $session_search_criteria['distance']>=20)
					 {
					   $this->request->data['JobSearchAgent']['distance'] = $session_search_criteria['distance'];
                       $distance = $this->request->data['JobSearchAgent']['distance'];
                     }
					else
					{
					   $this->request->data['JobSearchAgent']['distance'] = 20;
                       $distance = $this->request->data['JobSearchAgent']['distance'];

                    }


				      debug($this->request->data); //die();
                      $agent_id = $this->request->data['JobSearchAgent']['agent_id'];
                       $search_query =  '?job_subject='.$subject.'&job_subject_id='.$subject_id.'&zip_code='.$zip_code.'&distance='.$distance.'&agent_name='.$agent_name.'&agent_id='.$agent_id.'&update_agent=1';
                       $this->request->data['JobSearchAgent']['search_query']  = $search_query;

                         debug($search_query); //die();
						 if($this->{$this->modelClass}->JobSearchAgent->saveSearchAgent($id, $this->request->data))
						  //if($this->{$this->modelClass}->StudentSearchAgent->saveAll($this->request->data))

						 {
										$this->Session->setFlash
												(
															sprintf(__d('users', 'Your Search has been successfully saved.')),
														   'default',
															array('class' => 'alert alert-success')
												 );
												 $r = $this->getAllJobSearchAgents($this->Auth->user('id'));
                                                 $this->Session->write('agent_count', count($r));
												 $this->Session->delete('session_search_criteria');
												 return $this->redirect(array('action' => 'job_search_results_auth'));

						 }
               }

			} else {
			    	$this->Session->setFlash
       									(
       												sprintf(__d('users', 'Your have no new Search Criteria.Please Search first and build your Agent')),
       											   'default',
       												array('class' => 'alert alert-warning')
       									 );
			}                  return $this->redirect(array('action' => 'job_search_results_auth'));
		}//request->data

     } //is->post())

     else if ($this->request->is('ajax') ) {
            $this->render('edit', 'ajax');

            $this->{$this->modelClass}->JobSearchAgent->id = $id;
            $ssa = $this->{$this->modelClass}->JobSearchAgent->findById($id);

            //debug($ssa); die();
            if(!empty($ssa)) {

            }

            //return $this->redirect(array('action'=>'tutor_search_results'));
        }

           //$session_search_criteria = $this->Session->read('session_search_criteria');
           //debug($session_search_criteria); //die();
}

 public function job_search_results_auth() {

    $this->layout='tutor';

    $radiusSearch = new ZipSearch();
    $conditions = array();
    $students_model = new Student();
    $tutors_model = new Tutor();
    $tutor_subject_model = new TutorSubject();
    $subject_model = new Subject();

    $warning = $this->Session->read('warning');
       //debug($warning); die();
       $this->set('warning', $warning);
       $this->Session->delete('warning');
    if($this->request->is('get')) {

         $id = null;
         $rs = null;

         $posts_per_page = 7;
         $total_post_count = 0;
         $cur_page = 1;
         $start_page = 1;
         $display_page_navigation = 9;

         if(!empty($_GET['cur_page'])){
          $cur_page = $cur_page_b = $_GET['cur_page'];
         }

         //$this->set('cur_page', $cur_page);
         //debug($cur_page); die();

        /**
       * set current zip code
       */
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');

       // ovewrites the computed zip code in the session if user entered a zip code manually
      //  debug($cur_zip_code);
       $this->set('city', $this->_set_city_for_zip($cur_zip_code));
       $this->Session->write('cur_zip_code', $cur_zip_code);
      // $this->set('cur_zip_code', $cur_zip_code);
       $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";

     /**
       if(!empty($this->params->query['zip_code'])) {
           if( $this->params->query['zip_code'] === "") {
               $this->Session->write('cur_zip_code', $this->params->query['zip_code']);
           } else {
             $this->Session->write('cur_zip_code', $cur_zip_code);
           }
       } else {
             $this->Session->write('cur_zip_code', $cur_zip_code);
       }
       **/

      //// if(empty($this->params->query['zip_code'])  || $this->params->query['zip_code'] === "") {
        // $this->Session->write('cur_zip_code', "");
      // }


        //if (!empty($this->params->query) && !empty($this->params->query['distance'])) { // if submit

         //debug($this->params->query); die();
         //we need to have access to the search criteria for
         //when the user decides to save the Search resuts as a Search Agent
         //So we put them in the Session
         //$this->Session->write('$session_search_criteria', $this->params->query);
          $tutor_approved_subjects = $tutors_model->get_all_subjects_for_tutor($this->Auth->user('id'));
          //debug($tutor_approved_subjects); die();

          $return_array = array();
          foreach ($tutor_approved_subjects as $key1 => $value1) {
               $return_array[]  =  implode('(int) 0 =>', $value1);
           }

          $tutor_approved_subjects = $return_array;
          //$tutor_approved_subjects = null;
         // debug($return_array); die();
         $app_allowed = true;
         $tutor_profile = $this->{$this->modelClass}->TutorProfile->find('first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'))));

       if(empty($tutor_profile) || $tutor_profile['TutorProfile']['profile_status_count'] != '4' )  {

            $app_allowed = false;
            $this->set('app_allowed', $app_allowed) ;
            $this->set('tutor_approved_subjects', $tutor_approved_subjects) ;

           //Since the user has not completed his/her profile, we highjack the qury params
           // and change to the defaults of "All Subjects Group/All Subjects"
           //This will take care of the search conditions as well.. because search conditions are built off of query params
           /**
           $this->params->query['job_subject'] = "All Subjects";
           $this->params->query['job_subject_id'] = "200";
           $this->params->query['job_category'] = "All Subjects Group";
           $this->params->query['job_category_id'] = "AllSubjects";
          **/

       } else {

           $this->set('app_allowed', $app_allowed) ;
           $this->set('tutor_approved_subjects', $tutor_approved_subjects) ;
       }

          // init default parameters
            $this->params->query['subject'] = !empty($this->params->query['job_subject']) ? $this->params->query['job_subject'] :  self::SUBJECT_ID_100; //"100";

            // debug($this->params->query['subject']); // die();
            // debug($this->params->query['subject_id']); die();
             if(empty($this->params->query['job_subject_id'] ) &&
                !empty($this->params->query['job_subject'])) {
                $this->params->query['subject_id'] = $subject_model->get_subject_id($this->params->query['subject']);
             } else {
                $this->params->query['subject_id'] = !empty($this->params->query['job_subject_id']) ? $this->params->query['job_subject_id'] :  self::SUBJECT_ID_100; //"100";
                //$this->params->query['subject'] = !empty($this->params->query['job_subject']) ? $this->params->query['job_subject'] : self::SUBJECT_ID_100; //"100";
             }
             $this->params->query['category'] = !empty($this->params->query['job_category']) ? $this->params->query['job_category'] : "400";
             $this->params->query['category_id'] = !empty($this->params->query['job_category_id']) ? $this->params->query['job_category_id'] : "400";
            //debug($this->params->query['category_id']); die();
            $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $cur_zip_code;
            $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;
            $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 40;
			$this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";


       if(!preg_match('/^[0-9]{1,3}$/', $this->params->query['distance'])) {
        	$this->Session->setFlash
								(
											sprintf(__d('users', 'You did not enter a properly formatted distance.')),
										    'default',
											 array('class' => 'alert alert-warning')
								 );
        }

        $zip_search_distance = $this->params->query['distance'];
         // debug($this->params->query); die();
     try{

        /**
         * make condition array
         */
        $conditions_for_search = array();
        $conditions_for_search['category'] = $this->params->query['category']; //!empty($this->params->query['cat']) ? $this->params->query['cat'] : "All Categories";
        $conditions_for_search['category_id'] = $this->params->query['category_id']; //!empty($this->params->query['cat']) ? $this->params->query['cat'] : "All Categories";

        $conditions_for_search['subject'] = $this->params->query['subject']; //!empty($this->params->query['subject']) ? $this->params->query['subject'] : "All Subjects";
        $conditions_for_search['subject_id'] = $this->params->query['subject_id']; //!empty($this->params->query['subject']) ? $this->params->query['subject'] : "All Subjects";
        //debug($conditions_for_search['subject']); die();
       //$result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        //debug("test here"); die();
         //debug($this->params->query); //die();
        // debug($conditions_for_search); //();
        $result = $students_model->find_by_params($this->Auth->user('id'), $kwd, $conditions_for_search,  $cur_zip_code, $this->Session->read('cur_zip_code'), $this->params->query);
        $return_array = $this->_get_nav_data($result, $cur_page);
        //debug($this->params->query); die();
        $this->Session->write('session_search_criteria', $this->params->query);
        //debug($this->Session->read('session_search_criteria')); die();
        $job_search_agents = $this->getAllJobSearchAgents($this->Auth->user('id'));
        $agent_count =   count($job_search_agents) ;
        $this->Session->write('agent_count', $agent_count);

      //debug($this->params->query); die();
       //debug($job_search_agents); die();
       //$ssa = $this->{$this->modelClass}->StudentSearchAgent->findById($id);
       //$this->Session->write('ssa', $search_agents);
       $this->set('job_search_agents', $job_search_agents);
      // debug("teststts")    ; die();
        $job_apps = $this->findJobApplications($this->Auth->user('id'), null, "");
        $this->set('job_apps', $job_apps);
         //debug("teststts")    ; die();
        //$subjects_and_categories = $tutors_model->get_all_subjects_and_categories();

        $this->set('zip', $this->Session->read('cur_zip_code'));
        //debug($return_array); die();
        $this->set('jobs', $return_array);
        $this->set('distance', $this->params->query['distance']);
        $this->set('radius_distance', $this->params->query['distance']);

       // debug($this->params->query['subject_id']); die();
        //debug($this->params->query['category']);
        //die();

        //if( $this->params->query['subject_id'] === '100') {
        if( $this->params->query['subject_id'] === self::SUBJECT_ID_100) {

             //$subjects_array = $tutors_model->get_all_subjects_for_tutor($this->Auth->user('id'));
             $subjects_array = $tutors_model->get_all_subjects_for_tutor($this->Auth->user('id'));

               //debug($subjects_array); die();
                   $return_array = array();
                   foreach ($subjects_array as $key1 => $value1) {

                           //$return_array[]  =  implode('(int) 0 =>', array_values($value1));
                           $return_array[]  =  implode('(int) 0 =>', $value1);
                    }


                //$tutor_approved_subjects =
                $subjects_array = $return_array;
                //$this->set('tutor_approved_subjects', $tutor_approved_subjects) ;
                //$this->Session->write('tutor_approved_subjects', $tutor_approved_subjects);
                 //debug($tutor_approved_subjects); die();
                foreach ($subjects_array as $key1 => $value1) {
                  foreach ($job_search_agents as $key => $value) {
                        //debug($value['JobSearchAgent']['subject']);
                        if(!empty($value['JobSearchAgent']['subject']) && !empty($value['JobSearchAgent']['subject_id'])) {
                           // debug($value['JobSearchAgent']['subject']);
                            //debug(explode(" ",$value['JobSearchAgent']['subject'])); die();
                            if(!in_array($value['JobSearchAgent']['subject'], $subjects_array ) ||
                               !in_array($value['JobSearchAgent']['subject_id'], $subjects_array ) ) {
                               // $count = $count +1;
                                //debug("Not in");
                                $subjects_array = $subjects_array + array($value['JobSearchAgent']['subject_id'] => $value['JobSearchAgent']['subject']);
                                 // $subjects_array = $subjects_array + explode(" ",$value['JobSearchAgent']['subject']);

                               // debug(array_values(array_values($value1)));
                               //debug( explode(" ",$value['JobSearchAgent']['subject'])); die();
                                //debug($value['JobSearchAgent']['subject']); die();


                            }
                        }

                     }
                 }

                // debug($count) ;
                //die();
             // array_multisort($subjects_array);
             // debug($subjects_array); die();
             $subjects_array = $first_sub = array(self::SUBJECT_ID_100 => 'All My Subjects') + $subjects_array;
             //debug($subjects_array); die();
             $return_array = array(self::SUBJECT_ID_100 => 'All My Subjects') + $return_array;
              array_unique($subjects_array);
              array_unique($return_array);
             Configure::write('popularsubjects',$return_array);

        } else if($this->params->query['subject_id'] === self::SUBJECT_ID_200) {
             //$subjects_array = $tutors_model->get_all_subjects();
            // debug("test");
              $subjects_array = $tutors_model->get_all_subjects();
              $return_array = array();
                   foreach ($subjects_array as $key1 => $value1) {
                            //debug(array_values($value1)); die();
                           $return_array[]  =  implode('(int) 0 =>', array_values($value1));
                    }


             $subjects_array = $return_array;
             $subjects_array = $first_sub = array(self::SUBJECT_ID_200 => 'All Subjects') + $subjects_array;
              array_unique($subjects_array);
             Configure::write('popularsubjects',$subjects_array);

        } else if( $this->params->query['subject_id'] === self::SUBJECT_ID_300) {
          //  debug("here"); die();
             $subjects_array = $tutors_model->get_all_subjects();
              $return_array = array();
                   foreach ($subjects_array as $key1 => $value1) {
                            //debug(array_values($value1)); die();
                           $return_array[]  =  implode('(int) 0 =>', array_values($value1));
                    }


             $subjects_array = $return_array;
             $subjects_array = $first_sub = array(self::SUBJECT_ID_300 => 'All Related Subjects') + $subjects_array;
              array_unique($subjects_array);
             Configure::write('popularsubjects',$subjects_array);

        } else if(!in_array($this->params->query['subject_id'], array(self::SUBJECT_ID_100, self::SUBJECT_ID_200, self::SUBJECT_ID_300))) {
                       // debug($this->params->query['category']);
                       // debug($this->params->query['category_id']); //die();
                       // debug($this->params->query['subject']);
                       // debug($this->params->query['subject_id']);// die();

                 if($this->params->query['category_id'] === "MySubjectsGroup") {
                       // debug($this->params->query['category']); die();
                     $subjects_array = $tutors_model->get_all_subjects_for_tutor($this->Auth->user('id'));
                     $return_array = array();
                     foreach ($subjects_array as $key1 => $value1) {
                            //debug(array_values($value1)); die();
                           $return_array[]  =  implode('(int) 0 =>', array_values($value1));
                    }


                     $subjects_array = $return_array;
                     sort($subjects_array);

                     foreach ($job_search_agents as $key => $value) {
                        //debug($value['JobSearchAgent']['subject']);
                        if(!empty($value['JobSearchAgent']['subject']) && !empty($value['JobSearchAgent']['subject_id'])) {
                            if(!in_array($value['JobSearchAgent']['subject'], $subjects_array ) ||
                               !in_array($value['JobSearchAgent']['subject_id'], $subjects_array ) ) {
                                $subjects_array = $subjects_array + array($value['JobSearchAgent']['subject_id'] => $value['JobSearchAgent']['subject']);
                            }
                        }

                     }

                      // debug($subjects_array); die();
                     $subject_id = $this->params->query['subject_id'];
                     $subject =    $this->params->query['subject'];

                      $subjects_array =  array($subject_id => $subject)  + array(self::SUBJECT_ID_100 => 'All My Subjects') +  $subjects_array;
                      $return_array =  array($subject_id => $subject)  + array(self::SUBJECT_ID_100 => 'All My Subjects') +  $return_array;
                       array_unique($return_array);
                      // debug($return_array); die();

                       array_unique($subjects_array);

                       Configure::write('popularsubjects',$return_array);

                 } else if($this->params->query['category_id'] == "RelatedSubjectsGroup") {

                     $subject_id = $this->params->query['subject_id'];
                     $subject =    $this->params->query['subject'];
                     $subjects_array = $tutors_model->get_all_subjects();

                     $return_array = array();
                     foreach ($subjects_array as $key1 => $value1) {
                            //debug(array_values($value1)); die();
                           $return_array[]  =  implode('(int) 0 =>', array_values($value1));
                    }


                     $subjects_array = $return_array;
                     sort($subjects_array);
                     //debug($subjects_array); die();
                      foreach ($job_search_agents as $key => $value) {
                        //debug($value['JobSearchAgent']['subject']);
                        if(!empty($value['JobSearchAgent']['subject']) && !empty($value['JobSearchAgent']['subject_id'])) {
                            if(!in_array($value['JobSearchAgent']['subject'], $subjects_array ) ||
                               !in_array($value['JobSearchAgent']['subject_id'], $subjects_array ) ) {
                                $subjects_array = $subjects_array + array($value['JobSearchAgent']['subject_id'] => $value['JobSearchAgent']['subject']);
                            }
                        }

                     }
                     $subjects_array = $first_sub = array($subject_id => $subject)  + array(self::SUBJECT_ID_300 => 'All Related Subjects') + $subjects_array;
                    // debug($subjects_array); die();
                     array_unique($subjects_array);
                     Configure::write('popularsubjects',$subjects_array);

                 } else  if($this->params->query['category_id'] == "AllSubjects") {

                     $subject_id = $this->params->query['subject_id'];
                     $subject =    $this->params->query['subject'];
                     $subjects_array = $tutors_model->get_all_subjects();

                      $return_array = array();
                     foreach ($subjects_array as $key1 => $value1) {
                            //debug(array_values($value1)); die();
                           $return_array[]  =  implode('(int) 0 =>', array_values($value1));
                    }


                     $subjects_array = $return_array;
                     sort($subjects_array);
                     $subjects_array = $first_sub = array($subject_id => $subject) + array(self::SUBJECT_ID_300 => 'All Subjects') +  $subjects_array;
                      array_unique($subjects_array);
                     Configure::write('popularsubjects',$subjects_array);
                 }else {
                     //debug("here"); die();
                     $subject_id = $this->params->query['subject_id'];
                     $subject =    $this->params->query['subject'];
                     $subjects_array = $tutors_model->get_all_subjects();
                      $return_array = array();
                     foreach ($subjects_array as $key1 => $value1) {
                            //debug(array_values($value1)); die();
                           $return_array[]  =  implode('(int) 0 =>', array_values($value1));
                    }


                     $subjects_array = $return_array;
                     sort($subjects_array);
                     $subjects_array = $first_sub = array($subject_id => $subject) + $subjects_array;
                      array_unique($subjects_array);
                     Configure::write('popularsubjects',$subjects_array);
                 }
        }
       //Configure::write('popularsubjects',array('100' => 'All My Subjects'));

       // debug($this->params->query['subject']);
        if($this->params->query['subject'] === self::SUBJECT_ID_100) {
            $this->params->query['subject'] = 'All My Subjects';
             $this->params->query['subject_id'] = self::SUBJECT_ID_100;
        } else {
             $this->set('subject',  $this->params->query['subject']);
             $this->set('subject_id',  $this->params->query['subject_id']);
             //$this->set('category', $this->params->query['category_id']);

        }
        //$this->set('subject',  $this->params->query['subject']);
        //$this->set('subject_id',  $this->params->query['subject_id']);
        $this->set('category', $this->params->query['category_id']);

         if(!empty($this->params->query['kwd'])) {
             $this->set('sortBy', $this->params->query['kwd']);
            } else {
                $this->set('sortBy', h("Most Recent"));
         }

       // $this->set('subjects_and_categories', $subjects_and_categories);

       } catch (NotException $e) {
         $this->redirect(array('action' => 'job_search_results_auth'));
       }

   // }

  }

 //?job_subject=Algebra&job_subject_id=Algebra&zip_code=30326&distance=40&agent_name=diffEquations&agent_id=1&update

            //debug($this->params->query);// die();
            $update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 0;
			$this->set('update_agent', $update_agent);

			$agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
	        $this->set('agent_id', $agent_id);

            $id = !empty($this->params->query['id']) ? $this->params->query['id'] : 0;
	        $this->set('id', $id);

          // $this->Session->delete('agent_name');
           $agent_name= !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
           $this->set('agent_name', $agent_name);
}

public function job_search_results() {
    	if ($this->Auth->loggedIn()) {
     	  return $this->redirect(array('action' => 'job_search_results_auth'));
        }

      $warning = $this->Session->read('warning');
       //debug($warning); die();
       $this->set('warning', $warning);
       $this->Session->delete('warning');

    $this->layout='default';
    $radiusSearch = new ZipSearch();
    $conditions = array();
    $students_model = new Student();
    $tutors_model = new Tutor();
    $tutor_subject_model = new TutorSubject();
    $subject_model = new Subject();

    if($this->request->is('get')) {
         $id = null;
         $rs = null;

         $posts_per_page = 7;
         $total_post_count = 0;
         $cur_page = 1;
         $start_page = 1;
         $display_page_navigation = 9;

         if(!empty($_GET['cur_page'])){
          $cur_page = $_GET['cur_page'];
         }

        /**
       * set current zip code
       */
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
       // ovewrites the computed zip code in the session if user entered a zip code manually
      //  debug($cur_zip_code);
       $this->set('city', $this->_set_city_for_zip($cur_zip_code));
       $this->Session->write('cur_zip_code', $cur_zip_code);
      // $this->set('cur_zip_code', $cur_zip_code);
       $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
  /**
   if(!empty($this->params->query['zip_code'])) {
       if( $this->params->query['zip_code'] === "") {
           $this->Session->write('cur_zip_code', $this->params->query['zip_code']);
       } else {
         $this->Session->write('cur_zip_code', $cur_zip_code);
       }
    } else {
         $this->Session->write('cur_zip_code', $cur_zip_code);
    }
    **/
    // if(empty($this->params->query['zip_code'])  || $this->params->query['zip_code'] === "") {
       //  $this->Session->write('cur_zip_code', "");
    // }
          //debug($this->params->query['job_subject']);
         // debug($this->params->query['job_category']); die();
          // init default parameters
            $this->params->query['subject'] = !empty($this->params->query['job_subject']) ? $this->params->query['job_subject'] : self::SUBJECT_ID_100; //"100";

           // debug($this->params->query['subject']); die();
            //$this->params->query['subject_id'] = !empty($this->params->query['job_subject_id']) ? $this->params->query['job_subject_id'] :  self::SUBJECT_ID_100; //"100";

            $this->params->query['category'] = !empty($this->params->query['job_category']) ? $this->params->query['job_category'] : "400";
            $this->params->query['category_id'] = !empty($this->params->query['job_category_id']) ? $this->params->query['job_category_id'] : "400";
            //debug($this->params->query['category_id']); die();

            // debug($this->params->query['subject']); // die();
            // debug($this->params->query['subject_id']); die();
             if(empty($this->params->query['job_subject_id'] ) &&
                !empty($this->params->query['job_subject'])) {
                $this->params->query['subject_id'] = $subject_model->get_subject_id($this->params->query['subject']);
             } else {
                $this->params->query['subject_id'] = !empty($this->params->query['job_subject_id']) ? $this->params->query['job_subject_id'] :  self::SUBJECT_ID_100; //"100";
                //$this->params->query['subject'] = !empty($this->params->query['job_subject']) ? $this->params->query['job_subject'] : self::SUBJECT_ID_100; //"100";

             }
            $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $cur_zip_code;
            $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;
            $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
			$this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";


       if(!preg_match('/^[0-9]{1,3}$/', $this->params->query['distance'])) {
        	$this->Session->setFlash
								(
											sprintf(__d('users', 'You did not enter a properly formatted distance.')),
										    'default',
											 array('class' => 'alert alert-warning')
								 );
        }

     $zip_search_distance = $this->params->query['distance'];
         // debug($this->params->query); die();
     try{

        /**
         * make condition array
         */
        $conditions_for_search = array();
        $conditions_for_search['category'] = $this->params->query['category']; //!empty($this->params->query['cat']) ? $this->params->query['cat'] : "All Categories";
        $conditions_for_search['category_id'] = $this->params->query['category_id']; //!empty($this->params->query['cat']) ? $this->params->query['cat'] : "All Categories";

        $conditions_for_search['subject'] = $this->params->query['subject'];; //!empty($this->params->query['subject']) ? $this->params->query['subject'] : "All Subjects";
        $conditions_for_search['subject_id'] = $this->params->query['subject_id']; //!empty($this->params->query['subject']) ? $this->params->query['subject'] : "All Subjects";
        //debug($conditions_for_search['subject']); die();
       //$result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        //debug("test here"); die();
         //debug($this->params->query); die();
        $result = $students_model->find_by_params(null, $kwd, $conditions_for_search,  $this->params->query['zip_code'], $this->Session->read('cur_zip_code'), $this->params->query);
        $return_array = $this->_get_nav_data($result, $cur_page);

        $this->set('zip', $this->Session->read('cur_zip_code'));
       // debug($return_array); die();
        $this->set('jobs', $return_array);
        $this->set('distance', $this->params->query['distance']);
        $this->set('radius_distance', $this->params->query['distance']);



       // debug($this->params->query); die();
        if($this->params->query['subject'] === self::SUBJECT_ID_100) {
            $this->params->query['subject'] = 'All My Subjects';
             $this->params->query['subject_id'] = self::SUBJECT_ID_100; //'100';
        } else {
             $this->set('subject',  $this->params->query['subject']);
             $this->set('subject_id',  $this->params->query['subject_id']);
             //$this->set('category', $this->params->query['category_id']);

        }
        //$this->set('subject',  $this->params->query['subject']);
        //$this->set('subject_id',  $this->params->query['subject_id']);
        //$this->set('category', $this->params->query['category_id']);

         if(!empty($this->params->query['kwd'])) {
             $this->set('sortBy', $this->params->query['kwd']);
            } else {
                $this->set('sortBy', h("Most Recent"));
         }

        if(($this->params->query['subject_id'] === self::SUBJECT_ID_100 || $this->params->query['subject_id'] === self::SUBJECT_ID_200)
         && ($this->params->query['category_id'] === 'AllCategories' || $this->params->query['category_id'] === '400')
         ){
              $subjects_array = $tutors_model->get_all_subjects();
              $return_array = array();
                   foreach ($subjects_array as $key1 => $value1) {

                           $return_array[]  =  implode('(int) 0 =>', $value1);
                    }


              $subjects_array = $return_array;
              sort($subjects_array);
              $subjects_array = $first_sub = array(self::SUBJECT_ID_100 => 'All Subjects') + $subjects_array;
              Configure::write('popularsubjects',$subjects_array);

        } else if(!in_array($this->params->query['subject_id'], array(self::SUBJECT_ID_100, self::SUBJECT_ID_200, self::SUBJECT_ID_300))) {
                       // debug("test 1"); die();
                 if(!empty($this->params->query['category_id']) &&
                     $this->params->query['category_id'] != 'AllCategories') {
                        //debug($this->params->query['category']); die();
                     $subjects_array = $tutors_model->get_all_subjects($this->params->query['category_id']);
                     //$subjects_array = $tutors_model->get_all_subjects();
                      $return_array = array();
                           foreach ($subjects_array as $key1 => $value1) {

                                   $return_array[]  =  implode('(int) 0 =>', $value1);
                            }


                     $subjects_array = $return_array;
                    // debug($subjects_array); die();
                    sort($subjects_array);
                     $subject_id = $this->params->query['subject_id'];
                     $subject =    $this->params->query['subject'];

                     $subjects_array =  array($subject_id => $subject) + array(self::SUBJECT_ID_100 => 'All Subjects') +  $subjects_array;
                     //$subjects_array = $subjects_array + array('100' => 'All My Subjects');
                      //debug($subjects_array); die();
                     Configure::write('popularsubjects',$subjects_array);

                 } else  if(!empty($this->params->query['category_id']) &&
                     $this->params->query['category_id'] === 'AllCategories') {
                     $subjects_array = $tutors_model->get_all_subjects();

                     $return_array = array();
                               foreach ($subjects_array as $key1 => $value1) {

                                       $return_array[]  =  implode('(int) 0 =>', $value1);
                                }


                      $subjects_array = $return_array;
                      sort($subjects_array);
                     $subject_id = $this->params->query['subject_id'];
                     $subject =    $this->params->query['subject'];

                     $subjects_array =  array($subject_id => $subject) +  array(self::SUBJECT_ID_100 => 'All Subjects') +  $subjects_array;
                     Configure::write('popularsubjects',$subjects_array);
                 }

                  //$subject_id = $this->params->query['subject_id'];
                 // $subject =    $this->params->query['subject'];

                 // $this->set('category', $this->params->query['category_id']);
        }else if(($this->params->query['subject_id'] === self::SUBJECT_ID_100 ||
                   $this->params->query['subject_id'] === self::SUBJECT_ID_200) ) {
                   // debug("test 2"); die();
                     if($this->params->query['category_id'] != 'AllCategories' &&
                            $this->params->query['category_id'] != '400') {

                          $subjects_array = $tutors_model->get_all_subjects($this->params->query['category_id']);
                          $subject_id = $this->params->query['subject_id'];
                          $subject =    $this->params->query['subject'];


                          $return_array = array();
                               foreach ($subjects_array as $key1 => $value1) {

                                       $return_array[]  =  implode('(int) 0 =>', $value1);
                                }


                          $subjects_array = $return_array;
                          sort($subjects_array);
                          $subjects_array =  array($subject_id => $subject) + array(self::SUBJECT_ID_100 => 'All Subjects') +  $subjects_array;
                          Configure::write('popularsubjects',$subjects_array);
                     }


            }

        $this->set('category', $this->params->query['category_id']);

       } catch (NotException $e) {
         $this->redirect(array('action' => 'job_search_results'));
       }

   // }

  }

  }

protected function _set_city_for_zip($zip_code){

        $radiusSearch = new ZipSearch();
        $tutors_model = new Tutor();
        $search_city = "";
        $result = array();

        $curr_session_zip = $this->Session->read('cur_zip_code');
        //debug($curr_session_zip); die();
	   	if(empty($curr_session_zip) || $curr_session_zip != $zip_code ){

            $result = $tutors_model->find_city_ByZipCode($zip_code);
            if(!empty($result) && isset($result['city'])) {
              $search_city = $result['city'];
            }

            $this->Session->write('search_city', $search_city);
         }
          // debug($search_city); die();
         return $search_city;
}
protected function _get_nav_data($result_array, $cur_page){
    //debug($result_array); die();
    $posts_per_page = 4 ;
    $return_value = array();
    $total_post_count = sizeof($result_array);
    //debug($total_post_count); die();
    $total_page_count = ceil($total_post_count / $posts_per_page);

    //rearrange the array so the indexing below is right
    if(!empty($result_array)) {
        $result_array = array_values($result_array);
     }
    //debug($result_array); die();
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


public function findJobApplications($id, $conditions_for_search=array(), $kwd) {

   	$this->layout='tutor';
     $my_job_apps = null; //$search_agent_model;
     // debug("teststts")    ; die();
     $my_job_apps  = $this->{$this->modelClass}->findJobApplications($id, $conditions_for_search, $kwd);

      return $my_job_apps;

    //query the tutor_job_application table and
}



public function my_job_Applications() {

   	 $this->layout='tutor';
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


         $my_job_apps = null;
         $job_app_subjects = array();

         //This call will filter for just the Jobs applied for in the Specific Subject as specified in Search Conditions
         $my_job_apps  = $this->findJobApplications($this->Auth->user('id'), $conditions_for_search, $this->params->query['kwd']);

         //make a second call with empty Search conditions. So that All the Job Applied for are brought back
         //for display in the "Most Recent Job Apps" section" and also make sure the drop down shows all Subjects applied for
         $my_job_apps_all  = $this->findJobApplications($this->Auth->user('id'),"",  $this->params->query['kwd']);

        //debug($my_job_apps_all); die();
        if(!empty($my_job_apps_all)) {
            $i=0;
          foreach($my_job_apps_all as $key => $value) {
                 $job_app_subjects[] = array($value['job_id'] => h($value['job_subject']));
                 $i++;
          }
        }

        $return_array = array();

        $i=0;
        if(!empty($job_app_subjects)) {
          foreach ($job_app_subjects as $key1 => $value1) {
                           $return_array[]  =  implode('(int) 0 =>', $value1);
                           $i++;
             }
           }

       // die();
       // debug($return_array); die();
         $job_app_subjects = $return_array;
        //$job_app_subjects = array(self::SUBJECT_ID_100 => 'All') + $job_app_subjects;
        //Remove duplicate elements from Array so drop down only have unique subjects
         //$job_app_subjects = array_unique($job_app_subjects);
         sort($job_app_subjects);

         //debug($job_app_subjects);
         //Had to do this b/c for whatever reason the optin default behaviour does not work on View Screen. Weired!!!

       if(!empty($this->params->query['job_subject']) && $this->params->query['job_subject'] != 'All') {

         $subject_araay = array('0' => $this->params->query['job_subject']);
         $job_app_subjects =  array(self::SUBJECT_ID_100 => 'All') + $job_app_subjects;

         $job_app_subjects = array_merge($subject_araay, $job_app_subjects);

        } else {
            $job_app_subjects = array(self::SUBJECT_ID_100 => 'All') + $job_app_subjects;

        }
         $job_app_subjects = array_unique($job_app_subjects);
        // sort($job_app_subjects);
       // debug($job_app_subjects); die();
        Configure::write('job_app_subjects',$job_app_subjects);

         $this->set('recent_job_apps', h($my_job_apps_all));

         $my_job_apps = $this->_get_nav_data($my_job_apps, $cur_page);
         $this->set('job_apps', $my_job_apps);

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

public function submit_job_application() {

   	$this->layout='tutor';
    $id = null;
   // debug($this->request->data); die();
    // $this->Session->delete('error_array');

   //debug($job_id); die();

   // debug($this->{$this->modelClass}->TutorJobApplication->validationErrors); //die();

    $studentJobPost_model = new StudentJobPost();
    $student_model = new Student();

   //Tutor must be approved in the subject prior to applying for any job in subject
       if(empty($this->request->data['TutorJobApplication']['job_subject']) ||
        (!$data = $this->{$this->modelClass}->TutorSubject->find('first', array(
                            'conditions' => array(
                                'TutorSubject.tutor_id' => $this->Auth->user('id'),
                                 'TutorSubject.subject_name' => $this->request->data['TutorJobApplication']['job_subject'],
                                 'TutorSubject.approval_status' => 'Y'
                                  )))))
      {

                    $message = 'You mut be approved for this Subject before applying';
                    $warning = true;
                    $this->Session->write('warning', $warning);
                    $this->Session->setFlash($message, 'custom_msg');
                    return $this->redirect(array('action' => 'manage_subjects'));
       }

       //Tutor Profile must be completed first (Hourly rate, Radius etc...))
        $tutor_profile = $this->{$this->modelClass}->TutorProfile->find('first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'))));

       if(empty($tutor_profile) )  {
         $this->Session->setFlash(
								 sprintf(__d('users', 'Please  Complete Your Profile before applying for Jobs')),
						         'default',
								  array('class' => 'alert alert-warning')
							   );
        return $this->redirect(array('action' => 'manage_basic_profile'));

       } else if (empty($tutor_profile['TutorProfile']['basicProfile_status']))  {
         $this->Session->setFlash(
								 sprintf(__d('users', 'Please  Complete Your Basic Profile before applying for Jobs')),
						         'default',
								  array('class' => 'alert alert-warning')
							   );
        return $this->redirect(array('action' => 'manage_basic_profile'));

       } else if(empty($tutor_profile['TutorProfile']['publicProfile_status']))
         {
         $this->Session->setFlash(
								 sprintf(__d('users', 'Please Complete Your Public Profile before applying for Jobs')),
						         'default',
								  array('class' => 'alert alert-warning')
							   );
        return $this->redirect(array('action' => 'manage_public_profile'));

       }

       //Job being applied to must exist, been verified and current (ie, Not expired))
     if(empty($this->request->data['TutorJobApplication']['job_id'])
       || (!$data = $studentJobPost_model->find(
                            'first', array(
                            'conditions' => array(
                                'StudentJobPost.student_id' => $this->request->data['TutorJobApplication']['student_id'],
                                'StudentJobPost.job_id'  => $this->request->data['TutorJobApplication']['job_id'],
                                'StudentJobPost.verified'  => 1,
                                'StudentJobPost.exp_date >'  => date('Y-m-d H:i:s') ))))
      ) {
       //throw new NotFoundException(__('Invalid Request'));
       $this->Session->setFlash(
								 sprintf(__d('users', 'The Job you are trying to apply for no longer exists')),
						         'default',
								  array('class' => 'alert alert-warning')
							   );
        return $this->redirect(array('action' => 'job_search_results_auth'));
     }


     $user_tz = $this->Session->read('user_tz');
      if ($this->Session->check('user_tz'))  {
	       $tz = $this->Session->read('user_tz');
	      // debug($tz); //die();
	       $this->set_timezone($tz);

	   } else {
	      date_default_timezone_set('America/New_York');
	   // $tz = Configure::read('Config.timezone');
    }


     //Keep user from applying more than once
    if(!empty($this->request->data['TutorJobApplication']['job_id'])
    && ($data = $this->{$this->modelClass}->TutorJobApplication->find(
                            'first', array(
                            'conditions' => array(
                                'TutorJobApplication.tutor_id' => $this->Auth->user('id'), //request->data['TutorJobApplication']['tutor_id'],
                                'TutorJobApplication.job_id'  => $this->request->data['TutorJobApplication']['job_id']))))
      ) {
       //debug($data);
        $apply_date = date("m-d-Y", strtotime($data['TutorJobApplication']['application_date']));
       	$this->Session->setFlash(
								 sprintf(__d('users', 'Our records show that you have already applied for this Job on <b>'.$apply_date.'</b><br /> Please check your Job Application list and send a follow up message.')),
						         'default',
								  array('class' => 'alert alert-warning')
							   );
        return $this->redirect(array('action' => 'job_details_auth', $this->request->data['TutorJobApplication']['job_id']));

     }

   if($this->request->is('post')) {
      if(!empty($this->request->data) )  {
        //debug($this->request->data); die();
          $this->request->data['TutorJobApplication']['tutor_id'] = $this->Auth->user('id');
          $job_id = $this->request->data['TutorJobApplication']['job_id'];
          $app_date = date('Y-m-d H:i:s');
          $job_post_date = $this->request->data['TutorJobApplication']['job_post_date']; //date("m-d-Y H:i:s", strtotime($this->request->data['TutorJobApplication']['job_post_date']));

          //debug($job_post_date); die();
          //In order for this to be turned into a valid Date Object, $job_post_date has to come in as Y-m-d
          //I had to change this on front end through the use $job_post_date_hidden

           $job_post_date = new DateTime($job_post_date);
            if(!$job_post_date) {
                throw new NotFoundException(__('Invalid Job Post Date'));
            } else {
                $job_post_date = $job_post_date->format('Y-m-d');
            }

          //debug($job_post_date); die();
          $this->request->data['TutorJobApplication']['job_post_date'] = $job_post_date; // $date->format('Y-m-d H:i:s');
          $this->request->data['TutorJobApplication']['applicant_email'] = $sender = $this->Session->read('email_addr');
          $student = $student_model->findById($this->request->data['TutorJobApplication']['student_id']);

          $tutor = $this->{$this->modelClass}->findById($this->Auth->user('id'));

          $member_id = $tutor['Tutor']['member_id'] ;
          // debug($member_id); die();
          //debug($sender); die();
          $this->request->data['TutorJobApplication']['student_email'] = $student['Student']['email'];
          $this->request->data['TutorJobApplication']['member_id'] = $member_id; //$student['Student']['email'];

          //Will pass in the unique member_id assigned to tutor during Registration.. Have yet to do this at Reg
          //$this->request->data['TutorJobApplication']['profile_link'] = $full_url = Router::url(array('controller' => 'tutors', 'action'=>'tutor_details_profile_auth', $member_id), true);

           //get the tutor Subj Creds if he/she asks to include it
         //debug($this->request->data); die();
          $this->{$this->modelClass}->TutorJobApplication->set(array(

                                   'student_id' => $this->request->data['TutorJobApplication']['student_id'],
                                   'job_requester' => $this->request->data['TutorJobApplication']['student_name'],
                                   'tutor_id' => $this->request->data['TutorJobApplication']['tutor_id'],
                                   'job_id' => $this->request->data['TutorJobApplication']['job_id'],
                                   'job_subject' => $this->request->data['TutorJobApplication']['job_subject'],
                                   'job_category' => $this->request->data['TutorJobApplication']['job_category'],
                                   'job_title' => $this->request->data['TutorJobApplication']['job_title'],
                                   'job_desc' => $this->request->data['TutorJobApplication']['job_desc'],

                                   'job_post_date' => $job_post_date, //this->request->data['TutorJobApplication']['job_post_date'],
                                   'profile_pic' => $this->request->data['TutorJobApplication']['profile_pic'],
                                   'job_city' => $this->request->data['TutorJobApplication']['job_city'],
                                   'job_state' => $this->request->data['TutorJobApplication']['job_state'],
                                   'job_zip_code' => $this->request->data['TutorJobApplication']['job_zip'],

                                   'job_applicant' => $this->request->data['TutorJobApplication']['applicant_name'],
                                   'applicant_avg_rating_score' => $this->request->data['TutorJobApplication']['rating_score'],
                                   'applicant_ratings' => $this->request->data['TutorJobApplication']['ratings'],
                                   'application_date' => $app_date, //$this->request->data['TutorJobApplication']['application_date'],
                                   'personal_message' => $this->request->data['TutorJobApplication']['personal_message'],
                                   'subject_creds' => $this->request->data['TutorJobApplication']['subject_creds'],
                                  // 'applicant_pic' => $this->request->data['TutorJobApplication']['profile_pic'],
                                   'job_hourly_rate' => $this->request->data['TutorJobApplication']['hourly_rate'],
                                   'applicant_zip_code' => $this->request->data['TutorJobApplication']['job_zip'],
                                   'include_creds' => $this->request->data['TutorJobApplication']['creds'],
                                   'message_copy' =>  $this->request->data['TutorJobApplication']['message_copy'],


             ));

        if( $this->{$this->modelClass}->TutorJobApplication->validates(array('fieldList' => array('personal_message'))))
         {

               // if($this->{$this->modelClass}->TutorJobApplication->saveJobApplication($id, $this->request->data)  &&
                      //        $this->_send_job_application($job_id, $this->request->data))
                 // {
                   // debug($this->request->data); die();
                 if($this->{$this->modelClass}->TutorJobApplication->saveJobApplication($id, $this->request->data) )

        		   {
        	    					        //debug('test'); die();
                                           // $this->_send_job_application($job_id, $this->request->data);
        	    							$this->Session->setFlash
        	    									(
        	    												sprintf(__d('users', 'Your Job Appication has been successfully submitted.')),
        	    											   'default',
        	    												array('class' => 'alert alert-success')
        	    									 );


                                               // return $this->redirect(array('action' => 'view_job_application', $this->request->data['TutorJobApplication']['job_id']));

                   } else {
        	    					      $this->Session->setFlash
        	 						     		(
        	 						     					sprintf(__d('users', 'Something went wrong.')),
        	 						     					'default',
        	 						     					 array('class' => 'alert alert-warning')
        	    									 );


        				  }

         } else {

                     //debug($this->{$this->modelClass}->TutorJobApplication->validationErrors); die();
                     //$this->set('error_array', $this->{$this->modelClass}->TutorJobApplication->validationErrors);
                     $this->Session->write('error_array', $this->{$this->modelClass}->TutorJobApplication->validationErrors);

                    /** $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );**/
                        return $this->redirect(array('action' => 'job_details_auth', $this->request->data['TutorJobApplication']['job_id']));


                     }

                     // debug("here back"); die();

                     //if(  $this->_send_job_application($job_id, $this->request->data)) {

                         // debug($this->request->data); die();
                           $this->Session->write('jobAppData', $this->request->data);

                           $email_subject = $this->request->data['TutorJobApplication']['applicant_name'].' has applied for your '.$this->request->data['TutorJobApplication']['job_subject'].' request on Wizwonk';
						   $email_type = 'job_application';
						   $email_format ='html';
						   $email_template = $this->_pluginDot() .$email_type;
						   $layout = 'default';
						   $from_address = 'info@wizwonk.com';
						   $to_address = array($this->request->data['TutorJobApplication']['applicant_email']); //'damien.gueye@candidpartners.com' ; //
						   $email_instance = null;;
			               $sender_name ='Wizwonk';
			               $admin=null;
			               $userData = $this->request->data;

			               $viewVariables = array('model' => $this->modelClass,
									              'userData' => $userData);

                         // $this->_send_job_application($this->request->data);
                          $this->TransEmail->_sendEmail($this->{$this->modelClass}->data,$email_subject,$email_format,
						  								$email_instance, $from_address, $to_address,
						  								$sender_name, $email_template, $layout,
								                        $viewVariables, $admin, $email_type, $this->modelClass);

                           // $this->send_emails($this->request->data);
                          //  debug($jobAppData); die();

                            // debug("Stop"); die();
                             return $this->redirect(array('action' => 'view_job_application/'.$job_id));

                    // }
                    //debug($this->{$this->modelClass}->TutorJobApplication->validationErrors['personal_message'][0]); die();

                   // return $this->redirect(array('action' => 'job_details_auth', $this->request->data['TutorJobApplication']['job_id']));


          }
    }




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

public function view_job_application($job_id=null) {
   	$this->layout='tutor';

    //debug($job_id); die();
    if(empty($job_id) ) {
       throw new NotFoundException(__('Invalid Request'));
     }

   $tutor_model = new Tutor();
   if($this->request->is('get')) {
     //debug($job_id); die();

    //query the tutor_job_application table and pull the job app details (App_date and subject_name)

      $job_app_details = $tutor_model->find_job_app_by_id($this->Auth->user('id'), $job_id);

       //debug($job_app_details); die();
       $this->set('job_app_details', $job_app_details);

      if(!empty($job_app_details)) {
        $this->set('job_id',          h($job_app_details[0]['job_id']));
        $this->set('job_category',     h($job_app_details[0]['job_category']));
        $this->set('subject_name',     h($job_app_details[0]['job_subject']));
        $this->set('job_desc',         h($job_app_details[0]['job_desc']));

        $this->set('job_title',        h($job_app_details[0]['job_title']));
        $this->set('job_post_date',    h($job_app_details[0]['job_post_date']));
        $this->set('job_requester',    h($job_app_details[0]['job_requester']));

        $this->set('job_city',         h($job_app_details[0]['job_city']));
        $this->set('job_state',        h($job_app_details[0]['job_state']));
        $this->set('job_zip_code',     h($job_app_details[0]['job_zip_code']));
        $this->set('job_applicant',    h($job_app_details[0]['job_applicant']));

        $this->set('personal_message',     h($job_app_details[0]['personal_message']));
        $this->set('subject_creds',       h($job_app_details[0]['subject_creds']));
        $this->set('job_application_date', h($job_app_details[0]['application_date']));

        $this->set('applicant_ratings',          h($job_app_details[0]['applicant_ratings']));
        $this->set('applicant_avg_rating_score', h($job_app_details[0]['applicant_avg_rating_score']));

       }

    }

   // debug($this->{$this->modelClass}->TutorJobApplication->validationErrors); die();
   //return $this->redirect(array('action' => 'view_job_application'));

    // $this->set('jobAppData' , $this->Session->read('jobAppData'));
    //$this->Session->delete('jobAppData');

 }

public function job_details_auth($job_id=null) {

     $this->layout='tutor';
     if(empty($job_id) ) {
       throw new NotFoundException(__('Invalid Request'));
     }

     //Prevent user from landing on Job App screen if user has not been approved
     // for the specific subject
      /**   Need to figure out how to send in the subject_id or name
        if((!$data = $this->{$this->modelClass}->TutorSubject->find('first', array(
                            'conditions' => array(
                                'TutorSubject.tutor_id' => $this->Auth->user('id'),
                                 'TutorSubject.subject_name' => $this->request->data['TutorJobApplication']['job_subject'],
                                 'TutorSubject.approval_status' => 'Y'
                                  )))))
      {
                     $this->Session->setFlash(
								 sprintf(__d('users', 'You mut be approved for this Subject before applying')),
						         'default',
								  array('class' => 'alert alert-warning')
							   );
                   return $this->redirect(array('action' => 'manage_subjects'));
       }
       **/
     $job_details = array();
     $students_model = new Student();
     $tutor_model = new Tutor();
     $tutor_subj_creds = array();
     $warning = false;
     $tutor_profile = "";

    if($this->request->is('get')) {

         $tutor_profile = $this->{$this->modelClass}->TutorProfile->find('first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'))));
        //debug($tutor_profile);die();
       // empty($tutor_profile['TutorProfile']['basicProfile_status'])
       //if(empty($tutor_profile) || $tutor_profile['TutorProfile']['profile_status_count'] < '4' )  {
       if(
          empty($tutor_profile)||
          empty($tutor_profile['TutorProfile']['basicProfile_status']) ||
          empty($tutor_profile['TutorProfile']['publicProfile_status'])
          )  {

             $message = 'Please Complete Your Profile before applying for Jobs';
             $warning = true;
             $this->Session->write('warning', $warning);
             $this->Session->setFlash($message, 'custom_msg');


        return $this->redirect(array('action' => 'job_search_results_auth'));

       } else if (empty($job_id) || (!$job_details = $students_model->find_job_by_id($job_id)) ) {
             $message = 'Job could not be found';
             $warning = true;
             $this->Session->write('warning', $warning);
             $this->Session->setFlash($message, 'custom_msg');
             return $this->redirect(array('action' => 'job_search_results_auth'));

       } else if(! $job_details[0]['verified']) {

             $message = 'Job could not be found';
			             $warning = true;
			             $this->Session->write('warning', $warning);
			             $this->Session->setFlash($message, 'custom_msg');
             return $this->redirect(array('action' => 'job_search_results_auth'));

       } else {
            //debug($job_details); die();
            $this->set('job_details', $job_details);
            $subj_id = $job_details[0]['job_subject_id'];
            $subj_creds = $tutor_model->get_tutor_subj_cred($this->Auth->user('id'), $subj_id);
           // debug($subj_id); debug($subj_creds); die();
            $this->set('subj_creds', $subj_creds);
        }

        //$tutor_profile_photo = $job_details[0]['job_subject_id'];
        $tutor_profile_photo = $tutor_model->get_tutor_profile_pic($this->Auth->user('id'));
        if(!empty($tutor_profile_photo)){
          $this->set('profile_pic', $tutor_profile_photo);
         }
         // $tutor_profile = $tutor_model->get_tutor_profile($this->Auth->user('id'));
          // debug($tutor_profile['TutorProfile']); die();
        if(!empty($tutor_profile)) {
           // debug($tutor_profile); die();
          $first_name = $this->Session->read('username');
          $last_name = $this->Session->read('lastname');
          $last_name = substr($last_name,0,1);
          $blank = "  ";
          $full_name = "$first_name $blank $last_name";

         // debug($full_name); die();
         // $zip_code = $this->Session->read('zip_code');
          $this->set('full_name', $full_name);
          $this->set('city', $tutor_profile['TutorProfile']['city']);
          $this->set('state', $tutor_profile['TutorProfile']['state']);
          $this->set('profile_zip_code',  $tutor_profile['TutorProfile']['zip_code']);
          $this->set('degree', $tutor_profile['TutorProfile']['degree']);
          $this->set('school', $tutor_profile['TutorProfile']['school']);
          $this->set('hourly_rate', $tutor_profile['TutorProfile']['hourly_rate']);
          $this->set('travel_radius', $tutor_profile['TutorProfile']['travel_radius']);
          $this->set('bg_checked', $tutor_profile['TutorProfile']['background_checked']);
          $this->set('rating_score', $tutor_profile['TutorProfile']['avg_rating_score']);
          $this->set('ratings', $tutor_profile['TutorProfile']['ratings']);
         }
        // debug($tutor_ratings); die();
        $tutor_ratings = $tutor_model->get_tutor_ratings($this->Auth->user('id'));
       // debug($tutor_ratings); die();
        if(!empty($tutor_ratings)) {

          $this->set('rating_score', $tutor_ratings['avg_rating_score']);
          $this->set('ratings', $tutor_ratings['ratings']);

         }

         $error_array = $this->Session->read('error_array');
         if(!empty($error_array)) {
           $this->set('error_array', $this->Session->read('error_array'));
           $this->Session->delete('error_array');
         }

   }
}



public function job_details($job_id=null) {
      $this->layout='default';
    /** if(empty($job_id) ) {
       throw new NotFoundException(__('Invalid Request'));
     }
     **/

     //debug($job_id); die();
     $job_details = array();
     $students_model = new Student();

     if ($this->Auth->loggedIn()) {
	   	  	return $this->redirect(array('action' => 'job_details_auth/'.$job_id));

     } else if(empty($job_id) || (!$job_details = $students_model->find_job_by_id($job_id))) {
             $message = 'Job could not be found';
             $warning = true;
             $this->Session->write('warning', $warning);
             $this->Session->setFlash($message, 'custom_msg');
             return $this->redirect(array('action' => 'job_search_results'));

      } else if(!$job_details[0]['verified']) {

             $message = 'Job could not be found';
			 $warning = true;
			 $this->Session->write('warning', $warning);
			 $this->Session->setFlash($message, 'custom_msg');
             return $this->redirect(array('action' => 'job_search_results'));

       } else if(!empty($job_details)) {
             $this->set('job_details', $job_details);
        }

          /**else {
                 $message = 'The Job could not be found';
                 $this->Session->setFlash($message, 'custom_msg');
                 return $this->redirect(array('controller' =>'/', 'action' => '/job_search_error/'.$job_id));
              }
           **/
     }


public function job_search_tools() {
			$this->layout='tutor';

    if($this->request->is('get')) {

         $job_search_agents = null; //$search_agent_model;
         $job_search_agents  = $this->getAllJobSearchAgents($this->Auth->user('id'));
        // debug($job_search_agents); die();
         $this->set('job_search_agents', $job_search_agents);
     }
}


public function tutor_review_of_daraji() {
            $this->layout='tutor';
   }

public function account_confirm() {
         $this->layout='default';
   }


public function change_password() {
   $this->layout = 'tutor';
   $this->changePassword();

}

public function change_email() {
   $this->layout = 'tutor';
   $this->changeEmail();

}
   public function missingView() {
       $this->layout = 'tutor';
       //$this->render('missing_action');
   }


public function afterDeleteRename($id) {
  if(  $deletedRow = ($this->{$this->modelClass}->TutorImage->find(
                    array('TutorImage.data_id' => $id,
                          'TutorImage.tutor_id' => $this->Auth->user('id')
                    )))
    ) {

    $filename = $deletedRow['TutorImage']['thumb_image'];
    //debug($filename); die();
    $filepath = $filename;
    //$fiepath = WWW_ROOT. 'img/files/uploads'.$filename;

    $file = new File($deletedRow['TutorImage'][$filepath]);
    $file->delete();
  }
}

/*
	* @Method      :createSearchAgent
	* @Description :for create search agent
	* @access      :registered User Group
	* @param       :
	* @return      :null
	*/
function job_agent($id=null, $agent_id=null){

        //("test"); die();
		$this->layout='tutor';
        $subject ="";
        $subject_id="";
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


if(!$this->request->is('ajax') && $this->request->is('post')) {
     $id = null;
	 if(!empty($this->request->data))
	 {
	    //debug($this->request->data); die();
        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['JobSearchAgent']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

        if(!empty($this->request->data['JobSearchAgent']['id'])) {
        // debug("here a"); die();
                  $id = $this->request->data['JobSearchAgent']['id'];     //the Pk of Associated model (JobSearchAgent)
        }
        //The record should NOT exist.. This is a Totally new Record
        //If it does, this MUST have been injected/hacked

		/**
         if (empty($id) && ($data = $this->{$this->modelClass}->JobSearchAgent->find(
                            'first', array(
                            'conditions' => array(
                                'JobSearchAgent.student_id' => $this->Auth->user('id'),
                                'JobSearchAgent.id'  => $id)))))
                     {


                            throw new NotFoundException(__('Invalid Record.'));

                     }
             **/

             /**
             if(empty($id)) { //if true, Must be a new record
                    $r = $this->{$this->modelClass}->JobSearchAgent->find('all', array(
                              array('field' => 'MAX(JobSearchAgent.agent_id) as agent_id',
			   		     	        'value' => $this->Auth->user('id')
                              ),
                              'order' => 'agent_id DESC'
                     ));
                if(!empty($r)) {
                        //order by DESC should gives us the biggest Id first
                        $this->request->data['JobSearchAgent']['agent_id'] = $r[0]['JobSearchAgent']['agent_id'] + 1;
                         $agent_id = $this->request->data['JobSearchAgent']['agent_id'];
                     } else {
                      $this->request->data['JobSearchAgent']['agent_id']  = 0;
                      $agent_id = 0;
                      //debug("test");
                     }
                } else if (!empty($this->request->data['JobSearchAgent']['agent_id'])){
                    //User must be trying to edit the Agent.
                    //So an agent_id should already be present in request
                    $agent_id = $this->request->data['JobSearchAgent']['agent_id'];
                }
           **/

          // debug($this->request->data); die();
           // debug($r); die();
           $session_search_criteria = $this->Session->read('session_search_criteria');
           debug($session_search_criteria); die();
           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
                if(!empty($session_search_criteria['kwd'])) {
                    $kwd = $session_search_criteria['kwd'];
                } else {
                    $kwd = "";
                }
            $conditions = array(
								'tutor_id' => $this->Auth->user('id'),
								'agent_name' => $this->request->data['JobSearchAgent']['agent_name'],
						 );

            if ($this->{$this->modelClass}->JobSearchAgent->hasAny($conditions)){ //Cannot have a Duplicate Agent Name

                 $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'It appears that an Agent with same Name already exists.<br />Please save your Agent under a different name!!')),
                                               'default',
 												array('class' => 'alert alert-warning')

                                        );

                return; // $this->redirect()

             } else  { //Create the record

			    $r = $this->getAllJobSearchAgents($this->Auth->user('id'));
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
				  return $this->redirect(array('action' => 'job_search_results_auth'));
                }

			   for($i=1; $i<6; $i++) { //this is so that the agent_id is always between 1 and 5

						$data = $this->{$this->modelClass}->JobSearchAgent->find(
										'first', array(
										//'order' => array('JobSearchAgent.agent_id' => 'DESC'),
										'conditions' => array(
											'JobSearchAgent.tutor_id' => $this->Auth->user('id'),
											'JobSearchAgent.agent_id'  => $i)));
						if (!$data) {
							$this->request->data['JobSearchAgent']['agent_id'] = $i; //$data['JobSearchAgent']['agent_id'] + 1;

							break;
						} else {
						   $this->request->data['JobSearchAgent']['agent_id'] = $data['JobSearchAgent']['agent_id']; //1;
						}

               }
             	$this->{$this->modelClass}->JobSearchAgent->set($conditions);
                $this->{$this->modelClass}->JobSearchAgent->create();
               // $this->request->data['JobSearchAgent']['created'] = date('Y-m-d H:i:s');

                //$session_search_criteria = $this->Session->read('session_search_criteria');
				 //debug($session_search_criteria); die();
			//	if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
			//	{
					if(!empty($session_search_criteria['subject']))
					{
					   $this->request->data['JobSearchAgent']['subject'] = $session_search_criteria['subject'];
                       $subject = $this->request->data['JobSearchAgent']['subject'];
                    } else{
					   $this->request->data['JobSearchAgent']['subject'] = "";
                       $subject = $this->request->data['JobSearchAgent']['subject'];
                    }

                   	if(!empty($session_search_criteria['subject_id']))
					{
					   $this->request->data['JobSearchAgent']['subject_id'] = $session_search_criteria['subject_id'];
                       $subject_id = $this->request->data['JobSearchAgent']['subject_id'];

                    } else{
					   $this->request->data['JobSearchAgent']['subject_id'] = "";
                       $subject_id = $this->request->data['JobSearchAgent']['subject_id'];
                    }

					if(!empty($session_search_criteria['zip_code']))
					{
					   $this->request->data['JobSearchAgent']['zip_code'] = $session_search_criteria['zip_code'];
                       $zip_code = $this->request->data['JobSearchAgent']['zip_code'];
                    } else {

					   $this->request->data['JobSearchAgent']['zip_code'] = $this->Session->read('cur_zip_code');
                       $zip_code = $this->request->data['JobSearchAgent']['zip_code'];
                     }

					if(!empty($session_search_criteria['distance']) && $session_search_criteria['distance']>=20)
					 {
					   $this->request->data['JobSearchAgent']['distance'] = $session_search_criteria['distance'];
                       $distance = $this->request->data['JobSearchAgent']['distance'];
                     }
					else
					{
					   $this->request->data['JobSearchAgent']['distance'] = 20;
                       $distance = $this->request->data['JobSearchAgent']['distance'];

                    }


					if(!empty($session_search_criteria['kwd']))
					{
					   $this->request->data['JobSearchAgent']['kwd'] = $session_search_criteria['kwd'];
					   $kwd = $this->request->data['JobSearchAgent']['kwd'];
                    }
                    else
					{
					   $this->request->data['JobSearchAgent']['kwd'] = "";
                       $kwd = $this->request->data['JobSearchAgent']['kwd'];
                     }


                    if(!empty($session_search_criteria['cur_page']))
					{
					   $this->request->data['JobSearchAgent']['cur_page'] = $session_search_criteria['cur_page'];
                       $cur_page = $this->request->data['JobSearchAgent']['cur_page'];
                    }else
					{
					   $this->request->data['JobSearchAgent']['cur_page'] = 1;
                       $cur_page = $this->request->data['JobSearchAgent']['cur_page'];
                    }

				      //debug($this->request->data); die();
                       $search_query =  '?job_subject='.$subject.'&job_subject_id='.$subject_id.'&zip_code='.$zip_code.'&cur_page='.$cur_page.'&distance='.$distance.'&kwd='.$kwd;
                       $this->request->data['JobSearchAgent']['search_query']  = $search_query;

                       // debug($search_query); die();
						 if($this->{$this->modelClass}->JobSearchAgent->saveSearchAgent($id, $this->request->data))
						  //if($this->{$this->modelClass}->JobSearchAgent->saveAll($this->request->data))

						 {
										$this->Session->setFlash
												(
															sprintf(__d('users', 'Your Search has been successfully saved.')),
														   'default',
															array('class' => 'alert alert-success')
												 );
												 $r = $this->getAllJobSearchAgents($this->Auth->user('id'));
                                                 $this->Session->write('agent_count', count($r));
												 $this->Session->delete('session_search_criteria');

                                                 //$this->set('subject', $subject);
                                                 //$this->set('subject_id', $subject_id);
                                                  $this->Session->write('sess_subject', $subject);

												 return $this->redirect(array('action' => 'job_search_results_auth?job_subject='.$subject.'&job_subject_id='.$subject_id));

						 }
               }

			} else {
			    	$this->Session->setFlash
       									(
       												sprintf(__d('users', 'Your have no new Search Criteria.Please Search first and build your Agent')),
       											   'default',
       												array('class' => 'alert alert-warning')
       									 );
			}                  return $this->redirect(array('action' => 'job_search_results_auth'));
		}//request->data

     } //is->post())

     else if ($this->request->is('ajax') ) {
            $this->render('edit', 'ajax');
            //debug($id); die();
            //debug($this->params->query['id']); die();
            //$this->params->query['id']
            //$id=$this->request->data['id'];
            //debug($id); die();
            //$id=$this->params->query['id'];
            //$this->Session->delete('ssa');
            $this->{$this->modelClass}->JobSearchAgent->id = $id;
            $ssa = $this->{$this->modelClass}->JobSearchAgent->findById($id);
            //$this->Session->write('ssa', $ssa);
            //$this->set('ssa', $ssa);

            //debug($ssa); die();
            if(!empty($ssa)) {
                // debug($ssa); die();
                //$this->set('ssa', $ssa);
                //$this->set(compact('ssa')); // Pass $data to the view
               // $this->set('_serialize', 'ssa'); // Let the JsonView class know what variable to use
               // $this->Session->write('ssa', $data);
               // debug($ssa['JobSearchAgent']['subject']); die();

            }

            //return $this->redirect(array('action'=>'tutor_search_results'));
        }
}

function edit_search_agent($id=null, $agent_id=null, $agent_name=null){

        //("test"); die();
		$this->layout='tutor';
        $subject ="";
        $subject_id="";
        $zip_code = $this->Session->read('cur_zip_code');
        $distance = 20;
        $agent_id = 0;

 if($this->request->is('post')) {
     //$id = null;
	 if(!empty($this->request->data))
	 {
	   // debug($this->request->data); die();
        $session_search_criteria = $this->Session->read('session_search_criteria');
       debug($session_search_criteria); //die();
       // die();
        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['JobSearchAgent']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

        if(!empty($this->request->data['JobSearchAgent']['id'])) {
        // debug("here a"); die();
                  $id = $this->request->data['JobSearchAgent']['id'];     //the Pk of Associated model (JobSearchAgent)
        }

        //debug($id); die();
        if (!empty($id) && (!$data = $this->{$this->modelClass}->JobSearchAgent->find(
                            'first', array(
                            'conditions' => array(
                                //'JobSearchAgent.student_id' => $this->Auth->user('id'),
                                'JobSearchAgent.id'  => $id)))))
                     {


                            throw new NotFoundException(__('Invalid Record.'));

                     }
          // $session_search_criteria = $this->Session->read('session_search_criteria');
           //debug($session_search_criteria); die();
            $session_search_criteria = $this->Session->read('session_search_criteria');
         // debug($session_search_criteria); die();
           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
              $this->{$this->modelClass}->JobSearchAgent->set(array(
                                   'agent_name' => $this->request->data['JobSearchAgent']['agent_name'],
                                   'agent_id' => $this->request->data['JobSearchAgent']['agent_id'],
                                   'id' => $this->request->data['JobSearchAgent']['id'],
                                   'subject' => $session_search_criteria['subject'],
                                   'subject_id' => $session_search_criteria['subject_id'],
                                   'zip_code' => $session_search_criteria['zip_code'],
                                   'distance' => $session_search_criteria['distance'],
                                   //'cur_page' => $session_search_criteria['cur_page'],
                                   //'kwd' => $session_search_criteria['kwd']
                         ));
         }

       $postData = array();

      if( $this->{$this->modelClass}->JobSearchAgent->validates(
                  array('fieldList' => array('agent_name'))))
         {
             // debug($this->request->data); //die();
           $session_search_criteria = $this->Session->read('session_search_criteria');
         // debug($session_search_criteria); die();
           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
             $postData['JobSearchAgent']['agent_name'] = $agent_name = $this->request->data['JobSearchAgent']['agent_name'];
             $postData['JobSearchAgent']['agent_id'] = $agent_id = $this->request->data['JobSearchAgent']['agent_id'];
			 //$postData['JobSearchAgent']['id'] = $this->request->data['JobSearchAgent']['id'];
			 $id = $this->request->data['JobSearchAgent']['id'];
			 $postData['JobSearchAgent']['subject'] = $subject = $session_search_criteria['subject'];
             $postData['JobSearchAgent']['subject_id'] =$subject_id = $session_search_criteria['subject_id'];

             $postData['JobSearchAgent']['zip_code'] = $zip_code = $session_search_criteria['zip_code'];
             $postData['JobSearchAgent']['distance'] = $distance = $session_search_criteria['distance'];

             //$postData['JobSearchAgent']['kwd'] = $kwd = $session_search_criteria['kwd'];
			$search_query =  '?job_subject='.$subject.'&job_subject_id='.$subject_id.'&zip_code='.$zip_code.'&distance='.$distance;
            $postData['JobSearchAgent']['search_query']  = $search_query;

             debug($search_query); //die();
			 if($this->{$this->modelClass}->JobSearchAgent->saveSearchAgent($id, $postData))

                 {
       							$this->Session->setFlash
       									(
       												sprintf(__d('users', 'Your Search Agent has been successfully updated.')),
       											   'default',
       												array('class' => 'alert alert-success')
       									 );


                                          $this->Session->delete('session_search_criteria');
										  $this->Session->delete('agent_name');
										  $this->Session->delete('agent_id');
                                          $this->Session->delete('id');

                                          //$this->set('subject', $subject);
                                          //$this->set('subject_id', $subject_id);
                                           $this->Session->write('subject', $subject);

										 // return $this->redirect(array('action' => 'job_search_results_auth'));
                                           return $this->redirect(array('action' => 'job_search_results_auth?job_subject='.$subject.'&job_subject_id='.$subject_id));

    		     } else {

				               $this->Session->setFlash
       									(
       												sprintf(__d('users', 'Save failed.')),
       											   'default',
       												array('class' => 'alert alert-warning')
       									 );


                                          $this->Session->delete('session_search_criteria');
										  $this->Session->delete('agent_name');
										  $this->Session->delete('agent_id');
                                          $this->Session->delete('id');

										  return $this->redirect(array('action' => 'job_search_results_auth'));
				  }

             }

         }
      }
   } else if($this->request->is('get')) {

    //debug($this->params->query); die();
    if (!empty($this->params->query)) { // if submit
      $agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
      $agent_name = !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
      $id = !empty($this->params->query['id']) ? $this->params->query['id'] : 0;
      $update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 1;

     //debug($agent_name);
     //debug($agent_id);
    // debug($id); die();
     $this->set('agent_name', $agent_name);
     $this->set('agent_id', $agent_id);
     $this->set('id', $id);
     $this->set('update_agent', '1');

     $this->Session->write('agent_name', $agent_name);
     $this->Session->write('agent_id', $agent_id);
     $this->Session->write('id', $id);
     $this->Session->write('update_agent', $update_agent);

    }

      return $this->redirect(array('action' => 'save_job_results'));

    }
}

public function delete_agent($id=null) {
     $this->layout='tutor';

    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    }
   // debug("hey"); die();
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }

     if ( empty($id) || !($data = $this->{$this->modelClass}->JobSearchAgent->find(
                            'first', array('conditions' => array('JobSearchAgent.id' => $id)))))
    {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'job_search_results_auth'));
     }

     if ($data['JobSearchAgent']['id'] != $id) {
           //Blackhole Request
            throw new BadRequestException();
     }
    if($this->{$this->modelClass}->JobSearchAgent->delete($id)) {

        //$r=$this->agentCount();
        $r = $this->getAllJobSearchAgents($this->Auth->user('id'));
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

        return $this->redirect(array('action' => 'job_search_results_auth'));

     } else {

         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'deleted failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
     }

}

 protected function getAllJobSearchAgents($id) {

     $job_search_agents = null; //$search_agent_model;
     $job_search_agents  = $this->{$this->modelClass}->findJobSearchAgents($id);

      return $job_search_agents;

  }

  protected function my_job_search_agents() {

    $this->layout='tutor';
    if($this->request->is('get')) {

         $job_search_agents = null; //$search_agent_model;
         $job_search_agents  = $this->getAllJobSearchAgents($this->Auth->user('id'));
         debug($job_search_agents); die();
         $this->set('job_search_agents', $job_search_agents);
     }

  }


function ajax_subjects() {

   if (!$this->request->is('ajax')) {
        //debug('Donald'); //die();
        throw new MethodNotAllowedException();
    }
    $tutor_subject_model = new TutorSubject();
    $this->layout = 'ajax';
    $this->autoRender = false;

    $tutors_model = new Tutor();
    $id = $this->request->data['value'];
    if($id == "MySubjectsGroup") {
        //we re goig to get all the subjects for wich this user was
       // $data = json_encode($tutor_subject_model->get_all_subjects_for_tutor($this->Auth->user('id')));
        $data = json_encode($tutors_model->get_all_subjects_for_tutor($this->Auth->user('id')));
        //debug($data); die();
    } else if($id === "AllSubjects" || $id === "RelatedSubjectsGroup") {
         //we re goig to get all subjects offered by Daraji
         $data = json_encode($tutors_model->get_all_subjects());

    } else if($id === "AllCategories"){//This may be the case where user is not logged in
        $data = json_encode($tutors_model->get_all_subjects());
    } else {//get subjects for category
        $data = json_encode($tutors_model->get_all_subjects($id));
    }
    //$data = json_encode($tutors_model->get_all_subjects($id));
   // $this->set('data',$tutors_model->get_all_subjects($id));
   // return sort($data);
    return $data;
    //$this->set('data',$tutors_model->get_all_subjects($id));
    //$this->set('data', $data);
    //$this->render('/students/tutor_search_resuts_auth');

    //$this->render('/General/SerializeJson/');
    //return $data;
    //$this->render('/elements/ajax_dropdown');
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

    public function isAuthorized($user=null) {

	   if($this->params['controller']=='students') {
	   $this->Session->setFlash
	      				(
	      				  sprintf(__d('users', 'You are one nosy student.')),
	      				   'default',
	      					array('class' => 'alert alert-warning')
	   					);
	    }
	    return false;

}


 }

 ?>