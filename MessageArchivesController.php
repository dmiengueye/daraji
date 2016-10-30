<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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

//App::uses('UsersController', 'Users.Controller');
App::uses('Student', 'Model');
App::uses('Tutor', 'Model');
App::uses('File', 'Utility');

class MessageArchivesController extends AppController {
	
	
	public $name = 'MessageArchives';
	public $uses = array ('MessageArchive', 'Tutor', 'Student', 'TutorPreference', 'TutorProfile', 'TutorImage', 'TutorSubject');
    public $helpers = array('ZipCode', 'Html');
	
	// ******************pusher-app *************//

	
	public function message() {

debug("Here 1"); die();
		$this->layout	=	'default';
		//$tutor = $this->Tutor->findById($this->Session->read('Auth.Tutor.id')); 
		//$tutor1 = $this->Tutor->find('all'); 
		//$this->set('mystudent', $tutor);
		
		
	}

	public function message_relay() {
		
                //I have moved these to the config file (bootsrap.php)   DG

		//$app_id = '249421'; // App ID
		//$app_key = '565ee28a7185da8bdfa6'; // App Key
		//$app_secret = 'db640a49aa6af6e212f6'; // App Secret
		
		$app_id =     Configure::read('Pusher.credentials.appKey');   // App ID
		$app_key =    Configure::read('Pusher.credentials.appSecret');  // App Key
		$app_secret = Configure::read('Pusher.credentials.appId');    // App Secret
		
		$pusher = new Pusher($app_key, $app_secret, $app_id);

                //debug($pusher); die();
		// Check the receive message
		if( $this->request->is('ajax') ) {
			$this->autoRender = false;
		}
		if ($this->request->isPost()) {
			$data['message'] = $this->request->data['message'];
			$data['to'] = '';
			// get values here 
			//if(isset($_POST['message']) && !empty($_POST['message'])) {		
			//$data['message'] = $_POST['message'];	

			// Return the received message
			if($pusher->trigger('private-message', 'my_event', $data)) {				
				echo 'success';			
			} else {		
				echo 'error';	
			}
		}
		//$this->Pusher->subscribe('private-my-great-channel');
	} 
	
	public function channelauth()
	{
		if (!$this->Auth->loggedIn()) {
			$this->Auth->authError = false;
			
        } else  {
		
			//$app_id = '249421'; // App ID
			//$app_key = '565ee28a7185da8bdfa6'; // App Key
			//$app_secret = 'db640a49aa6af6e212f6'; // App Secret
			
			//I have moved these to the config file (bootsrap.php)   DG
			
			$app_id =     Configure::read('Pusher.credentials.appKey');   // App ID
			$app_key =    Configure::read('Pusher.credentials.appSecret');  // App Key
			$app_secret = Configure::read('Pusher.credentials.appId');    // App Secret
		
			$pusher = new Pusher($app_key, $app_secret, $app_id);
			// Check the receive message
			if( $this->request->is('ajax') ) {
				$this->autoRender = false;
			}
			if ($this->request->isPost()) {
				$auth 		= $pusher->socket_auth($this->request->data['channel_name'], $this->request->data['socket_id']);
				echo $auth;
			}
		}
	}
// ****************************//
}

?>
	