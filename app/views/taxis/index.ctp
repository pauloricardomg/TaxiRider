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
                							'mapListener' => $taxiRider->getAddJSListener($this, 'Taxi name?', 'Taxi')
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
	*  Called when the user clicks "Remove Taxi" button. 
	*/
	function deleteTaxi(){
		toDelete = true;
		document.getElementById('status_bar').innerHTML ='Select taxi:';
	}

	/* Enables add-mode.
	*  Called when the user clicks "Add Taxi" button. 
	*/
	function addTaxi(){
		toAdd = true;
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
	$taxiMarker = ($taxiStatus? "http://mapicons.nicolasmollet.com/wp-content/uploads/mapicons/shape-default/color-0ef256/shapecolor-color/shadow-1/border-dark/symbolstyle-contrast/symbolshadowstyle-dark/gradient-iphone/taxi.png" : 
								"http://mapicons.nicolasmollet.com/wp-content/uploads/mapicons/shape-default/color-f00e0e/shapecolor-color/shadow-1/border-dark/symbolstyle-white/symbolshadowstyle-dark/gradient-no/taxi.png");
	echo $googleMapV3->addMarker(array(
            'id'=>$taxiId,                                //Id of the marker 
            'latitude'=>$lat,        //Latitude of the marker 
            'longitude'=>$lng,        //Longitude of the marker 
            'markerIcon'=> $taxiMarker, //Custom icon 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $taxi['Taxi']['name'],                //Default text inside the information window 
 			'markerClickListener' => $taxiRider->getTaxiClickJSListener($this, 'Delete taxi '.$taxiName.'?', 'Taxi', $taxiId),
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