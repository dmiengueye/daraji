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
// App::uses('SearchableBehavior', 'Search.Model/Behavior');
// App::uses('SluggableBehavior', 'Utils.Model/Behavior');


class TutorCategorie extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorCategorie';

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

  public $hasMany = array (
	 	           'TutorSubject' => array(
	 	                 'className' => 'TutorSubject',
	 	                 'foreignKey' => 'tutor_categorie_id',
	 	              // 'conditions' => array('TutorSubject.active' => '1'),
	 	                 'dependent' => true
	 	               )
	 	          );
public $belongsTo = array(
        'Tutor'=> array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
            ));

 // public $hasAndBelongsToMany  = array (
 	 	                   // 'Tutor' => array(
 	 	                                // 'className' => 'Tutor',
 	 	                                 //'joinTable' => 'categories_tutors',
 	 	                                 //'foreignKey' => 'categorie_id',
 	 	                                 //'associationForeignKey' => 'tutor_id',
 	 	                                 //'unique' => true,  // keepExisting see here  http://book.cakephp.org/2.0/en/models/saving-your-data.html#saving-habtm
 									  	 //'unique' =>  keepExisting
 									  	 //'conditions' => '',
 									  	 //'fields' => '',
 									  	// 'order' => '',
 									    // 'limit' => '',
 									    // 'offset' => '',
 									    // 'finderQuery' => '',
 									   //  'with' => ''
 	 	                          //   )
	 	                     //  );

public function saveSubjectCategory($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['SubjectCategory']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
        }
           $this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false));

  			return true;
   }

} //end of class