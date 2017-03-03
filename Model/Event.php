<?php
/*
 * Model/Event.php
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
 
class Event extends AppModel {
	var $name = 'Event';
	var $displayField = 'title';
	var $validate = array(
	
		'title' => array('required' => array(
			   						     'rule' => array('notEmpty'),
			   						     'required' => true, 'allowEmpty' => false,
			   						     'message' => 'Please Provide an Event Title.')),
		
		
		'start' => array('required' => array(
			   						     'rule' => array('notEmpty', 'time'),
			   						     'required' => true, 'allowEmpty' => false,
			   						     'message' => 'Enter a Valid Start time.')),
	);

	var $belongsTo = array(
		'EventType' => array(
			'className' => 'EventType', //'FullCalendar.EventType',
			'foreignKey' => 'event_type_id'
		),
		
	);
	
	public function saveEvent($data)
	{
		//$this->create();
		debug($data); die();
		if($this->save($data)) {
			return true;
	    } 
		return false;		
	}

}
?>
