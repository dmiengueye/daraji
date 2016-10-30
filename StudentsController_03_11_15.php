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


class StudentsController extends UsersController {
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
     //debug($this->Student->getDataSource());
}



public function tutor_search_results() {
     
     if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'tutor_search_results_auth'));
       }

       $this->set('title_for_layout', 'Daraji-Tutor Search Results');
       $this->layout='default';
       
      // debug($this->Session->read('results')); die();

    $radiusSearch = new ZipSearch();
    $tutor = new Tutor();
    $conditions = array();
    $tutors_model = new Tutor();
     //debug("test")
     
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

    /**
       * set current zip code
       */
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
       // ovewrites the computed zip code in the session if user entered a zip code manually
      
       $this->set('city', $this->_set_city_for_zip($cur_zip_code));
       $this->Session->write('cur_zip_code', $cur_zip_code);
       $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
      
     if (!empty($this->params->query) && !empty($this->params->query['distance'])) { // if submit
       // if($this->params->query['is_advanced']) {
         //debug($this->params->query['kwd']); die();
      // init default parameters
            $this->params->query['subject'] = !empty($this->params->query['subject']) ? $this->params->query['subject'] : "";
            $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $cur_zip_code;
            $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
            $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;
            
            $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
            
            //$this->params->query['subject_2'] = !empty($this->params->query['subject_2']) ? $this->params->query['subject_2'] : "";
            //$this->params->query['subject_3'] = !empty($this->params->query['subject_3']) ? $this->params->query['subject_3'] : "";
            $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate']) ? $this->params->query['amount_min_rate'] : 10;
            $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate']) ? $this->params->query['amount_max_rate'] : 250;
            $this->params->query['min_age'] = !empty($this->params->query['min_age']) ? $this->params->query['min_age'] : 18;
            $this->params->query['max_age'] = !empty($this->params->query['max_age']) ? $this->params->query['max_age'] : 100;
            $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
            
           // $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
            
    // end init default parameters

       if(!preg_match('/^[0-9]{1,3}$/', $this->params->query['distance'])) {
        	$this->Session->setFlash
								(
											sprintf(__d('users', 'You did not enter a properly formatted distance.')),
										    'default',
											 array('class' => 'alert alert-warning')
								 );
      }

     $zip_search_distance = $this->params->query['distance'];
     $orderby = array('Tutor.zip_code' => 'asc' );
     $model = 'Tutor' ;

     try{

        /**
         * make condition array
         */
        $conditions_for_search = array();

        $conditions_for_search['subject'] = !empty($this->params->query['subject']) ? $this->params->query['subject'] : "";

       // $conditions_for_search['subject_2'] = "";
        //$conditions_for_search['subject_3'] = "";
        $conditions_for_search['hourly_rate'] = "";
        $conditions_for_search['age'] = "";
        $conditions_for_search['gender'] = "";
        $conditions_for_search['bg_checked'] = "";
        $conditions_for_search['is_advanced'] = false;

        // advanced search
       if($this->params->query['is_advanced'] == 1){
          $conditions_for_search['is_advanced'] = true;
         // $conditions_for_search['subject_2'] = !empty($this->params->query['subject_2']) ? $this->params->query['subject_2'] : "";
          //$conditions_for_search['subject_3'] = !empty($this->params->query['subject_3']) ? $this->params->query['subject_3'] : "";

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
        // $this->Session->read('cur_zip_code'); die();
          //debug($conditions_for_search['bg_checked']); die();
         // debug( $this->params->query['zip_code']); die();
        //$result = $tutors_model->find_by_params($conditions_for_search['subject'],  $this->params->query['zip_code'], $conditions_for_search['subject_2'], $conditions_for_search['subject_3'], $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        
        //debug($result); die();
        $subjects_and_categories = $tutors_model->get_all_subjects_and_categories();
        // if(!empty($subjects_and_categories)){
        //   asort($subjects_and_categories);
        // }
        //debug($subjects_and_categories); die();
/**
      if($kwd === "Distance") {
        App::import('ZipCodeHelper', 'View/Helper');
        $this->ZipDis = new ZipCodeHelper();
            $i=0;
            foreach($result as $key => $value) {
                //$d= $i;
                $d = $this->ZipDis->get_distance_between_zipcodes($this->params->query['zip_code'], 
                                                          $result[$i]['Tutor']['zip_code'], 
                                                          $this->params->query['distance'] );
                $inserted = array('dis' => $d);
                array_splice( $result[$i]['Tutor'], 16, 0, $inserted);
                $i++; 
          }
         usort($result, array($this, "sortByOrder"));
         debug($result); die();
      }
 **/  
        $return_array = $this->_get_nav_data($result, $cur_page);
        
        $this->set('zip', $this->Session->read('cur_zip_code'));
        $this->set('tutors', $return_array);
        $this->set('distance', $this->params->query['distance']);

        $this->set('subjects_and_categories', $subjects_and_categories);
        
        if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
        } else {
            $this->set('sortBy', "Best Match");
        }
        

          // $this->set('tutors', $result);
       } catch (NotException $e) {
         $this->redirect(array('action' => 'tutor_search_results_auth'));
       }
       
      //}
    }
    else{ // if not submit
      //debug("test"); die();
      // $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
        $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');

       // ovewrites the computed zip code in the session if user entered a zip code manually
      $this->Session->write('cur_zip_code', $cur_zip_code);
      //$result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $this->params->query['distance']);
      $result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $distance, $kwd);

      $return_array = $this->_get_nav_data($result, $cur_page);

      $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('tutors', $return_array);
      $this->set('distance', $distance);
      
      if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
      } else {
            $this->set('sortBy', "Best Match");
      }
        
      
      
    }
  }
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
         $this->layout='default';
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
    $search_agent_model = new StudentSearchAgent();
    $watchListModel = new StudentWatchList();
     //debug("test")
     
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

    /**
       * set current zip code
       */
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
       // ovewrites the computed zip code in the session if user entered a zip code manually
      
       $this->set('city', $this->_set_city_for_zip($cur_zip_code));
       $this->Session->write('cur_zip_code', $cur_zip_code);
       $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
       
     if (!empty($this->params->query) && !empty($this->params->query['distance'])) { // if submit
         
         //we need to have access to the search criteria for 
         //when the user decides to save the Search resuts as a Search Agent
         //So we put them in the Session
         //$this->Session->write('$session_search_criteria', $this->params->query);
         //debug("test"); die();
      // init default parameters
            $this->params->query['subject'] = !empty($this->params->query['subject']) ? $this->params->query['subject'] : "";
            $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $cur_zip_code;
            $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
            $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;
            
            $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
            
            //$this->params->query['subject_2'] = !empty($this->params->query['subject_2']) ? $this->params->query['subject_2'] : "";
            //$this->params->query['subject_3'] = !empty($this->params->query['subject_3']) ? $this->params->query['subject_3'] : "";
            $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate']) ? $this->params->query['amount_min_rate'] : 10;
            $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate']) ? $this->params->query['amount_max_rate'] : 250;
            $this->params->query['min_age'] = !empty($this->params->query['min_age']) ? $this->params->query['min_age'] : 18;
            $this->params->query['max_age'] = !empty($this->params->query['max_age']) ? $this->params->query['max_age'] : 100;
            $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
			$this->params->query['bg_checked'] = !empty($this->params->query['bg_checked']) ? $this->params->query['bg_checked'] : 0;
			$this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
			
			$update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 0;
			$this->set('update_agent', $update_agent);
			
			$agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
	        $this->set('agent_id', $agent_id);
            
            $id = !empty($this->params->query['id']) ? $this->params->query['id'] : 0;
	        $this->set('id', $id);
             
          // $this->Session->delete('agent_name');
           $agent_name= !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
          //if(!empty($agent_name)) {
           $this->set('agent_name', $agent_name);
            // $this->Session->write('agent_name', $agent_name);
         // }
           
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

     $zip_search_distance = $this->params->query['distance'];
     $orderby = array('Tutor.zip_code' => 'asc' );
     $model = 'Tutor' ;

     try{

        /**
         * make condition array
         */
        $conditions_for_search = array();

        $conditions_for_search['subject'] = !empty($this->params->query['subject']) ? $this->params->query['subject'] : "";

       // $conditions_for_search['subject_2'] = "";
        //$conditions_for_search['subject_3'] = "";
        $conditions_for_search['hourly_rate'] = "";
        $conditions_for_search['age'] = "";
        $conditions_for_search['gender'] = "";
        $conditions_for_search['bg_checked'] = "";
        $conditions_for_search['is_advanced'] = false;

        // advanced search
       if($this->params->query['is_advanced'] == 1){
          $conditions_for_search['is_advanced'] = true;
         // $conditions_for_search['subject_2'] = !empty($this->params->query['subject_2']) ? $this->params->query['subject_2'] : "";
          //$conditions_for_search['subject_3'] = !empty($this->params->query['subject_3']) ? $this->params->query['subject_3'] : "";

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
    $this->Session->write('session_search_criteria', $this->params->query);
    
   
   //debug( $this->Session->read('session_search_criteria')); die();
     $this->Session->delete('agent_name');
     $this->Session->delete('agent_id');
     $this->Session->delete('id');
        // end advanced search
        /**
         * end make condition array
         */
        // $this->Session->read('cur_zip_code'); die();
          //debug($conditions_for_search['bg_checked']); die();
         // debug( $this->params->query['zip_code']); die();
        //$result = $tutors_model->find_by_params($conditions_for_search['subject'],  $this->params->query['zip_code'], $conditions_for_search['subject_2'], $conditions_for_search['subject_3'], $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        
        /**
        $watch_list = $tutors_model->retreiveWatchList($this->Auth->user('id'));
        $i=0;
        foreach ($result as $key => $value) {
            foreach ($watch_list as $keyw => $valuew) {
				if(!empty($value['Tutor']['tutor_id']) && !empty($valuew['StudentWatchList']['tutor_id'])){
					if($value['Tutor']['tutor_id'] === $valuew['StudentWatchList']['tutor_id']) {
					    $inserted = array('on_watch_list' => $valuew['StudentWatchList']['on_watch_list']);
                        array_splice( $result[$i]['Tutor'], 16, 0, $inserted);
                        $i++;
					}
				}
              }
			}
        **/
       // debug($result); die();
        $subjects_and_categories = $tutors_model->get_all_subjects_and_categories();
        // if(!empty($subjects_and_categories)){
        //   asort($subjects_and_categories);
        // }
       // debug($result); die();
       //$search_agents = $this->getAllSearchAgents($this->Auth->user('id'));
       //$this->set('search_agents', $search_agents);
       
/**
      if($kwd === "Distance") {
        App::import('ZipCodeHelper', 'View/Helper');
        $this->ZipDis = new ZipCodeHelper();
            $i=0;
            foreach($result as $key => $value) {
                //$d= $i;
                $d = $this->ZipDis->get_distance_between_zipcodes($this->params->query['zip_code'], 
                                                          $result[$i]['Tutor']['zip_code'], 
                                                          $this->params->query['distance'] );
                $inserted = array('dis' => $d);
                array_splice( $result[$i]['Tutor'], 16, 0, $inserted);
                $i++; 
          }
         usort($result, array($this, "sortByOrder"));
         debug($result); die();
      }
 **/  
       // $return_array = $this->return_tutor_array($result);
        $return_array = $this->_get_nav_data($result, $cur_page);
        $return_array = $this->return_tutor_array($return_array);
        //debug($return_array); die();
       
         
        //die();
        //debug($return_array); die();
        
        $this->set('zip', $this->Session->read('cur_zip_code'));
        $this->set('tutors', $return_array);
        $this->set('distance', $this->params->query['distance']);
        $this->set('radius_distance', $this->params->query['distance']);

        $this->set('subjects_and_categories', $subjects_and_categories);
        
        if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
        } else {
            $this->set('sortBy', h("Best Match"));
        }
        
       $search_agents = $this->getAllSearchAgents($this->Auth->user('id'));
       $agent_count =   count($search_agents) ; 
       $this->Session->write('agent_count', $agent_count);
       
      // debug($search_agents); die();
       //$ssa = $this->{$this->modelClass}->StudentSearchAgent->findById($id);
       //$this->Session->write('ssa', $search_agents);
       $this->set('search_agents', $search_agents);  
       $watch_list_1 = $this->retreiveWatchList($this->Auth->user('id'));
      // debug($watch_list_1); die();
       $watch_list = $tutors_model->retreiveWatchList($this->Auth->user('id'));
      // debug($watch_list); die();
       $this->set('watch_lists', $watch_list);
       $this->set('watch_list_1', $watch_list_1);
       //$i=0;
       //foreach($search_agents as $search_agent) {
        //debug($search_agent); 
        // $agent_id = $search_agent['StudentSearchAgent']['agent_id'];
        // $this->Session->write($agent_id, $search_agent);
       //} //die();

       // $this->set('tutors', $result);
       } catch (NotException $e) {
         $this->redirect(array('action' => 'tutor_search_results_auth'));
       }
       
      //}
    }
    else{ // if not submit
      //debug("test"); die();
      // $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
        $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
	   
	  $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
	 
      $session_search_criteria = array();
      $session_search_criteria['zip_code'] = $cur_zip_code;
      $session_search_criteria['distance'] = $distance;
	  $session_search_criteria['kwd'] = $kwd;
	  
       // ovewrites the computed zip code in the session if user entered a zip code manually
      $this->Session->write('cur_zip_code', $cur_zip_code);
      //debug($session_search_criteria); die();
      $this->Session->write('session_search_criteria', $session_search_criteria);
      //$result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $this->params->query['distance']);
      $result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $distance, $kwd);

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
         

      $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('tutors', $return_array);
      $this->set('distance', $distance);
	  
	  $update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 0;
	  $this->set('update_agent', $update_agent);
	  
	  $agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
	  $this->set('agent_id', $agent_id);

         $agent_name= !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
	  $this->set('agent_name', $agent_name);
      
      if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
      } else {
            $this->set('sortBy', "Best Match");
      }
        //$student_model = new Student();
       $search_agents = $this->getAllSearchAgents($this->Auth->user('id'));
       $agent_count =   count($search_agents) ; 
       $this->Session->write('agent_count', $agent_count);
       //$search_agents = $this->{$this->modelClass}->findSearchAgents($this->Auth->user('id'));
      // debug($search_agents); die();
       $this->set('search_agents', $search_agents);
       //$this->Session->write('ssa', $search_agents);
       //debug("i am ahere");die();       
      // $watch_list = $this->{$this->modelClass}->retreiveWatchList($this->Auth->user('id'));
       //$watch_list = $this->retreiveWatchList($this->Auth->user('id'));
       $watch_list_1 = $this->retreiveWatchList($this->Auth->user('id'));
      // debug($watch_list_1); die();
       $watch_list = $tutors_model->retreiveWatchList($this->Auth->user('id'));
      // debug($watch_list); die();
       $this->set('watch_lists', $watch_list);
       //have to do this in order to be able to cleanly check the size of the watch List w/o
       //the Tutor array it belongs to
       $this->set('watch_list_1', $watch_list_1);
       
       $this->Session->delete('agent_name');
     $this->Session->delete('agent_id');
     $this->Session->delete('id');
       
    }
  } 
  
 }
 
 public function tutor_search_results_grid_auth() {
    $this->layout = 'student';
    $radiusSearch = new ZipSearch();
    $tutor = new Tutor();
    $conditions = array();
    $tutors_model = new Tutor();
    $search_agent_model = new StudentSearchAgent();
     //debug("test")
     
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

    /**
       * set current zip code
       */
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
       // ovewrites the computed zip code in the session if user entered a zip code manually
      
       $this->set('city', $this->_set_city_for_zip($cur_zip_code));
       $this->Session->write('cur_zip_code', $cur_zip_code);
       $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
       
     if (!empty($this->params->query) && !empty($this->params->query['distance'])) { // if submit
         
         //we need to have access to the search criteria for 
         //when the user decides to save the Search resuts as a Search Agent
         //So we put them in the Session
         //$this->Session->write('$session_search_criteria', $this->params->query);
         
      // init default parameters
            $this->params->query['subject'] = !empty($this->params->query['subject']) ? $this->params->query['subject'] : "";
            $this->params->query['zip_code'] = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $cur_zip_code;
            $this->params->query['is_advanced'] = !empty($this->params->query['is_advanced']) ? $this->params->query['is_advanced'] : 0;
            $this->params->query['cur_page'] = !empty($this->params->query['cur_page']) ? $this->params->query['cur_page'] : 1;
            
            $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
            
            //$this->params->query['subject_2'] = !empty($this->params->query['subject_2']) ? $this->params->query['subject_2'] : "";
            //$this->params->query['subject_3'] = !empty($this->params->query['subject_3']) ? $this->params->query['subject_3'] : "";
            $this->params->query['amount_min_rate'] = !empty($this->params->query['amount_min_rate']) ? $this->params->query['amount_min_rate'] : 10;
            $this->params->query['amount_max_rate'] = !empty($this->params->query['amount_max_rate']) ? $this->params->query['amount_max_rate'] : 250;
            $this->params->query['min_age'] = !empty($this->params->query['min_age']) ? $this->params->query['min_age'] : 18;
            $this->params->query['max_age'] = !empty($this->params->query['max_age']) ? $this->params->query['max_age'] : 100;
            $this->params->query['gender'] = !empty($this->params->query['gender']) ? $this->params->query['gender'] : 0;
			$this->params->query['bg_checked'] = !empty($this->params->query['bg_checked']) ? $this->params->query['bg_checked'] : 0;
			$this->params->query['kwd'] = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
			
			$update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 0;
			$this->set('update_agent', $update_agent);
			
			$agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
	        $this->set('agent_id', $agent_id);
            
            $id = !empty($this->params->query['id']) ? $this->params->query['id'] : 0;
	        $this->set('id', $id);
             
          // $this->Session->delete('agent_name');
           $agent_name= !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
          //if(!empty($agent_name)) {
           $this->set('agent_name', $agent_name);
            // $this->Session->write('agent_name', $agent_name);
         // }
           
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

     $zip_search_distance = $this->params->query['distance'];
     $orderby = array('Tutor.zip_code' => 'asc' );
     $model = 'Tutor' ;

     try{

        /**
         * make condition array
         */
        $conditions_for_search = array();

        $conditions_for_search['subject'] = !empty($this->params->query['subject']) ? $this->params->query['subject'] : "";

       // $conditions_for_search['subject_2'] = "";
        //$conditions_for_search['subject_3'] = "";
        $conditions_for_search['hourly_rate'] = "";
        $conditions_for_search['age'] = "";
        $conditions_for_search['gender'] = "";
        $conditions_for_search['bg_checked'] = "";
        $conditions_for_search['is_advanced'] = false;

        // advanced search
       if($this->params->query['is_advanced'] == 1){
          $conditions_for_search['is_advanced'] = true;
         // $conditions_for_search['subject_2'] = !empty($this->params->query['subject_2']) ? $this->params->query['subject_2'] : "";
          //$conditions_for_search['subject_3'] = !empty($this->params->query['subject_3']) ? $this->params->query['subject_3'] : "";

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
    $this->Session->write('session_search_criteria', $this->params->query);
    
   
   //debug( $this->Session->read('session_search_criteria')); die();
     $this->Session->delete('agent_name');
     $this->Session->delete('agent_id');
     $this->Session->delete('id');
        // end advanced search
        /**
         * end make condition array
         */
        // $this->Session->read('cur_zip_code'); die();
          //debug($conditions_for_search['bg_checked']); die();
         // debug( $this->params->query['zip_code']); die();
        //$result = $tutors_model->find_by_params($conditions_for_search['subject'],  $this->params->query['zip_code'], $conditions_for_search['subject_2'], $conditions_for_search['subject_3'], $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        $result = $tutors_model->find_by_params($kwd, $conditions_for_search['subject'],  $this->params->query['zip_code'], "", "", $conditions_for_search['hourly_rate'], $conditions_for_search['age'], $conditions_for_search['gender'], $conditions_for_search['bg_checked'], $conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);
        
       // debug($result); die();
        $subjects_and_categories = $tutors_model->get_all_subjects_and_categories();
        // if(!empty($subjects_and_categories)){
        //   asort($subjects_and_categories);
        // }
       // debug($result); die();
       //$search_agents = $this->getAllSearchAgents($this->Auth->user('id'));
       //$this->set('search_agents', $search_agents);
       
/**
      if($kwd === "Distance") {
        App::import('ZipCodeHelper', 'View/Helper');
        $this->ZipDis = new ZipCodeHelper();
            $i=0;
            foreach($result as $key => $value) {
                //$d= $i;
                $d = $this->ZipDis->get_distance_between_zipcodes($this->params->query['zip_code'], 
                                                          $result[$i]['Tutor']['zip_code'], 
                                                          $this->params->query['distance'] );
                $inserted = array('dis' => $d);
                array_splice( $result[$i]['Tutor'], 16, 0, $inserted);
                $i++; 
          }
         usort($result, array($this, "sortByOrder"));
         debug($result); die();
      }
 **/  
        $return_array = $this->_get_nav_data($result, $cur_page);
        
        $this->set('zip', $this->Session->read('cur_zip_code'));
        $this->set('tutors', $return_array);
        $this->set('distance', $this->params->query['distance']);

        $this->set('subjects_and_categories', $subjects_and_categories);
        
        if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
        } else {
            $this->set('sortBy', h("Best Match"));
        }
        
       $search_agents = $this->getAllSearchAgents($this->Auth->user('id'));
       $agent_count =   count($search_agents) ; 
       $this->Session->write('agent_count', $agent_count);
       
      // debug($search_agents); die();
       //$ssa = $this->{$this->modelClass}->StudentSearchAgent->findById($id);
       //$this->Session->write('ssa', $search_agents);
       $this->set('search_agents', $search_agents);
       //$i=0;
       //foreach($search_agents as $search_agent) {
        //debug($search_agent); 
        // $agent_id = $search_agent['StudentSearchAgent']['agent_id'];
        // $this->Session->write($agent_id, $search_agent);
       //} //die();

       // $this->set('tutors', $result);
       } catch (NotException $e) {
         $this->redirect(array('action' => 'tutor_search_results_auth'));
       }
       
      //}
    }
    else{ // if not submit
      //debug("test"); die();
      // $this->params->query['distance'] = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
        $distance = !empty($this->params->query['distance']) ? $this->params->query['distance'] : 20;
      //The user entered zip code always takes priority over the computed zip code.
       $cur_zip_code = !empty($this->params->query['zip_code']) ? $this->params->query['zip_code'] : $this->Session->read('cur_zip_code');
	   
	  $kwd = !empty($this->params->query['kwd']) ? $this->params->query['kwd'] : "";
	 
      $session_search_criteria = array();
      $session_search_criteria['zip_code'] = $cur_zip_code;
      $session_search_criteria['distance'] = $distance;
	  $session_search_criteria['kwd'] = $kwd;
	  
       // ovewrites the computed zip code in the session if user entered a zip code manually
      $this->Session->write('cur_zip_code', $cur_zip_code);
      //debug($session_search_criteria); die();
      $this->Session->write('session_search_criteria', $session_search_criteria);
      //$result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $this->params->query['distance']);
      $result = $tutors_model->get_all_tutors($this->Session->read('cur_zip_code'), $distance, $kwd);

      $return_array = $this->_get_nav_data($result, $cur_page);

      $this->set('zip', $this->Session->read('cur_zip_code'));
      $this->set('tutors', $return_array);
      $this->set('distance', $distance);
	  
	  $update_agent = !empty($this->params->query['update_agent']) ? $this->params->query['update_agent'] : 0;
	  $this->set('update_agent', $update_agent);
	  
	  $agent_id = !empty($this->params->query['agent_id']) ? $this->params->query['agent_id'] : 0;
	  $this->set('agent_id', $agent_id);

         $agent_name= !empty($this->params->query['agent_name']) ? $this->params->query['agent_name'] : "";
	  $this->set('agent_name', $agent_name);
      
      if(!empty($this->params->query['kwd'])) {
         $this->set('sortBy', $this->params->query['kwd']);
      } else {
            $this->set('sortBy', "Best Match");
      }
        //$student_model = new Student();
       $search_agents = $this->getAllSearchAgents($this->Auth->user('id'));
       $agent_count =   count($search_agents) ; 
       $this->Session->write('agent_count', $agent_count);
       //$search_agents = $this->{$this->modelClass}->findSearchAgents($this->Auth->user('id'));
      // debug($search_agents); die();
       $this->set('search_agents', $search_agents);
       //$this->Session->write('ssa', $search_agents);
       
       $this->Session->delete('agent_name');
     $this->Session->delete('agent_id');
     $this->Session->delete('id');
       
    }
  } 
  
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


public function tutorsearchresultsauthwithbootstrapmin() {
               $this->layout='student';
   }


   public function safetytips() {
               $this->layout='student';
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
                     $post_date = date('Y-m-d');
                     $exp_date = date('Y-m-d', strtotime($post_date. ' + 30 days'));
                     
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
                          //debug($name); die();
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
                             
                            $this->set('success', true);       
                            $this->Session->setFlash('Your Job Post is Successfully Saved and Pending Review. You will be notified when approved in the next 10 minutes!!', 'custom_msg');
		
                                     
					  }
                  } else {
                    /**
                    $this->Session->setFlash
									(
												sprintf(__d('users', 'Your Job Post failed! <br /> Please corect all Errors below and resubmit!')),
											   'default',
												array('class' => 'alert alert-warning')
									 );
                            **/    
                                $this->set('success', false);     
                                $this->Session->setFlash('Your Job Post failed!! Please corect all Errors below and resubmit!', 'custom_msg');
		      
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
   }

   public function tutor_search_tools() {
                 $this->layout='student';
    }
    
    public function watch_list_center() {
                 $this->layout='student';
    }
    
    public function my_job_posts() {
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
   	    // debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['StudentProfile']['student_id'] = $this->request->data[$this->modelClass]['id'];

   			          //if(!empty($this->request->data['StudentProfile']['id']))
   			                //$id = $this->request->data['StudentProfile']['id'];     //the Pk of Associated model (StudentProfile)
                        //debug($id); die();
                        
                        //The record should NOT exist.. This is a Totally new Record
                        //If it does, this MUST have been injected/hacked
   					  if (!empty($id)  || ($data = $this->{$this->modelClass}->StudentProfile->find(
                            'first', array(
                            'conditions' => array(
                                'StudentProfile.student_id' => $this->Auth->user('id'),
                                'StudentProfile.id'  => $id)))))
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

			          //if(!empty($this->request->data['StudentPreference']['id']))
			              //  $id = $this->request->data['StudentPreference']['id'];     //the Pk of Associated model (StudentPreference)

   					  if (!empty($id) || ($data = $this->{$this->modelClass}->StudentPreference->find(
                            'first', array(
                            'conditions' => array(
                                'StudentPreference.student_id' => $this->Auth->user('id'),
                                'StudentPreference.id'  => $id)))))
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
                               return $this->redirect(array('action' => 'tutor_search_results_auth'));
   					  
                         } else {

                             $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Failed to Save your changes. Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')

                                        );
                                        
                               return $this->redirect(array('action' => 'tutor_search_results_auth'));

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
        
    
if(!$this->request->is('ajax') && $this->request->is('post')) {
     $id = null;
	 if(!empty($this->request->data))
	 {
	    debug($this->request->data); die();
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
		   
          // debug($this->request->data); die();
           // debug($r); die();
           $session_search_criteria = $this->Session->read('session_search_criteria');
           //debug($session_search_criteria); die();
           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
                if(!empty($session_search_criteria['kwd'])) {
                    $kwd = $session_search_criteria['kwd'];
                } else {
                    $kwd = "";
                }
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
			//	{
					if(!empty($session_search_criteria['subject']))
					{
					   $this->request->data['StudentSearchAgent']['subject'] = $session_search_criteria['subject'];
                       $subject = $this->request->data['StudentSearchAgent']['subject'];
                    } else
					{
					   $this->request->data['StudentSearchAgent']['subject'] = "";
                       $subject = $this->request->data['StudentSearchAgent']['subject'];
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
					   $this->request->data['StudentSearchAgent']['distance'] = $session_search_criteria['distance'];
                       $distance = $this->request->data['StudentSearchAgent']['distance'];
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
					
					if(!empty($session_search_criteria['min_rate']) && $session_search_criteria['amount_min_rate']>=10)
					{
					   $this->request->data['StudentSearchAgent']['min_rate'] = $session_search_criteria['amount_min_rate'];
                       $min_rate = $this->request->data['StudentSearchAgent']['min_rate'];
                     }
					else
					{
					   $this->request->data['StudentSearchAgent']['min_rate'] = 10;
                       $min_rate = $this->request->data['StudentSearchAgent']['min_rate'];
                     }
					
					if(!empty($session_search_criteria['max_rate']) && $session_search_criteria['amount_max_rate']<=250)
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
                    
                   	//if(!empty($session_search_criteria['is_advanced']) && in_array($session_search_criteria['is_advanced'], array(0,1)))
					if(!empty($session_search_criteria['is_advanced']) && $session_search_criteria['is_advanced'])
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
                    	
				      //debug($this->request->data); die();
                       $search_query =  '?subject='.$subject.'&zip_code='.$zip_code.'&is_advanced='.$is_advanced.'&cur_page='.$cur_page.'&distance='.$distance.'&amount_min_rate='.$min_rate.'&amount_max_rate='.$max_rate.'&min_age='.$min_age.'&max_age='.'&gender='.$gender.'&bg_checked='.$bg_checked.'&kwd='.$kwd;
                       $this->request->data['StudentSearchAgent']['search_query']  = $search_query;
             
                       // debug($search_query); die();
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
												 $this->Session->delete('session_search_criteria');
												 return $this->redirect(array('action' => 'tutor_search_results_auth'));
												
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
            //debug($id); die();
            //debug($this->params->query['id']); die();
            //$this->params->query['id']
            //$id=$this->request->data['id']; 
            //debug($id); die();
            //$id=$this->params->query['id'];
            //$this->Session->delete('ssa');
            $this->{$this->modelClass}->StudentSearchAgent->id = $id;
            $ssa = $this->{$this->modelClass}->StudentSearchAgent->findById($id);
            //$this->Session->write('ssa', $ssa);
            //$this->set('ssa', $ssa);
                
            //debug($ssa); die();
            if(!empty($ssa)) {
                // debug($ssa); die();
                //$this->set('ssa', $ssa);
                //$this->set(compact('ssa')); // Pass $data to the view
               // $this->set('_serialize', 'ssa'); // Let the JsonView class know what variable to use
               // $this->Session->write('ssa', $data);
               // debug($ssa['StudentSearchAgent']['subject']); die();
        /**
                $this->set('id', $ssa['StudentSearchAgent']['id']);
                $this->Session->write('id', $ssa['StudentSearchAgent']['id']);
                
                $this->set('agent_id', $ssa['StudentSearchAgent']['agent_id']);
                $this->Session->write('agent_id', $ssa['StudentSearchAgent']['agent_id']);
                
                $this->set('agent_name', $ssa['StudentSearchAgent']['agent_name']);
                $this->Session->write('agent_name', $ssa['StudentSearchAgent']['agent_name']);
                
                //$this->set('subject_a', $ssa['StudentSearchAgent']['subject']);
                $this->Session->write('subject_a', $ssa['StudentSearchAgent']['subject']);
                
                $this->set('zip_code_a', $ssa['StudentSearchAgent']['zip_code']);
                $this->Session->write('zip_code_a', $ssa['StudentSearchAgent']['zip_code']);
                
                $this->set('distance_a', $ssa['StudentSearchAgent']['distance']);
                $this->Session->write('distance_a', $ssa['StudentSearchAgent']['distance_a']);
                
                $this->set('min_age_a', $ssa['StudentSearchAgent']['min_age']);
                $this->Session->write('min_age_a', $ssa['StudentSearchAgent']['min_age_a']);
                $this->set('max_age_a', $ssa['StudentSearchAgent']['max_age']);
                $this->Session->write('max_age_a', $ssa['StudentSearchAgent']['max_age_a']);
                
                $this->set('min_rate_a', $ssa['StudentSearchAgent']['min_rate']);
                $this->Session->write('min_rate_a', $ssa['StudentSearchAgent']['min_rate_a']);
                $this->set('max_rate_a', $ssa['StudentSearchAgent']['max_rate']);
                $this->Session->write('max_rate_a', $ssa['StudentSearchAgent']['max_rate_a']);
                
                $this->set('gender_a', $ssa['StudentSearchAgent']['gender']);
                $this->Session->write('gender_a', $ssa['StudentSearchAgent']['gender_a']);
                
                $this->set('bg_checked_a', $ssa['StudentSearchAgent']['bg_checked']);
                $this->Session->write('bg_checked_a', $ssa['StudentSearchAgent']['bg_checked_a']);
                
                $this->set('search_query_a', $ssa['StudentSearchAgent']['search_query']);
                $this->Session->write('search_query_a', $ssa['StudentSearchAgent']['search_query_a']);
                
                $this->set('is_advanced_a', $ssa['StudentSearchAgent']['is_advanced']);
                $this->Session->write('is_advanced_a', $ssa['StudentSearchAgent']['is_advanced_a']);
                
                $this->set('kwd_a', $ssa['StudentSearchAgent']['kwd']);
                $this->Session->write('kwd_a', $ssa['StudentSearchAgent']['kwd_a']);
                **/
            }
            
            //return $this->redirect(array('action'=>'tutor_search_results'));
        } 
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
        
 if($this->request->is('post')) {
     //$id = null;
	 if(!empty($this->request->data))
	 {
	    //debug($this->request->data); 
        //$session_search_criteria = $this->Session->read('session_search_criteria');
       // debug($session_search_criteria); die();
       // die();
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
          // $session_search_criteria = $this->Session->read('session_search_criteria');
           //debug($session_search_criteria); die();
            $session_search_criteria = $this->Session->read('session_search_criteria');
          // debug($session_search_criteria); die();
           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
       $this->{$this->modelClass}->StudentSearchAgent->set(array(
                                  'agent_name' => $this->request->data['StudentSearchAgent']['agent_name'],
                                   'agent_id' => $this->request->data['StudentSearchAgent']['agent_id'],
                                   'id' => $this->request->data['StudentSearchAgent']['id'],
                                   'subject' => $session_search_criteria['subject'],
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
             // debug($this->request->data); //die();
           $session_search_criteria = $this->Session->read('session_search_criteria');
         // debug($session_search_criteria); die();
           if(is_array($session_search_criteria) && !empty($session_search_criteria) &&  count($session_search_criteria)>0)
           {
             $postData['StudentSearchAgent']['agent_name'] = $agent_name = $this->request->data['StudentSearchAgent']['agent_name'];
             $postData['StudentSearchAgent']['agent_id'] = $agent_id = $this->request->data['StudentSearchAgent']['agent_id'];
			 //$postData['StudentSearchAgent']['id'] = $this->request->data['StudentSearchAgent']['id'];
			 $id = $this->request->data['StudentSearchAgent']['id'];
			 $postData['StudentSearchAgent']['subject'] = $subject = $session_search_criteria['subject'];
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
                
			$search_query =  '?subject='.$subject.'&zip_code='.$zip_code.'&is_advanced='.$is_advanced.'&cur_page='.$cur_page.'&distance='.$distance.'&amount_min_rate='.$min_rate.'&amount_max_rate='.$max_rate.'&min_age='.$min_age.'&max_age='.'&gender='.$gender.'&bg_checked='.$bg_checked.'&kwd='.$kwd;
            $postData['StudentSearchAgent']['search_query']  = $search_query;
             
			 if($this->{$this->modelClass}->StudentSearchAgent->saveSearchAgent($id, $postData))
    	         
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
										  
										  return $this->redirect(array('action' => 'tutor_search_results_auth'));
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
										  
										  return $this->redirect(array('action' => 'tutor_search_results_auth'));
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
     
     //debug($agent_name);
     //debug($agent_id);
    // debug($id); die();
     $this->set('agent_name', $agent_name);
     $this->set('agent_id', $agent_id);
     $this->set('id', $id);
     
     $this->Session->write('agent_name', $agent_name);
     $this->Session->write('agent_id', $agent_id);
     $this->Session->write('id', $id);
     
    }    
    
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
            $search_city = $result['city'];
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

public function remove_tutor($id=null) {
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
          $this->redirect(array('action' => 'tutor_search_results_auth'));
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

        return $this->redirect(array('action' => 'tutor_search_results_auth '));
        
     } else {
        
         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Failed to remove tutor. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
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

}