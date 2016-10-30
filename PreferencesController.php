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

//App::uses('StudentsController', 'StudentsController');
App::uses('Student', 'Model');

class PreferencesController extends AppController {
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Preferences';
	public $uses = array ('Preference', 'Student');

/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

//$this->Security->allowedControllers = array('Student');
//$this->Security->allowedActions = array('manage_preferences');

        parent::beforeFilter();
        AuthComponent::$sessionKey = 'Auth.Preference';
        $this->modelClass = 'Preference';
        $this->set('model', 'Preference');

//debug($this->Session->read()); die();
 $fname = $this->Session->read('Auth.Student.first_name'); //$this->loadModel('Student');
 //debug($fname); die();
 $this->set('fname', $fname);
 //debug($sessId); die();
//$id = $this->Auth->user('id'); //Using the session's user id is fine because it doesn't change/update
//debug($id); die();
//$student = $this->loadModel('Student');
//$student = new Student();
// if($this->Session->check('username')) {
	//	$user_name = $this->Session->read('username');
      //  $this->set('fname', $user_name);
  //  }

        /**
			* Changed in version 2.4: Sometimes, you want to display the authorization error only
			* the user has already logged-in. You can suppress this message by setting its value to boolean false
		**/

		 if (!$this->Auth->loggedIn()) {
			$this->Auth->authError = false;
                  }


    }


    public function manage_preferences() {
        // debug($this->request->data); die();
	     $this->layout='student';

	    // return $this->redirect(array('controller' => 'students', 'action' => 'manage_preferences'));
	      $this->layout='student';
		      $postData = array();

		      if($this->request->is('post')) {
		      //debug($this->Student->hasOne); die();
		           debug($this->request->data); die();
		           // debug($postData); die();
		 		          $postData[$this->modelClass]['id'] = $this->Auth->user('id');
		 		        // debug($this->request->data[$this->modelClass]['id']); die();
		          debug($this->request->data); die();
		          $postData[$this->modelClass]['email_verified'] = 0;
		          $postData[$this->modelClass]['StudentPreference']['student_id']   = $postData[$this->modelClass]['id'];
		          $postData[$this->modelClass]['StudentPreference']['new_features'] = (int)$this->request->data[$this->modelClass]['new_features'];
		          $postData[$this->modelClass]['StudentPreference']['promos']       = (int)$this->request->data[$this->modelClass]['promos'];
		          $postData[$this->modelClass]['StudentPreference']['daily_digest'] = (int)$this->request->data[$this->modelClass]['daily_digest'];
		          $postData[$this->modelClass]['StudentPreference']['new_tutor']    = (int)$this->request->data[$this->modelClass]['new_tutor'];
		          $postData[$this->modelClass]['StudentPreference']['lesson_review'] = (int)$this->request->data[$this->modelClass]['lesson_review'];
		          $postData[$this->modelClass]['StudentPreference']['sms_alerts'] =  (int)$this->request->data[$this->modelClass]['sms_alerts'];
		          $postData[$this->modelClass]['StudentPreference']['phone_number'] =  (int)$this->request->data[$this->modelClass]['phone_number'];
		          $postData[$this->modelClass]['StudentPreference']['carrier'] = $this->request->data[$this->modelClass]['carrier'];
         }
	}

}
