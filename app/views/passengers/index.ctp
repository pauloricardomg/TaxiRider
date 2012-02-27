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
    					<li>Search nearby taxis</li>
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
                							'mapListener' => getAddJSListener($this), 
            	));?>
    		</td>
  			</tr>
</table>
</div>

<!-- JavaScript -->

<script>
	//delete flag
	var toDelete = false;
	//add flag
	var toAdd = false;
	//change position flag
	var changePos = false;

	/* Enables delete-mode.
	*  Called when the user clicks "Remove Passenger" button. 
	*/
	function deletePassenger(){
		toDelete = true;
		document.getElementById('status_bar').innerHTML ='Select passenger:';
	}

	/* Enables add-mode.
	*  Called when the user clicks "Add Passenger" button. 
	*/
	function addPassenger(){
		toAdd = true;
		document.getElementById('status_bar').innerHTML ='Select new passenger position:';
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

// Helper methods

/**
 * Gets a google map marker listener that will submit the 
 * deletion request to the server upon click on the marker
 * 
 * @param unknown_type $obj the document object
 * @param unknown_type $passengerName the name of the passenger being deleted (for confirmation)
 * @param unknown_type $passengerId the ide of the passenger in the DB
 * @return string the javascript listener for the action
 */
function getDeleteJSListener($obj, $passengerName, $passengerId){
	return "function(event) {
	 		if(toDelete){
	 			var confirmDel = ".$obj->Js->confirm('Delete passenger '.$passengerName.'?').";
	 			if (confirmDel){
	 						var delForm = document.forms['PassengerDeleteForm'];
	 						delForm.elements['PassengerId'].value = ".$passengerId.";
	 			 			delForm.submit();
	 			} else{
	 				document.getElementById('status_bar').innerHTML ='&nbsp';
	 				toDelete = false;
	 			}
	 		} else {			
	 			infowindow".$passengerId.".open(map,marker".$passengerId.");
	 		}
		}";
}

/**
* Gets a google map listener that will submit the
* add request to the server upon click on the map
*
* @param unknown_type $obj the document object
* @return string the javascript listener for the action
*/
function getAddJSListener($obj){
	return "function(event) {
	 		if(toAdd){
	 			var passengerName = ".$obj->Js->prompt('Passenger name?', '').";
	 			if (passengerName != null){
	 						var addForm = document.forms['PassengerAddForm'];
	 						addForm.elements['PassengerName'].value = passengerName;
	 			 			addForm.elements['PassengerLatlng'].value = event.latLng.toString();
	 			 			//document.getElementById('status_bar').innerHTML = 'name: ' + passengerName + '. lat: ' + event.latLng.lat() + '. lon: ' + event.latLng.lng(); //debug
	 			 			addForm.submit();
	 			} else {
	 				document.getElementById('status_bar').innerHTML ='&nbsp';
	 				toAdd = false;
	 			}
	 		}
		}";
}

/**
* Gets a google map marker listener that will record the
* initial position of the marker before being moved
*
* @param unknown_type $obj the document object
* @return string the javascript listener for the action
*/
function getChangePosStartJSListener($obj){
	return "function(event) {
					initialLatLng = event.latLng;
		}";
}

/**
 * Gets a google map marker listener that will change
 * the position of a passenger, or roll back to the
 * previous position if the user cancels the operation
 *
 * @param unknown_type $obj the document object
 * @param unknown_type $passengerName
 * @param unknown_type $passengerId
 * 
 * @return string the javascript listener for the action
 */
function getChangePosEndJSListener($obj, $passengerName, $passengerId){
	return "function(event) {
		 			var confirmChangePos = ".$obj->Js->confirm('Change position of '.$passengerName.'?').";
	 				if (confirmChangePos){
	 						var changePosForm = document.forms['PassengerChangePositionForm'];
	 						changePosForm.elements['PassengerId'].value = ".$passengerId.";
	 			 			changePosForm.elements['PassengerLatlng'].value = event.latLng.toString();
	 			 			changePosForm.submit();	 				
					} else{
	 					document.getElementById('status_bar').innerHTML ='&nbsp';
						//Return initial position
						marker".$passengerId.".setPosition(initialLatLng);
												
						//Disable dragging markers
						for(var i=0; i<markers.length; i++) {
							var value = markers[i];
							value.setDraggable(false);
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

//Add a google maps markert for each passenger in the DB

foreach ($passengers as $passenger):
	list ($lat, $lng) = explode(",", $passenger['Passenger']['csv_latlng']);
	$passengerId = $passenger['Passenger']['id'];
	$passengerName = $passenger['Passenger']['name'];
	echo $googleMapV3->addMarker(array(
            'id'=>$passengerId,                                //Id of the marker 
            'latitude'=>$lat,        //Latitude of the marker 
            'longitude'=>$lng,        //Longitude of the marker 
            'markerIcon'=>'http://mapicons.nicolasmollet.com/wp-content/uploads/mapicons/shape-default/color-ffc11f/shapecolor-color/shadow-1/border-dark/symbolstyle-white/symbolshadowstyle-dark/gradient-no/male-2.png', //Custom icon 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $passenger['Passenger']['name'],                //Default text inside the information window 
 			'markerClickListener' => getDeleteJSListener($this, $passengerName, $passengerId),
 			'markerDragstartListener' => getChangePosStartJSListener($this),
 			'markerDragendListener' => getChangePosEndJSListener($this, $passengerName, $passengerId)));
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