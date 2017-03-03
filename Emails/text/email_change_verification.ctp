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

echo __d('users', 'Hello %s,', $user[$model]['first_name']);
echo "\n";
echo __d('users', 'The email address associated with your Daraji account has just been changed to %s,', $user[$model]['email']);
echo "\n";
echo __d('users', 'To confirm your new email and re-validate your account, you must visit the URL below within 24 hours');
echo "\n"; 
echo "\n";

if($model == 'Student') {
  echo Router::url(array('admin' => false, 'plugin' => null, 'controller' => 'students', 'action' => 'verify', 'email', $user[$model]['email_token']), true);
} else if($model == 'Tutor') {
   echo Router::url(array('admin' => false, 'plugin' => null, 'controller' => 'tutors', 'action' => 'verify', 'email', $user[$model]['email_token']), true);
}
echo "\n"; 
echo "\n";
echo 'Thanks,';
echo "\n";
echo 'Team Daraji';

