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


class TutorStudent extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorStudent';

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

	              'student_name' => array(
                            'required' => array(
			   						     'rule' => array('notEmpty'),
			   						     'required' => true, 'allowEmpty' => false,
			   						     'message' => 'Location Name is required.'))
					
 ); //end validates array

public function add_student($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['TutorStudent']['id'] = $id;  //write the pk into the data array so it knows this an update an not a create
        }
           $this->save($postData, array(
  		 				'validate' => true,
  		 				'callbacks' => true));

  			return true;
   }
   


public function get_tutor_students_ById($id){
    
    $students = array();
    $data = $this->find('all',
            array(
              'conditions' => array(
                'TutorStudent.tutor_id'  => $id,
                )
              ));
              
     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorStudent']['student_name'])){
					$students[] = $value['TutorStudent']['student_name'];
				}
			}
		}
   return $students;
}


public function get_all_students_for_tutor($id){
    $students = array();
    //debug($id);
  $data = $this->find('all',
            array(
              'conditions' => array(
                'TutorStudent.tutor_id'  => $id,
                )
              ));

     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorStudent']['student_name'])  && 
                  !empty($value['TutorStudent']['student_id'])){
                    $students[] = array($value['TutorStudent']['student_id'] => $value['TutorStudent']['student_name']);
    	
				}
			}
		}
 return $students;
}




} //end of class