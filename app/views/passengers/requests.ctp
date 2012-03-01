<!-- File: /app/views/passengers/requests.ctp -->

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

static $codetoStatusStr = array(0 => 'Open (Waiting for taxi response)', 1 => 'Accepted (Waiting taxi to arrive)', 2 => 'Rejected', 3 => 'Cancelled', 4 => 'Active (Boarded Taxi)', 5 => 'Closed');

//Passenger attributes
$passengerId = $thisPassenger['Passenger']['id'];
$passengerName = $thisPassenger['Passenger']['name'];

?>

<h2 align="center">Requests from <?php echo $passengerName?></h2>

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
 * Generate links for each of the passenger possible actions: cancel, enter or leave taxi and write review
 * @param unknown_type $obj the document object
 * @param unknown_type $reqStatusCode the current status code of the request
 * @param unknown_type $reqId the id of the request
 * @param unknown_type $passengerBoarded if the passenger is currently boarded
 * @param unknown_type $review
 */
function generateActions($obj, $reqStatusCode, $reqId, $passengerBoarded, $review){
	switch ($reqStatusCode) {
		case RequestStatus::Open:
			return $obj->Html->link("Cancel", array( "controller" => "requests", "action" => "update", "id" => $reqId, "status" => RequestStatus::Cancelled, 'model' => 'passengers'));
			break;
		case RequestStatus::Accepted:
			if(!$passengerBoarded){
				return $obj->Html->link("Enter Taxi", array( "controller" => "requests", "action" => "update",    "id" => $reqId, "passenger_boarded" => "true", 'model' => 'passengers'));
			} else {
				return 'Waiting taxi to arrive.';
			}	
			break;
		case RequestStatus::Active:
			if($passengerBoarded){
				return $obj->Html->link("Leave Taxi", array( "controller" => "requests", "action" => "update",    "id" => $reqId, "passenger_boarded" => "false", 'model' => 'passengers'));
			} else {
				return 'Waiting taxi to stop.';
			}
			break;
		case RequestStatus::Closed:
			return '<a href="#" onClick="writeReview('.$reqId.')">Write Review</a>';
			break;
		default:
			return '';
	}
}


//create requests table and populate it
echo $this->Html->tableHeaders(array('id', 'Taxi','Status','Creation date','Modification date','Start','End','Review','Actions'));

foreach ($requests as $request):
	$reqId = $request['Request']['id'];
	$reqTaxi = $request['Taxi']['name'];
	$reqStatusCode = $request['Request']['status'];
	$reqStatus = $codetoStatusStr[$reqStatusCode];
	$reqCreationDate = $request['Request']['created'];
	$reqModDate = $request['Request']['modified'];
	$reqStart = $request['Request']['addr_start'];
	$reqEnd = $request['Request']['addr_end'];
	$reqReview = $request['Request']['review'];
	$passengerBoarded = $request['Request']['passenger_boarded'];
	$actions = generateActions($this, $reqStatusCode, $reqId, $passengerBoarded, $reqReview);
	echo $this->Html->tableCells(array($reqId, $reqTaxi, $reqStatus, $reqCreationDate, $reqModDate, $reqStart, $reqEnd, $reqReview, $actions));
endforeach; 

?>

</tr>

</table>

<br/>

<?php echo $this->Html->link("Reload requests", array( "action" => "requests",    "id" => $passengerId)); ?>

<br/>
<br/>

<?php echo $this->Html->link("Return", array('action' => 'index')); ?>

<br/>
<br/>

</div>

<!--  PHP forms -->

<?php 
//Adds a form for the write review operation
echo $this->Form->create('Request', array('action' => 'writeReview', 'inputDefaults' => array( 'label' => false, 'div' => false)));
echo $this->Form->hidden('id');
echo $this->Form->hidden('review');
echo $this->Form->end();
?>

<!-- JavaScript -->

<script>
/** Writes and submit review **/
function writeReview(reqId){
	var review = <?php echo $this->Js->prompt("Please write review:", '')?>;
	if (review != null){
		var writeReviewForm = document.forms['RequestWriteReviewForm'];
 		writeReviewForm.elements['RequestId'].value = reqId;
 		writeReviewForm.elements['RequestReview'].value = review;
 		writeReviewForm.submit();
 	}
}
</script>

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