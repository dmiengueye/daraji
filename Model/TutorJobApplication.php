<?php

/*
	* StudentSearchAgent.php
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
	
	
class TutorJobApplication extends AppModel
{
	   
 /**
 * Name
 *
 * @var string
 */
 
  public  $name = 'TutorJobApplication';
        
  public $belongsTo = array(
        'Student'=> array(
            'className' => 'Student',
            'foreignKey' => 'student_id',
            //'conditions' => array('StudentSearchAgent.active' => '1'),
            'dependent' => true
            ),
         'Tutor'=> array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id',
            //'conditions' => array('StudentSearchAgent.active' => '1'),
            'dependent' => true  
            ));
		
	function __construct() {
		$this->validate = array(
            'personal_message' => array(
					'required' => array(
						'rule' => array('notEmpty'),
						'required' => true, 'allowEmpty' => false,
						'message' => 'Personal message must be at least 100 characters.'),
				
					'personal_message_min' => array(
						'rule' => array('minLength', '100'),
				        'message' => 'The message must have at least 10 characters.')
                    )         
		    );
		parent::__construct();
}   
public function findJobApplications($id=null, $search_cnditions, $kwd) {
    
      $job_apps = array();
      $my_job_apps = array();
      $subject = "";
      $subject_id = "";
      
     if(!empty($search_cnditions)) {
        $subject = $search_cnditions['subject'];
        $subject_id = $search_cnditions['subject_id'];
     }
     $orderByApplicationDate  = "";
     //debug($kwd); die();
    if($kwd != "") {
        if(strtolower($kwd) === strtolower("Oldest")) {
             $orderByApplicationDate = array('TutorJobApplication.application_date ASC'); 
        } 
            
     } else {
        
         $orderByApplicationDate = array('TutorJobApplication.application_date DESC');
     }
	 
    if(!empty($subject) && $subject != 'All'){
     $job_apps  = $this->find('all', 
                                    array('conditions' => array(
                                    'TutorJobApplication.tutor_id' => $id,
                                    'TutorJobApplication.job_subject' => $subject),
                                    //'order' => $orderByApplicationDate
                               ));
    } else {
         $job_apps  = $this->find('all', 
                                    array('conditions' => array(
                                    'TutorJobApplication.tutor_id' => $id),
                                    //'order' => $orderByApplicationDate
                               ));
        
    }   
    
    if(!empty($job_apps)) {                      
        foreach($job_apps as $key => $value){
            if(!empty($value['TutorJobApplication']['job_id'])){
					$my_job_apps[] = $value['TutorJobApplication'];
		    }
        }
     }
      
      //debug($my_job_apps); die();
  if(!empty($my_job_apps) && sizeof($my_job_apps) > 0) {
      if(strtolower($kwd) === strtolower("Oldest")) {
          usort($my_job_apps, function($a1, $a2) {
               //debug($a1); debug($a2); die();
               $v1 = strtotime($a1['application_date']);
               $v2 = strtotime($a2['application_date']);
               return $v1 - $v2; // $v2 - $v1 to reverse direction
            });
    } else {
        usort($my_job_apps, function($a1, $a2) {
               $v1 = strtotime($a1['application_date']);
               $v2 = strtotime($a2['application_date']);
               return $v2 - $v1; 
            });
        
       }
    }                   
     return $my_job_apps;  
  } 

public function getJobAppDetails($tutor_id, $job_id=null) {
     $job_app_details = array();
     $job_app_details  = $this->find('all', 
                                    array('conditions' => array(
                                    'TutorJobApplication.tutor_id' => $tutor_id,
                                    'TutorJobApplication.job_id' => $job_id)
                               ));
                               
        return $job_app_details;
      
}
  
public function saveJobApplication($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['TutorJobApplication']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
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