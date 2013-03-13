<?php
if ($notfound)
	die('Fair not found');

function makeUserOptions2($sel=0, $fair) {
	$users = User::getExhibitorsForArranger($fair->get('created_by'));

	$ret = '';
	foreach ($users as $user) {
		$chk = ($sel == $user->get('id')) ? ' selected="selected"' : '';
		$ret.= '<option value="'.$user->get('id').'"'.$chk.'>'.$user->get('company').'</option>';
	}
	return $ret;
}

?>


<div id="fullscreen">
	<p id="fullscreen_controls">
		<a class="button delete" href="javascript:void(0)" id="closeFullscreen"><?php echo $translator->{'Leave fullscreen'} ?></a>
	</p>
</div>

<script type="text/javascript">

	lang.bookStandSpace = '<?php echo $translator->{"Book stand space"} ?>';
	lang.editStandSpace = '<?php echo $translator->{"Edit stand space"} ?>';
	lang.moveStandSpace = '<?php echo $translator->{"Move stand space"} ?>';
	lang.deleteStandSpace = '<?php echo $translator->{"Delete stand space"} ?>';
	lang.reserveStandSpace = '<?php echo $translator->{"Reserve stand space"} ?>';
	lang.preliminaryBookStandSpace = '<?php echo $translator->{"Preliminary book stand space"} ?>';
	lang.cancelPreliminaryBooking = '<?php echo $translator->{"Cancel preliminary booking"} ?>';
	lang.editBooking = '<?php echo $translator->{"Edit booking"} ?>';
	lang.cancelBooking = '<?php echo $translator->{"Cancel booking"} ?>';
	lang.pasteExhibitor = '<?php echo $translator->{"Paste exhibitor"} ?>';
	lang.moreInfo = '<?php echo $translator->{"More info"} ?>';
	lang.space = '<?php echo $translator->{"Space"} ?>';
	lang.status = '<?php echo $translator->{"Status"} ?>';
	lang.area = '<?php echo $translator->{"Area"} ?>';
	lang.reservedUntil = '<?php echo $translator->{"Reserved until"} ?>';
	lang.by = '<?php echo $translator->{"by"} ?>';
	lang.commodity = '<?php echo $translator->{"commodity"} ?>';
	lang.clickToReserveStandSpace = '<?php echo $translator->{"Click to reserve stand space"} ?>';
	lang.presentation = '<?php echo $translator->{"Presentation"} ?>';
	lang.info = '<?php echo $translator->{"Info"} ?>';
	lang.deleteConfirm = '<?php echo $translator->{"Are you sure you want to delete this marker?"} ?>';
	lang.website = '<?php echo $translator->{"Website"} ?>';
	lang.print = '<?php echo $translator->{"Print"} ?>';
	lang.category = '<?php echo $translator->{"Categories"} ?>';
	lang.noPlaceRights = '<?php echo $translator->{"You do not have administrative rights on this map"} ?>';

	lang.StatusText = function(str) {
		if (str == 'open')
			return '<?php echo $translator->{"open"} ?>';
		else if (str == 'reserved')
			return '<?php echo $translator->{"reserved"} ?>';
		else if (str == 'booked')
			return '<?php echo $translator->{"booked"} ?>';
		else if (str == 'applied')
			return '<?php echo $translator->{"preliminary booked"} ?>';
	}
	
	<?php if ($reserve != 'false'): ?>
	var reserveId = <?php echo $reserve; ?>
	<?php else: ?>
	var reserveId = null;
	<?php endif; ?>
	
	<?php if ($position != 'false'): ?>
	var prePosId = <?php echo $position; ?>;
	<?php else: ?>
	var prePosId = null;
	<?php endif; ?>

	<?php if ($hasRights): ?>
	var hasRights = true;
	<?php else: ?>
	var hasRights = false;
	<?php endif; ?>

	var accessibleMaps = new Array;
	<?php foreach($accessible_maps as $map): ?>
		accessibleMaps.push(<?php echo $map ?>);
	<?php endforeach; ?>

	$(document).ready(function() {
		maptool.init(<?php echo reset($fair->get('maps'))->get('id'); ?>);
		<?php if (isset($_SESSION['copied_exhibitor'])): ?>
		copiedExhibitor = "<?php echo $_SESSION['copied_exhibitor'] ?>";
		<?php endif; ?>
	});
</script>

<div id="edit_position_dialogue" class="dialogue">
	<h3><?php echo $translator->{'New/Edit stand space'} ?></h3>

	<label for="position_name_input"><?php echo $translator->{'Name'} ?> *</label>
	<input type="text" name="position_name_input" id="position_name_input"/>

	<label for="position_area_input"><?php echo $translator->{'Area'} ?> (m<sup>2</sup>)</label>
	<input type="text" name="position_area_input" id="position_area_input"/>

	<label for="position_info_input"><?php echo $translator->{'Information'} ?></label>
	<textarea name="position_info_input" id="position_info_input"></textarea>

	<input type="hidden" name="position_id_input" id="position_id_input" value=""/>

	<p><input type="button" id="post_position" value="<?php echo $translator->{'Save and close'} ?>"/></p>

</div>

<div id="book_position_dialogue" class="dialogue">
	<h3><?php echo $translator->{'Book stand space'} ?></h3>

	<div class="ssinfo"></div>
	
	<label for="book_category_input"><?php echo $translator->{'Category'} ?></label>
	<select name="book_category_input[]" id="book_category_input" multiple="multiple">
		<?php foreach($fair->get('categories') as $cat): ?>
		<option value="<?php echo $cat->get('id') ?>"><?php echo $cat->get('name') ?></option>
		<?php endforeach; ?>
	</select>
	
	<label for="book_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<input type="text" name="book_commodity_input" id="book_commodity_input"/>

	<label for="book_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="book_message_input" id="book_message_input"></textarea>

	<label for="search_user_input"><?php echo $translator->{'Search'}; ?></label>
	<input type="text" name="search_user_input" id="search_user_input" />

	<label for="book_user_input"><?php echo $translator->{'User'} ?></label>
	<select name="book_user_input" id="book_user_input">
		<?php echo makeUserOptions2(0, $fair); ?>
		<?php //echo makeUserOptions2($fair->db, 'user', 0, 'level=1', 'company'); ?>
	</select>
	<a href="exhibitor/createFromMap/<?php echo $fair->get('url'); ?>"><?php echo $translator->{'New exhibitor'}; ?></a>

	<p><input type="button" id="book_post" value="<?php echo $translator->{'Confirm booking'} ?>"/></p>

</div>

<div id="more_info_dialogue" class="dialogue">
	<h3></h3>
	<p class="info"></p>
	<h4></h4>
	<p class="presentation"></p>
	<p class="website_link"></p>
</div>

<div id="reserve_position_dialogue" class="dialogue">
	<h3><?php echo $translator->{'Reserve stand space'} ?></h3>

	<div class="ssinfo"></div>
	
	<label for="reserve_category_input"><?php echo $translator->{'Category'} ?></label>
	<select name="reserve_category_input[]" id="reserve_category_input" multiple="multiple">
		<?php foreach($fair->get('categories') as $cat): ?>
		<option value="<?php echo $cat->get('id') ?>"><?php echo $cat->get('name') ?></option>
		<?php endforeach; ?>
	</select>
	
	<label for="reserve_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<input type="text" name="reserve_commodity_input" id="reserve_commodity_input"/>

	<label for="reserve_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="reserve_message_input" id="reserve_message_input"></textarea>

	<label for="search_user_input"><?php echo $translator->{'Search'}; ?></label>
	<input type="text" name="search_user_input" id="search_user_input" />

	<label for="reserve_user_input"><?php echo $translator->{'User'} ?></label>
	<select name="reserve_user_input" id="reserve_user_input">
		<?php echo makeUserOptions2(0, $fair); ?>
		<?php //echo makeUserOptions2($fair->db, 'user', 0, 'level=1', 'company'); ?>
	</select>
	<a href="exhibitor/createFromMap/<?php echo $fair->get('url'); ?>"><?php echo $translator->{'New exhibitor'}; ?></a>

	<label for="reserve_expires_input"><?php echo $translator->{'Reserved until'} ?> (dd-mm-yyyy)</label>
	<input type="text" class="datepicker" name="reserve_expires_input" id="reserve_expires_input"/>
	
	<p><input type="button" id="reserve_post" value="<?php echo $translator->{'Confirm reservation'} ?>"/></p>

</div>

<div id="apply_mark_dialogue" class="dialogue">
	<h3><?php echo $translator->{'Apply for stand space'} ?></h3>
	
	<div class="mssinfo"></div>
	
	<label for="apply_category_input"><?php echo $translator->{'Category'} ?></label>
	<select name="apply_category_input[]" id="apply_category_input" multiple="multiple">
		<?php foreach($fair->get('categories') as $cat): ?>
		<option value="<?php echo $cat->get('id') ?>"><?php echo $cat->get('name') ?></option>
		<?php endforeach; ?>
	</select>
	
	<label for="apply_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<input type="text" name="apply_commodity_input" id="apply_commodity_input"/>

	<label for="apply_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="apply_message_input" id="apply_message_input"></textarea>
	
	<p>
		<input type="button" id="apply_choose_more" value="<?php echo $translator->{'Book more stand spaces'} ?>"/>
		<input type="button" id="apply_confirm" value="<?php echo $translator->{'Confirm'} ?>"/>
	</p>
	
</div>

<div id="apply_position_dialogue" class="dialogue">
	<h3><?php echo $translator->{'Apply for stand space'} ?></h3>
	
	<div class="pssinfo"></div>

	<p><input type="button" id="apply_post" value="<?php echo $translator->{'Confirm'} ?>"/></p>

</div>

<div id="pancontrols">
	<img src="images/icons/pan_left.png" id="panleft" alt=""/>
	<img src="images/icons/pan_up.png" id="panup" alt=""/>
	<img src="images/icons/pan_down.png" id="pandown" alt=""/>
	<img src="images/icons/pan_right.png" id="panright" alt=""/>
</div>
<div data-role="fieldcontain">
 	<input type="range" name="zoombar" id="zoombar" value="0" min="0" max="100"  />
</div>

<div id="mapHolder">
	<div id="map">
		<img src="<?php echo reset($fair->get('maps'))->get('image'); ?>" alt="" id="map_img"/>
	</div>
</div>
<?php if( is_int($myMap) ) : ?>
<script>
	$(document).ready(function(){
		$('#map_link_<?php echo $myMap; ?>').click();
	});
</script>
<?php endif; ?>