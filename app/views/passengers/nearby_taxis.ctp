<!-- File: /app/views/passengers/nearby_taxis.ctp -->

<!-- HTML + PHP -->

<?php 

//Passenger attributes
$passengerId = $thisPassenger['Passenger']['id'];
$passengerName = $thisPassenger['Passenger']['name'];
list ($passengerLat, $passengerLng) = explode(",", $thisPassenger['Passenger']['csv_latlng']);
?>

<h2 align="center">Taxis nearby passenger <?php echo $passengerName?></h2>

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
    					<li><a href="#" onclick="requestTaxi()">Request Taxi</a></li>
    					<li><?php echo $this->Html->link("Return", array('action' => 'index')); ?></li>
    					</ul>
    			</ul>
    		</td>
    		<td width="70%" align="center">
    			<?php echo $googleMapV3->map(array('width'=>'800px',                //Width of the map 
    									   'height'=>'600px',                //Height of the map 
											'zoom'=>15,                        //Zoom 
                							'type'=>'HYBRID',                 //Type of map (ROADMAP, SATELLITE, HYBRID or TERRAIN) 
                							'latitude'=>$passengerLat,    //Default latitude if the browser doesn't support localization or you don't want localization
                							'longitude'=>$passengerLng,    //Default longitude if the browser doesn't support localization or you don't want localization
                							'localize'=>false,                //Boolean to localize your position or not
                							'mapListener' => getMapClickJSListener($this, 'Request')
            	));?>
    		</td>
  			</tr>
</table>
</div>

<!-- JavaScript -->

<script>

//Possible actions
NearbyTaxisActions = {
	NONE : 0,
    REQUEST : 1,
    SELECT_DEST : 2
}

var currentAction = NearbyTaxisActions.NONE;

/* Enables request taxi mode.
*  Called when the user clicks "Request taxi" button. 
*/
function requestTaxi(){
	currentAction = NearbyTaxisActions.REQUEST;
	document.getElementById('status_bar').innerHTML ='Select taxi to request: ';
}

</script>

<!-- PHP -->

<?php

// Helper functions


function getTaxiClickJSListener($modelName, $markerId){
	$add = $modelName."AddForm";
	$taxiIdElement = $modelName."TaxiId";
	return "function(event) {
				if(currentAction == NearbyTaxisActions.REQUEST){
		 			var addForm = document.forms['".$add."'];
		 			addForm.elements['".$taxiIdElement."'].value = ".$markerId.";
					currentAction = NearbyTaxisActions.SELECT_DEST;
					document.getElementById('status_bar').innerHTML ='Select final destination: ';
				}
	}";
}


function getMapClickJSListener($obj, $modelName){
	$addForm = $modelName."AddForm";
	$endLatLngElement = $modelName."EndLatlng";

	return "function(event) {
		 		if(currentAction == NearbyTaxisActions.SELECT_DEST){
		 				var confirmAdd = ".$obj->Js->confirm('Confirm taxi request?').";
				 		if (confirmAdd){
		 						var addForm = document.forms['".$addForm."'];
		 			 			addForm.elements['".$endLatLngElement."'].value = event.latLng.toString();
		 			 			//document.getElementById('status_bar').innerHTML = 'name: ' + passengerName + '. lat: ' + event.latLng.lat() + '. lon: ' + event.latLng.lng(); //debug
		 			 			addForm.submit();
		 			} else {
		 				document.getElementById('status_bar').innerHTML ='&nbsp';
		 				currentAction = NearbyTaxisActions.NONE;
		 			}
		 		}
			}";
}

// Forms

//Adds a form for the request Taxi operation
echo $this->Form->create('Request', array('action' => 'add', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('passenger_id', array('value' => $passengerId));
echo $this->Form->hidden('taxi_id');
echo $this->Form->hidden('end_latlng');
echo $this->Form->end();

//Add a google maps marker for the current passenger

echo $googleMapV3->addMarker(array(
            'id'=>$passengerId,                                //Id of the marker 
            'latitude'=>$passengerLat,        //Latitude of the marker 
            'longitude'=>$passengerLng,        //Longitude of the marker 
            'markerIcon'=> '../img/passenger.png', //Custom icon 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $passengerName));                //Default text inside the information window 

//Add a google maps marker for each nearby taxi

foreach ($nearbyTaxis as $taxi):
	list ($lat, $lng) = explode(",", $taxi['Taxi']['csv_latlng']);
	$taxiId = $taxi['Taxi']['id'];
	$taxiName = $taxi['Taxi']['name'];
	$taxiStatus = $taxi['Taxi']['status'];
	$taxiMarker = ($taxiStatus? '../img/taxi-green.png' : 
								'../img/taxi-red.png');
	echo $googleMapV3->addMarker(array(
            'id'=>$taxiId,                                //Id of the marker 
            'latitude'=>$lat,        //Latitude of the marker 
            'longitude'=>$lng,        //Longitude of the marker 
            'markerIcon'=> $taxiMarker, //Custom icon 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $taxi['Taxi']['name'],                //Default text inside the information window 
			'markerClickListener' => getTaxiClickJSListener('Request',$taxiId)));
 			//'markerClickListener' => $taxiRider->getTaxiClickJSListener($this, 'Delete taxi '.$taxiName.'?', 'Taxi', $taxiId),
 			//'markerDragstartListener' => $taxiRider->getChangePosStartJSListener($this),
 			//'markerDragendListener' => $taxiRider->getChangePosEndJSListener($this, 'Change position of '.$taxiName.'?', 'Taxi', $taxiId)));
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