<style>
.squaredFour {
	vertical-align: middle;
	display:inline-block;
}
.tableNoBorder td {
	padding: 12px 12px 0px;
	text-align: left;
}

</style>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $fair->get('name'); ?> - <?php echo $headline; ?></h1>
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
					id="bookingCreated0"
					value="0"
					<?php echo (is_array($mailSettings->bookingCreated) && in_array("0", $mailSettings->bookingCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
			<label class="squaredFour" for="bookingCreated0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="bookingCreated[]"
					id="bookingCreated1"
					value="1"
					<?php echo (is_array($mailSettings->bookingCreated) && in_array("1", $mailSettings->bookingCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingCreated1" />
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
					id="bookingEdited0"
					value="0"
					<?php echo (is_array($mailSettings->bookingEdited) && in_array("0", $mailSettings->bookingEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingEdited0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="bookingEdited[]"
					id="bookingEdited1"
					value="1"
					<?php echo (is_array($mailSettings->bookingEdited) && in_array("1", $mailSettings->bookingEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingEdited1" />
			</td>
		</tr>
		<!--		Ändra en bokning slut				-->
		
		<!--		Avbryt en bokning eller reservation start				-->
		<tr>
			<td><?php echo $bookingCancelled; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input 
					type="checkbox"
					name="bookingCancelled[]"
					id="bookingCancelled0"
					value="0"
					<?php echo (is_array($mailSettings->bookingCancelled) && in_array("0", $mailSettings->bookingCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingCancelled0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
				type="checkbox"
				name="bookingCancelled[]"
				id="bookingCancelled1"
				value="1"
				<?php echo (is_array($mailSettings->bookingCancelled) && in_array("1", $mailSettings->bookingCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingCancelled1" />
			</td>
			<td>
				<?php echo $toCurrentUser; ?>
				<input 
					type="checkbox"
					name="bookingCancelled[]"
					id="bookingCancelled2"
					value="2"
					<?php echo (is_array($mailSettings->bookingCancelled) && in_array("2", $mailSettings->bookingCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingCancelled2" />
			</td>			
		</tr>
		<!--		Avbryt en bokning slut				-->		
		<!--		Nedgradera en bokning start				-->
		<tr>
			<td><?php echo $bookingToReservation; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input 
					type="checkbox"
					name="bookingToReservation[]"
					id="bookingToReservation0"
					value="0"
					<?php echo (is_array($mailSettings->bookingToReservation) && in_array("0", $mailSettings->bookingToReservation)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingToReservation0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
				type="checkbox"
				name="bookingToReservation[]"
				id="bookingToReservation1"
				value="1"
				<?php echo (is_array($mailSettings->bookingToReservation) && in_array("1", $mailSettings->bookingToReservation)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="bookingToReservation1" />
			</td>	
		</tr>
		<!--		Nedgradera en bokning slut				-->
		<!--		Uppgradera en reservation start				-->
		<tr>
			<td><?php echo $reservationToBooking; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input 
					type="checkbox"
					name="reservationToBooking[]"
					id="reservationToBooking0"
					value="0"
					<?php echo (is_array($mailSettings->reservationToBooking) && in_array("0", $mailSettings->reservationToBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationToBooking0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
				type="checkbox"
				name="reservationToBooking[]"
				id="reservationToBooking1"
				value="1"
				<?php echo (is_array($mailSettings->reservationToBooking) && in_array("1", $mailSettings->reservationToBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationToBooking1" />
			</td>	
		</tr>
		<!--		Uppgradera en reservation slut				-->
		<!--		Ta emot en preliminärbokning start			-->
		<tr>
			<td><?php echo $recievePreliminaryBooking; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="recievePreliminaryBooking[]"
					id="recievePreliminaryBooking0"
					value="0"
					<?php echo (is_array($mailSettings->recievePreliminaryBooking) && in_array("0", $mailSettings->recievePreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="recievePreliminaryBooking0" />
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
					id="acceptPreliminaryBooking0"
					value="0"
					<?php echo (is_array($mailSettings->acceptPreliminaryBooking) && in_array("0", $mailSettings->acceptPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="acceptPreliminaryBooking0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="acceptPreliminaryBooking[]"
					id="acceptPreliminaryBooking1"
					value="1"
					<?php echo (is_array($mailSettings->acceptPreliminaryBooking) && in_array("1", $mailSettings->acceptPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="acceptPreliminaryBooking1" />
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
					id="cancelPreliminaryBooking0"
					value="0"
					<?php echo (is_array($mailSettings->cancelPreliminaryBooking) && in_array("0", $mailSettings->cancelPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="cancelPreliminaryBooking0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="cancelPreliminaryBooking[]"
					id="cancelPreliminaryBooking1"
					value="1"
					<?php echo (is_array($mailSettings->cancelPreliminaryBooking) && in_array("1", $mailSettings->cancelPreliminaryBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="cancelPreliminaryBooking1" />
			</td>
		</tr>
		<!--		Avbryt en preliminärbokning slut			-->
		<!--		Tar emot en ansökan om plats (kölista/anmälan till ett dolt event) start		-->	
		<tr>
			<td><?php echo $recieveRegistration; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="recieveRegistration[]"
					id="recieveRegistration0"
					value="0"
					<?php echo (is_array($mailSettings->recieveRegistration) && in_array("0", $mailSettings->recieveRegistration)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="recieveRegistration0" />
			</td>
		</tr>
		<!--		Tar emot en ansökan om plats (kölista/anmälan till ett dolt event) slut			-->

		<!--		Avbryt en anmälan start				-->
		<tr>
			<td><?php echo $registrationCancelled; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input 
					type="checkbox"
					name="registrationCancelled[]"
					id="registrationCancelled0"
					value="0"
					<?php echo (is_array($mailSettings->registrationCancelled) && in_array("0", $mailSettings->registrationCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="registrationCancelled0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
				type="checkbox"
				name="registrationCancelled[]"
				id="registrationCancelled1"
				value="1"
				<?php echo (is_array($mailSettings->registrationCancelled) && in_array("1", $mailSettings->registrationCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="registrationCancelled1" />
			</td>
			<td>
				<?php echo $toCurrentUser; ?>
				<input 
					type="checkbox"
					name="registrationCancelled[]"
					id="registrationCancelled2"
					value="2"
					<?php echo (is_array($mailSettings->registrationCancelled) && in_array("2", $mailSettings->registrationCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="registrationCancelled2" />
			</td>
		</tr>
		<!--		Avbryt en anmälan slut				-->

		<!--		Påminnelser för utgående reservationer start				-->
		<tr>
			<td><?php echo $reservationReminders; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input 
					type="checkbox"
					name="reservationReminders[]"
					id="reservationReminders0"
					value="0"
					<?php echo (is_array($mailSettings->reservationReminders) && in_array("0", $mailSettings->reservationReminders)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationReminders0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
				type="checkbox"
				name="reservationReminders[]"
				id="reservationReminders1"
				value="1"
				<?php echo (is_array($mailSettings->reservationReminders) && in_array("1", $mailSettings->reservationReminders)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationReminders1" />
			</td>
		</tr>
		<!--		Påminnelser för utgående reservationer slut				-->
		
	</table>

	<input type="submit" name="save" class="greenbutton bigbutton" value="<?php echo $save; ?>" />
</form>