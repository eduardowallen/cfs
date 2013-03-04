<h1><?php echo $edit_headline; ?></h1>

<?php if (isset($user_message)) {
	if (isset($error) && $error) {
		echo '<p class="error">'.$user_message.'</p>';
	} else {
		echo '<p>'.$user_message.'</p>';
	}
} ?>

<form action="user/edit/<?php echo $edit_id; ?>/<?php echo $edit_lvl; ?>" method="post" id="edit_user_form">

	<?php if (userLevel() == 100): ?>
	<select name="level">
		<option value="4">System administrator</option>
		<option value="3">Arranger</option>
		<option value="2">Booker</option>
		<option value="1">Exhibitor</option>
	</select>
	<?php endif; ?>
	
	<div class="form_column">
	
	<label for="alias"><?php echo $alias_label; ?> *</label>
	<input type="text" name="alias" id="alias" value="<?php echo $user->get('alias'); ?>"/>
	
	<label for="company"><?php echo $company_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="company" id="company" value="<?php echo $user->get('company'); ?>"/>

	<label for="customer_nr"><?php echo $customer_nr_label; ?></label>
	<input type="text" name="customer_nr" id="customer_nr" value="<?php echo $user->get('customer_nr'); ?>"/>

	<label for="name"><?php echo $contact_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>

	<label for="orgnr"><?php echo $orgnr_label; ?></label>
	<input type="text" name="orgnr" id="orgnr" value="<?php echo $user->get('orgnr'); ?>"/>

	<label for="address"><?php echo $address_label; ?></label>
	<input type="text" name="address" id="address" value="<?php echo $user->get('address'); ?>"/>

	<label for="zipcode"><?php echo $zipcode_label; ?></label>
	<input type="text" name="zipcode" id="zipcode" value="<?php echo $user->get('zipcode'); ?>"/>

	<label for="city"><?php echo $city_label; ?></label>
	<input type="text" name="city" id="city" value="<?php echo $user->get('city'); ?>"/>

	<label for="country"><?php echo $country_label; ?></label>
	<input type="text" name="country" id="country" value="<?php echo $user->get('country'); ?>"/>

	<label for="phone1"><?php echo $phone1_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="phone1" id="phone1" value="<?php echo $user->get('phone1'); ?>"/>

	<label for="phone2"><?php echo $phone2_label; ?></label>
	<input type="text" name="phone2" id="phone2" value="<?php echo $user->get('phone2'); ?>"/>

	<label for="phone3"><?php echo $phone3_label; ?></label>
	<input type="text" name="phone3" id="phone3" value="<?php echo $user->get('phone3'); ?>"/>

	<label for="fax"><?php echo $fax_label; ?></label>
	<input type="text" name="fax" id="fax" value="<?php echo $user->get('fax'); ?>"/>

	<label for="website"><?php echo $website_label; ?></label>
	<input type="text" name="website" id="website" value="<?php echo $user->get('website'); ?>"/>

	<label for="email"><?php echo $email_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/>
	
	</div>
	<div class="form_column">
	<input type="checkbox" id="copy"/><label class="inline-block" for="copy"><?php echo $copy_label ?></label>
	<label for="invoice_company"><?php echo $invoice_company_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="invoice_company" id="invoice_company" value="<?php echo $user->get('invoice_company'); ?>"/>

	<label for="invoice_address"><?php echo $invoice_address_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="invoice_address" id="invoice_address" value="<?php echo $user->get('invoice_address'); ?>"/>

	<label for="invoice_zipcode"><?php echo $invoice_zipcode_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="invoice_zipcode" id="invoice_zipcode" value="<?php echo $user->get('invoice_zipcode'); ?>"/>

	<label for="invoice_city"><?php echo $invoice_city_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="invoice_city" id="invoice_city" value="<?php echo $user->get('invoice_city'); ?>"/>

	<label for="invoice_email"><?php echo $invoice_email_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="invoice_email" id="invoice_email" value="<?php echo $user->get('invoice_email'); ?>"/>

	<label for="presentation"><?php echo $presentation_label; ?></label>
	<textarea name="presentation" id="presentation"><?php echo $user->get('presentation'); ?></textarea>
	
		<label for="#"><?php echo $locked_label; ?></label>
	<input<?php echo ($user->get('locked') == 0) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="0" id="locked0"/><label for="locked0" class="inline-block"><?php echo $locked_label0; ?></label>
	<input<?php echo ($user->get('locked') == 1) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="1" id="locked1"/><label for="locked1" class="inline-block"><?php echo $locked_label1; ?></label>
	
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
	
	</div>	
	

</form>

<script type="text/javascript">
	<?php if( isset($js_confirm) ) : ?>
		$(document).ready(function(){
			alert('<?php echo $js_confirm_text; ?>');
			window.location.href = '<?php echo BASE_URL;?>user/overview/4';
		});
	<?php endif; ?>
</script>