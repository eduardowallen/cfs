<?php
  global $translator;
?>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $fairname; ?> - <?php echo $invoice_headline; ?></h1>
<br />
<form action="fair/invoiceSettings/<?php echo $fair_id; ?>" method="post" enctype="multipart/form-data">

	<div class="form_column" id="form_column1">
		<h3><?php echo htmlspecialchars($translator->{'Customer contact'}); ?></h3>
		<label for="reference"><?php echo $reference_label; ?></label>
		<input type="text" name="reference" id="reference" value="<?php echo $reference; ?>" />

		<label for="company_name"><?php echo $company_name_label; ?></label>
		<input type="text" name="company_name" id="company_name" value="<?php echo $company_name; ?>" />

		<label for="address"><?php echo $address_label; ?></label>
		<input type="text" name="address" id="address" value="<?php echo $address; ?>" />

		<label for="zipcode"><?php echo $zipcode_label; ?></label>
		<input type="text" name="zipcode" id="zipcode" value="<?php echo $zipcode; ?>" />

		<label for="city"><?php echo $city_label; ?></label>
		<input type="text" name="city" id="city" value="<?php echo $city; ?>" />

		<label for="country"><?php echo $country_label; ?></label>
		<input type="text" name="country" id="country" value="<?php echo $country; ?>" />

		<label for="phone"><?php echo $phone_label; ?></label>
		<input type="text" name="phone" id="phone" value="<?php echo $phone; ?>" />		

		<label for="invoice_email"><?php echo $email_label; ?></label>
		<input type="text" name="invoice_email" id="invoice_email" value="<?php echo $invoice_email; ?>" />

		<label for="website"><?php echo $website_label; ?></label>
		<input type="text" name="website" id="website" value="<?php echo $website; ?>" />
	</div>

	<div class="form_column" id="form_column2">
		<h3><?php echo htmlspecialchars($translator->{'Bank & Organization'}); ?></h3>
		<label for="orgnr"><?php echo $orgnr_label; ?></label>
		<input type="text" name="orgnr" id="orgnr" value="<?php echo $orgnr; ?>" />

		<label for="bank_no"><?php echo $bank_no_label; ?></label>
		<input type="text" name="bank_no" id="bank_no" value="<?php echo $bank_no; ?>" />

		<label for="postgiro"><?php echo $postgiro_label; ?></label>
		<input type="text" name="postgiro" id="postgiro" value="<?php echo $postgiro; ?>" />

		<label for="vat_no"><?php echo $vat_no_label; ?></label>
		<input type="text" name="vat_no" id="vat_no" value="<?php echo $vat_no; ?>" />

		<label for="iban_no"><?php echo $iban_no_label; ?></label>
		<input type="text" name="iban_no" id="iban_no" value="<?php echo $iban_no; ?>" />

		<label for="swift_no"><?php echo $swift_no_label; ?></label>
		<input type="text" name="swift_no" id="swift_no" value="<?php echo $swift_no; ?>" />

		<label for="swish_no"><?php echo $swish_no_label; ?></label>
		<input type="text" name="swish_no" id="swish_no" value="<?php echo $swish_no; ?>" />
	</div>

	<div class="form_column" id="form_column3">
		<h3><?php echo htmlspecialchars($translator->{'Other invoice settings'}); ?></h3>
		<label for="invoice_id_start"><?php echo $invoice_id_start_label; ?></label>
		<input type="number" name="invoice_id_start" id="invoice_id_start" min="1" value="<?php echo $invoice_id_start; ?>" />
		<label for="credit_invoice_id_start"><?php echo $credit_invoice_id_start_label; ?></label>
		<input type="number" name="credit_invoice_id_start" id="credit_invoice_id_start" min="1" value="<?php echo $credit_invoice_id_start; ?>" />
	<br />
		<label for="pos_vat"><?php echo $pos_vat_label; ?></label>
			<select name="pos_vat" id="pos_vat">
				<option value="0"<?php if ($pos_vat == 0) echo ' selected="selected"'; ?>><?php echo $no_vat_label; ?></option>
				<option value="18"<?php if ($pos_vat == 18) echo ' selected="selected"'; ?>>18%</option>
				<option value="25"<?php if ($pos_vat == 25) echo ' selected="selected"'; ?>>25%</option>
			</select>
	<br />
		<label for="default_expirationdate"><?php echo $default_expirationdate_label; ?> (DD-MM-YYYY) *</label>
		<input class="datepicker date" type="text" name="default_expirationdate" id="default_expirationdate" value="<?php echo $default_expirationdate; ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date that will automatically be used when creating invoices for exhibitors on this event.'}); ?>" />
	<br />
		<input type="submit" name="save" value="<?php echo $save_label; ?>" class="greenbutton bigbutton" />
</div>


</form>