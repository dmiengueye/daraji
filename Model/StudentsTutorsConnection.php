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

/** Shoud inherit the Plugin Users Model
App::uses('AuthComponent', 'Controller/Component');
**/

App::uses('User', 'Users.Model');
App::uses('Student', 'Model');
App::uses('TutorProfile', 'Model');
App::uses('TutorImage', 'Model');
App::uses('Subject', 'Model');
App::uses('TutorSubject', 'Model');
App::uses('TutorRating', 'Model');
App::uses('ZipSearch', 'Model');
App::uses('Categorie', 'Model');
App::uses('Hash', 'Utility');
App::uses('Validation', 'Utility');
App::uses('StudentWatchList', 'Model');
App::uses('JobSearchAgent', 'Model');
App::uses('TutorJobApplication', 'Model');
// class Tutor extends User {
class StudentsTutorsConnection extends User {

public $name = 'StudentsTutorsConnection';

public $recursive = 2;
//public $virtualFields = array('distance' => 10);

                  

/**
 * Validation parameters
 *
 * @var array
 */
}
