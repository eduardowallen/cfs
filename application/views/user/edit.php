<?php $country_list = array(
		"Afghanistan",
		"Albania",
		"Algeria",
		"Andorra",
		"Angola",
		"Antigua and Barbuda",
		"Argentina",
		"Armenia",
		"Australia",
		"Austria",
		"Azerbaijan",
		"Bahamas",
		"Bahrain",
		"Bangladesh",
		"Barbados",
		"Belarus",
		"Belgium",
		"Belize",
		"Benin",
		"Bhutan",
		"Bolivia",
		"Bosnia and Herzegovina",
		"Botswana",
		"Brazil",
		"Brunei",
		"Bulgaria",
		"Burkina Faso",
		"Burundi",
		"Cambodia",
		"Cameroon",
		"Canada",
		"Cape Verde",
		"Central African Republic",
		"Chad",
		"Chile",
		"China",
		"Colombi",
		"Comoros",
		"Congo (Brazzaville)",
		"Congo",
		"Costa Rica",
		"Cote d'Ivoire",
		"Croatia",
		"Cuba",
		"Cyprus",
		"Czech Republic",
		"Denmark",
		"Djibouti",
		"Dominica",
		"Dominican Republic",
		"East Timor (Timor Timur)",
		"Ecuador",
		"Egypt",
		"El Salvador",
		"Equatorial Guinea",
		"Eritrea",
		"Estonia",
		"Ethiopia",
		"Fiji",
		"Finland",
		"France",
		"Gabon",
		"Gambia, The",
		"Georgia",
		"Germany",
		"Ghana",
		"Greece",
		"Grenada",
		"Guatemala",
		"Guinea",
		"Guinea-Bissau",
		"Guyana",
		"Haiti",
		"Honduras",
		"Hungary",
		"Iceland",
		"India",
		"Indonesia",
		"Iran",
		"Iraq",
		"Ireland",
		"Israel",
		"Italy",
		"Jamaica",
		"Japan",
		"Jordan",
		"Kazakhstan",
		"Kenya",
		"Kiribati",
		"Korea, North",
		"Korea, South",
		"Kuwait",
		"Kyrgyzstan",
		"Laos",
		"Latvia",
		"Lebanon",
		"Lesotho",
		"Liberia",
		"Libya",
		"Liechtenstein",
		"Lithuania",
		"Luxembourg",
		"Macedonia",
		"Madagascar",
		"Malawi",
		"Malaysia",
		"Maldives",
		"Mali",
		"Malta",
		"Marshall Islands",
		"Mauritania",
		"Mauritius",
		"Mexico",
		"Micronesia",
		"Moldova",
		"Monaco",
		"Mongolia",
		"Morocco",
		"Mozambique",
		"Myanmar",
		"Namibia",
		"Nauru",
		"Nepal",
		"Netherlands",
		"New Zealand",
		"Nicaragua",
		"Niger",
		"Nigeria",
		"Norway",
		"Oman",
		"Pakistan",
		"Palau",
		"Panama",
		"Papua New Guinea",
		"Paraguay",
		"Peru",
		"Philippines",
		"Poland",
		"Portugal",
		"Qatar",
		"Romania",
		"Russia",
		"Rwanda",
		"Saint Kitts and Nevis",
		"Saint Lucia",
		"Saint Vincent",
		"Samoa",
		"San Marino",
		"Sao Tome and Principe",
		"Saudi Arabia",
		"Senegal",
		"Serbia and Montenegro",
		"Seychelles",
		"Sierra Leone",
		"Singapore",
		"Slovakia",
		"Slovenia",
		"Solomon Islands",
		"Somalia",
		"South Africa",
		"Spain",
		"Sri Lanka",
		"Sudan",
		"Suriname",
		"Swaziland",
		"Sweden",
		"Switzerland",
		"Syria",
		"Taiwan",
		"Tajikistan",
		"Tanzania",
		"Thailand",
		"Togo",
		"Tonga",
		"Trinidad and Tobago",
		"Tunisia",
		"Turkey",
		"Turkmenistan",
		"Tuvalu",
		"Uganda",
		"Ukraine",
		"United Arab Emirates",
		"United Kingdom",
		"United States",
		"Uruguay",
		"Uzbekistan",
		"Vanuatu",
		"Vatican City",
		"Venezuela",
		"Vietnam",
		"Yemen",
		"Zambia",
		"Zimbabwe"
	);
?>

<h1><?php echo $headline; ?></h1>

<?php if (isset($user_message)) {
	if (isset($error) && $error) {
		echo '<p class="error">'.$user_message.'</p>';
	} else {
		echo '<p>'.$user_message.'</p>';
	}
} ?>

<form action="user/edit/<?php echo $edit_id; ?><?php if (isset($edit_level)) { echo '/'.$edit_level; } ?>" method="post" id="edit_user_form">
	
	<div class="form_column">

	<label for="orgnr"><?php echo $orgnr_label; ?></label>
	<input type="text" name="orgnr" id="orgnr" value="<?php echo $user->get('orgnr'); ?>"/>
	
	<label for="company"><?php echo $company_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="company" id="company" value="<?php echo $user->get('company'); ?>"/>

	<label for="commodity"><?php echo $commodity_label; ?></label>
	<textarea rows="3" style="width:250px; height:40px; resize:none;" name="commodity" id="commodity"><?php echo $user->get('commodity'); ?></textarea>

	<label for="customer_nr"><?php echo $customer_nr_label; ?></label>
	<input type="text" name="customer_nr" id="customer_nr" value="<?php echo $user->get('customer_nr'); ?>"/>

	<label for="name"><?php echo $contact_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>

	<label for="address"><?php echo $address_label; ?></label>
	<input type="text" name="address" id="address" value="<?php echo $user->get('address'); ?>"/>

	<label for="zipcode"><?php echo $zipcode_label; ?></label>
	<input type="text" name="zipcode" id="zipcode" value="<?php echo $user->get('zipcode'); ?>"/>

	<label for="city"><?php echo $city_label; ?></label>
	<input type="text" name="city" id="city" value="<?php echo $user->get('city'); ?>"/>

	<label for="country"><?php echo $country_label; ?> *</label>
	<select name="country" id="country" style="width:258px;">
	<?php foreach($country_list as $country) : ?>
		<option value="<?php echo $country?>"><?php echo $country?></option>
	<?php endforeach; ?>
	</select>

	<label for="phone1"><?php echo $phone1_label; ?> <?php echo ($openFields) ? '' : '*'; ?></label>
	<input type="text" name="phone1" id="phone1" value="<?php echo $user->get('phone1'); ?>"/>

	<label for="phone2"><?php echo $phone2_label; ?></label>
	<input type="text" name="phone2" id="phone2" value="<?php echo $user->get('phone2'); ?>"/>

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
		<div style="">
			<label for="presentation"><?php echo $presentation_label; ?></label>
			<?php tiny_mce($path='js/tiny_mce/tiny_mce.js', 570, 'presentation') ?>
			<textarea name="presentation" class="presentation" id="presentation"><?php echo $user->get('presentation'); ?></textarea>
		</div>
	</div>


	<div class="form_column">
	<h3><?php echo $contact_section; ?></h3>
	<label for="alias"><?php echo $alias_label; ?> *</label>
	<input type="text" name="alias" id="alias" value="<?php echo $user->get('alias'); ?>" <?php echo $disabled?>/>

	<label for="name"><?php echo $contact_label; ?> *</label>
	<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>

	<label for="phone3"><?php echo $phone3_label; ?></label>
	<input type="text" name="phone3" id="phone3" value="<?php echo $user->get('contact_phone'); ?>"/>

	<label for="phone4"><?php echo $phone4_label; ?></label>
	<input type="text" name="phone4" id="phone4" value="<?php echo $user->get('contact_phone2'); ?>"/>

	<label for="contact_email"><?php echo $contact_email ?> *</label>
	<input type="text" name="contact_email" id="contact_email" value="<?php echo $user->get('contact_email'); ?>"/>

	</div>	
	
	
	<label for="#"><?php echo $locked_label; ?></label>
	<input<?php echo ($user->get('locked') == 0) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="0" id="locked0"/><label for="locked0" class="inline-block"><?php echo $locked_label0; ?></label>
	<input<?php echo ($user->get('locked') == 1) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="1" id="locked1"/><label for="locked1" class="inline-block"><?php echo $locked_label1; ?></label>
	
	
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>

</form>
