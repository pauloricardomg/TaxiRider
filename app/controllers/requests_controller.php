<?php

class RequestsController extends AppController {

	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js', 'TaxiRider');
	var $uses = array('Request','Passenger');
	var $name = 'Requests';

	var $components = array('Session');
	
	function add() {
		$flashMsg = null;
		if (!empty($this->data)) {
			//Retrieve begin position from selected passenger
			$this->Passenger->id = $this->data['Request']['passenger_id'];
			$passengerPos = $this->Passenger->read('position');
			$this->data['Request']['start_position'] = $passengerPos['Passenger']['position'];
			
			//Retrieve end latitude and longitude from view
			$end_latlng = $this->data['Request']['end_latlng'];
			unset($this->data['Request']['end_latlng']);
			
			//Convert to geospatial representation
			$endPos = $this->Passenger->convertToPostGisPoint($end_latlng);
			$this->data['Request']['end_position'] = $endPos;
			
			//Add passenger
			if ($this->Request->save($this->data)) {
				$flashMsg = 'Request successfully added.';
			}
		} else{
			$flashMsg = 'Failed add request: empty data provided';
		}
		
		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect('/passengers/index');
	}
}

?>