 <?php
/**
 *
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
 * @since         CakePHP(tm) v 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * This is email configuration file.
 *
 * Use it to configure email transports of CakePHP.
 *
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *  Mail - Send using PHP mail function
 *  Smtp - Send using SMTP
 *  Debug - Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email. Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 */
class EmailConfig {

	public $default = array(
		'transport' => 'Mail',
		//'from' => 'you@localhost',
		'from' => 'info@daraji.com',
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8'
	 );

     public $mailgun = array(
        'transport' => 'Mailgun',
        'mg_domain'    => 'sandbox27bec34416544b3bb9fba4f17ccadc4f.mailgun.org',
        'mg_api_key'   => 'key-b910a0f7d3ab34011f4d836ef8ddc4ff',
        //'from' => array('no-reply@wizwonk.com' => 'Daraji'),
         'from' => array('info@wizwonk.com' => 'Daraji'),

        // Custom mailgun email, e.g.:
        // 'o:tag' => 'tag1',
        // 'o:campaign' => 'my-campaign',
    );

     public $mailgun_plug = array(
	        'transport' => 'Mailgun.Mailgun',
	        'mg_domain'    => 'wizwonk.com', //'sandbox27bec34416544b3bb9fba4f17ccadc4f.mailgun.org',
	        'mg_api_key'   => 'key-b910a0f7d3ab34011f4d836ef8ddc4ff',
	        //'from' => array('no-reply@wizwonk.com' => 'Daraji'),
	         'from' => array('info@wizwonk.com' => 'Daraji'),

	        // Custom mailgun email, e.g.:
	        // 'o:tag' => 'tag1',
	        // 'o:campaign' => 'my-campaign',
    );

	public $gmail = array(
	        //'from' => array('info@daraji.com' => 'My Site'),
	        'from' => array('info@daraji.com' => 'daraji.com'),
			//'host' => 'ssl://smtp.gmail.com',
            'host' => 'ssl://smtp.gmail.com',
		    'port' => 465,
		   // 'ssl' => 'yes',
			'username' => 'donald.guy35@gmail.com',
			'password' => 'wizwonkdaraji2015',
	        'transport' => 'smtp',
	        //'timeout' => 75
	       // 'client' => null
	);
	 public $atar = array(
	        'host' => 'smtp.gmail.com',
	        'port' => 465,
	        'ssl' => 'yes',
	        'username' => '',
	        'password' => '',
	        'transport' => 'Smtp'
    );

	public $smtp = array(
		'transport' => 'Smtp',
		'from' => array('site@localhost' => 'My Site'),
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'log' => false,
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

	public $fast = array(
		'from' => 'you@localhost',
		'sender' => null,
		'to' => null,
		'cc' => null,
		'bcc' => null,
		'replyTo' => null,
		'readReceipt' => null,
		'returnPath' => null,
		'messageId' => true,
		'subject' => null,
		'message' => null,
		'headers' => null,
		'viewRender' => null,
		'template' => false,
		'layout' => false,
		'viewVars' => null,
		'attachments' => null,
		'emailFormat' => null,
		'transport' => 'Smtp',
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'log' => true,
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

}
