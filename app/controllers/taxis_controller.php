<?php

//TODO: Refactor taxis and passengers controller - use components, since they share a considerable amount of code
class TaxisController extends AppController {

	var $helpers = array ('Html','Form', 'GoogleMapV3', 'Js', 'TaxiRider');
	var $uses = array('Taxi', 'Request');
	var $name = 'Taxis';

	var $components = array('Session');

	function index() {
		$taxis = $this->Taxi->find('all', array("fields" => array("id", "name", "status", "position_as_text")));

		$this->set('taxis', $taxis);
	}

	function delete() {
		$flashMsg = null;
		if(!empty($this->data)){
			$userToDelete = $this->data['Taxi']['id'];
			if ($this->Taxi->delete($userToDelete)) {
				$flashMsg = 'Taxi has been deleted.';
			}
		} else{
			$flashMsg = 'Failed delete taxi: empty data provided';
		}

		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect(array('action' => 'index'));
	}

	function changePosition() {
		$flashMsg = null;
		if (!empty($this->data)) {
			$this->Taxi->id = $this->data['Taxi']['id'];
			//Retrieve selected latitude and longitude from view
			$latlng = $this->data['Taxi']['latlng'];
			unset($this->data['Taxi']['latlng']);

			//Convert to geospatial representation
			$postGisPoint = $this->Taxi->convertToPostGisPoint($latlng);
			$this->data['Taxi']['position'] = $postGisPoint;
			if ($this->Taxi->save($this->data, array('id', 'position'))) {
				$flashMsg = 'Taxi position changed.';
			}
		}  else{
			$flashMsg = 'Failed change taxi position: empty data provided';
		}

		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect(array('action' => 'index'));
	}

	function add() {
		$flashMsg = null;
		if (!empty($this->data)) {
			//Retrieve selected latitude and longitude from view
			$latlng = $this->data['Taxi']['latlng'];
			unset($this->data['Taxi']['latlng']);
				
			//Convert to geospatial representation
			$postGisPoint = $this->Taxi->convertToPostGisPoint($latlng);
			$this->data['Taxi']['position'] = $postGisPoint;

			//Add taxi
			if ($this->Taxi->save($this->data, array('id', 'name', 'position'))) {
				$flashMsg = 'Taxi successfully added.';
			}
		} else{
			$flashMsg = 'Failed add taxi: empty data provided';
		}

		//No view - redirect to index view
		$this->Session->setFlash($flashMsg);
		$this->redirect(array('action' => 'index'));
	}
	
	function requests(){
		$thisData = $this->data;
		$namedParams = $this->params['named'];
	
		//Data can be received either by POST or named parameters
		if (!empty($thisData) || !empty($namedParams)) {
			//For simplicity, returning current taxi, instead of placing in the session
			$taxiId = null;
			if(!empty($thisData)){
				$taxiId = $thisData['Taxi']['id'];
			} else {
				$taxiId = $namedParams['id'];
			}
			$thisTaxi = $this->Taxi->read(array("id", "name", "position_as_text", "status"), $taxiId);
	
			//Also, retruning all requests from that taxi
			$taxiRequests = $this->Request->findAllByTaxiId($taxiId, array('Request.id', 'Request.status', 'Request.created',
																									  'Request.modified', 'Request.start_pos_as_text', 
																									  'Request.end_pos_as_text', 'Request.review', 
																									  'Request.passenger_picked','Passenger.name'), 'Request.id');
			
			//Setting variables to the view
			$this->set('thisTaxi', $thisTaxi);
			$this->set('requests', $taxiRequests);
		} else {
			//No view - redirect to index view
			$this->Session->setFlash('Failed to list requests: empty data provided');
			$this->redirect(array('action' => 'index'));
		}
	}

}

?>