<style>

#exhibitor-profile .scrolltable-wrap{max-height:20em !important;}
/*
h1 {
	padding:1.2em 1.2em 1em;
	color: #FFF;
	margin-left: -1.2em;
	margin-top: -3em;
	width:90%;
	word-wrap:break-word;
	overflow:hidden;
}*/

</style>
<h3 class="standSpaceName" style="margin-left:-0.7em;"><?php echo $headline; ?></h3>

<div role="tabpanel" id="exhibitor-profile">
	  <!-- Nav tabs -->
  <ul class="nav nav-tabs" id="exhibitor-profile" role="tablist" style="display:inline-block;">
    <li role="presentation"><a href="javascript:void(0)" id="company_info" class="tabs-tab" aria-controls="home" role="tab" data-toggle="tab"><?php echo uh($translator->{'Company information'}); ?></a></li>
    <li role="presentation"><a href="javascript:void(0)" id="invoice_info" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><?php echo uh($translator->{'Invoice address'}); ?></a></li>
    <li role="presentation"><a href="javascript:void(0)" id="profile_presentation" class="tabs-tab" aria-controls="messages" role="tab" data-toggle="tab"><?php echo uh($translator->{'Company presentation'}); ?></a></li>
    <li role="presentation"><a href="javascript:void(0)" id="bookings_this_event" class="tabs-tab" aria-controls="messages" role="tab" data-toggle="tab"><?php echo uh($translator->{'Bookings on this event'}); ?></a></li>
    <li role="presentation"><a href="javascript:void(0)" id="bookings_your_events" class="tabs-tab" aria-controls="messages" role="tab" data-toggle="tab"><?php echo uh($translator->{'Bookings on your other events'}); ?></a></li>
  </ul>
  <a href="exhibitor/printProfile/<?php echo $user->get('id'); ?>" target="_blank" id="printProfileLink"><?php echo uh($translator->{'Print'}); ?> <img src="images/icons/print.png" id="print_img"/></a>

	  <!-- Tab panes -->
	  <div class="tab-content" id="exhibitor-profile">
	    <div role="tabpanel" class="tab-pane active" id="company_info">
	<script>

	$(document).ready(function() {
	    // go to the latest tab, if it exists:
	    var lastProfileTab = localStorage.getItem('lastProfileTab');
	    if (lastProfileTab) {
			var selected = lastProfileTab;
			var div = 'div#' + selected;
			$('#exhibitor-profile .tab-div').css('display', 'none');
			$('#exhibitor-profile li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			$('[id="' + lastProfileTab + '"]').tab('show');

	    } else {
			var selected = 'company_info';
			var div = 'div#' + selected;
			$('#exhibitor-profile .tab-div').css('display', 'none');
			$('#exhibitor-profile li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
	    }
	});
		$('#exhibitor-profile .tabs-tab').on("click", function() {
			var selected = $(this).attr('id');
			var div = 'div#' + selected;
			$('#exhibitor-profile .tab-div').css('display', 'none');
			$('#exhibitor-profile li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			if (!$(div + ' table').hasClass('scrolltable')) {
				useScrolltable($(div + ' table'));
			}
			$(div + ' table').floatThead('getSizingRow');
			$(div + ' table').floatThead('reflow');
			localStorage.setItem('lastProfileTab', $(this).attr('id'));
		});

	</script>	    	
	    	<div id="company_info" style="display:none" class="tab-div tab-div-hidden">

			<h3><?php echo $company_section; ?></h3>

			<div class="form_column">

				<label for="orgnr"><?php echo $orgnr_label; ?></label>
				<div type="text" name="orgnr" id="orgnr"  ><?php echo $user->get('orgnr'); ?></div>

				<label for="company"><?php echo $company_label; ?></label>
				<div type="text" name="company" id="company"  ><?php echo $user->get('company'); ?></div>

				<label for="commodity"><?php echo $commodity_label; ?></label>
				<div rows="3" style="width:20.833em;" name="commodity" id="commodity"><?php echo $user->get('commodity'); ?></div>

				<label for="address"><?php echo $address_label; ?></label>
				<div type="text" name="address" id="address"  ><?php echo $user->get('address'); ?></div>

				<label for="zipcode"><?php echo $zipcode_label; ?></label>
				<div type="text" name="zipcode" id="zipcode"  ><?php echo $user->get('zipcode'); ?></div>

				<label for="city"><?php echo $city_label; ?></label>
				<div type="text" name="city" id="city"  ><?php echo $user->get('city'); ?></div>
			</div>
			<div class="form_column">
				<label for="country"><?php echo $country_label; ?></label>
				<div name="country" id="country" style="width:21.5em;">
					<?php echo $user->get('country');?>&nbsp;
				</div>

				<label for="phone1"><?php echo $phone1_label; ?></label>
				<div type="text" name="phone1" id="phone1"  ><?php echo $user->get('phone1'); ?></div>

				<label for="phone2"><?php echo $phone2_label; ?></label>
				<div type="text" name="phone2" id="phone2"  ><?php echo $user->get('phone2'); ?></div>

				<label for="email"><?php echo $email_label; ?></label>
				<div type="text" name="email" id="email"  ><?php echo $user->get('email'); ?></div>

				<label for="website"><?php echo $website_label; ?></label>
				<div type="text" name="website" id="website"  ><?php echo $user->get('website'); ?></div>
			</div>
		</div>
	</div>

	    <div role="tabpanel" class="tab-pane active" id="invoice_info">
	    	<div id="invoice_info" style="display:none" class="tab-div tab-div-hidden">	
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
					<div name="invoice_country" id="invoice_country" style="width:21.5em;">
						<?php echo $user->get('invoice_country');?>&nbsp;
					</div>

					<label for="invoice_email"><?php echo $invoice_email_label; ?></label>
					<div type="text" name="invoice_email" id="invoice_email"  ><?php echo $user->get('invoice_email'); ?></div>

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

<!--				<?php if(userLevel() > 3) :?>
					<label for="customid"><?php echo $customer_nr_label;?></label>
					<input type="text" name="customid" id="customid" value="<?php echo $user->get('customer_nr');?>" />
					<button onclick="saveCustomId()" type="button"><?php echo $save_customer_id?></button>
				<?php endif;?>-->

			</div>
		</div>
	    <div role="tabpanel" class="tab-pane active" id="profile_presentation">
	    	<div id="profile_presentation" style="display:none" class="tab-div tab-div-hidden">

				<h3><?php echo uh($translator->{'Company presentation'}); ?></h3>
				<?php foreach(glob(ROOT.'public/images/exhibitors/'.$user->get('id').'/*') as $filename) : ?>
					<img src="<?php echo('../images/exhibitors/'.$user->get('id').'/'. basename($filename) . "\n"); ?>" id="profile_presentation_img" />
				<?php endforeach; ?>				
				<div style="width: 41.66em; max-height: 29.166em; overflow-x: auto; overflow-y: auto;" name="presentation" id="presentation" class="presentation"><?php echo $user->get('presentation'); ?>
					<?php if ($user->get('presentation') == '')
					echo uh($translator->{'The exhibitor has not yet entered a company presentation.'}); ?>
				</div>

			</div>
		</div>
	    <div role="tabpanel" class="tab-pane active" id="bookings_this_event">
	    	<div id="bookings_this_event" style="display:none" class="tab-div tab-div-hidden">
				<h3><?php echo $bookings_samefair_section; ?></h3>

				<?php if (count($same_fair_positions) > 0): ?>
				<table class="std_table use-scrolltable" id="profileBookingsCurrentFair">
					<thead>
						<tr>
							<th><?php echo $tr_fairname; ?></th>
							<th><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th><?php echo $tr_booker; ?></th>
							<th><?php echo $tr_field; ?></th>
							<th><?php echo $tr_time; ?></th>
							<th><?php echo $tr_message; ?></th>
						</tr>
					</thead>
					<tbody>
				<?php foreach($same_fair_positions as $pos): ?>
						<tr>
							<td><a target="_blank" href="/mapTool/map/<?php echo $pos['fair']; ?>/<?php echo $pos['id']; ?>/<?php echo $pos['map']; ?>"><?php echo $pos['fair_name']; ?> </a></td>
							<td><?php echo $pos['name']; ?></td>
							<td class="center"><?php echo $pos['area']; ?></td>
							<td class="center"><?php echo $pos['company']; ?></td>
							<td class="center"><?php echo $pos['commodity']; ?></td>
							<td><?php echo ($pos['booking_time'] != '') ? date('d-m-Y H:i:s', $pos['booking_time']) : ''; ?></td>
							<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
				<?php if (strlen($pos['arranger_message']) > 0): ?>
								<a href="administrator/arrangerMessage/<?php echo (isset($pos['preliminary']) ? 'preliminary' : 'exhibitor') . '/' . $pos['exhibitor_id']; ?>" class="open-arranger-message">
									<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
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
			</div>
		</diV>
	    <div role="tabpanel" class="tab-pane active" id="bookings_your_events">
	    	<div id="bookings_your_events" style="display:none" class="tab-div tab-div-hidden">
				<h3><?php echo $bookings_section; ?></h3>

				<?php if (count($positions) > 0): ?>
				<table class="std_table use-scrolltable" id="profileBookings">
					<thead>
						<tr>
							<th><?php echo $tr_fairname; ?></th>
							<th><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th><?php echo $tr_booker; ?></th>
							<th><?php echo $tr_field; ?></th>
							<th><?php echo $tr_time; ?></th>
							<th><?php echo $tr_message; ?></th>
						</tr>
					</thead>
					<tbody>
				<?php foreach($positions as $pos): ?>
						<tr>
							<td><a target="_blank" href="/mapTool/map/<?php echo $pos['fair']; ?>/<?php echo $pos['id']; ?>/<?php echo $pos['map']; ?>"><?php echo $pos['fair_name']; ?> </a></td>
							<td><?php echo $pos['name']; ?></td>
							<td class="center"><?php echo $pos['area']; ?></td>
							<td class="center"><?php echo $pos['company']; ?></td>
							<td class="center"><?php echo $pos['commodity']; ?></td>
							<td><?php echo ($pos['booking_time'] != '') ? date('d-m-Y H:i:s', $pos['booking_time']) : ''; ?></td>
							<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
				<?php if (strlen($pos['arranger_message']) > 0): ?>
								<a href="administrator/arrangerMessage/<?php echo (isset($pos['preliminary']) ? 'preliminary' : 'exhibitor') . '/' . $pos['exhibitor_id']; ?>" class="open-arranger-message">
									<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
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
			</div>
		</div>
	</div>
</div>
