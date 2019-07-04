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
		<!--		Create a booking start				-->
		<tr>
			<td><?php echo $BookingCreated; ?></td>
			<td><?php echo $ToExhibitor; ?>
				<input
					type="checkbox"
					name="BookingCreated[]"
					id="BookingCreated1"
					value="1"
					<?php echo (is_array($mailSettings->BookingCreated) && in_array("1", $mailSettings->BookingCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="BookingCreated1" />
			</td>
		</tr>
		<!--		Create a booking end				-->
		
		<!--		Cancel a booking start				-->
		<tr>
			<td><?php echo $BookingCancelled; ?></td>
			<td><?php echo $ToExhibitor; ?>
				<input
				type="checkbox"
				name="BookingCancelled[]"
				id="BookingCancelled1"
				value="1"
				<?php echo (is_array($mailSettings->BookingCancelled) && in_array("1", $mailSettings->BookingCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="BookingCancelled1" />
			</td>
		</tr>
		<!--		Cancel a booking end				-->

		<!--		Recieve a preliminary booking start		-->
		<tr>
			<td><?php echo $PreliminaryCreated; ?></td>
			<td><?php echo $ToMyself; ?>
				<input
					type="checkbox"
					name="PreliminaryCreated[]"
					id="PreliminaryCreated0"
					value="0"
					<?php echo (is_array($mailSettings->PreliminaryCreated) && in_array("0", $mailSettings->PreliminaryCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="PreliminaryCreated0" />
			</td>
		</tr>		
		<!--		Recieve a preliminary booking end		-->

		<!--		Preliminary to booking start			-->
				<tr>
			<td><?php echo $PreliminaryToBooking; ?></td>
			<td><?php echo $ToMyself; ?>
				<input
					type="checkbox"
					name="PreliminaryToBooking[]"
					id="PreliminaryToBooking0"
					value="0"
					<?php echo (is_array($mailSettings->PreliminaryToBooking) && in_array("0", $mailSettings->PreliminaryToBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="PreliminaryToBooking0" />
			</td>
			<td>
				<?php echo $ToExhibitor; ?>
				<input
					type="checkbox"
					name="PreliminaryToBooking[]"
					id="PreliminaryToBooking1"
					value="1"
					<?php echo (is_array($mailSettings->PreliminaryToBooking) && in_array("1", $mailSettings->PreliminaryToBooking)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="PreliminaryToBooking1" />
			</td>
		</tr>		
		<!--		Preliminary to booking end 				-->
		
		<!--		Preliminary to reservation start		-->
		<tr>
			<td><?php echo $PreliminaryToReservation; ?></td>
			<td><?php echo $ToMyself; ?>
				<input
					type="checkbox"
					name="PreliminaryToReservation[]"
					id="PreliminaryToReservation0"
					value="0"
					<?php echo (is_array($mailSettings->PreliminaryToReservation) && in_array("0", $mailSettings->PreliminaryToReservation)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="PreliminaryToReservation0" />
			</td>
			<td>
				<?php echo $ToExhibitor; ?>
				<input
					type="checkbox"
					name="PreliminaryToReservation[]"
					id="PreliminaryToReservation1"
					value="1"
					<?php echo (is_array($mailSettings->PreliminaryToReservation) && in_array("1", $mailSettings->PreliminaryToReservation)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="PreliminaryToReservation1" />
			</td>
		</tr>		
		<!--		Preliminary to reservation end			-->

		<!--		Cancel a preliminary start				-->
		<tr>
			<td><?php echo $PreliminaryCancelled; ?></td>
			<td><?php echo $ToExhibitor; ?>
				<input
					type="checkbox"
					name="PreliminaryCancelled[]"
					id="PreliminaryCancelled1"
					value="1"
					<?php echo (is_array($mailSettings->PreliminaryCancelled) && in_array("1", $mailSettings->PreliminaryCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="PreliminaryCancelled1" />
			</td>
		</tr>
		<!--		Cancel a preliminary end				-->
		<!--		Recieve application start				-->	
		<tr>
			<td><?php echo $RegistrationCreated; ?></td>
			<td><?php echo $ToMyself; ?>
				<input
					type="checkbox"
					name="RegistrationCreated[]"
					id="RegistrationCreated0"
					value="0"
					<?php echo (is_array($mailSettings->RegistrationCreated) && in_array("0", $mailSettings->RegistrationCreated)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="RegistrationCreated0" />
			</td>
		</tr>
		<!--		Recieve application end					-->	

		<!--		Cancel application start				-->	
		<tr>
			<td><?php echo $RegistrationCancelled; ?></td>
			<td><?php echo $ToExhibitor; ?>
				<input
				type="checkbox"
				name="RegistrationCancelled[]"
				id="RegistrationCancelled1"
				value="1"
				<?php echo (is_array($mailSettings->RegistrationCancelled) && in_array("1", $mailSettings->RegistrationCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="RegistrationCancelled1" />
			</td>
		</tr>
		<!--		Cancel application end					-->
	</table>

	<input type="submit" name="save" class="greenbutton bigbutton" value="<?php echo $save; ?>" />
</form>