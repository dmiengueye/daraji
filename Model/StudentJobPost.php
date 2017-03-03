<?php

/*
	* StudentJobPost.php
	* 
	*
	* PHP version 5.3.13
	* @filesource
	* @package    
	* @author  Donald Guy   
	* @link       http://
	* @link       http://
	* @copyright  Copyright ? 2010
	* @version 0.0.1 
	*   - Initial release
	*/
	
App::uses('StudentJobDetail', 'Model');
	
class StudentJobPost extends AppModel
{
	   
 /**
 * Name
 *
 * @var string
 */
 
  public  $name = 'StudentJobPost';
  public $recursive = 1;
        
  public $belongsTo = array(
        'Student'=> array(
            'className' => 'Student',
            'foreignKey' => 'student_id',
            //'conditions' => array('StudentSearchAgent.active' => '1'),
            'dependent' => true
            ),
         );
		 
		/** public $hasMany= array (	                          
                                    'StudentJobDetail' => array(
							   				  'className' => 'StudentJobDetail',
											  'foreignKey' => false,
											  //’finderQuery’	=> ’SELECT * FROM student_job_details AS jobdetails WHERE jobdetails.job_id="sex"’
											  //’finderQuery’	=> ’SELECT Option.* FROM pulldowns, options AS Option WHERE pulldowns.id={$__cakeID__$} AND pulldowns.use_set=Option.set’
                                              //'finderQuery'	=> 'SELECT StudentJobDetail.* FROM student_job_posts, student_job_details AS StudentJobDetail WHERE StudentJobDetail.student_id={$__cakeID__$} AND student_job_posts.job_id=StudentJobDetail.job_id AND student_job_posts.student_job_post_id=StudentJobDetail.student_job_post_id',											  						   				  
											   'dependent' => true
							                ),
	                            );
         
         **/
		
	function __construct() {
		$this->validate = array(
        
            'job_description' => array(   //require a min length for Job Post
			   		  'required' => array(
			   		  'rule' => array('notEmpty'),
		   		       'required' => true, 'allowEmpty' => false,
					   'message' => 'Job Descrption is required.'),
                       
                       'job_description_min' => array(
			   						      'rule' => array('minLength', '15'),
			   						      'message' => 'To help our Instructors better understand your needs, your Job Descrption must be at least 15 character long.')),
                       
			'job_title' => array(   //require a min length for Job Post
			   		  'required' => array(
			   		  'rule' => array('notEmpty'),
		   		       'required' => true, 'allowEmpty' => false,
					   'message' => 'Job title is required.'),
                       
                       'job_title_min' => array(
			   						      'rule' => array('minLength', '5'),
			   						      'message' => 'The title must be at least 5 character long.'),
                                             
                                             
                         'job_title_max' => array(
			   						      'rule' => array('maxLength', '500'),
			   						      'message' => 'The title must be a Maximum of 500 characters long.')),
                                                           
                       
                       
            
           'job_category' => array(
				 		  'notEmpty' => array(
				          'rule' => array('categoryValidation'), //array('notEmpty'), //array('educationValidation'),
                          'allowEmpty' => false,
				 		  'message' => 'Please Select a Category from List.')),
                       
           'job_category_id' => array(   
			   		  'required' => array(
			   		  'rule' => array('notEmpty'),
		   		       'required' => true, 'allowEmpty' => false,
					   'message' => 'Job Category Id is required.')),
                       
            
              'job_subject' => array(
				 		  'notEmpty' => array(
				          'rule' => array('subjectValidation'), //array('notEmpty'), //array('educationValidation'),
                          'allowEmpty' => false,
				 		  'message' => 'Please Select a Subject from List.')),
                       
                       
             'job_subject_id' => array(   
			   		  'required' => array(
			   		  'rule' => array('notEmpty'),
		   		       'required' => true, 'allowEmpty' => false,
					   'message' => 'Job Subject Id is required.'))
                       
           
		
		);
		parent::__construct();
		}
   
public function categoryValidation($data) {
    if($data['job_category'] != '0' && $data['job_category'] != '') {
        return true;
    }
} 
   
public function subjectValidation($data) {

     if($data['job_subject'] != '0' && $data['job_subject'] != '') {
        return true;
     }
} 
public function retreiveMyJobPosts($id=null) {
     $allPosts = array();
     $allPosts  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentJobPost.student_id' => $id)
                               ));
                               
        return $allPosts;
      
} 

public function getStudentJobPostDetails($job_id=null) {
     $job_post_details = array();
     $job_post_details  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentJobPost.job_id' => $job_id)
                               ));
                               
        return $job_post_details;
      
}

public function getJobPosts($id=null, $search_cnditions, $kwd) {
    
      //$job_posts = array();
      $my_job_posts = array();
      $subject = "";
      $subject_id = "";
      
     //  debug($id); die();
     if(!empty($search_cnditions)) {
        $subject = $search_cnditions['subject'];
        $subject_id = $search_cnditions['subject_id'];
     }
     
    // debug($subject); //die();
    // debug($subject_id); die();
	
	
  if(!empty($subject) && $subject != 'All'  && $subject != '100'){
     $job_posts  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentJobPost.student_id' => $id,
                                    'StudentJobPost.job_subject' => $subject),
                                    //'StudentJobPost.job_subject_id' => $subject_id),
                                    //'order' => $orderByApplicationDate
									
						'contain' => 'StudentJobDetail'	                                
									
                                )
							);
    } else {
          //debug("Here"); die();
         $job_posts  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentJobPost.student_id' => $id)
                                    //'order' => $orderByApplicationDate
                               ));
        
    }   
    
     //debug($job_posts); die();
    if(!empty($job_posts)) {                      
        foreach($job_posts as $key => $value){
            if(!empty($value['StudentJobPost']['job_id'])){
					$my_job_posts[] = $value['StudentJobPost'];
		    }
        }
    }
      
    // debug($my_job_posts); die();
  if(!empty($my_job_posts) && sizeof($my_job_posts) > 0) {
      if(strtolower($kwd) === strtolower("Oldest")) {
          usort($my_job_posts, function($a1, $a2) {
               $v1 = strtotime($a1['post_date']);
               $v2 = strtotime($a2['post_date']);
               return $v1 - $v2; // $v2 - $v1 to reverse direction
            });
    } else {
        usort($my_job_posts, function($a1, $a2) {
               $v1 = strtotime($a1['post_date']);
               $v2 = strtotime($a2['post_date']);
               return $v2 - $v1; 
            });
        
       }
    }    
    
    //debug($my_job_posts); die();               
     return $my_job_posts;  
  }  
public function saveJobPost($id, $postData = array()) {

        // debug($postData); die();
         if(!empty($id)) {
           $postData['StudentJobPost']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
        }
           //debug($postData); die();
           if($this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false))) {
  		 				   
                           return true;
               } else  {
               // debug("here"); die();
               return false;
               }
   }
    
}

?>