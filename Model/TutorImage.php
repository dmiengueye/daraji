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
class TutorImage extends AppModel {
    

/**
 * Name
 *
 * @var string
 */
	public $name = 'TutorImage';
    public $useTable = 'tutor_images';

public $server_path  = "C:\app\wamp\www\cakephp-2.4.5\app\webroot";
//public function __construct() {
  //  $this->server_path = "C:\wamp\www\cakephp-2.4.5\app\webroot"; //$_SERVER['DOCUMENT_ROOT'];
    
//}
public $belongsTo = array(
        'Tutor' => array(
            'className' => 'Tutor',
            'foreignKey' => 'tutor_id'
        )
    );

var $actsAs = array(
    
   'Uploader.Attachment' => array(
        'image' => array(
            'dbColumn' => 'image',
            'tempDir' => 'C:/app/wamp/www/cakephp-2.4.5/app/tmp', 
            //'tempDir' => TMP,
            //'tempDir'  => 'https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimage',
			//'uploadDir' => 'c:/wamp/www/cakephp-2.4.5/app/webroot/img/files/uploads',
			//'finalPath' => '/files/uploads/',
            'uploadDir' => 'https://s3-us-west-2.amazonaws.com/www.daraji.com/images/testimg',
            //'uploadDir' => 'd2g1ajtxd18vox.cloudfront.net/images/users',
			'finalPath' => '/images/testimg/',
            'stopSave' => true,
            'overwrite' => true, 
            'transport' => array(
				'class' => AttachmentBehavior::S3,
				//'accessKey' => 'AKIAJRVF7AG2CTQPTSQQ',
				//'secretKey' => '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',
				'accessKey' => 'AKIAJHLIYSOQJN5MWMLQ',
				'secretKey' => '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',
				'bucket' => 'www.daraji.com',
				'region' => Aws\Common\Enum\Region::US_WEST_2,
				'folder' => 'images/testimg/',
                'scheme' => 'https'
			),           
            'width' => 100,
            'height' => 100,
            'class' => 'resize',
            'self' => true,
            'maxWidth' => 100,
			'minHeight' => 100,
			'extension' => array('gif', 'jpg', 'png', 'jpeg'),
			'type' => 'image',
			'mimeType' => array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'),
			'filesize' => 5242880,
			'required' => true,
            'metaColumns' => array(
				'ext' => 'extension',
				'type' => 'mimeType',
				'size' => 'fileSize',
				//'exif.model' => 'camera'
			),
            'transforms' => array(
                'thumb_image' => array(
                    'prepend' => 'thumb_',
                    'method' => 'resize',
                    'width' => 152,
                    'height' => 185,
                    'aspect' => true,
                    'expand' => true,
                    'mode' => 'width'
                ),
                'thumb_medium' => array(
                    'prepend' => 'thumb_medium_',
                    'method' => 'resize',
                    'width' => 80, //70,
                    'height' => 71, //60,
                    'aspect' => true,
                    'expand' => true,
                    'mode' => 'width'
                ),
                
            ),
        )
    ),
    
   
  'Uploader.FileValidation' => array(
        'image' => array(
            'required' => true,
            'extension' => array('jpg', 'jpeg', 'png', 'gif'),
            'mimeType' => array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'),
            'type' => 'image',
            'extension' => array(
                'value' => array('jpg', 'jpeg', 'png', 'gif'),
                'error' => 'Invalid image extension'
            ),
            'mimeType' => array(
                'value' => array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'),
                'error' => 'Invalid mime type'
            ),
            'type' => array(
                'value' => 'image',
                'error' => 'Invalid type'
            )
        )
    )
);


public function beforeUpload($options) {
	//$options['finalPath'] = '/img/uploads/' 
	//$options['uploadDir'] = WWW_ROOT . $options['finalPath'];
	 $options['created'] = date('Y-m-d H:i:s') ;
	 return $options;
}

public function saveProfilePhoto($id, $postData = array()) {

      // debug($postData); die();
      $this->beforeUpload($postData);
      $this->set($postData);
      $this->save($postData);

  			return true;
   }
   
   public function savePhoto($id, $postData = array()) {

       //debug($postData); die();
         if(!empty($id)) {
           $postData['TutorImage']['id'] = $id;  //write the pk into the data array so it know this an update an not a create
        }
           if($this->save($postData, array(
  		 				'validate' => false,
  		 				'callbacks' => false))){
							
                   return true;
  			 } else  {
               return false;
               }
   }


public function get_tutor_profile_pic($id) {
    
  $pic = "";
     $data = $this->find('first',
            array(
              'conditions' => array(
                'TutorImage.tutor_id'  => $id,
                 'TutorImage.status'  => '1',
                 'TutorImage.featured'  => '1',
              
                )
              ));
              
      //debug($data); die();   
      if(!empty($data)){
			//foreach ($data as $key => $value) {
			 //debug($data['TutorSubject']['subject_credentials']); die();
				if(!empty($data['TutorImage']['thumb_medium'])){
					$pic = $data['TutorImage']['thumb_medium'];
				}
			//}
		}
        //debug($pic); die();
  return $pic;
}


} //end of class