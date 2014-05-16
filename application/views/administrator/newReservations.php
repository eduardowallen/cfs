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
	'' => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer'}
	)
);
$bookings_columns = array_merge($bookings_columns, $general_column_info);

$reserved_columns = array(
	'' => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'expires' => $translator->{'Reserved until'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer'}
	)
);
$reserved_columns = array_merge($reserved_columns, $general_column_info);

$prelbookings_columns = array(
	'' => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		//'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer'}
	)
);
$prelbookings_columns = array_merge($prelbookings_columns, $general_column_info);
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

		return false;
	}

	var export_fields = {
		booked: <?php echo json_encode($bookings_columns); ?>,

		reserved: <?php echo json_encode($reserved_columns); ?>,

		prem: <?php echo json_encode($prelbookings_columns); ?>

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

		<label for="reserve_expires_input"><?php echo uh($translator->{'Reserved until'}); ?> (DD-MM-YYYY HH:MM <?php echo TIMEZONE; ?>)</label>
		<input type="text" class="dialogueInput datetime datepicker" name="expires" id="reserve_expires_input" value="" />

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



<h2 class="tblsite" style="margin-top:20px"><?php echo $headline; ?><a href="#" onclick="return hider(this,'booked')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>
<?php if(count($positions) > 0){ ?>

	<form action="administrator/exportNewReservations/1" method="post">
		<button type="submit" class="open-excel-export" name="export_excel" data-for="booked" style="float:right; margin-right:13%;"><?php echo uh($export); ?></button>

		<table class="std_table" id="booked">
			<thead>
				<tr>
					<th>
						<?php echo $tr_pos; ?>
					</th>
					<th>
						<?php echo $tr_area; ?>
					</th>
					<th>
						<?php echo $tr_booker; ?>
					</th>
					<th>
						<?php echo $tr_field; ?>
					</th>
					<th>
						<?php echo $tr_time; ?>
					</th>
					<th>
						<?php echo $tr_last_edited; ?>
					</th>
					<th>
						<?php echo $tr_message; ?>
					</th>
					<th data-sorter="false">
						<?php echo $tr_view; ?>
					</th>
					<th data-sorter="false">
						<?php echo $tr_edit; ?>
					</th>
					<th data-sorter="false">
						<?php echo $tr_delete; ?>
					</th>
					<th data-sorter="false">
						<input type="checkbox" class="check-all" data-group="rows-1" checked="checked" />
					</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($positions as $pos):?>
				<tr>
					<td><?php echo $pos['name']; ?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
					<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?> <?php echo TIMEZONE; ?></td>
					<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i:s', $pos['edit_time']) . ' ' . TIMEZONE : $never_edited_label); ?></td>
					<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
					</td>
					<td style="display:none;"><?php echo $pos['categories']; ?></td>
					<td style="display:none;"><?php echo $pos['options']; ?></td>
					<td>
					<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a href="administrator/editBooking/<?php echo $pos['id']; ?>" class="open-edit-booking">
							<img src="<?php echo BASE_URL; ?>images/icons/pencil.png" alt="<?php echo $tr_edit; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'booking')">
		
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" class="rows-1" checked="checked" /></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</form>
<?php } else { ?>
	<p> <?php echo $booked_notfound?> </p>
<?php }?>



	<h2 class="tblsite" style="margin-top:20px"><?php echo $rheadline; ?><a href="#" style="cursor:pointer;" onclick="return hider(this,'reserved')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL.'public/images/icons/min.png';?>" alt="" /></a></h2>

	<?php if(count($rpositions) > 0){?>

	<form action="administrator/exportNewReservations/2" method="post">
		<button type="submit" class="open-excel-export" name="export_excel" data-for="reserved" style="float:right; margin-right:13%;"><?php echo uh($export); ?></button>

		<table class="std_table" id="reserved">
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
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-2" checked="checked" /></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($rpositions as $pos): ?>
				<tr data-id="<?php echo $pos['id']; ?>">
					<td><?php echo $pos['name']; ?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
					<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?> <?php echo TIMEZONE; ?></td>
					<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i:s', $pos['edit_time']) . ' ' . TIMEZONE : $never_edited_label); ?></td>
					<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
	<?php if (strlen($pos['arranger_message']) > 0): ?>
							<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
								<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
							</a>
	<?php endif; ?>
					</td>
					<td style="display:none;"><?php echo $pos['categories']; ?></td>
					<td style="display:none;"><?php echo $pos['options']; ?></td>
					<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?> <?php echo TIMEZONE; ?></td>
					<td class="approve" style="display:none;"><?php echo BASE_URL.'administrator/approveReservation/'; ?></td>
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>

					<td class="center">
						<a href="administrator/editBooking/<?php echo $pos['id']; ?>" class="open-edit-reservation">
							<img src="<?php echo BASE_URL; ?>images/icons/pencil.png" alt="<?php echo $tr_edit; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Reservation')">
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
						</a>
					</td>
					<td class="center">
						<?php //echo "href=".BASE_URL.'administrator/approveReservation/'.$pos['position'] ?><?php //echo " title=".$tr_approve; ?>
						<a  style="cursor:pointer;"  onclick="showPopup('book',this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
						</a>
					</td>
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" class="rows-2" checked="checked" /></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</form>

<?php } else { ?>
	<p> <?php echo $reserv_notfound?> </p>
<?php }?>



	<h2 class="tblsite" style="margin-top:20px"><?php echo $prel_table; ?><a href="#" style="cursor:pointer;" onclick="return hider(this,'prem')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>

<?php if(count($prelpos) > 0){ ?>
	<form action="administrator/exportNewReservations/3" method="post">
		<button type="submit" class="open-excel-export" name="export_excel" data-for="prem" style="float:right; margin-right:13%;"><?php echo uh($export); ?></button>

		<table class="std_table" id="prem">
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
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-3" checked="checked" /></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($prelpos as $pos): ?>
				<tr id="prem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?> data-id="<?php echo $pos['id']; ?>">
					<td><?php echo $pos['name'];?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
					<td class="center"><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?> <?php echo TIMEZONE; ?></td>
					<td style="display:none;"></td>
					<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/preliminary/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
					</td>
					<td style="display: none;"><?php echo $pos['categories']; ?></td>
					<td style="display: none;"><?php echo $pos["options"]; ?></td>
					<td class="approve" style="display:none;"><?php echo BASE_URL.'administrator/newReservations/approve/'; ?></td>
					<td class="reserve" style="display:none;"><?php echo BASE_URL.'administrator/reservePrelBooking/'; ?></td>
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Preliminary Booking')">
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_deny; ?>" />
						</a>
					</td>
					<td class="center">
						<?php //echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?><?php //echo $tr_approve; ?>
						<a style="cursor:pointer;" onclick="showPopup('book',this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
						</a>
					</td>
					<td class="center">
						<!-- <a href="<?php //echo BASE_URL.'administrator/reservePrelBooking/'.$pos['id'] ?>" title="<?php //echo $tr_reserve; ?>"> -->
						<a style="cursor:pointer;" onclick="showPopup('reserve',this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_reserve; ?>" />
						</a>
					</td>
					<td><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" class="rows-3" checked="checked" /></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</form>
<?php } else { ?>
	<p> <?php echo $prel_notfound?> </p>
<?php }?>
<?php else: ?>
	<p>Du är inte behörig att administrera den här mässan.</p>
<?php endif; ?>