<?php
/**
 * Static content controller.
 *
 * This file will render views from Views/Students
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

//App::uses('AppController', 'Controller');
App::uses('UsersController', 'Users.Controller');
App::uses('MessageArchive', 'Model');
App::uses('Tutor', 'Model');

// app/Controller/CommonsController.php
    
class UserMessagesController extends UsersController {


/**
 * Controller name
 *
 * @var string
 */
	public $name = 'UserMessages';
	public $uses = array ('UserMessage');
   

// ****************************//

/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

		parent::beforeFilter();
        $this->set('model', 'Contact');		
}


public function message_success() {
	 //debug($this->request->data); die();
        $this->layout = 'default';
  
}
 public function message_us() {
	//debug($this->request->data); die();
     $this->layout = 'default';     
     $postData = array();	 
     $this->{$this->modelClass}->set(array(
	                             'name' =>     $this->request->data['UserMessage']['user_name'],
                                'email' =>     $this->request->data['UserMessage']['user_email'],  
                                'message' =>  $this->request->data['UserMessage']['user_message']
                                                                      
                             ));

   //we want to return student to page if for some reason, the job details he/she entered are not validated
    if($this->{$this->modelClass}->validates(array('fieldList' => array('name', 'email', 'message')))){
          
		   $postData['UserMessage']['name'] = $this->request->data['UserMessage']['user_name'];
		   $postData['UserMessage']['email'] = $this->request->data['UserMessage']['user_email'];
		    $postData['UserMessage']['message'] = $this->request->data['UserMessage']['user_message'];
		 // debug($postData); die();
        if(!$this->{$this->modelClass}->saveUserMessage(null, $postData))
		{
				$this->Session->setFlash
										(
											sprintf(__d('users', 'There appears to be some issues. Please try again!')),
													'default',
													array('class' => 'alert alert-warning')
										);
					$this->Session->write('error_array', $this->{$this->modelClass}->validationErrors);
					//$this->set('error_array', $this->{$this->modelClass}->validationErrors);
					
					$this->redirect($this->referer('/'));
		} 
    } else {
	   
				$this->Session->setFlash
										(
										 sprintf(__d('users', 'OOPS!! Looks like you have either entered an invalid email!')),
													'default',
													array('class' => 'alert alert-warning')
										);
					$this->Session->write('error_array', $this->{$this->modelClass}->validationErrors);
					$this->redirect($this->referer('/'));
           }
		   
	 $email_subject = 'New Inquiry From a User';
	 $email_type = 'user_message';
	 $email_format ='html';
	 $email_template = $this->_pluginDot() .$email_type;
	 $layout = 'default';
	 $from_address = 'info@wizwonk.com';
	 $to_address = 'damien.gueye30@gmail.com' ; 
	 $email_instance = null;;
	 $sender_name ='Wizwonk';
	 $admin=null;
	 $userData = $this->request->data;

	$viewVariables = array('model' => $this->modelClass,'userData' => $userData);
    // $this->_send_job_application($this->request->data);
    $this->TransEmail->_sendEmail($this->{$this->modelClass}->data,$email_subject,$email_format,
						  								$email_instance, $from_address, $to_address,
						  								$sender_name, $email_template, $layout,
								                        $viewVariables, $admin, $email_type, $this->modelClass);
   
   return $this->redirect(array('action' => 'message_success'));
   
 }
 


}