How to Defend Agaisnt CSRF
1. Get Request Must always be Idempotent (They Must Not make Any Changes... Just Read data)
2. Only USE POST/PUT Requests to make any changes/updates
3.Ensure that Form Data received come from a legitimate form from a legitimate user
  We can do that by storing a form token in user's session
  Add a hidden field to form that include form token as value
  Compare Session form token against submitted form token (Must Match)
  Store token generation time in user's session
  Check if too much time has passed/elapsed

<?php

//GET Requests should not make changes
//Only POST Requests should make changes

function request_is_get() {
  return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function request_is_post() {
  return $_SERVER['REQUEST_METHOD'] === 'POST';
}

//Usage
if(request_is_post()) {
 //process the form
 } else {
   //do something safe. Redirect, error page etc...
 }
?>
<?php

//Generate a token for use with CSRF Protection
//Does not store the token

//Must call session_start() before this load

function csrf_token() {
  return md5(unique(rand(), TRUE));
}

/Generate and store CSRF token in user's session
//Session must have been started

function create_csrf_token() {
  $token = csrf_token();
  _SESSION['csrf_token'] = $token;
  _SESSION['csrf_token_time'] = time();
  return $token;
}
//Destroy a token by removing it from session
function destroy_csrf_token() {
  _SESSION['csrf_token'] = null;
  _SESSION['csrf_token_time'] = null;
  return true;
}

//Retun an HTML tag including the CSRF token for use in our form
//Usage: echo csrf_token_tag()

function csrf_token_tag() {
    $token = csrf_token();
    return "<input type=\"hidden\" name="\csrf_token\" value=\"" .$token. "\">";
}

//Return true if user-submitted Post token is identical to the
//previuosly stored SESSION token
//Return false otherwise

function csrf_token_is_valid() {

	 if(isset($_POST['csrf_token'])) {
	   $user_token = $_POST['csrf_token'];
	   $stored_token = $_SESSION['csrf_token']
	   return $stored_token ===  $user_token;
	} else {
         return false
	}
}

//Usage: Either check the token validity and die on failure
//Or handle the failure in a more graceful way

function dies_on_csrf_token_failure() {
  if(!csrf_token_is_valid()) {
     die("CSRF token validation failed");
}

//Optional: Check to see if token is also recent
function csrf_token_is_recent() {
  $max_elapsed = 60 * 60 * 24 //1 day
  if(isset($_SESSION['csrf_token_time'])) {
  	$stored_time = $_SESSION['csrf_token_time'];
  	return ($stored_time + $max_elapsed) >= time();
  } else {
    //token has expired. Remove it from session
    destroy_csrf_token();
    return false;
  }
}
?>

<?php
//Usage on Front end
//The first time we load the page it is probably not going to be a POST Request.
//It will most likely be a GET request

if(request_is_post() {
  if(csrf_token_is_valid()) {
    $message = "Valid Form Submission";
    if(csrf_token_is_recent()) {
      $message .= " (recent)";
    } else {
      $message .= " (not recent)";
    }
  } else {
    $message = "CSRF TOKEN MISSIN Or MISSMATCHED";
  }
 } else {
   //Form not submitted or was a GET request
   $message = "Please login"
  }
}
?>

<html>
  <head>
     <title>Demo </title>
  </head>
  <body>
     <?php echo $message; ?> <br />
     <form action="" method="post">
     <?php echo csrf_token_tag(); ?>
     username:<input type="text" name="username" /><br />
     password:<input type="password" name="password" /><br />
     <input type="submit" value="Submit" /><br />
     </form>
  </body>
 </html>





