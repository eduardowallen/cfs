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
	window.print();
</script>
<style>
#new_header_show {
	display: none !important;
}
h1{
	padding:1.2em 1.2em 1em;
	color: #FFF;
	margin-left: -1.2em;
	margin-top: -3em;
	width:90%;
	word-wrap:break-word;
	overflow:hidden;
}
#content {
	max-width: 120em;
}
</style>
<div class="form_column">
	<h3><?php echo $company_section; ?></h3>

	<label for="orgnr"><?php echo $orgnr_label; ?></label>
	<div type="text" name="orgnr" id="orgnr"  ><?php echo $user->get('orgnr'); ?></div>

	<label for="company"><?php echo $company_label; ?></label>
	<div type="text" name="company" id="company"  ><?php echo $user->get('company'); ?></div>

	<label for="commodity"><?php echo $commodity_label; ?></label>
	<div rows="3" style="width:250px;" name="commodity" id="commodity"><?php echo $user->get('commodity'); ?></div>

	<label for="address"><?php echo $address_label; ?></label>
	<div type="text" name="address" id="address"  ><?php echo $user->get('address'); ?></div>

	<label for="zipcode"><?php echo $zipcode_label; ?></label>
	<div type="text" name="zipcode" id="zipcode"  ><?php echo $user->get('zipcode'); ?></div>

	<label for="city"><?php echo $city_label; ?></label>
	<div type="text" name="city" id="city"  ><?php echo $user->get('city'); ?></div>

	<label for="country"><?php echo $country_label; ?></label>
	<div name="country" id="country" style="width:258px;">
		<?php echo $user->get('country');?>&nbsp;
	</div>

	<label for="phone1"><?php echo $phone1_label; ?></label>
	<div type="text" name="phone1" id="phone1"  ><?php echo $user->get('phone1'); ?></div>

	<label for="phone2"><?php echo $phone2_label; ?></label>
	<div type="text" name="phone2" id="phone2"  ><?php echo $user->get('phone2'); ?></div>

	<label for="fax"><?php echo $fax_label; ?></label>
	<div type="text" name="fax" id="fax"  ><?php echo $user->get('fax'); ?></div>

	<label for="email"><?php echo $email_label; ?></label>
	<div type="text" name="email" id="email"  ><?php echo $user->get('email'); ?></div>

	<label for="website"><?php echo $website_label; ?></label>
	<div type="text" name="website" id="website"  ><?php echo $user->get('website'); ?></div>
</div>

<div class="form_column">
	<h3><?php echo $invoice_section; ?></h3>
	<label for="invoice_company"><?php echo $invoice_company_label; ?></label>
	<div type="text" name="invoice_company" id="invoice_company"  ><?php echo $user->get('invoice_company'); ?></div>

	<label for="invoice_address"><?php echo $invoice_address_label; ?></label>
	<div type="text" name="invoice_address" id="invoice_address"  ><?php echo $user->get('invoice_address'); ?></div>

	<label for="invoice_zipcode"><?php echo $invoice_zipcode_label; ?></label>
	<div type="text" name="invoice_zipcode" id="invoice_zipcode"  ><?php echo $user->get('invoice_zipcode'); ?></div>

	<label for="invoice_city"><?php echo $invoice_city_label; ?></label>
	<div type="text" name="invoice_city" id="invoice_city"  ><?php echo $user->get('invoice_city'); ?></div>

	<label for="invoice_country"><?php echo $country_label; ?></label>
	<div name="invoice_country" id="invoice_country" style="width:258px;">
		<?php echo $user->get('invoice_country');?>&nbsp;
	</div>

	<label for="invoice_email"><?php echo $invoice_email_label; ?></label>
	<div type="text" name="invoice_email" id="invoice_email"  ><?php echo $user->get('invoice_email'); ?></div>

	<label for="presentation"><?php echo $presentation_label; ?></label>
	<div style="width: 500px; max-height: 350px; overflow-x: auto; overflow-y: auto;" name="presentation" id="presentation" class="presentation">
		<?php foreach(glob(ROOT.'public/images/exhibitors/'.$user->get('id').'/*') as $filename) : ?>
			<img src="<?php echo('../images/exhibitors/'.$user->get('id').'/'. basename($filename) . "\n"); ?>" id="profile_presentation_img" />
		<?php endforeach; ?><?php echo $user->get('presentation'); ?></div>
</div>

<div class="form_column">
	<h3><?php echo $contact_section; ?></h3>
<?php if(userLevel() == 4) :?>
	<label for="alias"><?php echo $alias_label; ?></label>
	<div type="text" name="alias" id="alias"   disabled="disabled"><?php echo $user->get('alias'); ?></div>
<?php endif;?>
	<label for="name"><?php echo $contact_label; ?></label>
	<div type="text" name="name" id="name"  ><?php echo $user->get('name'); ?></div>

	<label for="phone3"><?php echo $phone3_label; ?></label>
	<div type="text" name="phone3" id="phone3"  ><?php echo $user->get('contact_phone'); ?></div>

	<label for="phone4"><?php echo $phone4_label; ?></label>
	<div type="text" name="phone4" id="phone4"  ><?php echo $user->get('contact_phone2'); ?></div>

	<label for="contact_email"><?php echo $contact_email ?></label>
	<div type="text" name="contact_email" id="contact_email"  ><?php echo $user->get('contact_email'); ?></div>
</div>

<h3 style="margin-top: 20px;"><?php echo $bookings_samefair_section; ?></h3>

<?php if (count($same_fair_positions) > 0): ?>
<table class="std_table" id="profileBookingsCurrentFair">
	<thead>
		<tr>
			<th><?php echo $tr_map; ?></th>
			<th><?php echo $tr_pos; ?></th>
			<th><?php echo $tr_area; ?></th>
			<th><?php echo $tr_field; ?></th>
			<th><?php echo $tr_time; ?></th>
			<th><?php echo $tr_message; ?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach($same_fair_positions as $pos): ?>
		<tr>
			<td><?php echo $pos['fair_map_name']; ?></td>
			<td><?php echo $pos['name']; ?></td>
			<td class="center"><?php echo $pos['area']; ?></td>
			<td class="center"><?php echo $pos['commodity']; ?></td>
			<td><?php echo ($pos['booking_time'] != '') ? date('d-m-Y H:i:s', $pos['booking_time']) : ''; ?></td>
			<td>
<?php if (strlen($pos['arranger_message']) > 0): ?>
				<?php echo $pos['arranger_message']; ?>
<?php else: ?>
			<?php echo('-'); ?>
<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
</table>

<?php else: ?>
<p><?php echo $no_bookings_label; ?></p>
<?php endif; ?>