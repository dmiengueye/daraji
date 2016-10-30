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

class StudentsController extends UsersController {
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Students';
	public $uses = array ('Student', 'StudentPreference', 'StudentProfile', 'ZipSearch', 'Tutor');

    public $components = array('Paginator');
    public $helpers = array('CustomPaginator');

/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

		parent::beforeFilter();
        AuthComponent::$sessionKey = 'Auth.Student';

	//	$this->Security->blackHoleCallback = 'blackhole';
        $this->Auth->allow('complete');
		$this->_setupPagination();
		$this->set('model', $this->modelClass);

		$id = $this->Auth->user('id'); //Using the session's user id to find logged in user
        if($this->Session->check(AuthComponent::$sessionKey . 'first_name')) {
                  $this->Session->write('username', $this->Session->read(AuthComponent::$sessionKey . 'first_name'));
        } else {
                $user_data = $this->{$this->modelClass}->findById($id);
		 		if($user_data != null  && !empty($user_data)) {
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
                    $last_name = $user_data[$this->modelClass]['last_name'];
		 		    $last_login = $user_data[$this->modelClass]['last_login'];
		            $this->set('fname', $user_fname);

		            $this->Session->write('username', $user_fname);
                    $this->Session->write('lastname', $last_name);
		            $this->Session->write('last_login', $last_login);

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
public function register() {
         $this->layout = 'default';
         //debug($this->request->data);
         // Call add() function of Parent (Plugin::UsersController)
         Configure::write('Users.role', 'student');
         $this->add();
         //$this->validationErrors;
         //debug($this->Student->validationErrors);
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


/**
public function index() {

     $this->set('title_for_layout', 'Daraji- Student Home');
     $this->layout='student';
     $this->set('students', $this->Paginator->paginate($this->modelClass));
}
**/

    public function home() {
          $this->set('title_for_layout', 'Daraji- Student Home');
        $this->layout='student';
      //  $this->Student->recursive = 0;
      //  $this->set('students', $this->paginate());
      //$this->set('students', $this->Paginator->paginate($this->modelClass));
    }


public function welcome() {

     $this->layout='student';
     //$first_login = true;

    if($this->Session->check('first_login')) {
       $first_login = $this->Session->read('first_login');
       $this->Session->delete('first_login');

    }
    // debug($first_login); die();
    //debug($this->Auth->user('last_login'));
    if(!$first_login) {

           return $this->redirect(array('action' => 'homeroomempty'));
    }
}

public function homeroomempty() {
      $this->layout='student';
     // debug($this->Student->getDataSource());
      //debug($this->Session->id());
       //debug(CakeSession::read('Auth'));
       // debug($this->Auth->user('last_login'));
       // debug($this->Session->read('Auth.Student'));
	  //debug($this->Auth->user('last_login'));
	    //if($this->Auth->user('last_login') == null) {
	           //return $this->redirect(array('action' => 'welcome'));
    //}
}

public function home_room() {

     $this->layout='student';
     debug($this->Student->getDataSource());
}

public function tutor_search_results() {

     if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'tutor_search_results_auth'));
       }

       $this->set('title_for_layout', 'Daraji-Tutor Search Results');
       $this->layout='default';

}

public function studentsearchresults() {
     $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='searchresults';
  }

public function StudentProfiledetail() {
     $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='student';
  }

  public function tutor_details_profile_auth() {
       $this->set('title_for_layout', 'Daraji-Tutor Search Results');
       $this->layout='student';
  }

  public function tutor_details_profile() {
         $this->set('title_for_layout', 'Daraji-Tutor Search Results');
         $this->layout='student';
  }



  public function requestTutor() {
    $this->layout='student';

  }

  public function tellYourFriends() {
    $this->layout='student';

  }

 public function contact() {
        $this->layout='default';
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

   if($this->request->is('get')) {
     $id = null;
     $rs = null;

     // pagination
     $posts_per_page = 2;
     $total_post_count = 0;
     $cur_page = 1;
     $start_page = 1;
     $display_page_navigation = 9;

     if(!empty($_GET['cur_page'])){
      $cur_page = $_GET['cur_page'];
    }
     // end pagination

     if (!empty($this->params->query) && !empty($this->params->query['distance'])) { // if submit
       if(!preg_match('/^[0-9]{1,3}$/', $this->params->query['distance'])) {
        	$this->Session->setFlash
								(
											sprintf(__d('users', 'You did not enter a properly formatted distance.')),
										    'default',
											 array('class' => 'alert alert-warning')
								 );
      }

      $zip_search_distance = $this->params->query['distance'];


      /**
       * set current zip code
       */
      $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
      $this->set('zip', $cur_zip_code);

     $orderby = array('Tutor.zip_code' => 'asc' );
     $model = 'Tutor' ;

     try{
        /**
         * make condition array
         */
        $conditions_for_search = array();

        $conditions_for_search['subject'] = !empty($this->params->query['subject']) ? $this->params->query['subject'] : "";

        $conditions_for_search['subject_2'] = "";
        $conditions_for_search['subject_3'] = "";
        $conditions_for_search['hourly_rate'] = "";
        $conditions_for_search['age'] = "";
        $conditions_for_search['gender'] = "";
        $conditions_for_search['bg_checked'] = "";
        $conditions_for_search['is_advanced'] = false;

        // advanced search
        if($this->params->query['is_advanced'] == 1){
          $conditions_for_search['is_advanced'] = true;
          $conditions_for_search['subject_2'] = !empty($this->params->query['subject_2']) ? $this->params->query['subject_2'] : "";
          $conditions_for_search['subject_3'] = !empty($this->params->query['subject_3']) ? $this->params->query['subject_3'] : "";

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
    }
        // end advanced search
        /**
         * end make condition array
         */
        $result = $tutors_model->find_by_params($conditions_for_search['subject'], $cur_zip_code, $conditions_for_search['subject_2'], $conditions_for_search['subject_3'], $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);

// for ($i=0; $i < 10; $i++) {
//   $result[] = $result[2];
// }
        $return_array = $this->_get_nav_data($result, $cur_page);

        $this->set('tutors', $return_array);



          // $this->set('tutors', $result);
       } catch (NotException $e) {
         $this->redirect(array('action' => 'tutor_search_results_auth'));
       }
    }
    else{ // if not submit
      $conditions = array(); //('Tutor.zip_code' => $zip_array);
      $orderby = array('Tutor.zip_code' => 'asc' );
      $model = 'Tutor' ;

     // $result = $tutors_model->get_all_tutors();
      $result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'));

      // $page_count = sizeof($result) / 2;
      // $page_count = ceil($page_count);

      // $this->setupPagination($page_count, $conditions, $orderby, $model);
      // $resut_pagenation = $this->Paginator->paginate($model);

      $return_array = $this->_get_nav_data($result, $cur_page);

      $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('tutors', $return_array);
    }
  }

 }

  protected function _get_nav_data($result_array, $cur_page){
    $posts_per_page = 2;
    $return_value = array();
    $total_post_count = sizeof($result_array);
    $total_page_count = ceil($total_post_count / $posts_per_page);

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

    for ($i=0; $i < $posts_per_page; $i++) {
      $print_num = $i + ($cur_page - 1) * $posts_per_page;

      if($print_num <= $total_post_count){
        if(!empty($result_array[$print_num])){
          $return_value[] = $result_array[$print_num];
        }
      }
    }

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

    public function post_job() {
                  $this->layout='student';
   }

   public function myaccount() {
              $this->layout='student';
   }

   public function request_tutor() {
                 $this->layout='default';
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


    public function my_tutors() {
                  $this->layout='student';
   }
    public function my_tutor_watch_list() {
                  $this->layout='student';
   }
   public function my_tutor_search_agents() {
                  $this->layout='student';
   }

   public function tutor_search_tools() {
                 $this->layout='student';
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
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['StudentProfile']['student_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['StudentProfile']['id']))
   			                $id = $this->request->data['StudentProfile']['id'];     //the Pk of Associated model (StudentProfile)

   					  if (($data = $this->{$this->modelClass}->StudentProfile->find(
                            'first', array(
                            'conditions' => array(
                                'StudentProfile.student_id' => $this->Auth->user('id'),
                                'StudentProfile.id'  => $id)))) && $data['StudentProfile']['id'] != $id)
                     {

                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Profile'));

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

   					  if (($data = $this->{$this->modelClass}->StudentPreference->find(
                            'first', array(
                            'conditions' => array(
                                'StudentPreference.student_id' => $this->Auth->user('id'),
                                'StudentPreference.id'  => $id)))) && $data['StudentPreference']['id'] != $id)
                     {

                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Preferences'));

                     }

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

   if(!empty($this->request->data['StudentProfile']['id']))
   			                $id = $this->request->data['StudentProfile']['id'];     //the Pk of Associated model (StudentProfile)

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
             'zip' => $this->request->data['zipCode'],

     ));

      if(!empty($this->request->data) )  {

        if( $this->{$this->modelClass}->StudentProfile->validates(
                  array('fieldList' => array('address_1','city', 'state', 'zip_code' ))))
         {

             $this->{$this->modelClass}->StudentProfile->id = $this->request->data['id'];
             $this->{$this->modelClass}->StudentProfile->saveField('address_1', $this->request->data['addr1']);
             $this->{$this->modelClass}->StudentProfile->saveField('address_2', $this->request->data['addr2']);
             $this->{$this->modelClass}->StudentProfile->saveField('city', $this->request->data['city']);
             $this->{$this->modelClass}->StudentProfile->saveField('state', $this->request->data['state']);
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
