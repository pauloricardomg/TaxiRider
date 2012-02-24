<?php
class Passenger extends AppModel {
	
	var $name = "Passenger";
	
	//Virtual field to retrieve points in postgresql
	var $virtualFields = array('text_pos' => 'AsText(Passenger.position)',
							   'kml_pos' => 'ST_AsKML(Passenger.position)');
	
	/**
	 * Convert a PostGis text point in format "POINT(lat lng)" to "lat, lng" string
	 * @param string $postgisPoint original point returned by postGis
	 * @return string the parsed point
	 */
	function convertToCsv($postgisPoint){
		preg_match("/POINT\((-?[0-9]*\.[0-9]*) (-?[0-9]*\.[0-9]*)\)/", $postgisPoint, $result);
		unset($result[0]); //remove first element from array
		return implode(",", $result);
	}
	
	//var $hasMany = 'Request';
}
?>