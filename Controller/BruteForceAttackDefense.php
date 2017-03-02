Slowing down & defending against Brute Force attack
In order to defend against Brute Force attack,
the system must throttle the Login (Wait period after x number of failed login attempts have been recorded)

handling Brute Force attack

Strong password
Slow Password Hashing algo (blowfish)
Logging
 Active Logging by emailing Site Admin and alerting him/her

Throttling
Delay user login for x amount of time after x numbers of login failures
IP BlackListing

function record_failed_login($username) {

//read the number of recorded failed login attempts from DB (there will be 0 the first time)
$failed_login = find_one_in_db('failed_logins', 'username', sql_prep($username));

if(!isset($failed_login)) {
$failed_login = [
   'username' => sql_prep($username);
   'count' => 1;
   'last_time' => time();
 ];
 add_record_to_db('failed_login' , $failed_login);
 } else {
     //There is at least one recorded failed login, possibly more
     $failed_login['count'] = $failed_login['count'] + 1;
     $failed_login['last_time'] = time();
     update_record_to_db('failed_login' ,'username', $failed_login);
 }

 return true;

}

function clear_failed_login($username) {

//read the number of recorded failed login attempts from DB (there will be 0 the first time)
$failed_login = find_one_in_db('failed_logins', 'username', sql_prep($username));
if(isset($failed_login)) {
     $failed_login['count'] = 0;
     $failed_login['last_time'] = time();
     update_record_to_db('failed_login' ,'username', $failed_login);
}

return true;
}

//Return the number of minutes to wait until user is allowed to login again
function throttle_failed_login($username) {
$throttle_at  = 5;
$delay_in_min = 10;
$delay = 60 * $delay_in_min;

//read the number of recorded failed login attempts from DB (there will be 0 the first time)
$failed_login = find_one_in_db('failed_logins', 'username', sql_prep($username));

//Once failure count is = $throttle_at value, user must wait for the $delay period to pass
if(isset($failed_login) && $$failed_login['count'] >= $trottle_at) {
	$remaining_delay = ($failed_login['last_time'] + delay)- time();
	$remaining_delay_in_min  = ceil($remaining_delay / 60);
 	return $remaining_delay_in_min;
 } else {
    //no delay
    return 0;
  }
}

Usage
$throttle_delay = throttle_failed_login($username)
if($trhottle_delay > 0) {

  $message = "Too many failed logins. ";
  $message =. "You must wait {$trhottle_delay} minutes before you can attempt another login again
} else {
     //No throttle.. continue with our app
}

-------------------------------------------------------------------------------------------------------------------------------

