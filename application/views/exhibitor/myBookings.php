<?php
global $translator;

$general_column_info = array(
	$translator->{"Company"} => array(
		'orgnr' => $translator->{'Organization number'},
		'company' => $translator->{'Company'},
		'commodity' => $translator->{'Commodity'},
		'address' => $translator->{'Address'},
		'zipcode' => $translator->{'Zip code'},
		'city' => $translator->{'City'},
		'country' => $translator->{'Country'},
		'phone1' => $translator->{'Phone 1'},
		'phone2' => $translator->{'Phone 2'},
		'fax' => $translator->{'Fax number'},
		'email' => $translator->{'E-mail'},
		'website' => $translator->{'Website'}
	),
	$translator->{"Billing address"} => array(
		'invoice_company' => $translator->{'Company'},
		'invoice_address' => $translator->{'Address'},
		'invoice_zipcode' => $translator->{'Zip code'},
		'invoice_city' => $translator->{'City'},
		'invoice_country' => $translator->{'Country'},
		'invoice_email' => $translator->{'E-mail'}
	),
	$translator->{"Contact person"} => array(
		'name' => $translator->{'Contact person'},
		'contact_phone' => $translator->{'Contact Phone'},
		'contact_phone2' => $translator->{'Contact Phone 2'},
		'contact_email' => $translator->{'Contact Email'}
	)
);

$bookings_columns = array(
	$translator->{"Booking"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$bookings_columns = array_merge($bookings_columns, $general_column_info);

$reserved_columns = array(
	$translator->{"Reservation"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'expires' => $translator->{'Reserved until'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$reserved_columns = array_merge($reserved_columns, $general_column_info);

$prelbookings_columns = array(
	$translator->{"Preliminary booking"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$prelbookings_columns = array_merge($prelbookings_columns, $general_column_info);

$fair_registrations_columns = array(
	$translator->{"Registration"} => array(
		'status' => $translator->{'Status'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$fair_registrations_columns = array_merge($fair_registrations_columns, $general_column_info);

$fair_registrations_deleted_columns = array(
	$translator->{"Registration (deleted)"} => array(
		'status' => $translator->{'Status'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$fair_registrations_deleted_columns = array_merge($fair_registrations_deleted_columns, $general_column_info);

$prelbookings_deleted_columns = array(
	$translator->{"Preliminary booking (deleted)"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$prelbookings_deleted_columns = array_merge($prelbookings_deleted_columns, $general_column_info);

$bookings_deleted_columns = array(
	$translator->{"Booking/reservation (deleted)"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$bookings_deleted_columns = array_merge($bookings_deleted_columns, $general_column_info);

$reserved_cloned_columns = array(
	$translator->{"Reservation (cloned)"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'expires' => $translator->{'Reserved until'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$reserved_cloned_columns = array_merge($reserved_cloned_columns, $general_column_info);
?>
<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
	.squaredFour{width:1.416em; height:1.416em;}
	.squaredFour:before{left:0.33em;top:0.33em;}
	.scrolltable-wrap{margin:0.5em 0em 1em 0em;}
	.no-search{max-height: 18em;}
	#review_list_div{max-height: 48em;}
</style>

<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<h1><?php echo $mainheadline; ?></h1>

<script type="text/javascript">

	function cancelBooking(link, position){
		var confirmDialogue = "<?php echo $confirm_cancel_prel?> <br/><strong>%s</strong>";
          $.confirm({
			title: '<?php echo $confirm_delete?>',
			content: confirmDialogue.replace('%s', position),
			confirm: function(){
			  cancelMyself(link);
			},
			cancel: function() {
			}
          });
	}

	function cancelRegistration(link, position){
		var confirmDialogue = "<?php echo $confirm_cancel_reg?> <br/><strong>%s</strong>";
          $.confirm({
			title: '<?php echo $confirm_delete?>',
			content: confirmDialogue.replace('%s', position),
			confirm: function(){
			  cancelMyself(link);
			},
			cancel: function() {
			}
          });
	}

	var export_fields = {
		booked: <?php echo json_encode($bookings_columns); ?>,

		reserved: <?php echo json_encode($reserved_columns); ?>,

		reserved_cloned: <?php echo json_encode($reserved_cloned_columns); ?>,

		prem: <?php echo json_encode($prelbookings_columns); ?>,

		fair_registrations: <?php echo json_encode($fair_registrations_columns); ?>,

		delprem: <?php echo json_encode($prelbookings_deleted_columns); ?>,

		delbookings: <?php echo json_encode($bookings_deleted_columns); ?>,

		delregistrations: <?php echo json_encode($fair_registrations_deleted_columns); ?>,

	};
</script>

<div id="review_prel_dialogue" style="padding:0 2.5em 0.833em 2.5em" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -3.7em; margin-right: -0.5em;"/>
	<h3 class="review standSpaceName" style="padding-bottom:0.416em;"></h3>
	<br />
	<br />
	<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
	<span style="font-size:1.7em;" id="review_user"></span>
	<br />	
	<div id="column" class="review_column1">
		<label for="review_registration_area" id="review_area_label"><?php echo uh($translator->{'Requested area'}); ?></label>
		<p name="review_registration_area" id="review_registration_area"></p>
		<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<p name="commodity" id="review_commodity_input"></p>
	</div>
	<div id="column" class="review_column2">
		<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
		<p id="review_category_list" style="width:100%; float:left;"></p>		
	</div>
	<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Exhibitor message to Organizer'}); ?></label>
	<p name="arranger_message" id="review_message"></p>	
	<div class="no-search" id="review_list_div" style="padding:1.66em 0em;">
		<div id="review_div">
			<table id="review_list" class="no-search" style="width:100%;">
			</table>
		</div>
		<table id="review_list2" class="no-search" style="width:100%;">
		</table>
	</div>
</div>


<div role="tabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="javascript:void(0)" id="booked" class="tabs-tab" aria-controls="home" role="tab" data-toggle="tab"><img src="images/icons/marker_booked.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Payed stand spaces'}); ?> (<?php echo count($positions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="reserved" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/marker_reserved.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Reservations, not payed'}); ?> (<?php echo count($rpositions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="reserved_cloned" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/Reserverad-gray.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Reservations to accept'}); ?> (<?php echo count($rcpositions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="prel_bookings" class="tabs-tab" aria-controls="messages" role="tab" data-toggle="tab"><img src="images/icons/marker_applied.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Stand spaces I have applied for'}); ?> (<?php echo count($prelpos); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="fair_registrations" class="tabs-tab" aria-controls="settings" role="tab" data-toggle="tab"><img src="images/icons/script.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Events I have applied for'}); ?> (<?php echo count($fair_registrations); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="fair_registrations_deleted" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/recyclebin_reg.png" class="tab_img_wide" style="vertical-align:top;" /> <?php echo uh($translator->{'Deleted/denied event applications'}); ?> (<?php echo count($fair_registrations_deleted); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="bookings_deleted" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/recyclebin_resbook.png" class="tab_img_wide" style="vertical-align:top;" /> <?php echo uh($translator->{'Deleted reservations'}); ?> (<?php echo count($del_positions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="prel_bookings_deleted" class="tabs-tab" aria-controls="settings" role="tab" data-toggle="tab"><img src="images/icons/recyclebin_prel.png" class="tab_img_wide" style="vertical-align:top;" /> <?php echo uh($translator->{'Deleted/denied stand space applications'}); ?> (<?php echo count($del_prelpos); ?>)</a></li>
  </ul>

  <!-- Tab panes -->
	  <div class="tab-content">
	    <div role="tabpanel" class="tab-pane active" id="booked">

	<script>

	$(document).ready(function() {
	    // go to the latest tab, if it exists:
	    var lastTab = localStorage.getItem('lastTab');
	    if (lastTab) {
			var selected = lastTab;
			var div = 'div#' + selected;
			$('.tab-div').css('display', 'none');
			$('li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			if (!$(div + ' table').hasClass('scrolltable')) {
				useScrolltable($(div + ' table'));
			}
			$(selected).floatThead('reflow');
			$(selected).floatThead('getSizingRow');
			$('[id="' + lastTab + '"]').tab('show');

	    } else {
			var selected = 'booked';
			var div = 'div#' + selected;
			$('.tab-div').css('display', 'none');
			$('li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			if (!$(div + ' table').hasClass('scrolltable')) {
				useScrolltable($(div + ' table'));
			}
	    }
	});
		$('.tabs-tab').on("click", function() {
			var selected = $(this).attr('id');
			var div = 'div#' + selected;
			$('.tab-div').css('display', 'none');
			$('li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			if (!$(div + ' table').hasClass('scrolltable')) {
				useScrolltable($(div + ' table'));
			}
			localStorage.setItem('lastTab', $(this).attr('id'));
		});

	</script>


<!-- Bookings start -->
	<div id="booked" style="display:none" class="tab-div tab-div-hidden">

	<?php if(count($positions) > 0){ ?>

		<form action="administrator/exportNewReservations/1" method="post">
			<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
			<div class="floatright right">
				<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="booked"></button>
			</div>

			<table class="std_table use-scrolltable" id="booked">
				<thead>
					<tr>
						<th><?php echo $tr_fair; ?></th>
						<th class="left"><?php echo $tr_pos; ?></th>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
						<th data-sorter="false"><?php echo $tr_view; ?></th>
						<th class="last" data-sorter="false">
							<input type="checkbox" id="check-all-bookings" class="check-all" data-group="rows-1" />
							<label class="squaredFour" for="check-all-bookings" />
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($positions as $pos):?>
					<tr
						data-id="<?php echo $pos['id']; ?>"
						data-categories="<?php echo uh($pos['categories']); ?>"
						data-options="<?php echo uh($pos['options']); ?>"
						data-articles="<?php echo uh($pos['articles']); ?>"
						data-amount="<?php echo uh($pos['amount']); ?>"
						data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
						data-optionid="<?php echo uh($pos['optionid']); ?>"
						data-optiontext="<?php echo uh($pos['optiontext']); ?>"
						data-optionprice="<?php echo uh($pos['optionprice']); ?>"
						data-optionvat="<?php echo uh($pos['optionvat']); ?>"
						data-articleid="<?php echo uh($pos['articleid']); ?>"
						data-articletext="<?php echo uh($pos['articletext']); ?>"
						data-articleprice="<?php echo uh($pos['articleprice']); ?>"
						data-articlevat="<?php echo uh($pos['articlevat']); ?>"
						data-articleamount="<?php echo uh($pos['articleamount']); ?>"
						data-posstatus="<?php echo uh('2'); ?>"
						data-posname="<?php echo uh($pos['name']); ?>"
						data-posprice="<?php echo uh($pos['price']); ?>"
						data-posinfo="<?php echo uh($pos['information']); ?>"
						data-posvat="<?php echo uh($pos['vat']); ?>"
						data-company="<?php echo uh($pos['company']); ?>"
						data-commodity="<?php echo uh($pos['commodity']); ?>"
						data-message="<?php echo uh($pos['arranger_message']); ?>"
						href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$pos['id']; ?>"
					>
						<td class="center open-view-this-preliminary"><?php echo uh($pos['fairname']); ?></td>
						<td class="left open-view-this-preliminary"><?php echo $pos['name']; ?></td>
						<td class="center open-view-this-preliminary"><?php echo $pos['area']; ?></td>
						<td class="left open-view-this-preliminary"><?php echo $pos['commodity']; ?></td>
						<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
						<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
						<td class="center">
							<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
								<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
							</a>
						</td>
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-1" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	<?php } else { ?>
		<p> <?php echo $booked_notfound?> </p>
	<?php }?>
	</div>
</div>
<!-- Bookings end -->


<!-- Reservations start -->
	<div role="tabpanel" class="tab-pane" id="reserved">

		<div id="reserved" style="display:none" class="tab-div tab-div-hidden">

			<?php if(count($rpositions) > 0){?>

			<form action="administrator/exportNewReservations/2" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
				<div class="floatright right">
					<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="reserved"></button>
				</div>

				<table class="std_table use-scrolltable" id="reserved">
					<thead>
						<tr>
							<th><?php echo $tr_fair; ?></th>
							<th class="left"><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<!--<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>-->
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_reserved_until; ?></th>
							<th data-sorter="false"><?php echo $tr_view; ?></th>
							<th class="last" data-sorter="false">
								<input type="checkbox" id="check-all-reserved" class="check-all" data-group="rows-2" />
								<label class="squaredFour" for="check-all-reserved" />
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($rpositions as $pos): ?>
						<tr
							data-id="<?php echo $pos['id']; ?>"
							data-categories="<?php echo uh($pos['categories']); ?>"
							data-options="<?php echo uh($pos['options']); ?>"
							data-articles="<?php echo uh($pos['articles']); ?>"
							data-amount="<?php echo uh($pos['amount']); ?>"
							data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
							data-optionid="<?php echo uh($pos['optionid']); ?>"
							data-optiontext="<?php echo uh($pos['optiontext']); ?>"
							data-optionprice="<?php echo uh($pos['optionprice']); ?>"
							data-optionvat="<?php echo uh($pos['optionvat']); ?>"
							data-articleid="<?php echo uh($pos['articleid']); ?>"
							data-articletext="<?php echo uh($pos['articletext']); ?>"
							data-articleprice="<?php echo uh($pos['articleprice']); ?>"
							data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
							data-articleamount="<?php echo uh($pos['articleamount']); ?>"
							data-posstatus="<?php echo uh('1'); ?>"
							data-posname="<?php echo uh($pos['name']); ?>"
							data-posprice="<?php echo uh($pos['price']); ?>"
							data-posinfo="<?php echo uh($pos['information']); ?>"
							data-posvat="<?php echo uh($pos['vat']); ?>"
							data-company="<?php echo uh($pos['company']); ?>"
							data-commodity="<?php echo uh($pos['commodity']); ?>"
							data-message="<?php echo uh($pos['arranger_message']); ?>"
							data-expires="<?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?>"
							href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$pos['id']; ?>"
						>
							<td class="center open-view-this-preliminary"><?php echo uh($pos['fairname']); ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['name']; ?></td>
							<td class="center open-view-this-preliminary"><?php echo $pos['area']; ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['commodity']; ?></td>
							<!--<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>-->
							<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
							<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?></td>					
							<td class="center">
								<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
								</a>
							</td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-2" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</form>
		<?php } else { ?>
			<p> <?php echo $reserv_notfound?> </p>
		<?php }?>
		</div>
	</div>
<!-- Reservations end -->


<!-- Cloned reservations start -->	
	<div role="tabpanel" class="tab-pane" id="reserved_cloned">

		<div id="reserved_cloned" style="display:none" class="tab-div tab-div-hidden">

			<?php if(count($rcpositions) > 0){?>

			<form action="administrator/exportNewReservations/8" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
				<div class="floatright right">
					<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="reserved_cloned"></button>
				</div>

				<table class="std_table use-scrolltable" id="reserved_cloned">
					<thead>
						<tr>
							<th><?php echo $tr_fair; ?></th>
							<th class="left"><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_reserved_until; ?></th>
							<th data-sorter="false"><?php echo $tr_view; ?></th>
							<th data-sorter="false"><?php echo $tr_confirm_reservation; ?></th>
							<th data-sorter="false"><?php echo $tr_deny_reservation; ?></th>
							<th class="last" data-sorter="false">
								<input type="checkbox" id="check-all-reserved_cloned" class="check-all" data-group="rows-8" />
								<label class="squaredFour" for="check-all-reserved_cloned" />
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($rcpositions as $pos): ?>
						<tr
							data-id="<?php echo $pos['id']; ?>"
							data-categories="<?php echo uh($pos['categories']); ?>"
							data-options="<?php echo uh($pos['options']); ?>"
							data-articles="<?php echo uh($pos['articles']); ?>"
							data-amount="<?php echo uh($pos['amount']); ?>"
							data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
							data-optionid="<?php echo uh($pos['optionid']); ?>"
							data-optiontext="<?php echo uh($pos['optiontext']); ?>"
							data-optionprice="<?php echo uh($pos['optionprice']); ?>"
							data-optionvat="<?php echo uh($pos['optionvat']); ?>"
							data-articleid="<?php echo uh($pos['articleid']); ?>"
							data-articletext="<?php echo uh($pos['articletext']); ?>"
							data-articleprice="<?php echo uh($pos['articleprice']); ?>"
							data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
							data-articleamount="<?php echo uh($pos['articleamount']); ?>"
							data-posstatus="<?php echo uh('1'); ?>"
							data-posname="<?php echo uh($pos['name']); ?>"
							data-posprice="<?php echo uh($pos['price']); ?>"
							data-posinfo="<?php echo uh($pos['information']); ?>"
							data-posvat="<?php echo uh($pos['vat']); ?>"
							data-company="<?php echo uh($pos['company']); ?>"
							data-commodity="<?php echo uh($pos['commodity']); ?>"
							data-message="<?php echo uh($pos['arranger_message']); ?>"
							data-expires="<?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?>"
							href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$pos['id']; ?>"
						>
							<td class="center open-view-this-preliminary"><?php echo uh($pos['fairname']); ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['name']; ?></td>
							<td class="center open-view-this-preliminary"><?php echo $pos['area']; ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['commodity']; ?></td>
							<!--<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>-->
							<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
							<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?></td>					
							<td class="center">
								<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
								</a>
							</td>
							<td class="center">
								<a href="<?php echo BASE_URL.'exhibitor/verifyReservation/'.$pos['id'].'/'.$pos['link'].'/accept'?>" target="_blank" title="<?php echo $tr_accept; ?>">
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/add_green.png" class="icon_img" />
								</a>
							</td>
							<td class="center">
								<a href="<?php echo BASE_URL.'exhibitor/verifyReservation/'.$pos['id'].'/'.$pos['link'].'/deny'?>" target="_blank" title="<?php echo $tr_deny; ?>">
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/delete.png" class="icon_img" />
								</a>
							</td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-8" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</form>

		<?php } else { ?>
			<p> <?php echo $reserv_cloned_notfound?> </p>
		<?php }?>
		</div>
	</div>
<!-- Cloned reservations end -->

<!-- Preliminary bookings start -->
	<div role="tabpanel" class="tab-pane" id="prel_bookings">

		<div id="prel_bookings" style="display:none" class="tab-div tab-div-hidden">

		<?php if(count($prelpos) > 0){ ?>
			<form action="administrator/exportNewReservations/3" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
				<div class="floatright right">
					<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="prem"></button>
				</div>

				<table class="std_table use-scrolltable" id="prem">
					<thead>
						<tr>
							<th><?php echo $tr_fair; ?></th>
							<th class="left"><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
							<th data-sorter="false"><?php echo $tr_view; ?></th>
							<th data-sorter="false"><?php echo $tr_delete; ?></th>
							<th class="last" data-sorter="false">
								<input type="checkbox" id="check-all-preliminary" class="check-all" data-group="rows-3" />
								<label class="squaredFour" for="check-all-preliminary" />
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($prelpos as $pos): ?>
						<tr
							id="prem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?>
							data-id="<?php echo $pos['id']; ?>"
							data-categories="<?php echo uh($pos['categories']); ?>"
							data-options="<?php echo uh($pos['options']); ?>"
							data-articles="<?php echo uh($pos['articles']); ?>"
							data-amount="<?php echo uh($pos['amount']); ?>"
							data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
							data-optionid="<?php echo uh($pos['optionid']); ?>"
							data-optiontext="<?php echo uh($pos['optiontext']); ?>"
							data-optionprice="<?php echo uh($pos['optionprice']); ?>"
							data-optionvat="<?php echo uh($pos['optionvat']); ?>"
							data-articleid="<?php echo uh($pos['articleid']); ?>"
							data-articletext="<?php echo uh($pos['articletext']); ?>"
							data-articleprice="<?php echo uh($pos['articleprice']); ?>"
							data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
							data-articleamount="<?php echo uh($pos['articleamount']); ?>"
							data-posstatus="<?php echo uh('3'); ?>"
							data-posname="<?php echo uh($pos['name']); ?>"
							data-posprice="<?php echo uh($pos['price']); ?>"
							data-posinfo="<?php echo uh($pos['information']); ?>"
							data-posvat="<?php echo uh($pos['vat']); ?>"
							data-company="<?php echo uh($pos['company']); ?>"
							data-commodity="<?php echo uh($pos['commodity']); ?>"
							data-message="<?php echo uh($pos['arranger_message']); ?>"
							href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$pos['id']; ?>"
						>
							<td class="center open-view-this-preliminary"><?php echo uh($pos['fairname']); ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['name'];?></td>
							<td class="center open-view-this-preliminary"><?php echo $pos['area']; ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['commodity']; ?></td>
							<td class="center"><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
							<td class="center">
								<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
								</a>
							</td>
							<td class="center">
								<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="cancelBooking('<?php echo BASE_URL . 'exhibitor/pre_delete/' . $pos['id'] . '/' . $pos['userid'] . '/' . $pos['position']; ?>', '<?php echo $pos['name']; ?>')">
									<img src="<?php echo BASE_URL; ?>images/icons/delete.png" class="icon_img deleteimg" alt="<?php echo $tr_delete; ?>" />
								</a>
							</td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-3" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
						</tr>
					<?php endforeach;?>
					</tbody>
				</table>
			</form>
		<?php } else { ?>
			<p> <?php echo $prel_notfound?> </p>
		<?php }?>
		</div>
	</div>
<!-- Preliminary bookings end -->


<!-- Deleted preliminary bookings start -->
	<div role="tabpanel" class="tab-pane" id="prel_bookings_deleted">

		<div id="prel_bookings_deleted" style="display:none" class="tab-div tab-div-hidden">

		<?php if(count($del_prelpos) > 0){ ?>
			<form action="administrator/exportNewReservations/6" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
				<div class="floatright right">
					<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="delprem"></button>
				</div>

				<table class="std_table use-scrolltable" id="delprem">
					<thead>
						<tr>
							<th><?php echo $tr_fair; ?></th>
							<th class="left"><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
							<th class="last" data-sorter="false">
								<input type="checkbox" id="check-all-preliminary-deleted" class="check-all" data-group="rows-6" />
								<label class="squaredFour" for="check-all-preliminary-deleted" />
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($del_prelpos as $pos): ?>
						<tr
							id="delprem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?>
							data-id="<?php echo $pos['id']; ?>"
							data-categories="<?php echo uh($pos['categories']); ?>"
							data-optionid="<?php echo uh($pos['optionid']); ?>"
							data-optiontext="<?php echo uh($pos['optiontext']); ?>"
							data-optionprice="<?php echo uh($pos['optionprice']); ?>"
							data-optionvat="<?php echo uh($pos['optionvat']); ?>"
							data-articleid="<?php echo uh($pos['articleid']); ?>"
							data-articletext="<?php echo uh($pos['articletext']); ?>"
							data-articleprice="<?php echo uh($pos['articleprice']); ?>"
							data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
							data-articleamount="<?php echo uh($pos['articleamount']); ?>"
							data-posstatus="<?php echo uh('3'); ?>"
							data-posname="<?php echo uh($pos['name']); ?>"
							data-posprice="<?php echo uh($pos['price']); ?>"
							data-posinfo="<?php echo uh($pos['information']); ?>"
							data-posvat="<?php echo uh($pos['vat']); ?>"
							data-company="<?php echo uh($pos['company']); ?>"
							data-commodity="<?php echo uh($pos['commodity']); ?>"
							data-message="<?php echo uh($pos['arranger_message']); ?>"
							href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$pos['id']; ?>"
						>
							<td class="center open-view-this-preliminary"><?php echo uh($pos['fairname']); ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['name'];?></td>
							<td class="center open-view-this-preliminary"><?php echo $pos['area']; ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['commodity']; ?></td>
							<td class="center"><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-6" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					<?php endforeach;?>
					</tbody>
				</table>
			</form>
		<?php } else { ?>
			<p> <?php echo $del_prel_notfound?> </p>
		<?php }?>
		</div>
	</div>
<!-- Deleted preliminary bookings end -->


<!-- Deleted bookings/reservations start -->
	<div role="tabpanel" class="tab-pane" id="bookings_deleted">
		<div id="bookings_deleted" style="display:none" class="tab-div tab-div-hidden">

		<?php if(count($del_positions) > 0){ ?>

			<form action="administrator/exportNewReservations/7" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
				<div class="floatright right">
					<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="delbookings"></button>
				</div>

				<table class="std_table use-scrolltable" id="delbookings">
					<thead>
						<tr>
							<th><?php echo $tr_fair; ?></th>
							<th class="left"><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
							<th class="last" data-sorter="false">
								<input type="checkbox" id="check-all-bookings-deleted" class="check-all" data-group="rows-7" />
								<label class="squaredFour" for="check-all-bookings-deleted" />
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($del_positions as $pos):?>
						<tr
							data-categories="<?php echo uh($pos['categories']); ?>"
							data-optionid="<?php echo uh($pos['optionid']); ?>"
							data-optiontext="<?php echo uh($pos['optiontext']); ?>"
							data-optionprice="<?php echo uh($pos['optionprice']); ?>"
							data-optionvat="<?php echo uh($pos['optionvat']); ?>"
							data-articleid="<?php echo uh($pos['articleid']); ?>"
							data-articletext="<?php echo uh($pos['articletext']); ?>"
							data-articleprice="<?php echo uh($pos['articleprice']); ?>"
							data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
							data-articleamount="<?php echo uh($pos['articleamount']); ?>"
							data-posname="<?php echo uh($pos['name']); ?>"
							data-posprice="<?php echo uh($pos['price']); ?>"
							data-posinfo="<?php echo uh($pos['information']); ?>"
							data-posvat="<?php echo uh($pos['vat']); ?>"
							data-company="<?php echo uh($pos['company']); ?>"
							data-commodity="<?php echo uh($pos['commodity']); ?>"
							data-message="<?php echo uh($pos['arranger_message']); ?>"
							href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$pos['id']; ?>"
						>
							<td class="center open-view-this-preliminary"><?php echo uh($pos['fairname']); ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['name']; ?></td>
							<td class="center open-view-this-preliminary"><?php echo $pos['area']; ?></td>
							<td class="left open-view-this-preliminary"><?php echo $pos['commodity']; ?></td>
							<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
							<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-7" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</form>
		<?php } else { ?>
			<p> <?php echo $booked_notfound?> </p>
		<?php }?>
		</div>
	</div>
<!-- Deleted bookings/reservations end -->


<!-- Registrations start -->

	<div role="tabpanel" class="tab-pane" id="fair_registrations">

		<div id="fair_registrations" style="display:none" class="tab-div tab-div-hidden">

		<?php if (count($fair_registrations) > 0): ?>
			<form action="administrator/exportNewReservations/5" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
				<div class="floatright right">
					<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="fair_registrations"></button>
				</div>

				<table class="std_table use-scrolltable" id="fair_registrations">
					<thead>
						<tr>
							<th><?php echo $tr_fair; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
							<th data-sorter="false"><?php echo $tr_delete; ?></th>
							<th class"last" data-sorter="false">
								<input type="checkbox" id="check-all-registrations" class="check-all" data-group="rows-5" />
								<label class="squaredFour" for="check-all-registrations" />
							</th>
						</tr>
					</thead>
					<tbody>
		<?php	foreach ($fair_registrations as $registration): ?>
						<tr data-id="<?php echo $registration['id']; ?>"
								data-type="registration"
								data-categories="<?php echo uh($registration['categories']); ?>"
								data-optionid="<?php echo uh($registration['optionid']); ?>"
								data-optiontext="<?php echo uh($registration['optiontext']); ?>"
								data-optionprice="<?php echo uh($registration['optionprice']); ?>"
								data-optionvat="<?php echo uh($registration['optionvat']); ?>"
								data-articleid="<?php echo uh($registration['articleid']); ?>"
								data-area="<?php echo uh($registration['area']); ?>"
								data-articletext="<?php echo uh($registration['articletext']); ?>"
								data-articleprice="<?php echo uh($registration['articleprice']); ?>"
								data-articlevat="<?php echo uh($registration['articlevat']); ?>"							
								data-articleamount="<?php echo uh($registration['articleamount']); ?>"
								data-company="<?php echo uh($registration['company']); ?>"
								data-commodity="<?php echo uh($registration['commodity']); ?>"
								data-message="<?php echo uh($registration['arranger_message']); ?>"
								href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$registration['id']; ?>"
							>
							<td class="center open-view-this-preliminary"><?php echo uh($registration['fairname']); ?></td>
							<td class="center open-view-this-preliminary"><?php echo uh($registration['area']); ?></td>
							<td class="left open-view-this-preliminary"><?php echo uh($registration['commodity']); ?></td>
							<td class="center"><?php echo date('d-m-Y H:i', $registration['booking_time']); ?></td>
							<td class="center">
								<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="cancelRegistration('<?php echo BASE_URL . 'exhibitor/registration_delete/' . $registration['id']; ?>', '<?php echo $registration['fairname']; ?>')">
									<img src="<?php echo BASE_URL; ?>images/icons/delete.png" class="icon_img deleteimg" alt="<?php echo $tr_delete; ?>" />
								</a>
							</td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $registration['id']; ?>" data-userid="<?php echo $registration['user']; ?>" class="rows-5" /><label class="squaredFour" for="<?php echo $registration['id']; ?>" /></td>
						</tr>
		<?php	endforeach; ?>
					</tbody>
				</table>
			</form>
		<?php else: ?>
			<p><?php echo $fregistrations_notfound; ?></p>
		<?php endif; ?>
		</div>
	</div>

		<!-- Registrations deleted start -->
	<div role="tabpanel" class="tab-pane" id="fair_registrations_deleted">

		<div id="fair_registrations_deleted" style="display:none" class="tab-div tab-div-hidden">

		<?php if (count($fair_registrations_deleted) > 0): ?>
			<form action="administrator/exportNewReservations/9" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo $search; ?>&nbsp; </h2>
				<div class="floatright right">
					<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="fair_registrations_deleted"></button>
				</div>

				<table class="std_table use-scrolltable" id="fair_registrations_deleted">
					<thead>
						<tr>
							<th><?php echo $tr_fair; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
							<th class"last" data-sorter="false">
								<input type="checkbox" id="check-all-registrations_deleted" class="check-all" data-group="rows-9" />
								<label class="squaredFour" for="check-all-registrations_deleted" />
							</th>
						</tr>
					</thead>
					<tbody>
		<?php	foreach ($fair_registrations_deleted as $registration): ?>
						<tr data-id="<?php echo $registration['id']; ?>"
								data-type="registration"
								data-categories="<?php echo uh($registration['categories']); ?>"
								data-optionid="<?php echo uh($registration['optionid']); ?>"
								data-optiontext="<?php echo uh($registration['optiontext']); ?>"
								data-optionprice="<?php echo uh($registration['optionprice']); ?>"
								data-optionvat="<?php echo uh($registration['optionvat']); ?>"
								data-articleid="<?php echo uh($registration['articleid']); ?>"
								data-area="<?php echo uh($registration['area']); ?>"
								data-articletext="<?php echo uh($registration['articletext']); ?>"
								data-articleprice="<?php echo uh($registration['articleprice']); ?>"
								data-articlevat="<?php echo uh($registration['articlevat']); ?>"							
								data-articleamount="<?php echo uh($registration['articleamount']); ?>"
								data-company="<?php echo uh($registration['company']); ?>"
								data-commodity="<?php echo uh($registration['commodity']); ?>"
								data-message="<?php echo uh($registration['arranger_message']); ?>"
								href="<?php echo BASE_URL.'exhibitor/reviewPrelBooking/'.$registration['id']; ?>"
							>
							<td class="center open-view-this-preliminary"><?php echo uh($registration['fairname']); ?></td>
							<td class="center open-view-this-preliminary"><?php echo uh($registration['area']); ?></td>
							<td class="left open-view-this-preliminary"><?php echo uh($registration['commodity']); ?></td>
							<td class="center"><?php echo date('d-m-Y H:i', $registration['booking_time']); ?></td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $registration['id']; ?>" data-userid="<?php echo $registration['user']; ?>" class="rows-9" /><label class="squaredFour" for="<?php echo $registration['id']; ?>" /></td>
						</tr>
		<?php	endforeach; ?>
					</tbody>
				</table>
			</form>
		<?php else: ?>
			<p><?php echo $fregistrations_deleted_notfound; ?></p>
		<?php endif; ?>
		</div>
	</div>
</div>