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

<h1><?php echo $headline; ?></h1>

<div class="form_column">
	<h3><?php echo $company_section; ?></h3>

	<label for="orgnr"><?php echo $orgnr_label; ?> *</label>
	<div type="text" name="orgnr" id="orgnr"  ><?php echo $user->get('orgnr'); ?></div>

	<label for="company"><?php echo $company_label; ?> *</label>
	<div type="text" name="company" id="company"  ><?php echo $user->get('company'); ?></div>

	<label for="commodity"><?php echo $commodity_label; ?></label>
	<div rows="3" style="width:250px;" name="commodity" id="commodity"><?php echo $user->get('commodity'); ?></div>

	<label for="address"><?php echo $address_label; ?> *</label>
	<div type="text" name="address" id="address"  ><?php echo $user->get('address'); ?></div>

	<label for="zipcode"><?php echo $zipcode_label; ?> *</label>
	<div type="text" name="zipcode" id="zipcode"  ><?php echo $user->get('zipcode'); ?></div>

	<label for="city"><?php echo $city_label; ?> *</label>
	<div type="text" name="city" id="city"  ><?php echo $user->get('city'); ?></div>

	<label for="country"><?php echo $country_label; ?> *</label>
	<div name="country" id="country" style="width:258px;">
		<?php echo $user->get('country');?>&nbsp;
	</div>

	<label for="phone1"><?php echo $phone1_label; ?> *</label>
	<div type="text" name="phone1" id="phone1"  ><?php echo $user->get('phone1'); ?></div>

	<label for="phone2"><?php echo $phone2_label; ?></label>
	<div type="text" name="phone2" id="phone2"  ><?php echo $user->get('phone2'); ?></div>

	<label for="fax"><?php echo $fax_label; ?></label>
	<div type="text" name="fax" id="fax"  ><?php echo $user->get('fax'); ?></div>

	<label for="email"><?php echo $email_label; ?> *</label>
	<div type="text" name="email" id="email"  ><?php echo $user->get('email'); ?></div>

	<label for="website"><?php echo $website_label; ?></label>
	<div type="text" name="website" id="website"  ><?php echo $user->get('website'); ?></div>
</div>

<div class="form_column">
	<h3><?php echo $invoice_section; ?></h3>
	<label for="invoice_company"><?php echo $invoice_company_label; ?> *</label>
	<div type="text" name="invoice_company" id="invoice_company"  ><?php echo $user->get('invoice_company'); ?></div>

	<label for="invoice_address"><?php echo $invoice_address_label; ?> *</label>
	<div type="text" name="invoice_address" id="invoice_address"  ><?php echo $user->get('invoice_address'); ?></div>

	<label for="invoice_zipcode"><?php echo $invoice_zipcode_label; ?> *</label>
	<div type="text" name="invoice_zipcode" id="invoice_zipcode"  ><?php echo $user->get('invoice_zipcode'); ?></div>

	<label for="invoice_city"><?php echo $invoice_city_label; ?> *</label>
	<div type="text" name="invoice_city" id="invoice_city"  ><?php echo $user->get('invoice_city'); ?></div>

	<label for="invoice_country"><?php echo $country_label; ?> *</label>
	<div name="invoice_country" id="invoice_country" style="width:258px;">
		<?php echo $user->get('invoice_country');?>&nbsp;
	</div>

	<label for="invoice_email"><?php echo $invoice_email_label; ?> *</label>
	<div type="text" name="invoice_email" id="invoice_email"  ><?php echo $user->get('invoice_email'); ?></div>

	<label for="presentation"><?php echo $presentation_label; ?></label>
	<div style="width: 800px; max-height: 350px; overflow-x: auto; overflow-y: auto; max-width: 700px;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?></div>
</div>

<div class="form_column">
	<h3><?php echo $contact_section; ?></h3>
	<label for="alias"><?php echo $alias_label; ?> *</label>
	<div type="text" name="alias" id="alias"   disabled="disabled"><?php echo $user->get('alias'); ?></div>

	<label for="name"><?php echo $contact_label; ?> *</label>
	<div type="text" name="name" id="name"  ><?php echo $user->get('name'); ?></div>

	<label for="phone3"><?php echo $phone3_label; ?> *</label>
	<div type="text" name="phone3" id="phone3"  ><?php echo $user->get('contact_phone'); ?></div>

	<label for="phone4"><?php echo $phone4_label; ?></label>
	<div type="text" name="phone4" id="phone4"  ><?php echo $user->get('contact_phone2'); ?></div>

	<label for="contact_email"><?php echo $contact_email ?> *</label>
	<div type="text" name="contact_email" id="contact_email"  ><?php echo $user->get('contact_email'); ?></div>
</div>

<?php if(userLevel() > 3) :?>
	<label for="customid"><?php echo $customer_nr_label;?></label>
	<input type="text" name="customid" id="customid" value="<?php echo $user->get('customer_nr');?>" />
	<button onclick="saveCustomId()" type="button"><?php echo $save_customer_id?></button>
<?php endif;?>

<h3><?php echo $bookings_section; ?></h3>

<?php if (count($positions) > 0): ?>
<table class="std_table use-scrolltable" id="profileBookings">
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
			<td><a target="_blank" href="/mapTool/map/<?php echo $pos['fair']; ?>/<?php echo $pos['id']; ?>/<?php echo $pos['map']; ?>"><?php echo $pos['fair_map_name']; ?> </a></td>
			<td><?php echo $pos['name']; ?></td>
			<td class="center"><?php echo $pos['area']; ?></td>
			<td class="center"><?php echo $pos['company']; ?></td>
			<td class="center"><?php echo $pos['commodity']; ?></td>
			<td><?php echo ($pos['booking_time'] != '') ? date('d-m-Y H:i:s', $pos['booking_time']) : ''; ?></td>
			<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
				<a href="administrator/arrangerMessage/<?php echo (isset($pos['preliminary']) ? 'preliminary' : 'exhibitor') . '/' . $pos['exhibitor_id']; ?>" class="open-arranger-message">
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

<?php /*
<form action="exhibitor/profile/<?php echo $user->get('id'); ?>" method="post">
	<h3><?php echo $ban_section_header ?></h3>
	<label for="ban_msg"><?php echo $ban_msg_label ?></label>
	<textarea name="ban_msg" id="ban_msg"></textarea>
	<p><!--input type="submit" name="ban_save" value="<?php echo $ban_save ?>"/-->
			<div type="submit" name="ban_save"  ><?php echo $ban_save ?></div></p>
</form>
*/ ?>