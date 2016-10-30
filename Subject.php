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

/**
** good article in relationship modeling
** http://ask.amoeba.co.in/joining-multiple-tables-in-cakephp-using-bindmodel-method/
**/
class Subject extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Subject';

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

public $hasOne = array (
 'TutorSubject' => array(
   'className' => 'TutorSubject',
   'foreignKey' => 'subject_id',
                    // 'conditions' => array('TutorSubject.active' => '1'),
   'dependent' => true
   )
 );

public $belongsTo = array(

       'Categorie'=> array(
            'className' => 'Categorie',
            'foreignKey' => 'categorie_id'
            )
      );

public function saveSubject($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['Subject']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
        }
           $this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false));

  			return true;
   }
/**
 * get category by subject name
 * @param  string $subject_name subject bame
 * @return string               category name (0: no search result)
 */
public function get_category_by_name($subject_name){
  $data = $this->find('all',
            array(
              'conditions' => array(
                //'Subject.name'  => '%'.$subject_name.'%'
                'Subject.name'  => $subject_name
                )
              ));
  //debug($data); die();
  if(!empty($data)){
    if(!empty($data[0]['Categorie']['name'])){
      return $data[0]['Categorie']['name'];
    }
    else{
        return 0;
    }
  }
  else{
    return 0;
  }
}

public function get_subject_by_name($subject_name){
  $data = $this->find('all',
            array(
              'conditions' => array(
                //'Subject.name LIKE'  => '%'.$subject_name.'%'
                'Subject.name'  => $subject_name
                )
              ));


  if(!empty($data)){
    if(!empty($data[0]['Subject']['name'])){
      return $data[0]['Subject']['name'];
    }
    else{
        return 0;
    }
  }
  else{
    return 0;
  }
}


} //end of class