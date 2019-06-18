<?php $country_list = array(
		"Sweden",
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
		"Perú",
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
<form action="administrator/newExhibitor" method="post">
  <?php if(isset($error) && $error != ''): ?>
    <p class="error"><?php echo $error; ?></p>
  <?php endif; ?>
	
	<label for="fair"><?php echo $fair_label; ?> *</label>
	<select name="fair" id="fair">
		<?php foreach ($fairs as $fair): ?>
		<option<?php if (isset($_SESSION['user_fair']) && $_SESSION['user_fair'] == $fair->get('id')) { echo ' selected="selected"'; } ?> value="<?php echo $fair->get('id'); ?>"><?php echo $fair->get('name'); ?></option>
		<?php endforeach; ?>
	</select>
	<br class="clear">
	<br class="clear">

  <div class="form_column" id="form_column1">
    <h3><?php echo uh($translator->{'Company'}); ?></h3>

    <label for="email"><?php echo uh($translator->{'E-mail'}); ?> *</label>
    <input type="text" autocomplete="off" name="email" id="email" title="<?php echo uh($translator->{"Insert your email address."}); ?>" value="<?php echo $user->get('email'); ?>"/>
    
    <label for="orgnr"><?php echo uh($translator->{'Organization number'}); ?> *</label>
    <input type="text" name="orgnr" id="orgnr" title="<?php echo uh($translator->{"Insert the organization number of your organization."}); ?>" value="<?php echo $user->get('orgnr'); ?>"/>
    
    <label for="company"><?php echo uh($translator->{'Company'}); ?> *</label>
    <input type="text" name="company" id="company" title="<?php echo uh($translator->{"Insert the name of your organization."}); ?>" value="<?php echo $user->get('company'); ?>"/>
    
    <label for="commodity"><?php echo uh($translator->{'Commodity'}); ?> *</label>
    <textarea rows="3" maxlength="200" style="width:250px; height:40px; resize:none;" name="commodity" title="<?php echo uh($translator->{"Insert the commodity that your organization represents."}); ?>" id="commodity"><?php echo $user->get('commodity'); ?></textarea>
    
    <label for="address"><?php echo uh($translator->{'Address'}); ?> *</label>
    <input type="text" name="address" id="address" title="<?php echo uh($translator->{"Insert the address of your organization."}); ?>" value="<?php echo $user->get('address'); ?>"/>
    
    <label for="zipcode"><?php echo uh($translator->{'Zip code'}); ?> *</label>
    <input type="text" name="zipcode" id="zipcode" title="<?php echo uh($translator->{"Insert the zip code of your organization."}); ?>" value="<?php echo $user->get('zipcode'); ?>"/>
    
    <label for="city"><?php echo uh($translator->{'City'}); ?> *</label>
    <input type="text" name="city" id="city" title="<?php echo uh($translator->{"Insert the city that your organization resides in."}); ?>" value="<?php echo $user->get('city'); ?>"/>
    
    <label for="country"><?php echo uh($translator->{'Country'}); ?> *</label>
    <select name="country" id="country" title="<?php echo uh($translator->{"Select the country that your organization resides in."}); ?>" style="width:258px;">
		<?php foreach($country_list as $country) : ?>
			<?php if($country == $user->get('country')):?>
				<option value="<?php echo $country?>" selected><?php echo $country?></option>
			<?php else:?>
				<option value="<?php echo $country?>"><?php echo $country?></option>
			<?php endif?>
		<?php endforeach; ?>
    </select>

    <label for="phone1"><?php echo uh($translator->{'Phone 1'}); ?> *</label>
    <input type="text" name="phone1" id="phone1" class="phone-val" title="<?php echo uh($translator->{"Insert the phone number of your organization."}); ?>" value="<?php echo $user->get('phone1'); ?>"/>
          
    <label for="phone2"><?php echo uh($translator->{'Phone 2'}); ?></label>
    <input type="text" name="phone2" id="phone2" class="phone-val" title="<?php echo uh($translator->{"Insert the second phone number of your organization."}); ?>" value="<?php echo $user->get('phone2'); ?>"/>
          
    <label for="website"><?php echo uh($translator->{'Website'}); ?></label>
    <input type="text" name="website" id="website" title="<?php echo uh($translator->{"Insert the website address of your organization."}); ?>" value="<?php echo $user->get('website'); ?>"/>
	
  </div>
        
  <div class="form_column" id="form_column2">
        
    <h3><?php echo uh($translator->{'Billing address'}); ?></h3>
    <input type="checkbox" id="copy" style="margin:0;"/><label class="squaredFour" style="display:inline-block; margin-right: 7px; vertical-align:inherit;" for="copy"></label><?php echo uh($translator->{'Copy from company details'}); ?>
	
    <label for="invoice_email"><?php echo uh($translator->{'E-mail'}); ?> *</label>
    <input type="text" autocomplete="off" name="invoice_email" id="invoice_email" title="<?php echo uh($translator->{"Insert the email address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_email'); ?>"/>
          
    <label for="invoice_company"><?php echo uh($translator->{'Company'}); ?> *</label>
    <input type="text" name="invoice_company" id="invoice_company" title="<?php echo uh($translator->{"Insert the organization name for the invoice."}); ?>" value="<?php echo $user->get('invoice_company'); ?>"/>
          
    <label for="invoice_address"><?php echo uh($translator->{'Address'}); ?> *</label>
    <input type="text" name="invoice_address" id="invoice_address" title="<?php echo uh($translator->{"Insert the address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_address'); ?>"/>
          
    <label for="invoice_zipcode"><?php echo uh($translator->{'Zip code'}); ?> *</label>
    <input type="text" name="invoice_zipcode" id="invoice_zipcode" title="<?php echo uh($translator->{"Insert the zip code at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_zipcode'); ?>"/>
          
    <label for="invoice_city"><?php echo uh($translator->{'City'}); ?> *</label>
    <input type="text" name="invoice_city" id="invoice_city" title="<?php echo uh($translator->{"Insert the city at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_city'); ?>"/>
          
    <label for="invoice_country"><?php echo uh($translator->{'Country'}); ?> *</label>
		<select name="invoice_country" id="invoice_country" title="<?php echo uh($translator->{"Select the country that the invoice organization resides in."}); ?>" style="width:258px;">
		<?php foreach($country_list as $country) : ?>
			<?php if($country == $user->get('invoice_country')):?>
				<option value="<?php echo $country?>" selected><?php echo $country?></option>
			<?php else:?>
				<option value="<?php echo $country?>"><?php echo $country?></option>
			<?php endif?>
		<?php endforeach; ?>
		</select>

        <label for="presentation"<?php echo (userLevel()==0?' style="margin-top:50px;"':''); ?>><?php echo uh($translator->{'Presentation (this is what will be shown to your customers)'}); ?></label>
        <?php tiny_mce($path='js/tiny_mce/tiny_mce.js', 565, 'presentation'); ?>
        <textarea style="height:355px;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></textarea>
  <p>
    <input type="submit" name="save" value="<?php echo uh($translator->{'Save'}); ?>" class="greenbutton bigbutton" />
  </p>		


  </div>
          
	<div class="form_column" id="form_column3">

    <h3><?php echo uh($translator->{'Contact'}); ?></h3>
          
    <label for="alias"><?php echo uh($translator->{'Username'}); ?> *</label>
    <input type="text" autocomplete="off" name="alias" id="alias" title="<?php echo uh($translator->{"Insert the desired username for this account. This will later on be used for you to log onto your account."}); ?>" value="<?php echo $user->get('alias'); ?>"<?php if ($user->get('id') != 0) { echo 'disabled="disabled"'; } ?>/>
          
    <label for="name"><?php echo uh($translator->{'Contact person'}); ?> *</label>
    <input type="text" name="name" id="name" title="<?php echo uh($translator->{"Insert the name of the contact person of this account."}); ?>" value="<?php echo $user->get('name'); ?>"/>
     
    <label for="phone3"><?php echo uh($translator->{'Contact Phone'}); ?> *</label>
    <input type="text" name="phone3" id="phone3" title="<?php echo uh($translator->{"Insert the phone number that we can reach the contact person for support about this account."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone'); ?>"/>

    <label for="phone4"><?php echo uh($translator->{'Contact Phone 2'}); ?> *</label>
    <input type="text" name="phone4" id="phone4" title="<?php echo uh($translator->{"Insert the cell phone number of the contact person. for this account. This information will be visible to organizers of events that you attend to."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone2'); ?>"/>

    <label for="contact_email"><?php echo uh($translator->{'Contact Email'}); ?> *</label>
    <input type="text" autocomplete="off" name="contact_email" id="contact_email" title="<?php echo uh($translator->{"Insert the email address of the contact person for this account. This information will be visible to organizers of events that you attend to."}); ?>" value="<?php echo $user->get('contact_email'); ?>"/>

  </div>
  



</form>