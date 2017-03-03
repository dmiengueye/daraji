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

App::uses('Security', 'Utility');
App::uses('UsersAppModel', 'Users.Model');
App::uses('SearchableBehavior', 'Search.Model/Behavior');
App::uses('SluggableBehavior', 'Utils.Model/Behavior');


class TutorSubject extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorSubject';

/**
 * Additional Find methods
 *
 * @var array
 */
	public $findMethods = array(
		'search' => true
	);

/**
 * All search fields need to be configured in the Model::filterArgs array.
 *
 * @var array
 * @link https://github.com/CakeDC/search
 */
public $filterArgs = array(
		'username' => array('type' => 'like'),
		'email' => array('type' => 'value')
	);


// public $hasOne = array (
//  'Subject' => array(
//    'className' => 'Subject',
//    'foreignKey' => 'subject_id',
//                     // 'conditions' => array('TutorSubject.active' => '1'),
//    'dependent' => true
//    )
//  );

public $belongsTo = array(
        'Tutor'=> array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
            ),

       'TutorCategorie'=> array(
            'className' => 'TutorCategorie',
            'foreignKey' => 'tutor_categorie_id'
            ),
       'Subject' => array(
         'className' => 'Subject',
         'foreignKey' => 'subject_id',
                          // 'conditions' => array('TutorSubject.active' => '1'),
         'dependent' => true
         )
      );

 //public $hasAndBelongsToMany  = array (
 	 	                   // 'Tutor' => array(
 	 	                                // 'className' => 'Tutor',
 	 	                                // 'joinTable' => 'tutors_subjects_tutors',
 	 	                                // 'foreignKey' => 'tutor_subject_id',
 	 	                                // 'associationForeignKey' => 'tutor_id',
 	 	                                 //'unique' => true,  // keepExisting see here  http://book.cakephp.org/2.0/en/models/saving-your-data.html#saving-habtm
 									  	// 'unique' =>  false,
 									  	// 'conditions' => '',
 									  	// 'fields' => '',
 									  	// 'order' => '',
 									    // 'limit' => '',
 									    // 'offset' => '',
 									     //'finderQuery' => '',
 									     //'with' => ''
 	 	                             //)
	 	                       //);


public $validate = array(

			  'subject_credentials' => array(
			   			'required' => array(
			   						     'rule' => array('notEmpty'),
			   						     'required' => true, //'allowEmpty' => false,
			   						     'message' => 'Please enter Your Credentials for this subject.'),

  			            'subject_credentials_min' => array(
			   						      'rule' => array('minLength', '100'),
			   						      'message' => 'The credentials must have at least 100 characters.'))


	); //end validates array

public function saveSubjectCredentials($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['TutorSubject']['id'] = $id;  //write the pk into the data array so it knows this an update an not a create
        }
           $this->save($postData, array(
  		 				'validate' => true,
  		 				'callbacks' => true));

  			return true;
   }

   public function saveSubject($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['TutorSubject']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
        }
          if($this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false))) {

  			            return true;
               } else  {
               return false;
               }
   }


public function get_tutor_subj_cred($id, $subj_id) {
     $creds = "";
     $data = $this->find('first',
            array(
              'conditions' => array(
                'TutorSubject.subject_id' => $subj_id,
                'TutorSubject.tutor_id'  => $id,
                'TutorSubject.approval_status'  => 'Y',
                'TutorSubject.opt_out'  => '0',
                'TutorSubject.searchable_status'  => '1',

                )
              ));
           //debug($data); die();
      if(!empty($data)){
			//foreach ($data as $key => $value) {
			 //debug($data['TutorSubject']['subject_credentials']); die();
				if(!empty($data['TutorSubject']['subject_credentials'])){
					$creds = $data['TutorSubject']['subject_credentials'];
				}
			//}
		}
   //debug($creds); die();
 return $creds;
}
public function get_tutor_subjects_ById($id){
    $subjects = array();
  $data = $this->find('all',
            array(
              'conditions' => array(
                //'Subject.name LIKE'  => '%'.$subject_name.'%'
                'TutorSubject.tutor_id'  => $id,
                'TutorSubject.approval_status'  => 'Y',
               // 'TutorSubject.opt_out'  => '0',
               // 'TutorSubject.searchable_status'  => '1',


                )
              ));
  // debug($data); die();
     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorSubject']['subject_name'])){
					$subjects[] = $value['TutorSubject']['subject_name'];
				}
			}
		}
  // debug($subjects); die();
 return $subjects;
}


public function get_all_subjects_for_tutor($id){
    $subjects = array();
    //debug($id);
	$job_search_auth = null;
  $data = $this->find('all',
            array(
              'conditions' => array(
                //'Subject.name LIKE'  => '%'.$subject_name.'%'
                'TutorSubject.tutor_id'  => $id,
                'TutorSubject.approval_status'  => 'Y',
                'TutorSubject.opt_out'  => '0',
                'TutorSubject.searchable_status'  => '1',
                'TutorSubject.credentials_status'  => '1'
                )
              ));
   //debug($data); die();
  // $subjects[] = array("100" => 'All My Subjects');
  	 $job_search_auth = CakeSession::read('jsauth');
	 $job_search_auth_ajax = CakeSession::read('jsauthajax');
     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorSubject']['subject_name'])  &&
                  !empty($value['TutorSubject']['subject_id'])){
				//	$subjects[] = $value['TutorSubject']['subject_name'];
                   // $subjects[] = array($value['TutorSubject']['subject_id'] => $value['TutorSubject']['subject_name']);
				   //Replaced below with above to see if it helps make it easier to display subjects under their categories in tutor_details_profile 04-08-17- DG 
				   if(!empty($job_search_auth )) {
					  // debug("first"); //die();
				       $subjects[] = array($value['TutorSubject']['subject_category_name'] => $value['TutorSubject']['subject_name']);					    
				   } if(!empty($job_search_auth_ajax )) {
					  // debug("ajax here");
                       $subjects[] = array($value['TutorSubject']['subject_id'] => $value['TutorSubject']['subject_name']);
				   } else {
					 // debug("hhh"); //die();
					   $subjects[] = array(array($value['TutorSubject']['subject_category_name'] => $value['TutorSubject']['subject_name']));
					//$subjects[] = array($value['TutorSubject']['subject_id'] => array($value['TutorSubject']['subject_name'], $value['TutorSubject']['subject_category_name']));
             
			    //$tutor_subjects = array(array('Math' => 'Algebra'), array('Math' => 'Calculus'),array('Science' => 'Biology'),array('Science' => 'Physics'));

				}
			}
		}
	 }
//debug($subjects); die();
 return $subjects;
}




} //end of class