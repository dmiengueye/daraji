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
App::uses('Hash', 'Utility');
App::uses('UsersAppModel', 'Users.Model');
App::uses('SearchableBehavior', 'Search.Model/Behavior');
App::uses('SluggableBehavior', 'Utils.Model/Behavior');

/**
 * Users Plugin User Model
 *
 * @package User
 * @subpackage User.Model
 */
class TutorRating extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorRating';



public $belongsTo = array(
        'Tutor' => array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
        )
    );

public $validate = array(
	
); //end validates array


public function saveRatings($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id) && $id != null) {
           $postData['TutorRating']['id'] = $id;  //write the pk into the data array so it knows this is an update an not a create
        }
           $this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false));

  			return true;
   }
   
public function get_tutor_ratings($id) {
    
     //$pic = "";
     $data = $this->find('first',
            array(
              'conditions' => array(
                'TutorRating.tutor_id'  => $id,
                // 'TutorRating.overall_ratings'  => 'N/R',
                // 'TutorRating.reviews'  => '1'
                 //'TutorProfile.status'  => '1',
              
                )
              ));
              
      if(!empty($data)){
			//foreach ($data as $key => $value) {
			 //debug($data['TutorSubject']['subject_credentials']); die();
				if(!empty($data['TutorRating'])){
					$data = $data['TutorRating'];
				}
			//}
		}
              
  //debug($data); die();    
  return $data;
}

} //end of class