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

App::uses('UsersController', 'Users.Controller');

class TutorsController extends UsersController {
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Tutors';
	public $uses = array ('Tutor', 'TutorPreference', 'TutorProfile');


/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

        //$this->Auth->autoRedirect = false;
        //$this->Auth->loginRedirect = null;
         //$this->Session->delete('Auth.redirect');
		parent::beforeFilter();
        AuthComponent::$sessionKey = 'Auth.Tutor';
        $this->modelClass = 'Tutor';
		$this->set('model', 'Tutor');

        $this->Auth->allow('complete');
		/**
		if ($this->request->action == 'register' || $this->request->action == 'joinus') {
		   			$this->Components->disable('Auth');
   		}
   		**/
		$this->_setupPagination();
		$this->set('model', $this->modelClass);

		$this->Session->delete('view_layout');
		$this->Session->write('view_layout', 'tutor');

		$id = $this->Auth->user('id'); //Using the session's user id is fine because it doesn't change/update
        if($this->Session->check(AuthComponent::$sessionKey . 'first_name')) {
                  $this->Session->write('username', $this->Session->read(AuthComponent::$sessionKey . 'first_name'));
                  //debug($this->Session->read('Auth.Tutor.first_name')); die();
        } else {
                $user_data = $this->{$this->modelClass}->findById($id);
		 		if($user_data != null) {
		 		    $user_fname = $user_data[$this->modelClass]['first_name'];
		 		    $last_name = $user_data[$this->modelClass]['last_name'];
		            $this->set('fname', $user_fname);
		            $this->Session->write('username', $user_fname);
		            $this->Session->write('lastname', $last_name);
                }
        }

        /**
			* Changed in version 2.4: Sometimes, you want to display the authorization error only
			* the user has already logged-in. You can suppress this message by setting its value to boolean false
		**/

		 if (!$this->Auth->loggedIn()) {
			$this->Auth->authError = false;
			$this->Session->write('view_layout', 'default');
        }
}
public function joinus() {
        $this->layout = 'default';
         //debug($this->request->data);
          Configure::write('Users.role', 'tutor');
         // Call add() function of Parent (Plugin::UsersController)
         //check the uniqueness of email
         $this->add();
         //$this->validationErrors;
         //debug($this->Tutor->validationErrors);
}

public function complete() {
        $this->layout = 'default';
        if($this->Session->check('completeEmail')){
            $this->set('completeEmail',$this->Session->read('completeEmail'));
            $this->Session->delete('completeEmail');
        } else{
            return $this->redirect(array('action' => 'login','controller'=>'tutors'));
        }
}

 public function home() {
          $this->set('title_for_layout', 'Daraji - Tutor Home');
        $this->layout='tutor';
      //  $this->Student->recursive = 0;
      //  $this->set('students', $this->paginate());
    }

public function welcome() {
     $this->layout='tutor';
    //debug($this->Auth->user('last_login'));
    if($this->Auth->user('last_login') != null) {
           return $this->redirect(array('action' => 'tutordashboardempty'));
    }
}


public function market_place() {
     $this->layout='tutor';

	         if($this->request->is('post')) {
	         $id = null;

	    	     if (!empty($this->request->data)) {
	    	     //debug($this->request->data); die();
	    	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
	    			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

	    			          if(!empty($this->request->data['TutorProfile']['id']))
	    			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)

	    					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
	    					   {
	    							$this->Session->setFlash
	    									(
	    												sprintf(__d('users', 'Profile has been successfully saved.')),
	    											   'default',
	    												array('class' => 'alert alert-success')
	    									 );

	    								return $this->redirect(array('action' => 'basic_profile'));
	    					  } else {
	    					      $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'Something went wrong.')),
	 						     					'default',
	 						     					 array('class' => 'alert alert-warning')
	    									 );
	    					  }
	                }
	          }

	           //set the primary key of preference table in the view and send it back as a hidden field
			      $mktPlaceModel = $this->{$this->modelClass}->TutorProfile->find
			     	                  (
			     	                    'first',
			     	                     array('field' => 'tutor_id',
			     	                    'value' => $this->Auth->user('id')
			     	                 ));

			    if(!empty($mktPlaceModel)) {
			      	//debug($mktPlaceModel); die();
   	                $this->set('prpk',   $mktPlaceModel['TutorProfile']['id']);
   	             }
  }

public function basic_profile() {
   $this->layout='tutor';
   if($this->request->is('post')) {
         $id = null;
   	     if (!empty($this->request->data)) {
   	                 //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['TutorProfile']['id'])) {
   			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)
   			          }

                      if ($this->{$this->modelClass}->TutorProfile->validates(
                              array('fieldList' => array(
                                      'TutorProfile.gender',
                                      'TutorProfile.education',
                                      'TutorProfile.degree',
                                      'TutorProfile.school',
                                      'TutorProfile.address_1',
                                      'TutorProfile.city',
                                      'TutorProfile.state',
                                      'TutorProfile.zip_code',
                                      'TutorProfile.maddress_1',
									  'TutorProfile.mcity',
									  'TutorProfile.mstate',
                                      'TutorProfile.mzip_code',
                                      'TutorProfile.primary_phone',
                                      'TutorProfile.pphone_type',
                                      'TutorProfile.secondary_phone',
                                      'TutorProfile.sphone_type'))))
                   {
   					     if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
   					        {
									$this->Session->setFlash
											(
														sprintf(__d('users', 'Basic Profile has been successfully saved.')),
													   'default',
														array('class' => 'alert alert-success')
											 );

								       return $this->redirect(array('action' => 'public_profile'));

   					         }
                  }
               }
         }


             //set the primary key of preference table in the view and send it back as a hidden field
   	      $basicProfileModel = $this->{$this->modelClass}->TutorProfile->find
   	                 (
   	                    'first',
   	                     array('field' => 'tutor_id',
   	                    'value' => $this->Auth->user('id')
   	                 ));

   	        //debug($this->request->data[$this->modelClass]['first_name']); die();
   	      if(!empty($basicProfileModel)) {
   	                //debug($basicProfileModel); die();
   	                $this->set('prpk',   $basicProfileModel['TutorProfile']['id']);
   	                $this->set('fn',     $this->Session->read('username'));
   	                $this->set('ln',     $this->Session->read('lastname'));

   	                //$this->set('ed',     $basicProfileModel['TutorProfile']['education']);
   	                //$this->set('degree', $basicProfileModel['TutorProfile']['degree']);
   	                //$this->set('school', $basicProfileModel['TutorProfile']['school']);

   	                //$this->set('add1',   $basicProfileModel['TutorProfile']['address_1']);
   	                //$this->set('add2',   $basicProfileModel['TutorProfile']['address_2']);
   	                //$this->set('city',   $basicProfileModel['TutorProfile']['city']);
   	                //$this->set('st',     $basicProfileModel['TutorProfile']['state']);
   	                //$this->set('zip',    $basicProfileModel['TutorProfile']['zip_code']);

   	                //$this->set('madd1',   $basicProfileModel['TutorProfile']['maddress_1']);
					//$this->set('madd2',   $basicProfileModel['TutorProfile']['maddress_2']);
					//$this->set('mcity',   $basicProfileModel['TutorProfile']['mcity']);
					//$this->set('mst',     $basicProfileModel['TutorProfile']['mstate']);
   	                //$this->set('mzip',    $basicProfileModel['TutorProfile']['mzip_code']);

   	               // $this->set('pp',     $basicProfileModel['TutorProfile']['primary_phone']);
   	                //$this->set('sp',     $basicProfileModel['TutorProfile']['secondary_phone']);
   	               // $this->set('mhop',   $basicProfileModel['TutorProfile']['pphone_type']);
   	               // $this->set('mhos',   $basicProfileModel['TutorProfile']['sphone_type']);
             }



}

public function public_profile() {
   $this->layout='tutor';

        if($this->request->is('post')) {
        $id = null;

   	     if (!empty($this->request->data)) {
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			             if(!empty($this->request->data['TutorProfile']['id'])) {
					            $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)
					     }

					     if ($this->{$this->modelClass}->TutorProfile->validates(
					                               array('fieldList' => array(
					                                       'TutorProfile.hourly_rate',
					                                       'TutorProfile.travel_radius',
					                                       'TutorProfile.cancel_policy',
					                                       'TutorProfile.title',
					                                       'TutorProfile.description'))))

                        {
							 if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
									   {
											$this->Session->setFlash
													(
																sprintf(__d('users', 'Public Profile has been successfully saved.')),
															   'default',
																array('class' => 'alert alert-success')
													 );

											return $this->redirect(array('action' => 'independent_contractor__agreement'));
										 }

   					 } // if ($this->{$this->modelClass}->TutorProfile->validates
               } //end if (!empty($this->request->data))
         } //  if($this->request->is('post'))

          //set the primary key of preference table in the view and send it back as a hidden field
   	      $publicProfileModel = $this->{$this->modelClass}->TutorProfile->find
   	                  (
   	                    'first',
   	                     array('field' => 'tutor_id',
   	                    'value' => $this->Auth->user('id')
   	                 ));

   	      if(!empty($publicProfileModel)) {
   	                //debug($basicProfileModel); die();
   	                $this->set('prpk',   $publicProfileModel['TutorProfile']['id']);
   	               // $this->set('hr',     $publicProfileModel['TutorProfile']['hourly_rate']);
   	               // $this->set('tr',     $publicProfileModel['TutorProfile']['travel_radius']);
   	               // $this->set('cp',     $publicProfileModel['TutorProfile']['cancellation_policy']);

   	               // $this->set('pt',     $publicProfileModel['TutorProfile']['profile_title']);
   	               // $this->set('pd',     $publicProfileModel['TutorProfile']['profile_desc']);

             }

}

public function independent_contractor__agreement() {

      if($this->request->is('post')) {
                 $postData = array();
	             $id = null;
	    	     if (!empty($this->request->data)) {
	    	                  //debug($this->request->data); die();
	    	                  $this->{$this->modelClass}->TutorProfile->set($this->request->data);
	    	                  $postData = $this->request->data;
	    			          $this->request->data['TutorProfile']['tutor_id'] = $this->Auth->user('id');

	    			          if(!empty($this->request->data['TutorProfile']['id']))
	    			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)

	    			            //The signature consists of first and last name concatenated together
	                           $postData['TutorProfile']['tutor_signature'] = $postData['TutorProfile']['first_name'].$postData['TutorProfile']['last_name'];
	                           if(!empty($postData['TutorProfile']['tutor_signature']))
	                                 $postData['TutorProfile']['signed_agreement'] = 1;

	                           //debug($postData); die();

	                          if ($this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('TutorProfile.first_name', 'TutorProfile.last_name', 'TutorProfile.terms_of_use', 'TutorProfile.work_auth'))))
	                          {
							       if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
								  	  {
								  	     $this->Session->setFlash(sprintf(__d('users', 'Agreement details has been successfully saved.')),
								  	    											   'default',
								  	    												array('class' => 'alert alert-success')
								  	    									 );
								  	        return $this->redirect(array('action' => 'add_subjects'));
	    					          }
							  } //else {
							      // invalid
							         // $errors = $this->{$this->modelClass}->TutorProfile->validationErrors;
							  //}
	                  }
             }

             //set the primary key of preference table in the view and send it back as a hidden field
		     $tProfileModel = $this->{$this->modelClass}->TutorProfile->find
		     	                  (
		     	                    'first',
		     	                     array('field' => 'tutor_id',
		     	                    'value' => $this->Auth->user('id')
		     	                 ));
		     if(!empty($tProfileModel))
		     {
		     	         //debug($tProfileModel); die();
   	                     $this->set('prpk',   $tProfileModel['TutorProfile']['id']);
   	                     $this->set('fn',     $this->Session->read('username'));
   	                     $this->set('ln',     $this->Session->read('lastname'));
   	         }
   }

public function add_subjects() {
     $this->layout='tutor';

           //return $this->redirect(array('action' => 'welcome'));
}

public function tutordashboardempty() {
     $this->layout='tutor';

           //return $this->redirect(array('action' => 'welcome'));
}


  public function tellYourFriends() {
    $this->layout='tutor';

  }

   public function accountsettings() {
            $this->layout='tutor';
    }

 public function manage() {
            $this->layout='tutor';
    }

 public function mysubjects() {
            $this->layout='tutor';
    }

  public function mydaraji() {
	            $this->layout='tutor';
    }

public function my_students() {
	            $this->layout='tutor';
    }

   public function studentsandlessons() {
		     $this->layout='tutor';
    }

    public function alllessons() {
			     $this->layout='tutor';
    }

     public function myscheduledlessons() {
				     $this->layout='tutor';
    }

     public function mysubmittedlessons() {
					$this->layout='tutor';
    }

    public function lesson_submission() {
				$this->layout='tutor';
    }

    public function studentfeedback() {
				$this->layout='tutor';
    }


     public function helptutor() {
					$this->layout='tutor';
    }

    public function background_check_consent() {
						$this->layout='tutor';
    }

    public function successtips() {
			$this->layout='tutor';
    }

    public function jobsearchresultsauth() {
			$this->layout='tutor';
    }

    public function jobsearchresults() {
			$this->layout='default';
    }

     public function job_details_auth() {
				$this->layout='tutor';
    }

     public function job_details() {
					$this->layout='default';
    }

    public function job_search_tools() {
			$this->layout='tutor';
    }

     public function post_job() {
			$this->layout='tutor';
    }

public function tutor_review_of_daraji() {
            $this->layout='tutor';
   }

public function account_confirm() {
         $this->layout='default';
   }


public function change_password() {
   $this->layout = 'tutor';
   $this->changePassword();

}

public function change_email() {
   $this->layout = 'tutor';
   $this->changeEmail();

}

public function manage_basic_profile() {
   $this->layout='tutor';

        if($this->request->is('post')) {
        $id = null;

   	     if (!empty($this->request->data)) {
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['TutorProfile']['id']))
   			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)

   					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
   					  }
               }
         }


             //set the primary key of preference table in the view and send it back as a hidden field
   	      $tProfileModel = $this->{$this->modelClass}->TutorProfile->find
   	                  (
   	                    'first',
   	                     array('field' => 'tutor_id',
   	                    'value' => $this->Auth->user('id')
   	                 ));
   	      if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   $tProfileModel['TutorProfile']['id']);
   	                $this->set('ed',     $tProfileModel['TutorProfile']['education']);
   	                $this->set('degree', $tProfileModel['TutorProfile']['degree']);
   	                $this->set('school', $tProfileModel['TutorProfile']['school']);

   	                $this->set('add1',   $tProfileModel['TutorProfile']['address_1']);
   	                $this->set('add2',   $tProfileModel['TutorProfile']['address_2']);
   	                $this->set('city',   $tProfileModel['TutorProfile']['city']);
   	                $this->set('st',     $tProfileModel['TutorProfile']['state']);
   	                $this->set('zip',    $tProfileModel['TutorProfile']['zip_code']);

   	                $this->set('madd1',   $tProfileModel['TutorProfile']['maddress_1']);
					$this->set('madd2',   $tProfileModel['TutorProfile']['maddress_2']);
					$this->set('mcity',   $tProfileModel['TutorProfile']['mcity']);
					$this->set('mst',     $tProfileModel['TutorProfile']['mstate']);
   	                $this->set('mzip',    $tProfileModel['TutorProfile']['mzip_code']);

   	                $this->set('pp',     $tProfileModel['TutorProfile']['primary_phone']);
   	                $this->set('sp',     $tProfileModel['TutorProfile']['secondary_phone']);
   	                $this->set('mhop',   $tProfileModel['TutorProfile']['pphone_type']);
   	                $this->set('mhos',   $tProfileModel['TutorProfile']['sphone_type']);
             }

}

public function manage_public_profile() {
   $this->layout='tutor';

        if($this->request->is('post')) {
        $id = null;

   	     if (!empty($this->request->data)) {
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['TutorProfile']['id']))
   			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)

   					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
   					  }
               }
         }


             //set the primary key of preference table in the view and send it back as a hidden field
   	      $tProfileModel = $this->{$this->modelClass}->TutorProfile->find
   	                  (
   	                    'first',
   	                     array('field' => 'tutor_id',
   	                    'value' => $this->Auth->user('id')
   	                 ));
   	      if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   $tProfileModel['TutorProfile']['id']);
   	                $this->set('hr',     $tProfileModel['TutorProfile']['hourly_rate']);
   	                $this->set('tr',     $tProfileModel['TutorProfile']['travel_radius']);
   	                $this->set('cp',     $tProfileModel['TutorProfile']['cancel_policy']);

   	                $this->set('title',         $tProfileModel['TutorProfile']['title']);
   	                $this->set('description',   $tProfileModel['TutorProfile']['description']);

             }

}

 public function manage_preferences() {

      $this->layout='tutor';
      if($this->request->is('post')) {
      $id = null;

 	     if (!empty($this->request->data)) {
 	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
 			          $this->request->data['TutorPreference']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

 			          if(!empty($this->request->data['TutorPreference']['id']))
 			                $id = $this->request->data['TutorPreference']['id'];     //the Pk of Associated model (TutorPreference)

 					  if($this->{$this->modelClass}->TutorPreference->savePreferences($id, $this->request->data))
 					   {
 							$this->Session->setFlash
 									(
 												sprintf(__d('users', 'Email/Sms Preferences successfully saved.')),
 											   'default',
 												array('class' => 'alert alert-success')
 									 );
 					  }
             }
       }


           //set the primary key of preference table in the view and send it back as a hidden field
 	      $tPrefModel = $this->{$this->modelClass}->TutorPreference->find
 	                  (
 	                    'first',
 	                     array('field' => 'tutor_id',
 	                    'value' => $this->Auth->user('id')
 	                 ));
 	      if(!empty($tPrefModel)) {
 	                //debug($tPrefModel); die();
 	                $this->set('ppk',  $tPrefModel['TutorPreference']['id']);
 	                $this->set('nf',   $tPrefModel['TutorPreference']['new_features']);
 	                $this->set('pmos', $tPrefModel['TutorPreference']['promos']);
 	                $this->set('dd',   $tPrefModel['TutorPreference']['daily_digest']);
 	                $this->set('ns',   $tPrefModel['TutorPreference']['new_students']);
 	                $this->set('ls',   $tPrefModel['TutorPreference']['lesson_submission']);
 	                $this->set('sa',   $tPrefModel['TutorPreference']['sms_alerts']);
 	                $this->set('pn',   $tPrefModel['TutorPreference']['phone_number']);
 	                $this->set('cr',   $tPrefModel['TutorPreference']['carrier']);
           }

 }

   public function missingView() {
       $this->layout = 'tutor';
       //$this->render('missing_action');
   }

  public function render($view = null, $layout = null) {
            if (is_null($view)) {
                $view = $this->action;
            }
            $viewPath = substr(get_class($this), 0, strlen(get_class($this)) - 10);
            if (!file_exists(APP . 'View' . DS . $viewPath . DS . $view . '.ctp')) {
                $this->plugin = 'Users';
            } else {
                $this->viewPath = $viewPath;
            }
            return parent::render($view, $layout);
    }
 }