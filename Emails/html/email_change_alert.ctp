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
echo "\n";
echo __d('users', 'The email address associated with your Daraji account email has just been changed to %s,',  $user[$model]['email']);
echo "\n";
echo __d('users', 'A new activation link has been sent to %s,',  $user[$model]['email']);
echo "\n";
echo __d('users', 'You must click on the link to confirm that you are the new e-mail owner before it can be activated on Daraji.');
echo "\n";
echo "\n";
echo __d('users', 'if you beleive you are receiving this email notification in error,');
echo "\n";
echo __d('users', 'please, contact Daraji Support team immediately at 404 441-2298 for further assisstance.');
echo "\n";
echo __d('users', 'Daraji Support Team can also be contacted via email.');
echo "\n"; 
echo "\n";
echo 'Thanks';
echo "\n";
echo 'Team Daraji';

