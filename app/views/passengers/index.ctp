<!-- File: /app/views/passengers/index.ctp -->

<h2 align="center">Passengers View</h2>
<table border="1" align="center">
  <tr>
    <td width="30%" style="font-size:25px">
    <ul>
    <li>Actions:</li>
    	<ul>
    		<li>Add passenger</li>
    		<li>Remove passenger</li>
    		<li>Change position</li>
    		<li>Search nearby taxis</li>
    	</ul>
    </ul>
    
    <!--   	- Register passenger
  	- Remove passenger
  	- Change position
	- Search for nearby taxis
	- Request a taxi
	- Cancel request
	- Retrieve requests
	- Retrieve taxi position
	- Board taxi
	- Leave taxi
	- Write review
	- Get Reviews -->
    
    </td>
    <td width="70%" align="center">
    	<?php echo $googleMapV3->map(array('width'=>'800px',                //Width of the map 
    									   'height'=>'600px',                //Height of the map 
											'zoom'=>9,                        //Zoom 
                							'type'=>'HYBRID',                 //Type of map (ROADMAP, SATELLITE, HYBRID or TERRAIN) 
                							'latitude'=>-34.608417,    //Default latitude if the browser doesn't support localization or you don't want localization
                							'longitude'=>-58.373161,    //Default longitude if the browser doesn't support localization or you don't want localization
                							'localize'=>false,                //Boolean to localize your position or not 
            ));?>
    </td>
  </tr>
</table>

<?php
$i = 0;
foreach ($passengers as $passenger):
	list ($lat, $lng) = explode(",", $passenger['Passenger']['text_pos']);
	echo $googleMapV3->addMarker(array(
            'id'=>$i,                                //Id of the marker 
            'latitude'=>$lat,        //Latitude of the marker 
            'longitude'=>$lng,        //Longitude of the marker 
            'markerIcon'=>'http://mapicons.nicolasmollet.com/wp-content/uploads/mapicons/shape-default/color-ffc11f/shapecolor-color/shadow-1/border-dark/symbolstyle-white/symbolshadowstyle-dark/gradient-no/male-2.png', //Custom icon 
            'shadowIcon'=>'http://mapicons.nicolasmollet.com/wp-content/uploads/mapicons/shape-default/color-ffc11f/shapecolor-color/shadow-1/border-dark/symbolstyle-white/symbolshadowstyle-dark/gradient-no/male-2.png', //Custom shadow 
            'infoWindow'=>true,                    //Boolean to show an information window when you click the marker or not
            'windowText'=>'Name: ' . $passenger['Passenger']['name']                //Default text inside the information window 
	));
	$i++;
endforeach; 
?>