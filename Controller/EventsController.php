<?php
/*
 * Controller/EventsController.php
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
App::uses('UsersController', 'Users.Controller');
App::uses('TutorsController', 'Controller');
App::uses('Categorie', 'Model');
App::uses('Subject', 'Model');
App::uses('TutorSubject', 'Model');
App::uses('Student', 'Model');
App::uses('StudentJobPost', 'Model');
App::uses('EventType', 'Model');
App::uses('Event', 'Model');
App::uses('File', 'Utility');

class EventsController extends TutorsController{


/**
 * Controller name
 *
 * @var string
 */
	var $name = 'Events';
	public $uses = array ('Tutor', 'TutorPreference', 'TutorProfile', 'TutorImage', 'TutorSubject');
    public $helpers = array('ZipCode', 'Html');


/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

		parent::beforeFilter();

}

public function index() {
	  $this->layout='tutor';
  if ($this->Auth->loggedIn()) {
     	 return $this->redirect(array('action' => 'home'));
   } else {
      return $this->redirect(array('controller' => 'commons', 'action' => 'index'));
   }

}

public function home() {

        $this->layout='tutor';
		$this->set('title_for_layout', 'Daraji - Tutor Home');
 }

	function add_calendar_event() {
	  $this->layout='ajax_spa';
	   $id = null;
       $event = new Event();
	   if($this->request->is('ajax')) {
		if (!empty($this->request->data)) {
			//$this->request->data[$this->modelClass->Event]['id'] = $this->Auth->user('id');
			//$this->{$this->modelClass}->Event->id = $this->Auth->user('id');
			 if(!empty($this->request->data['id']) &&
                                $this->request->data['id'] != null) {
                            $id = $this->request->data['id'];
             }

			$this->request->data['tutor_id'] = $this->Auth->user('id');

			if (($data = $event->find(
                            'first', array(
                            'conditions' => array(
                                'Event.tutor_id' => $this->Auth->user('id'),
                                'Event.id'  => $id)))) )
                     {

							$this->set('success', false);
                            $this->Session->setFlash('This Event already exists', 'custom_msg');
                     }

			$event->create();
			if ($event->saveEvent($this->request->data)) {
				$this->Session->setFlash(__('The event has been saved', true));
				$this->redirect(array('action' => 'calendar'));
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.', true));
			}
		}
	   }

		//$this->set('eventTypes', $this->Event->EventType->find('list'));
		$this->set('eventTypes', $eventType->find('list'));
		//$this->set('eventTypes', $this->{$this->modelClass}->EventType->find('list'));
	}

	 // The event_list action is called from the javascript to get the list of events (JSON)
	function list_calendar_events() {

		//$this->layout = "ajax";
		$this->layout = 'ajax_spa';
		//die("Here");
	    if (!$this->request->is('ajax')) {
         throw new MethodNotAllowedException();
        }

	  if(!empty($this->request->data)) {
		$conditions = array('conditions' => array(
			'tutor_id' =>  $this->Auth->user('id')  //The tutor must own the events
			//'UNIX_TIMESTAMP(start) >=' => $this->request->data['start'],
			//'UNIX_TIMESTAMP(start) <=' => $this->request->data['end']
		 ));


        //debug($this->Auth->user('id'));
		//debug($conditions);
		//debug($this->modelClass); die();

		$event = new Event();
		//$events = $this->{$this->modelClass}->find('all', $conditions);
		$events = $event->find('all', $conditions);

		//debug($conditions);
		//debug($events); //die();

		//print_r($conditions);
		// Scheduled(green) , Submitted (grey) and Unsubmitted(yellow)
		// scheduled_lesson
		// submitted_lesson
		// unsubmitted_lesson
		//die();
		$data = array();
        if(!empty($events) && count($events) > 0 ) {
			foreach($events as $event) {

				$data[] = array(
						'id' => $event['Event']['id'],
						'title'=>$event['Event']['title'],
						'start'=>str_replace(' ', 'T', $event['Event']['start']),
						'end' => str_replace(' ', 'T', $event['Event']['end']),
						'dow' => [ 1, 4 ] // Repeat monday and thursday
						'url' => Router::url('/') . 'lessons/view_lesson/'.$event['Event']['event_id'], //Router::url('/') . 'full_calendar/events/view/'.$event['Event']['id'],
						'url_edit' => Router::url('/') . 'lessons/edit_lesson/'.$event['Event']['event_id'],
						'details' => $event['Event']['details'],
						//'className' => $event['EventType']['class_name'],
						'className' => $event['Event']['class_name'],
						'color' => $event['Event']['color'],
						'eventId' => $event['Event']['event_id']

				);
			}
        }
		//debug($data);
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;

		}
	}

	//To view a Single Event
	function view($id = null) {
	   $this->layout = 'student';
		if (!$id) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('event', $this->Event->read(null, $id));
	}

     //To Edit a Single Event
	function edit($id = null) {
	   $this->layout = 'ajax_spa';
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('action' => 'index'));
		}

		if (!empty($this->data)) {
			if ($this->Event->save($this->data)) {
				$this->Session->setFlash(__('The event has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Event->read(null, $id);
		}
		$this->set('eventTypes', $this->Event->EventType->find('list'));
	}

	function delete($id = null) {
	   $this->layout = 'ajax_spa';
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for event', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Event->delete($id)) {
			$this->Session->setFlash(__('Event deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Event was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}

 // The update action is called from "webroot/js/ready.js" to update date/time when an event is dragged or resized
	function update() {
	    $this->layout = 'ajax_spa';
		//$vars = $this->params['url'];
		if (!empty($this->request->data)) {
			$event->id = $this->request->data['id']; //$vars['id'];
			$event->saveField('start', $this->request->data['start']);
			$event->saveField('end', $this->request->data['end']); //$vars['end']);
			//$event->saveField('all_day', $vars['allday']);
			$this->Session->setFlash(__('The event has been updated', true));
		    $this->redirect(array('action' => 'calendar'));
		} else {
			$this->Session->setFlash(__('The event could not be updated.', true));
		}
	}

}
?>
