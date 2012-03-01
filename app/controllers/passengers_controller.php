<?php

class PassengersController extends AppController {
	
	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js', 'TaxiRider');
	var $uses = array('Passenger', 'Taxi', 'Request');
	var $name = 'Passengers';
	
	var $components = array('Session');
	
	function index() {
		$passengers = $this->Passenger->find('all', array("fields" => array("id", "name", "position_as_text")));
		
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
			//$this->Passenger->id = $this->data['Passenger']['id'];
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
		if (!empty($this->data) && !empty($this->data['Passenger']['id'])) {
			$passengerId = $this->data['Passenger']['id'];

			//For simplicity also returning current passenger (instead of placing in the session, for example)
			$thisPassenger = $this->Passenger->read(array("id", "name", "position_as_text"), $passengerId);
						
			//Create complex join query - Passengers and Taxis
			$distance = $this->data['Passenger']['distance'];
			$options['fields'] = array("Taxi.id", "Taxi.name", "Taxi.status", "Taxi.position_as_text");
			$options['joins'] = array(
			array('table' => 'passengers',
			        'alias' => 'Passenger',
			        'type' => 'LEFT',
			        'conditions' => array(
						'Passenger.id = '.$this->Passenger->id
					)
			));
			//Only retrieve taxis within certain range
			$options['conditions'] = array('ST_DWithin(ST_Transform(Passenger.position,900913), ST_Transform(Taxi.position,900913), '.$distance.')'); //900913 = Google Maps projection
			$nearbyTaxis = $this->Taxi->find('all', $options);
			
			//Setting variables to the view
			$this->set('thisPassenger', $thisPassenger);
			$this->set('nearbyTaxis', $nearbyTaxis);
		}  else{
			//No view - redirect to index view
			$this->Session->setFlash('Failed to retrieve nearby taxis: empty data provided');
			$this->redirect(array('action' => 'index'));
		}
	}
	
	function requests(){
		$thisData = $this->data;
		$namedParams = $this->params['named'];
	
		//Data can be received either by POST or named parameters
		if (!empty($thisData) || !empty($namedParams)) {
			//For simplicity, returning current passenger, instead of placing in the session
			$passengerId = null;
			if(!empty($thisData)){
				$passengerId = $thisData['Passenger']['id'];
			} else {
				$passengerId = $namedParams['id'];
			}
			$thisPassenger = $this->Passenger->read(array("id", "name", "position_as_text"), $passengerId);
	
			//Also, retruning all requests from that passenger
			$passengerRequests = $this->Request->findAllByPassengerId($passengerId, array('Request.id', 'Request.status', 'Request.created',
																								  'Request.modified', 'Request.start_pos_as_text', 
																								  'Request.end_pos_as_text', 'Request.review', 
																								  'Request.passenger_boarded','Taxi.name'), 'Request.id');
			//Setting variables to the view
			$this->set('thisPassenger', $thisPassenger);
			$this->set('requests', $passengerRequests);
		} else {
			//No view - redirect to index view
			$this->Session->setFlash('Failed to list requests: empty data provided');
			$this->redirect(array('action' => 'index'));
		}
	}	
}

?>