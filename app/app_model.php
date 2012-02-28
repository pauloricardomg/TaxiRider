<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.app
 */
class AppModel extends Model {
	
	/**
	* Convert a PostGis text point in format "POINT(lng lat)" to "lat, lng" string
	* @param string $postgisPoint original point returned by postGis
	* @return string the parsed point
	*/
	function convertToCsv($postgisPoint){
		preg_match("/POINT\((-?[0-9]*\.[0-9]*) (-?[0-9]*\.[0-9]*)\)/", $postgisPoint, $result);
		unset($result[0]); //remove first element from array
		//reverse array, because postgis works with lng-lat, while gmaps with lat-lng
		return implode(",", array_reverse($result));
	}
	
	/**
	 * Convert a latitude and longitude to a PostGis point
	 * @param string $postgisPoint original point returned by postGis
	 * @return string the parsed point
	 */
	function convertToPostGisPoint($latlng){
		//Remove leading parentheses
		$latlng = str_replace(array('(',')'), '', $latlng);
	
		//Explode and trim latitude and longitude
		list ($lat, $lng) = array_map('trim',explode(",",$latlng));
	
		$expr = "ST_GeomFromText('POINT(".$lng." ".$lat.")', 4326)";
		$db = $this->getDataSource();
		return $db->expression($expr);
	}
	
}