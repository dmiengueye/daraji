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

//App::uses('User', 'Users.Model');
App::uses('Student', 'Model');
App::uses('Tutor', 'Model');
App::uses('TutorProfile', 'Model');
App::uses('TutorImage', 'Model');
App::uses('Hash', 'Utility');
App::uses('Validation', 'Utility');

class MessageArchive extends AppModel {

	public $name = 'MessageArchive';
	
	function __construct() {
		$this->validate = array(
            'message' => array(
					'required' => array(
						'rule' => array('notEmpty'),
						'required' => true, 
						'allowEmpty' => false,
						'message' => 'Message can not be empty.'),
                    )         
		    );
		parent::__construct();
	} 

	public function getmessage($to, $from) {
		 $messages = array();
		 $ids = array($to, $from);
		 //debug($ids); die();
		 $messages  = $this->find('all', 
										array('conditions' => array(
										'MessageArchive.from_user' => $ids,
										'MessageArchive.to_user' => $ids)
								   ));
							sort($messages);						
							//debug($messages); die();
								   
			return $messages;
		  
	}
	
	public function saveMessage($data)
	{
		//$this->create();
		//debug($data); die();
		if($this->save($data)) {
			return true;
	    } 
		return false;		
	}
	
}