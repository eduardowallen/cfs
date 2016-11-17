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
<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>

<form action="fair/modules/<?php echo $id; ?>" method="POST">
	<table class="tableNoBorder">
		<tr>
			<td><?php echo $smsFunction; ?></td>
			<td>
				<input
					type="checkbox"
					name="smsFunction[]"
					id="smsFunction"
					value="1"
					<?php echo (is_array($modules->smsFunction) && in_array("1", $modules->smsFunction)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="smsFunction" />
			</td>
		</tr>
		<tr>
			<td><?php echo $invoiceFunction; ?></td>
			<td>
				<input
					type="checkbox"
					name="invoiceFunction[]"
					id="invoiceFunction"
					value="1"
					<?php echo (is_array($modules->invoiceFunction) && in_array("1", $modules->invoiceFunction)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="invoiceFunction" />
			</td>
		</tr>
		<tr>
			<td><?php echo $raindanceFunction; ?></td>
			<td>
				<input
					type="checkbox"
					name="raindanceFunction[]"
					id="raindanceFunction"
					value="1"
					<?php echo (is_array($modules->raindanceFunction) && in_array("1", $modules->raindanceFunction)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="raindanceFunction" />
			</td>
		</tr>
		<tr>
			<td><?php echo $economyFunction; ?></td>
			<td>
				<input
					type="checkbox"
					name="economyFunction[]"
					id="economyFunction"
					value="1"
					<?php echo (is_array($modules->economyFunction) && in_array("1", $modules->economyFunction)) ? "checked=\"checked\"" : ""; ?>
				/>
				<label class="squaredFour" for="economyFunction" />
			</td>
		</tr>
	</table>
			<input type="submit" name="save" class="greenbutton mediumbutton" value="<?php echo $save; ?>" />
</form>
