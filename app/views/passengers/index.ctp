<!-- File: /app/views/passengers/index.ctp -->
<h2 align="center">Passengers View</h2>
<table>
	<tr>
		<th>Id</th>
		<th>Name</th>
		<th>Position</th>
	</tr>
	
	<!-- Here is where we loop through our $posts array, printing out post info -->
	
	<?php foreach ($passengers as $passenger): ?>
	<tr>
		<td><?php echo $passenger['Passenger']['id']; ?></td>        
	    <td>
	    	<?php echo $passenger['Passenger']['name']; ?>        
	    </td>        
	    <td><?php echo $passenger['Passenger']['position']; ?></td>
	</tr>
	<?php endforeach; ?>	  
	
</table>