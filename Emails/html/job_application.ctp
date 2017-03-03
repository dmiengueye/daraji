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

//echo 'view my profile here '. $userData['TutorJobApplication']['profile_link'];
echo Router::url(array('admin' => false, 'plugin' => null, 'controller' => 'students', 'action' => 'tutor_details_profile', 'GA09884844'), true);

echo "<br />";
echo __d('users', 'Hello %s,', $userData['TutorJobApplication']['student_name']);
echo "\n";
echo "<br />";
echo __d('users', '%s,', $userData['TutorJobApplication']['personal_message']);
echo "\n";
//echo __d('users', 'To confirm your new email and re-validate your account, you must visit the URL below within 24 hours');
echo "\n"; 
echo "\n";


//echo Router::url(array('admin' => false, 'plugin' => null, 'controller' => 'tutors', 'action' => 'tutor_profile_detail_auth' '3'), true);

echo "\n"; 
echo "\n";
echo "<br /><br />";
echo 'Thanks,';
echo "<br />";
echo 'Team Daraji';

