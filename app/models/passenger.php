<?php
class Passenger extends AppModel {
	
	var $name = "Passenger";
	
	//Virtual field to retrieve points in postgresql
	var $virtualFields = array('point_as_text' => 'AsText(Passenger.position)',
							   'point_as_kml' => 'ST_AsKML(Passenger.position)');
	//var $hasMany = 'Request';
	
	function afterFind($results) {
		foreach ($results as $key => $val):
			if (isset($val['Passenger']['point_as_text'])){
				$postgisPos = $val['Passenger']['point_as_text'];
				$csvLatLng = $this->convertToCsv($postgisPos);
				unset($val['Passenger']['point_as_text']);
				$results[$key]['Passenger']['csv_latlng'] = $csvLatLng;
			}			
		endforeach;
		return $results;
	}
}
?>