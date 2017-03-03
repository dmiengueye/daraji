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
App::uses('Tutor', 'Model');
App::uses('Hash', 'Utility');
App::uses('ZipSearch', 'Model');
App::uses('Subject', 'Model');
App::uses('TutorSubject', 'Model');
App::uses('Validation', 'Utility');
App::uses('StudentSearchAgent', 'Model');
App::uses('StudentWatchList', 'Model');
App::uses('StudentJobPost', 'Model');
App::uses('TutorJobApplication', 'Model');

class Contact extends AppModel {
   
/**
 * Name
 *
 * @var string
 */
	public $name = 'Contact';


	public $validate = array(
		'user_name' => array(
					'required' => array(
						'rule' => array('notEmpty'),
						'required' => true, 'allowEmpty' => false,
						'message' => 'Please enter your Name.'),
					'user_name_min' => array(
						'rule' => array('minLength', '2'),
						'message' => 'The name must have at least 2 characters.')),

	    'user_email' => array(
					'isValid' => array(
						'rule' => 'email',
						'required' => true,
						'message' => 'Please enter a valid email address.'),
					 ),
					 
		'user_message' => array(
					'required' => array(
						'rule' => array('notEmpty'),
						'required' => true, 'allowEmpty' => false,
						'message' => 'Please describe your message in one sentence or more.'),
					
					'user_message_min' => array(
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

public function saveContactMessage($id, $postData = array()) {
       // debug($postData); die();
         if(!empty($id)) {
           $postData['Contact']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
        }
           debug($postData); die();
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