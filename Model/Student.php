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
App::uses('Tutor', 'Model');
App::uses('Hash', 'Utility');
App::uses('ZipSearch', 'Model');
App::uses('Subject', 'Model');
App::uses('TutorSubject', 'Model');
App::uses('Validation', 'Utility');
App::uses('StudentSearchAgent', 'Model');
App::uses('StudentWatchList', 'Model');
App::uses('StudentJobPost', 'Model');
App::uses('StudentJobDetail', 'Model');
App::uses('TutorJobApplication', 'Model');
class Student extends User {
	 
    const ONLINE = 'online';
   	const SUBJECT_ID_100 = '100';
	const SUBJECT_ID_200 = '200';
	const SUBJECT_ID_300 = '300';


public $name = 'Student';
public $recursive = 3;

   //A Student has one Profile (details) and One Set of Preferences
     public $hasOne = array (
	                           'StudentPreference' => array(
							   				  'className' => 'StudentPreference',
							   				  'foreignKey' => 'student_id',
							   				 // 'conditions' => array('Profile.published' => '1'),
							   				  'dependent' => true
							                ),

							    'StudentProfile' => array(
							   	             'className' => 'StudentProfile',
							   	             'foreignKey' => 'student_id',
							   	           //'conditions' => array('Profile.published' => '1'),
							   	             'dependent' => true
	                                 )
	                             );


 public $hasMany = array (
	                           'StudentSearchAgent' => array(
							   				  'className' => 'StudentSearchAgent',
							   				  'foreignKey' => 'student_id',
							   				 // 'conditions' => array('StudentSearchAgent.agent_name' => '1'),
							   				  'dependent' => true
							                ),

                                   'StudentWatchList' => array(
							   				  'className' => 'StudentWatchList',
							   				  'foreignKey' => 'student_id',
							   				 // 'conditions' => array('StudentSearchAgent.agent_name' => '1'),
							   				  'dependent' => true
							                ),

                                    'StudentJobPost' => array(
							   				  'className' => 'StudentJobPost',
							   				  'foreignKey' => 'student_id',
							   				 // 'conditions' => array('StudentSearchAgent.agent_name' => '1'),
							   				  'dependent' => true
							                ),																		
	                              );

public $hasAndBelongsToMany = array(
        'Tutor' =>
            array(
                'className' => 'Tutor',
                'joinTable' => 'students_tutors',
                'foreignKey' => 'student_id',
                'associationForeignKey' => 'tutor_id',
                'unique' => 'keepExisting' , //true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => ''
               // 'with' => ''
            ),

       /**
        'Tutor' =>
            array(
                'className' => 'Tutor',
                'joinTable' => 'tutors_connections',
                'foreignKey' => 'student_id',
                'associationForeignKey' => 'tutor_id',
                'unique' => 'keepExisting' , //true,
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
 * Validation parametersTutor
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

		         // 'confirm_email' => array(
				 		//	'rule' => 'confirmEmail',
				//	'message' => 'The email must match.'),

				 'password' => array(
					'too_short' => array(
						'rule' => array('minLength', '6'),
						'message' => 'Your password must be at least 6 characters.'),

					'required' => array(
						'rule' => 'notEmpty',
						'message' => 'Password is required.')),

				//'confirm_password' => array(
				//	'rule' => 'confirmPassword',
					//'message' => 'The passwords must match.'),


			  'zip_code' => array(
			        'rule' => array('postal', null, 'us'),
			        'message' => 'A valid U.S zip code is required.'),


			     'referal' => array(
				      //'notEmpty' => array(
				 		'rule' => 'notEmpty',
				 		'message' => 'Please provide source type'
				 		//)
				 	),

				 'contactbox' => array(
				 		//'notEmpty' => array(
				 		'rule' => array('contactBoxValidation', 'referal'),
				 		'required' => true,
				 		'message' => 'Please provide source',
				 		//'allowEmpty' => false
				 		//)
				 	),

				 	'tos' => array(
							  'rule' => array('custom','[1]'),
					          'message' => 'You must agree to the terms of use.')

				); //end validates array

       /**
	     * Before isUniqueEmail
	     * @param array $options
	     * @return boolean
	     */



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
				
				debug("truess"); die();
  			return true;
  		}
		debug("falsee"); die();
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

function isUnique($check, $email=true) {
    $tutor = new Tutor();
    $user = null;
    $user2 = null;
	$user = $this->findByEmail($this->data[$this->alias]['email']);
	//$this->loadModel('Tutor');
	$user2  = $tutor->findByEmail($this->data[$this->alias]['email']);

//debug($user);
//debug($user2);
//die();
	if( $user != null && !empty($user) ){
		return false;
	} else if($user2 != null && !empty($user2) ){
	   return false;
	}

	return true;
}

public function contactBoxValidation($check, $referrer)
	{

	     //debug('hello');
	   //  if($this->data[$this->alias]['referal'] == '1' ) {
	          //  return Validation::notEmpty(current($check));
	    // }
	     if(
	        (Hash::get($this->data[$this->alias], $referrer) === '1') ||
	        (Hash::get($this->data[$this->alias], $referrer) === '2')
	       )
		         {
		             return Validation::notEmpty(current($check));
	             }

	      return true;
	 }

	 public function test(){
		echo 'ok';
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
	function find_by_params(
                            $id=null,
                            $kwd = "",
                            $search_cnditions,
                			$cur_zip_code,
                			$cur_session_zipcode,
                			$params_query
		                 )
	{
        //debug($search_cnditions); die();
        $category = $search_cnditions['category'];
	    $subject_1 = $search_cnditions['subject'];
        $category_id = $search_cnditions['category_id'];
        $subject_id = $search_cnditions['subject_id'];
        //debug('subject Id = '.$subject_id);
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

		 $radiusSearch = new ZipSearch();
         $tutor_subject_model = new TutorSubject();
         $tutor_subjects = array();
         $tutor_job_ids = array();

         $subject_model = new Subject();

         //Need to get the job_ids for jobs tutor has apllied for
         //and compare with job_ids in current job search
         //The jobs applied for are removed from search results


         //debug($tutor_job_ids); die();
         //get the all subjects for this tutor
          //$tutor_subjects = $tutor_subject_model->get_all_subjects_for_tutor($id);
          if(!empty($id)) {
              $tutor_subjects = $tutor_subject_model->get_tutor_subjects_ById($id);
          }
         //debug($tutor_subjects); die();
         //have to loop to pull the subjects from array above before i can do below
        /**
        $conditions_student = $this->conditionsForStudents($cur_zip_code, $cur_session_zipcode, $params_query);
        debug($results); die();
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
               // debug($zip_result); die();
			  }
		}
		if(empty($zip_result)){
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

         $data = array();
		$return = array();

        $conditions_student = array();


		if(!empty($zip_codes)){
			$conditions_student = array(
					'Student.zip_code' => $zip_codes,
					'Student.email_verified' => 1,
					'Student.active' => 1
				);
		  } else if(empty($zip_code) || $zip_code === "") {

			  $conditions_tutor = array(
						'Student.email_verified' => 1,
					    'Student.active' => 1,
					    //'Student.active' => 1
					);

			} else {
		      return $data = array();

		  }
        **/
        $data = array();
		$return = array();


        $conditions_student = $this->conditionsForStudents($cur_zip_code, $cur_session_zipcode, $params_query);

        if(empty($conditions_student)  || sizeof($conditions_student) <= 0){
                 return $data = array();
          }
          //else { //if no tutors were found in the zip code bag, return
			    // return $data = array();
          //}





		// end $conditions_student_jobs

			$is_subject_1_search = false;
			$subject_1_category = "";
            $today = date('Y-m-d');
			// tutor Search conditions
           // $conditions_student_jobs = array();


			//$category_modal = new Categorie();
            //$subject_1 = 'My Subjects';
            //debug($subject_1); die();
            //debug($conditions_student_jobs); die();
            $conditions_student_jobs = array();

            $conditions_student_jobs['StudentJobPost.verified'] = '1';
            $conditions_student_jobs['StudentJobPost.closed'] = '0';
            $conditions_student_jobs['StudentJobPost.exp_date >='] = $today;

           if(!empty($id) && !empty($subject_id)) { //$job_ids
                if($subject_id === '100')  { //User is asking for all his/her Subjects (All My Subjects)
    		         $conditions_student_jobs['StudentJobPost.job_subject'] = $tutor_subjects;
                     //$conditions_student_jobs['StudentJobPost.job_id'] != $tutor_job_ids;

                      $data = $this->search_conditions($id,
                                 "",
                                 "",
                                 $conditions_student,
                                 $conditions_student_jobs,
                                 $kwd
                               ) ;
                     //debug($data); die();
                }  else if(in_array($subject_id, array(self::SUBJECT_ID_200, self::SUBJECT_ID_300))) { //Related / All Subjects offered by Daraji

                      $data = $this->search_conditions($id,
                                 "",
                                 "",
                                 $conditions_student,
                                 $conditions_student_jobs,
                                 $kwd
                               ) ;

                               // debug($data); die();

                 } else {  //User is being very specific in his search.
                     //debug($subject_1);
                     $subject_id =  $subject_model->get_subject_id($subject_1);
                    if(!empty($subject_id)) {
                         $conditions_student_jobs['StudentJobPost.job_subject'] = $subject_1;
                         $conditions_student_jobs['StudentJobPost.job_subject_id'] = $subject_id;//pul this from Subj table
                         //$conditions_student_jobs['StudentJobPost.job_id'] != $tutor_job_ids;
                      }
                      $data = $this->search_conditions($id,
                                 "",
                                 "",
                                 $conditions_student,
                                 $conditions_student_jobs,
                                 $kwd
                               ) ;
                 }
              } else if(empty($id) && !empty($subject_id)) { //user is not be logged/Authenticated in
                    // debug($subject_id);
                       if(in_array($subject_id, array(self::SUBJECT_ID_100, self::SUBJECT_ID_200, self::SUBJECT_ID_300)) &&
                           (in_array($category_id, array('400', 'AllCategories')))) {
                        //get All Jobs for all Subjects in all Categories
                       //  debug("here test 111"); die();
                      // $conditions_student_jobs['StudentJobPost.job_id'] != $tutor_job_ids;
                         $data = $this->search_conditions($id,
                                     "",
                                     "",
                                     $conditions_student,
                                     $conditions_student_jobs,
                                     $kwd
                                   ) ;
                     } else if(in_array($subject_id, array(self::SUBJECT_ID_100, self::SUBJECT_ID_200, self::SUBJECT_ID_300)) &&
                               !(in_array($category_id, array('400', 'AllCategories')))) {
                          // $category_id != '400' && $category_id != 'AllCategories') { //Related / All Subjects offered by Daraji
                           //Get all jobs in all Subjects in this Category Id
                             //debug("here test 333 "); die();
                              $conditions_student_jobs['StudentJobPost.job_category_id'] = $category_id;//pul this from Subj table
                              //$conditions_student_jobs['StudentJobPost.job_id'] != $tutor_job_ids;
                              $data = $this->search_conditions($id,
                                     "",
                                     "",
                                     $conditions_student,
                                     $conditions_student_jobs,
                                     $kwd
                                   ) ;
                     } else  if(!in_array($subject_id, array(self::SUBJECT_ID_100, self::SUBJECT_ID_200, self::SUBJECT_ID_300)) &&
                               !(in_array($category_id, array('400', 'AllCategories')))) {
                          // $category_id != '400' && $category_id != 'AllCategories') { //Related / All Subjects offered by Daraji
                           //Get all jobs in all Subjects in this Category Id
                          // debug($category_id);
                          $subject_id =  $subject_model->get_subject_id($subject_1);
                                if(!empty($subject_id)) {
                                       //debug($subject_id); die();
                                     $conditions_student_jobs['StudentJobPost.job_subject'] = $subject_1;
                                     $conditions_student_jobs['StudentJobPost.job_subject_id'] = $subject_id;//pul this from Subj table
                                      $conditions_student_jobs['StudentJobPost.job_category_id'] = $category_id;  //pul this from Subj table
                                     //$conditions_student_jobs['StudentJobPost.job_id'] != $tutor_job_ids;
                                  }


                            //debug($conditions_student_jobs); die();
                            $data = $this->search_conditions($id,
                                     "",
                                     "",
                                     $conditions_student,
                                     $conditions_student_jobs,
                                     $kwd
                                   ) ;

                     } else if(
                               (in_array($category_id, array('400', 'AllCategories')))
                                &&
                               !in_array($subject_id, array(self::SUBJECT_ID_100, self::SUBJECT_ID_200, self::SUBJECT_ID_300))
                     ) {
                          // $category_id != '400' && $category_id != 'AllCategories') { //Related / All Subjects offered by Daraji
                           //Get all jobs in all Subjects in this Category Id
                             //debug("here test 5555 "); die();
                                $subject_id =  $subject_model->get_subject_id($subject_1);
                                if(!empty($subject_id)) {
                                     $conditions_student_jobs['StudentJobPost.job_subject'] = $subject_1;
                                     $conditions_student_jobs['StudentJobPost.job_subject_id'] = $subject_id;//pul this from Subj table
                                    // $conditions_student_jobs['StudentJobPost.job_id'] != $tutor_job_ids;
                                  }
                              // $conditions_student_jobs['StudentJobPost.job_category_id'] = $category_id;//pul this from Subj table

                            //debug($conditions_student_jobs); die();
                            $data = $this->search_conditions($id,
                                     "",
                                     "",
                                     $conditions_student,
                                     $conditions_student_jobs,
                                     $kwd
                                   ) ;

                     }
              }
            // debug($conditions_student_jobs); die();


    if(!empty($data)) {
        $data = array_values($data);
    }
    //debug($data); die();
    return $data;
}
function sortbyDateDESC( $a, $b ) {
    return strtotime($a["post_date"]) - strtotime($b["post_date"]);
}
function search_conditions(      $id=null,
                                 $subject_1 = "",
                                 $subject_1_category = "",
                                 $conditions_student = array(),
                                 $condition_student_jobs = array(),
                                 $kwd = ""
                               )
        {

    $orderByStudentJobPostDate  = "";
     $tutor_job_app_model  = new TutorJobApplication();
     $tutor_job_ids = array();

    // debug($kwd); die();
    if($kwd != "") {
        //debug($kwd); die();
        //if( (strtolower($kwd) === strtolower("Most Recent")) || (strtolower($kwd) === strtolower("Best Match"))) {
           //  $orderByStudentJobPostDate = array('StudentJobPost.post_date DESC');
        //} else

        if(strtolower($kwd) === strtolower("Oldest")) {
             $orderByStudentJobPostDate = array('StudentJobPost.post_date ASC');
        }

     } else {
         $orderByStudentJobPostDate = array('StudentJobPost.post_date DESC');
     }

     /**
      *  Need to exclude the jobs tutor had already applied for.
         To do that, we need to get all the job_ids for which tutor submitted an app
         And compare against the existing job_ids in from the pool of posted jobs by students
         Exclude the jobs that match from the search results.
         Search Results must ONLY bring back Jobs Tutor has NOT applied for.
     **/

      $tutor_job_apps = $tutor_job_app_model->findJobApplications($id, $conditions_for_search=array(), "");
         //debug($tutor_job_apps); die();
         if(!empty($tutor_job_apps)) {
             foreach($tutor_job_apps as $key => $value ) {
                if(!empty($value['job_id'])){
    					$tutor_job_ids[] =$value['job_id'];
    				}
               }
         }
     //debug($condition_student_jobs);
       //$orderByStudentWatchList = array('StudentWatchList.created DESC');
	     // $conditions_job_detail = array();
		// $conditions_job_detail['StudentJobDetail.student_id'][] = '{$__cakeID__$}';
		//  $conditions_job_detail['StudentJobDetail.job_id'][] = 'GA08721089';
		  //$conditions_job_details = implode("", $conditions_job_detail);
		  $student_job_details_model = new StudentJobDetail();
		  $today = date('Y-m-d H:i:s');
		  $conditionss['StudentJobPost.post_exp >= '] = $today;
		  $conditionss['StudentJobPost.verified'] = '1';
		  $conditionss['StudentJobPost.closed'] = '0';
		  
		  //$conditions = array('StudentJobPost.post_exp <=' => $today, 'StudentJobPost.verified' => '1', 'StudentJobPost.closed' => '0');
		 $job_ids =  $this->StudentJobPost->find('all', array('conditions' => $condition_student_jobs));
		  //debug($job_ids); die();
		 // $job_ids = implode("", $job_ids);
		 // debug($job_ids); die();
	    $conditions_job_detail = array(
					'StudentJobDetail.job_id' =>  $job_ids,
					//'StudentJobDetail.student_id' => 'StudentJobPost.student_id',
					//'finderQuery'	=> 'SELECT StudentJobDetail.* FROM student_job_posts, student_job_details AS StudentJobDetail WHERE student_job_posts.job_id=StudentJobDetail.job_id AND student_job_posts.student_job_post_id=StudentJobDetail.student_job_post_id',											  
							   				  
					
				);
				
				
		
	// debug($conditions_job_detail); //die();
     if(!empty($tutor_job_ids) && sizeof($tutor_job_ids) > 0) {
		 
        $data = $this->find('all', array(
					'conditions' => array(
						$conditions_student
						),
				        'contain' => array(
						   'StudentJobPost'	=>	array(
        							'conditions' =>	array(
                                                    $condition_student_jobs,
                                                    'NOT' => array('StudentJobPost.job_id' => $tutor_job_ids)
                                      ),
									  //'StudentJobDetail',
                                     // 'order' => $orderByStudentJobPostDate
        							),
                                  )
				           ));
         } else {
            $data = $this->find('all', array(
					'conditions' => array(
						$conditions_student
						),
				        'contain' => array(
						   'StudentJobPost'	=>	array(
        							'conditions' =>	array(
                                                    $condition_student_jobs,
													
                                      ),
									 /**'StudentJobDetail' =>	array(
									        'conditions' =>	$conditions_job_detail,
											//array(
													 // $conditions_job_detail
													 // ), 									 									 
									         )**/									                                                
        							),
									
                                  )
				           ));
         }

         //debug($data) ; die();
         $studentPosts = array();
         foreach ($data as $key => $value) {
				if(!empty($value['StudentJobPost'])){
					$studentPosts[] = $value['StudentJobPost'];
				}
          }

          // debug($studentPosts); die();
          $i=0;
          $studentJobPosts = array();
          foreach ($studentPosts as $key => $value) {
           // debug($value); die();
            //$j=0;
            $myKey = array_keys($value);
            //debug($myKey); die();
            for($j=0;$j<sizeof($myKey); $j++) {
				if(!empty($value[$j])){
					$studentJobPosts[] = $value[$j];
                    //$j++;
				}
              }
          }

            $data = $studentJobPosts;

        //  debug($data); die();

          //usort($data, 'sortbyDateDESC');
if(!empty($data) && sizeof($data) > 0) {
    if(strtolower($kwd) === strtolower("Oldest")) {
          usort($data, function($a1, $a2) {
               $v1 = strtotime($a1['post_date']);
               $v2 = strtotime($a2['post_date']);
               return $v1 - $v2; // $v2 - $v1 to reverse direction
            });
    } else {
        usort($data, function($a1, $a2) {
               $v1 = strtotime($a1['post_date']);
               $v2 = strtotime($a2['post_date']);
               return $v2 - $v1;
            });
       }
   }
           //debug($data); die();
		  
		  /**$newData = array();
		  foreach ($data as $key => $value) {
			  debug($value); // die();
			 // debug($value['StudentJobDetail']['job_id']);
			 // die();
			  if($value['job_id'] != $value['StudentJobDetail']['job_id']) {
				  unset($value['StudentJobDetail']);
				  $newData[] = $value;
			  } else {
				  $newData[] = $value;
			  }
		  }
		 **/ 
		   
          return $data;

}




public function findSearchAgents($id=null) {
    $studentSearchAgent = new StudentSearchAgent();
    $search_agents = null;
    //debug("here"); die();
    if(!empty($id)) {
          //debug($id); die();
       $search_agents = $studentSearchAgent->findSearchAgents($id);
    }
    return $search_agents;
}

public function retreiveWatchList($id=null) {
    $studentWatchList = new StudentWatchList();
    $watch_list = null;
    //debug("here"); die();
    if(!empty($id)) {
          //debug($id); die();
       $watch_list = $studentWatchList->retreiveWatchList($id);
    }

    // debug($watch_list); die();
    return $watch_list;
 }

public function find_job_by_id($job_id=null) {

    $studentJobPost = new StudentJobPost();
    $job_post = null;
    if(!empty($job_id)) {
       $job_post = $studentJobPost->getStudentJobPostDetails($job_id);
    }
    $data= array();
    //debug($job_post);
    foreach ($job_post as $key => $value) {
       if(!empty($value['StudentJobPost'])){
            $data[] = $value['StudentJobPost'];
        }
     }

    //debug($data); die();
    return $data;
 }

 public function findJobPosts($id=null, $conditions_for_search=array(), $kwd) {
    $jobPost = new StudentJobPost();
    $jobPosts = null;
    //debug("here"); die();
    if(!empty($id)) {
          //debug($id); die();
       $jobPosts = $jobPost->getJobPosts($id, $conditions_for_search, $kwd);
    }
    return $jobPosts;
 }

 protected function conditionsForStudents($cur_zip_code, $cur_session_zipcode, $params_query) {

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
                // debug($params_query); die();
                 if(empty($params_query['distance']) || $params_query['distance'] === "") {
                     $params_query['distance'] = 40;
                 }
				$zip_result = $radiusSearch->search($params_query, $cur_zip_code);
                 $zip_code = $cur_zip_code;

			  }
		}

        // debug($zip_result); die();
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

        //debug($conditions_student); die();
        //array('conditions' => array('LOWER(User.first_name)' => strtolower('John') ))
		
		$data = array();
		$return = array();
		$conditions_student = array();
		if(!empty($params_query['location']) && 
		   $params_query['location'] == self::ONLINE ) {
		//No need to be bound by geographical location when tutoring online
		 $conditions_student = array(
					'Student.email_verified' => 1,
					'Student.active' => 1,
					//'Student.profile_status' => 1
				);
		
	    }else if(!empty($zip_codes) && sizeof($zip_codes) > 0){
		    //debug("test"); die();
			$conditions_student = array(
					'Student.zip_code' => $zip_codes,
					'Student.email_verified' => 1,
					'Student.active' => 1
				);
		} else if(empty($zip_codes)) { //|| $zip_code === "") {

		    $conditions_student = array(
					'Student.email_verified' => 1,
					'Student.active' => 1
				);
        }
      // debug($conditions_student); die();
        return $conditions_student;
  }

}