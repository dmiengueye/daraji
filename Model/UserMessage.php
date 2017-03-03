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

/** Shoud inherit the Plugin Users Model
App::uses('AuthComponent', 'Controller/Component');
**/

App::uses('User', 'Users.Model');

class UserMessage extends User {
   
/**
 * Name
 *
 * @var string
 */
	public $name = 'UserMessage';


	public $validate = array(
		'first_name' => array(
					'required' => array(
						'rule' => array('notEmpty'),
						'required' => true, 'allowEmpty' => false,
						'message' => 'Please enter your Name.'),
					'first_name_min' => array(
						'rule' => array('minLength', '2'),
						'message' => 'The name must have at least 2 characters.')),

	    'email' => array(
					'isValid' => array(
						'rule' => 'email',
						'required' => true,
						'message' => 'Please enter a valid email address.'),
					 ),
					 
		'message' => array(
					'required' => array(
						'rule' => array('notEmpty'),
						'required' => true, 'allowEmpty' => false,
						'message' => 'Please describe your message in one sentence or more.'),
					
					'message_min' => array(
						'rule' => array('minLength', '10'),
						'message' => 'Your message must at least be a full sentence.')),
						
		
		'password' => array(
					'too_short' => array(
						'rule' => array('minLength', '6'),
						'message' => 'The password must have at least 6 characters.'),
                        
					'required' => array(
						'rule' => 'notEmpty',
						'message' => 'Please enter a password.')),

												
	 ); //end validates array

public function saveUserMessage($id, $postData = array()) {
       // debug($postData); die();
         if(!empty($id)) {
           $postData['UserMessage']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
        }
       // debug($postData); die();
        if($this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false))) {
  		 				   
                   return true;
        } else  {
                debug("here"); die();
               return false;
        }
   }

}

?>