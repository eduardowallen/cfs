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
		
		<!--		Avbryt en bokning start				-->
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
		<!--		Skapa en reservation start			-->
				<tr>
			<td><?php echo $reservationCreated; ?></td>
			<td>
				<?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="reservationCreated[]"
					id="reservationCreated0"
					value="0"
					<?php echo (is_array($mailSettings->reservationCreated) && in_array("0", $mailSettings->reservationCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationCreated0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="reservationCreated[]"
					id="reservationCreated1"
					value="1"
					<?php echo (is_array($mailSettings->reservationCreated) && in_array("1", $mailSettings->reservationCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationCreated1" />
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
					id="reservationEdited0"
					value="0"
					<?php echo (is_array($mailSettings->reservationEdited) && in_array("0", $mailSettings->reservationEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationEdited0" />
			</td>
			<td><?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="reservationEdited[]"
					id="reservationEdited1"
					value="1"
					<?php echo (is_array($mailSettings->reservationEdited) && in_array("1", $mailSettings->reservationEdited)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="reservationEdited1" />
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
			<td><?php echo $registerForFair; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="registerForFair[]"
					id="registerForFair0"
					value="0"
					<?php echo (is_array($mailSettings->registerForFair) && in_array("0", $mailSettings->registerForFair)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="registerForFair0" />
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="registerForFair[]"
					id="registerForFair1"
					value="1"
					<?php echo (is_array($mailSettings->registerForFair) && in_array("1", $mailSettings->registerForFair)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="registerForFair1" />
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