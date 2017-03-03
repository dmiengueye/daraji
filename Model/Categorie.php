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

class Categorie extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Categorie';

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
	 	           'Subject' => array(
	 	                 'className' => 'Subject',
	 	                 'foreignKey' => 'categorie_id',
	 	              // 'conditions' => array('Subject.active' => '1'),
	 	                 'dependent' => true
	 	               )
	 	          );

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
   
   
public function get_category_id($category){
    
    //debug($category); //die();
  $data = $this->find('first',
            array(
              'conditions' => array(
                'Categorie.name'  => $category
                )
              ));

  // debug($data); //die();
  if(!empty($data)){
    if(!empty($data['Categorie']['category_id'])){
        
       // debug($data['Categorie']['category_id']); //die();
      return $data['Categorie']['category_id'];
      
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