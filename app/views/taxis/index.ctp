<!-- File: /app/views/taxis/index.ctp -->

<!-- HTML + PHP -->

<h2 align="center">Taxis View</h2>

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
    					<li><a href="#" onclick="addTaxi()">Add taxi</a></li>
    					<li><a href="#" onclick="deleteTaxi()">Remove taxi</a></li>
    					<li><a href="#" onclick="changePosition()">Change position</a></li>
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
                							'mapListener' => getTaxiMapClickListener($this, 'Taxi name?', 'Taxi')
            	));?>
    		</td>
  			</tr>
</table>
</div>

<!-- JavaScript -->

<script>

	//Possible actions
	TaxiActions = {
			NONE : 0,
		    ADD : 1,
		    DELETE : 2,
		    MOVE : 3,
		    REQUESTS : 4
	}

	var currentAction = TaxiActions.NONE;
	
	/* View requests of a given taxi
	*  Called when the user clicks "View Requests" button. 
	*/
	function viewRequests(){
		currentAction = TaxiActions.REQUESTS;
		document.getElementById('status_bar').innerHTML ='Select taxi to view requests:';
	}
	
	/* Enables delete-mode.
	*  Called when the user clicks "Remove Taxi" button. 
	*/
	function deleteTaxi(){
		currentAction = TaxiActions.DELETE;
		document.getElementById('status_bar').innerHTML ='Select taxi:';
	}

	/* Enables add-mode.
	*  Called when the user clicks "Add Taxi" button. 
	*/
	function addTaxi(){
		currentAction = TaxiActions.ADD;
		document.getElementById('status_bar').innerHTML ='Select new taxi position:';
	}

	/* Enables change-position mode.
	*  Called when the user clicks "Change Position" button. 
	*/
	function changePosition(){
		document.getElementById('status_bar').innerHTML ='Drag and drop taxi to new position: ';

		//Enable dragging markers
		for(var i=0; i<markers.length; i++) {
			var value = markers[i];
			value.setDraggable(true);
		}
		
	}
</script>

<!-- PHP -->

<?php

// Helpers

/**
* Gets a google map click listener that will submit the
* operation (add or list requests) to the server upon click on the map
*
* Assumptions:
*
* - This listener needs an external "currentAction" variable, which
* is an enumeration (TaxiActions) that indicates what is the active
* action currently.
* 
* - Also equires JS helper being specified in the controller ($obj)
*
* @param unknown_type $obj the document object
* @param unknown_type $confirmMsg a message requesting the name of the entity to add
* @param unknown_type $modelName the name of the model being added
* @return string the javascript listener for the add action
*/
function getTaxiMapClickListener($obj, $confirmMsg, $modelName){
	$addForm = $modelName."AddForm";
	$nameElement = $modelName."Name";
	$latLngElement = $modelName."Latlng";

	return "function(event) {
		 		if(currentAction == TaxiActions.ADD){
		 			var taxiName = ".$obj->Js->prompt($confirmMsg, '').";
		 			if (taxiName != null){
		 						var addForm = document.forms['".$addForm."'];
		 						addForm.elements['".$nameElement."'].value = taxiName;
		 			 			addForm.elements['".$latLngElement."'].value = event.latLng.toString();
		 			 			//document.getElementById('status_bar').innerHTML = 'name: ' + taxiName + '. lat: ' + event.latLng.lat() + '. lon: ' + event.latLng.lng(); //debug
		 			 			addForm.submit();
		 			} else {
		 				document.getElementById('status_bar').innerHTML ='&nbsp';
		 				currentAction = TaxiActions.NONE;
		 			}
		 		}
			}";
}

/**
*
* Gets a google map marker click listener that will submit the
* (delete) operation to the server upon click on the marker
*
* Assumptions:
*
* - This listener needs an external "currentAction" variable, which
* is an enumeration (TaxiActions) that indicates what is the active
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
function getTaxiMarkerClickJSListener($obj, $taxiName, $modelName, $markerId){
	$delForm = $modelName."DeleteForm";
	$idElement = $modelName."Id";
	$requestsForm = $modelName."RequestsForm";
	return "function(event) {
		 		if(currentAction == TaxiActions.DELETE){
		 			var confirmDel = ".$obj->Js->confirm('Delete taxi '.$taxiName.'?').";
		 			if (confirmDel){
		 						var delForm = document.forms['".$delForm."'];
		 						delForm.elements['".$idElement."'].value = ".$markerId.";
		 			 			delForm.submit();
		 			} else {
		 				document.getElementById('status_bar').innerHTML ='&nbsp';
		 				toDelete = false;
		 			}
		 		} else if (currentAction == TaxiActions.REQUESTS){
				 		var confirmReq = ".$obj->Js->confirm("View requests from ".$taxiName."?").";
				 		if (confirmReq){
				 			window.location = '".$obj->Html->url(array( "action" => "requests", "id" => $markerId))."';
				 		} else{
				 			document.getElementById('status_bar').innerHTML ='&nbsp';
				 			currentAction = PassengerActions.NONE;
				 		}
		 		} else {
		 			infowindow".$markerId.".open(map,marker".$markerId.");
		 		}
			}";
}

// Forms

//Adds a form for the delete operation
echo $this->Form->create('Taxi', array('action' => 'delete', 'type' => 'delete', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('id');
echo $this->Form->end();

//Adds a form for the add operation
echo $this->Form->create('Taxi', array('action' => 'add', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('name');
echo $this->Form->hidden('latlng');
echo $this->Form->end();

//Adds a form for the change position operation
echo $this->Form->create('Taxi', array('action' => 'changePosition', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('id');
echo $this->Form->hidden('latlng');
echo $this->Form->end();

//Add a google maps marker for each taxi in the DB

foreach ($taxis as $taxi):
	list ($lat, $lng) = explode(",", $taxi['Taxi']['csv_latlng']);
	$taxiId = $taxi['Taxi']['id'];
	$taxiName = $taxi['Taxi']['name'];
	$taxiStatus = $taxi['Taxi']['status'];
	$taxiMarker = ($taxiStatus? "img/taxi-green.png" : 
								"img/taxi-red.png");
	echo $googleMapV3->addMarker(array(
            'id'=>$taxiId,                                //Id of the marker 
            'latitude'=>$lat,        //Latitude of the marker 
            'longitude'=>$lng,        //Longitude of the marker 
            'markerIcon'=> $taxiMarker, //Custom icon 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $taxi['Taxi']['name'],                //Default text inside the information window 
 			'markerClickListener' => getTaxiMarkerClickJSListener($this, $taxiName, 'Taxi', $taxiId),
 			'markerDragstartListener' => $taxiRider->getChangePosStartJSListener($this),
 			'markerDragendListener' => $taxiRider->getChangePosEndJSListener($this, 'Change position of '.$taxiName.'?', 'Taxi', $taxiId)));
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