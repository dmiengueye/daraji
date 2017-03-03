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
App::uses('Hash', 'Utility');
App::uses('UsersAppModel', 'Users.Model');
App::uses('SearchableBehavior', 'Search.Model/Behavior');
App::uses('SluggableBehavior', 'Utils.Model/Behavior');

/**
 * Users Plugin User Model
 *
 * @package User
 * @subpackage User.Model
 */
class TutorProfile extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorProfile';

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
        'Tutor' => array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
        )
    );

public $validate = array(
     //marketplace Rules validation

           'mkt_place_rules' => array(
			            'required' => array(
			   			'rule' => array('custom','[1]'),
			   			'required' => true, 'allowEmpty' => false,
			  			'message' => 'You must agree to the terms and conditions before proceeding. Please read and check the box.')),
                        
        
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
           
           /**             
           'age' => array(
                   'notEmpty' => array(
                      'rule' => array('comparison', '>=', 18),
                      'allowEmpty' => false,
                      'message' => 'You must be 18 years of Age'
                      )),
           **/
         'birthday' => array(
                'rule' => array('datetime', 'mdy'),
                'message' => 'Please enter a valid date and time.'),
          
			'school' => array(
					   'required' => array(
					   'rule' => array('notEmpty'),
					   'required' => true, 'allowEmpty' => false,
					   'message' => 'Please enter your school name.')),

			'degree' => array(
			   		  'required' => array(
			   		  'rule' => array('notEmpty'),
			   		  'required' => true, 'allowEmpty' => false,
					 'message' => 'Please enter your highest degree earned.')),

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

		              'maddress_1' => array(
						 		'required' => array(
						 		'rule' => array('notEmpty'),
						 		'required' => true, 'allowEmpty' => false,
								'message' => 'Please enter your Secondary street address or click checkbox if same as Primary.')),

						'maddress_2' => array(
								'required' => array(
								'rule' => array('notEmpty'),
								'required' => false, 'allowEmpty' => true,
								'message' => 'Please enter your street address 2.')),

						'mcity' => array(
								'required' => array(
								'rule' => array('notEmpty'),
								'required' => true, 'allowEmpty' => false,
								'message' => 'Please enter your current City or click checkbox if same as Primary.')),

						'mstate' => array(
										 'notEmpty' => array(
				                         'rule' => array('mStateValidation'), //array('notEmpty'), //array('educationValidation'),
                                         'allowEmpty' => false,
										 'message' => 'Please select your Secondary State or click checkbox if same as Primary.')),

						'mzip_code' => array(
								          'rule' => array('postal', null, 'us'),
					                      'message' => 'A valid US Zip Code is required Or click checkbox if same as Primary'),

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
								            'message' => 'Please Select a Phone Type')),
                    
                    //Public Profile data validation

       'hourly_rate' => array(
	   			//'required' => array(
  					'notEmpty' => array(
                        'rule' => array('comparison', '<=', 250),
	   					'required' => true, 'allowEmpty' => false,
					    'message' => 'Hourly Rate Must be Capped at $250 Per Hour.')),

	  'travel_radius' => array(
	  			        'required' => array(
	  					'rule' => array('notEmpty'),
	  					'required' => true, 'allowEmpty' => false,
					    'message' => 'Please set your maximum travel distance to meet a student.')),
	
     'cancel_policy' => array(
                          'notEmpty' => array(
                          'rule' => array('cancellationPolicyValidation'), //array('notEmpty'), //array('educationValidation'),
                          'allowEmpty' => false,
                          'message' => 'Please select a Cancellation Policy.')),

    
     'title' => array(
	 					'required' => array(
						   'rule' => array('notEmpty'),
						   'required' => true, 'allowEmpty' => false,
						   'message' => 'Please enter your profile title.'),
						
					   'title_max' => array(
							'rule' => array('maxLength', '100'),
						    'message' => 'The profile title is limited to 100 characters.')),

   'description' => array(
	 					'required' => array(
						    'rule' => array('notEmpty'),
						    'required' => true, 'allowEmpty' => false,
						    'message' => 'Please enter your Profile description.'),
					    'description_max' => array(
							'rule' => array('maxLength', '1500'),
						    'message' => 'The Profile description is limited to 1500 characters.')
				),


 //Independent Contractor Agreement Validation
        
              'terms_of_use' => array(
                             'required' => array(
			  				    'rule' => array('custom','[1]'),
			  				    'required' => true, 'allowEmpty' => false,
			  					'message' => 'You must agree to the terms of use.')),

			  'work_auth' => array(
			      'required' => array(
				    'rule' => array('custom','[1]'),
				    'required' => true, 'allowEmpty' => false,
					'message' => 'You must check here to indicate that you are authorized to work in the US.'))


	
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

function ageValidation($check) { 

        list($Y,$m,$d) = explode("-", $check['dob']); 

        $userAge = ( date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y ); 
                
        if ( ($userAge >= 18) && ($userAge <= 100) ) { 
                return true; 
        } else{ 
                return false;
               } 
       // endif; 

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
   
public function mStateValidation($data) {
          
           if($data['mstate'] != '0') {
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

public function cancellationPolicyValidation($data) {
          
          if($data['cancel_policy'] != '0') {		        
             return true;
          }
	      
}

public function saveProfile($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id) && $id != null) {
           $postData['TutorProfile']['id'] = $id;  //write the pk into the data array so it knows this is an update an not a create
        }
        
          //debug($postData); die();
           if($this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false))) {
  		 				   
                           return true;
               } else  {        
               return false;
               }

   }
   
public function get_tutor_profile($id) {
    
     //$pic = "";
     $data = $this->find('first',
            array(
              'conditions' => array(
                'TutorProfile.tutor_id'  => $id,
                 'TutorProfile.profile_ready'  => '1'
                 //'TutorProfile.status'  => '1',
              
                )
              ));
              
      if(!empty($data)){
			//foreach ($data as $key => $value) {
			 //debug($data['TutorSubject']['subject_credentials']); die();
				if(!empty($data['TutorProfile'])){
					$data = $data['TutorProfile'];
				}
			//}
		}
              
  //debug($data); die();    
  return $data;
}


} //end of class