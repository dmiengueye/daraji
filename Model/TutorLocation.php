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


class TutorLocation extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorLocation';

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

public $belongsTo = array(
        'Tutor'=> array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
            )
      );


public $validate = array(

	              'location_name' => array(
                            'required' => array(
			   						     'rule' => array('notEmpty'),
			   						     'required' => true, 'allowEmpty' => false,
			   						     'message' => 'Location Name is required.')),
			   			

			       'address_1' => array(
        			 		'required' => array(
                    			 		'rule' => array('notEmpty'),
                    			 		'required' => true, 'allowEmpty' => false,
                    					'message' => 'Please enter your cureent street address.')),


			        'city' => array(
					         'required' => array(
            					         'rule' => array('notEmpty'),
            					         'required' => true, 'allowEmpty' => false,
            					         'message' => 'Please enter your current City.')),

                    'state' => array(
					         'required' => array(
            					         'rule' => array('notEmpty', 'stateValidation'),
            					         'required' => true, 'allowEmpty' => false,
            					         'message' => 'Please Select current your State of Residence.')),

			       /** 'state' => array(
                          'notEmpty' => array(
				          'rule' => array('stateValidation'), //array('notEmpty'), //array('educationValidation'),
                          'allowEmpty' => false,
				 		  'message' => 'Please Select current your State of Residence')),
                          **/

			         'zip_code' => array(
                           'required' => array(
					           'rule' => array('postal', null, 'us'),
                               'required' => true, 'allowEmpty' => false,
			                   'message' => 'A valid US Zip Code is required.'))
					
 ); //end validates array

public function stateValidation($data) {
    
          if($data['state'] != '0') {
		        return true;
          } 
}

public function saveTutorLocation($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
            //debug("tttt"); die();
           $postData['TutorLocation']['id'] = $id;  //write the pk into the data array so it knows this an update an not a create
        }
        
       // debug($postData); die();
           $this->save($postData, array(
  		 				'validate' => true,
  		 				'callbacks' => true));

  			return true;
   }
   


public function get_tutor_location_ById($id){
    $locations = array();
    $data = $this->find('all',
            array(
              'conditions' => array(
                'TutorLocation.tutor_id'  => $id,
                )
              ));
              
     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorLocation']['location_name'])){
					$subjects[] = $value['TutorLocation']['location_name'];
				}
			}
		}
   return $locations;
}


public function get_all_locations_for_tutor($id){
    $locations = array();
    //debug($id);
  $data = $this->find('all',
            array(
              'conditions' => array(
                'TutorLocation.tutor_id'  => $id,
                )
              ));

     if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['TutorLocation']['location_name'])  && 
                  !empty($value['TutorLocation']['location_id'])){
                    $subjects[] = array($value['TutorLocation']['location_id'] => $value['TutorLocation']['location_name']);
    	
				}
			}
		}
 return $locations;
}




} //end of class