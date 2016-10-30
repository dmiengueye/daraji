<?php
App::uses('User', 'Users.Model');

class Student extends User {
    public $name = 'Student';
	var $virtualFields = array('name' => 'CONCAT(first_name, " ", last_name)');
	public $displayField = 'first_name';
	
/**
 * Validation parameters
 *
 * @var array
 */
	public $validate = array(
		'first_name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'required' => true, 'allowEmpty' => false,
				'message' => 'Please enter a first name.'
			),
			'alpha' => array(
				'rule' => array('alphaNumeric'),
				'message' => 'The first name must be alphanumeric.'
			),
		),
		'last_name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'required' => true, 'allowEmpty' => false,
				'message' => 'Please enter a last name.'
			),
			'alpha' => array(
				'rule' => array('alphaNumeric'),
				'message' => 'The last name must be alphanumeric.'
			),
		),
		'email' => array(
			'isValid' => array(
				'rule' => 'email',
				'required' => true,
				'message' => 'Please enter a valid email address.'
			),
			'isUnique' => array(
				'rule' => array('isUnique', 'email'),
				'message' => 'This email is already in use.'
			)
		),
        'confirm_email' => array(
			'rule' => 'confirmEmail',
			'message' => 'The email are not equal, please try again.'
		),
		'password' => array(
			'too_short' => array(
				'rule' => array('minLength', '6'),
				'message' => 'The password must have at least 6 characters.'
			),
			'required' => array(
				'rule' => 'notEmpty',
				'message' => 'Please enter a password.'
			)
		),
		'temppassword' => array(
			'rule' => 'confirmPassword',
			'message' => 'The passwords are not equal, please try again.'
		),
        'zip_code' => array(
			'rule' => 'alphaNumeric',
			'message' => 'Please enter valid zip code.'
		),
		'tos' => array(
			'rule' => array('custom','[1]'),
			'message' => 'You must agree to the terms of use.'
		)
	);
}