<h1><?php echo $headline; ?></h1>
<form action="administrator/newExhibitor" method="post">
	
	<div class="form_column">

	<p class="error"><?php echo $error; ?></p>
	
	<label for="fair"><?php echo $fair_label; ?> *</label>
	<select name="fair" id="fair">
		<?php foreach ($fairs as $fair): ?>
		<option<?php if (isset($_SESSION['user_fair']) && $_SESSION['user_fair'] == $fair->get('id')) { echo ' selected="selected"'; } ?> value="<?php echo $fair->get('id'); ?>"><?php echo $fair->get('name'); ?></option>
		<?php endforeach; ?>
	</select>
	
	<label for="username"><?php echo $alias_label; ?></label>
	<input type="text" name="username" id="username" value="<?php echo $user->get('alias'); ?>"  />
	
	<label for="company"><?php echo $company_label; ?> *</label>
	<input type="text" name="company" id="company" value="<?php echo $user->get('company'); ?>"/>
	
	<label for="commodity"><?php echo $commodity_label; ?> *</label>
	<input type="text" name="commodity" id="commodity" value="<?php echo $user->get('commodity'); ?>"/>
	
	<!--<label for="category"><?php echo $category_label; ?> *</label>
	<select name="category" id="category">
	<?php echo makeOptions($fair->db, 'exhibitor_category', 0, 'fair='.$_SESSION['user_fair']); ?>
	</select>-->
	
	<label for="name"><?php echo $contact_label; ?> *</label>
	<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>
	
	<label for="orgnr"><?php echo $orgnr_label; ?> *</label>
	<input type="text" name="orgnr" id="orgnr" value="<?php echo $user->get('orgnr'); ?>"/>
	
	<label for="address"><?php echo $address_label; ?> *</label>
	<input type="text" name="address" id="address" value="<?php echo $user->get('address'); ?>"/>
	
	<label for="zipcode"><?php echo $zipcode_label; ?> *</label>
	<input type="text" name="zipcode" id="zipcode" value="<?php echo $user->get('zipcode'); ?>"/>
	
	<label for="city"><?php echo $city_label; ?> *</label>
	<input type="text" name="city" id="city" value="<?php echo $user->get('city'); ?>"/>
	
	<label for="country"><?php echo $country_label; ?> *</label>
	<input type="text" name="country" id="country" value="<?php echo $user->get('country'); ?>"/>
	
	<label for="phone1"><?php echo $phone1_label; ?> *</label>
	<input type="text" name="phone1" id="phone1" value="<?php echo $user->get('phone1'); ?>"/>
	
	<label for="phone2"><?php echo $phone2_label; ?></label>
	<input type="text" name="phone2" id="phone2" value="<?php echo $user->get('phone2'); ?>"/>
	
	<label for="phone3"><?php echo $phone3_label; ?></label>
	<input type="text" name="phone3" id="phone3" value="<?php echo $user->get('phone3'); ?>"/>
	
	<label for="fax"><?php echo $fax_label; ?></label>
	<input type="text" name="fax" id="fax" value="<?php echo $user->get('fax'); ?>"/>
	
	<label for="website"><?php echo $website_label; ?></label>
	<input type="text" name="website" id="website" value="<?php echo $user->get('website'); ?>"/>
	
	<label for="email"><?php echo $email_label; ?> *</label>
	<input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/>
	
	<label for="presentation"><?php echo $presentation_label; ?></label>
	<textarea name="presentation" id="presentation"><?php echo $user->get('presentation'); ?></textarea>
	</div>
	<div class="form_column">
	<h3><?php echo $invoice_section; ?></h3>
	
	<input type="checkbox" id="copy"/><label class="inline-block" for="copy"><?php echo $copy_label ?></label>
	
	<label for="invoice_company"><?php echo $invoice_company_label; ?></label>
	<input type="text" name="invoice_company" id="invoice_company" value="<?php echo $user->get('invoice_company'); ?>"/>
	
	<label for="invoice_address"><?php echo $invoice_address_label; ?></label>
	<input type="text" name="invoice_address" id="invoice_address" value="<?php echo $user->get('invoice_address'); ?>"/>
	
	<label for="invoice_zipcode"><?php echo $invoice_zipcode_label; ?></label>
	<input type="text" name="invoice_zipcode" id="invoice_zipcode" value="<?php echo $user->get('invoice_zipcode'); ?>"/>
	
	<label for="invoice_city"><?php echo $invoice_city_label; ?></label>
	<input type="text" name="invoice_city" id="invoice_city" value="<?php echo $user->get('invoice_city'); ?>"/>
	
	<label for="invoice_email"><?php echo $invoice_email_label; ?></label>
	<input type="text" name="invoice_email" id="invoice_email" value="<?php echo $user->get('invoice_email'); ?>"/>
	
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
	</div>
</form>