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
 * 'DebugKit.Toolbar',
 */

App::uses('CakeEmail', 'Network/Email');
App::uses('AppController', 'Controller');

class StudentsController extends AppController {
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Students';

/**
 * If the controller is a plugin controller set the plugin name
 *
 * @var mixed

	public $plugin = null;
**/

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html',
		'Form',
		'Session',
		'Time',
		'Text'
	);

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Auth',
		'Session',
		'Cookie',
		'Paginator',

	);

/**
 * Constructor
 *
 * @param CakeRequest $request Request object for this controller. Can be null for testing,
 *  but expect that features that use the request parameters will not work.
 * @param CakeResponse $response Response object for this controller.
 */
	public function __construct() {
		//$this->_setupComponents();
		parent::__construct();
		//$this->_reInitControllerName();
	}

/**
  public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('register', 'complete');
    }
**/

/**
 * beforeFilter callback
 *
 * @return void
 **/
	public function beforeFilter() {
		parent::beforeFilter();
		$this->_setupAuth();
		$this->_setupPagination();

		$this->set('model', $this->modelClass);

		//if (!Configure::read('App.defaultEmail')) {
			//Configure::write('App.defaultEmail', 'noreply@' . env('HTTP_HOST'));
		//}
	}


protected function _setupAuth() {
		//if (Configure::read('Users.disableDefaultAuth') === true) {
		//if (Configure::read('Students.disableDefaultAuth') === true) {
		//	return;
		//}

		//$this->Auth->allow('add', 'reset', 'verify', 'logout', 'view', 'reset_password', 'login', 'resend_verification');

		$this->Auth->allow(
					       'login', 'register', 'joinus', 'index', 'jobsearchresults', 'aboutus',
					       'tutor_details_profile', 'tutorsearchresultslistview',
					       'request_tutor', 'resources', 'pwdrecovery',
					       'passwordrecoveryconfirm', 'pwdassistance', 'contactus', 'accountsettings',
					       'addsubjects', 'nextstep', 'secureqa', 'complete', 'resetpassword', 'changepassword', 'passwordchangeconfirm'
			       );

		//if (!is_null(Configure::read('Users.allowRegistration')) && !Configure::read('Users.allowRegistration')) {
			//$this->Auth->deny('add');
		//}

		if ($this->request->action == 'register' || $this->request->action == 'joinus') {
			$this->Components->disable('Auth');
		}

		$this->Auth->authenticate = array(
			'Form' => array(
				'fields' => array(
					'username' => 'email',
					'password' => 'password'),
				//'userModel' => $this->_pluginDot() . $this->modelClass,
				'userModel' => $this->modelClass,
				'scope' => array(
					$this->modelClass . '.active' => 1,
					$this->modelClass . '.email_verified' => 1)));

		//login page url
	    $this->Auth->loginAction = array('controller' => 'commons', 'action' => 'login');
	    //for logout
		$this->Auth->logoutRedirect = array('controller' => 'students', 'action' => 'logout');
		$this->Auth->loginRedirect = array('controller' => 'students', 'action' => 'homeroomempty');

				//$this->Auth->loginRedirect = '/';
				//$this->Auth->logoutRedirect = array('plugin' => Inflector::underscore($this->plugin), 'controller' => 'users', 'action' => 'login');
				//$this->Auth->loginAction = array('admin' => false, 'plugin' => Inflector::underscore($this->plugin), 'controller' => 'users', 'action' => 'login');
	}

	/**
	 * Sets the default pagination settings up
	 *
	 * Override this method or the index action directly if you want to change
	 * pagination settings.
	 *
	 * @return void
	 */
		protected function _setupPagination() {
			$this->Paginator->settings = array(
				'limit' => 12,
				'conditions' => array(
					$this->modelClass . '.active' => 1,
					$this->modelClass . '.email_verified' => 1
				)
			);
		}


/**
 * Returns a CakeEmail object
 *
 * @return object CakeEmail instance
 * @link http://book.cakephp.org/2.0/en/core-utility-libraries/email.html
 */
	protected function _getMailInstance() {
		$emailConfig = Configure::read('Users.emailConfig');
		if ($emailConfig) {
			return new CakeEmail($emailConfig);
		} else {
			return new CakeEmail('gmail');
		}
	}

/**
 * Sends the verification email
 *
 * This method is protected and not private so that classes that inherit this
 * controller can override this method to change the varification mail sending
 * in any possible way.
 *
 * @param string $to Receiver email address
 * @param array $options EmailComponent options
 * @return void
 */
	protected function _sendVerificationEmail($userData, $options = array()) {
		$defaults = array(
			'from' => Configure::read('App.defaultEmail'),
			'subject' => __d('students', 'Account verification'),
			//'template' => $this->_pluginDot() . 'account_verification',
			'template' => $this->Student .'account_verification',
			'layout' => 'default',
			'emailFormat' => CakeEmail::MESSAGE_TEXT
		);

		$options = array_merge($defaults, $options);

		$Email = $this->_getMailInstance();
		$Email->to($userData[$this->modelClass]['email'])
			->from($options['from'])
			->emailFormat($options['emailFormat'])
			->subject($options['subject'])
			->template($options['template'], $options['layout'])
			->viewVars(array(
			'model' => $this->modelClass,
				'user' => $userData
			))
			->send();
	}


/**
public function index() {

     $this->set('title_for_layout', 'Daraji- Student Home');
     $this->layout='student';
     $this->set('students', $this->Paginator->paginate($this->modelClass));
}
**/

    public function home() {
          $this->set('title_for_layout', 'Daraji- Student Home');
        $this->layout='student';
      //  $this->Student->recursive = 0;
      //  $this->set('students', $this->paginate());
      $this->set('students', $this->Paginator->paginate($this->modelClass));
    }

     public function signup() {
       /**  $this->layout='searchresults'; **/
         $this->layout='default';
/**
        if ($this->request->is('post')) {
            $this->Student->create();
            if ($this->Student->save($this->request->data)) {
                $this->Session->setFlash(__('The user: student has been saved'));
                //$this->redirect('/job_seekers/registration/');
                return $this->redirect(array('action' => 'nextstep'));
            }
            $this->Session->setFlash(__('The user: student could not be saved. Please, try again.'));
        }
**/

 // return $this->redirect(array('action' => 'nextstep'));

 }

 /**
  * User register action
  *
  * @return void
  */
 	public function add() {

 	    $this->layout='default';
 		if ($this->Auth->user()) {
 			$this->Session->setFlash(__d('students', 'You are already registered and logged in!'));
 			$this->redirect('/');
 		}

 		if (!empty($this->request->data)) {
 			$student = $this->{$this->modelClass}->register($this->request->data);
 			if ($student !== false) {
 				$Event = new CakeEvent(
 					//'Users.Controller.Users.afterRegistration',
 					'Students.afterRegistration',
 					$this,
 					array(
 						'data' => $this->request->data,
 					)
 				);
 				$this->getEventManager()->dispatch($Event);
 				if ($Event->isStopped()) {
 					$this->redirect(array('action' => 'login'));
 				}

 				//$this->_sendVerificationEmail($this->{$this->modelClass}->data);
 				$this->Session->setFlash(__d('students', 'Your account has been created. You should receive an e-mail shortly to authenticate your account. Once validated you will be able to login.'));
 				$this->redirect(array('action' => 'complete'));
 			} else {
 				unset($this->request->data[$this->modelClass]['password']);
 				unset($this->request->data[$this->modelClass]['confirm_password']);
 				$this->Session->setFlash(__d('students', 'Your account could not be created. Please, try again.'), 'default', array('class' => 'message warning'));
 			}
 		}
 	}

/**
     public function register() {

         $this->layout='default';
         if ($this->Auth->user()) {
		 			$this->Session->setFlash(__d('students', 'You are already registered and logged in!'));
		 			$this->redirect('/');
		 }
        if ($this->request->is('post')) {
            $this->Student->create();
            if ($this->Student->save($this->request->data)) {
                $this->Session->setFlash(__('The user: student has been saved'));
                //$this->redirect('/job_seekers/registration/');
                return $this->redirect(array('action' => 'complete'));
            }
            //$this->Session->setFlash(__('The user: student could not be saved. Please, try again.'));
            debug($this->Student->validationErrors);
        }


  //return $this->redirect(array('action' => 'complete'));

 }
**/
 public function nextstep() {
     $this->layout='default';

           //return $this->redirect(array('action' => 'complete'));
}

public function complete() {
     $this->layout='default';

          // return $this->redirect(array('action' => 'welcome'));
}

public function welcome() {
     $this->layout='student';

           //return $this->redirect(array('action' => 'welcome'));
}


 public function homeroomempty() {
     $this->layout='student';
}

public function home_room() {

     $this->layout='student';
     $this->set('users', $this->Paginator->paginate($this->modelClass));
}


public function tutorsearchresultslistview() {
     $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='default';
  }

public function studentsearchresults() {
     $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='searchresults';
  }

public function tutorprofiledetail() {
     $this->set('title_for_layout', 'Daraji-Tutor Search Results');
     $this->layout='student';
  }

  public function tutor_details_profile_auth() {
       $this->set('title_for_layout', 'Daraji-Tutor Search Results');
       $this->layout='student';
  }

  public function tutor_details_profile() {
         $this->set('title_for_layout', 'Daraji-Tutor Search Results');
         $this->layout='student';
  }

  public function studentpasswordrecovery() {
    //$this->layout='default';
    //logic to route to the logic for verifying submitted email and recovering pwd
    //if successful (ie, user was found in DB) then send
       return $this->redirect(array('action' => 'passwordrecoveryconfirm'));
    //else {
            // return $this->redirect(array('action' => 'usernotfound'));
        //}
    //
  }

   public function usernotfound() {
  	        $this->layout='default';

    }

  public function passwordrecoveryconfirm() {
      $this->layout='default';

  }

  public function chgPwd() {
    $this->layout='student';
  }

  public function addEmailAddress() {
      $this->layout='student';
  }

  public function requestTutor() {
    $this->layout='student';

  }

  public function tellYourFriends() {
    $this->layout='student';

  }

 public function contact() {
        $this->layout='default';
  }

   public function accountsettings() {
          $this->layout='student';
  }

   public function helpstudent() {
            $this->layout='student';
   }

 public function tutorsearchresultsauth() {
            $this->layout='student';
   }

    public function tutorsearchresultsauthwithbootstrapmin() {
               $this->layout='student';
   }


   public function safetytips() {
               $this->layout='student';
   }

    public function post_job() {
                  $this->layout='student';
   }

   public function myaccount() {
              $this->layout='student';
   }

   public function request_tutor() {
                 $this->layout='default';
   }

    public function lesson_scheduling() {
              $this->layout='student';
   }

    public function my_lessons() {
               $this->layout='student';
   }

    public function my_scheduled_lessons() {
                  $this->layout='student';
   }

    public function my_completed_lessons() {
                  $this->layout='student';
   }
    public function my_tutors() {
                  $this->layout='student';
   }
    public function my_tutor_watch_list() {
                  $this->layout='student';
   }
   public function my_tutor_search_agents() {
                  $this->layout='student';
   }

   public function tutor_search_tools() {
                 $this->layout='student';
    }

    public function my_pending_feedback() {
           $this->layout='student';
   }

   public function myfeedback() {
         $this->layout='student';
   }

    public function student_review_of_daraji() {
            $this->layout='student';
   }


   public function student_review_of_tutor() {
               $this->layout='student';
   }

     public function notes_on_tutor() {
                  $this->layout='student';
   }

   public function account_confirm() {
         $this->layout='default';
   }


  public function logout() {

	//$this->AuthExtension->checkRememberMe();
	$this->layout='student';
	$this->Session->delete("LOGGEDIN_USER_TYPE");
	$this->Auth->logout();

        return $this->redirect($this->Auth->logout());
   }
 }