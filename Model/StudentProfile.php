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

/**
 * Users Plugin User Model
 *
 * @package User
 * @subpackage User.Model
 */
class StudentProfile extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'StudentProfile';

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
        'Student' => array(
            'className' => 'Student',
            'foreignKey' => 'student_id'
        )
    );
    

public $validate = array(
        
        //Basic Profile data validation

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

            'education' => array(
				 		  'notEmpty' => array(
				          'rule' => array('educationValidation'), //array('notEmpty'), //array('educationValidation'),
                          'allowEmpty' => false,
				 		  'message' => 'Please provide your Education Level')),
                          
           
	        'gender' => array(
						'notEmpty' => array(
						'rule' => array('genderValidation'), //array('notEmpty'), //array('genderValidation'),
                        'allowEmpty' => false,
				 		'message' => 'Please provide your gender')),
                        
			'school' => array(
					   'required' => array(
					   'rule' => array('notEmpty'),
					   'required' => true, 'allowEmpty' => false,
					   'message' => 'Please enter your school name.')),

			
			 'address_1' => array(
			 		'required' => array(
			 		'rule' => array('notEmpty'),
			 		'required' => true, 'allowEmpty' => false,
					'message' => 'Please enter your cureent street address.')),

			      'address_2' => array(
						 	 'required' => array(
						 	 'rule' => array('notEmpty'),
						 	 'required' => false, 'allowEmpty' => true,
					         'message' => 'Please enter your street address 2.')),

			       'city' => array(
					         'required' => array(
					         'rule' => array('notEmpty'),
					         'required' => true, 'allowEmpty' => false,
					         'message' => 'Please enter your current City.')),

			        'state' => array(
                          'notEmpty' => array(
				          'rule' => array('stateValidation'), //array('notEmpty'), //array('educationValidation'),
                          'allowEmpty' => false,
				 		  'message' => 'Please Select current your State of Residence')),

			         'zip_code' => array(
					           'rule' => array('postal', null, 'us'),
			                   'message' => 'A valid US Zip Code is required.'),

		              
					    'primary_phone' => array(
										  'rule' => array('phone', null, 'us'),
					                      'message' => 'A valid Primary US Phone Number is required.'),
                         
                         'pphone_type' => array(
						 					'notEmpty' => array(
						 				    'rule' => array('phoneTypeValidation', 'primary_phone'),
										    'message' => 'Please Select a Phone Type')),

					    'secondary_phone' => array(
										   'rule' => array('phone', null, 'us'),
                                           'required' => false, 'allowEmpty' => true,
					                       'message' => 'A valid US Phone Number is required.'),

					     
						'sphone_type' => array(
										 	'notEmpty' => array(
										 	'rule' => array('sphoneTypeValidation', 'secondary_phone'),
								            'message' => 'Please Select a Phone Type'))
                    
      
	
); //end validates array

 public function genderValidation($data) {

	    // if((Hash::get($this->data[$this->alias]) === '0')) {
		        //     return false; //Validation::notEmpty(current($check));
	     // }
          
          if($data['gender'] != '0') {
		        if($data['gender'] === 'M'|| $data['gender'] === 'F') {
		            return true;
                }
          }
	      //return true;
   }


 public function educationValidation($data) {

		     //if((Hash::get($this->data[$this->alias]) === '0')) {
			        //     return false; //Validation::notEmpty(current($check));
		     // }
              if($data['education'] != '0') {
                //if($data['education'] === '1'|| $data['education'] === '2') {
		            return true;
                //}
             }
   }


public function stateValidation($data) {

   	     //if((Hash::get($this->data[$this->alias]) === '0')) {
   		   //          return false; //Validation::notEmpty(current($check));
   	      //}
          
           if($data['state'] != '0') {
		        return true;
            } 
   	      //return true;
}

public function phoneTypeValidation($data, $check) {
    //debug($data); die();
         if(!empty($check) && $check != null && $check != "") {
            //return ($data['pphone_type'] != '0');
            if($data['pphone_type'] != '0') {
                //debug('truethat'); //die();
                return true;
            }
         } else {
            return true;
         }
        
}

public function sphoneTypeValidation($data, $check) {
    //debug($data); die();
         if(!empty($check) && $check != null && $check != "") {
            //return ($data['pphone_type'] != '0');
            if($data['sphone_type'] != '0') {
               // debug('truethat'); die();
                return true;
            }
         } else {
            return true;
         }
        
}
public function saveProfile($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['StudentProfile']['id'] = $id;  //wite the pk into the data array so it know this an update an not a create

        }
           $this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false));

  			return true;
   }

} //end of class