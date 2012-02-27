<?php

class PassengersController extends AppController {
	
	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js');
	//var $uses = array('Passenger', 'Request');
	var $name = 'Passengers';
	
	var $components = array('Session');
	
	function index() {
		$passengers = $this->Passenger->find('all', array("fields" => array("id", "name", "text_pos")));
		
		//Convert PostGis POINT(lat lng) format to csv "lat, lng" string.
		foreach ($passengers as &$passenger):
			$postgisPos = $passenger['Passenger']['text_pos'];
			$csvPos = $this->Passenger->convertToCsv($postgisPos);
			$passenger['Passenger']['text_pos'] = $csvPos;
		endforeach;
		
		$this->set('passengers', $passengers);
	}
	
	function delete() {
		$userToDelete = $this->data['Passenger']['id'];
		if ($this->Passenger->delete($userToDelete)) {
			$this->Session->setFlash('Passenger has been deleted.');
			$this->redirect(array('action' => 'index'));
		}
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