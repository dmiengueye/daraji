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

class Subscription extends User {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Subscription';


	public $validate = array(
		

				  'email' => array(
					'isValid' => array(
						'rule' => 'email',
						'required' => true,
						'message' => 'Please enter a valid email address.'),
					'isUnique' => array(
						'rule' => array('isUnique', 'email'),
						'message' => 'This email is already in use.')),

		
				 'password' => array(
					'too_short' => array(
						'rule' => array('minLength', '6'),
						'message' => 'The password must have at least 6 characters.'),
                        
					'required' => array(
						'rule' => 'notEmpty',
						'message' => 'Please enter a password.')),

				

				); //end validates array







function isUnique($check, $email=true) {
    
	$user = $this->findByEmail($this->data[$this->alias]['email']);
   //debug($user); die();
	if(!empty($user) ){
		return false;
	} 

	return true;
}

public function subscribe($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['Common']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
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
