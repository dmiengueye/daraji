<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
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
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...


	Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));

	The following 2 lines were copied here by me. They are not part of this original file
	I got them from here:   http://www.blog.dilan.me/?p=122

	Router::connect('/admin', array('controller' => 'pages', 'action' => 'display', 'admin' => true, 'home'));
    Router::connect('/admin/pages/*', array('controller' => 'pages', 'action' => 'display', 'admin' => true));
*/
/**Router::connect('/students/manage_preferences', array('controller' => 'preferences', 'action' => 'manage_preferences'));**/
/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

Router::connect('/', array('controller' => 'commons', 'action' => 'index'));
/** Router::connect('/application/joinus', array('controller' => 'tutors', 'action' => 'joinus'));**/
/**Router::connect('/users', array('plugin' => null, 'controller' => 'students')); **/

/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
	  Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

      //----------------------------------

     //Un-Authenticated Student Links
      Router::connect('/users/accounts/register/*', array('controller' => 'students', 'action' => 'register'));
      Router::connect('/users/request_tutor/*', array('controller' => 'students', 'action' => 'request_tutor'));
      Router::connect('/users/search/tutor_search_results/*', array('controller' => 'students', 'action' => 'tutor_search_results'));
      Router::connect('/users/search/tutor_search/*', array('controller' => 'students', 'action' => 'tutor_search'));
      Router::connect('/users/view/tutor_profile/*', array('controller' => 'students', 'action' => 'tutor_details_profile'));
      Router::connect('/profile_search_error/*', array('controller' => 'search', 'action' => 'profile_search_error'));

      Router::connect('/users/accounts/confirm/*', array('controller' => 'students', 'action' => 'verify'));
      Router::connect('/users/accounts/signin/*', array('controller' => 'students', 'action' => 'login'));
      Router::connect('/users/contacts/send_message/*', array('controller' => 'students', 'action' => 'message_tutor'));

	   Router::connect('/user/tutor_request/zip_and_subject/*', array('controller' => 'students', 'action' => 'zip_subject'));
	   Router::connect('/user/tutor_request/intro/*', array('controller' => 'students', 'action' => 'introduction'));
	   Router::connect('/user/tutor_request/level/*', array('controller' => 'students', 'action' => 'grade_level'));
	   Router::connect('/user/tutor_request/start/*', array('controller' => 'students', 'action' => 'lesson_start'));
	   Router::connect('/user/tutor_request/schedule/*', array('controller' => 'students', 'action' => 'availability'));
	   Router::connect('/user/tutor_request/location/*', array('controller' => 'students', 'action' => 'lesson_location'));
	   Router::connect('/user/tutor_request/school/*', array('controller' => 'students', 'action' => 'student_school'));
	   Router::connect('/user/tutor_request/form/*', array('controller' => 'students', 'action' => 'final_reg_form'));
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      //Un-Authenticated Tutor Links
      Router::connect('/users/accounts/joinus/*', array('controller' => 'tutors', 'action' => 'joinus'));
      Router::connect('/users/jobs/job_search_results/*', array('controller' => 'tutors', 'action' => 'job_search_results'));
      Router::connect('/users/jobs/job_details/*', array('controller' => 'tutors', 'action' => 'job_details'));
      Router::connect('/job_search_error/*', array('controller' => 'jobs', 'action' => 'job_search_error'));
      Router::connect('/users/accounts/verify/*', array('controller' => 'tutors', 'action' => 'verify'));

      //Authenticated Tutor Links
      Router::connect('/users/jobs/job_search_results_auth/*', array('controller' => 'tutors', 'action' => 'job_search_results_auth'));
      Router::connect('/job_apps/my_job_applications/*', array('controller' => 'tutors', 'action' => 'my_job_applications'));
      Router::connect('/users/jobs/job_details_auth/*', array('controller' => 'tutors', 'action' => 'job_details_auth'));
      Router::connect('/users/accounts/apply/*', array('controller' => 'tutors', 'action' => 'join_via_job'));
      Router::connect('/users/upload_photo/*', array('controller' => 'tutors', 'action' => 'upload_photo'));
      ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	  
	   Router::connect('/users/accounts/sign_up/*', array('controller' => 'commons', 'action' => 'user_sign_up'));

	//  Router::connect('/message/*', array('controller' => 'commons', 'action' => 'message'));
	 // Router::connect('/message_relay/*', array('controller' => 'commons', 'action' => 'message_relay'));
	 // Router::connect('/channelauth/*', array('controller' => 'commons', 'action' => 'channelauth'));
	//  Router::connect('/chats/*', array('controller' => 'commons', 'action' => 'getChat'));
	//  Router::connect('/usertyping/*', array('controller' => 'commons', 'action' => 'typing'));

	 Router::connect('/hc/en-us/*', array('controller' => 'commons', 'action' => 'help'));

	  Router::connect('/user/subscribe/*', array('controller' => 'subscriptions', 'action' => 'subscribe'));
	  Router::connect('/user/subscribe_success/*', array('controller' => 'subscriptions', 'action' => 'subscribe_success'));

	  Router::connect('/learner/message/*', array('controller' => 'students', 'action' => 'message'));
	  Router::connect('/learner/message_relay/*', array('controller' => 'students', 'action' => 'message_relay'));
	  Router::connect('/learner/channelauth/*', array('controller' => 'students', 'action' => 'channelauth'));
	  Router::connect('/learner/chats/*', array('controller' => 'students', 'action' => 'getChat'));
	  Router::connect('/learner/usertyping/*', array('controller' => 'students', 'action' => 'typing'));

	   Router::connect('/teacher/message/*', array('controller' => 'tutors', 'action' => 'message'));
	  Router::connect('/teacher/message_relay/*', array('controller' => 'tutors', 'action' => 'message_relay'));
	  Router::connect('/teacher/channelauth/*', array('controller' => 'tutors', 'action' => 'channelauth'));
	  Router::connect('/teacher/chats/*', array('controller' => 'tutors', 'action' => 'getChat'));
	  Router::connect('/teacher/usertyping/*', array('controller' => 'tutors', 'action' => 'typing'));
	  //Router::connect('/users/push/*', array('controller' => 'tutors', 'action' => 'push'));

      // Common Links
      Router::connect('/users/accounts/login/*', array('controller' => 'commons', 'action' => 'login'));
      Router::connect('/users/accounts/forgot_password/*', array('controller' => 'commons', 'action' => 'reset_password'));



/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
