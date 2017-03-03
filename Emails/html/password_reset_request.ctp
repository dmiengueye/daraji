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

echo __d('users', 'A request to reset your password was sent. To change your password click the link below.');
echo "\n";

if($model == 'Student') {
  echo Router::url(array('admin' => false, 'plugin' => null, 'controller' => 'students', 'action' => 'reset_password', $token), true);
} else if($model == 'Tutor') {
   echo Router::url(array('admin' => false, 'plugin' => null, 'controller' => 'tutors', 'action' => 'reset_password', $token), true);
}
