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
    					<li>Add passenger</li>
    					<li><a href="#" onclick="deletePassenger()">Remove passenger</a></li>
    					<li>Change position</li>
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
            	));?>
    		</td>
  			</tr>
</table>
</div>

<!-- JavaScript -->

<script>
	//delete flag
	var toDelete = false;

	/* Enables delete-mode.
	*  Called when the user clicks "Remove Passenger" button. 
	*/
	function deletePassenger(){
		toDelete = true;
		document.getElementById('status_bar').innerHTML ='Select passenger:';
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
 * @return string the javascript listener for th
 */
function getDeleteJSListener($obj, $passengerName, $passengerId){
	return "function(event) {
	 		if(toDelete){
	 			var confirmDel = ".$obj->Js->confirm('Delete passenger '.$passengerName.'?').";
	 			if (confirmDel){
	 						document.getElementById('PassengerId').value = ".$passengerId.";
	 			 			document.forms['PassengerDeleteForm'].submit();
	 			} else{
	 				document.getElementById('status_bar').innerHTML ='&nbsp';
	 				toDelete = false;
	 			}
	 		} else {			
	 			infowindow".$passengerId.".open(map,marker".$passengerId.");
	 		}
		}";
}

// Forms

//Adds a form for the delete operation
echo $this->Form->create('Passenger', array('action' => 'delete', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->input('id');
$this->Form->end();


//Add a google maps markert for each passenger in the DB

foreach ($passengers as $passenger):
	list ($lat, $lng) = explode(",", $passenger['Passenger']['text_pos']);
	$passengerId = $passenger['Passenger']['id'];
	$passengerName = $passenger['Passenger']['name'];
	echo $googleMapV3->addMarker(array(
            'id'=>$passengerId,                                //Id of the marker 
            'latitude'=>$lat,        //Latitude of the marker 
            'longitude'=>$lng,        //Longitude of the marker 
            'markerIcon'=>'http://mapicons.nicolasmollet.com/wp-content/uploads/mapicons/shape-default/color-ffc11f/shapecolor-color/shadow-1/border-dark/symbolstyle-white/symbolshadowstyle-dark/gradient-no/male-2.png', //Custom icon 
            'shadowIcon'=>'http://mapicons.nicolasmollet.com/wp-content/uploads/mapicons/shape-default/color-ffc11f/shapecolor-color/shadow-1/border-dark/symbolstyle-white/symbolshadowstyle-dark/gradient-no/male-2.png', //Custom shadow 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $passenger['Passenger']['name'],                //Default text inside the information window 
 			'markerListener' => getDeleteJSListener($this, $passengerName, $passengerId)));
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