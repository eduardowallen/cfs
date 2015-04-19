<h1><?php echo $headline; ?></h1>

<form action="fair/sms/<?php echo $id; ?>" method="POST">
	<table class="tableNoBorder">
				<tr>
			<td><?php echo $smsFunction; ?></td>
			<td>
				<?php echo $active; ?>
				<input
					type="checkbox"
					name="smsFunction[]"
					value="1"
					<?php echo (is_array($smsSettings->smsFunction) && in_array("1", $smsSettings->smsFunction)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>
		</tr>
	</table>
			<input type="submit" name="save" class="save-btn" value="<?php echo $save; ?>" />
</form>
