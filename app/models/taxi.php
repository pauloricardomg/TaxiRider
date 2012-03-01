<?php
class Taxi extends AppModel {

	var $name = "Taxi";

	//Virtual field to retrieve points in postgresql
	var $virtualFields = array('position_as_text' => 'AsText(Taxi.position)',
								   'point_as_kml' => 'ST_AsKML(Taxi.position)');
	
	function afterFind($results) {
		foreach ($results as $key => $val):
		if (isset($val['Taxi']['position_as_text'])){
			$postgisPos = $val['Taxi']['position_as_text'];
			$csvLatLng = $this->convertToCsv($postgisPos);
			unset($val['Taxi']['position_as_text']);
			$results[$key]['Taxi']['csv_latlng'] = $csvLatLng;
		}
		endforeach;
		return $results;
	}
	
	//var $hasMany = 'Request';
}
?>