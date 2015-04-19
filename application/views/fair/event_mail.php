<h2><?php echo $heading; ?></h2>
<form action="fair/event_mail/<?php echo $id; ?>" method="POST">
	<table class="tableNoBorder">
		<!--		Skapa en bokning start				-->
				<tr>
			<td><?php echo $bookingCreated; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="bookingCreated[]"
					value="0"
					<?php echo (is_array($mailSettings->bookingCreated) && in_array("0", $mailSettings->bookingCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="bookingCreated[]"
					value="1"
					<?php echo (is_array($mailSettings->bookingCreated) && in_array("1", $mailSettings->bookingCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<!--		Skapa en bokning slut				-->
		
		<!--		Ändra en bokning start				-->
		<tr>
			<td><?php echo $bookingEdited; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="bookingEdited[]"
					value="0"
					<?php echo (is_array($mailSettings->bookingEdited) && in_array("0", $mailSettings->bookingEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="bookingEdited[]"
					value="1"
					<?php echo (is_array($mailSettings->bookingEdited) && in_array("1", $mailSettings->bookingEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<!--		Ändra en bokning slut				-->
		
		<!--		Avbryt en bokning start				-->
		<tr>
			<td><?php echo $bookingCancelled; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input 
					type="checkbox"
					name="bookingCancelled[]"
					value="0"
					<?php echo (is_array($mailSettings->bookingCancelled) && in_array("0", $mailSettings->bookingCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
				type="checkbox"
				name="bookingCancelled[]"
				value="1"
				<?php echo (is_array($mailSettings->bookingCancelled) && in_array("1", $mailSettings->bookingCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toCurrentUser; ?>
				<input 
					type="checkbox"
					name="bookingCancelled[]"
					value="2"
					<?php echo (is_array($mailSettings->bookingCancelled) && in_array("2", $mailSettings->bookingCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>			
		</tr>
		<!--		Avbryt en bokning slut				-->		
		<!--		Skapa en reservation start			-->
				<tr>
			<td><?php echo $reservationCreated; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="reservationCreated[]"
					value="0"
					<?php echo (is_array($mailSettings->reservationCreated) && in_array("0", $mailSettings->reservationCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="reservationCreated[]"
					value="1"
					<?php echo (is_array($mailSettings->reservationCreated) && in_array("1", $mailSettings->reservationCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<!--		Skapa en reservation slut			-->
		<!--		Ändra en reservation start			-->		
		<tr>
			<td><?php echo $reservationEdited; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="reservationEdited[]"
					value="0"
					<?php echo (is_array($mailSettings->reservationEdited) && in_array("0", $mailSettings->reservationEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td><?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="reservationEdited[]"
					value="1"
					<?php echo (is_array($mailSettings->reservationEdited) && in_array("1", $mailSettings->reservationEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<!--		Ändra en reservation slut			-->
		<!--		Avbryt en reservation start					
		<tr>
			<td><?php echo $reservationCancelled; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="reservationCancelled[]"
					value="0"
					<?php echo (is_array($mailSettings->reservationCancelled) && in_array("0", $mailSettings->reservationCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="reservationCancelled[]"
					value="1"
					<?php echo (is_array($mailSettings->reservationCancelled) && in_array("1", $mailSettings->reservationCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
				Avbryt en reservation slut			-->		
		<!--		Ta emot en preliminärbokning start			-->				
		<tr>
			<td><?php echo $recievePreliminaryBooking; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="recievePreliminaryBooking[]"
					value="0"
					<?php echo (is_array($mailSettings->recievePreliminaryBooking) && in_array("0", $mailSettings->recievePreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>		
		<!--		Ta emot en preliminärbokning slut			-->				
		<!--		Godkänner en preliminärbokning start			-->				
		<tr>
			<td><?php echo $acceptPreliminaryBooking; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="acceptPreliminaryBooking[]"
					value="0"
					<?php echo (is_array($mailSettings->acceptPreliminaryBooking) && in_array("0", $mailSettings->acceptPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="acceptPreliminaryBooking[]"
					value="1"
					<?php echo (is_array($mailSettings->acceptPreliminaryBooking) && in_array("1", $mailSettings->acceptPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>		
		<!--		Godkänner en preliminärbokning slut			-->								
		<!--		Avbryt en preliminärbokning start			-->						
		<tr>
			<td><?php echo $cancelPreliminaryBooking; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="cancelPreliminaryBooking[]"
					value="0"
					<?php echo (is_array($mailSettings->cancelPreliminaryBooking) && in_array("0", $mailSettings->cancelPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="cancelPreliminaryBooking[]"
					value="1"
					<?php echo (is_array($mailSettings->cancelPreliminaryBooking) && in_array("1", $mailSettings->cancelPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<!--		Avbryt en preliminärbokning slut			-->				
	</table>

	<input type="submit" name="save" class="save-btn" value="<?php echo $save; ?>" />
</form>