<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
   /**public $components = array('DebugKit.Toolbar');**/

   public $components = array(
       'DebugKit.Toolbar',
       'Session',
       'Auth' => array(
           /**'loginRedirect' => array('/'),**/
           'loginRedirect' => array('controller' => 'commons', 'action' => 'user'),
           'logoutRedirect' => array('controller' => 'commons', 'action' => 'login'),
           'authError' => 'You must be logged in to view this page.',
           'loginError' => 'Invalid Username or Password entered, please try again.'

       ));

        /*
	           * @Method      :beforeFilter
	           * @Description :to set up the Auth component
	           * @access      :null
	           * @param      :null
	           * @return     :null
	           */
	   	function beforeFilter() {

	   		//for checking the admin url
	   		$admin = Configure::read('Routing.prefixes');
	   		$this->Auth->autoRedirect= false;

	   		if ((isset($this->params['prefix']) && $this->params['prefix'])) {
	   		//set options for admin
	   		//Configure AuthComponent
	   		$this->Auth->userModel="Admin";
	   		$this->Auth->fields=array('username' => 'username', 'password' => 'password');

	   		//login page url
	   		$this->Auth->loginAction = array('controller' => 'admins', 'action' => 'admin_login');

	   		//for logout
	   		$this->Auth->logoutRedirect = array('controller' => 'admins', 'action' => 'admin_logout');
	   		$this->Auth->loginRedirect = array('controller' => 'admins', 'action' => 'admin_index');

	   		} else if($this->params['controller']=='students') {

	   			$this->Auth->userModel="Student";
	   			$this->Auth->fields=array('username' => 'email', 'password' => 'password');
	   			//login page url
	   			$this->Auth->loginAction = array('controller' => 'commons', 'action' => 'login');
	   			//for logout
	   			$this->Auth->logoutRedirect = array('controller' => 'students', 'action' => 'logout');
	   			$this->Auth->loginRedirect = array('controller' => 'students', 'action' => 'homeroomempty');

	   		}
	   		else if($this->params['controller']=='tutors')
	   		{
	   			$this->Auth->userModel="Tutor";
	   			$this->Auth->fields=array('username' => 'email', 'password' => 'password');
	   			//login page url
	   			$this->Auth->loginAction = array('controller' => 'commons', 'action' => 'login');
	   			//for logout
	   			$this->Auth->logoutRedirect = array('controller' => 'commons', 'action' => 'logout');
	   			$this->Auth->loginRedirect = array('controller' => 'tutors', 'action' => 'tutordashboardempty');
	   		}

	   		$this->Auth->allow(
			       'login', 'register', 'joinus', 'index', 'jobsearchresults', 'aboutus',
			       'tutor_details_profile', 'tutorsearchresultslistview',
			       'request_tutor', 'resources', 'pwdrecovery',
			       'passwordrecoveryconfirm', 'pwdassistance', 'contactus', 'accountsettings',
			       'addsubjects', 'nextstep', 'secureqa', 'complete', 'resetpassword', 'changepassword', 'passwordchangeconfirm'
			       );
             $this->Auth->authError="Login failed. Please enter a correct user id and/or password";
	   		 $this->set("authModel",$this->Auth->userModel);

        }

   public function isAuthorized($user) {
       // Here is where we should verify the role and give access based on role

       return true;
   }
}
