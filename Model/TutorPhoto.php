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

App::uses('Security', 'Utility');
App::uses('UsersAppModel', 'Users.Model');
App::uses('SearchableBehavior', 'Search.Model/Behavior');
App::uses('SluggableBehavior', 'Utils.Model/Behavior');
App::uses('AttachmentBehavior', 'Uploader.Model/Behavior');
App::uses('FileValidationBehavior', 'Uploader.Model/Behavior');

/**
 * Users Plugin User Model
 *
 * @package User
 * @subpackage User.Model
 */
class TutorPhoto extends AppModel {
    

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorPhoto';

public $server_path  = "/";
public function __construct() {
    $this->server_path = "C:\wamp\www\cakephp-2.4.5\app\webroot"; //$_SERVER['DOCUMENT_ROOT'];
    
}
public $belongsTo = array(
        'Tutor' => array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
        )
    );

public $actsAs = array(
	'Uploader.Attachment' => array(
		// Do not copy all these settings, it's merely an example
		'image' => array(
			//'nameCallback' => '',
			//'append' => '',
			//'prepend' => '',
			'tempDir' => TMP,
			'uploadDir' => 'C:/wamp/www/cakephp-2.4.5/app/webroot/files/uploads',
			'transportDir' => '',
			'finalPath' => '/files/uploads',
			//'dbColumn' => 'image',
			//'metaColumns' => array(),
			//'defaultPath' => '',
			//'overwrite' => false,
			'stopSave' => true,
			'allowEmpty' => true,
            'overwrite' => true,
			'transforms' => array(
                 'imageSmall' => array(
					'class' => 'crop',
					'append' => '-small',
					'overwrite' => true,
					'self' => false,
					'width' => 65,
					'height' => 50
				),
				'imageMedium' => array(
					'class' => 'resize',
					'append' => '-medium',
					'width' => 90,
					'height' => 60,
					'aspect' => false
				)
            
            
            ),
			//'transformers' => array(),
			//'transport' => array(),
			//'transporters' => array(),
			//'curl' => array()
		)
	),
    
    'Uploader.FileValidation' => array(
		'image' => array(
			'maxWidth' => 120,
			'minHeight' => 106,
			'extension' => array('gif', 'jpg', 'png', 'jpeg'),
            'required' => array(
				'value' => true,
				'error' => 'File required'
			),
			'type' => 'image',
			'mimeType' => array('image/gif'),
			'filesize' => 5242880,
			'required' => true
		),
        'thumbnail' => array(
			'required' => false
		)
	)
);


} //end of class