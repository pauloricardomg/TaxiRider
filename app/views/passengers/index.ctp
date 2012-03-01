<!-- File: /app/views/passengers/index.ctp -->

<!-- HTML + PHP -->

<h2 align="center">Passengers View</h2>

<div style="width: 100%;">
<table class="sample">
<tr>
	<td id="status_bar" style="color: red; font-weight: bold; text-align: center" colspan="2"><?php echo $this->Session->flash(); ?>&nbsp</td>
</tr>
<tr>
  			<td style="vertical-align: text-top;" width="30%">
  				<ul>
  					<li>Actions:</li>
  						<ul>
    					<li><a href="#" onclick="addPassenger()">Add passenger</a></li>
    					<li><a href="#" onclick="deletePassenger()">Remove passenger</a></li>
    					<li><a href="#" onclick="changePosition()">Change position</a></li>
    					<li><a href="#" onclick="nearbyTaxis()">Search nearby taxis</a></li>
    					<li><a href="#" onclick="viewRequests()">View requests</a></li>
    					</ul>
    			</ul>
    		</td>
    		<td width="70%" align="center">
    			<?php echo $googleMapV3->map(array('width'=>'800px',                //Width of the map 
    									   'height'=>'600px',                //Height of the map 
											'zoom'=>15,                        //Zoom 
                							'type'=>'HYBRID',                 //Type of map (ROADMAP, SATELLITE, HYBRID or TERRAIN) 
                							'latitude'=>-34.608417,    //Default latitude if the browser doesn't support localization or you don't want localization
                							'longitude'=>-58.373161,    //Default longitude if the browser doesn't support localization or you don't want localization
                							'localize'=>false,                //Boolean to localize your position or not
                							'mapListener' => getPassengerMapClickJSListener($this, 'Passenger')
            	));?>
    		</td>
  			</tr>
</table>
</div>

<!-- JavaScript -->

<script>
	//Possible actions
	PassengerActions = {
		NONE : 0,
	    ADD : 1,
	    DELETE : 2,
	    NEARBY_TAXIS : 3,
	    REQUESTS : 4
	}

	var currentAction = PassengerActions.NONE;

	/* Enables delete-mode.
	*  Called when the user clicks "Remove Passenger" button. 
	*/
	function deletePassenger(){
		currentAction = PassengerActions.DELETE;
		document.getElementById('status_bar').innerHTML ='Select passenger: ';
	}

	/* Enables add-mode.
	*  Called when the user clicks "Add Passenger" button. 
	*/
	function addPassenger(){
		currentAction = PassengerActions.ADD;
		document.getElementById('status_bar').innerHTML ='Select new passenger position:';
	}

	/* Search nearby taxis
	*  Called when the user clicks "Search nearby taxis" button. 
	*/
	function nearbyTaxis(){
		currentAction = PassengerActions.NEARBY_TAXIS;
		document.getElementById('status_bar').innerHTML ='Select passenger to search taxis nearby:';
	}

	/* View requests of a given passenger
	*  Called when the user clicks "View Requests" button. 
	*/
	function viewRequests(){
		currentAction = PassengerActions.REQUESTS;
		document.getElementById('status_bar').innerHTML ='Select passenger to view requests:';
	}

	/* Enables change-position mode.
	*  Called when the user clicks "Change Position" button. 
	*/
	function changePosition(){
		document.getElementById('status_bar').innerHTML ='Drag and drop passenger to new position: ';

		//Enable dragging markers
		for(var i=0; i<markers.length; i++) {
			var value = markers[i];
			value.setDraggable(true);
		}
	}
</script>

<!-- PHP -->

<?php

// Helper functions

/**
*
* Gets a google map marker click listener that will either delete the
* selected passenger, search the nearby taxis or list requests 
* according to the active action
*
* Assumptions:
*
* - This listener needs an external "currentAction" variable, which
* is an enumeration (PassengerActions) that indicates what is the active
* action currently.
*
* - Requires JS helper being specified in the controller
*
* - Assumes marker id is the id of the object to be removed in the DB
*
* @param unknown_type $obj the document object
* @param string $confirmMsg the delete confirmation message to be shown on a popup
* @param unknown_type $modelName the name of the model being deleted
* @param string $markerId the identifier of the marker to which this listener will
* 						be associated
* @return the javascript listener for the action
*/
function getPassengerClickJSListener($obj, $passengerName, $modelName, $markerId){
	$delForm = $modelName."DeleteForm";
	$nearbyTaxisForm = $modelName."NearbyTaxisForm";
	$requestsForm = $modelName."RequestsForm";
	$idElement = $modelName."Id";
	$distanceElement = $modelName."Distance";
	return "function(event) {
				switch(currentAction){
					case PassengerActions.DELETE:
				 		var confirmDel = ".$obj->Js->confirm("Delete ".$passengerName."?").";
				 		if (confirmDel){
				 					var delForm = document.forms['".$delForm."'];
				 					delForm.elements['".$idElement."'].value = ".$markerId.";
				 		 			delForm.submit();
				 		} else{
				 			document.getElementById('status_bar').innerHTML ='&nbsp';
				 			currentAction = PassengerActions.NONE;
				 		}
						break;
					case PassengerActions.NEARBY_TAXIS:
				 		var confirmReq = ".$obj->Js->confirm("Search taxis nearby ".$passengerName."?").";
				 		if (confirmReq){
				 					var nearbyForm = document.forms['".$nearbyTaxisForm."'];
				 					nearbyForm.elements['".$idElement."'].value = ".$markerId.";
				 					var distance = ".$obj->Js->prompt("Search taxis within which distance? (m)", '').";
				 					nearbyForm.elements['".$distanceElement."'].value = distance;
				 		 			nearbyForm.submit();
				 		} else{
				 			document.getElementById('status_bar').innerHTML ='&nbsp';
				 			currentAction = PassengerActions.NONE;
				 		}
						break;
					case PassengerActions.REQUESTS:
				 		var confirmReq = ".$obj->Js->confirm("View requests from ".$passengerName."?").";
				 		if (confirmReq){
				 			window.location = '".$obj->Html->url(array( "action" => "requests", "id" => $markerId))."';
				 		} else{
				 			document.getElementById('status_bar').innerHTML ='&nbsp';
				 			currentAction = PassengerActions.NONE;
				 		}
					default:
  						infowindow".$markerId.".open(map,marker".$markerId.");
				}
			}";
}


/**
* Gets a google map click listener that will submit the
* add request to the server upon click on the map
* 
* Assumptions:
* 
* - This listener needs an external "currentAction" variable, which
* is an enumeration (PassengerActions) that indicates what is the active
* action currently.
* 
* - Requires JS helper being specified in the controller ($obj)
*
* @param unknown_type $obj the document object
*  @param unknown_type $condition condition in which the routine is executed
* @param unknown_type $confirmMsg a message requesting the name of the entity to add
* @param unknown_type $modelName the name of the model being added
* @return string the javascript listener for the add action
*/
function getPassengerMapClickJSListener($obj, $modelName){
	$addForm = $modelName."AddForm";
	$nameElement = $modelName."Name";
	$latLngElement = $modelName."Latlng";

	return "function(event) {
		 		if(currentAction == PassengerActions.ADD){
		 			var passengerName = ".$obj->Js->prompt('Passenger name?', '').";
		 			if (passengerName != null){
		 						var addForm = document.forms['".$addForm."'];
		 						addForm.elements['".$nameElement."'].value = passengerName;
		 			 			addForm.elements['".$latLngElement."'].value = event.latLng.toString();
		 			 			//document.getElementById('status_bar').innerHTML = 'name: ' + passengerName + '. lat: ' + event.latLng.lat() + '. lon: ' + event.latLng.lng(); //debug
		 			 			addForm.submit();
		 			} else {
		 				document.getElementById('status_bar').innerHTML ='&nbsp';
		 				currentAction = PassengerActions.NONE;
		 			}
		 		}
			}";
}

// Forms

//Adds a form for the delete operation
echo $this->Form->create('Passenger', array('action' => 'delete', 'type' => 'delete', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('id');
echo $this->Form->end();

//Adds a form for the add operation
echo $this->Form->create('Passenger', array('action' => 'add', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('name');
echo $this->Form->hidden('latlng');
echo $this->Form->end();

//Adds a form for the change position operation
echo $this->Form->create('Passenger', array('action' => 'changePosition', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('id');
echo $this->Form->hidden('latlng');
echo $this->Form->end();

//Adds a form for the search nearby taxis operation
echo $this->Form->create('Passenger', array('action' => 'nearbyTaxis', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('id');
echo $this->Form->hidden('distance');
echo $this->Form->end();

//Add a google maps marker for each passenger in the DB

foreach ($passengers as $passenger):
	list ($lat, $lng) = explode(",", $passenger['Passenger']['csv_latlng']);
	$passengerId = $passenger['Passenger']['id'];
	$passengerName = $passenger['Passenger']['name'];
	echo $googleMapV3->addMarker(array(
            'id'=>$passengerId,                                //Id of the marker 
            'latitude'=>$lat,        //Latitude of the marker 
            'longitude'=>$lng,        //Longitude of the marker 
            'markerIcon'=>'img/passenger.png', //Custom icon 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $passenger['Passenger']['name'],                //Default text inside the information window 
 			'markerClickListener' => getPassengerClickJSListener($this, $passengerName, 'Passenger', $passengerId),
 			'markerDragstartListener' => $taxiRider->getChangePosStartJSListener($this),
 			'markerDragendListener' => $taxiRider->getChangePosEndJSListener($this, 'Change position of '.$passengerName.'?', 'Passenger', $passengerId)));
endforeach; 
?>

<!-- CSS -->

<style type="text/css">
table.sample {
	border-width: 1px;
	border-spacing: 2px;
	border-style: none;
	border-color: black;
	border-collapse: separate;
	background-color: white;
	font-size: 25px;
	empty-cells: show;
	margin-left: auto;
	margin-right: auto;
}
table.sample th {
	border-width: 1px;
	padding: 1px;
	border-style: solid;
	border-color: black;
	background-color: white;
	-moz-border-radius: ;
}
table.sample td {
	border-width: 1px;
	padding: 1px;
	border-style: solid;
	border-color: black;
	background-color: white;
	-moz-border-radius: ;
}
</style>