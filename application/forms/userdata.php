<?php
  /*  Form UserData used in registration-popup, on /user/register, /user/accountSettings, /user/edit, /arranger/edit
   *  Accepted variables:
   *    $popup - if defined, omits header and prints a JS exit button instead
   *    $header - if $popup is not defined, is written above the form
   *    $action - is used as the action field for the form, use this to direct form to the right location
   *    $user - if defined, provides existing information for the form
   *    $fairs - if defined, lists the information about fairs associated with the user
   *    $error - contains the error to be output, if any
   *  Function userLevel() is used to check the user access level
   *    0: a new user is registering themselves, in which case the form adds fields for password
   *    4: a master has some additional fields to edit, such as customer number and account lock
   */
  global $translator;
  
  if(!isset($user)) // If no user is defined...
    $user=new User(); // ...create a new empty user so that calling $user->get('<something>'); doesn't cause a fatal error
  
  $country_list = array("Sweden", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia,  The","Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea,  North","Korea,  South","Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");
?>
<form action="<?php echo $action; ?>" method="post"<?php echo isset($popup)?' id="popupform_register"':''; ?>>


<?php if(isset($popup)) : ?>
  <img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="position:absolute; margin-left:90.4%"/>
<?php else: ?>
  <h1><?php echo $headline; ?></h1>

  <p class="error"><?php echo (isset($error)?$error:''); ?></p>
<?php endif; ?>
  
  <div class="form_column" id="form_column1">
    <h3><?php echo htmlspecialchars($translator->{'Company'}); ?></h3>

    <label for="email"><?php echo htmlspecialchars($translator->{'E-mail'}); ?> *</label>
    <input type="text" autocomplete="off" name="email" id="email" title="<?php echo ujs($translator->{"Insert your email address."}); ?>" value="<?php echo $user->get('email'); ?>"/>
    
    <label for="orgnr"><?php echo htmlspecialchars($translator->{'Organization number'}); ?> *</label>
    <input type="text" name="orgnr" id="orgnr" title="<?php echo ujs($translator->{"Insert the organization number of your organization."}); ?>" value="<?php echo $user->get('orgnr'); ?>"/>
    
    <label for="company"><?php echo htmlspecialchars($translator->{'Company'}); ?> *</label>
    <input type="text" name="company" id="company" title="<?php echo ujs($translator->{"Insert the name of your organization."}); ?>" value="<?php echo $user->get('company'); ?>"/>
    
    <label for="commodity"><?php echo htmlspecialchars($translator->{'Commodity'}); ?> *</label>
    <textarea rows="3" style="width:250px; height:40px; resize:none;" name="commodity" title="<?php echo ujs($translator->{"Insert the commodity that your organization represents."}); ?>" id="commodity"><?php echo $user->get('commodity'); ?></textarea>
    
<?php if(userLevel() == 4): ?>
    <label for="customer_nr"><?php echo htmlspecialchars($translator->{'Customer number'}); ?></label>
    <input type="text" name="customer_nr" id="customer_nr" value="<?php echo $user->get('customer_nr'); ?>"/>
<?php endif; ?>

    <label for="address"><?php echo htmlspecialchars($translator->{'Address'}); ?> *</label>
    <input type="text" name="address" id="address" title="<?php echo ujs($translator->{"Insert the address of your organization."}); ?>" value="<?php echo $user->get('address'); ?>"/>
    
    <label for="zipcode"><?php echo htmlspecialchars($translator->{'Zip code'}); ?> *</label>
    <input type="text" name="zipcode" id="zipcode" title="<?php echo ujs($translator->{"Insert the zip code of your organization."}); ?>" value="<?php echo $user->get('zipcode'); ?>"/>
    
    <label for="city"><?php echo htmlspecialchars($translator->{'City'}); ?> *</label>
    <input type="text" name="city" id="city" title="<?php echo ujs($translator->{"Insert the city that your organization resides in."}); ?>" value="<?php echo $user->get('city'); ?>"/>
    
    <label for="country"><?php echo htmlspecialchars($translator->{'Country'}); ?> *</label>
    <select name="country" id="country" title="<?php echo ujs($translator->{"Select the country that your organization resides in."}); ?>" style="width:258px;">
		<?php foreach($country_list as $country) : ?>
			<?php if($country == $user->get('country')):?>
				<option value="<?php echo $country?>" selected><?php echo $country?></option>
			<?php else:?>
				<option value="<?php echo $country?>"><?php echo $country?></option>
			<?php endif?>
		<?php endforeach; ?>
    </select>

    <label for="phone1"><?php echo htmlspecialchars($translator->{'Phone 1'}); ?> *</label>
    <input type="text" name="phone1" id="phone1" class="phone-val" title="<?php echo ujs($translator->{"Insert the phone number of your organization."}); ?>" value="<?php echo $user->get('phone1'); ?>"/>
          
    <label for="phone2"><?php echo htmlspecialchars($translator->{'Phone 2'}); ?></label>
    <input type="text" name="phone2" id="phone2" class="phone-val" title="<?php echo ujs($translator->{"Insert the second phone number of your organization."}); ?>" value="<?php echo $user->get('phone2'); ?>"/>
          
    <label for="fax"><?php echo htmlspecialchars($translator->{'Fax number'}); ?></label>
    <input type="text" name="fax" id="fax" title="<?php echo ujs($translator->{"Insert fax number of your organization."}); ?>" value="<?php echo $user->get('fax'); ?>"/>
          
    <label for="website"><?php echo htmlspecialchars($translator->{'Website'}); ?></label>
    <input type="text" name="website" id="website" title="<?php echo ujs($translator->{"Insert the website address of your organization."}); ?>" value="<?php echo $user->get('website'); ?>"/>
	
<?php if(userLevel() == 4): ?>
	<label for="#"><?php echo htmlspecialchars($translator->{'Account locked'}); ?></label>
	<input<?php echo ($user->get('locked') == 0) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="0" id="locked0"/><label for="locked0" class="inline-block"><?php echo htmlspecialchars($translator->{'No'}); ?></label>
	<input<?php echo ($user->get('locked') == 1) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="1" id="locked1"/><label for="locked1" class="inline-block"><?php echo htmlspecialchars($translator->{'Yes'}); ?></label>
<?php endif; ?>

  </div>
        
  <div class="form_column" id="form_column2">
        
    <h3><?php echo htmlspecialchars($translator->{'Billing address'}); ?></h3>
            
    <input type="checkbox" id="copy"/>
    <label class="inline-block" for="copy"><?php echo htmlspecialchars($translator->{'Copy from company details'}); ?></label>
	
    <label for="invoice_email"><?php echo htmlspecialchars($translator->{'E-mail'}); ?> *</label>
    <input type="text" autocomplete="off" name="invoice_email" id="invoice_email" title="<?php echo ujs($translator->{"Insert the email address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_email'); ?>"/>
          
    <label for="invoice_company"><?php echo htmlspecialchars($translator->{'Company'}); ?> *</label>
    <input type="text" name="invoice_company" id="invoice_company" title="<?php echo ujs($translator->{"Insert the organization name for the invoice."}); ?>" value="<?php echo $user->get('invoice_company'); ?>"/>
          
    <label for="invoice_address"><?php echo htmlspecialchars($translator->{'Address'}); ?> *</label>
    <input type="text" name="invoice_address" id="invoice_address" title="<?php echo ujs($translator->{"Insert the address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_address'); ?>"/>
          
    <label for="invoice_zipcode"><?php echo htmlspecialchars($translator->{'Zip code'}); ?> *</label>
    <input type="text" name="invoice_zipcode" id="invoice_zipcode" title="<?php echo ujs($translator->{"Insert the zip code at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_zipcode'); ?>"/>
          
    <label for="invoice_city"><?php echo htmlspecialchars($translator->{'City'}); ?> *</label>
    <input type="text" name="invoice_city" id="invoice_city" title="<?php echo ujs($translator->{"Insert the city at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_city'); ?>"/>
          
    <label for="invoice_country"><?php echo htmlspecialchars($translator->{'Country'}); ?> *</label>
		<select name="invoice_country" id="invoice_country" title="<?php echo ujs($translator->{"Select the country that the invoice organization resides in."}); ?>" style="width:258px;">
		<?php foreach($country_list as $country) : ?>
			<?php if($country == $user->get('invoice_country')):?>
				<option value="<?php echo $country?>" selected><?php echo $country?></option>
			<?php else:?>
				<option value="<?php echo $country?>"><?php echo $country?></option>
			<?php endif?>
		<?php endforeach; ?>
		</select>

    <?php
      if(isset($popup)):
    ?>
        <div style="margin-top:50px;">
          <label for="presentation"><?php echo htmlspecialchars($translator->{'Presentation (this is what will be shown to your customers)'}); ?></label>
          <textarea style="height:355px;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></textarea>
        </div>
		
  <p>
    <input type="submit" name="save" value="<?php echo htmlspecialchars($translator->{'Save'}); ?>" class="save-btn" />
  </p>
    <?php else: ?>
        <label for="presentation"<?php echo (userLevel()==0?' style="margin-top:50px;"':''); ?>><?php echo htmlspecialchars($translator->{'Presentation (this is what will be shown to your customers)'}); ?></label>
        <?php tiny_mce($path='js/tiny_mce/tiny_mce.js', 565, 'presentation'); ?>
        <textarea style="height:355px;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></textarea>
  <p>
    <input type="submit" name="save" value="<?php echo htmlspecialchars($translator->{'Save'}); ?>" class="save-btn" />
  </p>		

    <?php endif; ?>
  </div>
          
	<div class="form_column" id="form_column3">

    <h3><?php echo htmlspecialchars($translator->{'Contact'}); ?></h3>
          
    <label for="alias"><?php echo htmlspecialchars($translator->{'Username'}); ?> *</label>
    <input type="text" autocomplete="off" name="alias" id="alias" title="<?php echo ujs($translator->{"Insert the desired username for this account. This will later on be used for you to log onto your account."}); ?>" value="<?php echo $user->get('alias'); ?>"<?php if ($user->get('id') != 0) { echo 'disabled="disabled"'; } ?>/>
          
    <label for="name"><?php echo htmlspecialchars($translator->{'Contact person'}); ?> *</label>
    <input type="text" name="name" id="name" title="<?php echo ujs($translator->{"Insert the name of the contact person of this account."}); ?>" value="<?php echo $user->get('name'); ?>"/>
     
    <label for="phone3"><?php echo htmlspecialchars($translator->{'Contact Phone'}); ?> *</label>
    <input type="text" name="phone3" id="phone3" title="<?php echo ujs($translator->{"Insert the phone number that we can reach the contact person for support about this account."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone'); ?>"/>

    <label for="phone4"><?php echo htmlspecialchars($translator->{'Contact Phone 2'}); ?> *</label>
    <input type="text" name="phone4" id="phone4" title="<?php echo ujs($translator->{"Insert the cell phone number of the contact person. for this account. This information will be visible to organizers of events that you attend to."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone2'); ?>"/>

    <label for="contact_email"><?php echo htmlspecialchars($translator->{'Contact Email'}); ?> *</label>
    <input type="text" autocomplete="off" name="contact_email" id="contact_email" title="<?php echo ujs($translator->{"Insert the email address of the contact person for this account. This information will be visible to organizers of events that you attend to."}); ?>" value="<?php echo $user->get('contact_email'); ?>"/>

<?php if(userLevel() == 0): ?>
    <label for="password"><?php echo htmlspecialchars($translator->{'Password'}); ?> *</label>
    <input type="password" name="password" id="password" title="<?php echo ujs($translator->{"Insert a password for this account. The password has to be Strong or Super strong."}); ?>" class="hasIndicator"/>

    <label for="password_repeat"><?php echo htmlspecialchars($translator->{'Password again (repeat to confirm)'}); ?> *</label>
    <input type="password" name="password_repeat" id="password_repeat" title="<?php echo ujs($translator->{"Repeat the password that you entered in the previous field."}); ?>"/>
		  
      
    </p>
<?php endif; ?>
  </div>
  



</form>

<?php
  if(isset($fairs)):
    foreach ($fairs as $fair):
?>
  <?php
    if ($fair['approved'] == 2) {
      $app = $translator->{'Locked'};
    } else if ($fair['approved'] == 1) {
      $app = $translator->{'Active'};
    } else {
      $app = $translator->{'Inactive'};
    }
  ?>
	<div class="fair-info">
    <p class="floatright">
      <a href="administrator/overview/<?php echo $fair['id'] ?>"><?php echo htmlspecialchars($translator->{'Administrators'}); ?></a>
      <a href="exhibitor/exhibitors/<?php echo $fair['id'] ?>"><?php echo htmlspecialchars($translator->{'Exhibitors'}); ?></a>
    </p>

    <?php echo '<strong>'.htmlspecialchars($translator->{'Event Name'}); ?></strong> <?php echo $fair['name']; ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Status'}).':</strong> '.htmlspecialchars($app); ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Website'}).'</strong>: <a href="'.$fair['url'].'">'.$fair['url'].'</a><br />'; ?>
    <?php echo '<strong>'.htmlspecialchars($translator->{'Maximum stand spaces'}).'</strong>: '.$fair['max_positions']; ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Number of visitors'}).'</strong>: '.$fair['page_views']; ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Number of occupied spaces'}).'</strong>: '.$fair['occupied_spaces']; ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Number of free spaces'}).'</strong>: '.$fair['free_spaces']; ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Creation time'}).'</strong>: '; if ($fair['creation_time']) echo date('d-m-Y H:i:s', $fair['creation_time']); ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Opening time'}).'</strong>: '; echo date('d-m-Y H:i:s', $fair['auto_publish']); ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Closing time'}).'</strong>: '; echo date('d-m-Y H:i:s', $fair['auto_close']); ?><br />
    <?php echo '<strong>'.htmlspecialchars($translator->{'Lock time'}).'</strong>: '; if ($fair['closing_time']) echo date('d-m-Y H:i:s', $fair['closing_time']); ?><br />
	</div>
<?php
    endforeach;
  endif;
?>