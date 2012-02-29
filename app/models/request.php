<?php
class Request extends AppModel {

	var $name = "Request";
	
	//Virtual field to retrieve points in postgresql
	var $virtualFields = array('start_pos_as_text' => 'AsText(Request.start_position)',
							   'end_pos_as_text' => 'AsText(Request.end_position)');
	
	function afterFind($results) {
		foreach ($results as $key => $val):
			if (isset($val['Request']['start_pos_as_text'])){
				$postgisPos = $val['Request']['start_pos_as_text'];
				$csvStart = $this->convertToCsv($postgisPos);
				unset($val['Request']['start_pos_as_text']);
				$results[$key]['Taxi']['csv_start'] = $csvStart;
			}
		
			if (isset($val['Request']['end_pos_as_text'])){
				$postgisPos = $val['Request']['end_pos_as_text'];
				$csvEnd = $this->convertToCsv($postgisPos);
				unset($val['Request']['end_pos_as_text']);
				$results[$key]['Taxi']['csv_end'] = $csvEnd;
			}
		endforeach;
		return $results;
	}
	
	//var $belongsTo = array('Requester' => 'User', 'Requested' => 'Taxi');

}
?>
