<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after core.php
 *
 * This file should load/create any application wide configuration settings, such as
 * Caching, Logging, loading additional configuration files.
 *
 * You should also use this file to include any files that provide global functions/constants
 * that your application uses.
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
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('CakeSession', 'Model/Datasource');
//CakeSession::$requestCountdown = 1;

// Setup a 'default' cache configuration for use in the application.
Cache::config('default', array('engine' => 'File'));

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 *
 * App::build(array(
 *     'Model'                     => array('/path/to/models/', '/next/path/to/models/'),
 *     'Model/Behavior'            => array('/path/to/behaviors/', '/next/path/to/behaviors/'),
 *     'Model/Datasource'          => array('/path/to/datasources/', '/next/path/to/datasources/'),
 *     'Model/Datasource/Database' => array('/path/to/databases/', '/next/path/to/database/'),
 *     'Model/Datasource/Session'  => array('/path/to/sessions/', '/next/path/to/sessions/'),
 *     'Controller'                => array('/path/to/controllers/', '/next/path/to/controllers/'),
 *     'Controller/Component'      => array('/path/to/components/', '/next/path/to/components/'),
 *     'Controller/Component/Auth' => array('/path/to/auths/', '/next/path/to/auths/'),
 *     'Controller/Component/Acl'  => array('/path/to/acls/', '/next/path/to/acls/'),
 *     'View'                      => array('/path/to/views/', '/next/path/to/views/'),
 *     'View/Helper'               => array('/path/to/helpers/', '/next/path/to/helpers/'),
 *     'Console'                   => array('/path/to/consoles/', '/next/path/to/consoles/'),
 *     'Console/Command'           => array('/path/to/commands/', '/next/path/to/commands/'),
 *     'Console/Command/Task'      => array('/path/to/tasks/', '/next/path/to/tasks/'),
 *     'Lib'                       => array('/path/to/libs/', '/next/path/to/libs/'),
 *     'Locale'                    => array('/path/to/locales/', '/next/path/to/locales/'),
 *     'Vendor'                    => array('/path/to/vendors/', '/next/path/to/vendors/'),
 *     'Plugin'                    => array('/path/to/plugins/', '/next/path/to/plugins/'),
 * ));
 *
 */

/**
 * Custom Inflector rules can be set to correctly pluralize or singularize table, model, controller names or whatever other
 * string is passed to the inflection functions
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */

/**
 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call
 * Uncomment one of the lines below, as you need. Make sure you read the documentation on CakePlugin to use more
 * advanced ways of loading plugins
 *
 * CakePlugin::loadAll(); // Loads all plugins at once
 * CakePlugin::load('DebugKit'); //Loads a single plugin named DebugKit
 *
 */
  CakePlugin::load('Uploader', array('routes' => true));
  CakePlugin::loadAll();
  
  /**
  CakePlugin::load('DebugKit');
  CakePlugin::load('Tools', array('bootstrap' => true));
  CakePlugin::load('Users', array('routes' => true));
  CakePlugin::load('Users', array('routes' => true));
  **/


/**
 * You can attach event listeners to the request lifecycle as Dispatcher Filter. By default CakePHP bundles two filters:
 *
 * - AssetDispatcher filter will serve your asset files (css, images, js, etc) from your themes and plugins
 * - CacheDispatcher filter will read the Cache.check configure variable and try to serve cached content generated from controllers
 *
 * Feel free to remove or add filters as you see fit for your application. A few examples:
 *
 * Configure::write('Dispatcher.filters', array(
 *		'MyCacheFilter', //  will use MyCacheFilter class from the Routing/Filter package in your app.
 *		'MyPlugin.MyFilter', // will use MyFilter class from the Routing/Filter package in MyPlugin plugin.
 * 		array('callable' => $aFunction, 'on' => 'before', 'priority' => 9), // A valid PHP callback type to be called on beforeDispatch
 *		array('callable' => $anotherMethod, 'on' => 'after'), // A valid PHP callback type to be called on afterDispatch
 *
 * ));
 */


Configure::write('App.defaultEmail', 'donald.guy35@gmail.com');
Configure::write('Users.emailConfig', 'gmail');

Configure::write('ipinfo_api_key', '795d03c0f1b0373fe77c6cf8799765b87ab48c46f879ea7b725e5839bcd2a52f');

Configure::write('referal', array(
     'Student' => 'Student',
     'Tutor' => 'Tutor',
	 'Google' => 'Google Search',
	 'Yahoo' => 'Yahoo! Search',
	 'Bing' => 'Bing Search',
	 'WM' => 'Word Of Mouth',
	 'Others' => 'Others'
));

Configure::write('carrier', array(
     '1' => 'AT&T',
     '2' => 'Verizon',
	 '3' => 'Sprint',
	 '4' => 'T-Mobile'
));

Configure::write('education', array(
     'Elem School' => 'Elementary School Ed',
     'Middle School' => 'Middle School Ed',
	 'High School Grad' => 'High School Grad',
     'Current College Undergrad' => 'Current College Undergrad',
	 'College Grad' => 'College Grad',
	 'Graduate Level' => 'Graduate Level',
	 'Post Graduate' => 'Post Graduate'
));

Configure::write('gender', array(
     'M' => 'Male',
     'F' => 'Female'
));


Configure::write('states', array(
'AA'  =>  'Armed Forces America',
'AE'  => 'Armed Forces',
'AP'  => 'Armed Forces Pacific',
'AK'  => 'Alaska',
'AL'  => 'Alabama',
'AR'  => 'Arkansas',
'AZ'  => 'Arizona',
'CA'  => 'California',
'CO'  => 'Colorado',
'CT'  => 'Connecticut',
'DC'  => 'Washington DC',
'DE'  => 'Delaware',
'FL'  => 'Florida',
'GA'  => 'Georgia',
'GU'  => 'Guam',
'HI'  => 'Hawaii',
'IA'  => 'Iowa',
'ID'  => 'Idaho',
'IL'  => 'Illinois',
'IN'  => 'Indiana',
'KS'  => 'Kansas',
'KY'  => 'Kentucky',
'LA'  => 'Louisiana',
'MA'  => 'Massachusetts',
'MD'  => 'Maryland',
'ME'  => 'Maine',
'MI'  => 'Michigan',
'MN'  => 'Minnesota',
'MO'  => 'Missouri',
'MS'  => 'Mississippi',
'MT'  => 'Montana',
'NC'  => 'North Carolina',
'ND'  => 'North Dakota',
'NE'  => 'Nebraska',
'NH'  => 'New Hampshire',
'NJ'  => 'New Jersey',
'NM'  => 'New Mexico',
'NV'  => 'Nevada',
'NY'  => 'New York',
'OH'  => 'Ohio',
'OK'  => 'Oklahoma',
'OR'  => 'Oregon',
'PA'  => 'Pennsylvania',
'PR'  => 'Puerto Rico',
'RI'  => 'Rhode Island',
'SC'  => 'South Carolina',
'SD'  => 'South Dakota',
'TN'  => 'Tennessee',
'TX'  => 'Texas',
'UT'  => 'Utah',
'VA'  => 'Virginia',
'VI'  => 'Virgin Islands',
'VT'  => 'Vermont',
'WA'  => 'Washington',
'WI'  => 'Wisconsin',
'WV'  => 'West Virginia',
'WY'  => 'Wyoming'
));

Configure::write('locations', array(
     'M' => 'Mobile',
     'H' => 'Home',
	 'O' => 'Office'
));

Configure::write('phonetypes', array(
     'M' => 'Mobile',
     'H' => 'Home',
	 'O' => 'Office'
));

Configure::write('leadTimes', array(
     '1' =>  '1 hour',
     '8' =>  '8 hours',
     '12' => '12 hours',
	 '16' => '16 hours',
	 '24' => '24 hours',
	 '36' => '36 hours',
	 '48' => '48 hours'	 
));

Configure::write('distances', array(
	 '20' => '20 miles',
     '1' => '1 mile',
     '5' => '5 miles',
	 '10' => '10 miles',
	 '15' => '15 miles',
	 //'5' => '20 miles',
	 '25' => '25 miles',
	 '30' => '30 miles',
	 '35' => '35 miles',
	 '40' => '40 miles',
     //'9' => '45 miles',
     //'10' => '50 miles'
));

Configure::write('sortcriteria', array(
     'Lowest Price' => 'Lowest Price',
     'Highest Price' => 'Highest Price',
	 'Distance' => 'Distance',
	 'hours' => 'Hours',
     'Ratings' => 'Ratings'
));

Configure::write('categories', array(
     'Msps' => 'Most Popular',
     'Math' => 'Mathematics',
     'Science' => 'Science',
	 'Engineering' => 'Engineering',
	 'Tech' => 'Technology'
     //'Ratings' => 'Ratings'
));

Configure::write('popularsubjects', array(
     'Alg1' => 'Algebra 1',
     'Calc2' => 'Calculus-II',
     'LinAlg' => 'Linear Algebra',
	 'BioChem' => 'BioChemistry',
	 'SATMath' => 'SAT-Math',
     'SATSci' => 'SAT-Science'
));

Configure::write('Dispatcher.filters', array(
	'AssetDispatcher',
	'CacheDispatcher'
));




//Configure::write('Exception.renderer', 'DarajiExceptionRenderer');

//Configure::write('Exception', array(
   // 'handler' => 'ErrorHandler::handleException',
    //'renderer' => 'AppExceptionRenderer',
    //'log' => true
//));
/**
 * Configures default file logging options
 */
App::uses('CakeLog', 'Log');
CakeLog::config('debug', array(
	'engine' => 'File',
	'types' => array('notice', 'info', 'debug'),
	'file' => 'debug',
));
CakeLog::config('error', array(
	'engine' => 'File',
	'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
	'file' => 'error',
));


/**
 * sessions
 */
// cur_zip_code			:	current user's zip code
/**
 * end sessions
 */