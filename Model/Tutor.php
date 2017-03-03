<?php
/**
 * Copyright 2010 - 2013, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2013, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/** Shoud inherit the Plugin Users Model
App::uses('AuthComponent', 'Controller/Component');
**/

App::uses('User', 'Users.Model');
App::uses('Student', 'Model');
App::uses('TutorProfile', 'Model');
App::uses('TutorImage', 'Model');
App::uses('Subject', 'Model');
App::uses('TutorSubject', 'Model');
App::uses('TutorRating', 'Model');
App::uses('ZipSearch', 'Model');
App::uses('Categorie', 'Model');
App::uses('Hash', 'Utility');
App::uses('Validation', 'Utility');
App::uses('StudentWatchList', 'Model');
App::uses('JobSearchAgent', 'Model');
App::uses('TutorJobApplication', 'Model');
App::uses('StudentsTutor', 'Model');
// class Tutor extends User {
class Tutor extends User {

const ONLINE = 'online';
public $name = 'Tutor';

public $recursive = 2;
//public $virtualFields = array('distance' => 10);



 //A Tutor has one Profile (details) and one set of Prefernces
        public $hasOne = array (
	                          'TutorPreference' => array(
							   				'className' => 'TutorPreference',
							   				'foreignKey' => 'tutor_id',
							   			  //'conditions' => array('TutorPreference.checked' => '1'),
							   				'dependent' => true
                                            ),
                              'TutorProfile' => array(
							  	           'className' => 'TutorProfile',
							  	           'foreignKey' => 'tutor_id',
							  	           'conditions' => array(
                                                 //'TutorProfile.profile_status' => 1,
                                                 'TutorProfile.profile_ready' => '1'
                                             ),
							  	           'dependent' => true
	                                       ),
								'TutorAvailability' => array(
											 'className' => 'TutorAvailability',
											 'foreignKey' => 'tutor_id',
											 //'conditions' => array('TutorSubject.searchable_status' => '1'),
											 'dependent' => true
										   ),
                              'TutorRating' => array(
							  	           'className' => 'TutorRating',
							  	           'foreignKey' => 'tutor_id',
							  	         //'conditions' => array('TutorProfile.active' => '1'),
							  	           'dependent' => true
	                                       )
	                       );

          public $hasMany = array (
   	 	           'TutorSubject' => array(
   	 	                 'className' => 'TutorSubject',
   	 	                 'foreignKey' => 'tutor_id',
   	 	                 'conditions' => array('TutorSubject.searchable_status' => '1'),
   	 	                 'dependent' => true
   	 	               ),
   	 	               'TutorCategorie' => array(
					      	 	                 'className' => 'TutorCategorie',
					      	 	                 'foreignKey' => 'tutor_id',
					      	 	              // 'conditions' => array('TutorSubject.active' => '1'),
					      	 	                  'dependent' => true
   	 	               ),

                       'TutorImage' => array(
					      	 	                 'className' => 'TutorImage',
					      	 	                 'foreignKey' => 'tutor_id',
					      	 	                 'conditions' => array(
                                                                 'TutorImage.status' => '1',
                                                                 'TutorImage.featured' => '1'),
 	 	                                          'dependent' => true
   	 	               ),
					   
					   
					'StudentsTutor' => array(
   	 	                 'className' => 'StudentsTutor',
   	 	                 'foreignKey' => 'tutor_id',
   	 	                 //'conditions' => array('TutorLevel.approve' => '1'),
   	 	                 'dependent' => true
   	 	               ),
					   
					    'TutorLevel' => array(
   	 	                 'className' => 'TutorLevel',
   	 	                 'foreignKey' => 'tutor_id',
   	 	                 //'conditions' => array('TutorLevel.approve' => '1'),
   	 	                 'dependent' => true
   	 	               ),
					   
					'TutorSession' => array(
   	 	                 'className' => 'TutorSession',
   	 	                 'foreignKey' => 'tutor_id',
   	 	                 //'conditions' => array('TutorLevel.approve' => '1'),
   	 	                 'dependent' => true
   	 	               ),
                       
                       
                     'TutorLocation' => array(
   	 	                 'className' => 'TutorLocation',
   	 	                 'foreignKey' => 'tutor_id',
   	 	                 //'conditions' => array('TutorSubject.searchable_status' => '1'),
   	 	                 'dependent' => true
   	 	               ),
                       
                       'TutorSchedule' => array(
   	 	                 'className' => 'TutorSchedule',
   	 	                 'foreignKey' => 'tutor_id',
   	 	                 //'conditions' => array('TutorSubject.searchable_status' => '1'),
   	 	                 'dependent' => true
   	 	               ),
					  
                       
                       'TutorLessonSubmittal' => array(
   	 	                 'className' => 'TutorLessonSubmittal',
   	 	                 'foreignKey' => 'tutor_id',
   	 	                 //'conditions' => array('TutorSubject.searchable_status' => '1'),
   	 	                 'dependent' => true
   	 	               ),
                       
                       /**
                        'TutorStudent' => array(
   	 	                   'className' => 'TutorStudent',
   	 	                   'foreignKey' => 'tutor_id',
   	 	                 //'conditions' => array('TutorSubject.searchable_status' => '1'),
   	 	                 'dependent' => true
   	 	               ),
                       **/
                       
                       'StudentWatchList' => array(
							   				  'className' => 'StudentWatchList',
							   				  'foreignKey' => 'tutor_id',
							   				 // 'conditions' => array('JobSearchAgent.agent_name' => '1'),
							   				  'dependent' => true
							                ),

                       'JobSearchAgent' => array(
							   				  'className' => 'JobSearchAgent',
							   				  'foreignKey' => 'tutor_id',
							   				 // 'conditions' => array('JobSearchAgent.agent_name' => '1'),
							   				  'dependent' => true
							                ),

                        'TutorJobApplication' => array(
       	 	                 'className' => 'TutorJobApplication',
       	 	                 'foreignKey' => 'tutor_id',
       	 	                // 'conditions' => array('TutorJobApplication.searchable_status' => '1'),
       	 	                 'dependent' => true
   	 	               ),
   	 	          );
                  
    public $hasAndBelongsToMany = array(
        'Student' =>
            array(
                'className' => 'Student',
                'joinTable' => 'students_tutors',
                'foreignKey' => 'tutor_id',
                'associationForeignKey' => 'student_id',
                'unique' => 'keepExisting' , //,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => ''
                //'with' => 'students_tutors'
            ),
       
       /**     
        'Student' =>
            array(
                'className' => 'Student',
                'joinTable' => 'tutors_connections',
                'foreignKey' => 'tutor_id',
                'associationForeignKey' => 'student_id',
                'unique' => 'keepExisting' , //,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => '',
                'with' => 'tutors_connections'
            )
        **/    
    );
    
     

/**
 * Validation parameters
 *
 * @var array
 */
	public $validate = array(
		'first_name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'required' => true, 'allowEmpty' => false,
				'message' => 'Please enter your first Name.'),
			'alpha' => array(
				'rule' => array('alphaNumeric'),
				'message' => 'The First name must be alphanumeric.'),
			'first_name_min' => array(
				'rule' => array('minLength', '3'),
				'message' => 'The first must have at least 3 characters.')),
		'last_name' => array(
					'required' => array(
						'rule' => array('notEmpty'),
						'required' => true, 'allowEmpty' => false,
						'message' => 'Please enter your last Name.'),
					'alpha' => array(
						'rule' => array('alphaNumeric'),
						'message' => 'The last name must be alphanumeric.'),
					'last_name_min' => array(
						'rule' => array('minLength', '3'),
				'message' => 'The last name must have at least 3 characters.')),
		'email' => array(
			'isValid' => array(
				'rule' => 'email',
				'required' => true,
				'message' => 'Please enter a valid email address.'),
			'isUnique' => array(
				'rule' => array('isUnique', 'email'),
				'message' => 'This email is already in use.')),

         'confirm_email' => array(
		 			'rule' => 'confirmEmail',
			'message' => 'The email must match.'),

		'password' => array(
			'too_short' => array(
				'rule' => array('minLength', '6'),
				'message' => 'Your password must be at least 6 characters.'),
			'required' => array(
				'rule' => 'notEmpty',
				'message' => 'Password is required.')),
		
        'zip_code' => array(
	        'rule' => array('postal', null, 'us'),
	        'message' => 'A valid U.S zip code is required.'),
	     
         'subject_credentials' => array(
			   			'required' => array(
			   						     'rule' => 'notEmpty',
			   						     'required' => true, 'allowEmpty' => false,
			   						     'message' => 'Please enter Your Credentials for this subject.'))
         
         );
	  

//  }




  /**
   * Custom validation method to ensure that the two entered passwords match
   *
   * @param string $password Password
   * @return boolean Success
   */
  	public function confirmPassword($password = null) {
  		if ((isset($this->data[$this->alias]['password']) && isset($password['confirm_password']))
  			&& !empty($password['confirm_password'])
  			&& ($this->data[$this->alias]['password'] === $password['confirm_password'])) {
  			return true;
  		}
  		return false;
  	}


/**
 * Compares the email confirmation
 *
 * @param array $email Email data
 * @return boolean
 */
	public function confirmEmail($email = null) {
		if ((isset($this->data[$this->alias]['email']) && isset($email['confirm_email']))
			&& !empty($email['confirm_email'])
			&& (strtolower($this->data[$this->alias]['email']) === strtolower($email['confirm_email']))) {
				return true;
		}
		return false;
	}


    /**
	     * Before isUniqueEmail
	     * @param array $options
	     * @return boolean
	**/

function isUnique($check, $email=true) {
	    $student = new Student();
	    $user = null;
	    $user2 = null;
		$user = $this->findByEmail($this->data[$this->alias]['email']);
		//$this->loadModel('Student');
		$user2  = $student->findByEmail($this->data[$this->alias]['email']);

		if( $user != null && !empty($user) ){
			return false;
		} else if($user2 != null && !empty($user2) ){
		   return false;
		}

		return true;
   }


    /**
     * search tutors and associated info by parameters
     * @param  string  $subject_1          		subject 1
     * @param  array   $cur_zip_code          	zip code of first condition (inputed or session)
     * @param  string  $subject_2          		subject 2
     * @param  string  $subject_3          		subject 3
     * @param  string  $hourly_rate        		hourly rate
     * @param  string  $age                		age
     * @param  string  $gender             		gender
     * @param  boolean $background_checked 		background check flag
     * @param  boolean $is_advanced 	   		check if current search is advanced search
     * @param  string  $cur_session_zipcode 	the zip code of current user stored in the session
     * @param  array   $params_query 	   	   	the form passed parameters (this used for the zipsearch)
     * @return array                       		return tutors and associated info by array
     */
     //$result = $tutors_model->find_by_params($kwd, 
     //$conditions_for_search['subject'],  
     //$this->params->query['zip_code'], 
     //"", "", $conditions_for_search['hourly_rate'], 
     //$conditions_for_search['age'], 
     //$conditions_for_search['gender'], 
     //$conditions_for_search['bg_checked'], 
     //$conditions_for_search['is_advanced'], $this->Session->read('cur_zip_code'), $this->params->query);

	function find_by_params($kwd = "",
			$subject_1 = "",
			$cur_zip_code,
			$subject_2 = "",
			$subject_3 = "",
			$hourly_rate  = "",
			$age = "",
			$gender = "",
			$background_checked = false,
			//$location = "",
			//$level = "HS",
			$is_advanced = false,
			$cur_session_zipcode,
			$params_query
		)
	{
       //debug($is_advanced); die();

		$zip_codes = array();
		$zip_result = array();
        $zip_code = "";
        $distance = "";
        $subject_search = true;
        $category_search = true;

        if(!empty($params_query['distance'])) {
           $distance = $params_query['distance'];
        } else {
            $distance = 40;
        }

       // debug("test"); die();
        $conditions_tutor = $this->conditionsForTutors($cur_zip_code, $cur_session_zipcode, $params_query);

        //debug($conditions_tutor); die();
    /**
    	$radiusSearch = new ZipSearch();
		$radiusSearch->set(array('zip_code' => $cur_zip_code));

		if ($radiusSearch->validates(array('fieldList' => array('zip_code'))))
        {
                 //debug($cur_zip_code); die();
			if(
              !((!$rs = $radiusSearch->find(
                        'first', array(
                        'conditions' => array(
                            'ZipSearch.zip_code' => $cur_zip_code))))
                 || (count($rs) == 0))
              )
              {
                // if inputed zip code is in zipsearch table

				$zip_result = $radiusSearch->search($params_query, $cur_zip_code);
                 $zip_code = $cur_zip_code;

			  }
		}

         //debug($zip_result); die();
		if(empty($zip_result)){
		   //The user provided Zip code was NOT found on the zip_searches table
		  // if user's input Zip code fails to yield result, then we try the API determined Zip'
			$zip_result = $radiusSearch->search($params_query, $cur_session_zipcode);
            $zip_code = $cur_session_zipcode;
		}

		if(!empty($zip_result)){
			foreach ($zip_result as $key => $value) {
				if(!empty($value['ZipSearch']['zip_code'])){
					$zip_codes[] = $value['ZipSearch']['zip_code'];
				}
			}
		}

        //debug($zip_codes); die();
        //array('conditions' => array('LOWER(User.first_name)' => strtolower('John') ))
		$data = array();
		$return = array();
		$conditions_tutor = array();
		if(!empty($zip_codes) && sizeof($zip_codes) > 0){
		    //debug("test"); die();
			$conditions_tutor = array(
					'Tutor.zip_code' => $zip_codes,
					'Tutor.email_verified' => 1,
					'Tutor.active' => 1,
					'Tutor.profile_status' => 1
				);
		} else if(empty($zip_code) || $zip_code === "") {

		    $conditions_tutor = array(
					'Tutor.email_verified' => 1,
					'Tutor.active' => 1,
					'Tutor.profile_status' => 1
				);
        }
     **/
		// end $conditions_tutor

		if($is_advanced){  // advanced search
			$is_subject_1_search = false;
           // debug("here"); die();
			// tutor profile conditions
			$conditions_tutor_profile = array();

			//debug($hourly_rate); //die();
			$hourly_rate = explode(",", $hourly_rate);
			$min_rate = $hourly_rate[0];
			$max_rate = $hourly_rate[1];
            
			//debug($min_rate);
			//debug($max_rate); //die();
			
             if(!empty($min_rate) && !empty($max_rate)) { //die();
			  $min_rate = $hourly_rate[0];
			  $max_rate = $hourly_rate[1];
            } else {
                $min_rate = 10;
			    $max_rate = 250;
            }
			
			//debug($min_rate);
			//debug($max_rate); die();

			if(!empty($hourly_rate)){
				//Make sure the db fields hourly_rate stays as smallint
                $conditions_tutor_profile['TutorProfile.hourly_rate >= '] = $min_rate;
				$conditions_tutor_profile['TutorProfile.hourly_rate <= '] = $max_rate;
			}

			if(!empty($gender)){
				$conditions_tutor_profile['TutorProfile.gender'] = $gender;
			}

			$age = explode(",", $age);
           // debug($age); die();
            if( !empty($age[0]) && !empty($age[1])) { //die();
			   $min_age = $age[0];
			   $max_age = $age[1];
            } else {
                $min_age = 18;
			   $max_age = 100;
            }
			
			 //debug($params_query);  die();
			 //die();
			if(!empty($age)){
			    //Make sure the db fields hourly_rate stays as smallint
                //Only way condition would work
				$conditions_tutor_profile['TutorProfile.age >= '] = $min_age;
				$conditions_tutor_profile['TutorProfile.age <= '] = $max_age;
			}

			if(!empty($background_checked) && $background_checked){
				$conditions_tutor_profile['TutorProfile.background_checked'] = 1;
			 }
			 
			 $conditions_tutor_level = array();
			 $conditions_tutor_session = array();
			 $conditions_tutor_availability = array();
			 //debug($params_query['level']); die();
			 
			 if(!empty($params_query['level'])) {
                 $conditions_tutor_level['TutorLevel.level'][]  = $params_query['level'];
			 } else {
				 $conditions_tutor_level['TutorLevel.level'][] = 'all';

			 }
		//	 debug($conditions_tutor_level); //die();
			// $params_query['location'] = 0;
			 if(!empty($params_query['location'])) {
                 $conditions_tutor_session['TutorSession.location'][]  = $params_query['location'];
			 } else {
				 $conditions_tutor_session['TutorSession.location'][] = 'all';
			 }
			
			 if(!empty($params_query['availability'])) {	
				 // debug($params_query['availability'])	;	  
				 $availabilities = $params_query['availability'];
			  }
			 // debug($availabilities); die();
			 
		   if(!empty($availabilities) ) { 			  //The tutor needs only to be available for one of the days in array to be retrieved
				 if(strpos($availabilities, 'sunday') !== false) {
					 $conditions_tutor_availability['TutorAvailability.sunday'][]  = 1;						 
				 } 
				 if(strpos($availabilities, 'monday') !== false) {
					 $conditions_tutor_availability['TutorAvailability.monday'][]  = 1;
                    // $conditions_tutor_availability['TutorAvailability.sunday'][]  = 0;					 
				 } 
				 if(strpos($availabilities, 'tuesday') !== false) {
					 $conditions_tutor_availability['TutorAvailability.tuesday'][]  = 1;	
                     // $conditions_tutor_availability['TutorAvailability.monday'][]  = 0;					 
				 } 
				 if(strpos($availabilities, 'wednesday') !== false) {
					 $conditions_tutor_availability['TutorAvailability.wednesday'][]  = 1;	
                     // $conditions_tutor_availability['TutorAvailability.tuesday'][]  = 0;					 
				 } 
				 if(strpos($availabilities, 'thursday') !== false) {
					 $conditions_tutor_availability['TutorAvailability.thursday'][]  = 1;
                     // $conditions_tutor_availability['TutorAvailability.wednesday'][]  = 0;					 
				 } 
				 if(strpos($availabilities, 'friday') !== false) {
					 $conditions_tutor_availability['TutorAvailability.friday'][]  = 1;	
                      // $conditions_tutor_availability['TutorAvailability.thursday'][]  = 0;					 
				 } 
				 if(strpos($availabilities, 'saturday') !== false) {
					 $conditions_tutor_availability['TutorAvailability.saturday'][]  = 1;
					  //$conditions_tutor_availability['TutorAvailability.friday'][]  = 0;
				 }
		    } 
			if(count($conditions_tutor_availability) >= 7) {
				//Reset because user is asking for tutors available on any day
				//If array is empty, won't be part of the search condition
				$conditions_tutor_availability = array(); 
			}
            $conditions_tutor_profile['TutorProfile.profile_ready'] = 1;

            // tutor image conditions
          //  debug($conditions_tutor_location);  die();
			$conditions_tutor_image = array();    //DG

			 $conditions_tutor_image = array(
						'TutorImage.status' => 1,
						'TutorImage.featured' => 1,
              );

			// tutor subject conditions
			$is_subject_1_search = false;
			$subject_1_category = "";
			// tutor subject conditions
			//$conditions_tutor_subject = array('OR' =>array());
            $conditions_tutor_subject = array();
            $conditions_tutor_subject_cat = array();
			$category_modal = new Categorie();
                  
			if(!empty($subject_1) && $subject_1 != 'All Subjects' ){
				/**
				 * get subject category
				 */
                //debug("here"); die();
				$category_data = $category_modal->find('all',
					array(
						'conditions' => array(
                        'OR' => array(
								  'name'	    => $subject_1,
                                  'category_id'	=> $subject_1
							))
						));

                //debug($category_data); //die();
                $subject_model = new Subject();
                $subject_1_name = $subject_model->get_subject_by_name($subject_1);
                //debug($subject_1_name); die();
                //debug($category_data[0]['Categorie']['name']); die();
               if(!empty($category_data) && strtolower($category_data[0]['Categorie']['name']) === strtolower($subject_1)){ // if the inputed value is category
					$subject_1_category = $subject_1;
                    //debug($subject_1_category); die();
                    //$cat =  strtolower('TutorSubject.subject_category_name');
                    $cat =  'TutorSubject.subject_category_name';
                    //$conditions_tutor_subject['OR'][$cat][] = $subject_1_category; //strtolower($subject_1_category);
                     $conditions_tutor_subject[$cat][] = $subject_1_category; //strtolower($subject_1_category);

                     $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                     $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                     $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                     $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;

                     $subject_search = false;
                     $category_search = true;

                    //debug($conditions_tutor_subject); die();

				} else if(!empty($category_data) && strtolower($category_data[0]['Categorie']['category_id']) === strtolower($subject_1)){ // if the inputed value is category
					$subject_1_category = $subject_1;
                    $cat =  'TutorSubject.subject_category_id';
                    //$conditions_tutor_subject['OR'][$cat][] = $subject_1_category; //strtolower($subject_1_category);
                     $conditions_tutor_subject[$cat][] = $subject_1_category;

                     $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                     $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                     $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                     $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;

                     $subject_search = false;
                     $category_search = true;

				} else if(!empty($subject_1_name)){
                     //$subj = strtolower('TutorSubject.subject_name');
                     $subj = 'TutorSubject.subject_name';
                     $conditions_tutor_subject[$subj][]  = $subject_1_name; //strtolower($subject_1);
                     $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                     $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                     $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                     $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;

                    //debug($conditions_tutor_subject); die();
                     $subject_search = true;
                     $category_search = false;

				} //else {
                    //debug($zip_code); die();
                   // return ($this->get_all_tutors($zip_code, $distance, $kwd));
                //}

            // debug($conditions_tutor_subject); die();
            /** $data = $this->search_conditions(
                                 $subject_1_name,
                                 $subject_1_category,
                                 $subject_search,
                                 $category_search,
                                 $conditions_tutor,
                                 $conditions_tutor_subject,
                                 //$conditions_tutor_subject_cat,
                                 $conditions_tutor_profile,
                                 $conditions_tutor_image,
                                 $kwd
                               ) ;**/
							   
							 // debug($conditions_tutor_profile); die();
				 $data = $this->search_conditions(
									 $subject_1_name,
									 $subject_1_category,
									 $subject_search,
									 $category_search,
									 $conditions_tutor,
									 $conditions_tutor_subject,
									 //$conditions_tutor_subject_cat,
									 $conditions_tutor_profile,
									 $conditions_tutor_image,
									 $kwd,
									 $conditions_tutor_level,
									 $conditions_tutor_session,
									 $conditions_tutor_availability
								   ) ;
								//debug("hehe"); die();
  
         } else { // User did not enter a search Keyword
        // debug($distance); //die();
                   //debug($conditions_tutor_profile); die();
                 $data = $this->get_all_tutors_adv($zip_code,
                                                   $cur_session_zipcode,
                                                   $distance,
                                                   $params_query,
                                                   $conditions_tutor_profile,
                                                  // $conditions_tutor_subject,
                                                   $kwd,
												   $conditions_tutor_level,
									               $conditions_tutor_session,
												   $conditions_tutor_availability
                                                   ); //, $hourly_rate, $age);
         }
          
         if(!empty($data)) {
                $data = array_values($data); //reindex array zero-based
          }
		  // debug($data); die();
          return $data;
		}
		else{ // no advanced search
              // debug("here we go"); die();
			$is_subject_1_search = false;
			$subject_1_category = "";

			// tutor Search conditions
			$conditions_tutor_subject = array();
            $conditions_tutor_profile = array();
            $conditions_tutor_image = array();

			$category_modal = new Categorie();

			if(!empty($subject_1)){
			  //debug($subject_1); die();
             /** if($subject_1 === 'Mathematics') {
                 $subject_1 = 'Math';
              } else if($subject_1 === 'Technology') {
                 $subject_1 = 'Tech';
                
              }
              **/
             // tutor profile conditions
                $conditions_tutor_profile = array(
						'TutorProfile.profile_ready' => 1
				);

	            // tutor image conditions
                 $conditions_tutor_image = array(
						'TutorImage.status' => 1,
						'TutorImage.featured' => 1,
                 );

               // see if the inputed keyword is a Subject
                $subject_model = new Subject();
                $subject_1_name = $subject_model->get_subject_by_name($subject_1);
                //debug($subject_1_name); die();
				/**
				 * get subject category
				 */
				// see if the inputed keyword is a category
					$category_data = $category_modal->find('all',
					array(
						'conditions' => array(
                        'OR' => array(
								  'name'	    => $subject_1,
                                  'category_id'	=> $subject_1
							))
						));
                      //debug($category_data); die();
				 if(!empty($category_data) && strtolower($category_data[0]['Categorie']['name']) === strtolower($subject_1)){ // if the inputed value is category
					$subject_1_category = $subject_1;
                   // debug($subject_1_category); die();
                    //$cat =  strtolower('TutorSubject.subject_category_name');
                    $cat =  'TutorSubject.subject_category_name';
                    //debug($subject_1_category); die();
                    //$conditions_tutor_subject['OR'][$cat][] = $subject_1_category; //strtolower($subject_1_category);
                    $conditions_tutor_subject[$cat][] = $subject_1_category; //strtolower($subject_1_category);

                     $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                     $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                     $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                     $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;

                      //debug($conditions_tutor_subject); die();
                    $subject_search = false;
                    $category_search = true;

				} else if(!empty($category_data) && strtolower($category_data[0]['Categorie']['category_id']) === strtolower($subject_1)){ // if the inputed value is category
					$subject_1_category = $subject_1;
                    $cat =  'TutorSubject.subject_category_id';

                    //debug($subject_1_category); die();
                    //$conditions_tutor_subject['OR'][$cat][] = $subject_1_category; //strtolower($subject_1_category);
                    $conditions_tutor_subject[$cat][] = $subject_1_category;

                     $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                     $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                     $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                     $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;

                     // debug($conditions_tutor_subject); die();
                    $subject_search = false;
                    $category_search = true;

                    // debug($subject_1_category); die();

				} else if(!empty($subject_1_name)){
                     //$subj = strtolower('TutorSubject.subject_name');
                     $subj = 'TutorSubject.subject_name';
                     $conditions_tutor_subject[$subj][]  = $subject_1_name; //strtolower($subject_1);
                     $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                     $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                     $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                     $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;

                  // debug($conditions_tutor_subject); die();
                     $subject_search = true;
                     $category_search = false;

				}//else { // Search keyword is NEITHER a Subject NOR a Category
                        //So we return all tutors within zip code that meet other criteria
                        //debug("test"); die();
                    //return ($this->get_all_tutors($zip_code, $distance, $kwd));
               // }
           //   debug($conditions_tutor_subject); die();
			$data = $this->search_conditions(
                                 $subject_1_name,
                                 $subject_1_category,
                                 $subject_search,
                                 $category_search,
                                 $conditions_tutor,
                                 $conditions_tutor_subject,
                                 //$conditions_tutor_subject_cat,
                                 $conditions_tutor_profile,
                                 $conditions_tutor_image,
                                 $kwd,
								 $conditions_tutor_level,
							     $conditions_tutor_session,
								 $conditions_tutor_availability
                               ) ;

               // debug($data); die();

       } else {// user did NOT put in a Search Keyword
          //debug($zip_code); die();
          $data = $this->get_all_tutors($zip_code, null, $cur_session_zipcode, $distance, $kwd, $params_query);
       }
    }

    if(!empty($data)) {
        $data = array_values($data);
    }
    return $data;
}

function get_all_tutors(   $cur_zip_code,
                           $cat,
                           $cur_session_zipcode,
                           $distance,
                           $kwd,$params_query
                         )
   {
	   
	   //debug("dfhdh"); die();

        $orderByTutorProfile  = "";
        $orderByTutorRating   = "";
        $orderByTutorHours    = "";
        $orderByTutorDistance  = "";
        //debug("test"); die();
        if($kwd != "") {
        if(strtolower($kwd) === strtolower("Lowest Price")) {
             $orderByTutorProfile = array('TutorProfile.hourly_rate ASC');
             //$orderByTutorRating   = "";
            // $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Highest Price")) {
             $orderByTutorProfile = array('TutorProfile.hourly_rate DESC');
            // $orderByTutorRating   = "";
            // $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Ratings")) {
            $orderByTutorRating = ""; //array('TutorRating.overall_rating DESC');
           //  $orderByTutorProfile  = "";
             //$orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Hours")) {
             $orderByTutorProfile = array('TutorProfile.hours DESC');
            // $orderByTutorRating = "";
             //$orderByTutorProfile  = "";
            // $orderByTutorHours    = "";
        }
     }
                //debug($zip_result); die();
                $data = array();
                $return = array();


          //debug($conditions_tutor_profile); die();
	        // tutor_conditions
           // debug($zip_codes); die();

          $orderByStudentWatchList = array('StudentWatchList.created DESC');
          // debug($cur_zip_code);
       //debug($cur_session_zipcode);
       //debug($distance); die();
          $conditions_tutor = $this->conditionsForTutors($cur_zip_code, $cur_session_zipcode, $params_query);
         // debug($conditions_tutor); die();
          
          if(empty($conditions_tutor)  ||  sizeof($conditions_tutor) <= 0){
                  return $data = $this->find('all') ; //array();
          }
          //else { //if no tutors were found in the zip code bag, return
			    // return $data = array();
         // }
		 
		 $conditions_tutor_profile = array();
		 $conditions_tutor_profile['TutorProfile.profile_ready'] = 1;
		 if(!empty($params_query['amount_min_rate']) && !empty($params_query['amount_max_rate'])){
				//Make sure the db fields hourly_rate stays as smallint
                $conditions_tutor_profile['TutorProfile.hourly_rate >= '] = $params_query['amount_min_rate'];
				$conditions_tutor_profile['TutorProfile.hourly_rate <= '] = $params_query['amount_max_rate'];
				//$conditions_tutor_profile['TutorProfile.profile_ready'] = 1;
			} //else {

           // tutor profile conditions
              //  $conditions_tutor_profile = array(
					//	'TutorProfile.profile_ready' => 1
					//);
			//}
			
			
			
			 //debug($min_age); 
			 //die();
			 if(!empty($params_query['min_age']) && !empty($params_query['max_age'])){
			    //Make sure the db fields hourly_rate stays as smallint
                //Ony way condition would work
				$conditions_tutor_profile['TutorProfile.age >= '] = $params_query['min_age'];
				$conditions_tutor_profile['TutorProfile.age <= '] = $params_query['max_age'];
			}
               // end tutor profile conditions


              //debug($conditions_tutor); die();
              //debug($cur_session_zipcode);
			// end tutor_conditions


	            // tutor image conditions
                 $conditions_tutor_image = array(
						'TutorImage.status' => 1,
						'TutorImage.featured' => 1,
					);
				//end $conditions_tutor_image = array();


                $conditions_tutor_subject = array();
                $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;
                
               // if(!empty($cat))
                 //  $conditions_tutor_subject['TutorSubject.subject_category_id'][] = $cat;
                 
               //  debug($conditions_tutor_subject); die();
				$data = $this->find('all', array(
					'conditions' => array(
						$conditions_tutor
                        //'order' => array('TutorProfie.hourly_rate ASC')
					 ),
                    'contain'=>array(
						'TutorProfile'	=>	array(
        							'conditions' =>	array(
                                                 $conditions_tutor_profile
                                      ),
                                      'order' => $orderByTutorProfile
        							),
                         'TutorSubject' =>	array(
							'conditions'	=>	array(
									$conditions_tutor_subject
								)
							) ,
                        'StudentWatchList', // =>	array('order' => $orderByStudentWatchList),
						'TutorPreference',
						'TutorRating',
						'TutorCategorie',
						'TutorImage' =>	array(
							'conditions'	=>	array(
									$conditions_tutor_image
								)
							)
						)
					));

              // debug($data); die();
                $i=0;
                foreach ($data as $key => $value) {
                    //We do not want to show Tutor whose Profile is not ready
                    if(empty($data[$i]['TutorProfile']['id']) || empty($data[$i]['TutorSubject']) || sizeof($data[$i]['TutorSubject']) <=0 ){
                                unset($data[$i]);
							}
                    $i++;
                }
                // debug($data); die();
               if(!empty($data) && sizeof($data) > 0) {
                    //debug("test"); die();
				      return $data;
                } else {
                     // debug("test"); die();
                    //will eventually write new code to return all tutors
                   $data = $this->find('all');
                }
                //debug($data); die();
                return $data;
    }

   function get_all_tutors_adv(
                        $cur_zipcode,
                        $cur_session_zipcode,
                        $distance,
                       // $params_query,
                       // $conditions_tutor_subject,
                        $params_query,
                        $conditions_tutor_profile,
                        $kwd,
						 $conditions_tutor_level,
						 $conditions_tutor_session,
						 $conditions_tutor_availability)
       {

        $orderByTutorProfile  = "";
        $orderByTutorRating   = "";
        $orderByTutorHours    = "";
        $orderByTutorDistance  = "";


       //($distance); //die();
      // debug($cur_session_zipcode); die();

                $data = array();
                $return = array();

              $conditions_tutor = $this->conditionsForTutors($cur_zipcode, $cur_session_zipcode, $params_query);

              /** if(!empty($conditions_tutor)  && sizeof($conditions_tutor) > 0){

              } else { //if no tutors were found in the zip code bag, return
			     return $data = array();
              } **/
			  
			  if(empty($conditions_tutor)  && sizeof($conditions_tutor) <= 0){
                     return $data = $this->find('all');
              } 
              //debug($cur_session_zipcode);
			// end tutor_conditions


	            // tutor image conditions
                 $conditions_tutor_image = array(
						'TutorImage.status' => 1,
						'TutorImage.featured' => 1,
					);
				//end $conditions_tutor_image = array();

                $conditions_tutor_subject = array();
                $conditions_tutor_subject['TutorSubject.searchable_status'][] = 1;
                $conditions_tutor_subject['TutorSubject.opt_out'][] = 0;
                $conditions_tutor_subject['TutorSubject.approval_status'][] = 'Y';
                $conditions_tutor_subject['TutorSubject.credentials_status'][] = 1;
                // if(!empty($cat))
                  // $conditions_tutor_subject['TutorSubject.subject_category_id'][] = $cat;
                 
                 //debug($conditions_tutor_subject); die();

    if($kwd != "") {
        if(strtolower($kwd) === strtolower("Lowest Price")) {
             $orderByTutorProfile = array('TutorProfile.hourly_rate ASC');
             $orderByTutorRating   = "";
             $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Highest Price")) {
             $orderByTutorProfile = array('TutorProfile.hourly_rate DESC');
             $orderByTutorRating   = "";
             $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Ratings")) {
            $orderByTutorRating = ""; //array('TutorRating.overall_rating DESC');
             $orderByTutorProfile  = "";
             $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Hours")) {
             $orderByTutorProfile = array('TutorProfile.hours DESC');
             $orderByTutorRating = "";
             //$orderByTutorProfile  = "";
             $orderByTutorHours    = "";
        }
     }

      //debug($conditions_tutor_profile); die();

       $orderByStudentWatchList = array('StudentWatchList.created DESC');
      $data = $this->find('all', array(
					'conditions' => array(
						$conditions_tutor
						),

					'contain'=>array(
						'TutorProfile'	=>	array(
        							'conditions' =>	array(
                                                    $conditions_tutor_profile
                                      ),
                                      'order' => $orderByTutorProfile
        							),
                          'TutorSubject' =>	array(
							'conditions'	=>	array(
									$conditions_tutor_subject
								)
							) ,							
						   'TutorLevel' =>	array(
                						 'conditions' => array(
											$conditions_tutor_level
											)
                						),																			
						   'TutorSession' =>	array(
                						 'conditions' => array(
											$conditions_tutor_session
											)
                						),
							
							'TutorAvailability' =>	array(
                						 'conditions' => array(
											'OR' => $conditions_tutor_availability
											)
                						),
							
                        // 'StudentWatchList', //=>	array('order' => $orderByStudentWatchList),
						//'TutorPreference',
						//'TutorRating',
						'TutorCategorie',
						'TutorImage' =>	array(
							'conditions'	=>	array(
									$conditions_tutor_image
								)
							)
						)
					));
              //debug($data); die();
                $i=0;
               /** foreach ($data as $key => $value) {
                    //We do not want to show Tutor whose Profile is not ready
                    // debug($data[$i]['TutorSubject']); //die();
                    if(empty($data[$i]['TutorProfile']['id']) || empty($data[$i]['TutorSubject']) || sizeof($data[$i]['TutorSubject']) <=0 ){
                                unset($data[$i]);
							}
                    $i++;
                } **/
				//debug($conditions_tutor_level); //die();
				//debug($conditions_tutor_availability); // die();
				//debug($data); die();
			foreach ($data as $key => $value) {
				if( $conditions_tutor_level['TutorLevel.level'][0] != 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] != 'all') {
						  
						if(empty($data[$i]['TutorProfile']['id']) || 
						   empty($data[$i]['TutorLevel'])         || 
						   sizeof($data[$i]['TutorLevel']) <=0     ||
						   empty($data[$i]['TutorSession'])       ||
						   sizeof($data[$i]['TutorSession']) <=0  ||
						   empty($data[$i]['TutorSubject'])       || 
						   sizeof($data[$i]['TutorSubject']) <=0  ||
						  (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday']) && 
								   empty($data[$i]['TutorAvailability']['monday'])&&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday']) && 
								   empty($data[$i]['TutorAvailability']['friday']) && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
						    )
						{
							   unset($data[$i]);
						}	
				
			  } else if($conditions_tutor_level['TutorLevel.level'][0] == 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] != 'all' ) {				  	  
							if(empty($data[$i]['TutorProfile']['id']) || 
							   empty($data[$i]['TutorSession'])       ||
							   sizeof($data[$i]['TutorSession']) <=0  ||
							   empty($data[$i]['TutorSubject'])       || 
							   sizeof($data[$i]['TutorSubject']) <=0  ||
							   (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday']) && 
								   empty($data[$i]['TutorAvailability']['monday'])&&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday']) && 
								   empty($data[$i]['TutorAvailability']['friday']) && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
						        )
						{
							   unset($data[$i]);
						}						
				  //debug($conditions_tutor_availability); //die();
			  } else if($conditions_tutor_level['TutorLevel.level'][0] != 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] == 'all' ) {	
					  					  
			    if(empty($data[$i]['TutorProfile']['id'])             || 
							   empty($data[$i]['TutorLevel'])         || 
							   sizeof($data[$i]['TutorLevel']) <=0    ||
							   empty($data[$i]['TutorSubject'])       || 
							   sizeof($data[$i]['TutorSubject']) <=0  ||
							   (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday']) && 
								   empty($data[$i]['TutorAvailability']['monday'])&&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday']) && 
								   empty($data[$i]['TutorAvailability']['friday']) && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
				        )
							{
								   unset($data[$i]);
							}
				
				} else if($conditions_tutor_level['TutorLevel.level'][0] == 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] == 'all') {	
					  					 //  debug("3");
					   if(empty($data[$i]['TutorProfile']['id']) || 
							 
							   empty($data[$i]['TutorSubject'])       || 
							   sizeof($data[$i]['TutorSubject']) <=0  ||
							  (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday']) && 
								   empty($data[$i]['TutorAvailability']['monday'])&&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday']) && 
								   empty($data[$i]['TutorAvailability']['friday']) && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
							 )
							{
								   unset($data[$i]);
							}
				
				}
				
				$i++;
         }
                // debug($data);
                //die();
               // if(!empty($data) && sizeof($data) > 0) {
                    //debug("test"); die();
				     // return $data;
                 //} //else {
                     // debug("test"); die();
                    //will eventually write new code to return all tutors
                    //return  ($this->find('all'));
                //}
                //debug($data); die();
                return $data;
		}

 function get_all_subjects_and_categories(){
    	$subject_model = new Subject();
    	$category_modal = new Categorie();
    	$subjects = $subject_model->find('all');

    	$return = array();

    	$categories = $category_modal->find('all');

    	foreach ($categories as $key => $value) {
    		$return[] = $value['Categorie']['name'];
    	}

        //debug($return); die();

    	foreach ($subjects as $key => $value) {
    		$return[] = $value['Subject']['name'];
    	}
        // debug($return); die();
    	return $return;
}

function get_student_connections($id=null) {
	
	$student_tutor_modal = new StudentsTutor();	
	$return = array();
	//debug($id); die();
	if(!empty($id)) {
    $students = $student_tutor_modal->find('all',  
                array(
					'conditions' => array(
						'tutor_id' => $id
						)));                      
	}
     if(!empty($students )) {
       foreach ($students as $key => $value) {
    	  $return[] = $value['StudentsTutor']['student_name'];
		  //$return[] = array($value['StudentsTutor']['student_id'] => $value['StudentsTutor']['student_name']);
       }
    }

  return $return;
  
	
}


function get_all_subjects($id=null){
    	$subject_model = new Subject();
        $subjects = array();
       	$return = array();

        if(!empty($id)) {
            $subjects = $subject_model->find('all',  
                array(
					'conditions' => array(
						'Subject.category_id' => $id
						))//,
                   //array('order' => array('name' => 'asc'))     
                        );

        } else {
              $subjects = $subject_model->find('all');
        }
        
       
//$this->set('clans', $this->Clan->find('list', array('order' => array('name' => 'asc)));

    	foreach ($subjects as $key => $value) {
    	   if(!empty($value['Subject']['name'])  &&
                  !empty($value['Subject']['subject_id'])){
    	//	$return[] = $value['Subject']['name'];
             $return[] = array($value['Subject']['subject_id'] => $value['Subject']['name']);
    	  }
    	}

        sort($return);
        // debug($return); die();

        //uasort($return, $this->cmp);
       // array_multisort($return);
        // debug($return); die();
    	return $return;
}
function cmp($a, $b) {
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
function get_all_categories($id=null){
    	//$subject_model = new Subject();
    	$category_modal = new Categorie();
        $categories = array();
    	$return = array();
             //debug("ete"); die();
     if(!empty($id)) {
    	$categories = $category_modal->find('all');
      }else {
        $categories = $category_modal->find('all',  array(
					'conditions' => array(
						'Categorie.category_id' => $id
						)));
      }
         foreach ($categories as $key => $value) {
            //debug($key);
            $key = $value['Categorie']['category_id'];

            $return[] =  array($value['Categorie']['category_id'] => $value['Categorie']['name']);
    	}

    	return $return;
}

public function get_all_subjects_for_tutor($id){
    $tutorSubjects = new TutorSubject();
    $tutor_subjects = null;
    //debug("here"); die();
    if(!empty($id)) {
          //debug($id); die();
       $tutor_subjects = $tutorSubjects->get_all_subjects_for_tutor($id);
    }
   //debug($tutor_subjects); die();
  // uasort($tutor_subjects, 'cmp');
 return $tutor_subjects;
}

public function get_tutor_subj_cred($id, $subj_id) {

    $tutorSubjects = new TutorSubject();
   if(!empty($id) && !empty($subj_id)) {
          //debug($id); die();
       $tutor_subj_cred = $tutorSubjects->get_tutor_subj_cred($id, $subj_id);
    }

     //debug($id); die();
    return $tutor_subj_cred;
}

public function get_tutor_profile_pic($id) {

    $tutorPhoto = new TutorImage();
   if(!empty($id)) {
          //debug($id); die();
       $pic = $tutorPhoto->get_tutor_profile_pic($id);
    }

    return $pic;
}

public function get_tutor_profile($id) {

    $tutorProfile = new TutorProfile();
   if(!empty($id)) {
          //debug($id); die();
       $profile = $tutorProfile->get_tutor_profile($id);
    }

    return $profile;
}

public function get_tutor_ratings($id) {

    $tutorRating = new TutorRating();
   if(!empty($id)) {
          //debug($id); die();
       $ratings = $tutorRating->get_tutor_ratings($id);
    }
     // debug($ratings); die();
    return $ratings;
}

public function find_job_app_by_id($id, $job_id=null) {

    $tutorJobApp = new TutorJobApplication();
    $job_app = null;
    $data= array();

    if(!empty($job_id)) {
       $job_app = $tutorJobApp->getJobAppDetails($id, $job_id);

    //debug($job_post);
    foreach ($job_app as $key => $value) {
       if(!empty($value['TutorJobApplication'])){
            $data[] = $value['TutorJobApplication'];
        }
     }
   }

    //debug($data); die();
    return $data;
 }

 function search_conditions(  $subject_1 = "",
                                 $subject_1_category = "",
                                 $subject_search = true,
                                 $category_search = false,
                                 $conditions_tutor = array(),
                                 $conditions_tutor_subject = array(),
                                 //$conditions_tutor_subject_cat = array(),
                                 $conditions_tutor_profile = array(),
                                 $conditions_tutor_image = array(),
                                 $kwd = "",
								 $conditions_tutor_level,
								 $conditions_tutor_session,
								 $conditions_tutor_availability
                               )
        {

          //  debug($conditions_tutor_profile); 
			// debug($conditions_tutor_session); 
			//die();

    $orderByTutorProfile  = "";
    $orderByTutorRating   = "";
    $orderByTutorHours    = "";
    $orderByTutorDistance  = "";

    if($kwd != "") {
        if(strtolower($kwd) === strtolower("Lowest Price")) {
             $orderByTutorProfile = array('TutorProfile.hourly_rate ASC');
             $orderByTutorRating   = "";
             $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Highest Price")) {
             $orderByTutorProfile = array('TutorProfile.hourly_rate DESC');
             $orderByTutorRating   = "";
             $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Ratings")) {
            $orderByTutorRating = ""; //array('TutorRating.overall_rating DESC');
             $orderByTutorProfile  = "";
             $orderByTutorHours    = "";
        } else if(strtolower($kwd) === strtolower("Hours")) {
             $orderByTutorProfile = array('TutorProfile.hours DESC');
             $orderByTutorRating = "";
             //$orderByTutorProfile  = "";
             $orderByTutorHours    = "";
        }
     }


    // $orderBy = "";
     //debug($orderBy); die();

       //debug($conditions_tutor_subject); die();

      // $orderByStudentWatchList = array('StudentWatchList.created DESC');
	  

        $data = $this->find('all', array(
					'conditions' => array(
						$conditions_tutor
						),
				        'contain' => array(
						   'TutorProfile'	=>	array(
        							'conditions' =>	array(
                                                    $conditions_tutor_profile
                                      ),
                                      'order' => $orderByTutorProfile
        							),
                           'TutorSubject'	=>	array(
                						'conditions' =>	array(
                								$conditions_tutor_subject,
                							)
                						),
										
							'TutorLevel' =>	array(
                						 'conditions' => array(
											
													  $conditions_tutor_level													  
												)
                						),
							'TutorSession' =>	array(
                						 'conditions' => array(
																								 
													  $conditions_tutor_session													  
												)
                						),
							'TutorAvailability' =>	array(
                						 'conditions' => array(
																								 
													'OR' =>  $conditions_tutor_availability												  
												)
                						),
                            'TutorPreference',
        					//'TutorRating' , //=> array ('order' => $orderByTutorRating),
        					'TutorCategorie',
                            'StudentWatchList', //=>	array('order' => $orderByStudentWatchList),       					
        					'TutorImage' =>	array(
        						'conditions'	=>	array(
        								$conditions_tutor_image
        							)
        						)
        					)
				));

//---------------------------------------------------------------------

           
           $i=0;
		  //debug($conditions_tutor_availability); //die();
		  // debug($data[$i]['TutorLevel']); 
		  //debug($conditions_tutor_level);
		 // debug($conditions_tutor_session); 
		  // die();
           /**
		   foreach ($data as $key => $value) {
                    if(empty($data[$i]['TutorProfile']['id']) || empty($data[$i]['TutorSubject']) || sizeof($data[$i]['TutorSubject']) <=0){
                       unset($data[$i]);
			         }
                    $i++;
              } 
			  **/
			  
			 // debug($data); die();
			  foreach ($data as $key => $value) {
				  if( $conditions_tutor_level['TutorLevel.level'][0] != 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] != 'all' ) {
						  
						if(empty($data[$i]['TutorProfile']['id']) || 
						   empty($data[$i]['TutorLevel'])         || 
						   sizeof($data[$i]['TutorLevel']) <=0    ||
						   empty($data[$i]['TutorSession'])       ||
						   sizeof($data[$i]['TutorSession']) <=0  ||
						   empty($data[$i]['TutorSubject'])       || 
						   sizeof($data[$i]['TutorSubject']) <=0  ||
						   (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday'])  && 
								   empty($data[$i]['TutorAvailability']['monday'])  &&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday'])  && 
								   empty($data[$i]['TutorAvailability']['friday'])    && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
						    )
						{
							   unset($data[$i]);
						}	
				
			  } else if( $conditions_tutor_level['TutorLevel.level'][0] == 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] != 'all') {	
                                  // debug("2");					  
							if(empty($data[$i]['TutorProfile']['id']) || 
							   empty($data[$i]['TutorSession'])       ||
							   sizeof($data[$i]['TutorSession']) <=0  ||
							   empty($data[$i]['TutorSubject'])       || 
							   sizeof($data[$i]['TutorSubject']) <=0  ||
							  (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday']) && 
								   empty($data[$i]['TutorAvailability']['monday'])&&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday']) && 
								   empty($data[$i]['TutorAvailability']['friday']) && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
						        )
						{
							   unset($data[$i]);
						}						
				  
			  } else if( $conditions_tutor_level['TutorLevel.level'][0] != 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] == 'all') {	
					  					  // debug("3");
					   if(empty($data[$i]['TutorProfile']['id']) || 
							   empty($data[$i]['TutorLevel'])         || 
							   sizeof($data[$i]['TutorLevel']) <=0     ||
							   empty($data[$i]['TutorSubject'])       || 
							   sizeof($data[$i]['TutorSubject']) <=0  ||
							   (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday']) && 
								   empty($data[$i]['TutorAvailability']['monday'])&&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday']) && 
								   empty($data[$i]['TutorAvailability']['friday']) && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
						        )
							{
								   unset($data[$i]);
							}
				
				}  else if( $conditions_tutor_level['TutorLevel.level'][0] == 'all' && 
				      $conditions_tutor_session['TutorSession.location'][0] == 'all') {	
					  					//   debug("3");
					   if(empty($data[$i]['TutorProfile']['id']) || 
							 
							   empty($data[$i]['TutorSubject'])       || 
							   sizeof($data[$i]['TutorSubject']) <=0  ||
							   (sizeof($conditions_tutor_availability) > 0 && 
								  (empty($data[$i]['TutorAvailability']['sunday']) && 
								   empty($data[$i]['TutorAvailability']['monday'])&&
								   empty($data[$i]['TutorAvailability']['tuesday']) && 
								   empty($data[$i]['TutorAvailability']['wednesday']) && 
								   empty($data[$i]['TutorAvailability']['thursday']) && 
								   empty($data[$i]['TutorAvailability']['friday']) && 
								   empty($data[$i]['TutorAvailability']['saturday']) 
						         ))
						        )
							{
								   unset($data[$i]);
							}
				
				}
				
				$i++;
         }
          //debug($data); die();
           $data = array_values($data);
           $j=0;
           $count=0;
           if($subject_search && !empty($data))  {
             foreach ($data as $key => $value) {
                // debug($value['TutorSubject']); //die();//[0]['name']);
              // if(!empty($value['TutorSubject'])) {
                    $count = sizeof($value['TutorSubject']);
                    foreach ($value['TutorSubject'] as $key => $value1) {
                        //debug(sizeof($value['TutorCategorie'])); die();
                       // array('conditions' => array('LOWER(User.first_name)' => strtolower('John') ))
                        if(strtolower($value1['subject_name']) != strtolower($subject_1)){
                        //if($value1['subject_name'] != strtolower($subject_1)){
                        //if($value1['subject_name'] != $subject_1){
                            $count--;
                         }
                    }
                    if($count == 0 ) { //}|| empty($value1['TutorSubject']) || sizeof($value1['TutorSubject']) <=0) {
                       // debug($data[$j]); die();
                        unset($data[$j]);
                    }
                  //}
                    $j++;
              }
           } else if($category_search && !empty($data)){
              foreach ($data as $key => $value) {
                //debug($value); die();
                    $count = sizeof($value['TutorCategorie']);
                    foreach ($value['TutorCategorie'] as $key => $value1) {
                       // debug(sizeof($value['TutorCategorie'])); //die();
                       //debug($value1); die();
                        if(
                         (strtolower($value1['name']) != strtolower($subject_1_category))
                           &&
                         (strtolower($value1['category_id']) != strtolower($subject_1_category))

                         ){
                        //if($value1['name'] != strtolower($subject_1_category)){
                            $count--;
                          }
                     }
                     if($count == 0 || empty($value['TutorSubject']) || sizeof($value['TutorSubject']) <=0) {
                         unset($data[$j]);
                     }
                     $j++;
                }
           }
              //debug(sizeof($data));
              //die();
            // debug($data); die();
        $cat_name = "";
        $subject_model = new Subject();
        //if subject search did not yield any results, we show every tutor in the Catgory of Subject
        /** thinking about this. I really do not want to manipulate the resuts if user had performed a Subject search
         * and nothing turns up. Below will try to pull all those in Category.
         *
        if($subject_search && empty($data))  {
            //debug("test"); die();
               $cat_name = $subject_model->get_category_by_name($subject_1);
               //debug($cat_name); die();
               $conditions_tutor_subject = array();
               //$m_c = strtolower('TutorSubject.subject_category_name');
               $m_c = 'TutorSubject.subject_category_name';
               $conditions_tutor_subject[$m_c][] = $cat_name; //strtolower($cat_name);
              // debug($conditions_tutor_subject); die();
             $orderByStudentWatchList = array('StudentWatchList.created DESC');
           $data = $this->find('all', array(
					'conditions' => array(
						$conditions_tutor
						),
				        'contain' => array(
						   'TutorProfile'	=>	array(
        							'conditions' =>	array(
                                                    $conditions_tutor_profile
                                      ),
                                      'order' => $orderByTutorProfile
        							),
                           'TutorSubject'	=>	array(
                						'conditions' =>	array(
                								$conditions_tutor_subject
                							)
                						),
                            'StudentWatchList', // =>	array('order' => $orderByStudentWatchList),
        					'TutorPreference',
        					'TutorRating',
        					'TutorCategorie',
        					'TutorImage' =>	array(
        						'conditions'	=>	array(
        								$conditions_tutor_image
        							)
        						)
        					)
              ));

              if(!empty($data)){
                    $l=0;
                     foreach ($data as $key => $value) {
                         if(empty($data[$l]['TutorProfile']['id'])){
                               unset($data[$l]);
        			         }
                         $l++;
                      }
                      $l=0;
                      foreach ($data as $key => $value) {
                            $count = sizeof($value['TutorCategorie']);
                            foreach ($value['TutorCategorie'] as $key => $value1) {
                                //debug(sizeof($value['TutorCategorie'])); die();
                               // debug($value1['name']);
                                if(strtolower($value1['name']) != strtolower($cat_name)){
                                //if($value1['name'] != strtolower($cat_name)){
                                    $count--;
                                 }
                            }
                            if($count == 0) {
                                unset($data[$l]);
                            }
                            $l++;
                          }
               }
         }
         **/

          return $data;

    }
    /**
     * search City associated with user zip code
     * @param  string  $cur_zipcode 	the zip code user entered
     * @param  array   $params_query 	   	   	the form passed parameters (this used for the zipsearch)
     * @return array                       		return the city that matches teh zip code
     */
    function find_city_ByZipCode ($cur_zipcode) {

        $radiusSearch = new ZipSearch();
        $radiusSearch->set(array('zip_code' => $cur_zipcode));
        $serach_city = "";
        $result = array();
        if ($radiusSearch->validates(array('fieldList' => array('zip_code'))))
         {
						if(
			              !((!$rs = $radiusSearch->find(
			                        'first', array(
			                        'conditions' => array(
			                            'ZipSearch.zip_code' => $cur_zipcode))))
			                 || (count($rs) == 0))
			              )
			           { // if computed zip code from ibDbInfo is in zipsearch table
                       $result['city'] = $rs['ZipSearch']['city'];
                       $result['state'] = $rs['ZipSearch']['state_abbr'];
							//$serach_city = $rs['ZipSearch']['city']; //$radiusSearch->find_city_ByZipCode($cur_zipcode);
                            //debug($result); die();
						}
        }

        //debug($result); die();
        //return $serach_city;
        return $result;
     }

public function findJobSearchAgents($id=null) {
    $jobSearchAgent = new JobSearchAgent();
    $job_search_agents = null;
    //debug("here"); die();
    if(!empty($id)) {
          //debug($id); die();
       $job_search_agents = $jobSearchAgent->findSearchAgents($id);
    }
    return $job_search_agents;
}

public function findJobApplications($id=null, $conditions_for_search=array(), $kwd) {
    $jobApplication = new TutorJobApplication();
    $jobApplications = null;
    //debug("here"); die();
    if(!empty($id)) {
          //debug($id); die();
       $jobApplications = $jobApplication->findJobApplications($id, $conditions_for_search, $kwd);
    }
    return $jobApplications;
}
public function retreiveWatchList($id=null, $kwd=null) {
     $allTutors = array();
     $tutor_ids = array();

    // debug($id); die();
     $allTutors  = $this->StudentWatchList->retreiveWatchList($id);

     	if(!empty($allTutors)){
			foreach ($allTutors as $key => $value) {
				if(!empty($value['StudentWatchList']['tutor_id'])){
					$tutor_ids[] = $value['StudentWatchList']['tutor_id'];
				}
			}
		}

        $conditions_tutor = array();
		if(!empty($tutor_ids)){
			$conditions_tutor = array(
					'Tutor.id' => $tutor_ids
					//'Tutor.email_verified' => 1,
					//'Tutor.active' => 1,
					//'Tutor.profile_status' => 1
				);
		}

        $orderByStudentWatchList = array('StudentWatchList.created DESC');
        $watch_list_data = $this->find('all', array(
					'conditions' => array(
						$conditions_tutor
						),
				        'contain' => array(
                            'StudentWatchList' =>	array('order' => $orderByStudentWatchList),
        					'TutorProfile',
                            'TutorImage'
        					)
              ));



             //debug($watch_list_data); ///die();

         $i=0;
         $j=0;
         foreach ($watch_list_data as $key => $value) {
              // We do not want to show Tutor whose Profile is not ready
              //foreach ($value['StudentWatchList'] as $key1 => $value1) {
                foreach ($watch_list_data[$i]['StudentWatchList'] as $key1 => $value1) {
                    //debug(($value['StudentWatchList'][$j])); //die();
                    //debug($value1);
                    //debug($id);
                    //debug($value['StudentWatchList'][$j]['student_id']);
                    if(empty($watch_list_data[$i]['StudentWatchList'][$j])
                          || $watch_list_data[$i]['StudentWatchList'][$j]['student_id'] != $id) {

                        unset($watch_list_data[$i]['StudentWatchList'][$j]);
                        //unset($value['StudentWatchList'][$j]);
                        //debug($value['StudentWatchList'][$j]);
                    } else if (sizeof($watch_list_data[$i]['StudentWatchList']) == 0){
                         // debug($watch_list_data[$i]['StudentWatchList']); die();
                          unset($watch_list_data[$i]['StudentWatchList']);

                    }
                    $j++;
             }
             $i++;
             $j=0;
         }
         //die();
         //debug($watch_list_data); die();
         $watch_list_data = array_values($watch_list_data);
         //($watch_list_data); die();
         $i=0;
         //$a_key = array_keys($tutor['StudentWatchList']);
     if(!empty($watch_list_data) && sizeof($watch_list_data) > 0)  {
       if(strtolower($kwd) === strtolower("Oldest")) {
          usort($watch_list_data, function($a1, $a2) {
               if(!empty($a1['StudentWatchList']) && sizeof($a1['StudentWatchList']) > 0)  {
                if(!empty($a2['StudentWatchList']) && sizeof($a2['StudentWatchList']) > 0)  {
                   $a1_key = array_keys($a1['StudentWatchList']);
                   $a2_key = array_keys($a2['StudentWatchList']);
                   $v1 = strtotime($a1['StudentWatchList'][$a1_key[0]]['created']);
                   $v2 = strtotime($a2['StudentWatchList'][$a2_key[0]]['created']);

                   return  $v1 - $v2; //to reverse direction
               }
              }
            });
        } else {
            usort($watch_list_data, function($a1, $a2) {
                // debug($a1);
                // debug($a2); die();
            if(!empty($a1['StudentWatchList']) && sizeof($a1['StudentWatchList']) > 0)  {
                if(!empty($a2['StudentWatchList']) && sizeof($a2['StudentWatchList']) > 0)  {
                   $a1_key = array_keys($a1['StudentWatchList']);
                   $a2_key = array_keys($a2['StudentWatchList']);
                   $v1 = strtotime($a1['StudentWatchList'][$a1_key[0]]['created']);
                   $v2 = strtotime($a2['StudentWatchList'][$a2_key[0]]['created']);
                   return $v2 - $v1;
                   }
                }
             });


        }
       }
        return $watch_list_data;

  }

protected function conditionsForTutors($cur_zip_code, $cur_session_zipcode, $params_query) {

        //debug($cur_zip_code); 
       // debug($cur_session_zipcode); //die();
        
    	$radiusSearch = new ZipSearch();
		$radiusSearch->set(array('zip_code' => $cur_zip_code));
        $zip_result = null;
		
		/**if(!empty($params_query['location']) && 
		      $params_query['location'] == 'online') {
			if(!empty($params_query['distance'])) {
		       unset($params_query['distance']);
			}
		} else **/
			
		if(empty($params_query['distance']) && 
		        $params_query['distance'] === "") {
                     $params_query['distance'] = 40;
        }
		
       // debug($params_query); die();
        
		if ($radiusSearch->validates(array('fieldList' => array('zip_code'))))
        { 
                 //debug($cur_zip_code); die();
			if(
              !((!$rs = $radiusSearch->find(
                        'first', array(
                        'conditions' => array(
                            'ZipSearch.zip_code' => $cur_zip_code))))
                 || (count($rs) == 0))
              )
              {
                // if inputed zip code is in zipsearch table
                // debug($params_query); die();
				
                
				$zip_result = $radiusSearch->search($params_query, $cur_zip_code);
                 $zip_code = $cur_zip_code;

			  }
		}

        //debug($zip_result); die();
		if(empty($zip_result)){
			 // debug("HHH"); die();
		   //The user provided Zip code was NOT found on the zip_searches table
		  // if user's input Zip code fails to yield result, then we try the API determined Zip'
		  //debug($cur_session_zipcode); 
			$zip_result = $radiusSearch->search($params_query, $cur_session_zipcode);
            $zip_code = $cur_session_zipcode;
			
			//debug($zip_code);
		}
         $zip_codes = null;
		if(!empty($zip_result)){
			foreach ($zip_result as $key => $value) {
				if(!empty($value['ZipSearch']['zip_code'])){
					$zip_codes[] = $value['ZipSearch']['zip_code'];
				}
			}
		}

		 // debug($zip_codes); die();
        //debug($conditions_tutor); die();
        //array('conditions' => array('LOWER(User.first_name)' => strtolower('John') ))
		$data = array();
		$return = array();
		$conditions_tutor = array();
	    if(!empty($params_query['location']) && 
		   $params_query['location'] == self::ONLINE ) {
		//No need to be bound by geographical location when tutoring online
		 $conditions_tutor = array(
					'Tutor.email_verified' => 1,
					'Tutor.active' => 1,
					'Tutor.profile_status' => 1
				);
		
	    }else if(!empty($zip_codes) && 
		         sizeof($zip_codes) > 0 ){
		    //debug("test"); die();
			$conditions_tutor = array(
					'Tutor.zip_code' => $zip_codes,
					'Tutor.email_verified' => 1,
					'Tutor.active' => 1,
					'Tutor.profile_status' => 1,
                     //'Tutor.profile' => $params_query['bg_checked']
				);
		} else if(empty($zip_codes)) { //if zip code provided was NOT found on zip code table.

		    $conditions_tutor = array(
					'Tutor.email_verified' => 1,
					'Tutor.active' => 1,
					'Tutor.profile_status' => 1
				);
        }
	
       // debug($conditions_tutor); die();
          return $conditions_tutor;
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

}
