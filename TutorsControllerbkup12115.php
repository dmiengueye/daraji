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
App::uses('Categorie', 'Model');
App::uses('Subject', 'Model');
App::uses('File', 'Utility');

class TutorsController extends UsersController {
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Tutors';
	public $uses = array ('Tutor', 'TutorPreference', 'TutorProfile', 'TutorImage', 'TutorSubject');


/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

		parent::beforeFilter();
        AuthComponent::$sessionKey = 'Auth.Tutor';
		$this->set('model', 'Tutor');
	//	$this->Security->blackHoleCallback = 'blackhole';

        $this->Auth->allow('complete');
		/**
		if ($this->request->action == 'joinus') {
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
		            $this->set('fname', h($user_fname));
		            $this->Session->write('username', $user_fname);
		            $this->Session->write('lastname', $last_name);
                }
        }

        /**
			* Changed in version 2.4: Sometimes, you want to display the authorization error only if
			* the user has already logged-in. You can suppress this message by setting its value to boolean false
		**/

		 if (!$this->Auth->loggedIn()) {
			$this->Auth->authError = false;
			$this->Session->write('view_layout', 'default');
        }
        
       // $this->Security->requirePost('delete');
        //$this->Security->requirePost('update_photo');
       // $this->Security->unlockedActions = array('update_photo');
       // $this->Security->unlockedActions = array('update_entry');
}
/**
public function blackhole($type) {
    // Handle errors.
    $this->layout = 'tutor';
    debug($type);
    throw new BadRequestException(__d('cake_dev', 'The request has been blackholed.'));
    //return $this->redirect('logout');
   // $this->redirect($this->Auth->logout());
}
**/
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
	    //$first_login = true;

	     if($this->Session->check('first_login')) {
	        $first_login = $this->Session->read('first_login');
	        $this->Session->delete('first_login');
	     }
	     // debug($first_login); die();
	     // debug($this->Auth->user('last_login'));
	     if(!$first_login) {
	            //$this->redirect($this->Auth->loginRedirect());
	            return $this->redirect(array('action' => 'tutor_dashboard'));
	     }
}



public function market_place() {
     $this->layout='tutor';

	         if($this->request->is('post')) {
	         $id = null;
	         $postData = array();

             if (!empty($this->request->data)) {
	    	   // debug($this->request->data); die();
      		         $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
                     $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];
                     
                     //check if the record exists, then check if a valid id (not null or empty) is passed in the request, 
                     //then compare the two. if they match make the assignment
                     
                     if(!empty($this->request->data['TutorProfile']['id']) && 
                                $this->request->data['TutorProfile']['id'] != null) {
                            $id = $this->request->data['TutorProfile']['id'];
                     }
                     
                     // if(!empty($id) && $id != null) { 
                              // There should not be a record yet. So there must not be a pk
                             // throw new NotFoundException(__('Invalid Request'));
                      // }
                        $postData = $this->request->data;
                       // debug($this->request->data); die(); 
                           $mstatus = $postData['TutorProfile']['mktplace_status'];
                        if(!$mstatus) {
                                     
                             // debug($this->request->data); die();      
                           $this->request->data['TutorProfile']['mktplace_status'] = 1;
                           $postData['TutorProfile']['mkt_place_rules'] = 1;
                           $this->request->data['TutorProfile']['profile_status_count']++ ;
                       }
                 

                        $this->{$this->modelClass}->TutorProfile->set(array(
                                  'mkt_place_rules' => $this->request->data['TutorProfile']['mkt_place_rules']
                                  ));
                                  
                        if ($this->{$this->modelClass}->TutorProfile->validates(
							  				array('fieldList' => array(
							  					'mkt_place_rules'))))
                         {
	    					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
                              
	    					   {
	    					        //debug('test'); die();
	    							$this->Session->setFlash
	    									(
	    												sprintf(__d('users', 'Marketplace rules Agreement has been successfully saved.')),
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
	    				 } else {
	    				     $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'You must agree to the terms and conditions. Please read the rules of the Online tutoring Market Place and check the box at the bottom of screen')),
	 						     					'default',
	 						     					 array('class' => 'alert error-message')
									     	);
                                             //debug($this->{$this->modelClass}->TutorProfile->validationErrors);    die(); 
	    				 }
	                }
	          }
                    
                $mktPlaceModel =  $this->{$this->modelClass}->TutorProfile->find('first', array(
					         'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
			    if(!empty($mktPlaceModel)) {
			      	//debug($mktPlaceModel); die();
   	                $this->set('prpk',   h($mktPlaceModel['TutorProfile']['id']));
                    $this->set('mkt_place_rules',   h($mktPlaceModel['TutorProfile']['mkt_place_rules']));
                    $this->set('profile_status_count',    h($mktPlaceModel['TutorProfile']['profile_status_count']));
                    $this->set('mktplace_status',    h($mktPlaceModel['TutorProfile']['mktplace_status']));
                    
   	                $mktPlaceStatus = $mktPlaceModel['TutorProfile']['mktplace_status'];

   	                if(!empty($mktPlaceStatus) && $mktPlaceStatus) {
   	                   return $this->redirect(array('action' => 'basic_profile'));
   	                }
   	             }
  }
  
  public function manage_market_place() {
     $this->layout='tutor';

	         if($this->request->is('post')) {
	         $id = null;
	         $postData = array();

             if (!empty($this->request->data)) {
	    	    // debug($this->request->data); die();
      		         $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
                     $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];
                     $id = $this->request->data['TutorProfile']['id'];
                     
                     // if(!empty($id) && $id != null) { 
                              // There should not be a record yet. So there must not be a pk
                             // throw new NotFoundException(__('Invalid Request'));
                      // }
                        $postData = $this->request->data;
                        $postData['TutorProfile']['mktplace_status'] = 1;
                        $postData['TutorProfile']['mkt_place_rules'] = 1;
                        $postData['TutorProfile']['profile_status_count']++;

                        $this->{$this->modelClass}->TutorProfile->set(array(
                                  'mktplace_rules' => $this->request->data['TutorProfile']['mktplace_rules']
                                  ));
                                  
                        if ($this->{$this->modelClass}->TutorProfile->validates(
							  				array('fieldList' => array(
							  					'mktplace_rules'))))
                         {
	    					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
                              
	    					   {
	    					        //debug('test'); die();
	    							$this->Session->setFlash
	    									(
	    												sprintf(__d('users', 'Marketplace rules has been successfully saved.')),
	    											   'default',
	    												array('class' => 'alert alert-success')
	    									 );

	    								return $this->redirect(array('action' => 'manage_basic_profile'));
	    					  } else {
	    					      $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'Something went wrong.')),
	 						     					'default',
	 						     					 array('class' => 'alert alert-warning')
	    									 );
	    					  }
	    				 } else {
	    				     $this->Session->setFlash
	 						     		(
	 						     					sprintf(__d('users', 'You must agree to the Online Marketplace rules. Please read the rules of the Online tutoring Market Place and check the box at the bottom of screen')),
	 						     					'default',
	 						     					 array('class' => 'alert error-message')
									     	);
                                             //debug($this->{$this->modelClass}->TutorProfile->validationErrors);    die(); 
	    				 }
	                }
	          }
                    
                $mktPlaceModel =  $this->{$this->modelClass}->TutorProfile->find('first', array(
					         'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
			    if(!empty($mktPlaceModel)) {
			      	//debug($mktPlaceModel); die();
   	                $this->set('prpk',   h($mktPlaceModel['TutorProfile']['id']));
                    $this->set('mkt_place_rules',   h($mktPlaceModel['TutorProfile']['mkt_place_rules']));
                    $this->set('profile_status_count',    h($mktPlaceModel['TutorProfile']['profile_status_count']));
                    
   	                $mktPlaceStatus = $mktPlaceModel['TutorProfile']['mktplace_status'];

   	                if(!empty($mktPlaceStatus) && $mktPlaceStatus) {
   	                   return $this->redirect(array('action' => 'basic_profile'));
   	                }
   	             }
  }

public function basic_profile() {
   $this->layout='tutor';
   if($this->request->is('post')) {
         $id = null;
   	     if (!empty($this->request->data)) {
   	                // debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			          if(!empty($this->request->data['TutorProfile']['id'])) {
   			                $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)
   			          }
                      
                     if (empty($id) || !($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'), 
                                'TutorProfile.id'  => $id))))) {
                                    
                          //error flash message
                          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
                   											   'default',
                   												array('class' => 'alert error-message')
                							       );
                          $this->redirect(array('action' => 'market_place'));
                                                    
                     }
                    
                     if ($data['TutorProfile']['id'] != $id) {                               
                           //Blackhole Request
                            throw new BadRequestException();
                     }  


                      $this->{$this->modelClass}->TutorProfile->set(array(
                                 // 'first_name' => $this->request->data['TutorProfile']['first_name'],
                                 // 'last_name' => $this->request->data['TutorProfile']['last_name'],
                                  'gender' => $this->request->data['TutorProfile']['gender'],
                                  'education' => $this->request->data['TutorProfile']['education'],
                                  'degree' => $this->request->data['TutorProfile']['degree'],
                                  'school' => $this->request->data['TutorProfile']['school'],
                                  
                                  'address_1' => $this->request->data['TutorProfile']['address_1'],
                                  'address_2' => $this->request->data['TutorProfile']['address_2'],
                                  'city' => $this->request->data['TutorProfile']['city'],
                                  'state' => $this->request->data['TutorProfile']['state'],
                                  'state_abbr' => $this->request->data['TutorProfile']['state'],
                                  'zip_code' => $this->request->data['TutorProfile']['zip_code'],
                                   
                                  'maddress_1' => $this->request->data['TutorProfile']['maddress_1'],
                                  'maddress_2' => $this->request->data['TutorProfile']['maddress_2'],
                                  'mcity' => $this->request->data['TutorProfile']['mcity'],
                                  'mstate' => $this->request->data['TutorProfile']['mstate'],
                                  'mstate_abbr' => $this->request->data['TutorProfile']['mstate'],
                                  'mzip_code' => $this->request->data['TutorProfile']['mzip_code'],                                  
                                  'primary_phone' => $this->request->data['TutorProfile']['primary_phone'],             
                                  'pphone_type' => $this->request->data['TutorProfile']['pphone_type'],
                                  'secondary_phone' => $this->request->data['TutorProfile']['secondary_phone'],
                                  'sphone_type' => $this->request->data['TutorProfile']['sphone_type']
                                  
                       ));
                      if ($this->{$this->modelClass}->TutorProfile->validates(
                              array('fieldList' => array(
                                     // 'first_name', 'last_name',
                                      'education',
                                      'gender',                                     
                                      'degree',
                                      'school',
                                      'address_1',
                                      'city',
                                      'state',
                                      'zip_code',
                                      'maddress_1',
									  'mcity',
									  'mstate',
                                      'mzip_code',
                                      'primary_phone',
                                      'pphone_type'
                                      //'secondary_phone',
                                      //'sphone_type'
                                      ))))
                   {
                         $postData = $this->request->data;
                         $status = $postData['TutorProfile']['basicProfile_status'];
                         if(!$status ) {
                                        
                            $postData['TutorProfile']['basicProfile_status'] = 1;
                            $postData['TutorProfile']['profile_status_count']++;
                       }
   					     if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
   					        {
									$this->Session->setFlash
											(
														sprintf(__d('users', 'Basic Profile has been successfully saved.')),
													   'default',
														array('class' => 'alert alert-success')
											 );

								       return $this->redirect(array('action' => 'public_profile'));

   					         } else {
   					            $this->Session->setFlash
											(
														sprintf(__d('users', 'Basic Profile has NOT been saved. Please try again!!')),
													   'default',
														array('class' => 'alert error-message')
											 );

								       //return $this->redirect(array('action' => 'basic_profile'));
   					         }
                  } else {
                    //$this->Session->setFlash
 									//(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 											//	sprintf(__d('users', '%s', h($this->{$this->modelClass}->TutorProfile->validationErrors))),
 											  // 'default',
 											//	array('class' => 'alert error-message')
 								//	 );
                                     
                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please correct all errors below and resubmit form', true)),
                                               'default',
 												array('class' => 'alert error-message')
                                     
                                        );
                  }
               }
         }


             //set the primary key of preference table in the view and send it back as a hidden field
            $basicProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
		   					         'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
   	        //debug($this->request->data[$this->modelClass]['first_name']); die();
           $bProfileStatus = $basicProfileModel['TutorProfile']['basicProfile_status'];
           $mktPlaceStatus = $basicProfileModel['TutorProfile']['mktplace_status'];

           if(empty($mktPlaceStatus) ||  !$mktPlaceStatus) {
                    return $this->redirect(array('action' => 'market_place'));

           } else if(!empty($bProfileStatus) && $bProfileStatus) {
   	               return $this->redirect(array('action' => 'public_profile'));
           }

   	      if(!empty($basicProfileModel)) {
   	                //debug($basicProfileModel); die();
   	                $this->set('prpk',   h($basicProfileModel['TutorProfile']['id']));
   	                $this->set('fn',     h($this->Session->read('username')));
   	                $this->set('ln',     h($this->Session->read('lastname')));
                    $this->set('gn',     h($basicProfileModel['TutorProfile']['gender']));
                    
   	                $this->set('ed',     h($basicProfileModel['TutorProfile']['education']));
   	                $this->set('degree', h($basicProfileModel['TutorProfile']['degree']));
   	                $this->set('school', h($basicProfileModel['TutorProfile']['school']));

   	                $this->set('add1',   h($basicProfileModel['TutorProfile']['address_1']));
   	                $this->set('add2',   h($basicProfileModel['TutorProfile']['address_2']));
   	                $this->set('city',   h($basicProfileModel['TutorProfile']['city']));
   	                $this->set('st',     h($basicProfileModel['TutorProfile']['state']));
   	                $this->set('zip',    h($basicProfileModel['TutorProfile']['zip_code']));

   	                $this->set('madd1',   h($basicProfileModel['TutorProfile']['maddress_1']));
					$this->set('madd2',   h($basicProfileModel['TutorProfile']['maddress_2']));
					$this->set('mcity',   h($basicProfileModel['TutorProfile']['mcity']));
					$this->set('mst',     h($basicProfileModel['TutorProfile']['mstate']));
   	                $this->set('mzip',    h($basicProfileModel['TutorProfile']['mzip_code']));

   	                $this->set('pp',     h($basicProfileModel['TutorProfile']['primary_phone']));
   	                $this->set('sp',     h($basicProfileModel['TutorProfile']['secondary_phone']));
   	                $this->set('mhop',   h($basicProfileModel['TutorProfile']['pphone_type']));
   	                $this->set('mhos',   h($basicProfileModel['TutorProfile']['sphone_type']));
                    $this->set('profile_status_count',    h($basicProfileModel['TutorProfile']['profile_status_count']));
                    $this->set('bps',    h($basicProfileModel['TutorProfile']['basicProfile_status']));

             }



}


public function public_profile() {
   $this->layout='tutor';

        if($this->request->is('post')) {
        $id = null;
        $postData = array();

   	     if (!empty($this->request->data)) {
   	     //debug($this->request->data); die();
   	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
   			          $this->request->data['TutorProfile']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

   			             if(!empty($this->request->data['TutorProfile']['id'])) {
					            $id = $this->request->data['TutorProfile']['id'];     //the Pk of Associated model (TutorProfile)
					     }
                         
                         
                     if (!($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'), 
                                'TutorProfile.id'  => $id))))) {
                                    
                          //error flash message
                          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
                   											   'default',
                   												array('class' => 'alert error-message')
                							       );
                          $this->redirect(array('action' => 'market_place'));
                                                    
                     }
                    
                     if ($data['TutorProfile']['id'] != $id) {                               
                           //Blackhole Request
                            throw new BadRequestException();
                     }  

                         
                          $this->{$this->modelClass}->TutorProfile->set(array(
                                  'hourly_rate' => $this->request->data['TutorProfile']['hourly_rate'],
                                  'travel_radius' => $this->request->data['TutorProfile']['travel_radius'],
                                  'cancel_policy' => $this->request->data['TutorProfile']['cancel_policy'],
                                  'title' => $this->request->data['TutorProfile']['title'],
                                  'description' => $this->request->data['TutorProfile']['description']
                                  
                                  ));

					     if ($this->{$this->modelClass}->TutorProfile->validates(
					                               array('fieldList' => array(
					                                       'hourly_rate',
					                                       'travel_radius',
					                                       'cancel_policy',
					                                       'title',
					                                       'description'))))

                        {
                              $postData = $this->request->data;
                              $bpstatus = $postData['TutorProfile']['publicProfile_status'];
                              if(!$bpstatus) {
                                        
                                $postData['TutorProfile']['publicProfile_status'] = 1;
                                $postData['TutorProfile']['profile_status_count']++;
                       }
							 if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
									   {
											$this->Session->setFlash
													(
																sprintf(__d('users', 'Public Profile has been successfully saved.')),
															   'default',
																array('class' => 'alert alert-success')
													 );

											return $this->redirect(array('action' => 'independent_contractor__agreement'));
										 } else {
										    $this->Session->setFlash
											(
														sprintf(__d('users', 'Public Profile has Not been saved.')),
													   'default',
														array('class' => 'alert alert-success')
											 );

								           return $this->redirect(array('action' => 'public_profile'));
                                          
										 }

   					 } else {
                                     
                          $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please correct all errors belwo and Resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')
                                     
                                        );
   					 }
               } 
         } 

          //set the primary key of preference table in the view and send it back as a hidden field

           $publicProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
		  		   					         'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));

          $pProfileStatus = $publicProfileModel['TutorProfile']['publicProfile_status'];
          $bProfileStatus = $publicProfileModel['TutorProfile']['basicProfile_status'];

          if(empty($bProfileStatus) || !$bProfileStatus) {
                     return $this->redirect(array('action' => 'basic_profile'));
          } else if(!empty($pProfileStatus) && $pProfileStatus) {
                     return $this->redirect(array('action' => 'independent_contractor__agreement'));
          }
   	      if(!empty($publicProfileModel)) {
   	                $this->set('prpk',   $publicProfileModel['TutorProfile']['id']);
                    $this->set('hr',     h($publicProfileModel['TutorProfile']['hourly_rate']));
   	                $this->set('tr',     h($publicProfileModel['TutorProfile']['travel_radius']));
   	                $this->set('cp',     h($publicProfileModel['TutorProfile']['cancel_policy']));

   	                $this->set('title',         h($publicProfileModel['TutorProfile']['title']));
   	                $this->set('description',  h($publicProfileModel['TutorProfile']['description']));
                    $this->set('pps',    h($publicProfileModel['TutorProfile']['publicProfile_status']));
                    $this->set('profile_status_count',    h($publicProfileModel['TutorProfile']['profile_status_count']));



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
                            
                            
                             if ( empty($id) ||!($data = $this->{$this->modelClass}->TutorProfile->find(
                                                        'first', array(
                                                        'conditions' => array(
                                                            'TutorProfile.tutor_id' => $this->Auth->user('id'), 
                                                            'TutorProfile.id'  => $id))))) 
                                 {
                                                                
                                      //error flash message
                                      $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
                               											   'default',
                               												array('class' => 'alert error-message')
                            							       );
                                      $this->redirect(array('action' => 'independent_contractor__agreement'));
                                                                
                                 }
                                
                                 if ($data['TutorProfile']['id'] != $id) {                               
                                       //Blackhole Request
                                        throw new NotFoundException(__('Invalid ICA'));;
                                 }  


	    			            //The signature consists of first and last name concatenated together
	                          
	                           //debug($postData); die();
                              
                             $this->{$this->modelClass}->TutorProfile->set(array(
                                  'terms_of_use' => $this->request->data['TutorProfile']['terms_of_use'],
                                  'work_auth' => $this->request->data['TutorProfile']['work_auth'],
                                  'first_name' => $this->request->data['TutorProfile']['first_name'],
                                  'last_name' => $this->request->data['TutorProfile']['last_name']));
                               
                               
                   if ($this->{$this->modelClass}->TutorProfile->validates(
							        array('fieldList' => array(
							          'terms_of_use',
							          'work_auth',
							          'first_name',
                                      'last_name'))))
                              {
                                       $postData['TutorProfile']['tutor_signature'] = $postData['TutorProfile']['first_name'].$postData['TutorProfile']['last_name'];
	                                   if(!empty($postData['TutorProfile']['tutor_signature'])) {
	                                           $postData['TutorProfile']['signed_agreement'] = 1;
                                        }

                                      //$postData = $this->request->data;
                                      $ica_status = $postData['TutorProfile']['ica_status'];
                                      if(!$ica_status) {
                                                
                                        $postData['TutorProfile']['ica_status'] = 1;
                                        $postData['TutorProfile']['profile_status_count']++;
                                      }
	                         
							       if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $postData))
								  	  {
								  	     $this->Session->setFlash(sprintf(__d('users', 'Agreement details has been successfully saved.')),
								  	    							'default',
								  	    							array('class' => 'alert alert-success')
								  	    						);
								  	        return $this->redirect(array('action' => 'add_subjects'));
	    					          } else {
	    					              $this->Session->setFlash(sprintf(__d('users', 'Agreement details save failed.')),
								  	    							'default',
								  	    							array('class' => 'alert error-message')
								  	    						);
                                          
	    					          }
							  
	                     } else {
                                     
                              $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all errors below and Re-Submit form')),
                                               'default',
 												array('class' => 'alert error-message')
                                     
                                        );
	                          } 
	               }
             }

             //set the primary key of preference table in the view and send it back as a hidden field


          $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
		  		  		   					         'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
           $icaStatus = $tProfileModel['TutorProfile']['ica_status'];
           $pProfileStatus = $tProfileModel['TutorProfile']['publicProfile_status'];

           if(empty($pProfileStatus) || !$pProfileStatus ) {
                    return $this->redirect(array('action' => 'public_profile'));
            }else if(!empty($icaStatus) && $icaStatus) {
                    return $this->redirect(array('action' => 'add_subjects'));
            }

            if(!empty($tProfileModel)) {
   	                   $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
   	                   $this->set('fn',     h($this->Session->read('username')));
   	                   $this->set('ln',     h($this->Session->read('lastname')));
                       $this->set('ica_status',    h($tProfileModel['TutorProfile']['ica_status']));
                       $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
   	           }
   }

public function add_subjects() {
    $this->layout='tutor';
	$postData = array();
    $category = new Categorie();

if($this->request->is('post')) { //1
 //debug($this->request->data); die();

		 //debug($this->request->data);   die();
		 //foreach ($this->request->data['TutorSubject'] as $key => $value) {
			//	$postData['TutorSubject']['$key'] = $key;
				//echo $postData['TutorSubject']['$key'] ."<br />";
			 // echo "$key <br />";
			//	next($this->request->data);
		 //}

  if(!empty($this->request->data)) { //2
     $conditions = array(
	     'tutor_id' => $this->Auth->user('id'),
		 'name' => $this->request->data['TutorSubjectCat']['category'],
         'category_id' => $this->request->data['TutorSubjectCat']['category_id']
	 );
	 if (!$this->{$this->modelClass}->TutorCategorie->hasAny($conditions)) {
         $this->{$this->modelClass}->TutorCategorie->set($conditions);
         $this->{$this->modelClass}->TutorCategorie->save() ;
      }

	  if ($this->{$this->modelClass}->TutorCategorie->hasAny($conditions)){  //3
		  $categoryRow = $this->{$this->modelClass}->TutorCategorie->find
			   		     	      ( 'first',
			   		     	         array('field' => 'tutor_id',
			   		     	        'value' => $this->Auth->user('id')
		     	                 ));

		  $cat_id =  $categoryRow['TutorCategorie']['id'];
	      foreach ($this->request->data['TutorSubject'] as $key => $value) { //4
            // debug($value); die();
           //  if ($this->{$this->modelClass}->TutorSubject->validates(array
  	                               //  ('fieldList' => array(
  	                                //  'TutorSubject' .'.' . $key
  	                                //  ))))
  	       // {

             if($value == '1') { // Even though all Subjects on the Form come through, we only store the ones selected (checked) by user
				 foreach ($this->request->data['TutorSubjectIds'] as $cle => $val) {
					    //$cle = strtolower(substr($key, 0, 5));
   	                    $subject_row = $category->{'Subject'}->find('all', array('conditions' => array('Subject'.'.'.'name' => $key)));
                       // debug($subject_row); die();
                        $cle = $subject_row[0]['Subject']['subject_id'];
					    //debug($cle); die();
						$conditions = array(
								'tutor_id' => $this->Auth->user('id'),
								'tutor_categorie_id' => $cat_id,
								'subject_name' => $key,
								'subject_id' => $cle, // french for key --:)
								'subject_category_name' => $this->request->data['TutorSubjectCat']['category'],
								'subject_category_id' => $this->request->data['TutorSubjectCat']['category_id'],
								'delete_status' => 'N',
								'approval_status' => 'N/A',
								'searchable_status' => 0,
								'credentials_status' => 0   //0=Not submiited, 1="Submitted"
						 );
						 unset($this->request->data['TutorSubjectIds'][$cle]); //already processed, removed from array.
						 break;  //we are breaking cause we need to process with the conditions we just built first
				   } // end foreach

				   if (!$this->{$this->modelClass}->TutorSubject->hasAny($conditions)){

				        $this->{$this->modelClass}->TutorSubject->create();
						$this->{$this->modelClass}->TutorSubject->set($conditions);
                         //debug($this->request->data); die();
						if($this->{$this->modelClass}->TutorSubject->save($this->request->data, array('validate' => false))) {
							next($this->request->data);
						} else {
								 $this->Session->setFlash(sprintf(__d('users', 'Subjects have NOT been Saved.')),
																	  'default',
																	   array('class' => 'alert alert-warming')
														 );
							   return $this->redirect(array('action' => 'add_subjects'));
						 }


					} else {
							// throw new CakeException(__d('cake_dev', 'Fail to save!!!.'));
							  $this->Session->setFlash(sprintf(__d('users', 'One or more subjects have previously been added and were skipped!!! ')),
																	  'default',
																	   array('class' => 'alert alert-warning')
													  );
							return $this->redirect(array('action' => 'add_subjects'));
					  }
				} // end if ($value == 1)
			}  // end foreach
		 }
	   }

	    $this->Session->setFlash(sprintf(__d('users', 'Subjects have been successfully saved.')),
	   																	'default',
	   																	 array('class' => 'alert alert-success')
	   												  );
		return $this->redirect(array('action' => 'manage_subjects'));
	 }


	   $options['joins'] = array(
	       array('table' => 'tutor_subjects',
	           'alias' => 'TutorSubject',
	           'type' => 'LEFT',
	           'foreignKey' => false,
	           'conditions' => array(
	               'TutorSubject.subject_id = Subject.subject_id',
	           ),
	       )
	   );

	    $options2['joins'] = array(
	   	       array('table' => 'subjects',
	   	           'alias' => 'Subject',
	   	           'type' => 'RIGHT',
	   	           'conditions' => array(
	   	               'Subject.subject_id != TutorSubject.subject_id',
	   	           ),
	   	       )
	   );

       $cats = $category->find('all', array('order' => array('Categorie.name ASC')));

	   //$category->find('all', array('conditions' => array('name' => 'Math')));  //$category->find('all', array('conditions' => array('name' => 'Math')));
	   $this->set('categories',$cats);
	   $subjects = $category->{'Subject'}->find('all', array('order' => array('Subject.name ASC')));

	  // $subjects = $category->{'Subject'}->find('all', $options);
	   //$subjects = $this->{$this->modelClass}->TutorSubject->find('all', $options2);

       //debug($subjects); die();
       $i=0;
       $viewData = $subjects; //();
       foreach($subjects as $subject) {
			$conditions = array(
				 'tutor_id' => $this->Auth->user('id'),
				 'subject_name' => $subject['Subject']['name'],
				 'subject_id' => $subject['Subject']['subject_id']
			);
			//debug($conditions); die();
		  if($this->{$this->modelClass}->TutorSubject->hasAny($conditions)) {
				//debug($conditions); die();
				//unset($subjects[$subject['Subject']['name']]);
				//unset($subjects[$subject['Subject']['subject_id']]);
				//debug($viewData[$i]['Subject']['name']);

				unset($viewData[$i]['Subject']);
				unset($viewData[$i]['Subject']);

				//debug($viewData); die();
				//$subjects = $viewData;
		  }
		  $i++;
		  $subjects = $viewData;
	  }
	  //debug($viewData); die();
	  $this->set('subjects', $subjects);
      
      


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
                     
                     
                     if (!($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'), 
                                'TutorProfile.id'  => $id))))) {
                                    
                          //error flash message
                           $this->Session->setFlash(sprintf(__d('users', '<center>You have attempted to update your profile before setting it up!!!! Profile must be set up in the following order:<br /><br />
                          1.<b>Market Place Rules</b> (Read & Sign) <br /> 2. <b>Basic Profile</b> <br /> 3.<b>Public Profile</b> <br /> 4.<b>Tutor Contract Agreement (Read & Sign)</b> <br /><br /> Click on <b>Market Place Rules</b> below to start.</center>')),
                   											   'default',
                   												array('class' => 'alert error-message'));
                                                                
                          $this->redirect(array('action' => 'manage_basic_profile'));
                                                    
                     }
                    
                     if ($data['TutorProfile']['id'] != $id) {                               
                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Profile'));
                     }  

                  $this->{$this->modelClass}->TutorProfile->set(array(
                                  'gender' => $this->request->data['TutorProfile']['gender'],
                                  'education' => $this->request->data['TutorProfile']['education'],
                                  'degree' => $this->request->data['TutorProfile']['degree'],
                                  'school' => $this->request->data['TutorProfile']['school'],
                                  
                                  'address_1' => $this->request->data['TutorProfile']['address_1'],
                                  'address_2' => $this->request->data['TutorProfile']['address_2'],
                                  'city' => $this->request->data['TutorProfile']['city'],
                                  'state' => $this->request->data['TutorProfile']['state'],
                                  'state_abbr' => $this->request->data['TutorProfile']['state'],
                                  'zip_code' => $this->request->data['TutorProfile']['zip_code'],
                                   
                                  'maddress_1' => $this->request->data['TutorProfile']['maddress_1'],
                                  'maddress_2' => $this->request->data['TutorProfile']['maddress_2'],
                                  'mcity' => $this->request->data['TutorProfile']['mcity'],
                                  'mstate' => $this->request->data['TutorProfile']['mstate'],
                                  'mstate_abbr' => $this->request->data['TutorProfile']['mstate'],
                                  'mzip_code' => $this->request->data['TutorProfile']['mzip_code'],
                                  
                                  'primary_phone' => $this->request->data['TutorProfile']['primary_phone'],
                                  'secondary_phone' => $this->request->data['TutorProfile']['secondary_phone'],
                                  'pphone_type' => $this->request->data['TutorProfile']['pphone_type'],
                                  'sphone_type' => $this->request->data['TutorProfile']['sphone_type']
                                  
                                  
           ));
           
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array(
                                                            'gender',
					                                        'education',
                                                            'degree',
                                                            'school',
                                                            'address_1',
                                                            'city','state',
                                                            'zip_code',
                                                            'maddress_1',
                                                            'mcity',
                                                            'mstate',
                                                            'mzip_code',
                                                            'primary_phone',
                                                            'pphone_type'
                                                            
                                                            ))))   
                {
                    
                      //$postData = $this->request->data;
                      
                       
                       //$postData = $this->request->data;
                         $status = $this->request->data['TutorProfile']['basicProfile_status'];
                         if(!$status ) {
                                        
                            $this->request->data['TutorProfile']['basicProfile_status'] = 1;
                            $this->request->data['TutorProfile']['profile_status_count']++;
                       }
   					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Basic Profile has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
                       if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) && 
                                         !$this->request->data['TutorProfile']['publicProfile_status'])
                             
                             { //&& basic_profile is already taken care of
                                        $this->redirect(array('action' => 'manage_public_profile'));
                             
                             } else if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) && 
                                         $this->request->data['TutorProfile']['publicProfile_status']) { 
                                            
                                            $this->redirect(array('action' => 'independent_contractor__agreement'));
                             }
   					  } else {
   					        $this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 												sprintf(__d('users', 'Save Failed')),
 											   'default',
 												array('class' => 'alert error-message')
 									 );
   					    
   					  }
               } else {
                  
                                     
                     $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct all Errors below and resubmit form!!')),
                                               'default',
 												array('class' => 'alert error-message')
                                     
                                        );
                
               }
         }
    }


             //set the primary key of preference table in the view and send it back as a hidden field
   	     // $tProfileModel = $this->{$this->modelClass}->TutorProfile->find
   	                 // (
   	                   // 'first',
   	                   //  array('field' => 'tutor_id',
   	                   // 'value' => $this->Auth->user('id')
   	                // ));

          $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
		 		  		  		   					         'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
          $this->set('fn',     h($this->Session->read('username')));
          $this->set('ln',     h($this->Session->read('lastname')));
          
   	      if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
                    $this->set('gn',     h($tProfileModel['TutorProfile']['gender']));
                    
   	                $this->set('ed',     h($tProfileModel['TutorProfile']['education']));
   	                $this->set('degree', h($tProfileModel['TutorProfile']['degree']));
   	                $this->set('school', h($tProfileModel['TutorProfile']['school']));

   	                $this->set('add1',   h($tProfileModel['TutorProfile']['address_1']));
   	                $this->set('add2',   h($tProfileModel['TutorProfile']['address_2']));
   	                $this->set('city',   h($tProfileModel['TutorProfile']['city']));
   	                $this->set('st',     h($tProfileModel['TutorProfile']['state']));
   	                $this->set('zip',    h($tProfileModel['TutorProfile']['zip_code']));

   	                $this->set('madd1',   h($tProfileModel['TutorProfile']['maddress_1']));
					$this->set('madd2',   h($tProfileModel['TutorProfile']['maddress_2']));
					$this->set('mcity',   h($tProfileModel['TutorProfile']['mcity']));
					$this->set('mst',     h($tProfileModel['TutorProfile']['mstate']));
   	                $this->set('mzip',    h($tProfileModel['TutorProfile']['mzip_code']));

   	                $this->set('pp',     h($tProfileModel['TutorProfile']['primary_phone']));
   	                $this->set('sp',     h($tProfileModel['TutorProfile']['secondary_phone']));
   	                $this->set('mhop',   h($tProfileModel['TutorProfile']['pphone_type']));
   	                $this->set('mhos',   h($tProfileModel['TutorProfile']['sphone_type']));
                    
                    $this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));
                    $this->set('ica',    h($tProfileModel['TutorProfile']['ica_status']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
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
                      
                     if (!($data = $this->{$this->modelClass}->TutorProfile->find(
                            'first', array(
                            'conditions' => array(
                                'TutorProfile.tutor_id' => $this->Auth->user('id'), 
                               // 'TutorProfile.publicProfile_status'  => 1,
                                'TutorProfile.id'  => $id))))) {
                                    
                          $this->Session->setFlash(sprintf(__d('users', '<center>You have attempted to update your profile before setting it up!!!! Profile must be set up in the following order:<br /><br />
                          1.<b>Market Place Rules</b> (Read & Sign) <br /> 2. <b>Basic Profile</b> <br /> 3.<b>Public Profile</b> <br /> 4.<b>Tutor Contract Agreement (Read & Sign)</b> <br /><br /> Click on <b>Market Place Rules</b> below to start.</center>')),
                   											   'default',
                   												array('class' => 'alert error-message'));
                          
                          $this->redirect(array('action' => 'manage_public_profile'));
                                                    
                     } 
                    
                     if ($data['TutorProfile']['id'] != $id) {                               
                           //Blackhole Request
                            throw new NotFoundException(__('Invalid Profile'));
                     }  
                      $this->{$this->modelClass}->TutorProfile->set(array(
                                  'hourly_rate' => $this->request->data['TutorProfile']['hourly_rate'],
                                  'travel_radius' => $this->request->data['TutorProfile']['travel_radius'],
                                  'cancel_policy' => $this->request->data['TutorProfile']['cancel_policy'],
                                  'title' => $this->request->data['TutorProfile']['title'],
                                  'description' => $this->request->data['TutorProfile']['description']
                                  
                                  ));
                                 
                       
                if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array(
					                                        'hourly_rate','travel_radius',
                                                            'cancel_policy','title','description'))))   
                {   
                       
                       
                        //$postData = $this->request->data;
                         $status = $this->request->data['TutorProfile']['publicProfile_status'];
                         if(!$status ) {
                                        
                            $this->request->data['TutorProfile']['publicProfile_status'] = 1;
                            $this->request->data['TutorProfile']['profile_status_count']++;
                       }
                       
   					  if($this->{$this->modelClass}->TutorProfile->saveProfile($id, $this->request->data))
   					   {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Public Profile has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
                             if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) && 
                                         !$this->request->data['TutorProfile']['basicProfile_status'])
                             
                             { //&& basic_profile is already taken care of
                                        $this->redirect(array('action' => 'manage_basic_profile'));
                             
                             } else if( ($this->request->data['TutorProfile']['profile_status_count'] < 4 ) && 
                                         $this->request->data['TutorProfile']['basicProfile_status']) { 
                                            
                                            $this->redirect(array('action' => 'independent_contractor__agreement'));
                             }
                             
   					  } else {
   					     $this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
										        sprintf(__d('users', 'Public Profile Save Failed. Please try Again!!')),
 											   'default',
 												array('class' => 'alert error-message')
 									 );
                        
   					  }
                  } else {
                        
                                     
                          $this->Session->setFlash
                                        (
                                                sprintf(__d('users', 'Please Correct All Errors below and Resubmit Form')),
                                               'default',
 												array('class' => 'alert error-message')
                                     
                                        );
                     }
                      
               }
         }


             //set the primary key of preference table in the view and send it back as a hidden field
   	      //$tProfileModel = $this->{$this->modelClass}->TutorProfile->find
   	                  //(
   	                  //  'first',
   	                   //  array('field' => 'tutor_id',
   	                   // 'value' => $this->Auth->user('id')
   	                // ));
   	      $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
			  		  		  	'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
   	      if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
   	                $this->set('hr',     h($tProfileModel['TutorProfile']['hourly_rate']));
   	                $this->set('tr',     h($tProfileModel['TutorProfile']['travel_radius']));
   	                $this->set('cp',     h($tProfileModel['TutorProfile']['cancel_policy']));

   	                $this->set('title',         h($tProfileModel['TutorProfile']['title']));
   	                $this->set('description',  h($tProfileModel['TutorProfile']['description']));
                    
                    $this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));
                    $this->set('ica',    h($tProfileModel['TutorProfile']['ica_status']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
                    //debug($tProfileModel['TutorProfile']['profile_status_count']); die();
                    
                    
             }

}

public function manage_photos() {
    
    $this->layout='tutor';
    $id = null;
   // debug($_SERVER['DOCUMENT_ROOT']) ; die();
  if ($this->request->is('post')) {
        //debug($this->request->data); die();
       
       // move_uploaded_file(
       // $this->data['TutorImage']['image']['tmp_name'],  
       // $_SERVER['DOCUMENT_ROOT'] . '/files/uploads' . $this->data['TutorImage']['image']['name']
       // );
               
        $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
        $this->request->data['TutorImage']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

          if(!empty($this->request->data['TutorImage']['id']))
                  $id = $this->request->data['TutorImage']['id'];     //the Pk of Associated model (TutorImage)
          
              
         $conditions = array(
				'tutor_id' => $this->Auth->user('id'),
                //'image' => '/files/uploads/'. $this->request->data['TutorImage']['image']['name']
                 //'image' => $this->request->data['TutorImage']['image']['name']
                'image' => 'https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimg/'. $this->request->data['TutorImage']['image']['name']							
			);	
           // https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimg/Cmum.jpg
            //https://s3-us-west-2.amazonaws.com/www.daraji.com/images/users/Chrysanthemum.jpg					
				   
   if (!$this->{$this->modelClass}->TutorImage->hasAny($conditions)){
        $this->request->data['TutorImage']['created'] = date('Y-m-d H:i:s');
        $this->request->data['TutorImage']['status'] = 0;
        $this->request->data['TutorImage']['featured'] = 0;
        
       for($i=1; $i<5; $i++) { //do not like this but will do for now. There has got to be a better way
            $data = $this->{$this->modelClass}->TutorImage->find(
                            'first', array(
                    		//'order' => array('TutorImage.data_id' => 'DESC'),
                            'conditions' => array(
                                'TutorImage.tutor_id' => $this->Auth->user('id'), 
                                'TutorImage.data_id'  => $i))); //,
              		            //'limit' => 1));
        	if (!$data) {
        		$this->request->data['TutorImage']['data_id'] = $i; //$data['TutorImage']['data_id'] + 1;
                break;
        	} else {	   
               $this->request->data['TutorImage']['data_id'] = $data['TutorImage']['data_id']; //1;
        	}
            
       }
           
       $this->{$this->modelClass}->TutorImage->create();
       //$this->{$this->modelClass}->TutorImage->set($conditions);
       if ($this->{$this->modelClass}->TutorImage->save($this->request->data)) {

            {
   							$this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile Photo has been successfully saved.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
            }
       }
       
    } else {
        
         //debug($this->Recipe->validationErrors);
        
        $this->Session->setFlash
   									(
   												sprintf(__d('users', 'A photo of same name already exists.')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
        }
       
       
    }
    
     // $images = $this->{$this->modelClass}->TutorImage->find(
                          // 'all', array('conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id'))));
      //$id_array =  array(0,1);
      //$order = "FIELD(id,". implode(“, “, $id_array).")";
      $images = $this->{$this->modelClass}->TutorImage->find(
                          'all', array(
                           'order' => array('TutorImage.status' => 'DESC', 'TutorImage.featured' => 'DESC' ),
                           'conditions' => array('TutorImage.tutor_id' => $this->Auth->user('id')
                           //'order' => array('TutorImage.status' => 'DESC')
                           )));
                           
                          // array('order' => array('Subject.name ASC')
                           
         
      $this->set('images',$images); 
      //debug($images); die();
                          
      // if(!empty($tProfilePhotoModel)) {
        //   $this->set('ppk',  $tProfilePhotoModel['TutorImage']['id']);
        //   $this->set('image',  $tProfilePhotoModel['TutorImage']['image']);          
       // }
}

public function update_entry($datastring=null) {
     
     //if (!$this->ResquestHandler->isAjax()) {
     if (!$this->request->is('ajax')) {
        throw new MethodNotAllowedException();
    } 
    
   $this->layout = 'ajax';
   $this->autoRender = false;
   

      $data = $this->request->data;
     //debug($data['editAct']);    die();                    
    switch ($this->request->data['editAct']) {
        
     case 'editHrate':             
            $this->{$this->modelClass}->TutorProfile->set(array('hourly_rate' => $this->request->data['datum'])); 
        if(!empty($this->request->data['datum']) )  { 
             //debug('test'); die();
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('hourly_rate')))) 
         {      
            
              
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.hourly_rate' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                   throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
         
        
     }
      //debug('test1'); die();
     break;
        case 'editTrad':             
           $this->{$this->modelClass}->TutorProfile->set(array('travel_radius' => $this->request->data['datum'])); 
        if(!empty($this->request->data['datum']) )  {                        
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('travel_radius')))) 
         {          
               
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.travel_radius' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                   throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
         
     }
     break;     
             
     case 'editCancelPolicy':             
      $this->{$this->modelClass}->TutorProfile->set(array('cancel_policy' => $this->request->data['datum'])); 
     if(!empty($this->request->data['datum']) )  {                        
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('cancel_policy')))) 
         {                               
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.cancel_policy' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
     }
     break;    
                 
    case 'editTitle': 
     $this->{$this->modelClass}->TutorProfile->set(array('title' => $this->request->data['datum'])); 
    // debug($this->request->data['datum']); die();
     if(!empty($this->request->data['datum']) )  {                        
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('title')))) 
         {                               
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.title' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                   throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
     }
     break;      
  case 'editDesc':  
   $this->{$this->modelClass}->TutorProfile->set(array('description' => $this->request->data['datum'])); 
    // debug($this->request->data['datum']); die();
     if(!empty($this->request->data['datum']) )  {                        
         if( $this->{$this->modelClass}->TutorProfile->validates(array('fieldList' => array('description')))) 
         {                               
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.description' => $this->request->data['datum']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                      );
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
     }
     break; 
     case 'editEducation' :
     //debug('here now'); die();
    //  debug($this->request->data); die();
     $this->{$this->modelClass}->TutorProfile->set(array(
             'education' => $this->request->data['ed'],
             'degree' => $this->request->data['degree'], 
             'school' => $this->request->data['school']
     )); 
     
      if(!empty($this->request->data) )  { 
        
        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('education','degree', 'school' )))) 
         {      
             //$this->{$this->modelClass}->TutorProfile->id = $this->Auth->user('id');
             //$this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.education', $this->request->data['ed']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.degree', $this->request->data['degree']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.school', $this->request->data['school']);
              
               //debug($this->request->data['degree']);
               
                 $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.education' => $this->request->data['ed'],
                      'TutorProfile.degree' => $this->request->data['degree'],
                      'TutorProfile.school' => $this->request->data['school']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id')));                      
                  
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
        
      }
      break;
      case 'editCadd' :
      //debug($this->request->data);
     $this->{$this->modelClass}->TutorProfile->set(array(
             'address_1' => $this->request->data['addr1'],
             'address_2' => $this->request->data['addr2'], 
             'city' => $this->request->data['city'],
             'state' => $this->request->data['state'],
             'state_abbr' => $this->request->data['state'],
             'zip' => $this->request->data['zipCode']
             
     )); 
     
      if(!empty($this->request->data) )  { 
        
        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('address_1','address_2', 'city', 'state', 'zip_code' )))) 
         {  
            
            // $this->{$this->modelClass}->TutorProfile->id = $this->Auth->user('id');
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.address_1', $this->request->data['addr1']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.address_2', $this->request->data['addr2']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.city', $this->request->data['city']);
             //$this->{$this->modelClass}->TutorProfile->id = $this->Auth->user('id');
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.state', $this->request->data['state']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.state_abbr', $this->request->data['state']);
            // $this->{$this->modelClass}->TutorProfile->saveField('TutorProfile.zip_code', $this->request->data['zipCode']);
             
                $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.address_1' => $this->request->data['addr1'],
                      'TutorProfile.address_2' => $this->request->data['addr2'],
                      'TutorProfile.city' => $this->request->data['city'],
                      'TutorProfile.state' => $this->request->data['state'],
                      'TutorProfile.state_abbr' => $this->request->data['state'],
                      'TutorProfile.zip_code' => $this->request->data['zipCode']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id')));                      
                
                  
                 // debug('After Save'); die();
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                     //$error = $this->validateErrors($this->{$this->modelClass}->TutorProfile);
                 // didn't validate logic
                 //$this->set('thrownError',$this->{$this->modelClass}->TutorProfile->validationErrors[$this->request->data['datum']]);
         }
        
      }
      break;
      case 'editMadd' :
    // debug('In Madd'); die();
     $this->{$this->modelClass}->TutorProfile->set(array(
             'maddress_1' => $this->request->data['maddr1'],
             'maddress_2' => $this->request->data['maddr2'], 
             'mcity' => $this->request->data['mcity'],
             'mstate' => $this->request->data['mstate'],
             'mstate_abbr' => $this->request->data['mstate'],
             'mzip' => $this->request->data['mzipCode'],
             
     )); 
     
      if(!empty($this->request->data) )  { 
        
        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('maddress_1','maddress_2', 'mcity', 'mstate', 'mzip_code' )))) 
         {      
               
                 $this->{$this->modelClass}->TutorProfile->updateAll(
                      array('TutorProfile.maddress_1' => $this->request->data['maddr1'],
                      'TutorProfile.maddress_2' => $this->request->data['maddr2'],
                      'TutorProfile.mcity' => $this->request->data['mcity'],
                      'TutorProfile.mstate' => $this->request->data['mstate'],
                      'TutorProfile.mstate_abbr' => $this->request->data['mstate'],
                      'TutorProfile.mzip_code' => $this->request->data['mzipCode']),
                      array('TutorProfile.tutor_id' => $this->Auth->user('id')));                      
                  
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                  
         }
        
      }
      break;
   case 'editCinfo' :
     //debug($this->request->data); die();
    // debug('In Cinfo'); die();
     $this->{$this->modelClass}->TutorProfile->set(array(
             'primary_phone' => $this->request->data['pphone'],
             'pphone_type' => $this->request->data['pphoneType'], 
             'secondary_phone' => $this->request->data['sphone'],
             'sphone_type' => $this->request->data['sphoneType']
            
             
     )); 
     
      if(!empty($this->request->data) )  { 
        //debug($this->request->data);
        if( $this->{$this->modelClass}->TutorProfile->validates(
                  array('fieldList' => array('primary_phone', 'pphone_type')))) //, 'secondary_phone', 'sphone_type')))) 
         {      
            
               //  debug('validated'); die();
             $this->{$this->modelClass}->TutorProfile->id = $this->request->data['id'];
             $this->{$this->modelClass}->TutorProfile->saveField('primary_phone', $this->request->data['pphone']);
             $this->{$this->modelClass}->TutorProfile->saveField('pphone_type', $this->request->data['pphoneType']);
             $this->{$this->modelClass}->TutorProfile->saveField('secondary_phone', $this->request->data['sphone']);
             $this->{$this->modelClass}->TutorProfile->saveField('sphone_type', $this->request->data['sphoneType']);
             
                // $this->{$this->modelClass}->TutorProfile->updateAll(
                    //  array('TutorProfile.primary_phone' => $this->request->data['pphone'],
                      //'TutorProfile.pphone_type' => $this->request->data['pphoneType'],
                      //'TutorProfile.secondary_phone' => $this->request->data['sphone'],
                      //'TutorProfile.sphone_type' => $this->request->data['sphoneType']),
                      //array('TutorProfile.tutor_id' => $this->Auth->user('id')));                      
                  
         } else {
                  throw new NotFoundException(__('Invalid Request'));
                  
         }
        
      }
      break;
     
     
  }
    
}
public function update_photo($id=null) {
    $this->layout = 'ajax';
   $this->autoRender = false;
   //debug($this->request->data); die();
     
     if (!$this->request->is('ajax')) {
        throw new MethodNotAllowedException();
    } 
    
   
    
    //if (!$this->request->is('post') || !$this->request->is('put')) {
      //  throw new MethodNotAllowedException();
   // }
     //if($this->request->is('ajax'))
     
   
     if ( empty($id) ||!($data = $this->{$this->modelClass}->TutorImage->find(
                            'first', array(
                            'conditions' => array(
                                'TutorImage.tutor_id' => $this->Auth->user('id'), 
                                'TutorImage.data_id'  => $id))))) 
     {
                                    
          //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_photos'));
                                    
     }
    
     if ($data['TutorImage']['data_id'] != $id) {                               
           //Blackhole Request
            throw new BadRequestException();
     }  
     
    $this->{$this->modelClass}->TutorImage->updateAll(
          array('TutorImage.featured' => 0),
          array('TutorImage.tutor_id' => $this->Auth->user('id'))
          );
    
    if($this->{$this->modelClass}->TutorImage->updateAll(
          array('TutorImage.featured' => 1),
          array('TutorImage.id'  => $data['TutorImage']['id'])
          )
    ) {
        
        $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Profile Pic updated on your public profile.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );
        
        return $this->redirect(array('action' => 'manage_photos')); 
        
    } else {
        
        $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Update failed.')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
                                     
            return $this->redirect(array('action' => 'manage_photos'));
    }         		            
    
     
}

public function delete_subject($id=null) {
     $this->layout='tutor';
     
    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    } 
    
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }
    
     if ( empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find(
                            'first', array('conditions' => array('TutorSubject.id' => $id))))) 
    {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_subjects'));
     }
     
     if ($data['TutorSubject']['id'] != $id) {                               
           //Blackhole Request
            throw new BadRequestException();
     }     
    if($this->{$this->modelClass}->TutorSubject->delete($id)) {
        
        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Subject has been successfully deleted.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_subjects'));
        
     } else {
        
         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'deleted failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
     }
                                                                                                  
}

public function deactivate_subject($id=null) {
     $this->layout='tutor';
     
    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    } 
    
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }
    if ( empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find(
                            'first', array('conditions' => array('TutorSubject.id' => $id))))) 
    {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_subjects'));
     }
     
     if ($data['TutorSubject']['id'] != $id) {                               
           //Blackhole Request
            throw new BadRequestException();
     }    
    $this->{$this->modelClass}->TutorSubject->id = $id;
    
    //$this->Post->saveField('title', 'A New Title for a N")
    if($this->{$this->modelClass}->TutorSubject->saveField('opt_out', 1)) {
        
        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Subject has been successfully deactivated.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_subjects'));
        
     } else {
        
         $this->Session->setFlash(
   												sprintf(__d('users', 'Subject deactivation failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
								 );
     }                                                                                                    
}

public function reactivate_subject($id=null) {
     $this->layout='tutor';
     
    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    } 
    
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }
    
    if ( empty($id) || !($data = $this->{$this->modelClass}->TutorSubject->find(
                            'first', array('conditions' => array('TutorSubject.id' => $id)
       )))) {
        //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_photos'));
     }
     
   
     if ($data['TutorSubject']['id'] != $id) {
                                //BLACKHOLE
                               // debug('hi'); die();
           //$this->Security->blackHoleCallback = 
           //'blackhole';
           //blackhole($type);
            throw new BadRequestException();
     }
    $this->{$this->modelClass}->TutorSubject->id = $id;
    
    //$this->Post->saveField('title', 'A New Title for a N")
    if($this->{$this->modelClass}->TutorSubject->saveField('opt_out', 0)) {
        
        $this->Session->setFlash
   									(
   												//sprintf(__d('users', 'The Subject with id: %s has been successfully deleted.', h($id))),
              	                              sprintf(__d('users', 'The Subject has been successfully reactivated.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_subjects'));
        
     } else {
        
         $this->Session->setFlash
   									(
   												sprintf(__d('users', 'Subject reactivation failed. Please try again!!!')),
   											   'default',
   												array('class' => 'alert alert-warning')
   									 );
     }
                                                                                                
    
}
public function delete_photo($id=null) {
    //May need to revisit this: Need to think about deleting with data_id and NOT PK
    //If that is the case, Search conditions must be changed and deleteAll() will be used
    
    //debug($id); die();
   // debug($this->request->data); die();
    $this->layout='tutor';
    
    if ($this->request->is('get')) {
        throw new MethodNotAllowedException();
    } 
    
    if (!$this->request->is('post') && !$this->request->is('put')) {
        throw new MethodNotAllowedException();
    }
    
    if ( empty($id) || !($data = $this->{$this->modelClass}->TutorImage->find(
                            'first', array('conditions' => array('TutorImage.id' => $id))))) 
    {
          //error flash message
          $this->Session->setFlash(sprintf(__d('users', 'Something went wrong!!!! Please, try Again!!.')),
   											   'default',
   												array('class' => 'alert error-message')
							       );
          $this->redirect(array('action' => 'manage_photos'));
     }
     
     if ($data['TutorImage']['id'] != $id) {                               
           //Blackhole Request
            throw new BadRequestException();
     }     
               
    // debug($data); die();                    
    if($this->{$this->modelClass}->TutorImage->delete($id)) 
    {            
       // $this->{$this->modelClass}->TutorImage->deleteFiles(WWW_ROOT . 'img/files/uploads/Chrysanthemum.jpg');
      // $this->{$this->modelClass}->TutorImage->deleteFiles($id);
                    
      //$filename1 = $data['TutorImage']['image'];
      $filename2 = $data['TutorImage']['thumb_image'];
      $filename3 = $data['TutorImage']['thumb_medium'];
      
      $fileNames = array($filename2, $filename3);
       foreach($fileNames as $fileName) {
           // debug($filename); die();
            //$file = new File(WWW_ROOT . 'img'.$fileName, false); //, 0777);
            $file =  new File($fileName);
            if($file->delete()) {
                    echo 'image deleted.....';
            }
      }  
      $this->Session->setFlash
   									(
   											//	sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
                                                sprintf(__d('users', 'The photo has been successfully deleted.')),
   											   'default',
   												array('class' => 'alert alert-success')
   									 );

        return $this->redirect(array('action' => 'manage_photos'));
    }

    return $this->redirect(array('action' => 'manage_photos'));

}

public function subject_credentials() {
      $this->layout='tutor';
      if($this->request->is('post')) {
      //debug($this->request->data); die();
         if (!empty($this->request->data)) {
 	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
 			          $this->request->data['TutorSubject']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

                if(!empty($this->request->data['TutorSubject']['id']))
 			                $id = $this->request->data['TutorSubject']['id'];     //the Pk of Associated model (TutorSubject)

                 
                $this->{$this->modelClass}->TutorSubject->set('subject_credentials', 
                                 $this->request->data['TutorSubject']['subject_credentials']);  
                              
                if ($this->{$this->modelClass}->TutorSubject->validates(
					                                array('fieldList' => array(
					                                        'subject_credentials'))))  
                {
					                                           
                      $this->request->data['TutorSubject']['credentials_status'] = 1;
                      if($this->{$this->modelClass}->TutorSubject->saveSubjectCredentials($id, $this->request->data))
 					   {
 							$this->Session->setFlash
 									(
 												sprintf(__d('users', 'Subject Credentials have been successfully saved.')),
 											   'default',
 												array('class' => 'alert alert-success')
 									 );
 					  } else {
 					      
                          	$this->Session->setFlash
 									(
 												sprintf(__d('users', 'Subject Credentials have NOT been saved.')),
 											   'default',
 												array('class' => 'alert alert-warning')
 									 );
 					  }
                                                            
                 } else {
 					      
                          	$this->Session->setFlash
 									(
                                              	//sprintf(__d('users', 'The photo with id: %s has been successfully deleted.', h($id))),
 												sprintf(__d('users', '%s', h($this->{$this->modelClass}->TutorSubject->validationErrors['subject_credentials'][0]))),
 											   'default',
 												array('class' => 'alert error-message')
 									 );
 					  }
                 
                 $errors = $this->validationErrors;
                //debug($this->{$this->modelClass}->TutorSubject->validationErrors);    die();        
         }     
     }
     return $this->redirect(array('action' => 'manage_subjects'));
    
}
public function manage_preferences() {

      $this->layout='tutor';
      if($this->request->is('post')) {
     // debug($this->request->data); die();
      $id = null;

 	     if (!empty($this->request->data)) {
 	          		  $this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
 			          $this->request->data['TutorPreference']['tutor_id'] = $this->request->data[$this->modelClass]['id'];

              if(!empty($this->request->data['TutorPreference']['id']))
 			                $id = $this->request->data['TutorPreference']['id'];     //the Pk of Associated model (TutorPreference)

              $this->{$this->modelClass}->TutorProfile->set(array(
                                  'new_features' => $this->request->data['TutorPreference']['new_features'],
                                  'promos' => $this->request->data['TutorPreference']['promos'],
                                  'daily_digest' => $this->request->data['TutorPreference']['daily_digest'],
                                  'new_students' => $this->request->data['TutorPreference']['new_students'],
                                  'lesson_submission' => $this->request->data['TutorPreference']['lesson_submission'],
                                  'sms_alerts' => $this->request->data['TutorPreference']['sms_alerts'],
                                  'phone_number' => $this->request->data['TutorPreference']['phone_number'],
                                  'carrier' => $this->request->data['TutorPreference']['carrier']
                                  
                                  ));
              //if ($this->{$this->modelClass}->TutorPreference->validates(
					                               //array('fieldList' => array(
                                                           // 'new_features',
                                                           // 'promos',
                                                            //'daily_digest',
                                                            //'new_students',
                                                           // 'lesson_submission',
                                                            //'sms_alerts',
					                                       // 'phone_number',
					                                        //'carrier'
                                                           // ))))
		      // {
 					  if($this->{$this->modelClass}->TutorPreference->savePreferences($id, $this->request->data))
 					   {
 							$this->Session->setFlash
 									(
 												sprintf(__d('users', 'Email/Sms Preferences successfully saved.')),
 											   'default',
 												array('class' => 'alert alert-success')
 									 );
 					  } else {
 					      $this->Session->setFlash
 									(
 												sprintf(__d('users', 'Email/Sms Preferences Not saved. Please try Again!!')),
 											   'default',
 												array('class' => 'error-message')
 									 );
 					  }
 				//}
             }
       }


           //set the primary key of preference table in the view and send it back as a hidden field
 	      $tPrefModel =  $this->{$this->modelClass}->TutorPreference->find('first', array(
			  		  		  	'conditions' => array('TutorPreference.tutor_id' => $this->Auth->user('id'))
                     ));
 	       // debug($this->Auth->user('id')); die();
 	      //  debug($tPrefModel); die();
 	      if(!empty($tPrefModel)) {
 	                //debug($tPrefModel); die();
 	                $this->set('ppk',  h($tPrefModel['TutorPreference']['id']));
 	                $this->set('nf',   h($tPrefModel['TutorPreference']['new_features']));
 	                $this->set('pmos', h($tPrefModel['TutorPreference']['promos']));
 	                $this->set('dd',   h($tPrefModel['TutorPreference']['daily_digest']));
 	                $this->set('ns',   h($tPrefModel['TutorPreference']['new_students']));
 	                $this->set('ls',   h($tPrefModel['TutorPreference']['lesson_submission']));
 	                $this->set('sa',   h($tPrefModel['TutorPreference']['sms_alerts']));
 	                $this->set('pn',   h($tPrefModel['TutorPreference']['phone_number']));
 	                $this->set('cr',   h($tPrefModel['TutorPreference']['carrier']));
           }

 }

public function manage_subjects() {
          $this->layout='tutor';
          $cats = $this->{$this->modelClass}->TutorCategorie->find('all',
          array(
                'conditions' => array('TutorCategorie.tutor_id' => $this->Auth->user('id')),
                'order' => array('TutorCategorie.name ASC'))
               );

		  $this->set('categories',h($cats));

		  $subjects = $this->{$this->modelClass}->TutorSubject->find('all',
		  array(
			    'conditions' => array('TutorSubject.tutor_id' => $this->Auth->user('id'), 'TutorSubject.delete_status' => 'N'),
			    'order' => array('TutorSubject.subject_name ASC'))
		       );

		  $this->set('subjects',h($subjects));
}

public function tutor_dashboard() {
     $this->layout='tutor';

           //return $this->redirect(array('action' => 'welcome'));
           
            $tProfileModel = $this->{$this->modelClass}->TutorProfile->find('first', array(
			  		  		  	'conditions' => array('TutorProfile.tutor_id' => $this->Auth->user('id'))
                     ));
   	      if(!empty($tProfileModel)) {
   	                //debug($tProfileModel); die();
   	                $this->set('prpk',   h($tProfileModel['TutorProfile']['id']));
   	                $this->set('hr',     h($tProfileModel['TutorProfile']['hourly_rate']));
   	                $this->set('tr',     h($tProfileModel['TutorProfile']['travel_radius']));
   	                $this->set('cp',     h($tProfileModel['TutorProfile']['cancel_policy']));

   	                $this->set('title',         h($tProfileModel['TutorProfile']['title']));
   	                $this->set('description',  h($tProfileModel['TutorProfile']['description']));
                    
                    $this->set('mkps',   h($tProfileModel['TutorProfile']['mktplace_status']));
                    $this->set('bps',    h($tProfileModel['TutorProfile']['basicProfile_status']));
                    $this->set('pps',    h($tProfileModel['TutorProfile']['publicProfile_status']));
                    $this->set('ica',    h($tProfileModel['TutorProfile']['ica_status']));
                    $this->set('profile_status_count',    h($tProfileModel['TutorProfile']['profile_status_count']));
                    //debug($tProfileModel['TutorProfile']['profile_status_count']); die();
                    
                    
             }
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
			//$this->layout='default';
	 if ($this->Auth->loggedIn()) {

	  		return $this->redirect(array('action' => 'jobsearchresultsauth'));
	  } else {
	      $this->layout='default';
	  }
    }

     public function job_details_auth() {
				$this->layout='tutor';
    }

     public function job_details() {

	   if ($this->Auth->loggedIn()) {

	   	  	return $this->redirect(array('action' => 'job_details_auth'));
	    } else {
	       $this->layout='default';
	    }
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
   public function missingView() {
       $this->layout = 'tutor';
       //$this->render('missing_action');
   }


public function afterDeleteRename($id) {
  if(  $deletedRow = ($this->{$this->modelClass}->TutorImage->find(
                    array('TutorImage.data_id' => $id, 
                          'TutorImage.tutor_id' => $this->Auth->user('id') 
                    )))
    ) {
                    
    $filename = $deletedRow['TutorImage']['thumb_image'];
    //debug($filename); die();
    $filepath = $filename;
    //$fiepath = WWW_ROOT. 'img/files/uploads'.$filename;
                          
    $file = new File($deletedRow['TutorImage'][$filepath]);
    $file->delete();
  }
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

    public function isAuthorized($user) {

	   if($this->params['controller']=='students') {
	   $this->Session->setFlash
	      				(
	      				  sprintf(__d('users', 'You are one nosy student.')),
	      				   'default',
	      					array('class' => 'alert alert-warning')
	   					);
	    }
	    return false;

}
 }
 
 ?>