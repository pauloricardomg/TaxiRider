<?php

class TaxiRiderHelper extends Helper {

	/**
	 *
	 * Gets a google map marker click listener that will submit the
	 * taxi delete request to the server upon click on the marker
	 *
	 * Constraints:
	 * 
	 * - This listener needs an external "toDelete" boolean variable,
	 * which indicates if the listener should be active or not. In
	 * case the listener is not active, the ordinary infowindow will
	 * be shown.
	 *
	 * - Requires JS helper being specified in the controller
	 * 
	 * - Assumes marker id is the id of the object to be removed in the DB
	 *
	 * @param unknown_type $obj the document object
	 * @param string $confirmMsg the delete confirmation message to be shown on a popup
	 * @param unknown_type $modelName the name of the model being deleted
	 * @param string $markerId the identifier of the marker to which this listener will
	 * 						be associated
	 * @return the javascript listener for the action
	 */
	function getTaxiClickJSListener($obj, $confirmMsg, $modelName, $markerId){
		$delForm = $modelName."DeleteForm";
		$idElement = $modelName."Id";
		return "function(event) {
		 		if(toDelete){
		 			var confirmDel = ".$obj->Js->confirm($confirmMsg).";
		 			if (confirmDel){
		 						var delForm = document.forms['".$delForm."'];
		 						delForm.elements['".$idElement."'].value = ".$markerId.";
		 			 			delForm.submit();
		 			} else{
		 				document.getElementById('status_bar').innerHTML ='&nbsp';
		 				toDelete = false;
		 			}
		 		} else {			
		 			infowindow".$markerId.".open(map,marker".$markerId.");
		 		}
			}";
	}
	
	/**
	 * Gets a google map click listener that will submit the
	 * add request to the server upon click on the map
	 *
	 * Constraints:
	 *
	 * - This listener needs an external "toAdd" boolean variable,
	 * which indicates if the listener should be active or not.
	 *
	 * - Also equires JS helper being specified in the controller ($obj)
	 * 
	 * @param unknown_type $obj the document object
	 * @param unknown_type $confirmMsg a message requesting the name of the entity to add
	 * @param unknown_type $modelName the name of the model being added
	 * @return string the javascript listener for the add action
	 */
	function getAddJSListener($obj, $confirmMsg, $modelName){
		$addForm = $modelName."AddForm";
		$nameElement = $modelName."Name";
		$latLngElement = $modelName."Latlng";
		
		return "function(event) {
		 		if(toAdd){
		 			var passengerName = ".$obj->Js->prompt($confirmMsg, '').";
		 			if (passengerName != null){
		 						var addForm = document.forms['".$addForm."'];
		 						addForm.elements['".$nameElement."'].value = passengerName;
		 			 			addForm.elements['".$latLngElement."'].value = event.latLng.toString();
		 			 			//document.getElementById('status_bar').innerHTML = 'name: ' + passengerName + '. lat: ' + event.latLng.lat() + '. lon: ' + event.latLng.lng(); //debug
		 			 			addForm.submit();
		 			} else {
		 				document.getElementById('status_bar').innerHTML ='&nbsp';
		 				toAdd = false;
		 			}
		 		}
			}";
	}

	/**
	 * Gets a google map marker listener that will record the
	 * initial position of the marker before being moved
	 *
	 * @param unknown_type $obj the document object
	 * @return string the javascript listener for the action
	 */
	function getChangePosStartJSListener($obj){
		return "function(event) {
						initialLatLng = event.latLng;
			}";
	}

	/**
	 * Gets a google map marker listener that will change
	 * the position of a passenger, or roll back to the
	 * previous position if the user cancels the operation
	 *
	 * @param unknown_type $obj the document object
	 * @param string $confirmMsg the change position confirmation message to be shown on a popup
	 * @param string $markerId the identifier of the marker to which this listener will
	 * 						be associated
	 *
	 * @return string the javascript listener for the action
	 */
	function getChangePosEndJSListener($obj, $confirmMsg, $modelName, $markerId){
		$changePos = $modelName."ChangePositionForm";
		$idElement = $modelName."Id";
		$latLngElement = $modelName."Latlng";
		return "function(event) {
			 			var confirmChangePos = ".$obj->Js->confirm($confirmMsg).";
		 				if (confirmChangePos){
		 						var changePosForm = document.forms['".$changePos."'];
		 						changePosForm.elements['".$idElement."'].value = ".$markerId.";
		 			 			changePosForm.elements['".$latLngElement."'].value = event.latLng.toString();
		 			 			changePosForm.submit();	 				
						} else{
		 					document.getElementById('status_bar').innerHTML ='&nbsp';
							//Return initial position
							marker".$markerId.".setPosition(initialLatLng);
													
							//Disable dragging markers
							for(var i=0; i<markers.length; i++) {
								var value = markers[i];
								value.setDraggable(false);
							}
		 				}
			}";
	}

}

?>