<h2><?php echo $heading; ?></h2>
<form action="fair/event_mail/<?php echo $id; ?>" method="POST">
	<table class="tableNoBorder">
<<<<<<< HEAD
		<!--		Skapa en bokning start				-->
				<tr>
			<td><?php echo $bookingCreated; ?></td>
=======
		<tr>
			<td><?php echo $editBooking; ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
<<<<<<< HEAD
					name="bookingCreated[]"
					value="0"
					<?php echo (is_array($mailSettings->bookingCreated) && in_array("0", $mailSettings->bookingCreated)) ? "checked=\"checked\"" : ""; ?>
=======
					name="bookingEdited[]"
					value="0"
					<?php echo (is_array($mailSettings->bookingEdited) && in_array("0", $mailSettings->bookingEdited)) ? "checked=\"checked\"" : ""; ?>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
<<<<<<< HEAD
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
=======
					name="bookingEdited[]"
					value="1"
					<?php echo (is_array($mailSettings->bookingEdited) && in_array("1", $mailSettings->bookingEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<tr>
			<td><?php echo $editReservation; ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
<<<<<<< HEAD
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
=======
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
		<tr>
			<td><?php echo $cancelBooking; ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
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
<<<<<<< HEAD
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
=======
			/>
		</td>
		</tr>
		<tr>
			<td><?php echo $cancelPreliminaryBooking; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="preliminaryCancelled[]"
					value="0"
					<?php echo (is_array($mailSettings->preliminaryCancelled) && in_array("0", $mailSettings->preliminaryCancelled)) ? "checked=\"checked\"" : ""; ?>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
<<<<<<< HEAD
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
=======
					name="preliminaryCancelled[]"
					value="1"
					<?php echo (is_array($mailSettings->preliminaryCancelled) && in_array("1", $mailSettings->preliminaryCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<tr>
			<td><?php echo $cancelReservation; ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
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
<<<<<<< HEAD
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
=======
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
	</table>

	<input type="submit" name="save" class="save-btn" value="<?php echo $save; ?>" />
</form>