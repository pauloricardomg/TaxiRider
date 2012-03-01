<?php
class Request extends AppModel {

	var $name = "Request";
	
	//Virtual field to retrieve points in postgresql
	var $virtualFields = array('start_pos_as_text' => 'AsText(Request.start_position)',
							   'end_pos_as_text' => 'AsText(Request.end_position)');
	
	//So it will return the taxi entry when retrieving the request entry
	var $belongsTo = array('Taxi', 'Passenger');
	
	/**
	 * Converts begin and end positions from postgis representations to CSV
	 * @param unknown_type $results the returned results on find
	 */
	function afterFind($results) {
		foreach ($results as $key => $val):
			if (isset($val['Request']['start_pos_as_text'])){
				$postgisPos = $val['Request']['start_pos_as_text'];
				$csvStart = $this->convertToCsv($postgisPos);
				unset($results[$key]['Request']['start_pos_as_text']);
				$results[$key]['Request']['csv_start'] = $csvStart;
			}
		
			if (isset($val['Request']['end_pos_as_text'])){
				$postgisPos = $val['Request']['end_pos_as_text'];
				$csvEnd = $this->convertToCsv($postgisPos);
				unset($results[$key]['Request']['end_pos_as_text']);
				$results[$key]['Request']['csv_end'] = $csvEnd;
			}
		endforeach;
		return $results;
	}
}
?>
