<h1><?php echo $headline; ?></h1>

<p><a href="user/changePassword" class="button settings"><?php echo uh($translator->{'Change password'}); ?></a></p>

<form action="arranger/accountSettings" method="post">
	
	<label for="alias"><?php echo $alias_label; ?> *</label>
	<input type="text" name="alias" id="alias" value="<?php echo $user->get('alias'); ?>" disabled="disabled"/>
	
	<label for="company"><?php echo $company_label; ?> *</label>
	<input type="text" name="company" id="company" value="<?php echo $user->get('company'); ?>"/>
	
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
	
	<label for="website"><?php echo $website_label; ?></label>
	<input type="text" name="website" id="website" value="<?php echo $user->get('website'); ?>"/>
	
	<label for="email"><?php echo $email_label; ?> *</label>
	<input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/>
	
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>" class="save-btn" /></p>
	
</form>