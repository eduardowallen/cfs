<h2><?php echo $heading; ?></h2>
<form action="fair/event_mail/<?php echo $id; ?>" method="POST">
	<table class="tableNoBorder">
		<tr>
			<td><?php echo $editBooking; ?></td>
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
		<tr>
			<td><?php echo $editReservation; ?></td>
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
		<tr>
			<td><?php echo $cancelBooking; ?></td>
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
		</tr>
		<tr>
			<td><?php echo $cancelPreliminaryBooking; ?></td>
			<td><?php echo $toMyself; ?>
				<input
					type="checkbox"
					name="preliminaryCancelled[]"
					value="0"
					<?php echo (is_array($mailSettings->preliminaryCancelled) && in_array("0", $mailSettings->preliminaryCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
			<td>
				<?php echo $toExhibitor; ?>
				<input
					type="checkbox"
					name="preliminaryCancelled[]"
					value="1"
					<?php echo (is_array($mailSettings->preliminaryCancelled) && in_array("1", $mailSettings->preliminaryCancelled)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
		<tr>
			<td><?php echo $cancelReservation; ?></td>
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
	</table>

	<input type="submit" name="save" class="save-btn" value="<?php echo $save; ?>" />
</form>