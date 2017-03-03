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
class Preference extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Preference';

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
					          'message' => 'You must agree to the terms of use.')

); //end validates array

  //public function beforeSave() {
     // foreach($this->data[$this->alias] as $field => $value) {
        //  if($field !== 'first_name') {
          //  $this->data[$this->alias]['field'] = $field;
          //  $this->data[$this->alias]['value'] = $value;
         // }
      // }
      // return true;
  //}

  public function savePreferences($postData = array()) {
             //debug($postData); die();
           $this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false));
  			return true;
 }
} //end of class