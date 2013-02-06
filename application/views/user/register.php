<h1><?php echo $headline; ?></h1>
<form action="user/register<?php echo (isset($fair_url)) ? '/'.$fair_url : ''; ?>" method="post">

	<p class="error"><?php echo $error; ?></p>
	
	<div class="form_column">
	<!--<h3><?php echo $company_section; ?></h3>-->

	<label for="orgnr"><?php echo $orgnr_label; ?> *</label>
	<input type="text" name="orgnr" id="orgnr" value="<?php echo $user->get('orgnr'); ?>"/>

	<label for="company"><?php echo $company_label; ?> *</label>
	<input type="text" name="company" id="company" value="<?php echo $user->get('company'); ?>"/>

	<label for="commodity"><?php echo $commodity_label; ?> *</label>
	<input type="text" name="commodity" id="commodity" value="<?php echo $user->get('commodity'); ?>"/>

	<!--<label for="category"><?php echo $category_label; ?> *</label>
	<select name="category" id="category">
	<?php echo makeOptions($user->db, 'exhibitor_category', 0, 'fair='.$fair->get('id')); ?>
	</select>-->

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

	<label for="email"><?php echo $email_label; ?> *</label>
	<input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/>

	<label for="website"><?php echo $website_label; ?></label>
	<input type="text" name="website" id="website" value="<?php echo $user->get('website'); ?>"/>

	<label for="presentation"><?php echo $presentation_label; ?></label>
	<textarea name="presentation" id="presentation"><?php echo $user->get('presentation'); ?></textarea>
	</div>

	<div class="form_column">

	<h3><?php echo $invoice_section; ?></h3>
	
	<input type="checkbox" id="copy"/><label class="inline-block" for="copy"><?php echo $copy_label ?></label>

	<label for="invoice_company"><?php echo $invoice_company_label; ?> *</label>
	<input type="text" name="invoice_company" id="invoice_company" value="<?php echo $user->get('invoice_company'); ?>"/>

	<label for="invoice_address"><?php echo $invoice_address_label; ?> *</label>
	<input type="text" name="invoice_address" id="invoice_address" value="<?php echo $user->get('invoice_address'); ?>"/>

	<label for="invoice_zipcode"><?php echo $invoice_zipcode_label; ?> *</label>
	<input type="text" name="invoice_zipcode" id="invoice_zipcode" value="<?php echo $user->get('invoice_zipcode'); ?>"/>

	<label for="invoice_city"><?php echo $invoice_city_label; ?> *</label>
	<input type="text" name="invoice_city" id="invoice_city" value="<?php echo $user->get('invoice_city'); ?>"/>

	<label for="invoice_email"><?php echo $invoice_email_label; ?> *</label>
	<input type="text" name="invoice_email" id="invoice_email" value="<?php echo $user->get('invoice_email'); ?>"/>

	<!--<h3><?php echo $contact_section; ?></h3>-->

	<label for="username"><?php echo $alias_label; ?></label>
	<input type="text" name="username" id="username" value="<?php echo $user->get('alias'); ?>"  />

	<label for="name"><?php echo $contact_label; ?> *</label>
	<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>

	<label for="password"><?php echo $password_label; ?> *</label>
	<input type="password" name="password" id="password" class="hasIndicator"/>

	<label for="password_repeat"><?php echo $password_repeat_label; ?> *</label>
	<input type="password" name="password_repeat" id="password_repeat"/>

	<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
	</div>
	
	<p style="display:inline-block; width:160px; background:#efefef; border:1px solid #b1b1b1; padding:10px; margin-right:0px;"><?php echo $pass_standard ?></p>

</form>