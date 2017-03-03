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
	
	
class StudentWatchList extends AppModel
{
	   
 /**
 * Name
 *
 * @var string
 */
 
  public  $name = 'StudentWatchList';
        
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
			'tutor_id' => array(
			   		  'required' => array(
			   		  'rule' => array('notEmpty'),
		   		       'required' => true, 'allowEmpty' => false,
					   'message' => 'Tutor is required.')),
                       
           
		
		);
		parent::__construct();
		}
    
  public function retreiveWatchList($id=null) {
     $allTutors = array();
     $tutor_ids = array();
	 
    // debug($id); die();
     $allTutors  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentWatchList.student_id' => $id)
                               ));
                               
        return $allTutors;
      
  } 
  public function saveTutorProfile($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['StudentWatchList']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
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