<!-- File: /app/views/taxis/requests.ctp -->

<!-- HTML + PHP -->

<?php 
// possible statuses: open=0, accepted=1, rejected=2, cancelled=3, active=4, closed=5

class RequestStatus
{
	const Open = 0;
	const Accepted = 1;
	const Rejected = 2;
	const Cancelled = 3;
	const Active = 4;
	const Closed = 5;
	// etc.	
}

static $codetoStatusStr = array(0 => 'Open (Please respond request)', 1 => 'Accepted (Move to passenger location)', 2 => 'Rejected', 3 => 'Cancelled', 4 => 'Active (Passenger on board)', 5 => 'Closed');

//Taxi attributes
$taxiId = $thisTaxi['Taxi']['id'];
$taxiName = $thisTaxi['Taxi']['name'];

?>

<h2 align="center">Requests from <?php echo $taxiName?></h2>

<div style="width: 100%; text-align: center">

<table class="sample">

<tr>
<td id="status_bar" style="color: red; font-weight: bold; text-align: center" colspan="10">
<?php 
$flashmsg = $this->Session->flash();
echo (empty($flashmsg)) ? 'In order to see status updates, please reload page.' : $flashmsg; 
?>
&nbsp
</td>
</tr>

<tr>

<?php 

/**
 * 
 * Generate links for each of the taxi possible actions: cancel, enter or leave taxi and write review
 * @param unknown_type $obj the document object
 * @param unknown_type $reqStatusCode the current status code of the request
 * @param unknown_type $reqId the id of the request
 * @param unknown_type $passengerPicked if the passenger was already picked
 * @param unknown_type $review
 */
function generateActions($obj, $reqStatusCode, $reqId, $passengerPicked, $review){
	switch ($reqStatusCode) {
		case RequestStatus::Open:
			$strToReturn = $obj->Html->link("Accept", array( "controller" => "requests", "action" => "update", "id" => $reqId, "status" => RequestStatus::Accepted, 'model' => 'taxis')) . " | ";
			$strToReturn .= $obj->Html->link("Reject", array( "controller" => "requests", "action" => "update", "id" => $reqId, "status" => RequestStatus::Rejected, 'model' => 'taxis'));
			return $strToReturn;
			break;
		case RequestStatus::Accepted:
			if(!$passengerPicked){
				return $obj->Html->link("Pick up passenger", array( "controller" => "requests", "action" => "update",    "id" => $reqId, "passenger_picked" => "true", 'model' => 'taxis'));
			} else {
				return 'Waiting for passenger to enter taxi.';
			}	
			break;
		case RequestStatus::Active:
			if($passengerPicked){
				return $obj->Html->link("Drop passenger", array( "controller" => "requests", "action" => "update",    "id" => $reqId, "passenger_picked" => "false", 'model' => 'taxis'));
			} else {
				return 'Waiting for passenger to leave taxi.';
			}
			break;
		default:
			return '';
	}
}


//create requests table and populate it
echo $this->Html->tableHeaders(array('id', 'Passenger','Status','Creation date','Modification date','Start','End','Review','Actions'));

foreach ($requests as $request):
	$reqId = $request['Request']['id'];
	$reqTaxi = $request['Passenger']['name'];
	$reqStatusCode = $request['Request']['status'];
	$reqStatus = $codetoStatusStr[$reqStatusCode];
	$reqCreationDate = $request['Request']['created'];
	$reqModDate = $request['Request']['modified'];
	$reqStart = $request['Request']['addr_start'];
	$reqEnd = $request['Request']['addr_end'];
	$reqReview = $request['Request']['review'];
	$passengerPicked = $request['Request']['passenger_picked'];
	$actions = generateActions($this, $reqStatusCode, $reqId, $passengerPicked, $reqReview);
	echo $this->Html->tableCells(array($reqId, $reqTaxi, $reqStatus, $reqCreationDate, $reqModDate, $reqStart, $reqEnd, $reqReview, $actions));
endforeach; 

?>

</tr>

</table>

<br/>

<?php echo $this->Html->link("Reload requests", array( "action" => "requests",    "id" => $taxiId)); ?>

<br/>
<br/>

<?php echo $this->Html->link("Return", array('action' => 'index')); ?>

<br/>
<br/>

</div>

<!-- CSS -->

<style type="text/css">
table.sample {
	border-width: 1px;
	border-spacing: 2px;
	border-style: solid;
	border-color: black;
	border-collapse: collapse;
	background-color: white;
	empty-cells: show;
	font-size: large;
	margin-left: auto;
	margin-right: auto;
}
table.sample th {
	border-width: 1px;
	padding: 1px;
	border-style: dashed;
	border-color: black;
	background-color: white;
	-moz-border-radius: ;
}
table.sample td {
	border-width: 1px;
	padding: 1px;
	border-style: dashed;
	border-color: black;
	background-color: white;
	-moz-border-radius: ;
}
</style>