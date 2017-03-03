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
App::uses('Hash', 'Utility');
App::uses('Validation', 'Utility');

/**
 * Users Plugin User Model
 *
 * @package User
 * @subpackage User.Model
 */
class StudentPreference extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'StudentPreference';

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

				'new_features' => array(
							  'rule' => array('custom','[1]'),
					          'message' => 'You must agree to the terms of use.'),

			  'promos' => array(
							  'rule' => array('custom','[1]'),
					          'message' => 'You must agree to the terms of use.'),


			     'daily_digest' => array(
							  'rule' => array('custom','[1]'),
					          'message' => 'You must agree to the terms of use.'),

				 'new_tutor' => array(
							  'rule' => array('custom','[1]'),
					          'message' => 'You must agree to the terms of use.'),

				'lesson_review' => array(
							  'rule' => array('custom','[1]'),
					          'message' => 'You must agree to the terms of use.'),

			   'sms_alerts' => array(
			   							  'rule' => array('custom','[1]'),
			   							  'required' => true, 'allowEmpty' => false,
			   					          'message' => 'You must agree to the terms of use.'),

			    'phone_number' => array(
			   							'rule' => array('phone', null, 'us'),
			   							'required' => false, 'allowEmpty' => true,
			   					        'message' => 'A valid US Phone Number is required.'),

			    'carrier' => array(
			   		           'notEmpty' => array(
			   		               'rule' => array('phoneCarrierValidation', 'phone_number'),
			   		               'message' => 'Please enter your current City.',
			   		               'allowEmpty' => false))

); //end validates array

public function phoneCarrierValidation($check, $phone_number) {

		     if(
		        (Hash::get($this->data[$this->alias], $phone_number) != '')
		            &&
		        (Hash::get($this->data[$this->alias] != "0"))
		     ) {
			             return true; //Validation::notEmpty(current($check));
		      }
		      return false;
   }

  //public function beforeSave() {
     // foreach($this->data[$this->alias] as $field => $value) {
        //  if($field !== 'first_name') {
          //  $this->data[$this->alias]['field'] = $field;
          //  $this->data[$this->alias]['value'] = $value;
         // }
      // }
      // return true;
  //}

  public function savePreferences($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['StudentPreference']['id'] = $id;  //wite the pk into the data array so it know this an update an not a create
        }
           $this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false));

  			return true;
   }
} //end of class