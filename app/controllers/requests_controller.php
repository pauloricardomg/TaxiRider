<?php

class RequestsController extends AppController {

	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js', 'TaxiRider');
	var $uses = array('Request','Passenger');
	var $name = 'Requests';

	var $components = array('Session');
	
	function add() {
		if (!empty($this->data)) {
			//Retrieve begin position from selected passenger
			$this->Passenger->id = $this->data['Request']['passenger_id'];
			$passengerPos = $this->Passenger->read('position');
			$this->data['Request']['start_position'] = $passengerPos['Passenger']['position'];
			
			//Retrieve end latitude and longitude from view
			$end_latlng = $this->data['Request']['end_latlng'];
			unset($this->data['Request']['end_latlng']);
			
			//Convert end position to geospatial representation
			$endPos = $this->Passenger->convertToPostGisPoint($end_latlng);
			$this->data['Request']['end_position'] = $endPos;
			
			//Add request
			if ($this->Request->save($this->data)) {
				$this->Session->setFlash('Request successfully added.');				
				$this->redirect('/passengers/requests/id:'.$this->Passenger->id);
			}
		}
		
		//No view - redirect to index view
		$this->Session->setFlash('Failed add request: empty data provided');
		$this->redirect('/passengers/index');
	}

	function update(){
		$flashMsg = 'Failed to update request: empty or invalid data provided';
		$namedParams = $this->params['named'];
		$model = 'passengers';
		
		if (!empty($namedParams)) {
			//Get the model from where the request originates (taxis or passengers)
			if(isset($namedParams['model'])){
				$model = $namedParams['model'];
				unset($namedParams['model']);
			}
						
			//Try to update request
			if($this->Request->save($namedParams)){
				$flashMsg = 'Request succesfully updated';
				$this->Session->setFlash($flashMsg);
				
				$field = 'passenger_id';
				if($model == 'taxis'){
					$field = 'taxi_id';
				}
				
				//Get passenger or taxi id to redirect to original requests view
				$queryResult = $this->Request->read(array('Request.'.$field));
				$entityId = $queryResult['Request'][$field];
				$this->redirect('/'.$model.'/requests/id:'.$entityId);
			}
		}
		
		//failure - return to index view of current model (default: passengers)
		$this->Session->setFlash($flashMsg);
		$this->redirect('/'.$model.'/index');
	}
	
	function writeReview() {
		$flashMsg = null;
		if (!empty($this->data)) {
			if ($this->Request->save($this->data, array('id', 'review'))) {
				$this->Session->setFlash('Review successfully added.');
				
				//Get passenger id to redirect to original requests view
				$queryResult = $this->Request->read(array('Passenger.id'));
				$passengerId = $queryResult['Passenger']['id'];
				$this->redirect('/passengers/requests/id:'.$passengerId);
			}
		}
	
		//No view - redirect to index view
		$this->Session->setFlash('Failed to write review: empty or invalid data provided');
		$this->redirect('/passengers/index');
	}
}

?>