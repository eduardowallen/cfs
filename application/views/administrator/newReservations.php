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
		//'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$prelbookings_columns = array_merge($prelbookings_columns, $general_column_info);

<<<<<<< HEAD
$prelbookings2_columns = array(
	$translator->{"Preliminary booking"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		//'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$prelbookings2_columns = array_merge($prelbookings2_columns, $general_column_info);

=======
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
$fair_registrations_columns = array(
	$translator->{"Registrations"} => array(
		'status' => $translator->{'Status'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$fair_registrations_columns = array_merge($fair_registrations_columns, $general_column_info);
?>
<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
</style>

<script type="text/javascript" src="js/tablesearch.js"></script>

<h1><?php echo $fair->get('name'); ?></h1>

<?php if (isset($fairs_admin)): // If a list of accessible fairs is found, display a drop-down list to choose from ?>
  <label class="inline-block"><?php echo uh($translator->{'Switch to event: '}); ?></label>
  <select onchange="if(this.value) document.location.href=this.value;">
  <?php
    $own = false;
    $options = '';
    foreach($fairs_admin as $fa) {
      $active = $fair->get('id') == $fa['id'];
      $own = $own || $active;
      $options .= '<option value="'.BASE_URL.'administrator/reservationsChangeFair/'.$fa['id'].'"'.($active?" selected":"").'>'.$fa['name'].'</option>';
    }
    
    if(!$own) :
  ?>
    <option value selected><?php echo $fair->get('name'); ?></option>
  <?php
    endif;
    echo $options;
  ?>
  </select>
  <br class="clear">
<?php endif; ?>

<script type="text/javascript">
	var confirmDialogue = "<?php echo $confirm_delete?> %s?";
	var deletion = "<?php echo $deletion_comment?>";

	function denyPrepPosition(link, position, status){
		if(confirm(confirmDialogue.replace('%s', position))){
			var message = prompt(deletion, "");
			denyPosition(link, message, position, status);
		}
	}

	function hider(btn, elem){
		var element = $('#'+elem).parent();
	
		if (element.css('display') == 'none') {
			element.css('display','block');
			$(btn).children().attr('src', '<?php echo BASE_URL."public/images/icons/min.png";?>');
		} else {
			element.css('display','none');
			$(btn).children().attr('src', '<?php echo BASE_URL."public/images/icons/utv.png";?>');
		}
<<<<<<< HEAD

		return false;
	}

	var export_fields = {
		booked: <?php echo json_encode($bookings_columns); ?>,

		reserved: <?php echo json_encode($reserved_columns); ?>,

		prem: <?php echo json_encode($prelbookings_columns); ?>,
		
		iprem: <?php echo json_encode($prelbookings2_columns); ?>,

		fair_registrations: <?php echo json_encode($fair_registrations_columns); ?>

=======

		return false;
	}

	var export_fields = {
		booked: <?php echo json_encode($bookings_columns); ?>,

		reserved: <?php echo json_encode($reserved_columns); ?>,

		prem: <?php echo json_encode($prelbookings_columns); ?>,

		fair_registrations: <?php echo json_encode($fair_registrations_columns); ?>

>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
	};
</script>

<?php if ($hasRights): ?>

<div id="reserve_position_dialogue" class="dialogue">
	<form action="" method="post">
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
		<h3 class="confirm"><?php echo uh($translator->{'Reserve stand space'}); ?></h3>
		<h3 class="edit"><?php echo uh($translator->{'Edit reservation'}); ?></h3>

		<p>
			<strong><?php echo uh($translator->{'Space'}); ?> <span class="position-name"></span></strong>
		</p>
		
		<label for="reserve_category_input"><?php echo uh($translator->{'Category'}); ?></label>
		<div id="reserve_category_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<?php foreach($fair->get('categories') as $cat): ?>
				<p style="margin:0; width:100%; float:left;">
					<input type="checkbox" name="categories[]" value="<?php echo $cat->get('id') ?>" /><?php echo $cat->get('name') ?>
				</p>
			<?php endforeach; ?>
		</div>
		
		<label for="reserve_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<input type="text" class="dialogueInput" name="commodity" id="reserve_commodity_input" />

		<label for="reserve_option_input"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div id="reserve_option_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<?php foreach($fair->get('options') as $opt): ?>
				<p style="margin:0; width:100%; float:left;">
					<input type="checkbox" name="options[]" value="<?php echo $opt->get('id') ?>" /><?php echo $opt->get('text') ?>
				</p>
			<?php endforeach; ?>
		</div>
		
		<label for="reserve_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="arranger_message" id="reserve_message_input"></textarea>

		<label for="reserve_user_input"><?php echo uh($translator->{'User'}); ?></label>
		<select style="width:300px;" id="reserve_user_input" disabled="disabled">
			<option id="reserve_user"></option>
		</select>

		<label for="reserve_expires_input"><?php echo uh($translator->{'Reserved until'}); ?> (DD-MM-YYYY HH:MM)</label>
		<input type="text" class="dialogueInput datetime datepicker" name="expires" id="reserve_expires_input" value="" />
		<img src="/images/icons/icon_help.png" class="helpicon_map" title="<?php echo uh($translator->{'The date that you set here is the date when the reservation expires and the stand space is reopened (green) for preliminary bookings.'}); ?>" />

		<p>
			<input type="hidden" name="id" id="reserve_id" />
			<input type="submit" name="reserve" class="confirm" value="<?php echo uh($translator->{'Confirm reservation'}); ?>" />
			<input type="submit" name="reserve" class="edit" value="<?php echo uh($translator->{'Save'}); ?>" class="save-btn" />
		</p>
	</form>
</div>

<div id="book_position_dialogue" class="dialogue">
	<form action="" method="post">
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
		<h3 class="confirm"><?php echo uh($translator->{'Book stand space'}); ?></h3>
		<h3 class="edit"><?php echo uh($translator->{'Edit booking'}); ?></h3>

		<p>
			<strong><?php echo uh($translator->{'Space'}); ?> <span class="position-name"></span></strong>
		</p>
		
		<label for="book_category_input"><?php echo uh($translator->{'Category'}); ?></label>
		<div id="book_category_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<?php foreach($fair->get('categories') as $cat): ?>
			<p style="margin:0; width:100%; float:left;">
				<input type="checkbox" name="categories[]" value="<?php echo $cat->get('id') ?>" /><?php echo $cat->get('name') ?>
			</p>
			<?php endforeach; ?>
		</div>
		
		<label for="book_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<input type="text" class="dialogueInput" name="commodity" id="book_commodity_input" />

		<label for="book_option_input"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div id="book_option_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<?php foreach($fair->get('options') as $opt): ?>
			<p style="margin:0; width:100%; float:left;">
				<input type="checkbox" name="options[]" value="<?php echo $opt->get('id') ?>" /><?php echo $opt->get('text') ?>
			</p>
			<?php endforeach; ?>
		</div>

		<label for="book_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="arranger_message" id="book_message_input"></textarea>

		<label for="book_user_input"><?php echo uh($translator->{'User'}); ?></label>
		<select style="width:300px;" id="book_user_input" disabled="disabled">
			<option id="book_user"></option>
		</select>

		<p>
			<input type="hidden" name="id" id="book_id" />
			<input type="submit" name="approve" class="confirm" value="<?php echo uh($translator->{'Confirm booking'}); ?>" />
			<input type="submit" name="approve" class="edit" value="<?php echo uh($translator->{'Save'}); ?>" />
		</p>
	</form>
</div>



<<<<<<< HEAD
<h2 class="tblsite" style="margin-top:20px"><img src="images/icons/marker_booked.png"/> <?php echo $headline; ?></h2>
=======
<h2 class="tblsite" style="margin-top:20px"><?php echo $headline; ?><a href="#" onclick="return hider(this,'booked')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
<?php if(count($positions) > 0){ ?>

	<form action="administrator/exportNewReservations/1" method="post">
		<div class="floatright right">
<<<<<<< HEAD
		<?php if($fair->get('sms_settings') === '{"smsFunction":["1"]}') {?>
			<button type="submit" class="open-sms-send" name="send_sms" data-for="booked" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
		<?php } ?>
=======
			<button type="submit" class="open-sms-send" name="send_sms" data-for="booked" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			<button type="submit" class="open-excel-export" name="export_excel" data-for="booked"><?php echo uh($export); ?></button>
		</div>

		<table class="std_table use-scrolltable" id="booked">
			<thead>
				<tr>
					<th><?php echo $tr_pos; ?></th>
					<th><?php echo $tr_area; ?></th>
					<th><?php echo $tr_booker; ?></th>
					<th><?php echo $tr_field; ?></th>
					<th><?php echo $tr_time; ?></th>
					<th><?php echo $tr_last_edited; ?></th>
					<th><?php echo $tr_message; ?></th>
					<th data-sorter="false"><?php echo $tr_comments; ?></th>
					<th data-sorter="false"><?php echo $tr_view; ?></th>
					<th data-sorter="false"><?php echo $tr_edit; ?></th>
					<th data-sorter="false"><?php echo $tr_delete; ?></th>
<<<<<<< HEAD
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-1" /></th>
=======
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-1" checked="checked" /></th>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				</tr>
			</thead>
			<tbody>
			<?php foreach($positions as $pos):?>
				<tr
					data-categories="<?php echo uh($pos['categories']); ?>"
					data-options="<?php echo uh($pos['options']); ?>"
					data-posname="<?php echo uh($pos['name']); ?>"
					data-company="<?php echo uh($pos['company']); ?>"
					data-commodity="<?php echo uh($pos['commodity']); ?>"
					data-message="<?php echo uh($pos['arranger_message']); ?>"
				>
					<td><?php echo $pos['name']; ?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
<<<<<<< HEAD
					<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
					<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i:s', $pos['edit_time']) : $never_edited_label); ?></td>
=======
					<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?> <?php echo TIMEZONE; ?></td>
					<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i:s', $pos['edit_time']) . ' ' . TIMEZONE : $never_edited_label); ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
					</td>
					<td class="center">
						<a href="#" class="js-show-comment-dialog" data-user="<?php echo $pos['userid']; ?>" data-fair="<?php echo $pos['fair']; ?>" data-position="<?php echo $pos['position']; ?>" title="<?php echo $tr_comments; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/notes.png" alt="<?php echo $tr_comments; ?>" />
						</a>
					</td>
					<td>
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a href="administrator/editBooking/<?php echo $pos['id']; ?>" class="open-edit-booking" title="<?php echo $tr_edit; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/pencil.png" alt="<?php echo $tr_edit; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'booking')">
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
						</a>
					</td>
<<<<<<< HEAD
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-1" /></td>
=======
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-1" checked="checked" /></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</form>
<?php } else { ?>
	<p> <?php echo $booked_notfound?> </p>
<?php }?>



<<<<<<< HEAD
	<h2 class="tblsite" style="margin-top:20px"><img src="images/icons/marker_reserved.png"/> <?php echo $rheadline; ?></h2>
=======
	<h2 class="tblsite" style="margin-top:20px"><?php echo $rheadline; ?><a href="#" style="cursor:pointer;" onclick="return hider(this,'reserved')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL.'public/images/icons/min.png';?>" alt="" /></a></h2>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217

	<?php if(count($rpositions) > 0){?>

	<form action="administrator/exportNewReservations/2" method="post">
		<div class="floatright right">
<<<<<<< HEAD
			<?php if($fair->get('sms_settings') === '{"smsFunction":["1"]}') {?>
			<button type="submit" class="open-sms-send" name="send_sms" data-for="reserved" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
			<?php } ?>
=======
			<button type="submit" class="open-sms-send" name="send_sms" data-for="reserved" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			<button type="submit" class="open-excel-export" name="export_excel" data-for="reserved"><?php echo uh($export); ?></button>
		</div>

		<table class="std_table use-scrolltable" id="reserved">
			<thead>
				<tr>
					<th><?php echo $tr_pos; ?></th>
					<th><?php echo $tr_area; ?></th>
					<th><?php echo $tr_booker; ?></th>
					<th><?php echo $tr_field; ?></th>
					<th><?php echo $tr_time; ?></th>
					<th><?php echo $tr_last_edited; ?></th>
					<th><?php echo $tr_message; ?></th>
					<th><?php echo $tr_reserved_until; ?></th>
					<th data-sorter="false"><?php echo $tr_view; ?></th>
					<th data-sorter="false"><?php echo $tr_edit; ?></th>
					<th data-sorter="false"><?php echo $tr_deny; ?></th>
					<th data-sorter="false"><?php echo $tr_approve; ?></th>
<<<<<<< HEAD
					<th data-sorter="false" colspan="3"><input type="checkbox" class="check-all" data-group="rows-2" /></th>
=======
					<th data-sorter="false" colspan="3"><input type="checkbox" class="check-all" data-group="rows-2" checked="checked" /></th>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				</tr>
			</thead>
			<tbody>
			<?php foreach($rpositions as $pos): ?>
				<tr
					data-id="<?php echo $pos['id']; ?>"
					data-categories="<?php echo uh($pos['categories']); ?>"
					data-options="<?php echo uh($pos['options']); ?>"
					data-posname="<?php echo uh($pos['name']); ?>"
					data-company="<?php echo uh($pos['company']); ?>"
					data-commodity="<?php echo uh($pos['commodity']); ?>"
					data-message="<?php echo uh($pos['arranger_message']); ?>"
<<<<<<< HEAD
					data-expires="<?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?>"
=======
					data-expires="<?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?> <?php echo TIMEZONE; ?>"
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					data-approveurl="<?php echo BASE_URL.'administrator/approveReservation/'; ?>"
				>
					<td><?php echo $pos['name']; ?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
<<<<<<< HEAD
					<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
					<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i:s', $pos['edit_time']) : $never_edited_label); ?></td>
=======
					<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?> <?php echo TIMEZONE; ?></td>
					<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i:s', $pos['edit_time']) . ' ' . TIMEZONE : $never_edited_label); ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
	<?php if (strlen($pos['arranger_message']) > 0): ?>
							<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
								<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
							</a>
	<?php endif; ?>
					</td>
<<<<<<< HEAD
					<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?></td>
=======
					<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?> <?php echo TIMEZONE; ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>

					<td class="center">
						<a href="administrator/editBooking/<?php echo $pos['id']; ?>" class="open-edit-reservation" title="<?php echo $tr_edit; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/pencil.png" alt="<?php echo $tr_edit; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Reservation')">
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
						</a>
					</td>
					<td class="center">
						<?php //echo "href=".BASE_URL.'administrator/approveReservation/'.$pos['position'] ?><?php //echo " title=".$tr_approve; ?>
						<a  style="cursor:pointer;" title="<?php echo $tr_approve; ?>" onclick="showPopup('book',this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
						</a>
					</td>
<<<<<<< HEAD
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-2" /></td>
=======
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-2" checked="checked" /></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					<!--<td class="approve" style="display:none;"></td>-->
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</form>

<?php } else { ?>
	<p> <?php echo $reserv_notfound?> </p>
<?php }?>



<<<<<<< HEAD
	<h2 class="tblsite" style="margin-top:20px"><img src="images/icons/marker_applied.png"/> <?php echo $prel_table; ?></h2>
=======
	<h2 class="tblsite" style="margin-top:20px"><?php echo $prel_table; ?><a href="#" style="cursor:pointer;" onclick="return hider(this,'prem')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217

<?php if(count($prelpos) > 0){ ?>
	<form action="administrator/exportNewReservations/3" method="post">
		<div class="floatright right">
<<<<<<< HEAD
			<?php if($fair->get('sms_settings') === '{"smsFunction":["1"]}') {?>
			<button type="submit" class="open-sms-send" name="send_sms" data-for="prem" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
			<?php } ?>
=======
			<button type="submit" class="open-sms-send" name="send_sms" data-for="prem" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			<button type="submit" class="open-excel-export" name="export_excel" data-for="prem"><?php echo uh($export); ?></button>
		</div>

		<table class="std_table use-scrolltable" id="prem">
			<thead>
				<tr>
					<th><?php echo $tr_pos; ?></th>
					<th><?php echo $tr_area; ?></th>
					<th><?php echo $tr_booker; ?></th>
					<th><?php echo $tr_field; ?></th>
					<th><?php echo $tr_time; ?></th>
					<th><?php echo $tr_message; ?></th>
					<th data-sorter="false"><?php echo $tr_view; ?></th>
					<th data-sorter="false"><?php echo $tr_deny; ?></th>
					<th data-sorter="false"><?php echo $tr_approve; ?></th>
					<th data-sorter="false"><?php echo $tr_reserve; ?></th>
<<<<<<< HEAD
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-3" /></th>
=======
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-3" checked="checked" /></th>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				</tr>
			</thead>
			<tbody>
			<?php foreach($prelpos as $pos): ?>
				<tr
					id="prem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?>
					data-id="<?php echo $pos['id']; ?>"
					data-approveurl="<?php echo BASE_URL.'administrator/newReservations/approve/'; ?>"
					data-reserveurl="<?php echo BASE_URL.'administrator/reservePrelBooking/'; ?>"
					data-categories="<?php echo uh($pos['categories']); ?>"
					data-options="<?php echo uh($pos['options']); ?>"
					data-posname="<?php echo uh($pos['name']); ?>"
					data-company="<?php echo uh($pos['company']); ?>"
					data-commodity="<?php echo uh($pos['commodity']); ?>"
					data-message="<?php echo uh($pos['arranger_message']); ?>"
				>
					<td><?php echo $pos['name'];?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
<<<<<<< HEAD
					<td class="center"><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
=======
					<td class="center"><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?> <?php echo TIMEZONE; ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/preliminary/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
					</td>
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" title="<?php echo $tr_deny; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Preliminary Booking')">
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_deny; ?>" />
						</a>
					</td>
					<td class="center">
						<?php //echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?><?php //echo $tr_approve; ?>
						<a style="cursor:pointer;" title="<?php echo $tr_approve; ?>" onclick="showPopup('book', this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
						</a>
					</td>
					<td class="center">
						<!-- <a href="<?php //echo BASE_URL.'administrator/reservePrelBooking/'.$pos['id'] ?>" title="<?php //echo $tr_reserve; ?>"> -->
						<a style="cursor:pointer;" title="<?php echo $tr_reserve; ?>" onclick="showPopup('reserve',this)">
<<<<<<< HEAD
							<img src="<?php echo BASE_URL; ?>images/icons/reserve.png" alt="<?php echo $tr_reserve; ?>" />
						</a>
					</td>
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-3" /></td>
=======
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_reserve; ?>" />
						</a>
					</td>
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-3" checked="checked" /></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</form>
<<<<<<< HEAD
<?php } else { ?>
	<p> <?php echo $prel_notfound?> </p>
<?php }?>

<!-- prelbookings inactive -->
	<h2 class="tblsite" style="margin-top:20px; color:#000;"><?php echo $prel_table_inactive; ?></h2>

<?php if(count($iprelpos) > 0){ ?>
	<form action="administrator/exportNewReservations/4" method="post">
		<div class="floatright right">
			<?php if($fair->get('sms_settings') === '{"smsFunction":["1"]}') {?>
			<button type="submit" class="open-sms-send" name="send_sms" data-for="iprem" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
			<?php } ?>
			<button type="submit" class="open-excel-export" name="export_excel" data-for="iprem"><?php echo uh($export); ?></button>
		</div>

		<table class="std_table use-scrolltable" id="iprem">
			<thead>
				<tr>
					<th><?php echo $tr_pos; ?></th>
					<th><?php echo $tr_area; ?></th>
					<th><?php echo $tr_booker; ?></th>
					<th><?php echo $tr_field; ?></th>
					<th><?php echo $tr_time; ?></th>
					<th><?php echo $tr_message; ?></th>
					<th data-sorter="false"><?php echo $tr_view; ?></th>
					<th data-sorter="false"><?php echo $tr_deny; ?></th>
<!--				<th data-sorter="false"><?php echo $tr_approve; ?></th>
					<th data-sorter="false"><?php echo $tr_reserve; ?></th>-->
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-4" /></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($iprelpos as $pos): ?>
				<tr
					id="iprem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?>
					data-id="<?php echo $pos['id']; ?>"
					data-approveurl="<?php echo BASE_URL.'administrator/newReservations/approve/'; ?>"
					data-reserveurl="<?php echo BASE_URL.'administrator/reservePrelBooking/'; ?>"
					data-categories="<?php echo uh($pos['categories']); ?>"
					data-options="<?php echo uh($pos['options']); ?>"
					data-posname="<?php echo uh($pos['name']); ?>"
					data-company="<?php echo uh($pos['company']); ?>"
					data-commodity="<?php echo uh($pos['commodity']); ?>"
					data-message="<?php echo uh($pos['arranger_message']); ?>"
				>
					<td><?php echo $pos['name'];?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
					<td class="center"><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
					<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/preliminary/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
					</td>
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" title="<?php echo $tr_deny; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Preliminary Booking')">
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_deny; ?>" />
						</a>
					</td>
					
<!--					<td class="center">
						<?php //echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?><?php //echo $tr_approve; ?>
						<a style="cursor:pointer;" title="<?php echo $tr_approve; ?>" onclick="showPopup('book', this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
						</a>
					</td>
					<td class="center">
						 <a href="<?php //echo BASE_URL.'administrator/reservePrelBooking/'.$pos['id'] ?>" title="<?php //echo $tr_reserve; ?>"> 
						<a style="cursor:pointer;" title="<?php echo $tr_reserve; ?>" onclick="showPopup('reserve',this)">
							<img src="<?php echo BASE_URL; ?>images/icons/reserve.png" alt="<?php echo $tr_reserve; ?>" />
						</a>
					</td>
-->					
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-4" /></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</form>
=======
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
<?php } else { ?>
	<p> <?php echo $prel_notfound?> </p>
<?php }?>

<<<<<<< HEAD
<!-- Registrations -->
	<h2 class="tblsite" style="margin-top:20px"><img src="images/icons/script.png"/> <?php echo $fair_registrations_headline; ?></h2>

<?php if (count($fair_registrations) > 0): ?>
	<form action="administrator/exportNewReservations/5" method="post">
=======


	<h2 class="tblsite" style="margin-top:20px"><?php echo $fair_registrations_headline; ?><a href="#" style="cursor:pointer;" onclick="return hider(this,'fair_registrations')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>

<?php if (count($fair_registrations) > 0): ?>
	<form action="administrator/exportNewReservations/4" method="post">
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
		<div class="floatright right">
			<button type="submit" class="open-sms-send" name="send_sms" data-for="fair_registrations" data-fair="<?php echo $fair->get('id'); ?>"><?php echo uh($send_sms_label); ?></button><br />
			<button type="submit" class="open-excel-export" name="export_excel" data-for="fair_registrations"><?php echo uh($export); ?></button>
		</div>

		<table class="std_table use-scrolltable" id="fair_registrations">
			<thead>
				<tr>
					<th><?php echo $tr_area; ?></th>
					<th><?php echo $tr_booker; ?></th>
<<<<<<< HEAD
					<th><?php echo $tr_time; ?></th>
					<th><?php echo $tr_field; ?></th>
					<th><?php echo $tr_message; ?></th>
					<th data-sorter="false"><?php echo $tr_copy; ?></th>
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-5" /></th>
=======
					<th><?php echo $tr_field; ?></th>
					<th><?php echo $tr_message; ?></th>
					<th data-sorter="false"><?php echo $tr_view; ?></th>
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-4" checked="checked" /></th>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				</tr>
			</thead>
			<tbody>
<?php	foreach ($fair_registrations as $registration): ?>
				<tr data-id="<?php echo $registration->id; ?>">
					<td class="center"><?php echo uh($registration->area); ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $registration->user; ?>" class="showProfileLink"><?php echo uh($registration->company); ?></a></td>
<<<<<<< HEAD
					<td class="center"><?php echo date('d-m-Y H:i:s', $registration->booking_time); ?></td>	
					<td class="center"><?php echo uh($registration->commodity); ?></td>				
=======
					<td class="center"><?php echo uh($registration->commodity); ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					<td class="center" title="<?php echo uh($registration->arranger_message); ?>">
<?php		if (strlen($registration->arranger_message) > 0): ?>
						<a href="administrator/arrangerMessage/registration/<?php echo $registration->id; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php		endif; ?>
					</td>
					<td class="center">
<<<<<<< HEAD
						<a href="mapTool/pasteRegistration/<?php echo $registration->fair . '/' . $registration->id; ?>" target="_blank" title="<?php echo $tr_copy; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_copy; ?>" />
						</a>
					</td>
					<td class="center"><input type="checkbox" name="rows[]" value="<?php echo $registration->id; ?>" data-userid="<?php echo $registration->user; ?>" class="rows-5" /></td>
=======
						<a href="mapTool/pasteRegistration/<?php echo $registration->fair . '/' . $registration->id; ?>" target="_blank" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center"><input type="checkbox" name="rows[]" value="<?php echo $registration->id; ?>" data-userid="<?php echo $registration->user; ?>" class="rows-4" checked="checked" /></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				</tr>
<?php	endforeach; ?>
			</tbody>
		</table>
	</form>
<?php else: ?>
	<p><?php echo $fregistrations_notfound; ?></p>
<?php endif; ?>

<?php else: ?>
	<p>Du är inte behörig att administrera den här mässan.</p>
<?php endif; ?>