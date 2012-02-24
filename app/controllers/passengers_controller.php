<?php

class PassengersController extends AppController {
	
	var $helpers = array ('Html','Form');
	//var $uses = array('Passenger', 'Request');
	var $name = 'Passengers';
	
	//var $components = array('Session');
	
	function index() {
		$this->set('passengers', $this->Passenger->find('all'));
	}
	
// 	function add() {
// 		if (!empty($this->data)) {
// 			if ($this->Passenger->save($this->data)) {
// 				$this->Session->setFlash('Passenger successfully added.');
// 				//$this->redirect(array('action' => 'index'));
// 			}
// 		}
// 	}
}

?>