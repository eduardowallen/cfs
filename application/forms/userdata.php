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
   *    4: a master has some additional fields to edit, such as account lock
   */
  global $translator;
  
  if(!isset($user)) // If no user is defined...
    $user=new User(); // ...create a new empty user so that calling $user->get('<something>'); doesn't cause a fatal error
  
  $country_list = array("Sweden", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia,  The","Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea,  North","Korea,  South","Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");
?>
<?php if(isset($popup)) : ?>

<style>
  .squaredFour{width:1.416em; height:1.416em;}
  .squaredFour:before{left:0.33em;top:0.33em;}
/* USER REGISTER FORM */
#popupform_register {
  position: absolute;
  width: 66em;
  margin-bottom: 2em;
  background:transparent;
  border:none;
  -moz-border-radius:8px;
  -webkit-border-radius:8px;
  border-radius:0px;
}
#popupform_register fieldset {
  border-top: solid #48A547 5em;
  background: white;
  padding: 3em;
  width: 66em;
  /*stacking fieldsets above each other*/
  position: absolute;
}
#popupform_register #progressbar {
  width: 66em;
}
#popupform_register #progressbar li {
  width: 25%;
}
/*Hide all except first fieldset*/
#popupform_register fieldset:not(:first-of-type) {
  display: none;
}
#popupform_register .form_column {
  width: 50%;
  padding: 2em 0em 1em 3em;
}
#popupform_register .standSpaceName {
  margin-left: 0.8em;
  margin-top: -5.3em;
}

#presentation_ifr {
  height:15em !important;
}
</style>
<?php endif; ?>
<form action="<?php echo $action; ?>" <?php echo isset($popup)?' class="form popup"':'class="form"'; ?> method="post"<?php echo isset($popup)?' id="popupform_register"':'id="form_register"'; ?>>
<?php if(isset($popup)) : ?>

  <ul id="progressbar" style="display:none">
    <li class="active"></li>
    <li></li>
    <li></li>
    <li></li>
  </ul>

  <fieldset>
    <h3 class="standSpaceName"><?php echo uh($translator->{'Company information'}); ?></h3>    
    <div class="form_column" id="form_column1">

      <label for="contact_email"><?php echo uh($translator->{'Contact Email'}); ?> *</label>
      <input type="text" autocomplete="off" name="contact_email" id="contact_email" title="<?php echo uh($translator->{"Insert the email address of the contact person for this account. This information will be visible to organizers of events that you attend to."}); ?>" value="<?php echo $user->get('contact_email'); ?>" placeholder="<?php echo uh($translator->{"Contact Email address"}); ?>"/>
      
      <label for="orgnr"><?php echo uh($translator->{'Organization number'}); ?> *</label>
      <input type="text" name="orgnr" id="orgnr" title="<?php echo uh($translator->{"Insert the organization number of your organization."}); ?>" value="<?php echo $user->get('orgnr'); ?>" placeholder="<?php echo uh($translator->{"Organization number"}); ?>"/>
      
      <label for="company"><?php echo uh($translator->{'Company name'}); ?> *</label>
      <input type="text" name="company" id="company" title="<?php echo uh($translator->{"Insert the name of your organization."}); ?>" value="<?php echo $user->get('company'); ?>" placeholder="<?php echo uh($translator->{"Name of organization"}); ?>"/>
      
      <label for="commodity"><?php echo uh($translator->{'Commodity'}); ?> *</label>
      <textarea rows="3" maxlength="200" style="width:20.83em; height:3.33em; resize:none;" name="commodity" title="<?php echo uh($translator->{"Insert the commodity that your organization represents."}); ?>" placeholder="<?php echo uh($translator->{"Organization commodity"}); ?>" id="commodity"><?php echo $user->get('commodity'); ?></textarea>
      
      <label for="address"><?php echo uh($translator->{'Address'}); ?> *</label>
      <input type="text" name="address" id="address" title="<?php echo uh($translator->{"Insert the address of your organization."}); ?>" value="<?php echo $user->get('address'); ?>" placeholder="<?php echo uh($translator->{"Organization address"}); ?>"/>
      
      <label for="zipcode"><?php echo uh($translator->{'Zip code'}); ?> *</label>
      <input type="text" name="zipcode" id="zipcode" title="<?php echo uh($translator->{"Insert the zip code of your organization."}); ?>" value="<?php echo $user->get('zipcode'); ?>" placeholder="<?php echo uh($translator->{"Organization zipcode"}); ?>"/>
      
      <label for="city"><?php echo uh($translator->{'City'}); ?> *</label>
      <input type="text" name="city" id="city" title="<?php echo uh($translator->{"Insert the city that your organization resides in."}); ?>" value="<?php echo $user->get('city'); ?>" placeholder="<?php echo uh($translator->{"Organization city"}); ?>"/>
      
      <label for="country"><?php echo uh($translator->{'Country'}); ?> *</label>
      <select name="country" id="country" title="<?php echo uh($translator->{"Select the country that your organization resides in."}); ?>" style="width:21.5em;">
  		<?php foreach($country_list as $country) : ?>
  			<?php if($country == $user->get('country')):?>
  				<option value="<?php echo $country?>" selected><?php echo $country?></option>
  			<?php else:?>
  				<option value="<?php echo $country?>"><?php echo $country?></option>
  			<?php endif?>
  		<?php endforeach; ?>
      </select>
    </div>
    <div class="form_column" id="form_column2">
      <label for="phone1"><?php echo uh($translator->{'Phone 1'}); ?> *</label>
      <input type="text" name="phone1" id="phone1" class="phone-val" title="<?php echo uh($translator->{"Insert the phone number of your organization."}); ?>" value="<?php echo $user->get('phone1'); ?>" placeholder="<?php echo uh($translator->{"eg: +46019294690"}); ?>"/>
            
      <label for="phone2"><?php echo uh($translator->{'Phone 2'}); ?></label>
      <input type="text" name="phone2" id="phone2" class="phone-val" title="<?php echo uh($translator->{"Insert the second phone number of your organization."}); ?>" value="<?php echo $user->get('phone2'); ?>" placeholder="<?php echo uh($translator->{"eg: +46707386668"}); ?>"/>
            
      <label for="website"><?php echo uh($translator->{'Website'}); ?></label>
      <input type="text" name="website" id="website" title="<?php echo uh($translator->{"Insert the website url of your organization."}); ?>" value="<?php echo $user->get('website'); ?>" placeholder="<?php echo uh($translator->{"eg: www.example.com"}); ?>"/>

      <label for="facebook">Facebook</label>
      <input type="text" name="facebook" id="facebook" title="<?php echo uh($translator->{"Insert the Facebook url of your organization."}); ?>" value="<?php echo $user->get('facebook'); ?>" placeholder="<?php echo uh($translator->{"eg: https://facebook.com/youralias"}); ?>"/>

      <label for="twitter">Twitter</label>
      <input type="text" name="twitter" id="twitter" title="<?php echo uh($translator->{"Insert the Twitter url of your organization."}); ?>" value="<?php echo $user->get('twitter'); ?>" placeholder="<?php echo uh($translator->{"eg: https://twitter.com/youralias"}); ?>"/>

      <label for="google_plus">Google+</label>
      <input type="text" name="google_plus" id="google_plus" title="<?php echo uh($translator->{"Insert the Google+ url of your organization."}); ?>" value="<?php echo $user->get('google_plus'); ?>" placeholder="<?php echo uh($translator->{"eg: https://plus.google.com/+youralias"}); ?>"/>

      <label for="youtube">Youtube</label>
      <input type="text" name="youtube" id="youtube" title="<?php echo uh($translator->{"Insert the Youtube url channel of your organization."}); ?>" value="<?php echo $user->get('youtube'); ?>" placeholder="<?php echo uh($translator->{"eg: https://youtube.com/youralias"}); ?>"/>
    </div>
      <br />

      <input type="button" name="cancel" style="margin-left:2.5em;" class="cancelbutton redbutton mediumbutton" value="<?php echo uh($translator->{'Cancel'}); ?>" />
      <input type="button" id="register_first_step" name="next" class="greenbutton mediumbutton" value="<?php echo uh($translator->{'Next'}); ?>" />
  </fieldset>

  <fieldset>
    <h3 class="standSpaceName"><?php echo uh($translator->{'Billing address'}); ?></h3>
    <div class="form_column" id="form_column3">
      <input type="checkbox" id="copy" style="margin:0;"/><label class="squaredFour" style="display:inline-block; margin-right: 0.583em; vertical-align:inherit;" for="copy"></label><?php echo uh($translator->{'Copy from company details'}); ?>
  	
      <label for="invoice_email"><?php echo uh($translator->{'E-mail'}); ?> *</label>
      <input type="text" autocomplete="off" name="invoice_email" id="invoice_email" title="<?php echo uh($translator->{"Insert the email address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_email'); ?>" placeholder="<?php echo uh($translator->{"Organization invoice email"}); ?>"/>
            
      <label for="invoice_company"><?php echo uh($translator->{'Company'}); ?> *</label>
      <input type="text" name="invoice_company" id="invoice_company" title="<?php echo uh($translator->{"Insert the organization name for the invoice."}); ?>" value="<?php echo $user->get('invoice_company'); ?>" placeholder="<?php echo uh($translator->{"Organization name for invoice"}); ?>"/>
            
      <label for="invoice_address"><?php echo uh($translator->{'Address'}); ?> *</label>
      <input type="text" name="invoice_address" id="invoice_address" title="<?php echo uh($translator->{"Insert the address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_address'); ?>" placeholder="<?php echo uh($translator->{"Organization address for invoice"}); ?>"/>
    </div>

    <div class="form_column" id="form_column4">
      <br />
      <label for="invoice_zipcode"><?php echo uh($translator->{'Zip code'}); ?> *</label>
      <input type="text" name="invoice_zipcode" id="invoice_zipcode" title="<?php echo uh($translator->{"Insert the zip code at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_zipcode'); ?>" placeholder="<?php echo uh($translator->{"Organization zipcode for invoice"}); ?>"/>
            
      <label for="invoice_city"><?php echo uh($translator->{'City'}); ?> *</label>
      <input type="text" name="invoice_city" id="invoice_city" title="<?php echo uh($translator->{"Insert the city at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_city'); ?>" placeholder="<?php echo uh($translator->{"Organization city for invoice"}); ?>"/>
            
      <label for="invoice_country"><?php echo uh($translator->{'Country'}); ?> *</label>
  		<select name="invoice_country" id="invoice_country" title="<?php echo uh($translator->{"Select the country that the invoice organization resides in."}); ?>" style="width:21.5em;">
  		<?php foreach($country_list as $country) : ?>
  			<?php if($country == $user->get('invoice_country')):?>
  				<option value="<?php echo $country?>" selected><?php echo $country?></option>
  			<?php else:?>
  				<option value="<?php echo $country?>"><?php echo $country?></option>
  			<?php endif?>
  		<?php endforeach; ?>
  		</select>
    </div>
    <input type="button" name="previous" style="margin-left:2.5em;" class="previous bluebutton mediumbutton" value="<?php echo uh($translator->{'Previous'}); ?>" />
    <input type="button" name="cancel" class="cancelbutton redbutton mediumbutton" value="<?php echo uh($translator->{'Cancel'}); ?>" />
    <input type="button" id="register_second_step" name="next" class="greenbutton mediumbutton" value="<?php echo uh($translator->{'Next'}); ?>" />    
  </fieldset>
    <fieldset>
    <h3 class="standSpaceName"><?php echo uh($translator->{'Company presentation'}); ?></h3>
        <div style="padding: 2em 3em 0em;">
          <label for="presentation"><?php echo uh($translator->{'Presentation (this is what will be shown to your customers)'}); ?></label>
          <textarea style="height:15em;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></textarea>
        </div>
    <br />
    <input type="button" name="previous" style="margin-left:2.5em;" class="previous bluebutton mediumbutton" value="<?php echo uh($translator->{'Previous'}); ?>" />
    <input type="button" name="cancel" class="cancelbutton redbutton mediumbutton" value="<?php echo uh($translator->{'Cancel'}); ?>" />
    <input type="button" name="next" class="next greenbutton mediumbutton" value="<?php echo uh($translator->{'Next'}); ?>" />
  </fieldset>

  <fieldset>
    <h3 class="standSpaceName"><?php echo uh($translator->{'Account and Contact'}); ?></h3>
  	<div class="form_column" id="form_column5">

      <label for="alias"><?php echo uh($translator->{'Username'}); ?> *</label>
      <input type="text" autocomplete="off" name="alias" id="alias" onblur="this.value=forceLower(this.value);" style="text-transform: lowercase;" title="<?php echo uh($translator->{"Insert the desired username for this account. This will later on be used for you to log onto your account."}); ?>" value="<?php echo $user->get('alias'); ?>" placeholder="<?php echo uh($translator->{"Account username"}); ?>" <?php if ($user->get('id') != 0) { echo 'disabled="disabled"'; } ?>/>
            
      <label for="name"><?php echo uh($translator->{'Contact person'}); ?> *</label>
      <input type="text" name="name" id="name" title="<?php echo uh($translator->{"Insert the name of the contact person of this account."}); ?>" value="<?php echo $user->get('name'); ?>" placeholder="<?php echo uh($translator->{"Name for contact person"}); ?>"/>
       
      <label for="phone3"><?php echo uh($translator->{'Contact Phone'}); ?> *</label>
      <input type="text" name="phone3" id="phone3" title="<?php echo uh($translator->{"Insert the phone number that we can reach the contact person for support about this account."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone'); ?>" placeholder="<?php echo uh($translator->{"eg: +46019294690"}); ?>"/>

      <label for="phone4"><?php echo uh($translator->{'Contact Phone 2'}); ?> *</label>
      <input type="text" name="phone4" id="phone4" title="<?php echo uh($translator->{"Insert the cell phone number of the contact person. for this account. This information will be visible to organizers of events that you attend to."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone2'); ?>" placeholder="<?php echo uh($translator->{"eg: +460707386668"}); ?>"/>
    </div>
    <div class="form_column" id="form_column6">
      <label for="email"><?php echo uh($translator->{'Restoration e-mail'}); ?> *</label>
      <input type="text" autocomplete="off" name="email" id="email" title="<?php echo uh($translator->{"Insert an email address that will be used when restoring your account. The e-mail address can only be used for one unique CFS-account."}); ?>" value="<?php echo $user->get('email'); ?>" placeholder="<?php echo uh($translator->{"Restoration e-mail address"}); ?>"/>

      <label for="password"><?php echo uh($translator->{'Password'}); ?> *</label>
      <input type="password" name="password" id="password" title="<?php echo uh($translator->{"Insert a password for this account. The password has to be Strong or Super strong."}); ?>" class="hasIndicator"/>

      <label for="password_repeat"><?php echo uh($translator->{'Password again (repeat to confirm)'}); ?> *</label>
      <input type="password" name="password_repeat" id="password_repeat" title="<?php echo uh($translator->{"Repeat the password that you entered in the previous field."}); ?>"/>

    </div>
    <br />
      <input type="button" name="previous" style="margin-left:2.5em;" class="previous bluebutton mediumbutton" value="<?php echo uh($translator->{'Previous'}); ?>" />
      <input type="button" name="cancel" class="cancelbutton redbutton mediumbutton" value="<?php echo uh($translator->{'Cancel'}); ?>" />
      <input type="submit" name="save" class="submit greenbutton mediumbutton" value="<?php echo uh($translator->{'Register'}); ?>" />    
  </fieldset>
<?php else: ?>

  <h1><?php echo $headline; ?></h1>
<?php if (userlevel() == 1 || userlevel() == 4): ?>
  <a href="user/uploadlogo"><input type="button" class="greenbutton mediumbutton" value="<?php echo uh($translator->{'Upload logotype'}); ?>"></input></a>
  <p class="error"><?php echo (isset($error)?$error:''); ?></p>
<?php endif; ?>
  
  <div class="form_column" id="form_column1">
    <h3><?php echo uh($translator->{'Company'}); ?></h3>

    <label for="contact_email"><?php echo uh($translator->{'Contact Email'}); ?> *</label>
    <input type="text" autocomplete="off" name="contact_email" id="contact_email" title="<?php echo uh($translator->{"Insert the email address of the contact person for this account. This information will be visible to organizers of events that you attend to."}); ?>" value="<?php echo $user->get('contact_email'); ?>" placeholder="<?php echo uh($translator->{"Contact Email address"}); ?>"/>
    
    <label for="orgnr"><?php echo uh($translator->{'Organization number'}); ?> *</label>
    <input type="text" name="orgnr" id="orgnr" title="<?php echo uh($translator->{"Insert the organization number of your organization."}); ?>" value="<?php echo $user->get('orgnr'); ?>" placeholder="<?php echo uh($translator->{"Organization number"}); ?>"/>
    
    <label for="company"><?php echo uh($translator->{'Company'}); ?> *</label>
    <input type="text" name="company" id="company" title="<?php echo uh($translator->{"Insert the name of your organization."}); ?>" value="<?php echo $user->get('company'); ?>" placeholder="<?php echo uh($translator->{"Name of organization"}); ?>"/>
    
    <label for="commodity"><?php echo uh($translator->{'Commodity'}); ?> *</label>
    <textarea rows="3" maxlength="200" style="width:20.833em; height:3.33em; resize:none;" name="commodity" title="<?php echo uh($translator->{"Insert the commodity that your organization represents."}); ?>" placeholder="<?php echo uh($translator->{"Organization commodity"}); ?>" id="commodity"><?php echo $user->get('commodity'); ?></textarea>
    
    <label for="address"><?php echo uh($translator->{'Address'}); ?> *</label>
    <input type="text" name="address" id="address" title="<?php echo uh($translator->{"Insert the address of your organization."}); ?>" value="<?php echo $user->get('address'); ?>" placeholder="<?php echo uh($translator->{"Organization address"}); ?>"/>
    
    <label for="zipcode"><?php echo uh($translator->{'Zip code'}); ?> *</label>
    <input type="text" name="zipcode" id="zipcode" title="<?php echo uh($translator->{"Insert the zip code of your organization."}); ?>" value="<?php echo $user->get('zipcode'); ?>" placeholder="<?php echo uh($translator->{"Organization zipcode"}); ?>"/>
    
    <label for="city"><?php echo uh($translator->{'City'}); ?> *</label>
    <input type="text" name="city" id="city" title="<?php echo uh($translator->{"Insert the city that your organization resides in."}); ?>" value="<?php echo $user->get('city'); ?>" placeholder="<?php echo uh($translator->{"Organization city"}); ?>"/>
    
    <label for="country"><?php echo uh($translator->{'Country'}); ?> *</label>
    <select name="country" id="country" title="<?php echo uh($translator->{"Select the country that your organization resides in."}); ?>" style="width:21.5em;">
    <?php foreach($country_list as $country) : ?>
      <?php if($country == $user->get('country')):?>
        <option value="<?php echo $country?>" selected><?php echo $country?></option>
      <?php else:?>
        <option value="<?php echo $country?>"><?php echo $country?></option>
      <?php endif?>
    <?php endforeach; ?>
    </select>

    <label for="phone1"><?php echo uh($translator->{'Phone 1'}); ?> *</label>
    <input type="text" name="phone1" id="phone1" class="phone-val" title="<?php echo uh($translator->{"Insert the phone number of your organization."}); ?>" value="<?php echo $user->get('phone1'); ?>" placeholder="<?php echo uh($translator->{"eg: +46019294690"}); ?>"/>
          
    <label for="phone2"><?php echo uh($translator->{'Phone 2'}); ?></label>
    <input type="text" name="phone2" id="phone2" class="phone-val" title="<?php echo uh($translator->{"Insert the second phone number of your organization."}); ?>" value="<?php echo $user->get('phone2'); ?>" placeholder="<?php echo uh($translator->{"eg: +46707386668"}); ?>"/>
          
    <label for="website"><?php echo uh($translator->{'Website'}); ?></label>
    <input type="text" name="website" id="website" title="<?php echo uh($translator->{"Insert the website address of your organization."}); ?>" value="<?php echo $user->get('website'); ?>" placeholder="<?php echo uh($translator->{"eg: www.example.com"}); ?>"/>
    
    <label for="facebook">Facebook</label>
    <input type="text" name="facebook" id="facebook" title="<?php echo uh($translator->{"Insert the Facebook address of your organization."}); ?>" value="<?php echo $user->get('facebook'); ?>" placeholder="<?php echo uh($translator->{"eg: https://facebook.com/facebookname"}); ?>"/>    

    <label for="twitter">Twitter</label>
    <input type="text" name="twitter" id="twitter" title="<?php echo uh($translator->{"Insert the Twitter address of your organization."}); ?>" value="<?php echo $user->get('twitter'); ?>" placeholder="<?php echo uh($translator->{"eg: https://twitter.com/twittername"}); ?>"/>

    <label for="google_plus">Google+</label>
    <input type="text" name="google_plus" id="google_plus" title="<?php echo uh($translator->{"Insert the Google+ address of your organization."}); ?>" value="<?php echo $user->get('google_plus'); ?>" placeholder="<?php echo uh($translator->{"eg: https://plus.google.com/+googleplusname"}); ?>"/>      

    <label for="youtube">Youtube</label>
    <input type="text" name="youtube" id="youtube" title="<?php echo uh($translator->{"Insert the Youtube url channel of your organization."}); ?>" value="<?php echo $user->get('youtube'); ?>" placeholder="<?php echo uh($translator->{"eg: https://youtube.com/youralias"}); ?>"/>

<?php if(userLevel() == 4): ?>
  <label for="#"><?php echo uh($translator->{'Account locked'}); ?></label>
  <input<?php echo ($user->get('locked') == 0) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="0" id="locked0"/><label for="locked0" class="inline-block"><?php echo uh($translator->{'No'}); ?></label>
  <input<?php echo ($user->get('locked') == 1) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="1" id="locked1"/><label for="locked1" class="inline-block"><?php echo uh($translator->{'Yes'}); ?></label>
<?php endif; ?>

  </div>
        
  <div class="form_column" id="form_column2">
        
    <h3><?php echo uh($translator->{'Billing address'}); ?></h3>
            
    <input type="checkbox" id="copy" style="margin:0;"/><label class="squaredFour" style="display:inline-block; margin-right: 0.5833em; vertical-align:inherit;" for="copy"></label><?php echo uh($translator->{'Copy from company details'}); ?>
  
    <label for="invoice_email"><?php echo uh($translator->{'E-mail'}); ?> *</label>
    <input type="text" autocomplete="off" name="invoice_email" id="invoice_email" title="<?php echo uh($translator->{"Insert the email address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_email'); ?>" placeholder="<?php echo uh($translator->{"Organization invoice email"}); ?>"/>
          
    <label for="invoice_company"><?php echo uh($translator->{'Company'}); ?> *</label>
    <input type="text" name="invoice_company" id="invoice_company" title="<?php echo uh($translator->{"Insert the organization name for the invoice."}); ?>" value="<?php echo $user->get('invoice_company'); ?>" placeholder="<?php echo uh($translator->{"Organization name for invoice"}); ?>"/>
          
    <label for="invoice_address"><?php echo uh($translator->{'Address'}); ?> *</label>
    <input type="text" name="invoice_address" id="invoice_address" title="<?php echo uh($translator->{"Insert the address at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_address'); ?>" placeholder="<?php echo uh($translator->{"Organization address for invoice"}); ?>"/>
          
    <label for="invoice_zipcode"><?php echo uh($translator->{'Zip code'}); ?> *</label>
    <input type="text" name="invoice_zipcode" id="invoice_zipcode" title="<?php echo uh($translator->{"Insert the zip code at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_zipcode'); ?>" placeholder="<?php echo uh($translator->{"Organization zipcode for invoice"}); ?>"/>
          
    <label for="invoice_city"><?php echo uh($translator->{'City'}); ?> *</label>
    <input type="text" name="invoice_city" id="invoice_city" title="<?php echo uh($translator->{"Insert the city at which we can reach the organization for invoice."}); ?>" value="<?php echo $user->get('invoice_city'); ?>" placeholder="<?php echo uh($translator->{"Organization city for invoice"}); ?>"/>
          
    <label for="invoice_country"><?php echo uh($translator->{'Country'}); ?> *</label>
    <select name="invoice_country" id="invoice_country" title="<?php echo uh($translator->{"Select the country that the invoice organization resides in."}); ?>" style="width:21.5em;">
    <?php foreach($country_list as $country) : ?>
      <?php if($country == $user->get('invoice_country')):?>
        <option value="<?php echo $country?>" selected><?php echo $country?></option>
      <?php else:?>
        <option value="<?php echo $country?>"><?php echo $country?></option>
      <?php endif?>
    <?php endforeach; ?>
    </select>

    <label for="presentation"<?php echo (userLevel()==0?' style="margin-top:6em;"':''); ?>><?php echo uh($translator->{'Presentation (this is what will be shown to your customers)'}); ?></label>
    <?php tiny_mce($path='js/tiny_mce/tiny_mce.js', 565, 'presentation'); ?>
    <textarea style="height:20em;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></textarea>
  <p>
    <input type="submit" name="save" value="<?php echo uh($translator->{'Save'}); ?>" class="greenbutton bigbutton margin_tb" />
  </p>

  </div>
          
  <div class="form_column" id="form_column3">

    <h3><?php echo uh($translator->{'Contact'}); ?></h3>
          
    <label for="alias"><?php echo uh($translator->{'Username'}); ?> *</label>
    <input type="text" autocomplete="off" name="alias" id="alias" onblur="this.value=forceLower(this.value);" style="text-transform: lowercase;" title="<?php echo uh($translator->{"Insert the desired username for this account. This will later on be used for you to log onto your account."}); ?>" value="<?php echo $user->get('alias'); ?>" placeholder="<?php echo uh($translator->{"Account username"}); ?>" <?php if ($user->get('id') != 0) { echo 'disabled="disabled"'; } ?>/>
          
    <label for="name"><?php echo uh($translator->{'Contact person'}); ?> *</label>
    <input type="text" name="name" id="name" title="<?php echo uh($translator->{"Insert the name of the contact person of this account."}); ?>" value="<?php echo $user->get('name'); ?>" placeholder="<?php echo uh($translator->{"Name for contact person"}); ?>"/>
     
    <label for="phone3"><?php echo uh($translator->{'Contact Phone'}); ?> *</label>
    <input type="text" name="phone3" id="phone3" title="<?php echo uh($translator->{"Insert the phone number that we can reach the contact person for support about this account."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone'); ?>" placeholder="<?php echo uh($translator->{"eg: +46019294690"}); ?>"/>

    <label for="phone4"><?php echo uh($translator->{'Contact Phone 2'}); ?> *</label>
    <input type="text" name="phone4" id="phone4" title="<?php echo uh($translator->{"Insert the cell phone number of the contact person. for this account. This information will be visible to organizers of events that you attend to."}); ?>" class="phone-val" value="<?php echo $user->get('contact_phone2'); ?>" placeholder="<?php echo uh($translator->{"eg: +460707386668"}); ?>"/>
    <br>
    <br>
    <label for="email" style="font-size:1.25em;"><?php echo uh($translator->{'Restoration e-mail'}); ?> *</label>
    <input type="text" autocomplete="off" name="email" id="email" title="<?php echo uh($translator->{"Insert an email address that will be used when restoring your account. The e-mail address can only be used for one unique CFS-account."}); ?>" value="<?php echo $user->get('email'); ?>" placeholder="<?php echo uh($translator->{"Restoration e-mail address"}); ?>"/>
    <br>
      <input type="checkbox" name="newsletter" value="1" <?php echo (strpos($user->get('newsletter'), 'accepted')) ? ' checked="checked"' : ''; ?> style="opacity:1 !important; margin: 1em 0 0 0 !important;"/><label style="max-width:100% !important; padding: 1em 0 0 2em !important;" for="newsletter"><?php echo uh($translator->{'I want to subscribe to newsletters from Chartbooking.'}); ?></label>

<?php if(userLevel() == 0): ?>
    <label for="password"><?php echo uh($translator->{'Password'}); ?> *</label>
    <input type="password" name="password" id="password" title="<?php echo uh($translator->{"Insert a password for this account. The password has to be Strong or Super strong."}); ?>" class="hasIndicator"/>

    <label for="password_repeat"><?php echo uh($translator->{'Password again (repeat to confirm)'}); ?> *</label>
    <input type="password" name="password_repeat" id="password_repeat" title="<?php echo uh($translator->{"Repeat the password that you entered in the previous field."}); ?>"/>

    </p>
<?php endif; ?>
  </div>
<?php endif; ?>
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
      <a href="administrator/overview/<?php echo $fair['id'] ?>"><?php echo uh($translator->{'Administrators'}); ?></a>
      <a href="exhibitor/exhibitors/<?php echo $fair['id'] ?>"><?php echo uh($translator->{'Exhibitors'}); ?></a>
    </p>

    <?php echo '<strong>'.uh($translator->{'Event name'}); ?></strong> <?php echo $fair['name']; ?><br />
    <?php echo '<strong>'.uh($translator->{'Status'}).':</strong> '.uh($app); ?><br />
    <?php echo '<strong>'.uh($translator->{'Website'}).'</strong>: <a href="'.$fair['url'].'">'.$fair['url'].'</a><br />'; ?>
    <?php echo '<strong>'.uh($translator->{'Maximum stand spaces'}).'</strong>: '.$fair['max_positions']; ?><br />
    <?php echo '<strong>'.uh($translator->{'Number of visitors'}).'</strong>: '.$fair['page_views']; ?><br />
    <?php echo '<strong>'.uh($translator->{'Number of occupied spaces'}).'</strong>: '.$fair['occupied_spaces']; ?><br />
    <?php echo '<strong>'.uh($translator->{'Number of free spaces'}).'</strong>: '.$fair['free_spaces']; ?><br />
    <?php echo '<strong>'.uh($translator->{'Creation time'}).'</strong>: '; if ($fair['creation_time']) echo date('d-m-Y H:i:s', $fair['creation_time']); ?><br />
    <?php echo '<strong>'.uh($translator->{'Opening time'}).'</strong>: '; echo date('d-m-Y H:i:s', $fair['event_start']); ?><br />
    <?php echo '<strong>'.uh($translator->{'Closing time'}).'</strong>: '; echo date('d-m-Y H:i:s', $fair['event_stop']); ?><br />
    <?php echo '<strong>'.uh($translator->{'Lock time'}).'</strong>: '; if ($fair['closing_time']) echo date('d-m-Y H:i:s', $fair['closing_time']); ?><br />
	</div>
<?php
    endforeach;
  endif;
?>
