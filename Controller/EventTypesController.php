<?php
/*
 * Controllers/EventTypesController.php
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
 
class EventTypesController extends AppController {

	var $name = 'EventTypes';

	function add_event_type() {
	   $this->layout = 'ajax_spa';
		if (!empty($this->request->data)) {
			$this->{$this->modelClass}->create();
			if ($this->{$this->modelClass}->save($this->data)) {
				$this->Session->setFlash(__('The event type has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The event type could not be saved. Please, try again.', true));
			}
		}
	}
	
	function view($id = null) {
	   $this->layout = 'ajax_spa';
		if(!$id) {
			$this->Session->setFlash(__('Invalid event type', true));
			//$this->redirect(array('action' => 'index'));
			$this->redirect($this->referer('/'));
		}
		$this->set('eventType', $this->{$this->modelClass}->read(null, $id));
	}

	function edit($id = null) {
	   $this->layout = 'student';
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid event type', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->EventType->save($this->data)) {
				$this->Session->setFlash(__('The event type has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The event type could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->EventType->read(null, $id);
		}
	}

	function delete($id = null) {
	   $this->layout = 'student';
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for event type', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->EventType->delete($id)) {
			$this->Session->setFlash(__('Event type deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Event type was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
?>
