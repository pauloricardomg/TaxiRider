<?php
class Passenger extends AppModel {
	
	var $name = "Passenger";
	
	//Virtual field to retrieve points in postgresql
	var $virtualFields = array(    'position' => 'AsText(Passenger.position)');
	
	//var $hasMany = 'Request';
}
?>