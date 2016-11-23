<?php if ($notfound) :?>
	<div id="wrong_url">
		<img src="images/images/wrong_url.png" style="padding-right:0.833em;" />
		<br />
		<p class="wrongurltext">
		<?php echo($translator->{'Fair not found'}); ?>
		</p></div>
<? die();
	//die($translator->{'Fair not found'});
endif;


function makeUserOptions1($sel=0, $fair) {
	$users = User::getExhibitorsForFair($fair->get('id'));

	$ret = '';
	foreach ($users as $user) {
		$chk = ($sel == $user->get('id')) ? ' selected="selected"' : '';
		$ret.= '<option value="'.$user->get('id').'"'.$chk.'>'.$user->get('company').'</option>';
	}
	return $ret;
}

function makeUserOptions2($sel=0, $fair) {
	$users = User::getExhibitorsForFair($fair->get('id'));

	$ret = '';
	foreach ($users as $user) {
		$chk = ($sel == $user->get('id')) ? ' selected="selected"' : '';
		$ret.= '<li onclick="chooseThis(this)" value="'.$user->get('id').'"'.$chk.'>'.$user->get('company').'</li>';
	}
	return $ret;
}

function makeUserOptions3($sel=0, $fair) {
	$users = User::getExhibitorsForFair($fair->get('id'));

	$ret = '';
	foreach ($users as $user) {
		$chk = ($sel == $user->get('id')) ? ' selected="selected"' : '';
		$ret.= '<li onclick="chooseThisBook(this)" value="'.$user->get('id').'"'.$chk.'>'.$user->get('company').'</li>';
	}
	return $ret;
}

?>

<?php 
	$visible = null;

	$f = new Fair;
	if (userLevel() > 0) {
		$f->loadsimple($_SESSION['user_fair'], 'id');
	} else {
		$f->loadsimple($_SESSION['outside_fair_url'], 'url');
	}
	
	// Hämta ut fältet hidden för att se om mässan är dold eller ej.
	if($f->get('hidden') == 0) :
		$visible = 'true';
	else:
		$visible = 'false';
	endif;

	if ($visible == 'false' && !$hasRights): ?>
		<script type="text/javascript">
			$().ready(function(){
				var fakevent = {preventDefault: function() {}};
				function alertBox(evt, message) {
					evt.preventDefault();
					$('#overlay').show();
					$('#alertBox .msg').html(message).parent().show();
					<?php if ((userLevel() == 3) || (userLevel() == 2)): ?>
						$('#alertBox').hide();
					<?php endif; ?>
				}
<?php	if (userLevel() > 1): ?>
		$('#alertBox2').show();
		$('#overlay').show();
			var url2 = "user/accountSettings";

		$('#alertBox2 input').click(function() {
			$(location).attr('href',url2);
			});
<?php		endif; ?>			
<?php	if ($f->get('allow_registrations') == 1 && userLevel() == 1): ?>
<?php		if ($has_prev_registrations): ?>
<?php			if ($f->get('hidden_info') != ''): ?>
				confirmBox(fakevent, <?php echo json_encode($f->get('hidden_info')); ?>, confirmedRegisterEvent, 'OK_CANCEL');
<?php 			else: ?>
				confirmBox(fakevent, '<?php echo ujs($translator->{'This event is hidden! If you want to register for this event, press OK'}); ?>', maptool.applyForFair, 'OK_CANCEL');
<?php 			endif; ?>
				function confirmedRegisterEvent() {
					confirmBox(fakevent, '<?php echo ujs($translator->{'You have already requested a stand space on this event. Do you want to make another one?'}); ?>', maptool.applyForFair, 'YES_NO', 'exhibitor/myBookings');
				}
<?php		else: ?>
<?php 			if ($f->get('hidden_info') != ''): ?>
					confirmBox(fakevent, <?php echo json_encode($f->get('hidden_info')); ?>, maptool.applyForFair, 'OK_CANCEL');
<?php 			else: ?>
					confirmBox(fakevent, '<?php echo ujs($translator->{'This event is hidden! If you want to register for this event, press OK'}); ?>', maptool.applyForFair, 'OK_CANCEL');
<?php 			endif; ?>
<?php	endif; ?>

<?php	else: ?>
		$('#alertBox').show();
<?php	if ($f->get('hidden_info') != ''): ?>		
		alertBox(fakevent, <?php echo json_encode($f->get('hidden_info')); ?>);
<?php 	else: ?>
		alertBox(fakevent, <?php echo json_encode($translator->{'This fair is hidden'}); ?>);
<?php endif; ?>
		$('#overlay').show();
			var url = "exhibitor/myBookings";

		$('#alertBox input').click(function() {
			$('#alertBox').hide();
<?php	if ($f->get('allow_registrations') == 0 && userLevel() == 1): ?>			
			$(location).attr('href',url);
<?php		endif; ?>			
			$('#nouser_dialogue').show();
		});

<?php	endif; ?>
			});
		</script>
<?php
	endif;

	// Om mässan är synlig
	if(($visible == 'false' && userLevel() > 2) || ($visible == 'false' && userLevel() == 2 && $hasRights) || $visible == 'true') :
		// Om användaren har nivå 1 men ej är ansluten till mässan
		if (userLevel() == 1 && !userIsConnectedTo($f->get('id'))):
			// Ajax-kod för att ansluta en användare till mässan ?>
			<script type="text/javascript">
				$().ready(function(){
					$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'connectToFair=1&fairId=' + <?php echo $f->get('id')?>,
					success: function(response) {
						res = JSON.parse(response);
						window.location = '<?php echo $fair->get('url')?>';
					}
				});
			});
			</script>
		<?php endif;?>

<?php if ($hasRights): ?>
		<div id="maptoolbox">
			<h3 id="maptoolbox_header">
				<?php echo uh($translator->{'Map tools'}); ?>
				<img src="images/icons/grid_icon.png" id="grid_icon"/>
				<a href="#" id="maptoolbox_minimize" title="<?php echo uh($translator->{'Minimize'}); ?>"></a>
			</h3>

			<p id="zoombar">
				<img src="images/zoom_slider.png" alt=""/>
				<a href="javascript:void(0)" id="in"></a>
				<a href="javascript:void(0)" id="out"></a>
			</p>
			<div id="maptoolbox_controls">
				<label style="display: inline-block;">
					<input type="button" class="greenbutton" value="<?php echo uh($translator->{"Grid activated"}); ?>" id="maptool_grid_activated2" />
					<!--<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'If this is unchecked, the grid will not be generated.'}); ?>" />-->
				</label>
					<input type="checkbox" id="maptool_grid_activated" style="display:none"/>
				<label style="display: inline-block;">
					<input type="button" class="greenbutton" value="<?php echo uh($translator->{'Change color'}); ?>" id="maptool_grid_white2" />
				</label>
				<input type="checkbox" id="maptool_grid_white" style="display:none"/>
				<h4 style="margin: 0px"><?php echo uh($translator->{'X-axis'}); ?></h4>
				<p><?php echo uh($translator->{'Show'}); ?></p>
				<input type="checkbox" id="maptool_grid_visible_x"></input>
				<label class="squaredFour" for="maptool_grid_visible_x"></label>
				<p><?php echo uh($translator->{'Snap stand spaces'}); ?></p>
				<input type="checkbox" id="maptool_grid_snap_markers_x" />
				<label class="squaredFour" for="maptool_grid_snap_markers_x"></label>
				<h4 style="margin: 1.66em 0px 0px 0px"><?php echo uh($translator->{'Y-axis'}); ?></h4>
					<p><?php echo uh($translator->{'Show'}); ?></p>
					<input type="checkbox" id="maptool_grid_visible_y" />
					<label class="squaredFour" for="maptool_grid_visible_y"></label>
					<p><?php echo uh($translator->{'Snap stand spaces'}); ?></p>
					<input type="checkbox" id="maptool_grid_snap_markers_y" />
					<label class="squaredFour" for="maptool_grid_snap_markers_y"></label>
				<label style="padding-top: 1.66em; padding-bottom: 0.833em;">
					<?php echo uh($translator->{'Opacity'}); ?>:
					<input type="range" id="maptool_grid_opacity" min="1" max="100" value="100" />
					<input type="text" id="maptool_grid_opacity_num" value="100" class="spinner" />
				</label>				
				<label style="padding-bottom: 1.66em;">
					<?php echo uh($translator->{'Move grid'}); ?>
					<input type="checkbox" id="maptool_grid_is_moving" />
					<label class="squaredFour" for="maptool_grid_is_moving"></label>
				</label>
				<span class="maptoolbox-label-row">
					<?php echo uh($translator->{'Coordinates'}); ?>
					<label style="margin: 0px; float:right; padding:0px 0.66em 0px 0px;"> 
						Y &nbsp
						<input type="text" id="maptool_grid_coord_y" value="0" class="spinner" />
					</label>					
					<label style="margin: 0px; float:right; padding:0px 0.833em 0px 0px;"> 
						X &nbsp
						<input type="text" id="maptool_grid_coord_x" value="0" class="spinner" />
					</label>

				</span>
				<br/>
					<label>
						<?php echo uh($translator->{'X-axis gap'}); ?>
						<input type="text" id="maptool_grid_height" value="20" class="spinner" />
					</label>
					<label>
						<?php echo uh($translator->{'Y-axis gap'}); ?>
						<input type="text" id="maptool_grid_width" value="20" class="spinner" />
					</label>
				<!--<label>
					<span class="maptoolbox-label"><?php echo uh($translator->{'W x H per cell'}); ?>:</span>
					<input type="text" id="maptool_grid_width_rat" value="20" class="spinner" />
					x
					<input type="text" id="maptool_grid_height_rat" value="20" class="spinner" />
				</label>-->
				<br/>
				<label style="display: inline-block;">
					<input type="button" class="greenbutton" id="maptool_grid_save" value="<?php echo uh($translator->{'Save'}); ?>" />
				</label>
				<label style="display: inline-block;">
					<input type="button" class="greenbutton" id="maptool_grid_reset" value="<?php echo uh($translator->{'Reset grid'}); ?>" />
				</label>
			</div>
		</div>

		<style id="maptool_grid_style">
		</style>
<?php else: ?>
	<style>
	#maptool_grid {
		display:none;
	}
	</style>
		<p id="zoombar">
			<img src="images/zoom_slider.png" alt=""/>
			<a href="javascript:void(0)" id="in"></a>
			<a href="javascript:void(0)" id="out"></a>
		</p>
<?php endif; ?>

	<div id="mapHolder">
		<div id="map">
			<div id="maptool_grid"><div id="maptool_grid_frame"></div></div>
			<img alt="" id="map_img" />
		</div>
	</div>

<?php endif;?>

<script type="text/javascript">

	<?php switch (LANGUAGE) {
		case "sv":
			$locale = "sv-SE";
			break;
		case "eng":
			$locale = "en-US";
			break;
		case "de":
			$locale = "de-DE";
			break;
		case "es":
			$locale = "es-ES";
			break;
	}?>

	lang.locale = "<?php echo $locale; ?>";
	lang.visit_us_facebook = '<?php echo ujs($translator->{"Visit us on Facebook"}); ?>';
	lang.visit_us_twitter = '<?php echo ujs($translator->{"Visit us on Twitter"}); ?>';
	lang.visit_us_google = '<?php echo ujs($translator->{"Visit us on Google"}); ?>';
	lang.visit_us_youtube = '<?php echo ujs($translator->{"Visit us on Youtube"}); ?>';
	lang.bookStandSpace = '<?php echo ujs($translator->{"Book stand space (if already payed)"}); ?>';
	lang.bookPrelStandSpace = '<?php echo ujs($translator->{"Book requested stand space"}); ?>';
	lang.editBookedStandSpace = '<?php echo ujs($translator->{"Edit booking for stand space"}); ?>';
	lang.editReservedStandSpace = '<?php echo ujs($translator->{"Edit reservation for stand space"}); ?>';
	lang.editStandSpace = '<?php echo ujs($translator->{"Edit stand space"}); ?>';
	lang.newStandSpace = '<?php echo ujs($translator->{"New stand space"}); ?>';
	lang.moveStandSpace = '<?php echo ujs($translator->{"Move stand space"}); ?>';
	lang.deleteStandSpace = '<?php echo ujs($translator->{"Delete stand space"}); ?>';
	lang.reserveStandSpace = '<?php echo ujs($translator->{"Reserve stand space (if not yet payed"}); ?>';
	lang.reservePrelStandSpace = '<?php echo ujs($translator->{"Reserve requested stand space"}); ?>';
	lang.preliminaryBookStandSpace = '<?php echo ujs($translator->{"Preliminary book stand space"}); ?>';
	lang.applyForFair = '<?php echo ujs($translator->{"Queue for stand space"}); ?>';
	lang.cancelPreliminaryBooking = '<?php echo ujs($translator->{"Cancel preliminary booking"}); ?>';
	lang.editBooking = '<?php echo ujs($translator->{"Edit booking"}); ?>';
	lang.cancelBooking = '<?php echo ujs($translator->{"Cancel booking"}); ?>';
	lang.cancelBookingComment = '<?php echo ujs($translator->{"Enter comment about deletion"}); ?>';
	lang.pasteExhibitor = '<?php echo ujs($translator->{"Paste exhibitor"}); ?>';
	lang.notes = '<?php echo ujs($translator->{"Notes"}); ?>';
	lang.moreInfo = '<?php echo ujs($translator->{"More info"}); ?>';
	lang.space = '<?php echo ujs($translator->{"Space"}); ?>';
	lang.status = '<?php echo ujs($translator->{"Status"}); ?>';
	lang.area = '<?php echo ujs($translator->{"Area"}); ?>';
	lang.cloned = '<?php echo ujs($translator->{"(Cloned)"}); ?>';
	lang.price = '<?php echo ujs($translator->{"Price (without VAT)"}); ?>';
	lang.reservedUntil = '<?php echo ujs($translator->{"Reserved until"}); ?>';
	lang.by = '<?php echo ujs($translator->{"by"}); ?>';
	lang.bookedBy = '<?php echo ujs($translator->{"Booked by"}); ?>';
	lang.commodity = '<?php echo ujs($translator->{"commodity"}); ?>';
	lang.no_commodity = '<?php echo ujs($translator->{"No commodity has been entered by the exhibitor"}); ?>';
	lang.clickToReserveStandSpace = '<?php echo ujs($translator->{"Click to reserve stand space"}); ?>';
	lang.presentation = '<?php echo ujs($translator->{"Presentation"}); ?>';
	lang.ex_presentation = '<?php echo ujs($translator->{"Exhibitor presentation"}); ?>';
	lang.info = '<?php echo ujs($translator->{"Info"}); ?>';
	lang.standSpaceInformation = '<?php echo ujs($translator->{"Information about the stand space"}); ?>';
	lang.deleteConfirm = '<?php echo ujs($translator->{"Are you sure you want to delete this marker?"}); ?>';
	lang.website = '<?php echo ujs($translator->{"Website"}); ?>';
	lang.print = '<?php echo ujs($translator->{"Print"}); ?>';
	lang.category = '<?php echo ujs($translator->{"Categories"}); ?>';
	lang.extra_options = '<?php echo ujs($translator->{"Extra options"}); ?>';
	lang.no_options = '<?php echo ujs($translator->{"No extra options."}); ?>';
	lang.articles = '<?php echo ujs($translator->{"Articles"}); ?>';
	lang.noPlaceRights = '<?php echo ujs($translator->{"You do not have administrative rights on this map"}); ?>';
	lang.clickToViewMoreInfo = '<?php echo ujs($translator->{"Click to view more information"}); ?>';
	lang.loginToViewMoreInfo = '<?php echo ujs($translator->{"Login to view more information"}); ?>';
	lang.noPresentationText = '<?php echo ujs($translator->{"The company has not specified any information."}); ?>';
	lang.insert_comment = '<?php echo ujs($translator->{"Insert comment"}); ?>';
	lang.viewBooking = '<?php echo ujs($translator->{"View booking"}); ?>';
	lang.showPreliminaryBookings = '<?php echo ujs($translator->{"View preliminary bookings"}); ?>';
	lang.passwd_superstrong = '<?php echo ujs($translator->{"Super strong"}); ?>';
	lang.passwd_strong = '<?php echo ujs($translator->{"Strong"}); ?>';
	lang.passwd_medium = '<?php echo ujs($translator->{"Medium"}); ?>';
	lang.passwd_weak = '<?php echo ujs($translator->{"Weak"}); ?>';
	lang.white_grid = '<?php echo ujs($translator->{"White grid"}); ?>';
	lang.black_grid = '<?php echo ujs($translator->{"Black grid"}); ?>';
	
	lang.StatusText = function(str) {
		if (str == 'open')
			return '<?php echo ujs($translator->{"open"}); ?>';
		else if (str == 'reserved')
			return '<?php echo ujs($translator->{"reserved"}); ?>';
		else if (str == 'booked')
			return '<?php echo ujs($translator->{"booked"}); ?>';
		else if (str == 'applied')
			return '<?php echo ujs($translator->{"preliminary booked"}); ?>';
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

	var fair_url = '<?php echo $fair->get('url')?>';
	var accessibleMaps = new Array;
	<?php foreach($accessible_maps as $map): ?>
		accessibleMaps.push(<?php echo $map ?>);
	<?php endforeach; ?>

	<?php if ($visible == 'true' || ($visible == 'false' && $hasRights)): ?>
	$(document).ready(function() {
		<?php 
			$id = "";
			if(!empty($myMap)){
				if($myMap == '\'false\''){
					$maps = $fair->get('maps');
					$id = reset($maps)->get('id');
				} else {
					$id = $myMap;
				}
			}
			echo 'maptool.init('.$id.');';
			
		?>
		
		<?php if (isset($_SESSION['copied_exhibitor'])): ?>
		copiedExhibitor = "<?php echo $_SESSION['copied_exhibitor'] ?>";
		<?php endif; ?>

		<?php if (isset($copied_fair_registration)): ?>
		copiedFairRegistration = <?php echo JsonResponse::encode($copied_fair_registration); ?>;
		<?php endif; ?>
	});
	<?php endif; ?>
</script>
<script>
	var confirmDialogue = "<?php echo $translator->{'Are you sure that you want to remove stand space'}; ?>";
	var deletion = "<?php echo $translator->{'Enter comment about deletion'}; ?>";

	function denyPrepPosition(link, position, status, clicked){
		if(confirm(confirmDialogue.replace('%s', position))){
			var message = prompt(deletion, "");
			denyPosition(link, message, position, status, clicked);
		}
	}
</script>


<div id="preliminaryConfirm" class="dialogue">
	<img src="images/icons/close_dialogue.png" style="margin-top:-3.7em" class="closeDialogue"/>
	<h2 class="standSpaceName" style="margin-top:-3.15em; text-align:center;"><?php echo $translator->{"Thank you for your preliminary booking"}; ?></h2>
	<p><?php echo $translator->{"A receipt of your booking has been sent to your inbox and it is now up to the Organizer to do the rest of the work. You can preliminary book more stand spaces if you want in the same manner."}; ?></p>
	<input type="button" class="greenbutton mediumbutton closeDialogue" style="margin-bottom:1em;" value="Ok" />
</div>

<div id="fairRegistrationConfirm" class="dialogue">
	<img src="images/icons/close_dialogue.png" style="margin-top:-3.7em" class="closeDialogue"/>
	<h2 class="standSpaceName" style="margin-top:-3.15em; text-align:center;"><?php echo $translator->{"Thank you for your application"}; ?></h2>
	<p><?php echo $translator->{"A receipt of your application has been sent to your inbox and it is now up to the Organizer to do the rest of the work. You can apply for more stand spaces if you want in the same manner."}; ?></p>
	<input type="button" class="greenbutton mediumbutton closeDialogue" style="margin-bottom:1em;" value="Ok" />
</div>

<div id="edit_position_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" style="margin-top:-3.7em" class="closeDialogue"/>
	<h3 class="standSpaceName"><?php echo uh($translator->{'New/Edit stand space'}); ?></h3>

	<label for="position_name_input"><?php echo uh($translator->{'Name'}); ?> *</label>
	<input type="text" class="dialogueInput"  name="position_name_input" id="position_name_input"/>

	<label for="position_area_input"><?php echo uh($translator->{'Area'}); ?> </label>
	<input type="text" class="dialogueInput"  name="position_area_input" id="position_area_input"/>

	<label for="position_price_input"><?php echo uh($translator->{'Price (without VAT)'}); ?> </label>
	<input type="number" class="form-control bfh-number" name="position_price_input" min="0" value="0" step="0.01" id="position_price_input" title="<?php echo ujs($translator->{"Comma as delimiter is not accepted. Please use dot instead (eg: 234.53 = 234,53)."}); ?>" style="width:10em; text-align:left;" />

	<label for="position_info_input"><?php echo uh($translator->{'Information'}); ?></label>
	<textarea name="position_info_input" id="position_info_input" placeholder="<?php echo uh($translator->{'Enter information about the stand space that would be interesting for the exhibitor to know, for example: This stand space is in the center of the IT-area and very well positioned for demonstration of your products.'}); ?>"></textarea>

	<input type="hidden" name="position_id_input" id="position_id_input" value=""/>

	<p><input type="button" class="greenbutton mediumbutton" id="post_position" value="<?php echo uh($translator->{'Save and close'}); ?>"/></p>

</div>

<div id="more_info_dialogue" class="dialogue">
	<h3 class="standSpaceName"></h3>
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -0.5em;"/>
	<br />
	<div id="column" style="width:21.66em;">
		<?php if ($_COOKIE['language'] == "sv"): ?>
		<img id="ex_logo" style="text-align:center;" src="../images/images/no_logo_Svenska.png" />
		<?php else : ?>
		<img id="ex_logo" style="text-align:center;" src="../images/images/no_logo_English.png" />
		<?php endif; ?>
			<div style="display:inline-block; padding:2em 1em 1em 0; max-width:45%" id="status"></div>
			<div style="display:inline-block; float:right; width:55%; padding:2em 0em 1em 0em;" id="area"></div>
			<div style="display:none; padding-top: 1.5em" id="price"></div>
		
	</div>
	<div id="column">
		<div class="info">
			<div class="website_link"></div>
			<div style="display:inline-block; padding:1em 0 0 0; width:100%;" id="categories"></div>
			<div style="display:inline-block; padding:1em 0 1em 0; width:100%;" id="commodity"></div>
		</div>
	</div>
		<h4></h4>
		<div class="presentation"></div>
		<div id="more_info_print"></div>
</div>

<div id="preliminary_bookings_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -3.7em; margin-right: -0.5em;" />
	<h3 class="standSpaceName"><?php echo uh($translator->{"Preliminary bookings"}); ?></h3>
	<table class="std_table" id="prelBookingsList">
		<thead>
			<tr>
				<th><?php echo uh($translator->{"Stand space"}); ?></th>
				<th><?php echo uh($translator->{"Area"}); ?></th>
				<th><?php echo uh($translator->{'Booked by'}); ?></th>
				<th><?php echo uh($translator->{"Trade"}); ?></th>
				<th><?php echo uh($translator->{'Time of booking'}); ?></th>
				<th><?php echo uh($translator->{"Message to organizer in list"}); ?></th>
				<th><?php echo uh($translator->{"Deny"}); ?></th>
				<th><?php echo uh($translator->{"Approve (if already payed)"}); ?></th>
				<th><?php echo uh($translator->{'Reserve stand space (if not yet payed)'}); ?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
</div>

<div id="todayDt" td="<?php echo time(); ?>"> </div>



<style>
#reserve_position_form fieldset {
	padding-top: 0;
	padding-bottom: 2.5em;
	border-top: 5em solid #3258CD;
}

#review_prel_dialogue #column {
	padding-top:0;
}

</style>
<!-- multistep form -->
<form id="reserve_position_form" class="form booking_form">
<!-- progressbar -->
<ul id="progressbar">
	<li class="active"><?php echo uh($translator->{'Categories and assortment'}); ?></li>
	<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
	<li><?php echo uh($translator->{'Message to organizer (optional)'}); ?></li>
	<li><?php echo uh($translator->{'Confirm booking'}); ?>
</ul>
	<fieldset>
		<!-- FIELDSET NUMBER ONE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
	<div id="column" style="padding-right:2em;">
		<div class="ssinfo"></div>
				<!-- Kalenderfunktion för att välja platsens slutgiltiga reservationsdatum -->
		<label for="reserve_expires_input"><?php echo uh($translator->{'Reserved until'}); ?> (DD-MM-YYYY HH:MM)</label>
		<input type="text" class="dialogueInput datetime datepicker" name="reserve_expires_input" id="reserve_expires_input" title="<?php echo uh($translator->{'The date that you set here is the date when the reservation expires and the stand space is reopened (green) for preliminary bookings.'}); ?>" value=""/>
	</div>		
	<div id="column">
		<label for="search_user_input"><?php echo uh($translator->{'Search exhibitor'}); ?></label>
		<input type="text" style="width:25em;" name="search_user_input" id="search_user_input" title="<?php echo ujs($translator->{'While still having focus on the search field: press enter to insert the Exhibitors official commodity.'}); ?>"/>
	  <!-- Drop-downlista för att välja användare att boka in -->	
		<label for="reserve_user_input"><?php echo uh($translator->{'Select Exhibitor'}); ?></label>
		<select  style="width:25em;" name="reserve_user_input" id="reserve_user_input">
			<?php echo makeUserOptions1(0, $fair); ?>
		</select>
		<label class="label_medium" for="reserve_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<textarea name="reserve_commodity_input" maxlength="200" class="commodity_big" id="reserve_commodity_input"></textarea>
	</div>

	    		<!-- Div för att välja kategori -->
		<label class="table_header" for="reserve_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<div class="scrolltable-wrap" id="reserve_category_scrollbox_div">
			<table class="std_table std_booking_table" id="reserve_category_scrollbox">
				<thead>
					<tr>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('categories') as $cat){ ?>
					<tr>
						<td class="left"><?php echo $cat->get('name') ?></td>
						<td class="cfscheckbox">
							<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>	
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="reserve_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER TWO -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
			<!--  Extra tillval -->
	<?php if ($fair->get('extraOptions')): ?>
		<label class="table_header" for="reserve_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table std_booking_table" id="reserve_option_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
						<tr>
							<td><?php echo $extraOption->get('custom_id') ?></td>
							<?php if ($extraOption->get('required') == 1): ?>
								<td class="left"><?php echo $extraOption->get('text') ?>*</td>
							<?php else : ?>
								<td class="left"><?php echo $extraOption->get('text') ?></td>
							<?php endif; ?>
							<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($extraOption->get('vat') == 18) { ?>
								<input hidden value="18" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
							</td>							
							<td class="cfscheckbox">
								<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>"/>
								<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
	<?php echo uh($translator->{"There are no options to display"}); ?>
	<br />
	<?php endif; ?>
			<!--  Artiklar  -->
	<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights): ?>
		<label class="table_header" for="reserve_article_input"><?php echo uh($translator->{"Articles"}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table" id="reserve_article_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('articles') as $article) { ?>
						<?php if ($article->get('required') != 1 || $hasRights): ?>
							<tr>
								<td><?php echo $article->get('custom_id') ?></td>
								<td class="left"><?php echo $article->get('text') ?></td>
								<td><?php echo $article->get('price') ?></td>
								<td style="display:none;">
									<?php if ($article->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($article->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($article->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
								</td>						
								<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
							</tr>
						<?php endif; ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Articles'}); ?></h2>
	<?php echo uh($translator->{"There are no articles to display"}); ?>
	<br />
	<br />
	<?php endif; ?>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
		
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER THREE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />

		<label class="label_long" for="reserve_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="reserve_message_input" class="msg_to_organizer" id="reserve_message_input"></textarea>
		<br />
		<br />

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="reserve_review" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER FOUR -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<div id="review_prel_dialogue">
			<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
			<span style="font-size:1.7em;" id="review_user"></span>
			<br />
			<div id="column" class="review_column1">
				<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<p name="commodity" id="review_commodity_input"></p>
			</div>
			<div id="column" class="review_column2">
				<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
				<p id="review_category_list" style="width:100%; float:left;"></p>		
			</div>
			<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Message to organizer'}); ?></label>
			<p name="arranger_message" id="review_message"></p>	
			<div class="no-search" id="review_list_div" style="padding:1.66em 0px;">
				<div id="review_div">
					<table id="review_list" class="no-search" style="width:100%;">
					</table>
				</div>
				<table id="review_list2" class="no-search" style="width:100%;">
				</table>
			</div>
		</div>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="submit" name="submit" id="reserve_post" class="submit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
		
	</fieldset>
</form>




<style>
#book_position_form fieldset {
	padding-top: 0;
	padding-bottom: 2.5em;
	border-top: solid #d21d1d 5em;
}
    
input[type="checkbox"] {
	margin: 0px 0px 0px -0.66em;
}
</style>
<!-- multistep form -->
<form id="book_position_form" class="form booking_form">
<!-- progressbar -->
<ul id="progressbar">
	<li class="active"><?php echo uh($translator->{'Categories and assortment'}); ?></li>
	<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
	<li><?php echo uh($translator->{'Message to organizer (optional)'}); ?></li>
	<li><?php echo uh($translator->{'Confirm booking'}); ?>
</ul>
	<fieldset>
		<!-- FIELDSET NUMBER ONE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
	<div id="column" style="padding-right:2em;">
		<div class="ssinfo"></div>	
	</div>		
	<div id="column">
		<label for="search_user_input"><?php echo uh($translator->{'Search exhibitor'}); ?></label>
		<input type="text" style="width:25em;" name="search_user_input" id="search_user_input" title="<?php echo ujs($translator->{'While still having focus on the search field: press enter to insert the Exhibitors official commodity.'}); ?>"/>
	  <!-- Drop-downlista för att välja användare att boka in -->	
		<label for="book_user_input"><?php echo uh($translator->{'Select Exhibitor'}); ?></label>
		<select  style="width:25em;" name="book_user_input" id="book_user_input">
			<?php echo makeUserOptions1(0, $fair); ?>
		</select>
		<label class="label_medium" for="book_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<textarea name="book_commodity_input" maxlength="200" class="commodity_big" id="book_commodity_input"></textarea>			
	</div>
	    		<!-- Div för att välja kategori -->
		<label class="table_header" for="book_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<div class="scrolltable-wrap" id="book_category_scrollbox_div">
		<table class="std_table std_booking_table" id="book_category_scrollbox">
			<thead>
				<tr>
					<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
					<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($fair->get('categories') as $cat){ ?>
				<tr>
					<td class="left"><?php echo $cat->get('name') ?></td>
					<td class="cfscheckbox">
						<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
						<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		</div>	
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="book_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER TWO -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
			<!--  Extra tillval -->
	<?php if ($fair->get('extraOptions')): ?>
		<label class="table_header" for="book_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table std_booking_table" id="book_option_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
					<tr>
						<td><?php echo $extraOption->get('custom_id') ?></td>
							<?php if ($extraOption->get('required') == 1): ?>
								<td class="left"><?php echo $extraOption->get('text') ?>*</td>
							<?php else : ?>
								<td class="left"><?php echo $extraOption->get('text') ?></td>
							<?php endif; ?>
						<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($extraOption->get('vat') == 18) { ?>
								<input hidden value="18" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
							</td>						
						<td class="cfscheckbox">
							<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>		
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
	<?php echo uh($translator->{"There are no options to display"}); ?>
	<br />
	<?php endif; ?>
			<!--  Artiklar  -->
	<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights): ?>
		<label class="table_header" for="book_article_input"><?php echo uh($translator->{"Articles"}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table" id="book_article_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('articles') as $article) { ?>
						<?php if ($article->get('required') != 1 || $hasRights): ?>
							<tr>
								<td><?php echo $article->get('custom_id') ?></td>
								<td class="left"><?php echo $article->get('text') ?></td>
								<td><?php echo $article->get('price') ?></td>
								<td style="display:none;">
									<?php if ($article->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($article->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($article->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
								</td>						
								<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
							</tr>
						<?php endif; ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Articles'}); ?></h2>
	<?php echo uh($translator->{"There are no articles to display"}); ?>
	<br />
	<br />
	<?php endif; ?>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
		
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER THREE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />

		<label class="label_long" for="book_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="book_message_input" class="msg_to_organizer" id="book_message_input"></textarea>
		<br />
		<br />

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="book_review" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER FOUR -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<div id="review_prel_dialogue">
			<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
			<span style="font-size:1.7em;" id="review_user"></span>
			<br />			
			<div id="column" class="review_column1">
				<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<p name="commodity" id="review_commodity_input"></p>
			</div>
			<div id="column" class="review_column2">
				<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
				<p id="review_category_list" style="width:100%; float:left;"></p>		
			</div>
			<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Message to organizer'}); ?></label>
			<p name="arranger_message" id="review_message"></p>	
			<div class="no-search" id="review_list_div" style="padding:1.66em 0px;">
				<div id="review_div">
					<table id="review_list" class="no-search" style="width:100%;">
					</table>
				</div>
				<table id="review_list2" class="no-search" style="width:100%;">
				</table>
			</div>
		</div>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="submit" name="submit" id="book_post" class="submit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
		
	</fieldset>
</form>

<style>
#fair_registration_form fieldset {
	padding-top: 0;
	padding-bottom: 2.5em;
	border-top: solid #48A547 5em;
}
</style>
<!-- multistep form -->
<form id="fair_registration_form" class="form booking_form">
<!-- progressbar -->
<ul id="progressbar">
	<li class="active"><?php echo uh($translator->{'Categories and space'}); ?></li>
	<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
	<li><?php echo uh($translator->{'Message and Assortment'}); ?></li>
	<li><?php echo uh($translator->{'Confirm registration'}); ?>
</ul>
	<fieldset>
		<!-- FIELDSET NUMBER ONE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<label for="registration_area_input"><?php echo uh($translator->{'Requested area'}); ?> *</label>
		<input type="text" name="area" id="registration_area_input" />
	    		<!-- Div för att välja kategori -->
		<label class="table_header" for="registration_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<div class="scrolltable-wrap" id="registration_category_scrollbox_div">
			<table class="std_table std_booking_table" id="registration_category_scrollbox">
				<thead>
					<tr>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('categories') as $cat){ ?>
					<tr>
						<td class="left"><?php echo $cat->get('name') ?></td>
						<td class="cfscheckbox">
							<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>	
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="registration_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER TWO -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
			<!--  Extra tillval -->
	<?php if ($fair->get('extraOptions')): ?>
		<label class="table_header" for="registration_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table std_booking_table" id="registration_option_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
						<?php if ($extraOption->get('required') == 1): ?>
						<tr>
							<td><?php echo $extraOption->get('custom_id') ?></td>
							<td class="left"><?php echo $extraOption->get('text') ?>*</td>
							<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input value="25" />
							<?php } else if ($extraOption->get('vat') == 18) { ?>
								<input value="18" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input value="12" />
							<?php } else { ?>
								<input value="0" />
							<?php } ?>
							</td>
							<td class="cfscheckbox">
								<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>" checked="checked" />
								<input type="checkbox" disabled="disabled" checked>
								<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
							</td>
						</tr>
					<?php else : ?>
						<tr>
							<td><?php echo $extraOption->get('custom_id') ?></td>
							<td class="left"><?php echo $extraOption->get('text') ?></td>
							<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($extraOption->get('vat') == 18) { ?>
								<input hidden value="18" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
							</td>
							<td class="cfscheckbox">
								<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>"/>
								<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
							</td>
						</tr>
						<?php endif; ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
	<?php echo uh($translator->{"There are no options to display"}); ?>
	<br />
	<?php endif; ?>
			<!--  Artiklar  -->
	<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights): ?>
		<label class="table_header" for="registration_article_scrollbox"><?php echo uh($translator->{"Articles"}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table" id="registration_article_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('articles') as $article) { ?>
						<?php if ($article->get('required') != 1 || $hasRights): ?>
							<tr>
								<td><?php echo $article->get('custom_id') ?></td>
								<td class="left"><?php echo $article->get('text') ?></td>
								<td><?php echo $article->get('price') ?></td>
								<td style="display:none;">
									<?php if ($article->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($article->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($article->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
								</td>
								<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
							</tr>
						<?php endif; ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Articles'}); ?></h2>
	<?php echo uh($translator->{"There are no articles to display"}); ?>
	<br />
	<br />
	<?php endif; ?>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
		
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER THREE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<label class="label_medium" for="registration_commodity_input"><?php echo uh($translator->{'Commodity'}); ?> *</label>
		<?php if(isset($me)) : ?>
			<textarea name="registration_commodity_input" maxlength="200" class="commodity_big" id="registration_commodity_input" value="<?php echo $me->get('commodity')?>"></textarea>
		<?php else : ?>
			<textarea name="registration_commodity_input" maxlength="200" class="commodity_big" id="registration_commodity_input"></textarea>
		<?php endif; ?>


		<label class="label_long" for="registration_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="registration_message_input" class="msg_to_organizer" id="registration_message_input"></textarea>
		<br />
		<br />

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" id="registration_review" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER FOUR -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<div id="review_prel_dialogue">
			<div id="column" class="review_column1">
				<label for="review_registration_area"><?php echo uh($translator->{'Area'}); ?></label>
				<p name="review_registration_area" id="review_registration_area"></p>
				<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<p name="commodity" id="review_commodity_input"></p>
			</div>
			<div id="column" class="review_column2">
				<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
				<p id="review_category_list" style="width:100%; float:left;"></p>		
			</div>
			<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Message to organizer'}); ?></label>
			<p name="arranger_message" id="review_message"></p>	
			<div class="no-search" id="review_list_div" style="padding:1.66em 0px;">
				<div id="review_div">
					<table id="review_list" class="no-search" style="width:100%;">
					</table>
				</div>
				<table id="review_list2" class="no-search" style="width:100%;">
				</table>
			</div>
		</div>

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="submit" name="submit" id="registration_confirm" class="submit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
		
	</fieldset>
</form>
<style>
#apply_mark_form fieldset {
	padding-top: 0;
	padding-bottom: 2.5em;
	border-top: solid #48A547 5em;
}
</style>
<!-- multistep form -->
<form id="apply_mark_form" class="form booking_form">
<!-- progressbar -->
<ul id="progressbar">
	<li class="active"><?php echo uh($translator->{'Categories'}); ?></li>
	<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
	<li><?php echo uh($translator->{'Message and Assortment'}); ?></li>
	<li><?php echo uh($translator->{'Confirm booking'}); ?>
</ul>
	<fieldset>
		<!-- FIELDSET NUMBER ONE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<div class="ssinfo"></div>
	    		<!-- Div för att välja kategori -->
		<label class="table_header" for="apply_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<div class="scrolltable-wrap" id="apply_category_scrollbox_div">
			<table class="std_table std_booking_table" id="apply_category_scrollbox">
				<thead>
					<tr>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('categories') as $cat){ ?>
					<tr>
						<td class="left"><?php echo $cat->get('name') ?></td>
						<td class="cfscheckbox">
							<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>	
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="prel_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER TWO -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
			<!--  Extra tillval -->
	<?php if ($fair->get('extraOptions')): ?>
		<label class="table_header" for="apply_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table std_booking_table" id="apply_option_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
						<?php if ($extraOption->get('required') == 1): ?>
						<tr>
							<td><?php echo $extraOption->get('custom_id') ?></td>
							<td class="left"><?php echo $extraOption->get('text') ?>*</td>
							<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input value="25" />
							<?php } else if ($extraOption->get('vat') == 18) { ?>
								<input value="18" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input value="12" />
							<?php } else { ?>
								<input value="0" />
							<?php } ?>
							</td>
							<td class="cfscheckbox">
								<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>" checked="checked" />
								<input type="checkbox" disabled="disabled" checked>
								<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
							</td>
						</tr>
					<?php else : ?>
						<tr>
							<td><?php echo $extraOption->get('custom_id') ?></td>
							<td class="left"><?php echo $extraOption->get('text') ?></td>
							<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($extraOption->get('vat') == 18) { ?>
								<input hidden value="18" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
							</td>
							<td class="cfscheckbox">
								<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>"/>
								<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
							</td>
						</tr>
						<?php endif; ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
	<?php echo uh($translator->{"There are no options to display"}); ?>
	<br />
	<?php endif; ?>
			<!--  Artiklar  -->
	<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights): ?>
		<label class="table_header" for="apply_article_scrollbox"><?php echo uh($translator->{"Articles"}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table" id="apply_article_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('articles') as $article) { ?>
						<?php if ($article->get('required') != 1 || $hasRights): ?>
							<tr>
								<td><?php echo $article->get('custom_id') ?></td>
								<td class="left"><?php echo $article->get('text') ?></td>
								<td><?php echo $article->get('price') ?></td>
								<td style="display:none;">
									<?php if ($article->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($article->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($article->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
								</td>
								<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
							</tr>
						<?php endif; ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
	<br/>
	<h2><?php echo uh($translator->{'Articles'}); ?></h2>
	<?php echo uh($translator->{"There are no articles to display"}); ?>
	<br />
	<br />
	<?php endif; ?>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
		
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER THREE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<label class="label_medium" for="apply_commodity_input"><?php echo uh($translator->{'Commodity'}); ?> *</label>
		<?php if(isset($me)) : ?>
			<textarea name="apply_commodity_input" maxlength="200" class="commodity_big" id="apply_commodity_input" value="<?php echo $me->get('commodity')?>"></textarea>
		<?php else : ?>
			<textarea name="apply_commodity_input" maxlength="200" class="commodity_big" id="apply_commodity_input"></textarea>
		<?php endif; ?>


		<label class="label_long" for="apply_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="apply_message_input" class="msg_to_organizer" id="apply_message_input"></textarea>
		<br />
		<br />

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" id="apply_review" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER FOUR -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -1.5em;"/>
		<br />
		<div id="review_prel_dialogue">
			<div id="column" class="review_column1">
				<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<p name="commodity" id="review_commodity_input"></p>
			</div>
			<div id="column" class="review_column2">
				<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
				<p id="review_category_list" style="width:100%; float:left;"></p>		
			</div>
			<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Message to organizer'}); ?></label>
			<p name="arranger_message" id="review_message"></p>	
			<div class="no-search" id="review_list_div" style="padding:1.66em 0px;">
				<div id="review_div">
					<table id="review_list" class="no-search" style="width:100%;">
					</table>
				</div>
				<table id="review_list2" class="no-search" style="width:100%;">
				</table>
			</div>
		</div>

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="submit" name="submit" id="apply_confirm" class="submit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
		
	</fieldset>
</form>


<div id="fair_registration_paste_type_dialogue" class="dialogue" style="border-top: 5em solid #47A547;">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top:-3.8em;" />
	<h3 class="standSpaceName"><?php echo uh($translator->{'Paste registration'}); ?></h3>
	<br/>
	<p>
		<label>
			<?php echo uh($translator->{'Set type of booking:'}); ?>
			<select id="paste_fair_registration_type">
				<option value="0"><?php echo uh($translator->{'Booking'}); ?></option>
				<option value="1"><?php echo uh($translator->{'Reservation'}); ?></option>
			</select>
		</label>
	</p>

	<p>
		<input type="button" class="greenbutton mediumbutton" id="paste_fair_registration" value="<?php echo uh($translator->{'Continue'}); ?>" />
	</p>
</div>
</div>



<?php if( is_int($myMap) ) : ?>
<script>
	$(document).ready(function(){
		$('#map_link_<?php echo $myMap; ?>').click();
	});
</script>
<?php endif; ?>


<?php if (!isset($_SESSION['user_level']) && (!isset($_SESSION['visitor']) || !$_SESSION['visitor'])) { ?>
<div id="nouser_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 22.33em;" />
	<img src="images/button_icons/Chartbooker Fair System Logotype.png" alt="" class="nouser_cfslogo" style="margin: 0 0 0 0.4166em;" />
	<div id="inner">
		<p class="center">
			<?php echo ($translator->{'To use this service you need an account:'}); ?>
		</p>
	</div>

	<div class="clear floatleft cfspanel">
		<p class="right"><a class="link-button loginlink" href="user/login/<?php echo $_SESSION['outside_fair_url'] ?>" id="open_loginform"><span style="font-weight:600;"><?php echo uh($translator->{'I already have an account'}); ?></a></span></p>
 <!--		<div id="user_login_dialogue">
			<form action="user/login" method="post">
				<p class="error"></p>
				<div>
        <label for="user"><?php echo uh($translator->{"Username"}); ?></label>
				<input type="text" name="user" id="user"/>
				<label for="pass"><?php echo uh($translator->{"Password"}); ?></label>
				<input type="password" name="pass" id="pass"/>
					<p>
						<input type="hidden" name="outside_fair_url" value="<?php echo $_SESSION["outside_fair_url"]; ?>" />
						<input type="submit" name="login" value="<?php echo uh($translator->{"Log in"}); ?>" class="save-btn"/>
					</p>
				</div>
			</form>
		</div>-->
	</div>	

	<div class="floatright cfspanel">
		<p><a href="user/register" class="link-button registerlink"><span style="font-weight:600;"><?php echo uh($translator->{'Register new account'}); ?></a></span></p>
	</div>
	
	<div>
		<p><a class="link-button helpLink"><span style="font-weight:600;"><?php echo uh($translator->{'I NEED HELP'}); ?></a></span></p>
		<p></p>
		<p>
			<a href="user/resetPassword/backref/<?php echo $fair->get('url'); ?>">
				<span class="backhref"><?php echo uh($translator->{"Forgot your password?"}); ?></span>
			</a>
		</p>
	</div>
</div>
<div class="modal"><!-- Place at bottom of page --></div>
<script>
	$(document).ready(function() {
		$('#overlay').show();
		$('#nouser_dialogue').show();

	if ( $('#alertBox').filter(':visible').length){
	    $('#nouser_dialogue').hide();
	} 

		ajaxLoginForm($('#user_login_dialogue form'));

		$('#open_loginform').click(function(e) {
			$('#user_login_dialogue').show();
		});

		$('.registerlink').click(function(e) {
			$(".closeDialogue").click(function() {
				if ($('#nouser_dialogue:visible').length) {
					$("#overlay").show();
				}
			});
		});
	});
</script>
<?php } ?>
