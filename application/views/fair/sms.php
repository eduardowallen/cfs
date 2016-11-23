<style>
input[type="checkbox"] {
	opacity: 1;
	position:inherit;
	margin:12px 0px 0px 12px;
	height:auto;
	width:auto;
}
</style>

<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>

<br />
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
			<!--
			<td><?php echo $invoiceFunction; ?></td>
			<td>
				<?php echo $active; ?>
				<input
					type="checkbox"
					name="invoiceFunction[]"
					value="1"
					<?php echo (is_array($invoiceSettings->invoiceFunction) && in_array("1", $invoiceSettings->invoiceFunction)) ? "checked=\"checked\"" : ""; ?>
				/>
			</td>-->
		</tr>
	</table>
			<input type="submit" name="save" class="greenbutton mediumbutton" value="<?php echo $save; ?>" />
</form>
