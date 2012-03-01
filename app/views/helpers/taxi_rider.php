<?php

class TaxiRiderHelper extends Helper {

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