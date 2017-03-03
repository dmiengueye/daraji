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
	
	
class StudentSearchAgent extends AppModel
{
	   
 /**
 * Name
 *
 * @var string
 */
 
  public  $name = 'StudentSearchAgent';
        
  public $belongsTo = array(
        'Student'=> array(
            'className' => 'Student',
            'foreignKey' => 'student_id',
            //'conditions' => array('StudentSearchAgent.active' => '1'),
            'dependent' => true
            ));
		
	function __construct() {
		$this->validate = array(
			'agent_name' => array(
			   		  'required' => array(
			   		  'rule' => array('notEmpty'),
		   		       'required' => true, 'allowEmpty' => false,
					   'message' => 'Please Enter an Agent Name.')),
		
		);
		parent::__construct();
		}
    
  public function findSearchAgents($id=null) {
     $searchAgents = null;
     //debug($id); die();
     $searchAgents  = $this->find('all', 
                                    array('conditions' => array(
                                    'StudentSearchAgent.student_id' => $id)));
                                    
     
        return $searchAgents;
      
  }  
  public function saveSearchAgent($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['StudentSearchAgent']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
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