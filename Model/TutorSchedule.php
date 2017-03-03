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

App::uses('Security', 'Utility');
App::uses('UsersAppModel', 'Users.Model');
App::uses('SearchableBehavior', 'Search.Model/Behavior');
App::uses('SluggableBehavior', 'Utils.Model/Behavior');


class TutorSchedule extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorSchedule';

/**
 * Additional Find methods
 *
 * @var array
 */
	public $findMethods = array(
		'search' => true
	);

/**
 * All search fields need to be configured in the Model::filterArgs array.
 *
 * @var array
 * @link https://github.com/CakeDC/search
 */
public $filterArgs = array(
		'username' => array('type' => 'like'),
		'email' => array('type' => 'value')
	);

public $belongsTo = array(
        'Tutor'=> array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
            )
      );


public $validate = array(

                 'subject_name' => array(
					         'required' => array(
            					         'rule' => array('notEmpty', 'subjectNameValidation'),
            					         'required' => true, 'allowEmpty' => false,
            					         'message' => 'Subject is required.')),
                                         
                  'student_name' => array(
					         'required' => array(
            					         'rule' => array('notEmpty', 'studentNameValidation'),
            					         'required' => true, 'allowEmpty' => false,
            					         'message' => 'Student is required.')),
          
			      'schedule_date' => array(
        			 		'required' => array(
                    			 		'rule' => 'datetime', //array('notEmpty'),
                    			 		'required' => true, 'allowEmpty' => false,
                    					'message' => 'Please enter a valid date.')),


	              'start_time' => array(
                            'required' => array(
			   						     'rule' => array('notEmpty', 'time'),
			   						     'required' => true, 'allowEmpty' => false,
			   						     'message' => 'Enter a Valid Start time.')),
                  'end_time' => array(
                            'required' => array(
			   						     'rule' => array('notEmpty', 'time'),
			   						     'required' => true, 'allowEmpty' => false,
		   		
                   				         'message' => 'Enter a Valid End time.')),

                   
                  'duration' => array(
					        // 'required' => array(
            					        // 'durationRule_1' => array(
                                            // 'rule'=> array('range', 60, 600),
                                            // 'message' => 'Duration must be between 30 mins and 10 hours',
                                          // ),
                                           
                                           'durationRule_2' => array(
                                              'rule' => array('notEmpty', 'isInteger'),
                                              'message' => 'Duration must be a Number',
                                           ),
                                        //)
                                        ) 
            					         //'required' => true, 'allowEmpty' => false,
            					         //'message' => 'Duration must be between 30 mins and 10 hours'))

					
 ); //end validates array

public function subjectNameValidation($data) {
    
          if($data['subject_name'] != '0') {
		        return true;
          } 
}

public function studentNameValidation($data) {
    
          if($data['student_name'] != '0') {
		        return true;
          } 
}

 public function isInteger($data)
    {
        if (!is_scalar($data['student_name'] ) || is_float($data['student_name'] )) {
            return false;
        }
        //if (is_int($value)) {
        if(is_int($data['student_name'] )) {
            return true;
        }
        return (bool)preg_match('/^-?[0-9]+$/', $data['student_name'] );
    }


public function saveTutorSchedule($id, $postData = array()) {

      // debug($postData); die();
         if(!empty($id)) {
            //debug("tttt"); die();
           $postData['TutorSchedule']['id'] = $id;  //write the pk into the data array so it knows this an update an not a create
        }
        
       // debug($postData); die();
           if($this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => true))){
							
					return true;

  			 } else  {
               return false;
               }
   }
   


public function get_tutor_schedules_ById($id){
    $schedules = array();
    $data = $this->find('all',
            array(
              'conditions' => array(
                'TutorSchedule.tutor_id'  => $id,
                )
              ));
    
    /**          
     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorSchedule']['location_name'])){
					$schedules[] = $value['TutorSchedule']['location_name'];
				}
			}
		}
        **/
   return $schedules;
}


public function get_all_schedules_for_tutor($id){
    $schedules = array();
    //debug($id);
  $data = $this->find('all',
            array(
              'conditions' => array(
                'TutorSchedule.tutor_id'  => $id,
                )
              ));

/**
     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorSchedule']['location_name'])  && 
                  !empty($value['TutorSchedule']['location_id'])){
                    $schedules[] = array($value['TutorSchedule']['location_id'] => $value['TutorLocation']['location_name']);
    	
				}
			}
		}
        **/
 return $schedules;
}




} //end of class