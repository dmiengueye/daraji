<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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


class ChiropractorsController extends AppController {
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Chiropractor';
	public $uses = array ('Chiropractor') ;//

/**
 * beforeFilter callback
 *
 * @return void
 **/
public function beforeFilter() {

		parent::beforeFilter();
       
}


public function index() {

     $this->set('title_for_layout', 'Dr Timothy Richardson- Patient Home');
     //$this->layout='student';
}

public function about_us() {

     $this->set('title_for_layout', 'Dr Timothy Richardson- About Us');
     //$this->layout='student';
}

public function contact_us() {

     $this->set('title_for_layout', 'Dr Timothy Richardson- Contact');
     //$this->layout='student';
}

public function faqs() {

     $this->set('title_for_layout', 'Dr Timothy Richardson- FQAQs');
     //$this->layout='student';
}

public function services() {

     $this->set('title_for_layout', 'Dr Timothy Richardson- Services');
     //$this->layout='student';
}




}