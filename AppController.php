<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
App::uses('Tutor', 'Model');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    const SUBJECT_ID_100 = '100';
	const SUBJECT_ID_200 = '200';
	const SUBJECT_ID_300 = '300';

public $theme = "CakeAdminLTE";
/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html',
		'Form',
		'Session',
		'Time',
		'Text',
        'Js'
	);

/**
 * Components
 *
 * @var array
 */
	public $components = array(
	   //'DebugKit.Toolbar',
	   // 'Security' => array('csrfUseOnce' => false),
		'Auth',
		'Session',
		'Cookie',
		'Paginator',
		//'Security',
        'RequestHandler',
		'Search.Prg',
		'Users.RememberMe',
        'Users.Recaptcha',
        'Users.TransEmail',
        //'OAuth.OAuth'
	);



   /**
    * beforeFilter callback
    *
    * @return void
    **/
   	public function beforeFilter() {

   		//$this->_setupAuth();
   		//$this->Auth->loginRedirect = null;
   		//$loginRedirect = null
        //date_default_timezone_set('America/New_York');
   		$this->Auth->allow(
				   		 'login', 'register', 'faqs_help', 'joinus', 'join_via_job', 'index', 'jobsearchresults', 'job_details', 'aboutus', 'tutor_search',
				   		  'ajax_subjects', 'ajax_cat_subjects', 'feed', 'job_search','job_search_results','tutor_details_profile', 'tutor_search_results','verify', 'logout', 'view',
				   		 'request_tutor', 'message_tutor', 'add', 'resources','resend_verification','contactus','reset_password','complete','all_ajax_cat_subjects', 'tutor_request', 'tutor_request_sign_up', 'how_it_works_student','how_it_works_tutor', 'about_us', 'pre_tutor_request'

   			             );


      if($this->params['controller']=='/') { // || $this->params['controller'] == 'commons' ) {

                              // $loggedInUserType = $this->Session->read('loggedInUserType');
			 			      if( $this->Session->check('loggedInUserType') &&
			 						          $this->Session->read('loggedInUserType') == 'Auth.Tutor') {
			 						          $this->redirect(array('controller'=>'tutors','action' => 'home'));

			 		           } else if ( $this->Session->check('loggedInUserType') &&
			 						          $this->Session->read('loggedInUserType') == 'Auth.Student') {
			 						          $this->redirect(array('controller'=>'students','action' => 'home'));
			 				   }
         }


	   if($this->params['controller']=='commons') {
	      // debug("test");
	 		if( $this->Session->check('commonLayout')) {
	 		    //debug("test");
	 			 $this->layout = $this->Session->read('commonLayout');
                // debug($this->layout);
	 		}

       }

  //debug($this->Session->read());
      // debug("tetette");
      // $zip_code = 30326;
       // $cur_zip_code = $this->Session->read('cur_zip_code');
       // debug($cur_zip_code); //die();

      $this->_initial_set_up();
	  
	  $subjects_and_categories = null; //$tutors_model->get_all_subjects_and_categories();
     // $this->set('subjects_and_categories', $subjects_and_categories);
	  
	  if(!$this->Session->check('subjects_and_categories')) {
		  $tutor_model = new Tutor();
		  $this->Session->write('subjects_and_categories', $tutor_model->get_all_subjects_and_categories());  
	  }
      // $this->_set_timeZone();
   	}


function _set_timeZone() {

    	$ipinfo_api_key = Configure::read('ipinfo_api_key');
	   	$ip_address = $_SERVER['REMOTE_ADDR'];
        $user_tz = Configure::read('Config.timezone'); //set in core.php '-04:00'  ;

	   	$curl = curl_init();
	   	curl_setopt( $curl, CURLOPT_URL, "http://api.ipinfodb.com/v3/ip-city/?key={$ipinfo_api_key}&ip={$ip_address}" );
	   	curl_setopt( $curl, CURLOPT_VERBOSE , 1 );
	   	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , 0 );
	   	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST , 0 );
	   	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , 1 );
	   	curl_setopt( $curl, CURLOPT_HTTPHEADER, array('content-type:application/x-www-form-urlencoded') );
	      //curl_setopt( $curl, CURLOPT_POST, 1 );

	   	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

	   	$result = curl_exec($curl);
	   	curl_close( $curl );

       	if(!empty($result) ){
	            //debug($ip_address); die();
	   	        $result = explode(";", $result);
                if(!empty($result[10]) && $result[10] != "-"){
	   		              $user_tz = $result[10];
                          //$this->Session->write('user_tz', $user_tz);
	   	        }
        }


     //$user_tz = '-06:00';
     if($user_tz === '-04:00') {
        date_default_timezone_set('America/New_York');
        Configure::write('Config.timezone', 'America/New_York');
     } else if($user_tz === '-05:00') {
        date_default_timezone_set('America/Chicago');
        Configure::write('Config.timezone', 'America/Chicago');
     } else if($user_tz === '-06:00') {
        date_default_timezone_set('America/Denver');
        Configure::write('Config.timezone', 'America/Denver');

     } //Below is complicated because Los Angeles is currently UTC-7
     //But Most of Arizona is still in MST (UCT-7).. They do not change with Daylight saving
     //So I woud have to get the User's state or city from which request is made
     //see https://www.timeanddate.com/time/us/arizona-no-dst.html
     else  if($user_tz === '-07:00') {
        date_default_timezone_set('America/Los_Angeles');
        Configure::write('Config.timezone', 'America/Los_Angeles');
     } else  if($user_tz === '-09:00') {
        date_default_timezone_set('America/Anchorage');
        Configure::write('Config.timezone', 'America/Anchorage');
     }else  if($user_tz === '-10:00') {
        //see https://secure.php.net/manual/en/timezones.america.php
        date_default_timezone_set('Pacific/Honolulu');
        Configure::write('Config.timezone', 'Pacific/Honolulu');
     }
}


function validateUSAZip($zip_code)
{
  // debug($zip_code);
  if(preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$zip_code))
    return true;
  else
    return false;
}
   	/**
   	 * set current zip code
   	 */
protected function _initial_set_up(){


     $is_valid_zip = false;
     $user_tz = null;
     $default_zip_code = 30326; // default zip code
    //debug($cur_zip_code); die()


//$this->Session->delete('cur_zip_code');
//if($this->Session->check('cur_zip_code')) {

	   // $cur_zip_code = $this->Session->read('cur_zip_code');
        //$is_valid_zip = $this->validateUSAZip($cur_zip_code);
        //debug($cur_zip_code);
       // debug($is_valid_zip); //die();
//}

$cur_zip_code = $this->Session->read('cur_zip_code');
//debug($cur_zip_code); //die();

//$cur_zip_code = null;
if(empty($cur_zip_code) || $cur_zip_code === "") { // || !$is_valid_zip){

	     // debug($is_valid_zip);
               //debug($cur_zip_code); // die();

	   	$ipinfo_api_key = Configure::read('ipinfo_api_key');
	   	$ip_address = $_SERVER['REMOTE_ADDR'];
        $user_tz = Configure::read('Config.timezone'); //set in core.php '-05:00'  ;
	   	$curl = curl_init();
             // $curl = curl_init('http://api.ipinfodb.com/v3/ip-city/?key={$ipinfo_api_key}&ip={$ip_address}');

	   	curl_setopt( $curl, CURLOPT_URL, "http://api.ipinfodb.com/v3/ip-city/?key={$ipinfo_api_key}&ip={$ip_address}" );

	   	curl_setopt( $curl, CURLOPT_VERBOSE , 1 );
	   	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , 0 );
	   	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST , 0 );
	   	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , 1 );
	   	curl_setopt( $curl, CURLOPT_HTTPHEADER, array('content-type:application/x-www-form-urlencoded') );
	      //curl_setopt( $curl, CURLOPT_POST, 1 );

	   	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

	   	$result = curl_exec($curl);
       // debug($result); //die();

	   	curl_close( $curl );
	   	$default_zip_code = 30326; // default zip code
	   	//debug($cur_zip_code); //die();

        $city="your city";
        //$user_tz = null;
        $zip_code = null;

	   	if(!empty($ip_address)) {
	            //debug($ip_address); die();
	   	      $result = explode(";", $result);
              //debug($result);die();
              $this->Session->write('exp_result', $result);
	   	      if(!empty($result[7]) && $result[7] != "-"){
	   		     $zip_code = $result[7];
                         //debug($ip_address); die();
	   	       }
               if(!empty($result[6]) && $result[6] != "-"){
	   		              $city = $result[6];
                         //$this->Session->write('exp_result', $result);
	   	        }

               if(!empty($result[10]) && $result[10] != "-"){
	   		        $user_tz = $result[10];
	   		       // debug($user_tz);
                    $this->Session->write('user_tz', $user_tz);
	   	       }
	  }

	 if(!empty($zip_code)){
	   //debug($zip_code); die();
	    $this->Session->write('cur_zip_code', $zip_code);
	 }else {

	   $this->Session->write('cur_zip_code', $default_zip_code);
	 }
     if(!empty($city)){
               //debug($city); die();
	   	      $this->Session->write('search_city', $city);
	 }

       // $user_tz = '-07:00';
        if(!empty($user_tz)){
             if($user_tz === '-04:00') {
                date_default_timezone_set('America/New_York');
                Configure::write('Config.timezone', 'America/New_York');
             } else if($user_tz === '-05:00') {
                date_default_timezone_set('America/Chicago');
                Configure::write('Config.timezone', 'America/Chicago');
             }else  if($user_tz === '-07:00') {
                date_default_timezone_set('America/Los_Angeles');
                Configure::write('Config.timezone', 'America/Los_Angeles');
             }else if($user_tz === '-06:00') {
                date_default_timezone_set('America/Phoenix');
                Configure::write('Config.timezone', 'America/Phoenix');
             }
         // $this-> _set_user_timeZone($user_tz);
	      $this->Session->write('user_tz', $user_tz);
	   } else {
	      $this->Session->write('user_tz', '-04:00');
	      date_default_timezone_set('America/New_York');

	   }


     }

	 $cur_zip_code = $this->Session->read('cur_zip_code');
 }

  protected function _set_city_for_zip($zip_code){

                $this->Session->delete('search_city');
		$cur_city = $this->Session->read('search_city');

	   	if(empty($cur_city)){
	   		$ipinfo_api_key = Configure::read('ipinfo_api_key');
	   		$ip_address = $_SERVER['REMOTE_ADDR'];

	   		$curl = curl_init();
                     // $curl = curl_init('http://api.ipinfodb.com/v3/ip-city/?key={$ipinfo_api_key}&ip={$ip_address}');

	   		curl_setopt( $curl, CURLOPT_URL, "http://api.ipinfodb.com/v3/ip-city/?key={$ipinfo_api_key}&ip={$ip_address}" );

	   		curl_setopt( $curl, CURLOPT_VERBOSE , 1 );
	   		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , 0 );
	   		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST , 0 );
	   		curl_setopt( $curl, CURLOPT_RETURNTRANSFER , 1 );
	   		curl_setopt( $curl, CURLOPT_HTTPHEADER, array('content-type:application/x-www-form-urlencoded') );
	   		// curl_setopt( $curl, CURLOPT_POST, 1 );

	   		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
                        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

	   		$result = curl_exec($curl);

	   		curl_close( $curl );

	   		//$zip_code = 30326; // default zip code

	   		if(!empty($ip_address)){
	   			$result = explode(";", $result);

                                if(!empty($result[6]) && $result[6] != "-"){
	   				$city = $result[6];
                                       //$this->Session->write('search_city', $city);
	   			}
	   		}


                        if(!empty($city)){
	   			$this->Session->write('search_city', $city);
	   		}

	   	}

	   	//$cur_zip_code = $this->Session->read('cur_zip_code');
    }


}
