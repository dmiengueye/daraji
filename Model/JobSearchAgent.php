<?php

/*
	* JobSearchAgent.php
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
	
	
class JobSearchAgent extends AppModel
{
	   
 /**
 * Name
 *
 * @var string
 */
 
  public  $name = 'JobSearchAgent';
        
  public $belongsTo = array(
        'Tutor'=> array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id',
            //'conditions' => array('JobSearchAgent.active' => '1'),
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
     $jobSearchAgents = null;
     //debug($id); die();
     $jobSearchAgents  = $this->find('all', 
                                    array('conditions' => array(
                                    'JobSearchAgent.tutor_id' => $id)));
                                    
        return $jobSearchAgents;
      
  }  
public function saveSearchAgent($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['JobSearchAgent']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
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