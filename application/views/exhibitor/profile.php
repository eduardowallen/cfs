<?php
  $country_list = array("Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia,  The","Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea,  North","Korea,  South","Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");
?>

<script>
	function saveCustomId(){
		// Hämta kundnummer
		var customerId = $('#customid').val();

		$.ajax({
			url: 'exhibitor/saveCustomerId/<?php echo $user->get('id')?>/'+customerId,
			type: 'GET'
		}).success(function(responseData){
		
			alert(responseData);
		});
	}
</script>

<div id="arranger_message_popup" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />

	<h3><?php echo $tr_message; ?></h3>

	<p id="arranger_message_text"></p>

	<p class="center">
		<a href="#" class="link-button close-popup"><?php echo $ok_label; ?></a>
	</p>
</div>

<h1><?php echo $headline; ?></h1>
<form action="user/accountSettings" method="post">
	<div class="form_column">
		<?php if (false)://userLevel() == 2): // This code was showing the wrong, minified version of profile for administrators?>
      <label for="alias"><?php echo $alias_label; ?> *</label>
      <!--input type="text" name="alias" id="alias" value="<?php echo $user->get('alias'); ?>" disabled="disabled"/-->
      <div type="text" name="alias" id="alias"   disabled="disabled"><?php echo $user->get('alias'); ?></div>

      <label for="name"><?php echo $contact_label; ?> *</label>
      <!--input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/-->
      <div type="text" name="name" id="name"  ><?php echo $user->get('name'); ?></div>

      <label for="phone1"><?php echo $phone1_label; ?> *</label>
      <!--input type="text" name="phone1" id="phone1" value="<?php echo $user->get('phone1'); ?>"/-->
      <div type="text" name="phone1" id="phone1"  ><?php echo $user->get('phone1'); ?></div>
    
      <label for="phone2"><?php echo $phone2_label; ?></label>
      <!--input type="text" name="phone2" id="phone2" value="<?php echo $user->get('phone2'); ?>"/-->
			<div type="text" name="phone2" id="phone2"  ><?php echo $user->get('phone2'); ?></div>

      <label for="phone3"><?php echo $phone3_label; ?></label>
      <!--input type="text" name="phone3" id="phone3" value="<?php echo $user->get('contact_phone'); ?>"/-->
			<div type="text" name="phone3" id="phone3"  ><?php echo $user->get('contact_phone'); ?></div>
      
      <label for="email"><?php echo $email_label; ?> *</label>
      <!--input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/-->
			<div type="text" name="email" id="email"  ><?php echo $user->get('email'); ?></div>

		<?php else:?>
      <h3><?php echo $company_section; ?></h3>
    
      <label for="orgnr"><?php echo $orgnr_label; ?> *</label>
      <!--input type="text" name="orgnr" id="orgnr" value="<?php echo $user->get('orgnr'); ?>"/-->
			<div type="text" name="orgnr" id="orgnr"  ><?php echo $user->get('orgnr'); ?></div>
    
      <label for="company"><?php echo $company_label; ?> *</label>
      <!--input type="text" name="company" id="company" value="<?php echo $user->get('company'); ?>"/-->
			<div type="text" name="company" id="company"  ><?php echo $user->get('company'); ?></div>
    
      <label for="commodity"><?php echo $commodity_label; ?></label>
      <!--textarea rows="3" style="width:250px; height:40px; resize:none;" name="commodity" id="commodity"><?php echo $user->get('commodity'); ?></textarea-->
			<div rows="3" style="width:250px;" name="commodity" id="commodity"><?php echo $user->get('commodity'); ?></div>
    
      <!--<label for="category"><?php //echo $category_label; ?> *</label>
      <select name="category" id="category">
      <?php //echo makeOptions($user->db, 'exhibitor_category', 0, 'fair='.$_SESSION['user_fair']); ?>
      </select>-->
    
      <label for="address"><?php echo $address_label; ?> *</label>
      <!--input type="text" name="address" id="address" value="<?php echo $user->get('address'); ?>"/-->
			<div type="text" name="address" id="address"  ><?php echo $user->get('address'); ?></div>
    
      <label for="zipcode"><?php echo $zipcode_label; ?> *</label>
      <!--input type="text" name="zipcode" id="zipcode" value="<?php echo $user->get('zipcode'); ?>"/-->
			<div type="text" name="zipcode" id="zipcode"  ><?php echo $user->get('zipcode'); ?></div>
    
      <label for="city"><?php echo $city_label; ?> *</label>
      <!--input type="text" name="city" id="city" value="<?php echo $user->get('city'); ?>"/-->
			<div type="text" name="city" id="city"  ><?php echo $user->get('city'); ?></div>
    
      <label for="country"><?php echo $country_label; ?> *</label>
      <!--select name="country" id="country" style="width:258px;">
      <?php foreach($country_list as $country) : ?>
        <?php if($country == $user->get('country')):?>
          <option value="<?php echo $country?>" selected><?php echo $country?></option>
        <?php else:?>
          <option value="<?php echo $country?>"><?php echo $country?></option>
        <?php endif?>
      <?php endforeach; ?>
      </select-->
      <div name="country" id="country" style="width:258px;">
        <?php echo $user->get('country');?>&nbsp;
      </div>

      <label for="phone1"><?php echo $phone1_label; ?> *</label>
      <!--input type="text" name="phone1" id="phone1" value="<?php echo $user->get('phone1'); ?>"/-->
			<div type="text" name="phone1" id="phone1"  ><?php echo $user->get('phone1'); ?></div>
    
      <label for="phone2"><?php echo $phone2_label; ?></label>
      <!--input type="text" name="phone2" id="phone2" value="<?php echo $user->get('phone2'); ?>"/-->
			<div type="text" name="phone2" id="phone2"  ><?php echo $user->get('phone2'); ?></div>
    
      <label for="fax"><?php echo $fax_label; ?></label>
      <!--input type="text" name="fax" id="fax" value="<?php echo $user->get('fax'); ?>"/-->
			<div type="text" name="fax" id="fax"  ><?php echo $user->get('fax'); ?></div>
    
      <label for="email"><?php echo $email_label; ?> *</label>
      <!--input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/-->
			<div type="text" name="email" id="email"  ><?php echo $user->get('email'); ?></div>
    
      <label for="website"><?php echo $website_label; ?></label>
      <!--input type="text" name="website" id="website" value="<?php echo $user->get('website'); ?>"/-->
			<div type="text" name="website" id="website"  ><?php echo $user->get('website'); ?></div>
    </div>

      
    <div class="form_column">
      <h3><?php echo $invoice_section; ?></h3>
        <!--input type="checkbox" id="copy"/><label class="inline-block" for="copy"><?php echo $copy_label ?></label-->
        <label for="invoice_company"><?php echo $invoice_company_label; ?> *</label>
        <!--input type="text" name="invoice_company" id="invoice_company" value="<?php echo $user->get('invoice_company'); ?>"/-->
			<div type="text" name="invoice_company" id="invoice_company"  ><?php echo $user->get('invoice_company'); ?></div>

        <label for="invoice_address"><?php echo $invoice_address_label; ?> *</label>
        <!--input type="text" name="invoice_address" id="invoice_address" value="<?php echo $user->get('invoice_address'); ?>"/-->
			<div type="text" name="invoice_address" id="invoice_address"  ><?php echo $user->get('invoice_address'); ?></div>

        <label for="invoice_zipcode"><?php echo $invoice_zipcode_label; ?> *</label>
        <!--input type="text" name="invoice_zipcode" id="invoice_zipcode" value="<?php echo $user->get('invoice_zipcode'); ?>"/-->
			<div type="text" name="invoice_zipcode" id="invoice_zipcode"  ><?php echo $user->get('invoice_zipcode'); ?></div>

        <label for="invoice_city"><?php echo $invoice_city_label; ?> *</label>
        <!--input type="text" name="invoice_city" id="invoice_city" value="<?php echo $user->get('invoice_city'); ?>"/-->
			<div type="text" name="invoice_city" id="invoice_city"  ><?php echo $user->get('invoice_city'); ?></div>

        <label for="invoice_country"><?php echo $country_label; ?> *</label>
        <!--select name="invoice_country" id="invoice_country" style="width:258px;">
        <?php foreach($country_list as $country) : ?>
          <?php if($country == $user->get('invoice_country')):?>
            <option value="<?php echo $country?>" selected><?php echo $country?></option>
          <?php else:?>
            <option value="<?php echo $country?>"><?php echo $country?></option>
          <?php endif?>
        <?php endforeach; ?>
        </select-->
        <div name="invoice_country" id="invoice_country" style="width:258px;">
          <?php echo $user->get('invoice_country');?>&nbsp;
        </div>

        <label for="invoice_email"><?php echo $invoice_email_label; ?> *</label>
        <!--input type="text" name="invoice_email" id="invoice_email" value="<?php echo $user->get('invoice_email'); ?>"/-->
			<div type="text" name="invoice_email" id="invoice_email"  ><?php echo $user->get('invoice_email'); ?></div>
    
        <label for="presentation"><?php echo $presentation_label; ?></label>
        <?php tiny_mce($path='js/tiny_mce/tiny_mce.js', 565, 'presentation')?> 
        <!--textarea style="height:355px;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></textarea-->
			<div style="width: 565px; max-height: 1000px; overflow-x: auto;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></div>
      </div>
      

    <div class="form_column">
      <h3><?php echo $contact_section; ?></h3>
      <label for="alias"><?php echo $alias_label; ?> *</label>
      <!--input type="text" name="alias" id="alias" value="<?php echo $user->get('alias'); ?>" disabled="disabled"/-->
			<div type="text" name="alias" id="alias"   disabled="disabled"><?php echo $user->get('alias'); ?></div>

      <label for="name"><?php echo $contact_label; ?> *</label>
      <!--input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/-->
			<div type="text" name="name" id="name"  ><?php echo $user->get('name'); ?></div>

      <label for="phone3"><?php echo $phone3_label; ?> *</label>
      <!--input type="text" name="phone3" id="phone3" value="<?php echo $user->get('contact_phone'); ?>"/-->
			<div type="text" name="phone3" id="phone3"  ><?php echo $user->get('contact_phone'); ?></div>

      <label for="phone4"><?php echo $phone4_label; ?></label>
      <!--input type="text" name="phone4" id="phone4" value="<?php echo $user->get('contact_phone2'); ?>"/-->
			<div type="text" name="phone4" id="phone4"  ><?php echo $user->get('contact_phone2'); ?></div>

      <label for="contact_email"><?php echo $contact_email ?> *</label>
      <!--input type="text" name="contact_email" id="contact_email" value="<?php echo $user->get('contact_email'); ?>"/-->
			<div type="text" name="contact_email" id="contact_email"  ><?php echo $user->get('contact_email'); ?></div>

    </div>
	<?php endif; ?>	
	<?php if(userLevel() > 3) :?>
		<label for="customid"><?php echo $customer_nr_label;?></label>
		<input type="text" name="customid" id="customid" value="<?php echo $user->get('customer_nr');?>" />
		<button onclick="saveCustomId()" type="button"><?php echo $save_customer_id?></button>
	<?php endif;?>
</form>

<h3><?php echo $bookings_section; ?></h3>
<?php if (count($positions) > 0): ?>
<table class="std_table">
<thead>
	<tr>
		<th><?php echo $tr_event; ?></th>
		<th><?php echo $tr_pos; ?></th>
		<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
		<th><?php echo $tr_booker; ?></th>
		<th><?php echo $tr_field; ?></th>
		<th><?php echo $tr_time; ?></th>
		<th><?php echo $tr_message; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($positions as $pos): ?>
	<tr>
		<td><a href="/mapTool/map/<?php echo $pos->map->get('fair'); ?>/<?php echo $pos->get('id'); ?>/<?php echo $pos->map->get('id'); ?>"><?php echo $pos->map->get('name'); ?></a></td>
		<td><?php echo $pos->get('name'); ?></td>
		<td class="center"><?php echo $pos->get('area'); ?></td>
		<td class="center"><?php echo $pos->get('company'); ?></td>
		<td class="center"><?php echo $pos->get('commodity'); ?></td>
		<td><?php echo ($pos->get('booking_time') != '') ? date('d-m-Y H:i:s', $pos->get('booking_time')) : ''; ?></td>
		<td class="center" title="<?php echo htmlspecialchars($pos->get('arranger_message')); ?>">
<?php if (strlen($pos->get('arranger_message')) > 0): ?>
						<a href="administrator/arrangerMessage/<?php echo ($pos->get('preliminary') ? 'preliminary' : 'exhibitor') . '/' . $pos->get('exhibitor_id'); ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p><?php echo $no_bookings_label; ?></p>
<?php endif; ?>
<?php  echo '';/*
<form action="exhibitor/profile/<?php echo $user->get('id'); ?>" method="post">
	<h3><?php echo $ban_section_header ?></h3>
	<label for="ban_msg"><?php echo $ban_msg_label ?></label>
	<textarea name="ban_msg" id="ban_msg"></textarea>
	<p><!--input type="submit" name="ban_save" value="<?php echo $ban_save ?>"/-->
			<div type="submit" name="ban_save"  ><?php echo $ban_save ?></div></p>
</form>
*/ ?>
