<?php

class PassengersController extends AppController {
	
	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js', 'TaxiRider');
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
		$flashMsg = null;
		if(!empty($this->data)){
			$userToDelete = $this->data['Passenger']['id'];
			if ($this->Passenger->delete($userToDelete)) {
				$flashMsg = 'Passenger has been deleted.';
			}	
		} else{
			$flashMsg = 'Failed delete passenger: empty data provided';
		}
		
		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect(array('action' => 'index'));
	}
	
	function changePosition() {
		$flashMsg = null;
		if (!empty($this->data)) {
			$this->Passenger->id = $this->data['Passenger']['id'];
			//Retrieve selected latitude and longitude from view
			$latlng = $this->data['Passenger']['latlng'];
			unset($this->data['Passenger']['latlng']);

			//Convert to geospatial representation
			$postGisPoint = $this->Passenger->convertToPostGisPoint($latlng);
			$this->data['Passenger']['position'] = $postGisPoint;
			if ($this->Passenger->save($this->data, array('id', 'position'))) {
				$flashMsg = 'Passenger position changed.';
			}
		}  else{
			$flashMsg = 'Failed change passenger position: empty data provided';
		}
		
		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect(array('action' => 'index'));
	}
	
	function add() {
		$flashMsg = null;
		if (!empty($this->data)) {
			//Retrieve selected latitude and longitude from view
			$latlng = $this->data['Passenger']['latlng'];
			unset($this->data['Passenger']['latlng']);
			
			//Convert to geospatial representation
			$postGisPoint = $this->Passenger->convertToPostGisPoint($latlng);
			$this->data['Passenger']['position'] = $postGisPoint;

			//Add passenger
			if ($this->Passenger->save($this->data, array('id', 'name', 'position'))) {
				$flashMsg = 'Passenger successfully added.';
			}
		} else{
			$flashMsg = 'Failed add passenger: empty data provided';
		}
		
		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect(array('action' => 'index'));
	}
		
}

?>