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
        date_default_timezone_set('America/New_York');
   		$this->Auth->allow(
				   		 'login', 'register', 'joinus', 'join_via_job', 'index', 'jobsearchresults', 'job_details', 'aboutus', 'tutor_search', 
				   		  'ajax_subjects', 'job_search','job_search_results','tutor_details_profile', 'tutor_search_results','verify', 'logout', 'view',
				   		 'request_tutor', 'message_tutor', 'add', 'resources','resend_verification','contactus','reset_password','complete'

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
	 		if( $this->Session->check('commonLayout')) {
	 			 $this->layout = $this->Session->read('commonLayout');
	 		}
       }
       $this->_set_current_zip_code();
   	}

function validateUSAZip($zip_code)
{
  if(preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$zip_code))
    return true;
  else
    return false;
}
   	/**
   	 * set current zip code
   	 */
protected function _set_current_zip_code(){

        
        $is_valid_zip = true;

       //$this->Session->delete('cur_zip_code');

	$cur_zip_code = $this->Session->read('cur_zip_code');

       // debug($cur_zip_code);
        $is_valid_zip = $this->validateUSAZip($cur_zip_code);

       // debug($is_valid_zip); die();
         // $is_valid_zip = false;


	if(empty($cur_zip_code) || $cur_zip_code === "" || !$is_valid_zip){

	      //debug($is_valid_zip);	   
               // debug($cur_zip_code); //die();

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
	      //curl_setopt( $curl, CURLOPT_POST, 1 );

	   	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

	   	$result = curl_exec($curl);           
	   	curl_close( $curl );

	   	//$zip_code = 30326; // default zip code
                $city="your city";

	   	if(!empty($ip_address)){
	            //debug($ip_address); die();
	   	      $result = explode(";", $result);
                      $this->Session->write('exp_result', $result);
	   	      if(!empty($result[7]) && $result[7] != "-"){
	   		   $zip_code = $result[7];
                         //debug($ip_address); die();
	   	       }
                
                      if(!empty($result[6]) && $result[6] != "-"){
	   		  $city = $result[6];
                         //$this->Session->write('exp_result', $result);
	   	      }
	 }
         //debug($zip_code); //die();
        // debug($city);
             
	 if(!empty($zip_code)){
	   //debug($zip_code); die();
	    $this->Session->write('cur_zip_code', $zip_code);
	 }
            if(!empty($city)){
               //debug($city); die();
	   	$this->Session->write('search_city', $city);
	 }
            
     }

	 $cur_zip_code = $this->Session->read('cur_zip_code');
       // debug($cur_zip_code);
 }
    
  protected function _set_city_for_zip(){
        
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
