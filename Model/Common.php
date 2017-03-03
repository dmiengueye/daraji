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
App::uses('CakeSession', 'Model/Datasource');
App::uses('CustomPasswordHasher', 'Controller/Component/Auth');
App::uses('Tutor', 'Model');

App::uses('User', 'Users.Model');

//App::uses('Crypto', 'Model');
//use Crypto\Crypto;
//use Crypto\Exception as Ex;

use Crypto\Crypto;
use Crypto\Exception as Ex;

//use Crypto\Crypto;
//use Crypto\Exception as Ex;

//require_once 'autoload.php';

class Common extends User {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Common';


	public $validate = array(
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

				  'email' => array(
					'isValid' => array(
						'rule' => 'email',
						'required' => true,
						'message' => 'Please enter a valid email address.'),
					'isUnique' => array(
						'rule' => array('isUnique', 'email'),
						'message' => 'This email is already in use.')),

		          'confirm_email' => array(
				 			'rule' => 'confirmEmail',
					'message' => 'The email must match.'),

				 'password' => array(
					'too_short' => array(
						'rule' => array('minLength', '6'),
						'message' => 'The password must have at least 6 characters.'),
                        
					'required' => array(
						'rule' => 'notEmpty',
						'message' => 'Please enter a password.')),

				'confirm_password' => array(
					'rule' => 'confirmPassword',
					'message' => 'The passwords must match.'),


			  'zip_code' => array(
			        'rule' => array('postal', null, 'us'),
			        'message' => 'A valid US Zip Code is required.'),


			     'referal' => array(
				      //'notEmpty' => array(
				 		'rule' => 'notEmpty',
				 		'message' => 'Please provide source type'
				 		//)
				 	),

				 'contactbox' => array(
				 		//'notEmpty' => array(
				 		'rule' => array('contactBoxValidation', 'referal'),
				 		'required' => true,
				 		'message' => 'Please provide source',
				 		//'allowEmpty' => false
				 		//)
				 	),

				 	'tos' => array(
							  'rule' => array('custom','[1]'),
					          'message' => 'You must agree to the terms of use.')

				); //end validates array



/**
   * Custom validation method to ensure that the two entered passwords match
   *
   * @param string $password Password
   * @return boolean Success
   */
  	public function confirmPassword($password = null) {
  		if ((isset($this->data[$this->alias]['password']) && isset($password['confirm_password']))
  			&& !empty($password['confirm_password'])
  			&& ($this->data[$this->alias]['password'] === $password['confirm_password'])) {
  			return true;
  		}
  		return false;
  	}


/**
 * Compares the email confirmation
 *
 * @param array $email Email data
 * @return boolean
 */
	public function confirmEmail($email = null) {
		if ((isset($this->data[$this->alias]['email']) && isset($email['confirm_email']))
			&& !empty($email['confirm_email'])
			&& (strtolower($this->data[$this->alias]['email']) === strtolower($email['confirm_email']))) {
				return true;
		}
		return false;
	}

function isUnique($check, $email=true) {
    $tutor = new Tutor();
    $user = null;
    $user2 = null;
	$user = $this->findByEmail($this->data[$this->alias]['email']);
	//$this->loadModel('Tutor');
	$user2  = $tutor->findByEmail($this->data[$this->alias]['email']);

//debug($user);
//debug($user2);
//die();
	if( $user != null && !empty($user) ){
		return false;
	} else if($user2 != null && !empty($user2) ){
	   return false;
	}

	return true;
}



}
