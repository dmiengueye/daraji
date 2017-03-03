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
	
	
class StudentJobDetail extends AppModel
{
	   
 /**
 * Name
 *
 * @var string
 */
 
  public  $name = 'StudentJobDetail';
        
  /**public $belongsTo = array(
        'StudentJobPost'=> array(
            'className' => 'StudentJobPost',
            'foreignKey' => false, //'student_job_post_id',
            //'conditions' => array('StudentSearchAgent.active' => '1'),
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
			   						      'message' => 'To help our Instructors better understand your needs, your Job Descrption must be at least 15 character long.'))                      			                            
		
		);
		parent::__construct();
		}
   


public function getStudentJobPostDetails($job_id=null) {
     $job_post_details = array();
     $job_post_details  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentJobDetail.job_id' => $job_id)
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
                               ));
    } else {
          //debug("Here"); die();
         $job_posts  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentJobPost.student_id' => $id)
                                    //'order' => $orderByApplicationDate
                               ));
        
    }   
    
    // debug($job_posts); die();
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

       //debug($postData); die();
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