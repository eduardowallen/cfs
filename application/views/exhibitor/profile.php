<h1><?php echo $headline; ?></h1>
<form action="user/accountSettings" method="post">

	<div class="form_column">
	<h3><?php echo $company_section; ?></h3>

	<label for="orgnr"><?php echo $orgnr_label; ?> *</label>
	<input type="text" name="orgnr" id="orgnr" value="<?php echo $user->get('orgnr'); ?>"/>

	<label for="company"><?php echo $company_label; ?> *</label>
	<input type="text" name="company" id="company" value="<?php echo $user->get('company'); ?>"/>

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
	<input type="text" disabled="disabled" name="email" id="email" value="<?php echo $user->get('email'); ?>"/>

	<label for="website"><?php echo $website_label; ?></label>
	<input type="text" name="website" id="website" value="<?php echo $user->get('website'); ?>"/>
	</div>

	<div class="form_column">

	<h3><?php echo $invoice_section; ?></h3>

	<label for="invoice_address"><?php echo $invoice_address_label; ?></label>
	<input type="text" name="invoice_address" id="invoice_address" value="<?php echo $user->get('invoice_address'); ?>"/>

	<label for="invoice_zipcode"><?php echo $invoice_zipcode_label; ?></label>
	<input type="text" name="invoice_zipcode" id="invoice_zipcode" value="<?php echo $user->get('invoice_zipcode'); ?>"/>

	<label for="invoice_city"><?php echo $invoice_city_label; ?></label>
	<input type="text" name="invoice_city" id="invoice_city" value="<?php echo $user->get('invoice_city'); ?>"/>

	<label for="invoice_email"><?php echo $invoice_email_label; ?></label>
	<input type="text" name="invoice_email" id="invoice_email" value="<?php echo $user->get('invoice_email'); ?>"/>

	<h3><?php echo $contact_section; ?></h3>

	<label for="name"><?php echo $contact_label; ?> *</label>
	<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>

	<h3><?php echo $presentation_section; ?></h3>

	<label for="presentation"><?php echo $presentation_label; ?></label>
	<textarea name="presentation" id="presentation"><?php echo $user->get('presentation'); ?></textarea>
	
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
	</div>

</form>
<h3><?php echo $bookings_section; ?></h3>
<table class="std_table">
<thead>
	<tr>
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
		<td><?php echo $pos->get('name'); ?></td>
		<td class="center"><?php echo $pos->get('area'); ?></td>
		<td class="center"><?php echo $pos->get('company'); ?></td>
		<td class="center"><?php echo $pos->get('commodity'); ?></td>
		<td><?php echo ($pos->get('booking_time') != '') ? date('d-m-Y H:i:s', $pos->get('booking_time')) : ''; ?></td>
		<td><?php echo $pos->get('arranger_message'); ?></td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php  echo '';/*
<form action="exhibitor/profile/<?php echo $user->get('id'); ?>" method="post">
	<h3><?php echo $ban_section_header ?></h3>
	<label for="ban_msg"><?php echo $ban_msg_label ?></label>
	<textarea name="ban_msg" id="ban_msg"></textarea>
	<p><input type="submit" name="ban_save" value="<?php echo $ban_save ?>"/></p>
</form>
*/ ?>
