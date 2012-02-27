<?php

class PassengersController extends AppController {
	
	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js');
	//var $uses = array('Passenger', 'Request');
	var $name = 'Passengers';
	
	var $components = array('Session');
	
	function index() {
		$passengers = $this->Passenger->find('all', array("fields" => array("id", "name", "point_as_text")));
		
		//Convert PostGis POINT(lat lng) format to csv "lat, lng" string.
		foreach ($passengers as &$passenger):
			$postgisPos = $passenger['Passenger']['point_as_text'];
			$csvLatLng = $this->Passenger->convertToCsv($postgisPos);
			unset($passenger['Passenger']['point_as_text']);
			$passenger['Passenger']['csv_latlng'] = $csvLatLng;
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
	
	function add() {
		if (!empty($this->data)) {
			//Retrieve selected latitude and longitude from view
			$lat = $this->data['Passenger']['lat'];
			$lng = $this->data['Passenger']['lng'];
			unset($this->data['Passenger']['lat']);
			unset($this->data['Passenger']['lng']);
			
			//Convert to geospatial representation
			$postGisPoint = $this->Passenger->convertToPostGisPoint($lat, $lng);
			$this->data['Passenger']['position'] = $postGisPoint;

			//Add passenger
			if ($this->Passenger->save($this->data, array('id', 'name', 'position'))) {
				$this->Session->setFlash('Passenger successfully added.');
				$this->redirect(array('action' => 'index'));
			}
		} else{
			$this->Session->setFlash('Failed.');
			$this->redirect(array('action' => 'index'));
		}
	}
}

?>