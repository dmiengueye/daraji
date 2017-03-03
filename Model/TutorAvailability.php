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


class TutorAvailability extends AppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorAvailability';

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
            'foreignKey' => 'tutor_id',
			//'dependent' => true
            ),

      );

} //end of class