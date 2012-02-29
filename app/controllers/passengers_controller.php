<?php

class PassengersController extends AppController {
	
	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js', 'TaxiRider');
	var $uses = array('Passenger', 'Taxi', 'Request');
	var $name = 'Passengers';
	
	var $components = array('Session');
	
	function index() {
		$passengers = $this->Passenger->find('all', array("fields" => array("id", "name", "point_as_text")));
		
		$this->set('passengers', $passengers);
	}
	
	function requests(){
		if (!empty($this->data)) {
			$passengerRequests = $this->Request->findAllByPassengerId($this->data['Passenger']['id']);
			$this->set('requests', $passengerRequests);
		} else {
			//No view - redirect to index view
			$flashMsg = 'Failed to list requests: empty data provided';
			$this->Session->setFlash($flashMsg);
			$this->redirect(array('action' => 'index'));
		}
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
			if ($this->Passenger->save($this->data)) {
				$flashMsg = 'Passenger successfully added.';
			}
		} else{
			$flashMsg = 'Failed add passenger: empty data provided';
		}
		
		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect(array('action' => 'index'));
	}
	
	function nearbyTaxis() {
		$flashMsg = null;
		if (!empty($this->data) && !empty($this->data['Passenger']['id'])) {
			$passengerId = $this->data['Passenger']['id'];
			
			//$this->Passenger->id = $passengerId;
			$thisPassenger = $this->Passenger->read(array("id", "name", "point_as_text"), $passengerId);
						
			$distance = $this->data['Passenger']['distance'];
			$options['fields'] = array("Taxi.id", "Taxi.name", "Taxi.status", "Taxi.point_as_text");
			$options['joins'] = array(
			array('table' => 'passengers',
			        'alias' => 'Passenger',
			        'type' => 'LEFT',
			        'conditions' => array(
						'Passenger.id = '.$this->Passenger->id
					)
			));
			$options['conditions'] = array('ST_DWithin(ST_Transform(Passenger.position,900913), ST_Transform(Taxi.position,900913), '.$distance.')'); //900913 = Google Maps projection
			
			$nearbyTaxis = $this->Taxi->find('all', $options);
			
			$this->set('thisPassenger', $thisPassenger);
			$this->set('nearbyTaxis', $nearbyTaxis);
		}  else{
			$flashMsg = 'Failed to retrieve nearby taxis: empty data provided';
			$this->Session->setFlash($flashMsg);
			$this->redirect(array('action' => 'index'));
		}
	}
		
}

?>